<?php

use App\Http\Controllers\RssUrlController;
use App\Http\Controllers\RssItemController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::resource('rss/urls', RssUrlController::class)->names([
        'index' => 'rss.urls.index',
        'create' => 'rss.urls.create',
        'store' => 'rss.urls.store',
        'show' => 'rss.urls.show',
        'edit' => 'rss.urls.edit',
        'update' => 'rss.urls.update',
        'destroy' => 'rss.urls.destroy',
    ]);

    Route::get('/rss/items', [RssItemController::class, 'index'])->name('rss.items.index');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
