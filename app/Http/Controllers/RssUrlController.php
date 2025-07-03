<?php

namespace App\Http\Controllers;

use App\Models\RssUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RssUrlController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rssUrls = RssUrl::forUser(Auth::user());

        return view('rss.urls.index', compact('rssUrls'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('rss.urls.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'url' => 'required|url|unique:rss_urls,url,NULL,id,user_id,'.Auth::id(),
        ]);

        Auth::user()->rssUrls()->create($request->only('url'));

        return redirect()->route('rss.urls.index')
            ->with('success', 'RSS URL created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $rssUrl = RssUrl::findByUser(Auth::user(), $id);
        if (! $rssUrl) {
            abort(404);
        }

        return view('rss.urls.show', compact('rssUrl'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $rssUrl = RssUrl::findByUser(Auth::user(), $id);
        if (! $rssUrl) {
            abort(404);
        }

        return view('rss.urls.edit', compact('rssUrl'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'url' => 'required|url|unique:rss_urls,url,'.$id.',id,user_id,'.Auth::id(),
        ]);

        $rssUrl = RssUrl::findByUser(Auth::user(), $id);
        if (! $rssUrl) {
            abort(404);
        }
        $rssUrl->update($request->only('url'));

        return redirect()->route('rss.urls.index')
            ->with('success', 'RSS URL updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $rssUrl = RssUrl::findByUser(Auth::user(), $id);
        if (! $rssUrl) {
            abort(404);
        }
        $rssUrl->delete();

        return redirect()->route('rss.urls.index')
            ->with('success', 'RSS URL deleted successfully.');
    }

    /**
     * Re-enable a disabled RSS URL.
     */
    public function reEnable(string $id)
    {
        $rssUrl = RssUrl::findByUser(Auth::user(), $id);
        if (! $rssUrl) {
            abort(404);
        }

        if (! $rssUrl->is_disabled) {
            return redirect()->route('rss.urls.index')
                ->with('info', 'RSS URL is already active.');
        }

        $rssUrl->reEnable();

        return redirect()->route('rss.urls.index')
            ->with('success', 'RSS URL has been re-enabled successfully.');
    }
}
