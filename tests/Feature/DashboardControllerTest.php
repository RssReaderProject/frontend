<?php

use App\Models\RssItem;
use App\Models\RssUrl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows dashboard with correct stats for user with no data', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertViewIs('dashboard.index');
    $response->assertViewHas('stats', [
        'total_feeds' => 0,
        'active_feeds' => 0,
        'total_posts' => 0,
        'today_posts' => 0,
    ]);
    $response->assertViewHas('recentItems');
    $response->assertSee('Welcome back, '.$user->name);
    $response->assertSee('Total Feeds');
    $response->assertSee('Active Feeds');
    $response->assertSee('Total Posts');
    $response->assertSee('Today');
    $response->assertSee('Posts');
});

it('shows dashboard with correct stats for user with RSS data', function () {
    $user = User::factory()->create();

    // Create RSS URLs
    $activeUrl = RssUrl::factory()->create(['user_id' => $user->id]);
    $disabledUrl = RssUrl::factory()->create([
        'user_id' => $user->id,
        'disabled_at' => now(),
    ]);

    // Create RSS items
    $todayItem = RssItem::factory()->create([
        'user_id' => $user->id,
        'publish_date' => today(),
    ]);
    $yesterdayItem = RssItem::factory()->create([
        'user_id' => $user->id,
        'publish_date' => today()->subDay(),
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertViewHas('stats', [
        'total_feeds' => 2,
        'active_feeds' => 1,
        'total_posts' => 2,
        'today_posts' => 1,
    ]);
});

it('shows recent items in correct order', function () {
    $user = User::factory()->create();
    $rssUrl = RssUrl::factory()->create(['user_id' => $user->id]);

    // Create items with different dates
    $oldItem = RssItem::factory()->create([
        'user_id' => $user->id,
        'rss_url_id' => $rssUrl->id,
        'title' => 'Old Item',
        'publish_date' => now()->subDays(5),
    ]);

    $newItem = RssItem::factory()->create([
        'user_id' => $user->id,
        'rss_url_id' => $rssUrl->id,
        'title' => 'New Item',
        'publish_date' => now()->subDay(),
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertViewHas('recentItems');

    $recentItems = $response->viewData('recentItems');
    expect($recentItems)->toHaveCount(2);
    expect($recentItems->first()->title)->toBe('New Item');
    expect($recentItems->last()->title)->toBe('Old Item');
});

it('limits recent items to 5', function () {
    $user = User::factory()->create();
    $rssUrl = RssUrl::factory()->create(['user_id' => $user->id]);

    // Create 7 items
    RssItem::factory()->count(7)->create([
        'user_id' => $user->id,
        'rss_url_id' => $rssUrl->id,
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertStatus(200);
    $recentItems = $response->viewData('recentItems');
    expect($recentItems)->toHaveCount(5);
});

it('only shows user\'s own data', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    // User 1's data
    RssUrl::factory()->create(['user_id' => $user1->id]);
    RssItem::factory()->create(['user_id' => $user1->id]);

    // User 2's data
    RssUrl::factory()->create(['user_id' => $user2->id]);
    RssItem::factory()->create(['user_id' => $user2->id]);

    $response = $this->actingAs($user1)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertViewHas('stats', [
        'total_feeds' => 1,
        'active_feeds' => 1,
        'total_posts' => 1,
        'today_posts' => 0,
    ]);

    $recentItems = $response->viewData('recentItems');
    expect($recentItems)->toHaveCount(1);
});

it('handles items without publish dates', function () {
    $user = User::factory()->create();
    $rssUrl = RssUrl::factory()->create(['user_id' => $user->id]);

    // Create item without publish date
    RssItem::factory()->create([
        'user_id' => $user->id,
        'rss_url_id' => $rssUrl->id,
        'publish_date' => null,
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertViewHas('stats', [
        'total_feeds' => 1,
        'active_feeds' => 1,
        'total_posts' => 1,
        'today_posts' => 0,
    ]);
});

it('requires authentication', function () {
    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('login'));
});

it('shows correct navigation links', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertSee(route('rss.urls.create'));
    $response->assertSee(route('rss.items.index'));
    $response->assertSee(route('rss.urls.index'));
    $response->assertSee('Add New RSS Feed');
    $response->assertSee('View All Posts');
    $response->assertSee('Manage RSS Feeds');
});

it('shows empty state when no recent items', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertSee('No recent posts found');
});

it('shows recent items with RSS URL information', function () {
    $user = User::factory()->create();
    $rssUrl = RssUrl::factory()->create([
        'user_id' => $user->id,
        'url' => 'https://example.com/feed.xml',
    ]);

    RssItem::factory()->create([
        'user_id' => $user->id,
        'rss_url_id' => $rssUrl->id,
        'title' => 'Test Article',
        'link' => 'https://example.com/article',
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertSee('Test Article');
    $response->assertSee('https://example.com/feed.xml');
    $response->assertSee('https://example.com/article');
});
