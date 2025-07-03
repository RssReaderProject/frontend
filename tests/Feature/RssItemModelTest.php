<?php

use App\Models\User;
use App\Models\RssUrl;
use App\Models\RssItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('RssItem Model', function () {
    it('can be created with rss_url_id', function () {
        $user = User::factory()->create();
        $rssUrl = RssUrl::factory()->create(['user_id' => $user->id]);
        
        $item = RssItem::factory()->create([
            'user_id' => $user->id,
            'rss_url_id' => $rssUrl->id,
        ]);

        expect($item->rss_url_id)->toBe($rssUrl->id);
        expect($item->rssUrl)->toBeInstanceOf(RssUrl::class);
        expect($item->rssUrl->id)->toBe($rssUrl->id);
    });

    it('can be created without rss_url_id', function () {
        $user = User::factory()->create();
        
        $item = RssItem::factory()->create([
            'user_id' => $user->id,
            'rss_url_id' => null,
        ]);

        expect($item->rss_url_id)->toBeNull();
        expect($item->rssUrl)->toBeNull();
    });

    it('belongs to a user', function () {
        $user = User::factory()->create();
        $item = RssItem::factory()->create(['user_id' => $user->id]);

        expect($item->user)->toBeInstanceOf(User::class);
        expect($item->user->id)->toBe($user->id);
    });

    it('can access rss items through rss url relationship', function () {
        $user = User::factory()->create();
        $rssUrl = RssUrl::factory()->create(['user_id' => $user->id]);
        $item = RssItem::factory()->create([
            'user_id' => $user->id,
            'rss_url_id' => $rssUrl->id,
        ]);

        expect($rssUrl->rssItems)->toHaveCount(1);
        expect($rssUrl->rssItems->first()->id)->toBe($item->id);
    });

    it('cascades delete when rss url is deleted', function () {
        $user = User::factory()->create();
        $rssUrl = RssUrl::factory()->create(['user_id' => $user->id]);
        $item = RssItem::factory()->create([
            'user_id' => $user->id,
            'rss_url_id' => $rssUrl->id,
        ]);

        // Verify item exists
        expect(RssItem::find($item->id))->not->toBeNull();

        // Delete the RSS URL
        $rssUrl->delete();

        // Verify item is also deleted due to cascade
        expect(RssItem::find($item->id))->toBeNull();
    });

    it('keeps items when rss url is null', function () {
        $user = User::factory()->create();
        $item = RssItem::factory()->create([
            'user_id' => $user->id,
            'rss_url_id' => null,
        ]);

        // Verify item exists
        expect(RssItem::find($item->id))->not->toBeNull();

        // Item should still exist even if no RSS URL
        expect($item->fresh()->rss_url_id)->toBeNull();
    });

    it('can filter by rss_url_id', function () {
        $user = User::factory()->create();
        $rssUrl1 = RssUrl::factory()->create(['user_id' => $user->id]);
        $rssUrl2 = RssUrl::factory()->create(['user_id' => $user->id]);
        
        $item1 = RssItem::factory()->create([
            'user_id' => $user->id,
            'rss_url_id' => $rssUrl1->id,
        ]);
        $item2 = RssItem::factory()->create([
            'user_id' => $user->id,
            'rss_url_id' => $rssUrl2->id,
        ]);

        $filteredItems = RssItem::where('rss_url_id', $rssUrl1->id)->get();
        
        expect($filteredItems)->toHaveCount(1);
        expect($filteredItems->first()->id)->toBe($item1->id);
    });

    it('can eager load rss url relationship', function () {
        $user = User::factory()->create();
        $rssUrl = RssUrl::factory()->create(['user_id' => $user->id]);
        RssItem::factory()->create([
            'user_id' => $user->id,
            'rss_url_id' => $rssUrl->id,
        ]);

        $item = RssItem::with('rssUrl')->where('user_id', $user->id)->first();
        
        expect($item->rssUrl)->toBeInstanceOf(RssUrl::class);
        expect($item->rssUrl->id)->toBe($rssUrl->id);
    });
}); 