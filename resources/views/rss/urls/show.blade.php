@extends('layouts.app')

@section('title', 'RSS URL Details')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <x-page-header title="RSS URL Details" subtitle="View detailed information about this RSS feed">
                <a href="{{ route('rss.urls.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to RSS URLs
                </a>
            </x-page-header>

            <x-form-card title="URL Information" subtitle="Feed details and status">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted">ID</label>
                        <p class="mb-0">
                            <span class="badge bg-secondary">{{ $rssUrl->id }}</span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted">Status</label>
                        <p class="mb-0">
                            @if($rssUrl->is_disabled)
                                <x-status-badge status="disabled" />
                            @elseif($rssUrl->consecutive_failures >= 3)
                                <x-status-badge status="cooldown" />
                            @elseif($rssUrl->consecutive_failures > 0)
                                <x-status-badge status="failures" :failures="$rssUrl->consecutive_failures" :lastFailure="$rssUrl->last_failure_at" />
                            @else
                                <x-status-badge status="active" />
                            @endif
                        </p>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold text-muted">RSS URL</label>
                    <p class="mb-0">
                        <a href="{{ $rssUrl->url }}" target="_blank" class="text-decoration-none text-primary fw-medium">
                            {{ $rssUrl->url }}
                            <i class="bi bi-box-arrow-up-right ms-1" style="font-size: 0.8em;"></i>
                        </a>
                    </p>
                </div>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted">Created</label>
                        <p class="mb-0 text-muted">
                            <i class="bi bi-calendar-plus me-1"></i>
                            {{ $rssUrl->created_at->format('F d, Y \a\t g:i A') }}
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-muted">Last Updated</label>
                        <p class="mb-0 text-muted">
                            <i class="bi bi-calendar-check me-1"></i>
                            {{ $rssUrl->updated_at->format('F d, Y \a\t g:i A') }}
                        </p>
                    </div>
                </div>

                @if($rssUrl->consecutive_failures > 0)
                    <div class="mt-3">
                        <label class="form-label fw-semibold text-muted">Failure Tracking</label>
                        <div class="d-flex align-items-center">
                            <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                <div class="progress-bar bg-warning" style="width: {{ ($rssUrl->consecutive_failures / 10) * 100 }}%"></div>
                            </div>
                            <small class="text-muted">{{ $rssUrl->consecutive_failures }}/10 failures</small>
                        </div>
                        @if($rssUrl->last_failure_at)
                            <small class="text-muted">
                                Last failure: {{ $rssUrl->last_failure_at->diffForHumans() }}
                            </small>
                        @endif
                    </div>
                @endif

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('rss.urls.index') }}" class="btn btn-secondary">
                        <i class="bi bi-list me-1"></i> Back to List
                    </a>
                    <a href="{{ route('rss.urls.edit', $rssUrl) }}" class="btn btn-primary">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                    <form action="{{ route('rss.urls.destroy', $rssUrl) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="btn btn-danger"
                                onclick="return confirm('Are you sure you want to delete this RSS URL?')">
                            <i class="bi bi-trash me-1"></i> Delete
                        </button>
                    </form>
                </div>
            </x-form-card>
        </div>
    </div>
@endsection 