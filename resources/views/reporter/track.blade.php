@extends('layouts.reporter-public')

@section('title', 'Track your report — EmergencyRS')

@section('content')
<div class="reporter-main">

    <div class="page-header">
        <h1 class="page-title">Track your report</h1>
        <p class="page-sub">Enter your report ID to see real-time updates on your emergency request.</p>
    </div>

    <div class="step-card">
        <form action="{{ route('reporter.track') }}" method="GET">
            <p class="step-label">Report Identifier</p>
            <div class="search-group">
                <input 
                    type="text" 
                    name="id" 
                    class="field-input" 
                    placeholder="e.g. INC-0001" 
                    value="{{ request('id') }}"
                >
                <button type="submit" class="gps-btn search-btn">
                    Search
                </button>
            </div>
        </form>
    </div>

    @if($incident)
        <div class="step-card status-card">
            <div class="status-card-header">
                <div>
                    <p class="step-label" style="margin-bottom: 4px;">Current Status</p>
                    <h2 class="status-title">
                        {{ ucfirst($incident->status) }}
                    </h2>
                </div>
                <div class="status-icon-wrap">
                    @if($incident->status === 'pending')
                        <svg width="28" height="28" fill="none" stroke="#64748B" viewBox="0 0 24 24" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    @elseif($incident->status === 'dispatched')
                        <svg width="28" height="28" fill="none" stroke="#2563EB" viewBox="0 0 24 24" stroke-width="2"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    @else
                        <svg width="28" height="28" fill="none" stroke="#10B981" viewBox="0 0 24 24" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
                    @endif
                </div>
            </div>

            <div class="progress-bar-wrap" style="height: 8px; background: #E2E8F0; border-radius: 4px; margin-bottom: 24px; position: relative; overflow: hidden;">
                @php
                    $pct = match($incident->status) {
                        'pending'    => 20,
                        'dispatched' => 45,
                        'en_route'   => 65,
                        'arrived'    => 85,
                        'resolved'   => 100,
                        default      => 20,
                    };
                @endphp
                <div class="progress-fill" style="position: absolute; left: 0; top: 0; height: 100%; background: var(--brand-accent); width: {{ $pct }}%; transition: width 1s ease-out;"></div>
            </div>

            <div class="incident-summary" style="background: #F8FAFC; padding: 16px; border-radius: 12px; border: 1px solid #E2E8F0;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <p style="font-size: 13px; font-weight: 700; color: #64748B;">REPORT SUMMARY</p>
                    <span style="font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 12px; background: {{ $incident->credibility_score >= 70 ? '#DCFCE7' : ($incident->credibility_score >= 40 ? '#FEF9C3' : '#FEE2E2') }}; color: {{ $incident->credibility_score >= 70 ? '#166534' : ($incident->credibility_score >= 40 ? '#854D0E' : '#991B1B') }};">
                        Credibility: {{ $incident->credibility_label }}
                    </span>
                </div>
                <p style="font-size: 14px; line-height: 1.5; color: #1E293B;">
                    <strong>Type:</strong> {{ ucfirst($incident->type) }}<br>
                    <strong>Time:</strong> {{ $incident->created_at->diffForHumans() }}<br>
                    <strong>Media:</strong> {{ $incident->media->count() }} attached<br>
                    <strong>Description:</strong> {{ $incident->description }}
                </p>
            </div>
        </div>

        <div class="step-card">
            <p class="step-label">Live Updates</p>
            <div class="track-timeline" style="margin-top: 16px;">
                @forelse($incident->statusUpdates as $update)
                    <div style="display: flex; gap: 16px; margin-bottom: 20px;">
                        <div style="width: 12px; height: 12px; border-radius: 50%; background: var(--brand-accent); margin-top: 4px; flex-shrink: 0; box-shadow: 0 0 8px rgba(37,99,235,0.4);"></div>
                        <div>
                            <p style="font-weight: 700; font-size: 14px;">{{ ucfirst($update->status) }}</p>
                            <p style="font-size: 12px; color: #94A3B8; margin-bottom: 4px;">{{ $update->created_at->format('H:i • M d') }}</p>
                            <p style="font-size: 14px; color: #475569;">{{ $update->note }}</p>
                        </div>
                    </div>
                @empty
                    <p style="font-size: 14px; color: #94A3B8; text-align: center;">Waiting for dispatcher to review your report...</p>
                @endforelse
            </div>
        </div>
    @elseif(request('id'))
        <div class="step-card" style="text-align: center; border-color: #FECACA; background: #FEF2F2;">
            <p style="color: #DC2626; font-weight: 600;">Report not found.</p>
            <p style="font-size: 13px; color: #991B1B; margin-top: 4px;">Please double-check the ID or try again later.</p>
        </div>
    @endif

</div>{{-- /.reporter-main --}}
@endsection

<style>
    .field-input { flex: 1; height: 48px; padding: 0 16px; border: 2px solid var(--border-light); border-radius: 12px; font-family: inherit; font-size: 15px; outline: none; transition: border-color 0.2s; }
    .field-input:focus { border-color: var(--brand-accent) !important; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1); }
    .search-group { display: flex; gap: 12px; }
    .search-btn { width: auto; padding: 0 24px; border-radius: 12px; }
    .status-card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
    .status-title { font-family: var(--font-heading); font-size: 24px; font-weight: 800; color: var(--text-main); margin: 0; }
    .status-icon-wrap { width: 56px; height: 56px; border-radius: 50%; background: #F1F5F9; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }

    @media (max-width: 600px) {
        .search-group { flex-direction: column; }
        .search-btn { width: 100%; height: 48px; }
        .status-card-header { flex-direction: column-reverse; align-items: center; text-align: center; gap: 16px; }
    }
</style>
