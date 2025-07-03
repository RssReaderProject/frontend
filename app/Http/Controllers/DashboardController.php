<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $stats = [
            'total_feeds' => $user->rssUrls()->count(),
            'active_feeds' => $user->rssUrls()->whereNull('disabled_at')->count(),
            'total_posts' => $user->rssItems()->count(),
            'today_posts' => $user->rssItems()->whereDate('publish_date', today())->count(),
        ];

        $recentItems = $user->rssItems()
            ->with('rssUrl')
            ->latest('publish_date')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact('stats', 'recentItems'));
    }
}
