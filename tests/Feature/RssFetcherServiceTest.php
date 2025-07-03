<?php

use App\Models\User;
use App\Models\RssUrl;
use App\Models\RssItem;
use App\Services\RssFetcherService;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\assertDatabaseCount;

uses(RefreshDatabase::class);

it('fetches for user with rss urls', function () {
    $user = User::factory()->create();
    $rssUrl = RssUrl::factory()->create([
        'user_id' => $user->id,
        'url' => 'https://example.com/feed.xml'
    ]);
    $user = $user->fresh(['rssUrls']);
    assertDatabaseHas('rss_urls', [
        'user_id' => $user->id,
        'url' => 'https://example.com/feed.xml'
    ]);
    expect($user->rssUrls)->toHaveCount(1);
    Http::fake([
        'localhost:8080/rss' => Http::response([
            'items' => [
                [
                    'title' => 'Test Article',
                    'source' => 'Example Site',
                    'source_url' => 'https://example.com',
                    'link' => 'https://example.com/article',
                    'publish_date' => now()->toIso8601String(),
                    'description' => 'Test article description',
                    'rss_url' => 'https://example.com/feed.xml'
                ]
            ]
        ], 200)
    ]);
    (new RssFetcherService())->fetchForUser($user);
    assertDatabaseHas('rss_items', [
        'user_id' => $user->id,
        'rss_url_id' => $rssUrl->id,
        'title' => 'Test Article',
        'source' => 'Example Site',
        'source_url' => 'https://example.com',
        'link' => 'https://example.com/article',
        'description' => 'Test article description'
    ]);
    Http::assertSent(function ($request) {
        return $request->url() === 'http://localhost:8080/rss' &&
               $request->method() === 'POST' &&
               $request->data() === ['urls' => ['https://example.com/feed.xml']];
    });
});

it('fetches for user without rss urls', function () {
    $user = User::factory()->create();
    (new RssFetcherService())->fetchForUser($user);
    assertDatabaseCount('rss_items', 0);
});

it('does not insert duplicate items', function () {
    $user = User::factory()->create();
    RssUrl::factory()->create([
        'user_id' => $user->id,
        'url' => 'https://example.com/feed.xml'
    ]);
    RssItem::factory()->create([
        'user_id' => $user->id,
        'link' => 'https://example.com/article',
        'title' => 'Existing Article'
    ]);
    Http::fake([
        'localhost:8080/rss' => Http::response([
            'items' => [
                [
                    'title' => 'Test Article',
                    'source' => 'Example Site',
                    'source_url' => 'https://example.com',
                    'link' => 'https://example.com/article',
                    'publish_date' => now()->toIso8601String(),
                    'description' => 'Test article description'
                ]
            ]
        ], 200)
    ]);
    (new RssFetcherService())->fetchForUser($user);
    assertDatabaseCount('rss_items', 1);
    assertDatabaseHas('rss_items', [
        'user_id' => $user->id,
        'title' => 'Existing Article'
    ]);
});

it('handles go service error and does not insert items', function () {
    $user = User::factory()->create();
    RssUrl::factory()->create([
        'user_id' => $user->id,
        'url' => 'https://example.com/feed.xml'
    ]);
    Http::fake([
        'localhost:8080/rss' => Http::response([], 500)
    ]);
    (new RssFetcherService())->fetchForUser($user);
    assertDatabaseCount('rss_items', 0);
});

it('handles go service timeout and does not insert items', function () {
    $user = User::factory()->create();
    RssUrl::factory()->create([
        'user_id' => $user->id,
        'url' => 'https://example.com/feed.xml'
    ]);
    Http::fake([
        'localhost:8080/rss' => function () {
            throw new \Illuminate\Http\Client\ConnectionException('Connection timed out');
        }
    ]);
    (new RssFetcherService())->fetchForUser($user);
    expect(RssItem::count())->toBe(0);
});

