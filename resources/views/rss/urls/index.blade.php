@extends('layouts.app')

@section('title', 'RSS URLs')

@section('content')
    <x-page-header title="RSS URLs" subtitle="Manage your RSS feed subscriptions">
        <a href="{{ route('rss.urls.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Add New RSS URL
        </a>
    </x-page-header>

    @if($rssUrls->count() > 0)
        <div class="card shadow-sm border-0">
            <div class="card-header bg-light border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-list-ul me-2"></i>Your RSS Feeds ({{ $rssUrls->count() }})
                    </h6>
                    <small class="text-muted">Click on a URL to open it in a new tab</small>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="border-0">ID</th>
                                <th scope="col" class="border-0">URL</th>
                                <th scope="col" class="border-0">Status</th>
                                <th scope="col" class="border-0">Failures</th>
                                <th scope="col" class="border-0">Created</th>
                                <th scope="col" class="border-0 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rssUrls as $rssUrl)
                                <tr>
                                    <td class="align-middle">
                                        <span class="badge bg-secondary">{{ $rssUrl->id }}</span>
                                    </td>
                                    <td class="align-middle">
                                        <a href="{{ $rssUrl->url }}" target="_blank" class="text-decoration-none text-primary fw-medium">
                                            {{ Str::limit($rssUrl->url, 50) }}
                                            <i class="bi bi-box-arrow-up-right ms-1" style="font-size: 0.8em;"></i>
                                        </a>
                                    </td>
                                    <td class="align-middle">
                                        @if($rssUrl->is_disabled)
                                            <x-status-badge status="disabled" />
                                        @elseif($rssUrl->consecutive_failures >= 3)
                                            <x-status-badge status="cooldown" />
                                        @elseif($rssUrl->consecutive_failures > 0)
                                            <x-status-badge status="failures" :failures="$rssUrl->consecutive_failures" :lastFailure="$rssUrl->last_failure_at" />
                                        @else
                                            <x-status-badge status="active" />
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        @if($rssUrl->consecutive_failures > 0)
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                    <div class="progress-bar bg-warning" style="width: {{ ($rssUrl->consecutive_failures / 10) * 100 }}%"></div>
                                                </div>
                                                <small class="text-muted">{{ $rssUrl->consecutive_failures }}/10</small>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        <small class="text-muted">
                                            {{ $rssUrl->created_at->format('M d, Y') }}
                                            <br>
                                            {{ $rssUrl->created_at->format('H:i') }}
                                        </small>
                                    </td>
                                    <td class="align-middle text-end">
                                        <x-action-buttons 
                                            :viewUrl="route('rss.urls.show', $rssUrl)"
                                            :editUrl="route('rss.urls.edit', $rssUrl)"
                                            :deleteUrl="route('rss.urls.destroy', $rssUrl)"
                                            :reEnableUrl="route('rss.urls.re-enable', $rssUrl)"
                                            :showReEnable="$rssUrl->is_disabled"
                                        />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <x-empty-state 
            title="No RSS URLs found" 
            description="Get started by adding your first RSS URL to begin collecting posts from your favorite feeds."
            actionText="Add Your First RSS URL"
            actionUrl="{{ route('rss.urls.create') }}"
            icon="bi-rss"
        />
    @endif
@endsection 