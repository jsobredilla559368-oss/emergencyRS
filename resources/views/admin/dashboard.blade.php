@extends('layouts.admin')

@section('title', 'Admin Dashboard — EmergencyRS')
@section('page-title', 'System Overview')

@section('content')
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px; margin-bottom: 32px;">
    <div class="card">
        <p style="color: var(--text-muted); font-size: 14px; margin: 0;">Total Users</p>
        <h3 style="font-size: 28px; margin: 8px 0 0;">{{ $stats['total_users'] }}</h3>
    </div>
    <div class="card">
        <p style="color: var(--text-muted); font-size: 14px; margin: 0;">Total Incidents</p>
        <h3 style="font-size: 28px; margin: 8px 0 0;">{{ $stats['total_incidents'] }}</h3>
    </div>
    <div class="card">
        <p style="color: var(--text-muted); font-size: 14px; margin: 0;">Pending Reports</p>
        <h3 style="font-size: 28px; margin: 8px 0 0; color: #ef4444;">{{ $stats['pending_incidents'] }}</h3>
    </div>
    <div class="card">
        <p style="color: var(--text-muted); font-size: 14px; margin: 0;">Resolved Today</p>
        <h3 style="font-size: 28px; margin: 8px 0 0; color: #10b981;">{{ $stats['resolved_today'] }}</h3>
    </div>
</div>

<div class="card">
    <h3 style="margin-top:0;">System Health</h3>
    <p style="color: var(--text-muted);">All systems operational. Database connectivity stable.</p>
</div>
@endsection
