<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'RSS Reader')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg bg-body-tertiary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="{{ route('dashboard') }}">
                <i class="bi bi-rss me-2"></i>
                RSS Reader
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" aria-current="{{ request()->routeIs('dashboard') ? 'page' : '' }}" href="{{ route('dashboard') }}">
                            <i class="bi bi-house me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('rss.urls.*') ? 'active' : '' }}" aria-current="{{ request()->routeIs('rss.urls.*') ? 'page' : '' }}" href="{{ route('rss.urls.index') }}">
                            <i class="bi bi-link-45deg me-1"></i> RSS URLs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('rss.items.*') ? 'active' : '' }}" aria-current="{{ request()->routeIs('rss.items.*') ? 'page' : '' }}" href="{{ route('rss.items.index') }}">
                            <i class="bi bi-newspaper me-1"></i> Posts
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i>
                            {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li><h6 class="dropdown-header">Settings</h6></li>
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="bi bi-person me-2"></i> Profile
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('password.edit') }}">
                                <i class="bi bi-shield-lock me-2"></i> Password
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('appearance') }}">
                                <i class="bi bi-palette me-2"></i> Appearance
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container py-4">
        <!-- Alerts -->
        <x-alert type="success" />
        <x-alert type="error" />
        <x-alert type="warning" />
        <x-alert type="info" />

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-top mt-5">
        <div class="container py-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-muted mb-0 small">
                        &copy; {{ date('Y') }} RSS Reader. Built with Laravel & Bootstrap 5.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-0 small">
                        <i class="bi bi-heart-fill text-danger"></i> Made with love
                    </p>
                </div>
            </div>
        </div>
    </footer>
</body>
</html> 