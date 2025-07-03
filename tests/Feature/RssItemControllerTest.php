<?php

use App\Models\RssItem;
use App\Models\RssUrl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
});

test('authenticated user can view rss items index', function () {
    $response = $this->actingAs($this->user)->get(route('rss.items.index'));

    $response->assertStatus(200);
    $response->assertViewIs('rss.items.index');
    $response->assertViewHas('items');
    $response->assertViewHas('feeds');
    $response->assertViewHas('filters');
});

test('unauthenticated user cannot access rss items index', function () {
    $response = $this->get(route('rss.items.index'));

    $response->assertRedirect(route('login'));
});

test('rss items index shows only user items', function () {
    // Create items for the user
    $userItem = RssItem::factory()->forUser($this->user)->create(['title' => 'User Item']);
    
    // Create items for another user
    $otherItem = RssItem::factory()->forUser($this->otherUser)->create(['title' => 'Other Item']);

    $response = $this->actingAs($this->user)->get(route('rss.items.index'));

    $response->assertStatus(200);
    $response->assertViewHas('items', function ($items) use ($userItem, $otherItem) {
        return $items->contains($userItem) && !$items->contains($otherItem);
    });
});

test('rss items are ordered by publish date desc', function () {
    $oldItem = RssItem::factory()->forUser($this->user)->create(['publish_date' => now()->subDays(2)]);
    $newItem = RssItem::factory()->forUser($this->user)->create(['publish_date' => now()]);
    $middleItem = RssItem::factory()->forUser($this->user)->create(['publish_date' => now()->subDay()]);

    $response = $this->actingAs($this->user)->get(route('rss.items.index'));

    $response->assertStatus(200);
    $response->assertViewHas('items', function ($items) use ($newItem, $middleItem, $oldItem) {
        $itemIds = $items->pluck('id')->toArray();
        $expectedOrder = [$newItem->id, $middleItem->id, $oldItem->id];
        return $itemIds === $expectedOrder;
    });
});

test('can filter rss items by title', function () {
    $matchingItem = RssItem::factory()->forUser($this->user)->create(['title' => 'Matching Title']);
    $nonMatchingItem = RssItem::factory()->forUser($this->user)->create(['title' => 'Different Title']);

    $response = $this->actingAs($this->user)->get(route('rss.items.index', ['title' => 'Matching']));

    $response->assertStatus(200);
    $response->assertViewHas('items', function ($items) use ($matchingItem, $nonMatchingItem) {
        return $items->contains($matchingItem) && !$items->contains($nonMatchingItem);
    });
});

test('can filter rss items by date', function () {
    $todayItem = RssItem::factory()->forUser($this->user)->create(['publish_date' => now()]);
    $yesterdayItem = RssItem::factory()->forUser($this->user)->create(['publish_date' => now()->subDay()]);

    $response = $this->actingAs($this->user)->get(route('rss.items.index', ['date' => now()->toDateString()]));

    $response->assertStatus(200);
    $response->assertViewHas('items', function ($items) use ($todayItem, $yesterdayItem) {
        return $items->contains($todayItem) && !$items->contains($yesterdayItem);
    });
});

test('can filter rss items by feed', function () {
    $feed = RssUrl::factory()->forUser($this->user)->create(['url' => 'https://example.com/feed']);
    $otherFeed = RssUrl::factory()->forUser($this->user)->create(['url' => 'https://other.com/feed']);
    
    $matchingItem = RssItem::factory()->forUser($this->user)->forRssUrl($feed)->create();
    $nonMatchingItem = RssItem::factory()->forUser($this->user)->forRssUrl($otherFeed)->create();

    $response = $this->actingAs($this->user)->get(route('rss.items.index', ['feed_id' => $feed->id]));

    $response->assertStatus(200);
    $response->assertViewHas('items', function ($items) use ($matchingItem, $nonMatchingItem) {
        return $items->contains($matchingItem) && !$items->contains($nonMatchingItem);
    });
});

test('feed filter ignores invalid feed id', function () {
    $item = RssItem::factory()->forUser($this->user)->create();

    $response = $this->actingAs($this->user)->get(route('rss.items.index', ['feed_id' => 999]));

    $response->assertStatus(200);
    $response->assertViewHas('items', function ($items) use ($item) {
        return $items->contains($item);
    });
});

test('feed filter ignores feed from other user', function () {
    $otherUserFeed = RssUrl::factory()->forUser($this->otherUser)->create();
    $item = RssItem::factory()->forUser($this->user)->create();

    $response = $this->actingAs($this->user)->get(route('rss.items.index', ['feed_id' => $otherUserFeed->id]));

    $response->assertStatus(200);
    $response->assertViewHas('items', function ($items) use ($item) {
        return $items->contains($item);
    });
});

test('rss items index shows feeds for user', function () {
    $userFeed = RssUrl::factory()->forUser($this->user)->create();
    $otherUserFeed = RssUrl::factory()->forUser($this->otherUser)->create();

    $response = $this->actingAs($this->user)->get(route('rss.items.index'));

    $response->assertStatus(200);
    $response->assertViewHas('feeds', function ($feeds) use ($userFeed, $otherUserFeed) {
        return $feeds->contains($userFeed) && !$feeds->contains($otherUserFeed);
    });
});

test('rss items index shows filters in view', function () {
    $response = $this->actingAs($this->user)->get(route('rss.items.index', [
        'feed_id' => 1,
        'date' => '2023-01-01',
        'title' => 'test'
    ]));

    $response->assertStatus(200);
    $response->assertViewHas('filters', [
        'feed_id' => '1',
        'date' => '2023-01-01',
        'title' => 'test'
    ]);
});

test('rss items index paginates results', function () {
    // Create more than 20 items (default pagination)
    RssItem::factory()->count(25)->forUser($this->user)->create();

    $response = $this->actingAs($this->user)->get(route('rss.items.index'));

    $response->assertStatus(200);
    $response->assertViewHas('items', function ($items) {
        return $items->count() === 20; // Default pagination
    });
}); 