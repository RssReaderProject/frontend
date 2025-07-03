@extends('layouts.app')

@section('title', 'Forgot Password')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm mt-5">
                <div class="card-body p-4">
                    <h1 class="h4 mb-3 text-center">Forgot Password</h1>
                    <p class="mb-4 text-center text-muted">Enter your email address and we'll send you a link to reset your password</p>

                    @if(session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input 
                                id="email" 
                                type="email" 
                                name="email" 
                                value="{{ old('email') }}" 
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

                        <button type="submit" class="btn btn-primary w-100">Send Password Reset Link</button>
                    </form>

                    <div class="text-center mt-4">
                        <p class="mb-0">Remember your password? <a href="{{ route('login') }}">Log in</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 