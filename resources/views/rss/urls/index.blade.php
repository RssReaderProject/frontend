@extends('layouts.app')

@section('title', 'RSS URLs')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">RSS URLs</h1>
        <a href="{{ route('rss.urls.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> Add New RSS URL
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($rssUrls->count() > 0)
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">URL</th>
                                <th scope="col">Created</th>
                                <th scope="col" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rssUrls as $rssUrl)
                                <tr>
                                    <td>{{ $rssUrl->id }}</td>
                                    <td>
                                        <a href="{{ $rssUrl->url }}" target="_blank" class="text-decoration-none">
                                            {{ $rssUrl->url }}
                                        </a>
                                    </td>
                                    <td>{{ $rssUrl->created_at->format('M d, Y H:i') }}</td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('rss.urls.show', $rssUrl) }}" class="btn btn-sm btn-outline-primary">View</a>
                                            <a href="{{ route('rss.urls.edit', $rssUrl) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                            <form action="{{ route('rss.urls.destroy', $rssUrl) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this RSS URL?')">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <h5 class="card-title text-muted">No RSS URLs found</h5>
                <p class="card-text text-muted">Get started by adding your first RSS URL.</p>
                <a href="{{ route('rss.urls.create') }}" class="btn btn-primary">
                    Add Your First RSS URL
                </a>
            </div>
        </div>
    @endif
@endsection 