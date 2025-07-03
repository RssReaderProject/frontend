@extends('layouts.app')

@section('title', 'RSS Posts')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">RSS Posts</h1>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('rss.items.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="feed_id" class="form-label">RSS Feed</label>
                    <select name="feed_id" id="feed_id" class="form-select">
                        <option value="">All Feeds</option>
                        @foreach($feeds as $feed)
                            <option value="{{ $feed->id }}" {{ request('feed_id') == $feed->id ? 'selected' : '' }}>
                                {{ $feed->url }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="date" class="form-label">Date</label>
                    <input type="date" name="date" id="date" class="form-control" value="{{ request('date') }}">
                </div>
                <div class="col-md-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" name="title" id="title" class="form-control" value="{{ request('title') }}" placeholder="Search in titles...">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="d-grid gap-2 w-100">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Filter
                        </button>
                        <a href="{{ route('rss.items.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($items->count() > 0)
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Posts ({{ $items->total() }} total)</span>
                    <small class="text-muted">Sorted by publish date (newest first)</small>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($items as $item)
                        <div class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">
                                        <a href="{{ $item->link }}" target="_blank" class="text-decoration-none text-dark fw-semibold">
                                            {{ $item->title }}
                                            <i class="bi bi-box-arrow-up-right ms-1 text-muted" style="font-size: 0.8em;"></i>
                                        </a>
                                    </h6>
                                    <p class="mb-1 text-muted small">
                                        <i class="bi bi-rss me-1"></i>
                                        {{ $item->source }}
                                        @if($item->rssUrl)
                                            <span class="ms-2">
                                                <i class="bi bi-link-45deg me-1"></i>
                                                <span class="text-info">{{ $item->rssUrl->url }}</span>
                                            </span>
                                        @endif
                                        @if($item->publish_date)
                                            <span class="ms-2">
                                                <i class="bi bi-calendar3 me-1"></i>
                                                {{ $item->publish_date->format('M d, Y H:i') }}
                                            </span>
                                        @endif
                                    </p>
                                    @if($item->description)
                                        <p class="mb-0 text-muted small">
                                            {{ Str::limit(strip_tags($item->description), 200) }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Pagination -->
        @if($items->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $items->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        @endif
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <h5 class="card-title text-muted">No posts found</h5>
                <p class="card-text text-muted">
                    @if(request()->hasAny(['feed_id', 'date', 'title']))
                        Try adjusting your filters or 
                        <a href="{{ route('rss.items.index') }}" class="text-decoration-none">clear all filters</a>.
                    @else
                        No RSS posts have been fetched yet. Make sure you have RSS URLs configured and the fetcher service is running.
                    @endif
                </p>
                @if(!request()->hasAny(['feed_id', 'date', 'title']))
                    <a href="{{ route('rss.urls.index') }}" class="btn btn-primary">
                        <i class="bi bi-rss"></i> Manage RSS URLs
                    </a>
                @endif
            </div>
        </div>
    @endif
@endsection 