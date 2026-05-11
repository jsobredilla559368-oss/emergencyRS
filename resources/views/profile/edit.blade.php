@php
    $role = auth()->user()->role;
    $layout = match($role) {
        'dispatcher' => 'layouts.dispatcher',
        'admin'      => 'layouts.admin',
        'reporter'   => 'layouts.reporter',
        default      => 'layouts.responder',
    };
    $dashRoute = match($role) {
        'admin'      => route('admin.dashboard'),
        'dispatcher' => route('dispatcher.dashboard'),
        'responder'  => route('responder.dashboard'),
        default      => route('reporter.dashboard'),
    };
@endphp
@extends($layout)

@section('title', 'My Profile — EmergencyRS')
@section('page-title', 'My Profile')

@push('styles')
<style>
    .profile-grid { display: grid; grid-template-columns: 280px 1fr; gap: 24px; align-items: start; }
    @media(max-width:800px) { .profile-grid { grid-template-columns: 1fr; } }

    .profile-avatar-card {
        background: var(--bg-panel, #fff); border: 1px solid var(--border-subtle, #e2e8f0);
        border-radius: 20px; padding: 32px 24px; text-align: center;
    }
    .profile-avatar-ring {
        width: 88px; height: 88px; border-radius: 50%;
        background: linear-gradient(135deg, #2563EB, #7C3AED);
        display: flex; align-items: center; justify-content: center;
        font-size: 32px; font-weight: 800; color: #fff;
        margin: 0 auto 16px; box-shadow: 0 8px 24px rgba(37,99,235,0.3);
    }
    .profile-name  { font-size: 18px; font-weight: 800; color: var(--text-primary, #0F172A); margin-bottom: 4px; }
    .profile-role  { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700;
                     text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 20px; }
    .role-reporter   { background:#EFF6FF; color:#2563EB; }
    .role-responder  { background:#ECFDF5; color:#059669; }
    .role-dispatcher { background:#F5F3FF; color:#7C3AED; }
    .role-admin      { background:#FFF7ED; color:#EA580C; }

    .profile-stat { padding: 12px 0; border-bottom: 1px solid var(--border-subtle, #e2e8f0); text-align: left; }
    .profile-stat:last-child { border-bottom: none; }
    .profile-stat-label { font-size: 11px; color: var(--text-dim, #94A3B8); font-weight: 600; text-transform: uppercase; }
    .profile-stat-val   { font-size: 14px; font-weight: 700; color: var(--text-primary, #0F172A); margin-top: 2px; }

    .profile-section {
        background: var(--bg-panel, #fff); border: 1px solid var(--border-subtle, #e2e8f0);
        border-radius: 20px; padding: 28px; margin-bottom: 20px;
    }
    .profile-section-title { font-size: 15px; font-weight: 800; color: var(--text-primary, #0F172A); margin-bottom: 20px;
                              padding-bottom: 12px; border-bottom: 1px solid var(--border-subtle, #e2e8f0); }

    .form-group { margin-bottom: 18px; }
    .form-label { display: block; font-size: 12px; font-weight: 700; color: var(--text-dim, #64748B);
                  text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
    .form-input {
        width: 100%; height: 44px; padding: 0 14px;
        border: 1.5px solid var(--border-subtle, #E2E8F0); border-radius: 12px;
        font-size: 14px; font-family: inherit; color: var(--text-primary, #0F172A);
        background: var(--bg-input, #F8FAFC); outline: none; transition: border-color 0.2s;
        box-sizing: border-box;
    }
    .form-input:focus { border-color: #2563EB; background: #fff; }

    .btn-primary {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 24px; background: linear-gradient(135deg,#2563EB,#7C3AED);
        color: #fff; border: none; border-radius: 12px; font-size: 14px; font-weight: 700;
        cursor: pointer; transition: opacity 0.2s;
    }
    .btn-primary:hover { opacity: 0.88; }
    .btn-danger {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 24px; background: #EF4444;
        color: #fff; border: none; border-radius: 12px; font-size: 14px; font-weight: 700;
        cursor: pointer; transition: opacity 0.2s;
    }
    .btn-danger:hover { opacity: 0.88; }

    .alert-success { background:#ECFDF5; border:1px solid #A7F3D0; color:#065F46; padding:12px 16px; border-radius:10px; margin-bottom:16px; font-size:13px; }
    .alert-error   { background:#FEF2F2; border:1px solid #FECACA; color:#991B1B; padding:12px 16px; border-radius:10px; margin-bottom:16px; font-size:13px; }
</style>
@endpush

@section('content')
<div class="profile-grid">

    {{-- ── LEFT: Avatar card ── --}}
    <div>
        <div class="profile-avatar-card">
            <div class="profile-avatar-ring">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div class="profile-name">{{ auth()->user()->name }}</div>
            <span class="profile-role role-{{ auth()->user()->role }}">{{ ucfirst(auth()->user()->role) }}</span>

            <div>
                <div class="profile-stat">
                    <div class="profile-stat-label">Email</div>
                    <div class="profile-stat-val" style="font-size:12px;word-break:break-all;">{{ auth()->user()->email }}</div>
                </div>
                @if(auth()->user()->phone)
                <div class="profile-stat">
                    <div class="profile-stat-label">Phone</div>
                    <div class="profile-stat-val">{{ auth()->user()->phone }}</div>
                </div>
                @endif
                <div class="profile-stat">
                    <div class="profile-stat-label">Member Since</div>
                    <div class="profile-stat-val">{{ auth()->user()->created_at->format('M d, Y') }}</div>
                </div>
                <div class="profile-stat">
                    <div class="profile-stat-label">Email Status</div>
                    <div class="profile-stat-val" style="color:{{ auth()->user()->email_verified_at ? '#10B981' : '#F59E0B' }};">
                        {{ auth()->user()->email_verified_at ? '✓ Verified' : '⚠ Not Verified' }}
                    </div>
                </div>
            </div>

            <div style="margin-top: 20px;">
                <a href="{{ $dashRoute }}" style="display:block; padding: 10px; border: 1.5px solid var(--border-subtle,#e2e8f0);
                   border-radius: 10px; font-size: 13px; font-weight: 700; color: var(--text-muted,#64748B);
                   text-decoration: none; transition: all 0.2s;"
                   onmouseover="this.style.borderColor='#2563EB';this.style.color='#2563EB'"
                   onmouseout="this.style.borderColor='';this.style.color=''">
                    ← Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    {{-- ── RIGHT: Forms ── --}}
    <div>

        {{-- Flash messages --}}
        @if(session('status') === 'profile-updated')
            <div class="alert-success">✓ Profile updated successfully.</div>
        @endif
        @if(session('status') === 'password-updated')
            <div class="alert-success">✓ Password changed successfully.</div>
        @endif
        @if($errors->any())
            <div class="alert-error">{{ $errors->first() }}</div>
        @endif

        {{-- Update Profile --}}
        <div class="profile-section">
            <div class="profile-section-title">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width:16px;height:16px;display:inline;margin-right:6px;"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Personal Information
            </div>
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PATCH')
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-input" value="{{ old('name', auth()->user()->name) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-input" value="{{ old('email', auth()->user()->email) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-input" value="{{ old('phone', auth()->user()->phone) }}" placeholder="+63 9XX XXX XXXX">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" class="form-input" value="{{ old('address', auth()->user()->address) }}" placeholder="Your address">
                    </div>
                </div>
                <button type="submit" class="btn-primary">Save Changes</button>
            </form>
        </div>

        {{-- Change Password --}}
        <div class="profile-section">
            <div class="profile-section-title">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width:16px;height:16px;display:inline;margin-right:6px;"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                Change Password
            </div>
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('PUT')
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-input" autocomplete="current-password">
                    </div>
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-input" autocomplete="new-password">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-input" autocomplete="new-password">
                    </div>
                </div>
                <button type="submit" class="btn-primary">Update Password</button>
            </form>
        </div>

        {{-- Danger Zone --}}
        <div class="profile-section" style="border-color: #FECACA;">
            <div class="profile-section-title" style="color:#EF4444; border-bottom-color:#FECACA;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width:16px;height:16px;display:inline;margin-right:6px;"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Danger Zone
            </div>
            <p style="font-size:13px; color:var(--text-muted,#64748B); margin-bottom:16px;">
                Permanently delete your account. This action cannot be undone and all your data will be removed.
            </p>
            <form method="POST" action="{{ route('profile.destroy') }}"
                  onsubmit="return confirm('Are you sure you want to delete your account? This is permanent and cannot be undone.');">
                @csrf
                @method('DELETE')
                <input type="password" name="password" class="form-input" placeholder="Enter your password to confirm" style="max-width:300px; margin-bottom:12px;">
                <br>
                <button type="submit" class="btn-danger">Delete My Account</button>
            </form>
        </div>

    </div>
</div>
@endsection
