<?php

namespace App\Services;

use App\Models\RssItem;
use App\Models\RssUrl;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RssFetcherService
{
    private string $serviceUrl;

    private int $retentionDays;

    public function __construct()
    {
        $this->serviceUrl = config('services.rss.url', 'http://localhost:8080');
        $this->retentionDays = config('services.rss.retention_days', 30);
    }

    /**
     * Fetch RSS items for all users
     */
    public function fetchForAllUsers(): void
    {
        $users = User::with('rssUrls')->get();

        foreach ($users as $user) {
            if ($user->rssUrls->isEmpty()) {
                continue;
            }

            $this->fetchForUser($user);
        }
    }

    /**
     * Fetch RSS items for a specific user
     */
    public function fetchForUser(User $user): void
    {
        // Get only active RSS URLs (not disabled and not in cooldown)
        $activeUrls = RssUrl::activeForUser($user);

        if ($activeUrls->isEmpty()) {
            Log::info("No active RSS URLs found for user {$user->id} (all URLs are disabled or in cooldown)");

            return;
        }

        $urls = $activeUrls->pluck('url')->toArray();

        try {
            $response = Http::timeout(30)->post($this->serviceUrl.'/rss', [
                'urls' => $urls,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->processItems($user, $data['items'] ?? [], $activeUrls);
                Log::info("Successfully fetched RSS items for user {$user->id}", [
                    'urls_count' => count($urls),
                    'items_count' => count($data['items'] ?? []),
                ]);
            } else {
                Log::error("Failed to fetch RSS items for user {$user->id}", [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                // Record failure for all attempted URLs
                foreach ($activeUrls as $rssUrl) {
                    $rssUrl->recordFailure();
                }
            }
        } catch (\Exception $e) {
            Log::error("Exception while fetching RSS items for user {$user->id}", [
                'error' => $e->getMessage(),
                'urls' => $urls,
            ]);
            // Record failure for all attempted URLs
            foreach ($activeUrls as $rssUrl) {
                $rssUrl->recordFailure();
            }
        }
    }

    /**
     * Process RSS items and update database intelligently
     */
    private function processItems(User $user, array $items, Collection $activeUrls): void
    {
        // Track unique URLs that returned items
        $successfulUrlKeys = collect();
        $updatedCount = 0;

        DB::transaction(function () use ($user, $items, &$successfulUrlKeys, &$updatedCount) {
            Log::info("Processing items for user {$user->id}", [
                'items_count' => count($items),
                'items' => $items,
            ]);

            if (empty($items)) {
                Log::info("No items to process for user {$user->id}");
                $this->cleanupOldItems($user); // Always run cleanup

                return;
            }

            // Get all RSS URLs for this user to map URLs to IDs
            $rssUrls = $user->rssUrls()->get()->keyBy('url');

            $newItems = [];

            foreach ($items as $item) {
                // Find the RSS URL ID based on the rss_url from the API response
                $rssUrlId = null;
                if (isset($item['rss_url'])) {
                    $rssUrl = $rssUrls->get($item['rss_url']);
                    if ($rssUrl) {
                        $rssUrlId = $rssUrl->id;
                        // Track this URL as successfully returning items (only once)
                        $successfulUrlKeys->put($item['rss_url'], $rssUrl);
                    } else {
                        Log::warning("RSS URL not found for user {$user->id}", [
                            'rss_url' => $item['rss_url'],
                            'available_urls' => $rssUrls->keys()->toArray(),
                        ]);
                    }
                }

                // Only create the item if rss_url_id is found (i.e., the item belongs to one of the user's feeds)
                if ($rssUrlId === null) {
                    continue;
                }

                // Check if item already exists using the unique constraint
                $exists = RssItem::where('user_id', $user->id)
                    ->where('link', $item['link'] ?? '')
                    ->exists();

                if ($exists) {
                    Log::info("Item already exists for user {$user->id}", ['link' => $item['link']]);

                    continue;
                }

                $newItems[] = [
                    'user_id' => $user->id,
                    'rss_url_id' => $rssUrlId,
                    'title' => $item['title'] ?? '',
                    'source' => $item['source'] ?? '',
                    'source_url' => $item['source_url'] ?? '',
                    'link' => $item['link'] ?? '',
                    'publish_date' => $this->parsePublishDate($item['publish_date'] ?? ''),
                    'description' => $item['description'] ?? '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $updatedCount++;
            }

            Log::info('Prepared items for insertion', [
                'user_id' => $user->id,
                'new_items_count' => count($newItems),
                'updated_count' => $updatedCount,
            ]);

            // Insert new items in batches, using ignore to handle any race conditions
            if (! empty($newItems)) {
                try {
                    $inserted = RssItem::insertOrIgnore($newItems);
                    Log::info("Inserted items for user {$user->id}", ['inserted_count' => $inserted]);
                } catch (\Exception $e) {
                    Log::error("Failed to insert items for user {$user->id}", [
                        'error' => $e->getMessage(),
                        'items_count' => count($newItems),
                    ]);
                    // If insertOrIgnore fails due to unique constraint, try individual inserts
                    foreach ($newItems as $item) {
                        try {
                            RssItem::create($item);
                        } catch (\Exception $insertException) {
                            // Log but continue with other items
                            Log::warning('Failed to insert RSS item', [
                                'user_id' => $user->id,
                                'link' => $item['link'],
                                'error' => $insertException->getMessage(),
                            ]);
                        }
                    }
                }
            }

            // Clean up old items
            $this->cleanupOldItems($user);
        });

        // Call recordSuccess() outside the transaction to ensure updates are visible after the transaction
        foreach ($successfulUrlKeys as $rssUrl) {
            // Call recordSuccess for each unique URL that returned items
            $rssUrl->recordSuccess();
        }

        Log::info("Processed RSS items for user {$user->id}", [
            'new_items' => $updatedCount,
            'total_items' => count($items),
        ]);
    }

    /**
     * Parse publish date from various formats
     */
    private function parsePublishDate(?string $dateString): ?string
    {
        if (empty($dateString)) {
            return null;
        }

        try {
            $date = new \DateTime($dateString);

            return $date->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            Log::warning("Failed to parse publish date: {$dateString}");

            return null;
        }
    }

    /**
     * Clean up old RSS items based on retention period
     */
    private function cleanupOldItems(User $user): void
    {
        $cutoffDate = now()->subDays($this->retentionDays);

        $deletedCount = RssItem::where('user_id', $user->id)
            ->where('publish_date', '<', $cutoffDate)
            ->delete();

        if ($deletedCount > 0) {
            Log::info("Cleaned up {$deletedCount} old RSS items for user {$user->id}");
        }
    }

    /**
     * Get statistics for RSS fetching
     */
    public function getStats(): array
    {
        $totalUsers = User::count();
        $usersWithRss = User::whereHas('rssUrls')->count();
        $totalItems = RssItem::count();
        $recentItems = RssItem::where('created_at', '>=', now()->subDay())->count();

        return [
            'total_users' => $totalUsers,
            'users_with_rss' => $usersWithRss,
            'total_items' => $totalItems,
            'recent_items' => $recentItems,
            'service_url' => $this->serviceUrl,
            'retention_days' => $this->retentionDays,
        ];
    }
}
