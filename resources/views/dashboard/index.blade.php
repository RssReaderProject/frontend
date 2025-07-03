@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <x-page-header 
        title="Dashboard" 
        subtitle="Welcome back, {{ Auth::user()->name }}! Here's an overview of your RSS feeds."
    />

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-rss text-primary fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-1">Total Feeds</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['total_feeds'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-check-circle text-success fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-1">Active Feeds</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['active_feeds'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-newspaper text-info fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-1">Total Posts</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['total_posts'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-calendar3 text-warning fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-1">Today's Posts</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['today_posts'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-light border-0">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-lightning me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('rss.urls.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Add New RSS Feed
                        </a>
                        <a href="{{ route('rss.items.index') }}" class="btn btn-outline-primary">
                            <i class="bi bi-newspaper me-2"></i>View All Posts
                        </a>
                        <a href="{{ route('rss.urls.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-gear me-2"></i>Manage RSS Feeds
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-light border-0">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-clock-history me-2"></i>Recent Activity
                    </h6>
                </div>
                <div class="card-body">
                    @if($recentItems->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentItems as $item)
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex align-items-start">
                                        <div class="flex-shrink-0">
                                            <i class="bi bi-newspaper text-muted"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1">
                                                <a href="{{ $item->link }}" target="_blank" class="text-decoration-none">
                                                    {{ Str::limit($item->title, 50) }}
                                                </a>
                                            </h6>
                                            <small class="text-muted">
                                                {{ $item->publish_date?->diffForHumans() ?? 'Unknown date' }}
                                                @if($item->rssUrl)
                                                    • {{ Str::limit($item->rssUrl->url, 30) }}
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">No recent posts found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection 