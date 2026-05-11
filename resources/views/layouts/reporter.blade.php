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
    <link rel="stylesheet" href="{{ asset('css/responder.css') }}">
    <script src='https://api.mapbox.com/mapbox-gl-js/v3.1.2/mapbox-gl.js'></script>
    <link href='https://api.mapbox.com/mapbox-gl-js/v3.1.2/mapbox-gl.css' rel='stylesheet' />
    @stack('styles')
</head>

<body>

    <a href="#main-content" class="skip-link">Skip to main content</a>

    {{-- ═══ SIDEBAR ═══ --}}
    <aside class="sidebar" role="navigation" aria-label="Main Navigation">
        <div class="sidebar-logo">
            <div class="sidebar-logo-icon">
                <svg width="16" height="16" viewBox="0 0 20 20" fill="none">
                    <path d="M10 1.5L2.5 5V10C2.5 14 5.8 17.8 10 18.5C14.2 17.8 17.5 14 17.5 10V5L10 1.5Z"
                        fill="rgba(255,255,255,0.15)" stroke="white" stroke-width="0.75" />
                    <path d="M10 6.5V13.5M6.5 10H13.5" stroke="white" stroke-width="1.75" stroke-linecap="round" />
                </svg>
            </div>
            <div>
                <div class="sidebar-logo-text">EmergencyRS</div>
            </div>
            <span class="sidebar-logo-badge ms-auto">Reporter</span>
        </div>

        <div class="sidebar-section">
            <p class="sidebar-section-label">Menu</p>

            <a href="{{ route('reporter.dashboard') }}"
                class="nav-item {{ request()->routeIs('reporter.dashboard') ? 'active' : '' }}"
                {{ request()->routeIs('reporter.dashboard') ? 'aria-current="page"' : '' }}>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="3" width="7" height="7" />
                    <rect x="14" y="3" width="7" height="7" />
                    <rect x="3" y="14" width="7" height="7" />
                    <rect x="14" y="14" width="7" height="7" />
                </svg>
                Dashboard
            </a>

            <a href="{{ route('reporter.form') }}" class="nav-item">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 4v16m8-8H4" />
                </svg>
                Report Emergency
            </a>

            <a href="{{ route('reporter.track') }}"
                class="nav-item {{ request()->routeIs('reporter.track') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                Track Report
            </a>

            <a href="{{ route('notifications.index') }}"
                class="nav-item {{ request()->routeIs('notifications.index') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" aria-hidden="true">
                    <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 00-9.33-5" />
                    <path d="M15 17H9m6 0a3 3 0 01-6 0" />
                </svg>
                Notifications
            </a>
        </div>

        <div class="sidebar-bottom">
            <div class="sidebar-user">
                <div class="sidebar-avatar">
                    {{ strtoupper(substr(auth()->user()->name ?? 'R', 0, 1)) }}
                </div>
                <div>
                    <div class="sidebar-user-name">{{ auth()->user()->name ?? 'Reporter' }}</div>
                    <div class="sidebar-user-role">Community Reporter</div>
                </div>
            </div>
            <a href="{{ route('profile.edit') }}" class="nav-item {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                My Profile
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-item logout"
                    style="width:100%;background:none;border:none;cursor:pointer;text-align:left;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" aria-hidden="true">
                        <path
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1" />
                    </svg>
                    Sign out
                </button>
            </form>
        </div>
    </aside>

    <div class="sidebar-overlay" onclick="document.body.classList.remove('sidebar-open')"></div>

    {{-- ═══ MAIN ═══ --}}
    <div class="main-wrap">

        {{-- Topbar --}}
        <header class="topbar" role="banner">
            <button class="mobile-toggle" onclick="document.body.classList.toggle('sidebar-open')" aria-label="Toggle Menu">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width:20px;height:20px;"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <h1 class="topbar-title">@yield('page-title', 'Dashboard')</h1>
            <div class="topbar-right">
                <a href="{{ route('notifications.index') }}" class="topbar-icon-btn" aria-label="Notifications">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" aria-hidden="true">
                        <path
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    @if(\App\Models\IncidentNotification::where('user_id', auth()->id())->whereNull('read_at')->exists())
                        <span class="notif-dot"></span>
                    @endif
                </a>
            </div>
        </header>

        {{-- Content --}}
        <main class="main-content" id="main-content" role="main">
            @if(session('success'))
                <div class="alert-toast success">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><path d="M20 6L9 17l-5-5"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert-toast error">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
                    {{ session('error') }}
                </div>
            @endif
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>

</html>
