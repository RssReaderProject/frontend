<?php

use App\Models\RssUrl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
});

test('rss url belongs to a user', function () {
    $rssUrl = RssUrl::factory()->forUser($this->user)->create();

    expect($rssUrl->user)->toBeInstanceOf(User::class);
    expect($rssUrl->user->id)->toBe($this->user->id);
});

test('user has many rss urls', function () {
    $rssUrls = RssUrl::factory()->count(3)->forUser($this->user)->create();

    expect($this->user->rssUrls)->toHaveCount(3);
    expect($this->user->rssUrls->first())->toBeInstanceOf(RssUrl::class);
});

test('forUser returns all rss urls for a specific user', function () {
    // Create RSS URLs for the user
    $userRssUrls = RssUrl::factory()->count(3)->forUser($this->user)->create();

    // Create RSS URLs for another user
    $otherUserRssUrls = RssUrl::factory()->count(2)->forUser($this->otherUser)->create();

    $result = RssUrl::forUser($this->user);

    expect($result)->toHaveCount(3);
    expect($result->pluck('user_id')->unique())->toContain($this->user->id);
    expect($result->pluck('user_id')->unique())->not->toContain($this->otherUser->id);
});

test('forUser returns empty collection when user has no rss urls', function () {
    $result = RssUrl::forUser($this->user);

    expect($result)->toBeEmpty();
    expect($result)->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class);
});

test('forUser returns rss urls ordered by latest first', function () {
    $oldRssUrl = RssUrl::factory()->forUser($this->user)->create(['created_at' => now()->subDays(2)]);
    $newRssUrl = RssUrl::factory()->forUser($this->user)->create(['created_at' => now()]);
    $middleRssUrl = RssUrl::factory()->forUser($this->user)->create(['created_at' => now()->subDay()]);

    $result = RssUrl::forUser($this->user);

    expect($result->first()->id)->toBe($newRssUrl->id);
    expect($result->last()->id)->toBe($oldRssUrl->id);
});

test('findByUser returns rss url for user', function () {
    $rssUrl = RssUrl::factory()->forUser($this->user)->create();

    $result = RssUrl::findByUser($this->user, $rssUrl->id);

    expect($result)->toBeInstanceOf(RssUrl::class);
    expect($result->id)->toBe($rssUrl->id);
    expect($result->user_id)->toBe($this->user->id);
});

test('findByUser returns null for non-existent rss url', function () {
    $result = RssUrl::findByUser($this->user, 999);

    expect($result)->toBeNull();
});

test('findByUser returns null for rss url belonging to different user', function () {
    $rssUrl = RssUrl::factory()->forUser($this->otherUser)->create();

    $result = RssUrl::findByUser($this->user, $rssUrl->id);

    expect($result)->toBeNull();
});

test('rss url can be created with user association', function () {
    $rssUrlData = [
        'url' => 'https://example.com/feed.xml',
        'user_id' => $this->user->id,
    ];

    $rssUrl = RssUrl::create($rssUrlData);

    expect($rssUrl->url)->toBe($rssUrlData['url']);
    expect($rssUrl->user_id)->toBe($this->user->id);
    expect($rssUrl->user)->toBeInstanceOf(User::class);
});

test('rss url can be created through user relationship', function () {
    $url = 'https://example.com/feed.xml';

    $rssUrl = $this->user->rssUrls()->create(['url' => $url]);

    expect($rssUrl->url)->toBe($url);
    expect($rssUrl->user_id)->toBe($this->user->id);
    expect($rssUrl->user)->toBeInstanceOf(User::class);
});

test('rss url factory creates rss url with user', function () {
    $rssUrl = RssUrl::factory()->create();

    expect($rssUrl->user)->toBeInstanceOf(User::class);
    expect($rssUrl->url)->toMatch('/^https:\/\/.*\.(com|org|net)\/.*\.(xml|rss)$/');
});

test('rss url factory forUser method works correctly', function () {
    $rssUrl = RssUrl::factory()->forUser($this->user)->create();

    expect($rssUrl->user_id)->toBe($this->user->id);
    expect($rssUrl->user)->toBeInstanceOf(User::class);
    expect($rssUrl->user->id)->toBe($this->user->id);
});

test('rss url can be updated', function () {
    $rssUrl = RssUrl::factory()->forUser($this->user)->create(['url' => 'https://old.com/feed.xml']);
    $newUrl = 'https://new.com/feed.xml';

    $rssUrl->update(['url' => $newUrl]);

    expect($rssUrl->fresh()->url)->toBe($newUrl);
    expect($rssUrl->fresh()->user_id)->toBe($this->user->id);
});

test('rss url can be deleted', function () {
    $rssUrl = RssUrl::factory()->forUser($this->user)->create();

    $rssUrl->delete();

    expect(RssUrl::find($rssUrl->id))->toBeNull();
    expect($this->user->rssUrls()->count())->toBe(0);
});

test('rss url deletion cascades properly', function () {
    $rssUrl = RssUrl::factory()->forUser($this->user)->create();
    $rssUrlId = $rssUrl->id;

    $this->user->delete();

    expect(RssUrl::find($rssUrlId))->toBeNull();
});

test('rss url has correct fillable attributes', function () {
    $rssUrl = new RssUrl;

    expect($rssUrl->getFillable())->toContain('url');
    expect($rssUrl->getFillable())->toContain('user_id');
});