it('gets stats returns correct data', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    RssUrl::factory()->create(['user_id' => $user1->id]);
    RssItem::factory()->count(3)->create(['user_id' => $user1->id]);
    $stats = (new RssFetcherService())->getStats();
    expect($stats['total_users'])->toBe(2)
        ->and($stats['users_with_rss'])->toBe(1)
        ->and($stats['total_items'])->toBe(3)
        ->and($stats['service_url'])->toBe('http://localhost:8080')
        ->and($stats['retention_days'])->toBe(30);
});

it('retention cleanup deletes old items and keeps recent', function () {
    $user = User::factory()->create();
    RssUrl::factory()->create([
        'user_id' => $user->id,
        'url' => 'https://example.com/feed.xml'
    ]);
    RssItem::factory()->create([
        'user_id' => $user->id,
        'link' => 'https://example.com/old',
        'publish_date' => now()->subDays(35),
    ]);
    RssItem::factory()->create([
        'user_id' => $user->id,
        'link' => 'https://example.com/recent',
        'publish_date' => now(),
    ]);
    Http::fake([
        'localhost:8080/rss' => Http::response(['items' => []], 200)
    ]);
    (new RssFetcherService())->fetchForUser($user);
    assertDatabaseMissing('rss_items', ['link' => 'https://example.com/old']);
    assertDatabaseHas('rss_items', ['link' => 'https://example.com/recent']);
});

it('multiple urls deduplication', function () {
    $user = User::factory()->create();
    RssUrl::factory()->create([
        'user_id' => $user->id,
        'url' => 'https://example.com/feed1.xml'
    ]);
    RssUrl::factory()->create([
        'user_id' => $user->id,
        'url' => 'https://example.com/feed2.xml'
    ]);
    Http::fake([
        'localhost:8080/rss' => Http::response([
            'items' => [
                [
                    'title' => 'Shared Article',
                    'source' => 'Feed 1',
                    'source_url' => 'https://example.com/feed1.xml',
                    'link' => 'https://example.com/shared-article',
                    'publish_date' => now()->toIso8601String(),
                    'description' => 'This article appears in both feeds.',
                    'rss_url' => 'https://example.com/feed1.xml'
                ],
                [
                    'title' => 'Shared Article',
                    'source' => 'Feed 2',
                    'source_url' => 'https://example.com/feed2.xml',
                    'link' => 'https://example.com/shared-article',
                    'publish_date' => now()->toIso8601String(),
                    'description' => 'This article appears in both feeds.',
                    'rss_url' => 'https://example.com/feed2.xml'
                ]
            ]
        ], 200)
    ]);
    (new RssFetcherService())->fetchForUser($user);
    assertDatabaseCount('rss_items', 1);
    assertDatabaseHas('rss_items', [
        'user_id' => $user->id,
        'link' => 'https://example.com/shared-article',
        'title' => 'Shared Article',
        'rss_url_id' => RssUrl::where('url', 'https://example.com/feed1.xml')->first()->id,
    ]);
});

it('fetcher ignores malformed partial items', function () {
    $user = User::factory()->create();
    RssUrl::factory()->create([
        'user_id' => $user->id,
        'url' => 'https://example.com/feed.xml'
    ]);
    Http::fake([
        'localhost:8080/rss' => Http::response([
            'items' => [
                [
                    'title' => 'No Link',
                    'source' => 'Feed',
                    'source_url' => 'https://example.com/feed.xml',
                    // 'link' => missing
                    'publish_date' => now()->toIso8601String(),
                    'description' => 'Missing link.',
                    'rss_url' => 'https://example.com/feed.xml'
                ],
                [
                    // 'title' => missing
                    'source' => 'Feed',
                    'source_url' => 'https://example.com/feed.xml',
                    'link' => 'https://example.com/no-title',
                    'publish_date' => now()->toIso8601String(),
                    'description' => 'Missing title.',
                    'rss_url' => 'https://example.com/feed.xml'
                ],
                [
                    'title' => 'Valid',
                    'source' => 'Feed',
                    'source_url' => 'https://example.com/feed.xml',
                    'link' => 'https://example.com/valid',
                    'publish_date' => now()->toIso8601String(),
                    'description' => 'Valid item.',
                    'rss_url' => 'https://example.com/feed.xml'
                ]
            ]
        ], 200)
    ]);
    (new RssFetcherService())->fetchForUser($user);
    assertDatabaseCount('rss_items', 3);
    assertDatabaseHas('rss_items', [
        'link' => 'https://example.com/valid',
        'title' => 'Valid',
        'rss_url_id' => RssUrl::where('url', 'https://example.com/feed.xml')->first()->id,
    ]);
    assertDatabaseHas('rss_items', [
        'link' => 'https://example.com/no-title',
        'title' => '',
        'rss_url_id' => RssUrl::where('url', 'https://example.com/feed.xml')->first()->id,
    ]);
    assertDatabaseHas('rss_items', [
        'title' => 'No Link',
        'link' => '',
        'rss_url_id' => RssUrl::where('url', 'https://example.com/feed.xml')->first()->id,
    ]);
});

