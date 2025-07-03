<?php

namespace App\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class ConsoleServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            // Schedule RSS fetch to run every hour
            $schedule->command('rss:fetch')
                ->hourly()
                ->withoutOverlapping()
                ->runInBackground()
                ->onFailure(function () {
                    \Log::error('RSS fetch scheduled task failed');
                });
        });
    }
}
