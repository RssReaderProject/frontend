@extends('layouts.app')

@section('title', 'Profile Settings')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Profile Information</h5>
                </div>
                <div class="card-body">
                    @if(session('status') === 'profile-updated')
                        <div class="alert alert-success">
                            Profile updated successfully.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('patch')

                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input 
                                id="name" 
                                type="text" 
                                name="name" 
                                value="{{ old('name', $user->name) }}" 
                                class="form-control @error('name') is-invalid @enderror" 
                                required 
                                autofocus 
                                autocomplete="name"
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input 
                                id="email" 
                                type="email" 
                                name="email" 
                                value="{{ old('email', $user->email) }}" 
                                class="form-control @error('email') is-invalid @enderror" 
                                required 
                                autocomplete="username"
                            >
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('rss.urls.index') }}" class="btn btn-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Delete Account</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Once your account is deleted, all of its resources and data will be permanently deleted.</p>

                    <form method="POST" action="{{ route('profile.destroy') }}" class="mt-3">
                        @csrf
                        @method('delete')

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input 
                                id="password" 
                                type="password" 
                                name="password" 
                                class="form-control @error('password', 'userDeletion') is-invalid @enderror" 
                                placeholder="Enter your password to confirm"
                            >
                            @error('password', 'userDeletion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete your account?')">
                            Delete Account
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection 