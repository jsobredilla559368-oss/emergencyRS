<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In — {{ config('app.name', 'EmergencyRS') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">

</head>
<body>

{{-- ═══ LEFT: BRAND PANEL ═══ --}}
<div class="brand-panel">
    <div class="brand-logo">
        <div class="brand-logo-icon">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                <path d="M10 1.5L2.5 5V10C2.5 14 5.8 17.8 10 18.5C14.2 17.8 17.5 14 17.5 10V5L10 1.5Z"
                      fill="rgba(255,255,255,0.15)" stroke="rgba(255,255,255,0.5)" stroke-width="0.75"/>
                <path d="M10 6.5V13.5M6.5 10H13.5" stroke="white" stroke-width="1.75" stroke-linecap="round"/>
            </svg>
        </div>
        <span class="brand-name">EmergencyRS</span>
    </div>

    <div class="brand-body">
        <p class="brand-eyebrow">Response Management</p>
        <h1 class="brand-headline">Coordinated response, when it matters most.</h1>
        <p class="brand-sub">A centralized platform for dispatching, tracking, and resolving emergency incidents across your municipality.</p>
    </div>

    <div class="brand-stats">
        <div>
            <div class="stat-num">2.4k</div>
            <div class="stat-lbl">Incidents resolved</div>
        </div>
        <div>
            <div class="stat-num">98%</div>
            <div class="stat-lbl">Response rate</div>
        </div>
        <div>
            <div class="stat-num">47</div>
            <div class="stat-lbl">Active units</div>
        </div>
    </div>

    <p class="brand-copyright">© {{ date('Y') }} EmergencyRS · All rights reserved</p>
</div>

{{-- ═══ RIGHT: FORM PANEL ═══ --}}
<div class="form-panel">
    <div class="form-card">

        {{-- Session flash --}}
        @if (session('status'))
            <div class="flash-status">{{ session('status') }}</div>
        @endif

        <div class="form-head">
            <h2 class="form-title">Welcome back</h2>
            <p class="form-sub">Choose your role to continue</p>
        </div>

        {{-- ── Role Selector ── --}}
        <div class="role-grid" id="role-grid">
            <button type="button" class="role-card" data-role="reporter" id="role-reporter" onclick="selectRole('reporter')">
                <div class="role-icon" style="background: rgba(16,185,129,0.15); color: #10B981;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <span class="role-label">Reporter</span>
                <span class="role-desc">Community</span>
            </button>
            <button type="button" class="role-card" data-role="responder" id="role-responder" onclick="selectRole('responder')">
                <div class="role-icon" style="background: rgba(6,182,212,0.15); color: #06B6D4;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 9m0 8V9m0 0L9 7"/></svg>
                </div>
                <span class="role-label">Responder</span>
                <span class="role-desc">Field Unit</span>
            </button>
            <button type="button" class="role-card" data-role="dispatcher" id="role-dispatcher" onclick="selectRole('dispatcher')">
                <div class="role-icon" style="background: rgba(139,92,246,0.15); color: #8B5CF6;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg>
                </div>
                <span class="role-label">Dispatcher</span>
                <span class="role-desc">Control Room</span>
            </button>
            <button type="button" class="role-card" data-role="admin" id="role-admin" onclick="selectRole('admin')">
                <div class="role-icon" style="background: rgba(245,158,11,0.15); color: #F59E0B;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <span class="role-label">Admin</span>
                <span class="role-desc">System Manager</span>
            </button>
        </div>

        {{-- ── Email / Password Form ── --}}
        <form method="POST" action="{{ route('login') }}" id="login-form">
            @csrf
            <input type="hidden" name="intended_role" id="intended_role" value="">

            <div id="role-hint" style="display:none; padding: 10px 14px; border-radius: 10px; font-size: 12px; font-weight: 600; margin-bottom: 16px; border: 1px solid; letter-spacing: 0.5px;"></div>
            @csrf

            <div class="field">
                <label for="email" class="field-label">Email address</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    class="field-input"
                    value="{{ old('email') }}"
                    placeholder="you@department.gov"
                    required
                    autofocus
                    autocomplete="username"
                >
                @error('email')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="password" class="field-label">Password</label>
                <div class="pwd-wrap">
                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="field-input"
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                    >
                    <button type="button" class="pwd-toggle" onclick="togglePwd()" aria-label="Toggle password visibility">
                        <svg id="eye-show" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <svg id="eye-hide" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                            <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="meta-row">
                <label class="remember">
                    <input type="checkbox" name="remember" id="remember_me">
                    Remember me
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="forgot">Forgot password?</a>
                @endif
            </div>

            <button type="submit" class="submit-btn">Sign in</button>
        </form>

        @if (Route::has('register'))
            <p class="form-footer">
                Don't have an account? <a href="{{ route('register') }}">Request access</a>
            </p>
        @endif

    </div>
</div>

<style>
    .role-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-bottom: 20px;
    }
    .role-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        padding: 14px 10px;
        border-radius: 12px;
        border: 2px solid #e5e7eb;
        background: #f9fafb;
        cursor: pointer;
        transition: all 0.2s;
        text-align: center;
    }
    .role-card:hover {
        border-color: #9ca3af;
        background: #f3f4f6;
        transform: translateY(-1px);
    }
    .role-card.selected {
        border-color: var(--role-color, #6366f1) !important;
        background: var(--role-bg, rgba(99,102,241,0.05)) !important;
        box-shadow: 0 0 0 3px var(--role-shadow, rgba(99,102,241,0.15));
    }
    .role-icon {
        width: 38px; height: 38px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
    }
    .role-icon svg { width: 18px; height: 18px; }
    .role-label { font-size: 13px; font-weight: 700; color: #111827; }
    .role-desc   { font-size: 10px; color: #6b7280; font-weight: 500; }
</style>
<script>
    const roleConfig = {
        reporter:   { color: '#10B981', bg: 'rgba(16,185,129,0.05)',   shadow: 'rgba(16,185,129,0.2)',   hint: '✅ Signing in as a Community Reporter' },
        responder:  { color: '#06B6D4', bg: 'rgba(6,182,212,0.05)',    shadow: 'rgba(6,182,212,0.2)',    hint: '🚨 Signing in as a Field Responder' },
        dispatcher: { color: '#8B5CF6', bg: 'rgba(139,92,246,0.05)',   shadow: 'rgba(139,92,246,0.2)',   hint: '📡 Signing in as a Dispatcher' },
        admin:      { color: '#F59E0B', bg: 'rgba(245,158,11,0.05)',   shadow: 'rgba(245,158,11,0.2)',   hint: '⚙️ Signing in as a System Admin' },
    };

    function selectRole(role) {
        // Update hidden input
        document.getElementById('intended_role').value = role;

        // Update card states
        document.querySelectorAll('.role-card').forEach(c => {
            c.classList.remove('selected');
            c.style.removeProperty('--role-color');
            c.style.removeProperty('--role-bg');
            c.style.removeProperty('--role-shadow');
        });

        const selected = document.getElementById('role-' + role);
        const cfg = roleConfig[role];
        selected.classList.add('selected');
        selected.style.setProperty('--role-color', cfg.color);
        selected.style.setProperty('--role-bg', cfg.bg);
        selected.style.setProperty('--role-shadow', cfg.shadow);

        // Show hint banner
        const hint = document.getElementById('role-hint');
        hint.style.display = 'block';
        hint.textContent = cfg.hint;
        hint.style.color = cfg.color;
        hint.style.borderColor = cfg.color;
        hint.style.background = cfg.bg;
    }

    function togglePwd() {
        const input = document.getElementById('password');
        const show  = document.getElementById('eye-show');
        const hide  = document.getElementById('eye-hide');
        if (input.type === 'password') {
            input.type = 'text';
            show.style.display = 'none';
            hide.style.display = 'block';
        } else {
            input.type = 'password';
            show.style.display = 'block';
            hide.style.display = 'none';
        }
    }
</script>
</body>
</html>

