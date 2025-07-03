@extends('layouts.app')

@section('title', 'RSS Posts')

@section('content')
    <x-page-header 
        title="RSS Posts" 
        subtitle="Browse and filter posts from your RSS feeds"
    />

    <!-- Filters -->
    <x-filter-card title="Search & Filter Posts" :clearUrl="route('rss.items.index')">
        <div class="col-md-4">
            <x-form-select 
                name="feed_id" 
                label="RSS Feed" 
                :options="$feeds->pluck('url', 'id')->prepend('All Feeds', '')"
                :selected="request('feed_id')"
                placeholder="All Feeds"
            />
        </div>
        <div class="col-md-3">
            <x-form-input 
                name="date" 
                label="Date" 
                type="date" 
                :value="request('date')"
            />
        </div>
        <div class="col-md-3">
            <x-form-input 
                name="title" 
                label="Title" 
                type="text" 
                :value="request('title')"
                placeholder="Search in titles..."
            />
        </div>
    </x-filter-card>

    @if($items->count() > 0)
        <div class="card shadow-sm border-0">
            <div class="card-header bg-light border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-newspaper me-2"></i>Posts ({{ $items->total() }} total)
                    </h6>
                    <small class="text-muted">Sorted by publish date (newest first)</small>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($items as $item)
                        <div class="list-group-item list-group-item-action border-0">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-2">
                                        <a href="{{ $item->link }}" target="_blank" class="text-decoration-none text-dark fw-semibold">
                                            {{ $item->title }}
                                            <i class="bi bi-box-arrow-up-right ms-1 text-muted" style="font-size: 0.8em;"></i>
                                        </a>
                                    </h6>
                                    <div class="d-flex flex-wrap gap-3 mb-2">
                                        <span class="badge bg-light text-dark border">
                                            <i class="bi bi-rss me-1"></i>
                                            {{ $item->source }}
                                        </span>
                                        @if($item->rssUrl)
                                            <span class="badge bg-info bg-opacity-10 text-info border border-info">
                                                <i class="bi bi-link-45deg me-1"></i>
                                                {{ Str::limit($item->rssUrl->url, 30) }}
                                            </span>
                                        @endif
                                        @if($item->publish_date)
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary">
                                                <i class="bi bi-calendar3 me-1"></i>
                                                {{ $item->publish_date->format('M d, Y H:i') }}
                                            </span>
                                        @endif
                                    </div>
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
        <x-empty-state 
            title="No posts found" 
            :description="request()->hasAny(['feed_id', 'date', 'title']) 
                ? 'Try adjusting your filters or clear all filters to see more posts.' 
                : 'No RSS posts have been fetched yet. Make sure you have RSS URLs configured and the fetcher service is running.'"
            :actionText="!request()->hasAny(['feed_id', 'date', 'title']) ? 'Manage RSS URLs' : null"
            :actionUrl="!request()->hasAny(['feed_id', 'date', 'title']) ? route('rss.urls.index') : null"
            icon="bi-newspaper"
        />
    @endif
@endsection 