<?php

use App\Models\RssItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
});

test('rss item belongs to a user', function () {
    $rssItem = RssItem::factory()->forUser($this->user)->create();

    expect($rssItem->user)->toBeInstanceOf(User::class);
    expect($rssItem->user->id)->toBe($this->user->id);
});

test('user has many rss items', function () {
    $rssItems = RssItem::factory()->count(3)->forUser($this->user)->create();

    expect($this->user->rssItems)->toHaveCount(3);
    expect($this->user->rssItems->first())->toBeInstanceOf(RssItem::class);
});

test('forUser returns all rss items for a specific user', function () {
    // Create RSS items for the user
    $userRssItems = RssItem::factory()->count(3)->forUser($this->user)->create();

    // Create RSS items for another user
    $otherUserRssItems = RssItem::factory()->count(2)->forUser($this->otherUser)->create();

    $result = RssItem::forUser($this->user);

    expect($result)->toHaveCount(3);
    expect($result->pluck('user_id')->unique())->toContain($this->user->id);
    expect($result->pluck('user_id')->unique())->not->toContain($this->otherUser->id);
});

test('forUser returns empty collection when user has no rss items', function () {
    $result = RssItem::forUser($this->user);

    expect($result)->toBeEmpty();
    expect($result)->toBeInstanceOf(\Illuminate\Support\Collection::class);
});

test('forUser returns rss items ordered by publish date desc', function () {
    $oldItem = RssItem::factory()->forUser($this->user)->create(['publish_date' => now()->subDays(2)]);
    $newItem = RssItem::factory()->forUser($this->user)->create(['publish_date' => now()]);
    $middleItem = RssItem::factory()->forUser($this->user)->create(['publish_date' => now()->subDay()]);

    $result = RssItem::forUser($this->user);

    expect($result->first()->id)->toBe($newItem->id);
    expect($result->last()->id)->toBe($oldItem->id);
});

test('forUser returns empty collection when user is null', function () {
    $result = RssItem::forUser(null);

    expect($result)->toBeEmpty();
    expect($result)->toBeInstanceOf(\Illuminate\Support\Collection::class);
});

test('findByUser returns rss item for user', function () {
    $rssItem = RssItem::factory()->forUser($this->user)->create();

    $result = RssItem::findByUser($this->user, $rssItem->id);

    expect($result)->toBeInstanceOf(RssItem::class);
    expect($result->id)->toBe($rssItem->id);
    expect($result->user_id)->toBe($this->user->id);
});

test('findByUser returns null for non-existent rss item', function () {
    $result = RssItem::findByUser($this->user, 999);

    expect($result)->toBeNull();
});

test('findByUser returns null for rss item belonging to different user', function () {
    $rssItem = RssItem::factory()->forUser($this->otherUser)->create();

    $result = RssItem::findByUser($this->user, $rssItem->id);

    expect($result)->toBeNull();
});

test('recentForUser returns items from last 7 days by default', function () {
    $recentItem = RssItem::factory()->forUser($this->user)->create(['publish_date' => now()->subDays(3)]);
    $oldItem = RssItem::factory()->forUser($this->user)->create(['publish_date' => now()->subDays(10)]);
    $veryRecentItem = RssItem::factory()->forUser($this->user)->create(['publish_date' => now()->subDay()]);

    $result = RssItem::recentForUser($this->user);

    expect($result)->toHaveCount(2);
    expect($result->pluck('id'))->toContain($recentItem->id);
    expect($result->pluck('id'))->toContain($veryRecentItem->id);
    expect($result->pluck('id'))->not->toContain($oldItem->id);
});

test('recentForUser returns items ordered by publish date desc', function () {
    $olderRecent = RssItem::factory()->forUser($this->user)->create(['publish_date' => now()->subDays(6)]);
    $newerRecent = RssItem::factory()->forUser($this->user)->create(['publish_date' => now()->subDays(1)]);

    $result = RssItem::recentForUser($this->user);

    expect($result->first()->id)->toBe($newerRecent->id);
    expect($result->last()->id)->toBe($olderRecent->id);
});

test('recentForUser accepts custom days parameter', function () {
    $itemWithinDays = RssItem::factory()->forUser($this->user)->create(['publish_date' => now()->subDays(5)]);
    $itemOutsideDays = RssItem::factory()->forUser($this->user)->create(['publish_date' => now()->subDays(15)]);

    $result = RssItem::recentForUser($this->user, 10);

    expect($result->pluck('id'))->toContain($itemWithinDays->id);
    expect($result->pluck('id'))->not->toContain($itemOutsideDays->id);
});

test('recentForUser returns empty collection when user is null', function () {
    $result = RssItem::recentForUser(null);

    expect($result)->toBeEmpty();
    expect($result)->toBeInstanceOf(\Illuminate\Support\Collection::class);
});

