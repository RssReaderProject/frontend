<?php

namespace App\Http\Controllers;

use App\Models\RssUrl;
use Illuminate\Http\Request;

class RssUrlController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rssUrls = RssUrl::all();
        return view('rss-urls.index', compact('rssUrls'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('rss-urls.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'url' => 'required|url|unique:rss_urls,url'
        ]);

        RssUrl::create($request->only('url'));

        return redirect()->route('rss-urls.index')
            ->with('success', 'RSS URL created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $rssUrl = RssUrl::findOrFail($id);
        return view('rss-urls.show', compact('rssUrl'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $rssUrl = RssUrl::findOrFail($id);
        return view('rss-urls.edit', compact('rssUrl'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'url' => 'required|url|unique:rss_urls,url,' . $id
        ]);

        $rssUrl = RssUrl::findOrFail($id);
        $rssUrl->update($request->only('url'));

        return redirect()->route('rss-urls.index')
            ->with('success', 'RSS URL updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $rssUrl = RssUrl::findOrFail($id);
        $rssUrl->delete();

        return redirect()->route('rss-urls.index')
            ->with('success', 'RSS URL deleted successfully.');
    }
}
