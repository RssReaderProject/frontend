@extends('layouts.app')

@section('title', 'Add RSS URL')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <x-page-header title="Add RSS URL" subtitle="Add a new RSS feed to your collection">
                <a href="{{ route('rss.urls.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to RSS URLs
                </a>
            </x-page-header>

            <x-form-card>
                <form action="{{ route('rss.urls.store') }}" method="POST">
                    @csrf
                    
                    <x-form-input 
                        name="url" 
                        label="RSS URL" 
                        type="url" 
                        placeholder="https://example.com/feed.xml"
                        required
                    />

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('rss.urls.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i> Add RSS URL
                        </button>
                    </div>
                </form>
            </x-form-card>
        </div>
    </div>
@endsection 