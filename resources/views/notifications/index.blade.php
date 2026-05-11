@php
    $role = auth()->user()->role;
    $layout = match($role) {
        'dispatcher' => 'layouts.dispatcher',
        'admin'      => 'layouts.admin',
        'reporter'   => 'layouts.reporter',
        default      => 'layouts.responder',
    };
@endphp
@extends($layout)

@section('title', 'Notifications — EmergencyRS')
@section('page-title', 'Alerts & Notifications')

@section('content')
<div class="glass-card" style="padding: 24px; border-radius: 20px; background: var(--bg-panel); border: 1px solid var(--border-subtle);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h3 class="section-label" style="margin:0">Recent Alerts</h3>
        @if($notifications->count() > 0)
            <span style="font-size: 12px; color: var(--text-dim);">{{ $notifications->count() }} alerts found</span>
        @endif
    </div>

    <div class="notification-list" style="display: flex; flex-direction: column; gap: 12px;">
        @forelse($notifications as $notif)
            <div class="notif-item" style="display: flex; gap: 16px; padding: 16px; border-radius: 12px; background: rgba(255,255,255,0.03); border: 1px solid var(--border-subtle); position: relative;">
                <div class="notif-icon" style="width: 40px; height: 40px; border-radius: 10px; background: rgba(59, 130, 246, 0.1); color: var(--accent-blue); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 20px; height: 20px;">
                        <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>
                <div style="flex: 1;">
                    <p style="font-size: 14px; font-weight: 700; color: var(--text-primary); margin: 0 0 4px 0;">{{ $notif->title }}</p>
                    <p style="font-size: 13px; color: var(--text-muted); line-height: 1.4; margin: 0 0 8px 0;">{{ $notif->message }}</p>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span style="font-size: 11px; color: var(--text-dim);">{{ $notif->created_at->diffForHumans() }}</span>
                        @if($notif->incident_id)
                            @php
                                $incidentLink = match($role) {
                                    'dispatcher' => route('dispatcher.incident', $notif->incident_id),
                                    'responder'  => route('responder.incident', $notif->incident_id),
                                    'reporter'   => route('reporter.track', ['id' => 'INC-' . str_pad($notif->incident_id, 4, '0', STR_PAD_LEFT)]),
                                    default      => route('reporter.track', ['id' => 'INC-' . str_pad($notif->incident_id, 4, '0', STR_PAD_LEFT)]),
                                };
                            @endphp
                            <a href="{{ $incidentLink }}" style="font-size: 11px; color: var(--accent-cyan); text-decoration: none; font-weight: 700;">View Incident →</a>
                        @endif
                    </div>
                </div>
                <form action="{{ route('notifications.destroy', $notif->id) }}" method="POST" style="position: absolute; top: 12px; right: 12px;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="background: none; border: none; color: var(--text-dim); cursor: pointer; padding: 4px;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 14px; height: 14px;">
                            <path d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </form>
            </div>
        @empty
            <div style="text-align: center; padding: 48px 24px; color: var(--text-dim);">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" style="width: 48px; height: 48px; opacity: 0.3; margin-bottom: 16px;">
                    <path d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                </svg>
                <p style="font-size: 14px; font-weight: 600;">No new notifications</p>
                <p style="font-size: 12px;">You're all caught up!</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
