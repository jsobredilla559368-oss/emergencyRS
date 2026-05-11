<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Reporter — EmergencyRS')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    {{-- Load the reporter-specific bright CSS for public-facing pages --}}
    <link rel="stylesheet" href="{{ asset('css/reporter.css') }}">
    <script src='https://api.mapbox.com/mapbox-gl-js/v3.1.2/mapbox-gl.js'></script>
    <link href='https://api.mapbox.com/mapbox-gl-js/v3.1.2/mapbox-gl.css' rel='stylesheet' />
    @stack('styles')
</head>
<body>
    {{-- Simple top nav for public pages --}}
    <nav class="reporter-nav">
        <div class="reporter-nav-inner">
            <a href="{{ route('reporter.form') }}" class="reporter-brand">
                <div class="reporter-brand-icon">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="none">
                        <path d="M10 1.5L2.5 5V10C2.5 14 5.8 17.8 10 18.5C14.2 17.8 17.5 14 17.5 10V5L10 1.5Z"
                            fill="rgba(255,255,255,0.15)" stroke="white" stroke-width="0.75" />
                        <path d="M10 6.5V13.5M6.5 10H13.5" stroke="white" stroke-width="1.75" stroke-linecap="round" />
                    </svg>
                </div>
                <span>EmergencyRS</span>
            </a>
            <div class="reporter-nav-actions">
                @auth
                    <a href="{{ auth()->user()->role === 'reporter' ? route('reporter.dashboard') : route('dashboard') }}" class="reporter-nav-link">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="reporter-nav-link" style="background:none;border:none;cursor:pointer;font-size:inherit;">Sign out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="reporter-nav-link">Sign In</a>
                    <a href="{{ route('reporter.track') }}" class="reporter-nav-link">Track Report</a>
                @endauth
            </div>
        </div>
    </nav>

    <main id="main-content" style="flex: 1;">
        @if(session('success'))
            <div style="background: #D1FAE5; border: 1px solid #6EE7B7; color: #065F46; padding: 12px 20px; font-size: 14px; font-weight: 600; border-radius: 10px; margin: 16px auto; max-width: 680px;">
                ✓ {{ session('success') }}
            </div>
        @endif
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
