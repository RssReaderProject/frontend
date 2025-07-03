<?php

namespace App\Providers;

use App\Models\RssUrl;
use App\Observers\RssUrlObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
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
        RssUrl::observe(RssUrlObserver::class);
    }
}