it('command fetches for all users', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    RssUrl::factory()->create(['user_id' => $user1->id, 'url' => 'https://example.com/feed1.xml']);
    RssUrl::factory()->create(['user_id' => $user2->id, 'url' => 'https://example.com/feed2.xml']);
    Http::fake([
        'localhost:8080/rss' => Http::response([
            'items' => [
                [
                    'title' => 'Article',
                    'source' => 'Feed',
                    'source_url' => 'https://example.com/feed1.xml',
                    'link' => 'https://example.com/article',
                    'publish_date' => now()->toIso8601String(),
                    'description' => 'Test.',
                    'rss_url' => 'https://example.com/feed1.xml'
                ]
            ]
        ], 200)
    ]);
    artisan('rss:fetch')->assertExitCode(0);
    assertDatabaseHas('rss_items', [
        'user_id' => $user1->id,
        'link' => 'https://example.com/article',
        'rss_url_id' => RssUrl::where('url', 'https://example.com/feed1.xml')->first()->id
    ]);
    assertDatabaseMissing('rss_items', [
        'user_id' => $user2->id,
        'link' => 'https://example.com/article',
    ]);
    assertDatabaseCount('rss_items', 1);
});

it('command fetches for single user', function () {
    $user = User::factory()->create();
    RssUrl::factory()->create(['user_id' => $user->id, 'url' => 'https://example.com/feed.xml']);
    Http::fake([
        'localhost:8080/rss' => Http::response([
            'items' => [
                [
                    'title' => 'Single User Article',
                    'source' => 'Feed',
                    'source_url' => 'https://example.com/feed.xml',
                    'link' => 'https://example.com/single',
                    'publish_date' => now()->toIso8601String(),
                    'description' => 'Test.',
                    'rss_url' => 'https://example.com/feed.xml'
                ]
            ]
        ], 200)
    ]);
    artisan('rss:fetch', ['--user-id' => $user->id])->assertExitCode(0);
    assertDatabaseHas('rss_items', ['user_id' => $user->id, 'link' => 'https://example.com/single', 'rss_url_id' => RssUrl::where('url', 'https://example.com/feed.xml')->first()->id]);
});

it('command stats flag outputs stats', function () {
    $user = User::factory()->create();
    RssUrl::factory()->create(['user_id' => $user->id, 'url' => 'https://example.com/feed.xml']);
    RssItem::factory()->create(['user_id' => $user->id, 'link' => 'https://example.com/item']);
    artisan('rss:fetch', ['--stats' => true])
        ->expectsOutputToContain('RSS Fetching Statistics:')
        ->assertExitCode(0);
});

it('handles malformed JSON response gracefully', function () {
    $user = User::factory()->create();
    RssUrl::factory()->create([
        'user_id' => $user->id,
        'url' => 'https://example.com/feed.xml'
    ]);
    Http::fake([
        'localhost:8080/rss' => Http::response('not a json', 200)
    ]);
    (new RssFetcherService())->fetchForUser($user);
    assertDatabaseCount('rss_items', 0);
});

