<?php

use App\Models\RssUrl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('index displays only user rss urls', function () {
    // Create RSS URLs for the authenticated user
    $userRssUrls = RssUrl::factory()->count(3)->forUser($this->user)->create();
    
    // Create RSS URLs for another user
    $otherUser = User::factory()->create();
    $otherUserRssUrls = RssUrl::factory()->count(2)->forUser($otherUser)->create();

    $response = $this->actingAs($this->user)->get('/rss/urls');

    $response->assertStatus(200);
    $response->assertViewIs('rss.urls.index');
    $response->assertViewHas('rssUrls');

    // Should see only user's RSS URLs
    foreach ($userRssUrls as $rssUrl) {
        $response->assertSee($rssUrl->url);
    }
    
    // Should not see other user's RSS URLs
    foreach ($otherUserRssUrls as $rssUrl) {
        $response->assertDontSee($rssUrl->url);
    }
});

test('index shows empty state when no rss urls exist', function () {
    $response = $this->actingAs($this->user)->get('/rss/urls');

    $response->assertStatus(200);
    $response->assertViewIs('rss.urls.index');
    $response->assertViewHas('rssUrls');
    $response->assertSee('No RSS URLs found');
});

test('create displays the create form', function () {
    $response = $this->actingAs($this->user)->get('/rss/urls/create');

    $response->assertStatus(200);
    $response->assertViewIs('rss.urls.create');
});

test('store creates a new rss url with valid data for authenticated user', function () {
    $rssUrlData = [
        'url' => 'https://example.com/feed.xml',
    ];

    $response = $this->actingAs($this->user)->post('/rss/urls', $rssUrlData);

    $response->assertRedirect('/rss/urls');
    $response->assertSessionHas('success', 'RSS URL created successfully.');

    $this->assertDatabaseHas('rss_urls', [
        'url' => $rssUrlData['url'],
        'user_id' => $this->user->id,
    ]);
});

test('store validates required url field', function () {
    $response = $this->actingAs($this->user)->post('/rss/urls', []);

    $response->assertSessionHasErrors(['url']);
    $response->assertStatus(302);
});

test('store validates url format', function () {
    $response = $this->actingAs($this->user)->post('/rss/urls', [
        'url' => 'not-a-valid-url',
    ]);

    $response->assertSessionHasErrors(['url']);
    $response->assertStatus(302);
});

test('store validates unique url per user', function () {
    $existingUrl = 'https://example.com/feed.xml';
    RssUrl::factory()->forUser($this->user)->create(['url' => $existingUrl]);

    $response = $this->actingAs($this->user)->post('/rss/urls', [
        'url' => $existingUrl,
    ]);

    $response->assertSessionHasErrors(['url']);
    $response->assertStatus(302);
});

test('store allows same url for different users', function () {
    $existingUrl = 'https://example.com/feed.xml';
    $otherUser = User::factory()->create();
    RssUrl::factory()->forUser($otherUser)->create(['url' => $existingUrl]);

    $response = $this->actingAs($this->user)->post('/rss/urls', [
        'url' => $existingUrl,
    ]);

    $response->assertRedirect('/rss/urls');
    $response->assertSessionHas('success', 'RSS URL created successfully.');

    $this->assertDatabaseHas('rss_urls', [
        'url' => $existingUrl,
        'user_id' => $this->user->id,
    ]);
});

test('show displays the specified rss url for authenticated user', function () {
    $rssUrl = RssUrl::factory()->forUser($this->user)->create();

    $response = $this->actingAs($this->user)->get("/rss/urls/{$rssUrl->id}");

    $response->assertStatus(200);
    $response->assertViewIs('rss.urls.show');
    $response->assertViewHas('rssUrl', $rssUrl);
    $response->assertSee($rssUrl->url);
});

test('show returns 404 for other users rss url', function () {
    $otherUser = User::factory()->create();
    $rssUrl = RssUrl::factory()->forUser($otherUser)->create();

    $response = $this->actingAs($this->user)->get("/rss/urls/{$rssUrl->id}");

    $response->assertStatus(404);
});

test('show returns 404 for non-existent rss url', function () {
    $response = $this->actingAs($this->user)->get('/rss/urls/999');

    $response->assertStatus(404);
});

test('edit displays the edit form for authenticated user', function () {
    $rssUrl = RssUrl::factory()->forUser($this->user)->create();

    $response = $this->actingAs($this->user)->get("/rss/urls/{$rssUrl->id}/edit");

    $response->assertStatus(200);
    $response->assertViewIs('rss.urls.edit');
    $response->assertViewHas('rssUrl', $rssUrl);
    $response->assertSee($rssUrl->url);
});

test('edit returns 404 for other users rss url', function () {
    $otherUser = User::factory()->create();
    $rssUrl = RssUrl::factory()->forUser($otherUser)->create();

    $response = $this->actingAs($this->user)->get("/rss/urls/{$rssUrl->id}/edit");

    $response->assertStatus(404);
});

test('edit returns 404 for non-existent rss url', function () {
    $response = $this->actingAs($this->user)->get('/rss/urls/999/edit');

    $response->assertStatus(404);
});

