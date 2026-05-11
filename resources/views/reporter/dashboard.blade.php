@extends('layouts.reporter')

@section('title', 'My Dashboard — EmergencyRS')
@section('page-title', 'Reporter Dashboard')

@section('content')
<div class="dashboard-container">

    {{-- Flash messages --}}
    @if(session('success'))
    <div style="background:#ECFDF5;border:1px solid #A7F3D0;color:#065F46;padding:12px 16px;border-radius:12px;margin-bottom:20px;font-size:13px;font-weight:600;">
        ✓ {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div style="background:#FEF2F2;border:1px solid #FECACA;color:#991B1B;padding:12px 16px;border-radius:12px;margin-bottom:20px;font-size:13px;font-weight:600;">
        ⚠ {{ session('error') }}
    </div>
    @endif

    {{-- ── CREDIBILITY & STATS ── --}}
    <div class="stats-row" style="margin-bottom: 24px;">
        
        {{-- Credibility Card (Highlight) --}}
        <div class="stat-card total" style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.2) 0%, rgba(139, 92, 246, 0.05) 100%); border: 1px solid rgba(139, 92, 246, 0.3);">
            <div class="stat-card-left">
                <div class="stat-card-num" style="color: var(--accent-violet);">{{ round($avgCredibility) }}%</div>
                <div class="stat-card-label">Avg. Credibility Score</div>
            </div>
            <div class="stat-card-icon" style="background: rgba(139, 92, 246, 0.2); color: var(--accent-violet);">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
        </div>

        <div class="stat-card resolved">
            <div class="stat-card-left">
                <div class="stat-card-num">{{ $resolvedReports }}</div>
                <div class="stat-card-label">Resolved Reports</div>
            </div>
            <div class="stat-card-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path d="M5 13l4 4L19 7"/>
                </svg>
            </div>
        </div>

        <div class="stat-card pending">
            <div class="stat-card-left">
                <div class="stat-card-num">{{ $pendingReports }}</div>
                <div class="stat-card-label">Pending Action</div>
            </div>
            <div class="stat-card-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- ── REPORT HISTORY ── --}}
    <div class="glass-card" style="padding: 0; overflow: hidden;">
        <div class="panel-header dashboard-header">
            <div>
                <h2 class="panel-title">My Emergency Reports</h2>
                <p class="panel-subtitle">Total of {{ $totalReports }} submissions</p>
            </div>
            <a href="{{ route('reporter.form') }}" class="primary-action-btn new-report-btn">
                + New Report
            </a>
        </div>

        <div class="incident-list" style="padding: 0;">
            @forelse ($incidents as $i)
            <div class="incident-card reporter-incident-card">
                
                {{-- Left: Icon + Info (clickable to track) --}}
                <a href="{{ route('reporter.track', ['id' => 'INC-' . str_pad($i->id, 4, '0', STR_PAD_LEFT)]) }}"
                   class="incident-link">
                    <div class="incident-icon-wrap">
                        <span class="badge badge-{{ $i->type }}" style="padding:8px;border-radius:8px;">
                            {{ strtoupper(substr($i->type, 0, 1)) }}
                        </span>
                    </div>
                    <div class="incident-info">
                        <div class="incident-id-row">
                            <p class="incident-id-text">INC-{{ str_pad($i->id, 4, '0', STR_PAD_LEFT) }}</p>
                            <span class="badge badge-{{ $i->status }}" style="font-size:10px;padding:2px 8px;">{{ ucfirst(str_replace('_',' ',$i->status)) }}</span>
                        </div>
                        <p class="incident-desc-text">
                            {{ $i->description }}
                        </p>
                        <p class="incident-meta-text">
                            📍 {{ $i->location_address ?? 'Location not specified' }} · {{ $i->created_at->format('M d, Y') }}
                        </p>
                    </div>
                </a>

                {{-- Right: Credibility + Withdraw --}}
                <div class="incident-actions">
                    <div class="credibility-label">
                        <span style="font-size:12px;font-weight:600;color:{{ $i->credibility_score >= 70 ? 'var(--accent-green)' : ($i->credibility_score >= 40 ? 'var(--accent-amber)' : 'var(--accent-red)') }};">
                            {{ $i->credibility_score }}% Credibility
                        </span>
                    </div>

                    @if($i->status === 'pending')
                    @php $displayId = 'INC-' . str_pad($i->id, 4, '0', STR_PAD_LEFT); @endphp
                    <form action="{{ route('reporter.incidents.withdraw', $i->id) }}" method="POST"
                          onsubmit="return confirm('Withdraw {{ $displayId }}? This will permanently delete this report.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="withdraw-btn">
                            ✕ Withdraw
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @empty
            <div style="padding:60px;text-align:center;">
                <p style="color:var(--text-dim);margin-bottom:20px;">You haven't reported any emergencies yet.</p>
                <a href="{{ route('reporter.form') }}" class="primary-action-btn" style="display:inline-block;width:auto;padding:12px 24px;">
                    Report an Emergency
                </a>
            </div>
            @endforelse
        </div>
    </div>

</div>

<style>
    .dashboard-header { padding: 16px 20px; border-bottom: 1px solid var(--border-subtle); display:flex; justify-content:space-between; align-items:center; }
    .new-report-btn { width:auto; padding:8px 16px; font-size:12px; }
    .reporter-incident-card { border:none; border-bottom: 1px solid var(--border-subtle); border-radius: 0; margin: 0; padding: 16px; display: flex; justify-content: space-between; align-items: center; transition: background 0.15s; }
    .reporter-incident-card:hover { background: rgba(255,255,255,0.03); }
    .incident-link { display:flex; gap:12px; align-items:center; flex:1; text-decoration:none; min-width: 0; }
    .incident-icon-wrap { width:40px; height:40px; border-radius:10px; background:rgba(255,255,255,0.05); display:flex; align-items:center; justify-content:center; border:1px solid var(--border-subtle); flex-shrink: 0; }
    .incident-info { min-width: 0; flex: 1; }
    .incident-id-row { display:flex; align-items:center; gap:8px; margin-bottom:2px; flex-wrap: wrap; }
    .incident-id-text { font-weight:700; font-size:14px; color:var(--text-primary); margin: 0; }
    .incident-desc-text { font-size:12px; color:var(--text-dim); margin: 0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .incident-meta-text { font-size:10px; color:var(--text-muted); margin-top:2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .incident-actions { text-align:right; margin-left:16px; flex-shrink:0; }
    .withdraw-btn { font-size:10px; font-weight:700; padding:4px 10px; background:rgba(239,68,68,0.1); color:#EF4444; border:1px solid rgba(239,68,68,0.3); border-radius:6px; cursor:pointer; transition:all 0.15s; }
    .withdraw-btn:hover { background:#EF4444; color:#fff; }

    @media (max-width: 768px) {
        .dashboard-header { flex-direction: column; align-items: flex-start; gap: 12px; padding: 12px 16px; }
        .new-report-btn { width: 100%; text-align: center; }
        .reporter-incident-card { flex-direction: column; align-items: flex-start; gap: 12px; padding: 12px 16px; }
        .incident-link { width: 100%; gap: 12px; }
        .incident-actions { width: 100%; text-align: left; margin-left: 0; display: flex; align-items: center; justify-content: space-between; border-top: 1px solid var(--border-subtle); padding-top: 10px; }
        .incident-desc-text, .incident-meta-text { white-space: normal; }
    }
</style>
@endsection