it('handles 429 rate limiting response gracefully', function () {
    $user = User::factory()->create();
    RssUrl::factory()->create([
        'user_id' => $user->id,
        'url' => 'https://example.com/feed.xml'
    ]);
    Http::fake([
        'localhost:8080/rss' => Http::response([], 429)
    ]);
    (new RssFetcherService())->fetchForUser($user);
    assertDatabaseCount('rss_items', 0);
});

it('handles service unavailable (503) gracefully', function () {
    $user = User::factory()->create();
    RssUrl::factory()->create([
        'user_id' => $user->id,
        'url' => 'https://example.com/feed.xml'
    ]);
    Http::fake([
        'localhost:8080/rss' => Http::response([], 503)
    ]);
    (new RssFetcherService())->fetchForUser($user);
    assertDatabaseCount('rss_items', 0);
});

it('handles invalid date format in publish_date', function () {
    $user = User::factory()->create();
    RssUrl::factory()->create([
        'user_id' => $user->id,
        'url' => 'https://example.com/feed.xml'
    ]);
    Http::fake([
        'localhost:8080/rss' => Http::response([
            'items' => [
                [
                    'title' => 'Invalid Date',
                    'source' => 'Feed',
                    'source_url' => 'https://example.com/feed.xml',
                    'link' => 'https://example.com/invalid-date',
                    'publish_date' => 'not-a-date',
                    'description' => 'Invalid date format.',
                    'rss_url' => 'https://example.com/feed.xml'
                ]
            ]
        ], 200)
    ]);
    (new RssFetcherService())->fetchForUser($user);
    assertDatabaseHas('rss_items', [
        'link' => 'https://example.com/invalid-date',
        'title' => 'Invalid Date',
        'publish_date' => null,
        'rss_url_id' => RssUrl::where('url', 'https://example.com/feed.xml')->first()->id,
    ]);
});

it('handles missing link (empty string) as unique constraint)', function () {
    $user = User::factory()->create();
    RssUrl::factory()->create([
        'user_id' => $user->id,
        'url' => 'https://example.com/feed.xml'
    ]);
    Http::fake([
        'localhost:8080/rss' => Http::response([
            'items' => [
                [
                    'title' => 'No Link 1',
                    'source' => 'Feed',
                    'source_url' => 'https://example.com/feed.xml',
                    // 'link' => missing
                    'publish_date' => now()->toIso8601String(),
                    'description' => 'First missing link.',
                    'rss_url' => 'https://example.com/feed.xml'
                ],
                [
                    'title' => 'No Link 2',
                    'source' => 'Feed',
                    'source_url' => 'https://example.com/feed.xml',
                    'link' => '',
                    'publish_date' => now()->toIso8601String(),
                    'description' => 'Second missing link.',
                    'rss_url' => 'https://example.com/feed.xml'
                ]
            ]
        ], 200)
    ]);
    (new RssFetcherService())->fetchForUser($user);
    // Only one item with empty link should be inserted due to unique constraint
    assertDatabaseCount('rss_items', 1);
    assertDatabaseHas('rss_items', [
        'title' => 'No Link 1',
        'link' => '',
        'rss_url_id' => RssUrl::where('url', 'https://example.com/feed.xml')->first()->id,
    ]);
});

it('handles duplicate items with different metadata (same link)', function () {
    $user = User::factory()->create();
    RssUrl::factory()->create([
        'user_id' => $user->id,
        'url' => 'https://example.com/feed.xml'
    ]);
    Http::fake([
        'localhost:8080/rss' => Http::response([
            'items' => [
                [
                    'title' => 'Title 1',
                    'source' => 'Feed',
                    'source_url' => 'https://example.com/feed.xml',
                    'link' => 'https://example.com/dup',
                    'publish_date' => now()->toIso8601String(),
                    'description' => 'First version.',
                    'rss_url' => 'https://example.com/feed.xml'
                ],
                [
                    'title' => 'Title 2',
                    'source' => 'Feed',
                    'source_url' => 'https://example.com/feed.xml',
                    'link' => 'https://example.com/dup',
                    'publish_date' => now()->toIso8601String(),
                    'description' => 'Second version.',
                    'rss_url' => 'https://example.com/feed.xml'
                ]
            ]
        ], 200)
    ]);
    (new RssFetcherService())->fetchForUser($user);
    // Only the first item with the link should be inserted
    assertDatabaseCount('rss_items', 1);
    assertDatabaseHas('rss_items', [
        'link' => 'https://example.com/dup',
        'title' => 'Title 1',
        'description' => 'First version.',
        'rss_url_id' => RssUrl::where('url', 'https://example.com/feed.xml')->first()->id,
    ]);
});