test('update modifies existing rss url with valid data for authenticated user', function () {
    $rssUrl = RssUrl::factory()->forUser($this->user)->create(['url' => 'https://old-example.com/feed.xml']);
    $newUrl = 'https://new-example.com/feed.xml';

    $response = $this->actingAs($this->user)->put("/rss/urls/{$rssUrl->id}", [
        'url' => $newUrl,
    ]);

    $response->assertRedirect('/rss/urls');
    $response->assertSessionHas('success', 'RSS URL updated successfully.');

    $this->assertDatabaseHas('rss_urls', [
        'id' => $rssUrl->id, 
        'url' => $newUrl,
        'user_id' => $this->user->id,
    ]);
    $this->assertDatabaseMissing('rss_urls', [
        'id' => $rssUrl->id, 
        'url' => 'https://old-example.com/feed.xml',
    ]);
});

test('update validates required url field', function () {
    $rssUrl = RssUrl::factory()->forUser($this->user)->create();

    $response = $this->actingAs($this->user)->put("/rss/urls/{$rssUrl->id}", []);

    $response->assertSessionHasErrors(['url']);
    $response->assertStatus(302);
});

test('update validates url format', function () {
    $rssUrl = RssUrl::factory()->forUser($this->user)->create();

    $response = $this->actingAs($this->user)->put("/rss/urls/{$rssUrl->id}", [
        'url' => 'not-a-valid-url',
    ]);

    $response->assertSessionHasErrors(['url']);
    $response->assertStatus(302);
});

test('update validates unique url excluding current record for same user', function () {
    $rssUrl1 = RssUrl::factory()->forUser($this->user)->create(['url' => 'https://example1.com/feed.xml']);
    $rssUrl2 = RssUrl::factory()->forUser($this->user)->create(['url' => 'https://example2.com/feed.xml']);

    $response = $this->actingAs($this->user)->put("/rss/urls/{$rssUrl1->id}", [
        'url' => 'https://example2.com/feed.xml',
    ]);

    $response->assertSessionHasErrors(['url']);
    $response->assertStatus(302);
});

test('update allows same url for same record', function () {
    $rssUrl = RssUrl::factory()->forUser($this->user)->create(['url' => 'https://example.com/feed.xml']);

    $response = $this->actingAs($this->user)->put("/rss/urls/{$rssUrl->id}", [
        'url' => 'https://example.com/feed.xml',
    ]);

    $response->assertRedirect('/rss/urls');
    $response->assertSessionHas('success', 'RSS URL updated successfully.');
});

test('update allows same url for different users', function () {
    $otherUser = User::factory()->create();
    $rssUrl1 = RssUrl::factory()->forUser($this->user)->create(['url' => 'https://example1.com/feed.xml']);
    $rssUrl2 = RssUrl::factory()->forUser($otherUser)->create(['url' => 'https://example2.com/feed.xml']);

    $response = $this->actingAs($this->user)->put("/rss/urls/{$rssUrl1->id}", [
        'url' => 'https://example2.com/feed.xml',
    ]);

    $response->assertRedirect('/rss/urls');
    $response->assertSessionHas('success', 'RSS URL updated successfully.');
});

test('update returns 404 for other users rss url', function () {
    $otherUser = User::factory()->create();
    $rssUrl = RssUrl::factory()->forUser($otherUser)->create();

    $response = $this->actingAs($this->user)->put("/rss/urls/{$rssUrl->id}", [
        'url' => 'https://example.com/feed.xml',
    ]);

    $response->assertStatus(404);
});

test('update returns 404 for non-existent rss url', function () {
    $response = $this->actingAs($this->user)->put('/rss/urls/999', [
        'url' => 'https://example.com/feed.xml',
    ]);

    $response->assertStatus(404);
});

test('destroy deletes the specified rss url for authenticated user', function () {
    $rssUrl = RssUrl::factory()->forUser($this->user)->create();

    $response = $this->actingAs($this->user)->delete("/rss/urls/{$rssUrl->id}");

    $response->assertRedirect('/rss/urls');
    $response->assertSessionHas('success', 'RSS URL deleted successfully.');

    $this->assertDatabaseMissing('rss_urls', ['id' => $rssUrl->id]);
});

test('destroy returns 404 for other users rss url', function () {
    $otherUser = User::factory()->create();
    $rssUrl = RssUrl::factory()->forUser($otherUser)->create();

    $response = $this->actingAs($this->user)->delete("/rss/urls/{$rssUrl->id}");

    $response->assertStatus(404);
});

test('destroy returns 404 for non-existent rss url', function () {
    $response = $this->actingAs($this->user)->delete('/rss/urls/999');

    $response->assertStatus(404);
});

test('unauthenticated users cannot access rss urls', function () {
    $rssUrl = RssUrl::factory()->forUser($this->user)->create();

    // Test index
    $this->get('/rss/urls')->assertRedirect('/login');

    // Test create
    $this->get('/rss/urls/create')->assertRedirect('/login');

    // Test store
    $this->post('/rss/urls', ['url' => 'https://example.com/feed.xml'])->assertRedirect('/login');

    // Test show
    $this->get("/rss/urls/{$rssUrl->id}")->assertRedirect('/login');

    // Test edit
    $this->get("/rss/urls/{$rssUrl->id}/edit")->assertRedirect('/login');

    // Test update
    $this->put("/rss/urls/{$rssUrl->id}", ['url' => 'https://example.com/feed.xml'])->assertRedirect('/login');

    // Test destroy
    $this->delete("/rss/urls/{$rssUrl->id}")->assertRedirect('/login');
});
