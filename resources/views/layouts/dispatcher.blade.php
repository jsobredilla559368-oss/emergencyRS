<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dispatcher — EmergencyRS')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/responder.css') }}">
    <script src='https://api.mapbox.com/mapbox-gl-js/v3.1.2/mapbox-gl.js'></script>
    <link href='https://api.mapbox.com/mapbox-gl-js/v3.1.2/mapbox-gl.css' rel='stylesheet' />
    <style>
        :root {
            --accent-main: var(--accent-violet);
        }
        .sidebar-logo-badge {
            background: rgba(139, 92, 246, 0.15);
            color: var(--accent-violet);
            border-color: rgba(139, 92, 246, 0.2);
        }
        .nav-item.active {
            background: rgba(139, 92, 246, 0.1);
            color: var(--accent-violet);
            box-shadow: inset 2px 0 0 var(--accent-violet);
        }
    </style>
    @stack('styles')
</head>

<body>
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <aside class="sidebar" role="navigation" aria-label="Dispatcher Navigation">
        <div class="sidebar-logo">
            <div class="sidebar-logo-icon" style="box-shadow: 0 0 15px rgba(139, 92, 246, 0.2);">
                <svg width="16" height="16" viewBox="0 0 20 20" fill="none">
                    <path d="M10 1.5L2.5 5V10C2.5 14 5.8 17.8 10 18.5C14.2 17.8 17.5 14 17.5 10V5L10 1.5Z"
                        fill="rgba(255,255,255,0.15)" stroke="white" stroke-width="0.75" />
                    <path d="M10 6.5V13.5M6.5 10H13.5" stroke="white" stroke-width="1.75" stroke-linecap="round" />
                </svg>
            </div>
            <div>
                <div class="sidebar-logo-text">EmergencyRS</div>
            </div>
            <span class="sidebar-logo-badge ms-auto">Dispatcher</span>
        </div>

        <div class="sidebar-section">
            <p class="sidebar-section-label">Control Room</p>

            <a href="{{ route('dispatcher.dashboard') }}"
                class="nav-item {{ request()->routeIs('dispatcher.dashboard') ? 'active' : '' }}"
                aria-current="{{ request()->routeIs('dispatcher.dashboard') ? 'page' : 'false' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Live Map
            </a>

            <a href="{{ route('dispatcher.incident-log') }}"
                class="nav-item {{ request()->routeIs('dispatcher.incident-log') ? 'active' : '' }}"
                aria-current="{{ request()->routeIs('dispatcher.incident-log') ? 'page' : 'false' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/>
                </svg>
                Incident Log
            </a>

            <a href="{{ route('dispatcher.unit-tracking') }}"
                class="nav-item {{ request()->routeIs('dispatcher.unit-tracking') ? 'active' : '' }}"
                aria-current="{{ request()->routeIs('dispatcher.unit-tracking') ? 'page' : 'false' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87m-4-12a4 4 0 010 7.75"/>
                </svg>
                Unit Tracking
            </a>

            <a href="{{ route('notifications.index') }}"
                class="nav-item {{ request()->routeIs('notifications.index') ? 'active' : '' }}"
                aria-current="{{ request()->routeIs('notifications.index') ? 'page' : 'false' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                Notifications
            </a>
        </div>

        <div class="sidebar-bottom">
            <div class="sidebar-user">
                <div class="sidebar-avatar" style="background: var(--accent-violet);">
                    {{ strtoupper(substr(auth()->user()->name ?? 'D', 0, 1)) }}
                </div>
                <div>
                    <div class="sidebar-user-name">{{ auth()->user()->name ?? 'Dispatcher' }}</div>
                    <div class="sidebar-user-role">Dispatcher</div>
                </div>
            </div>
            <a href="{{ route('profile.edit') }}" class="nav-item {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                My Profile
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-item logout">Sign out</button>
            </form>
        </div>
    </aside>

    <div class="sidebar-overlay" onclick="document.body.classList.remove('sidebar-open')"></div>

    <div class="main-wrap">
        <header class="topbar" role="banner">
            <button class="mobile-toggle" onclick="document.body.classList.toggle('sidebar-open')" aria-label="Toggle Menu">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width:20px;height:20px;"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <h1 class="topbar-title">@yield('page-title', 'Control Room')</h1>
            <div class="topbar-right" style="display: flex; align-items: center; gap: 16px;">
                <a href="{{ route('notifications.index') }}" class="topbar-icon-btn" style="position: relative; color: var(--text-dim); transition: color 0.2s;" aria-label="Notifications">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 22px; height: 22px;">
                        <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    @if(\App\Models\IncidentNotification::where('user_id', auth()->id())->whereNull('read_at')->exists())
                        <span style="position: absolute; top: -2px; right: -2px; width: 8px; height: 8px; background: var(--accent-violet); border-radius: 50%; border: 2px solid var(--bg-panel);"></span>
                    @endif
                </a>
            </div>
        </header>
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

    {{-- Real-time polling for new alerts --}}
    <script>
        let lastNotificationCount = {{ \App\Models\IncidentNotification::where('user_id', auth()->id())->whereNull('read_at')->count() }};
        
        function checkNewAlerts() {
            fetch('{{ route('notifications.index') }}') // Simplest way to check without new routes
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newCount = doc.querySelectorAll('.notification-item.unread').length; // Assuming there's a list

                    // Or better, just check the red dot status via a dedicated endpoint if possible.
                    // For now, let's just do a simple refresh check of the bell icon.
                    const bellContainer = document.querySelector('.topbar-right');
                    if (bellContainer) {
                        const newDot = doc.querySelector('.topbar-icon-btn span');
                        const currentDot = bellContainer.querySelector('.topbar-icon-btn span');
                        
                        if (newDot && !currentDot) {
                            // Play sound and show dot
                            playNotificationSound();
                            const span = document.createElement('span');
                            span.style.cssText = "position: absolute; top: -2px; right: -2px; width: 8px; height: 8px; background: var(--accent-violet); border-radius: 50%; border: 2px solid var(--bg-panel);";
                            bellContainer.querySelector('.topbar-icon-btn').appendChild(span);
                        }
                    }
                });
        }

        function playNotificationSound() {
            const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
            audio.play().catch(e => console.log('Audio play blocked by browser'));
        }

        // Poll every 10 seconds
        setInterval(checkNewAlerts, 10000);
    </script>
</body>
</html>