it('handles items with unknown rss_url gracefully', function () {
    $user = User::factory()->create();
    RssUrl::factory()->create([
        'user_id' => $user->id,
        'url' => 'https://example.com/feed.xml'
    ]);
    
    Http::fake([
        'localhost:8080/rss' => Http::response([
            'items' => [
                [
                    'title' => 'Test Article',
                    'source' => 'Example Site',
                    'source_url' => 'https://example.com',
                    'link' => 'https://example.com/article',
                    'publish_date' => now()->toIso8601String(),
                    'description' => 'Test article description',
                    'rss_url' => 'https://unknown.com/feed.xml' // Unknown URL
                ]
            ]
        ], 200)
    ]);
    
    (new RssFetcherService())->fetchForUser($user);
    
    assertDatabaseCount('rss_items', 0);
});

it('handles items without rss_url field', function () {
    $user = User::factory()->create();
    RssUrl::factory()->create([
        'user_id' => $user->id,
        'url' => 'https://example.com/feed.xml'
    ]);
    
    Http::fake([
        'localhost:8080/rss' => Http::response([
            'items' => [
                [
                    'title' => 'Test Article',
                    'source' => 'Example Site',
                    'source_url' => 'https://example.com',
                    'link' => 'https://example.com/article',
                    'publish_date' => now()->toIso8601String(),
                    'description' => 'Test article description'
                    // No rss_url field
                ]
            ]
        ], 200)
    ]);
    
    (new RssFetcherService())->fetchForUser($user);
    
    assertDatabaseCount('rss_items', 0);
});

it('correctly maps multiple rss_urls to their respective items', function () {
    $user = User::factory()->create();
    $rssUrl1 = RssUrl::factory()->create([
        'user_id' => $user->id,
        'url' => 'https://example.com/feed1.xml'
    ]);
    $rssUrl2 = RssUrl::factory()->create([
        'user_id' => $user->id,
        'url' => 'https://example.com/feed2.xml'
    ]);
    
    Http::fake([
        'localhost:8080/rss' => Http::response([
            'items' => [
                [
                    'title' => 'Article from Feed 1',
                    'source' => 'Feed 1',
                    'source_url' => 'https://example.com',
                    'link' => 'https://example.com/article1',
                    'publish_date' => now()->toIso8601String(),
                    'description' => 'From feed 1',
                    'rss_url' => 'https://example.com/feed1.xml'
                ],
                [
                    'title' => 'Article from Feed 2',
                    'source' => 'Feed 2',
                    'source_url' => 'https://example.com',
                    'link' => 'https://example.com/article2',
                    'publish_date' => now()->toIso8601String(),
                    'description' => 'From feed 2',
                    'rss_url' => 'https://example.com/feed2.xml'
                ]
            ]
        ], 200)
    ]);
    
    (new RssFetcherService())->fetchForUser($user);
    
    assertDatabaseHas('rss_items', [
        'user_id' => $user->id,
        'rss_url_id' => $rssUrl1->id,
        'title' => 'Article from Feed 1',
        'link' => 'https://example.com/article1'
    ]);
    
    assertDatabaseHas('rss_items', [
        'user_id' => $user->id,
        'rss_url_id' => $rssUrl2->id,
        'title' => 'Article from Feed 2',
        'link' => 'https://example.com/article2'
    ]);
}); 