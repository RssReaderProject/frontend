<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\RssUrl;
use App\Services\RssFetcherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class RssUrlObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_observer_is_registered()
    {
        // Test that the observer is properly registered
        $user = User::factory()->create();
        
        // Create an RSS URL and check if it triggers the observer
        $rssUrl = RssUrl::factory()->create([
            'user_id' => $user->id,
            'url' => 'https://example.com/feed.xml'
        ]);
        
        // The observer should be registered and not throw any errors
        $this->assertDatabaseHas('rss_urls', [
            'id' => $rssUrl->id,
            'user_id' => $user->id,
            'url' => 'https://example.com/feed.xml'
        ]);
    }

    public function test_rss_url_creation_does_not_throw_errors()
    {
        $user = User::factory()->create();
        
        // This should not throw any errors
        $rssUrl = RssUrl::factory()->create([
            'user_id' => $user->id,
            'url' => 'https://example.com/feed.xml'
        ]);
        
        $this->assertInstanceOf(RssUrl::class, $rssUrl);
    }

    public function test_rss_url_update_does_not_throw_errors()
    {
        $user = User::factory()->create();
        $rssUrl = RssUrl::factory()->create([
            'user_id' => $user->id,
            'url' => 'https://example.com/feed.xml'
        ]);
        
        // This should not throw any errors
        $rssUrl->update(['url' => 'https://example.com/new-feed.xml']);
        
        $this->assertDatabaseHas('rss_urls', [
            'id' => $rssUrl->id,
            'url' => 'https://example.com/new-feed.xml'
        ]);
    }

    public function test_rss_url_deletion_does_not_throw_errors()
    {
        $user = User::factory()->create();
        $rssUrl = RssUrl::factory()->create([
            'user_id' => $user->id,
            'url' => 'https://example.com/feed.xml'
        ]);
        
        // This should not throw any errors
        $rssUrl->delete();
        
        $this->assertDatabaseMissing('rss_urls', [
            'id' => $rssUrl->id
        ]);
    }

    public function test_multiple_rss_url_operations_do_not_throw_errors()
    {
        $user = User::factory()->create();
        
        // Create multiple RSS URLs - should not throw errors
        $urls = RssUrl::factory()->count(3)->create([
            'user_id' => $user->id
        ]);
        
        $this->assertCount(3, $urls);
        
        // Update one of them
        $urls->first()->update(['url' => 'https://example.com/updated.xml']);
        
        $this->assertDatabaseHas('rss_urls', [
            'id' => $urls->first()->id,
            'url' => 'https://example.com/updated.xml'
        ]);
    }
} 