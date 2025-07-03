@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm mt-5">
                <div class="card-body p-4">
                    <h1 class="h4 mb-3 text-center">Reset Password</h1>
                    <p class="mb-4 text-center text-muted">Enter your new password below</p>

                    <form method="POST" action="{{ route('password.store') }}">
                        @csrf

                        <!-- Password Reset Token -->
                        <input type="hidden" name="token" value="{{ $request->route('token') }}">

                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input 
                                id="email" 
                                type="email" 
                                name="email" 
                                value="{{ old('email', $request->email) }}" 
                                class="form-control @error('email') is-invalid @enderror" 
                                required 
                                autofocus 
                                autocomplete="email" 
                                placeholder="email@example.com"
                            >
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input 
                                id="password" 
                                type="password" 
                                name="password" 
                                class="form-control @error('password') is-invalid @enderror" 
                                required 
                                autocomplete="new-password" 
                                placeholder="New password"
                            >
                            @error('password')
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
                                placeholder="Confirm new password"
                            >
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                    </form>

                    <div class="text-center mt-4">
                        <p class="mb-0">Remember your password? <a href="{{ route('login') }}">Log in</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 