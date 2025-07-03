@extends('layouts.app')

@section('title', 'RSS URL Details')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('rss-urls.index') }}" class="btn btn-outline-secondary me-3">
                    <i class="bi bi-arrow-left"></i> Back to RSS URLs
                </a>
                <h1 class="h2 mb-0">RSS URL Details</h1>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-4">URL Information</h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">ID</label>
                            <p class="form-control-plaintext">{{ $rssUrl->id }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Created At</label>
                            <p class="form-control-plaintext">{{ $rssUrl->created_at->format('F d, Y \a\t g:i A') }}</p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">RSS URL</label>
                        <p class="form-control-plaintext">
                            <a href="{{ $rssUrl->url }}" target="_blank" class="text-decoration-none">
                                {{ $rssUrl->url }}
                            </a>
                        </p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">Updated At</label>
                        <p class="form-control-plaintext">{{ $rssUrl->updated_at->format('F d, Y \a\t g:i A') }}</p>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('rss-urls.index') }}" class="btn btn-secondary">
                            Back to List
                        </a>
                        <a href="{{ route('rss-urls.edit', $rssUrl) }}" class="btn btn-primary">
                            Edit
                        </a>
                        <form action="{{ route('rss-urls.destroy', $rssUrl) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="btn btn-danger"
                                    onclick="return confirm('Are you sure you want to delete this RSS URL?')">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 