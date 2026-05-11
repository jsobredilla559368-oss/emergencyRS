<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin — EmergencyRS')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-body: #f8fafc;
            --bg-sidebar: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --accent: #2563eb;
            --border: #e2e8f0;
        }
        body { font-family: 'Inter', sans-serif; background: var(--bg-body); color: var(--text-main); margin: 0; display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: var(--bg-sidebar); border-right: 1px solid var(--border); display: flex; flex-direction: column; }
        .sidebar-header { padding: 24px; border-bottom: 1px solid var(--border); font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; font-size: 18px; color: var(--accent); }
        .sidebar-menu { flex: 1; padding: 16px; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 10px 16px; border-radius: 8px; text-decoration: none; color: var(--text-muted); font-weight: 500; margin-bottom: 4px; transition: all 0.2s; }
        .nav-item:hover { background: #f1f5f9; color: var(--text-main); }
        .nav-item.active { background: #eff6ff; color: var(--accent); }
        .main-content { flex: 1; display: flex; flex-direction: column; min-width: 0; }
        .topbar { height: 64px; background: #fff; border-bottom: 1px solid var(--border); display: flex; align-items: center; padding: 0 32px; justify-content: space-between; position: sticky; top: 0; z-index: 40; }
        .page-content { padding: 32px; flex: 1; }
        .card { background: #fff; border-radius: 12px; border: 1px solid var(--border); padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }

        .mobile-toggle { display: none; }
        .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 45; }

        @media (max-width: 768px) {
            .sidebar { position: fixed; left: -260px; top: 0; bottom: 0; z-index: 50; transition: left 0.3s ease; }
            body.sidebar-open .sidebar { left: 0; }
            body.sidebar-open .sidebar-overlay { display: block; }
            .topbar { padding: 0 16px; }
            .page-content { padding: 16px; }
            .mobile-toggle { display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; background: #f1f5f9; border: 1px solid var(--border); border-radius: 8px; cursor: pointer; margin-right: 12px; }
        }
    </style>
    @stack('styles')
</head>

<body>
    <aside class="sidebar">
        <div class="sidebar-header">EmergencyRS Admin</div>
        <div class="sidebar-menu">
            <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width:16px;height:16px;"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                Dashboard
            </a>
            <a href="{{ route('admin.users') }}" class="nav-item {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width:16px;height:16px;"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87m-4-12a4 4 0 010 7.75"/></svg>
                User Management
            </a>
            <a href="{{ route('admin.system-logs') }}" class="nav-item {{ request()->routeIs('admin.system-logs') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width:16px;height:16px;"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/></svg>
                System Logs
            </a>
            <a href="{{ route('admin.analytics') }}" class="nav-item {{ request()->routeIs('admin.analytics') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width:16px;height:16px;"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>
                Analytics
            </a>
        </div>
        <div style="padding: 16px; border-top: 1px solid var(--border);">
            <a href="{{ route('profile.edit') }}" style="display:flex;align-items:center;gap:10px;padding:10px;border-radius:8px;text-decoration:none;color:var(--text-main);margin-bottom:8px;transition:background 0.15s;"
               onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background=''">
                <div style="width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,#2563EB,#7C3AED);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:13px;flex-shrink:0;">
                    {{ strtoupper(substr(auth()->user()->name,0,1)) }}
                </div>
                <div style="min-width:0;">
                    <div style="font-size:13px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ auth()->user()->name }}</div>
                    <div style="font-size:11px;color:var(--text-muted);">My Profile</div>
                </div>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" style="width:100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; background: #fff; cursor: pointer; font-size:13px;">Sign out</button>
            </form>
        </div>
    <div class="sidebar-overlay" onclick="document.body.classList.remove('sidebar-open')"></div>

    <div class="main-content">
        <header class="topbar">
            <div style="display:flex; align-items:center;">
                <button class="mobile-toggle" onclick="document.body.classList.toggle('sidebar-open')" aria-label="Toggle Menu">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width:20px;height:20px;"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h2 style="margin:0; font-size: 18px; font-family: 'Plus Jakarta Sans';">@yield('page-title', 'Dashboard')</h2>
            </div>
        </header>
        <div class="page-content">
            @yield('content')
        </div>
    </div>

    @stack('scripts')
</body>
</html>
