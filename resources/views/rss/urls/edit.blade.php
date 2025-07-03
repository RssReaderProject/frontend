@extends('layouts.app')

@section('title', 'Edit RSS URL')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('rss.urls.index') }}" class="btn btn-outline-secondary me-3">
                    <i class="bi bi-arrow-left"></i> Back to RSS URLs
                </a>
                <h1 class="h2 mb-0">Edit RSS URL</h1>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('rss.urls.update', $rssUrl) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="url" class="form-label">RSS URL</label>
                            <input type="url" 
                                   class="form-control @error('url') is-invalid @enderror"
                                   name="url" 
                                   id="url" 
                                   value="{{ old('url', $rssUrl->url) }}"
                                   placeholder="https://example.com/feed.xml"
                                   required>
                            @error('url')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('rss.urls.index') }}" class="btn btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Update RSS URL
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection 