@extends('layouts.app')

@section('title', 'Password Settings')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Update Password</h5>
                </div>
                <div class="card-body">
                    @if(session('status') === 'password-updated')
                        <div class="alert alert-success">
                            Password updated successfully.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        @method('put')

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input 
                                id="current_password" 
                                type="password" 
                                name="current_password" 
                                class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" 
                                required 
                                autofocus 
                                autocomplete="current-password"
                            >
                            @error('current_password', 'updatePassword')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input 
                                id="password" 
                                type="password" 
                                name="password" 
                                class="form-control @error('password', 'updatePassword') is-invalid @enderror" 
                                required 
                                autocomplete="new-password"
                            >
                            @error('password', 'updatePassword')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm New Password</label>
                            <input 
                                id="password_confirmation" 
                                type="password" 
                                name="password_confirmation" 
                                class="form-control" 
                                required 
                                autocomplete="new-password"
                            >
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">Update Password</button>
                            <a href="{{ route('rss.urls.index') }}" class="btn btn-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection 