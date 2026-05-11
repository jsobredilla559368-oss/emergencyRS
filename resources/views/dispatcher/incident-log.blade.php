@extends('layouts.dispatcher')

@section('title', 'Incident Log — Dispatcher')
@section('page-title', 'Incident Log')

@section('content')

{{-- ── STATS ROW ── --}}
<div class="stats-row" style="margin-bottom: 24px;">
    <div class="stat-card total">
        <div class="stat-card-left">
            <div class="stat-card-num">{{ $totalAll }}</div>
            <div class="stat-card-label">Total Reports</div>
        </div>
        <div class="stat-card-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg>
        </div>
    </div>
    <div class="stat-card pending">
        <div class="stat-card-left">
            <div class="stat-card-num">{{ $totalActive }}</div>
            <div class="stat-card-label">Active Incidents</div>
        </div>
        <div class="stat-card-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
        </div>
    </div>
    <div class="stat-card resolved">
        <div class="stat-card-left">
            <div class="stat-card-num">{{ $totalResolved }}</div>
            <div class="stat-card-label">Resolved</div>
        </div>
        <div class="stat-card-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
    </div>
</div>

{{-- ── FILTERS ── --}}
<form method="GET" action="{{ route('dispatcher.incident-log') }}" class="filter-bar" style="margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
    <div class="filter-search-wrap" style="flex: 1; min-width: 180px;">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
        </svg>
        <input type="text" name="q" class="filter-search" placeholder="Search description or address…" value="{{ request('q') }}">
    </div>

    <select name="status" class="filter-select" onchange="this.form.submit()">
        <option value="">All Statuses</option>
        @foreach(['pending','dispatched','en_route','arrived','resolved'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
        @endforeach
    </select>

    <select name="type" class="filter-select" onchange="this.form.submit()">
        <option value="">All Types</option>
        @foreach(['medical','fire','crime','disaster','accident','flood','earthquake','hazmat','missing_person','others'] as $t)
            <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$t)) }}</option>
        @endforeach
    </select>

    <select name="severity" class="filter-select" onchange="this.form.submit()">
        <option value="">All Severities</option>
        <option value="high"   {{ request('severity') === 'high'   ? 'selected' : '' }}>High</option>
        <option value="medium" {{ request('severity') === 'medium' ? 'selected' : '' }}>Medium</option>
        <option value="low"    {{ request('severity') === 'low'    ? 'selected' : '' }}>Low</option>
    </select>

    @if(request()->hasAny(['q','status','type','severity']))
        <a href="{{ route('dispatcher.incident-log') }}" style="font-size: 12px; color: var(--text-dim); align-self: center; white-space: nowrap;">✕ Clear filters</a>
    @endif
</form>

