<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\RssFetcherService;
use Illuminate\Console\Command;

class FetchRssItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rss:fetch {--user-id= : Fetch RSS items for a specific user ID} {--stats : Show statistics only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch RSS items from configured RSS URLs for all users or a specific user';

    /**
     * Execute the console command.
     */
    public function handle(RssFetcherService $rssFetcher): int
    {
        $this->info('Starting RSS item fetch process...');

        // Show statistics if requested
        if ($this->option('stats')) {
            $stats = $rssFetcher->getStats();
            $this->displayStats($stats);

            return self::SUCCESS;
        }

        $userId = $this->option('user-id');

        if ($userId) {
            return $this->fetchForSpecificUser($rssFetcher, $userId);
        } else {
            return $this->fetchForAllUsers($rssFetcher);
        }
    }

    /**
     * Fetch RSS items for a specific user
     */
    private function fetchForSpecificUser(RssFetcherService $rssFetcher, int $userId): int
    {
        $user = User::find($userId);

        if (! $user) {
            $this->error("User with ID {$userId} not found.");

            return self::FAILURE;
        }

        $this->info("Fetching RSS items for user: {$user->name} (ID: {$user->id})");

        if ($user->rssUrls->isEmpty()) {
            $this->warn("User {$user->name} has no RSS URLs configured.");

            return self::SUCCESS;
        }

        $this->info("Found {$user->rssUrls->count()} RSS URLs for user {$user->name}");

        try {
            $rssFetcher->fetchForUser($user);
            $this->info("Successfully fetched RSS items for user {$user->name}");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to fetch RSS items for user {$user->name}: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    /**
     * Fetch RSS items for all users
     */
    private function fetchForAllUsers(RssFetcherService $rssFetcher): int
    {
        $usersWithRss = User::whereHas('rssUrls')->count();
        $totalUsers = User::count();

        $this->info("Found {$usersWithRss} users with RSS URLs out of {$totalUsers} total users");

        if ($usersWithRss === 0) {
            $this->warn('No users have RSS URLs configured.');

            return self::SUCCESS;
        }

        try {
            $rssFetcher->fetchForAllUsers();
            $this->info('Successfully fetched RSS items for all users');

            // Show final statistics
            $stats = $rssFetcher->getStats();
            $this->displayStats($stats);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to fetch RSS items: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    /**
     * Display RSS fetching statistics
     */
    private function displayStats(array $stats): void
    {
        $this->newLine();
        $this->info('RSS Fetching Statistics:');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Users', $stats['total_users']],
                ['Users with RSS', $stats['users_with_rss']],
                ['Total RSS Items', $stats['total_items']],
                ['Recent Items (24h)', $stats['recent_items']],
                ['Service URL', $stats['service_url']],
                ['Retention Days', $stats['retention_days']],
            ]
        );
    }
}
