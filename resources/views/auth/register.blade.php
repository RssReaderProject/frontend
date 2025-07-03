@extends('layouts.app')

@section('title', 'Register')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm mt-5">
                <div class="card-body p-4">
                    <h1 class="h4 mb-3 text-center">Register</h1>
                    <p class="mb-4 text-center text-muted">Create a new account to get started</p>

                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input 
                                id="name" 
                                type="text" 
                                name="name" 
                                value="{{ old('name') }}" 
                                class="form-control @error('name') is-invalid @enderror" 
                                required 
                                autofocus 
                                autocomplete="name" 
                                placeholder="Your name"
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input 
                                id="email" 
                                type="email" 
                                name="email" 
                                value="{{ old('email') }}" 
                                class="form-control @error('email') is-invalid @enderror" 
                                required 
                                autocomplete="email" 
                                placeholder="email@example.com"
                            >
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input 
                                id="password" 
                                type="password" 
                                name="password" 
                                class="form-control @error('password') is-invalid @enderror" 
                                required 
                                autocomplete="new-password" 
                                placeholder="Password"
                            >
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input 
                                id="password_confirmation" 
                                type="password" 
                                name="password_confirmation" 
                                class="form-control" 
                                required 
                                autocomplete="new-password" 
                                placeholder="Confirm password"
                            >
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Register</button>
                    </form>

                    <div class="text-center mt-4">
                        <p class="mb-0">Already have an account? <a href="{{ route('login') }}">Log in</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 