test('rss url timestamps are automatically set', function () {
    $rssUrl = RssUrl::factory()->forUser($this->user)->create();

    expect($rssUrl->created_at)->not->toBeNull();
    expect($rssUrl->updated_at)->not->toBeNull();
    expect($rssUrl->created_at)->toBeInstanceOf(\Carbon\Carbon::class);
    expect($rssUrl->updated_at)->toBeInstanceOf(\Carbon\Carbon::class);
});

test('recordFailure increments consecutive_failures and sets last_failure_at, disables at 10', function () {
    $rssUrl = RssUrl::factory()->forUser($this->user)->create([
        'consecutive_failures' => 0,
        'last_failure_at' => null,
        'disabled_at' => null,
    ]);

    $rssUrl->recordFailure();
    $rssUrl->refresh();
    expect($rssUrl->consecutive_failures)->toBe(1);
    expect($rssUrl->last_failure_at)->not->toBeNull();
    expect($rssUrl->is_disabled)->toBeFalse();

    // Fail 9 more times
    for ($i = 0; $i < 9; $i++) {
        $rssUrl->recordFailure();
    }
    $rssUrl->refresh();
    expect($rssUrl->consecutive_failures)->toBe(10);
    expect($rssUrl->is_disabled)->toBeTrue();
    expect($rssUrl->disabled_at)->not->toBeNull();
});

test('recordSuccess resets consecutive_failures and last_failure_at', function () {
    $rssUrl = RssUrl::factory()->forUser($this->user)->create([
        'consecutive_failures' => 5,
        'last_failure_at' => now(),
    ]);
    $rssUrl->recordSuccess();
    $rssUrl->refresh();
    expect($rssUrl->consecutive_failures)->toBe(0);
    expect($rssUrl->last_failure_at)->toBeNull();
});

test('reEnable resets disabled_at and failure state', function () {
    $rssUrl = RssUrl::factory()->forUser($this->user)->create([
        'consecutive_failures' => 10,
        'last_failure_at' => now(),
        'disabled_at' => now(),
    ]);
    $rssUrl->reEnable();
    $rssUrl->refresh();
    expect($rssUrl->is_disabled)->toBeFalse();
    expect($rssUrl->consecutive_failures)->toBe(0);
    expect($rssUrl->last_failure_at)->toBeNull();
    expect($rssUrl->disabled_at)->toBeNull();
});

test('is_disabled virtual property works', function () {
    $rssUrl = RssUrl::factory()->forUser($this->user)->create(['disabled_at' => null]);
    expect($rssUrl->is_disabled)->toBeFalse();
    $rssUrl->update(['disabled_at' => now()]);
    $rssUrl->refresh();
    expect($rssUrl->is_disabled)->toBeTrue();
});

test('shouldSkipFetch returns correct value based on active scope logic', function () {
    // Not disabled, <3 failures
    $rssUrl = RssUrl::factory()->forUser($this->user)->create([
        'consecutive_failures' => 2,
        'last_failure_at' => now(),
        'disabled_at' => null,
    ]);
    expect($rssUrl->shouldSkipFetch())->toBeFalse();

    // Not disabled, last_failure_at null
    $rssUrl->update(['consecutive_failures' => 5, 'last_failure_at' => null]);
    $rssUrl->refresh();
    expect($rssUrl->shouldSkipFetch())->toBeFalse();

    // Not disabled, 3+ failures, last_failure_at < 1 day ago
    $rssUrl->update(['consecutive_failures' => 3, 'last_failure_at' => now()->subHours(12)]);
    $rssUrl->refresh();
    expect($rssUrl->shouldSkipFetch())->toBeTrue();

    // Not disabled, 3+ failures, last_failure_at > 1 day ago, <10 failures
    $rssUrl->update(['consecutive_failures' => 5, 'last_failure_at' => now()->subDays(2)]);
    $rssUrl->refresh();
    expect($rssUrl->shouldSkipFetch())->toBeFalse();

    // Not disabled, 10 failures, last_failure_at > 1 day ago
    $rssUrl->update(['consecutive_failures' => 10, 'last_failure_at' => now()->subDays(2)]);
    $rssUrl->refresh();
    expect($rssUrl->shouldSkipFetch())->toBeTrue();

    // Disabled
    $rssUrl->update(['disabled_at' => now()]);
    $rssUrl->refresh();
    expect($rssUrl->shouldSkipFetch())->toBeTrue();
});

test('active scope returns only active URLs', function () {
    $active = RssUrl::factory()->forUser($this->user)->create([
        'consecutive_failures' => 0,
        'last_failure_at' => null,
        'disabled_at' => null,
    ]);
    $cooldown = RssUrl::factory()->forUser($this->user)->create([
        'consecutive_failures' => 3,
        'last_failure_at' => now()->subHours(12),
        'disabled_at' => null,
    ]);
    $disabled = RssUrl::factory()->forUser($this->user)->create([
        'consecutive_failures' => 10,
        'last_failure_at' => now(),
        'disabled_at' => now(),
    ]);
    $out_of_cooldown = RssUrl::factory()->forUser($this->user)->create([
        'consecutive_failures' => 3,
        'last_failure_at' => now()->subDays(2),
        'disabled_at' => null,
    ]);

    $activeUrls = RssUrl::activeForUser($this->user);

    $activeUrlIds = $activeUrls->pluck('id')->toArray();

    expect($activeUrlIds)->toContain($active->id);
    expect($activeUrlIds)->toContain($out_of_cooldown->id);
    expect($activeUrlIds)->not->toContain($cooldown->id);
    expect($activeUrlIds)->not->toContain($disabled->id);
});
