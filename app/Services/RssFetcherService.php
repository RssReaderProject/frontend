<?php

namespace App\Services;

use App\Models\RssItem;
use App\Models\RssUrl;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

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
        // Always fetch the latest RSS URLs from the database
        $rssUrls = $user->rssUrls()->get();
        
        if ($rssUrls->isEmpty()) {
            Log::info("No RSS URLs found for user {$user->id}");
            return;
        }

        $urls = $rssUrls->pluck('url')->toArray();
        
        try {
            $response = Http::timeout(30)->post($this->serviceUrl . '/rss', [
                'urls' => $urls
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->processItems($user, $data['items'] ?? []);
                Log::info("Successfully fetched RSS items for user {$user->id}", [
                    'urls_count' => count($urls),
                    'items_count' => count($data['items'] ?? [])
                ]);
            } else {
                Log::error("Failed to fetch RSS items for user {$user->id}", [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Exception while fetching RSS items for user {$user->id}", [
                'error' => $e->getMessage(),
                'urls' => $urls
            ]);
        }
    }

    /**
     * Process RSS items and update database intelligently
     */
    private function processItems(User $user, array $items): void
    {
        Log::info("Processing items for user {$user->id}", [
            'items_count' => count($items),
            'items' => $items
        ]);

        if (empty($items)) {
            Log::info("No items to process for user {$user->id}");
            $this->cleanupOldItems($user); // Always run cleanup
            return;
        }

        $newItems = [];
        $updatedCount = 0;

        foreach ($items as $item) {
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

        Log::info("Prepared items for insertion", [
            'user_id' => $user->id,
            'new_items_count' => count($newItems),
            'updated_count' => $updatedCount
        ]);

        // Insert new items in batches, using ignore to handle any race conditions
        if (!empty($newItems)) {
            try {
                $inserted = RssItem::insertOrIgnore($newItems);
                Log::info("Inserted items for user {$user->id}", ['inserted_count' => $inserted]);
            } catch (\Exception $e) {
                Log::error("Failed to insert items for user {$user->id}", [
                    'error' => $e->getMessage(),
                    'items_count' => count($newItems)
                ]);
                // If insertOrIgnore fails due to unique constraint, try individual inserts
                foreach ($newItems as $item) {
                    try {
                        RssItem::create($item);
                    } catch (\Exception $insertException) {
                        // Log but continue with other items
                        Log::warning("Failed to insert RSS item", [
                            'user_id' => $user->id,
                            'link' => $item['link'],
                            'error' => $insertException->getMessage()
                        ]);
                    }
                }
            }
        }

        // Clean up old items
        $this->cleanupOldItems($user);

        Log::info("Processed RSS items for user {$user->id}", [
            'new_items' => $updatedCount,
            'total_items' => count($items)
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