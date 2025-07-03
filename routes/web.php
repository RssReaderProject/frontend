<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RssItemController;
use App\Http\Controllers\RssUrlController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('rss/urls', RssUrlController::class)->names([
        'index' => 'rss.urls.index',
        'create' => 'rss.urls.create',
        'store' => 'rss.urls.store',
        'show' => 'rss.urls.show',
        'edit' => 'rss.urls.edit',
        'update' => 'rss.urls.update',
        'destroy' => 'rss.urls.destroy',
    ]);

    Route::patch('/rss/urls/{id}/re-enable', [RssUrlController::class, 'reEnable'])->name('rss.urls.re-enable');

    Route::get('/rss/items', [RssItemController::class, 'index'])->name('rss.items.index');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
