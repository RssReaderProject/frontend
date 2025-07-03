@extends('layouts.app')

@section('title', 'Appearance Settings')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Appearance Settings</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">This feature is not yet implemented. The application currently uses Bootstrap's default theme.</p>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('rss.urls.index') }}" class="btn btn-secondary">Back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 