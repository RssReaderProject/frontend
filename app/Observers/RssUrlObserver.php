<?php

namespace App\Observers;

use App\Models\RssUrl;
use App\Services\RssFetcherService;
use Illuminate\Support\Facades\App;

class RssUrlObserver
{
    /**
     * Handle the RssUrl "created" event.
     */
    public function created(RssUrl $rssUrl): void
    {
        $this->scheduleRssFetch($rssUrl);
    }

    /**
     * Handle the RssUrl "updated" event.
     */
    public function updated(RssUrl $rssUrl): void
    {
        $this->scheduleRssFetch($rssUrl);
    }

    /**
     * Handle the RssUrl "deleted" event.
     */
    public function deleted(RssUrl $rssUrl): void
    {
        // No need to fetch RSS for deleted URLs
    }

    /**
     * Handle the RssUrl "restored" event.
     */
    public function restored(RssUrl $rssUrl): void
    {
        $this->scheduleRssFetch($rssUrl);
    }

    /**
     * Handle the RssUrl "force deleted" event.
     */
    public function forceDeleted(RssUrl $rssUrl): void
    {
        // No need to fetch RSS for deleted URLs
    }

    /**
     * Schedule RSS fetching to happen after the response is sent.
     * This ensures only one fetch happens per request, even if multiple URLs are changed.
     */
    private function scheduleRssFetch(RssUrl $rssUrl): void
    {
        // Use a static flag to ensure we only schedule once per request
        static $scheduled = false;
        
        if ($scheduled) {
            return;
        }

        $scheduled = true;

        // Schedule the fetch to happen after the response is sent
        App::terminating(function () use ($rssUrl) {
            try {
                $fetcher = app(RssFetcherService::class);
                $fetcher->fetchForUser($rssUrl->user);
            } catch (\Exception $e) {
                // Log the error but don't throw it since this is happening after response
                \Log::error('Failed to fetch RSS after URL change: ' . $e->getMessage(), [
                    'user_id' => $rssUrl->user_id,
                    'url_id' => $rssUrl->id,
                    'url' => $rssUrl->url
                ]);
            }
        });
    }
}