{{-- ── FULL LOG TABLE ── --}}
<div class="glass-card" style="padding: 0; overflow: hidden;">
    <div class="panel-header" style="padding: 16px 20px; border-bottom: 1px solid var(--border-subtle);">
        <h2 class="panel-title">All Incident Records</h2>
        <p class="panel-subtitle">{{ $incidents->total() }} records found</p>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
                <tr style="border-bottom: 1px solid var(--border-subtle);">
                    <th style="padding: 12px 20px; text-align: left; color: var(--text-dim); font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">ID</th>
                    <th style="padding: 12px 16px; text-align: left; color: var(--text-dim); font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Type / Severity</th>
                    <th style="padding: 12px 16px; text-align: left; color: var(--text-dim); font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Status</th>
                    <th style="padding: 12px 16px; text-align: left; color: var(--text-dim); font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Reporter</th>
                    <th style="padding: 12px 16px; text-align: left; color: var(--text-dim); font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Assigned To</th>
                    <th style="padding: 12px 16px; text-align: left; color: var(--text-dim); font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Updates</th>
                    <th style="padding: 12px 16px; text-align: left; color: var(--text-dim); font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Reported</th>
                    <th style="padding: 12px 16px; text-align: left; color: var(--text-dim); font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($incidents as $i)
                <tr style="border-bottom: 1px solid var(--border-subtle); transition: background 0.15s;" onmouseover="this.style.background='rgba(255,255,255,0.02)'" onmouseout="this.style.background='transparent'">
                    <td style="padding: 14px 20px;">
                        <span style="font-family: monospace; font-size: 13px; font-weight: 700; color: var(--text-primary);">
                            INC-{{ str_pad($i->id, 4, '0', STR_PAD_LEFT) }}
                        </span>
                    </td>
                    <td style="padding: 14px 16px;">
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <span class="badge badge-{{ $i->type }}" style="display: inline-block; width: fit-content;">{{ ucfirst($i->type) }}</span>
                            <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;
                                color: {{ $i->severity === 'high' ? 'var(--accent-red)' : ($i->severity === 'medium' ? 'var(--accent-amber)' : 'var(--accent-green)') }};">
                                {{ ucfirst($i->severity) }}
                            </span>
                        </div>
                    </td>
                    <td style="padding: 14px 16px;">
                        <span class="badge badge-{{ $i->status }}">{{ ucfirst(str_replace('_',' ',$i->status)) }}</span>
                    </td>
                    <td style="padding: 14px 16px; color: var(--text-primary);">
                        {{ $i->reporter->name ?? 'Anonymous' }}
                    </td>
                    <td style="padding: 14px 16px;">
                        @if($i->responder)
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="width: 24px; height: 24px; border-radius: 50%; background: var(--accent-cyan); display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700; color: #000;">
                                    {{ strtoupper(substr($i->responder->name, 0, 1)) }}
                                </div>
                                <span style="color: var(--text-primary);">{{ $i->responder->name }}</span>
                            </div>
                        @else
                            <span style="color: var(--text-dim); font-style: italic; font-size: 12px;">Unassigned</span>
                        @endif
                    </td>
                    <td style="padding: 14px 16px; text-align: center;">
                        <span style="font-size: 13px; font-weight: 700; color: var(--accent-cyan);">{{ $i->statusUpdates->count() }}</span>
                    </td>
                    <td style="padding: 14px 16px; color: var(--text-dim); font-size: 12px;">
                        {{ $i->created_at->format('M d, Y') }}<br>
                        <span style="font-size: 11px;">{{ $i->created_at->format('H:i') }}</span>
                    </td>
                    <td style="padding: 14px 16px;">
                        <a href="{{ route('dispatcher.incident', $i->id) }}"
                           style="font-size: 11px; font-weight: 700; color: var(--accent-violet); text-decoration: none; padding: 5px 10px; border: 1px solid var(--accent-violet); border-radius: 6px; white-space: nowrap; transition: background 0.2s;"
                           onmouseover="this.style.background='rgba(139,92,246,0.1)'" onmouseout="this.style.background='transparent'">
                            Open →
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="padding: 60px; text-align: center; color: var(--text-dim);">
                        No incidents found matching your filters.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($incidents->hasPages())
    <div style="padding: 16px 20px; border-top: 1px solid var(--border-subtle); display: flex; justify-content: space-between; align-items: center;">
        <span style="font-size: 12px; color: var(--text-dim);">
            Showing {{ $incidents->firstItem() }}–{{ $incidents->lastItem() }} of {{ $incidents->total() }}
        </span>
        <div style="display: flex; gap: 6px;">
            @if($incidents->onFirstPage())
                <span style="padding: 6px 12px; border-radius: 8px; border: 1px solid var(--border-subtle); color: var(--text-dim); font-size: 12px; cursor: not-allowed;">← Prev</span>
            @else
                <a href="{{ $incidents->previousPageUrl() }}" style="padding: 6px 12px; border-radius: 8px; border: 1px solid var(--border-subtle); color: var(--text-primary); font-size: 12px; text-decoration: none;">← Prev</a>
            @endif
            @if($incidents->hasMorePages())
                <a href="{{ $incidents->nextPageUrl() }}" style="padding: 6px 12px; border-radius: 8px; border: 1px solid var(--accent-violet); color: var(--accent-violet); font-size: 12px; text-decoration: none;">Next →</a>
            @else
                <span style="padding: 6px 12px; border-radius: 8px; border: 1px solid var(--border-subtle); color: var(--text-dim); font-size: 12px; cursor: not-allowed;">Next →</span>
            @endif
        </div>
    </div>
    @endif
</div>

@endsection
