<?php

use App\Http\Controllers\RssUrlController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
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
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