test('rss item can be created with all required fields', function () {
    $rssItemData = [
        'user_id' => $this->user->id,
        'title' => 'Test Article Title',
        'source' => 'Test Source',
        'source_url' => 'https://testsource.com',
        'link' => 'https://testsource.com/article',
        'publish_date' => now(),
        'description' => 'This is a test article description.',
    ];

    $rssItem = RssItem::create($rssItemData);

    expect($rssItem->title)->toBe($rssItemData['title']);
    expect($rssItem->source)->toBe($rssItemData['source']);
    expect($rssItem->source_url)->toBe($rssItemData['source_url']);
    expect($rssItem->link)->toBe($rssItemData['link']);
    expect($rssItem->description)->toBe($rssItemData['description']);
    expect($rssItem->user_id)->toBe($this->user->id);
    expect($rssItem->user)->toBeInstanceOf(User::class);
});

test('rss item can be created through user relationship', function () {
    $itemData = [
        'title' => 'Test Article Title',
        'source' => 'Test Source',
        'source_url' => 'https://testsource.com',
        'link' => 'https://testsource.com/article',
        'publish_date' => now(),
        'description' => 'This is a test article description.',
    ];

    $rssItem = $this->user->rssItems()->create($itemData);

    expect($rssItem->title)->toBe($itemData['title']);
    expect($rssItem->user_id)->toBe($this->user->id);
    expect($rssItem->user)->toBeInstanceOf(User::class);
});

test('rss item factory creates item with user', function () {
    $rssItem = RssItem::factory()->create();

    expect($rssItem->user)->toBeInstanceOf(User::class);
    expect($rssItem->title)->not->toBeEmpty();
    expect($rssItem->source)->not->toBeEmpty();
    expect($rssItem->source_url)->not->toBeEmpty();
    expect($rssItem->link)->not->toBeEmpty();
    expect($rssItem->description)->not->toBeEmpty();
    expect($rssItem->publish_date)->toBeInstanceOf(\Carbon\Carbon::class);
});

test('rss item factory forUser method works correctly', function () {
    $rssItem = RssItem::factory()->forUser($this->user)->create();

    expect($rssItem->user_id)->toBe($this->user->id);
    expect($rssItem->user)->toBeInstanceOf(User::class);
    expect($rssItem->user->id)->toBe($this->user->id);
});

test('rss item factory fromSource method works correctly', function () {
    $source = 'Custom Source';
    $sourceUrl = 'https://customsource.com';

    $rssItem = RssItem::factory()->fromSource($source, $sourceUrl)->forUser($this->user)->create();

    expect($rssItem->source)->toBe($source);
    expect($rssItem->source_url)->toBe($sourceUrl);
});

test('rss item factory recent method works correctly', function () {
    $rssItem = RssItem::factory()->recent()->forUser($this->user)->create();

    expect($rssItem->publish_date)->toBeGreaterThan(now()->subDays(7));
    expect($rssItem->publish_date)->toBeLessThanOrEqual(now());
});

test('rss item can be updated', function () {
    $rssItem = RssItem::factory()->forUser($this->user)->create(['title' => 'Old Title']);
    $newTitle = 'Updated Title';

    $rssItem->update(['title' => $newTitle]);

    expect($rssItem->fresh()->title)->toBe($newTitle);
    expect($rssItem->fresh()->user_id)->toBe($this->user->id);
});

test('rss item can be deleted', function () {
    $rssItem = RssItem::factory()->forUser($this->user)->create();

    $rssItem->delete();

    expect(RssItem::find($rssItem->id))->toBeNull();
    expect($this->user->rssItems()->count())->toBe(0);
});

test('rss item deletion cascades properly', function () {
    $rssItem = RssItem::factory()->forUser($this->user)->create();
    $rssItemId = $rssItem->id;

    $this->user->delete();

    expect(RssItem::find($rssItemId))->toBeNull();
});

test('rss item has correct fillable attributes', function () {
    $rssItem = new RssItem;

    expect($rssItem->getFillable())->toContain('user_id');
    expect($rssItem->getFillable())->toContain('title');
    expect($rssItem->getFillable())->toContain('source');
    expect($rssItem->getFillable())->toContain('source_url');
    expect($rssItem->getFillable())->toContain('link');
    expect($rssItem->getFillable())->toContain('publish_date');
    expect($rssItem->getFillable())->toContain('description');
});

test('rss item has correct casts', function () {
    $rssItem = new RssItem;

    expect($rssItem->getCasts())->toHaveKey('publish_date');
    expect($rssItem->getCasts()['publish_date'])->toBe('datetime');
});

test('rss item timestamps are automatically set', function () {
    $rssItem = RssItem::factory()->forUser($this->user)->create();

    expect($rssItem->created_at)->not->toBeNull();
    expect($rssItem->updated_at)->not->toBeNull();
    expect($rssItem->created_at)->toBeInstanceOf(\Carbon\Carbon::class);
    expect($rssItem->updated_at)->toBeInstanceOf(\Carbon\Carbon::class);
});

test('rss item publish_date is properly cast to datetime', function () {
    $publishDate = now()->subDays(5);
    $rssItem = RssItem::factory()->forUser($this->user)->create(['publish_date' => $publishDate]);

    expect($rssItem->publish_date)->toBeInstanceOf(\Carbon\Carbon::class);
    expect($rssItem->publish_date->toDateString())->toBe($publishDate->toDateString());
});
