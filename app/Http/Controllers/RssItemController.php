<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RssItem;
use App\Models\RssUrl;

class RssItemController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = RssItem::with('rssUrl')->where('user_id', $user->id);

        // Filter by RSS feed
        if ($request->filled('feed_id')) {
            $feed = RssUrl::where('id', $request->feed_id)->where('user_id', $user->id)->first();
            if ($feed) {
                $query->where('rss_url_id', $feed->id);
            }
        }

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('publish_date', $request->date);
        }

        // Filter by title
        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        $items = $query->orderByDesc('publish_date')->paginate(20);
        $feeds = RssUrl::where('user_id', $user->id)->get();

        return view('rss.items.index', [
            'items' => $items,
            'feeds' => $feeds,
            'filters' => $request->only(['feed_id', 'date', 'title'])
        ]);
    }
} 