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

        $query = RssItem::where('user_id', $user->id);

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // Filter by title
        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        $items = $query->orderByDesc('publish_date')->paginate(20);

        return view('rss.items.index', [
            'items' => $items,
            'filters' => $request->only(['date', 'title'])
        ]);
    }
} 