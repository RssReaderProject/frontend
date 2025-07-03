@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">Posts Preview</h2>
            
            <div class="card">
                <div class="card-body">
                    <!-- Filters -->
                    <div class="mb-4">
                        <form method="GET" action="{{ route('rss.items.index') }}">
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="title" class="form-label">Filter by Title</label>
                                    <input type="text" 
                                           name="title" 
                                           id="title" 
                                           value="{{ $filters['title'] ?? '' }}"
                                           class="form-control"
                                           placeholder="Search titles...">
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="date" class="form-label">Filter by Date</label>
                                    <input type="date" 
                                           name="date" 
                                           id="date" 
                                           value="{{ $filters['date'] ?? '' }}"
                                           class="form-control">
                                </div>
                                
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        Filter
                                    </button>
                                    @if(!empty($filters))
                                        <a href="{{ route('rss.items.index') }}" class="btn btn-secondary">
                                            Clear
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Results count -->
                    <div class="mb-3">
                        <p class="text-muted">
                            Showing {{ $items->firstItem() ?? 0 }} to {{ $items->lastItem() ?? 0 }} of {{ $items->total() }} posts
                        </p>
                    </div>

                    <!-- Posts list -->
                    @if($items->count() > 0)
                        <div class="list-group">
                            @foreach($items as $item)
                                <a href="{{ $item->link }}" 
                                   target="_blank" 
                                   rel="noopener noreferrer"
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1">{{ $item->title }}</h5>
                                    </div>
                                    <div class="d-flex align-items-center text-muted small">
                                        <span class="me-3">
                                            <i class="bi bi-calendar me-1"></i>
                                            {{ $item->publish_date ? $item->publish_date->format('M j, Y') : 'No date' }}
                                        </span>
                                        <span>
                                            <i class="bi bi-link-45deg me-1"></i>
                                            {{ $item->source }}
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4">
                            {{ $items->appends($filters)->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-file-text display-1 text-muted"></i>
                            <h3 class="mt-3">No posts found</h3>
                            <p class="text-muted">
                                @if(!empty($filters))
                                    Try adjusting your filters or 
                                    <a href="{{ route('rss.items.index') }}" class="text-decoration-none">view all posts</a>.
                                @else
                                    No posts have been fetched yet. Add some RSS feeds to get started.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 