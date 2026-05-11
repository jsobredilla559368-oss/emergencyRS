@extends('layouts.admin')
@section('title', 'System Logs — Admin')
@section('page-title', 'System Logs')

@push('styles')
<style>
    :root {
        --color-orange: #F97316; --color-orange-bg: #FFF7ED; --color-orange-border: #FED7AA;
        --color-blue:   #3B82F6; --color-blue-bg:   #EFF6FF; --color-blue-border:   #BFDBFE;
        --color-cyan:   #06B6D4; --color-cyan-bg:   #ECFEFF; --color-cyan-border:   #A5F3FC;
        --color-amber:  #F59E0B; --color-amber-bg:  #FFFBEB; --color-amber-border:  #FDE68A;
        --color-green:  #10B981; --color-green-bg:  #ECFDF5; --color-green-border:  #A7F3D0;
        --color-violet: #8B5CF6; --color-violet-bg: #F5F3FF; --color-violet-border: #DDD6FE;
        --color-gray:   #64748B; --color-gray-bg:   #F8FAFC; --color-gray-border:   #E2E8F0;
    }
    .log-filters { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 20px; align-items: center; }
    .filter-chip {
        padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600;
        border: 1.5px solid var(--border); background: #fff; color: var(--text-muted);
        cursor: pointer; text-decoration: none; transition: all 0.15s;
    }
    .filter-chip:hover   { border-color: var(--accent); color: var(--accent); }
    .filter-chip.active  { background: var(--accent); border-color: var(--accent); color: #fff; }
    .log-search { flex: 1; min-width: 200px; height: 36px; padding: 0 14px; border: 1.5px solid var(--border); border-radius: 20px; font-size: 13px; font-family: inherit; outline: none; }
    .log-search:focus { border-color: var(--accent); }

    .log-timeline { display: flex; flex-direction: column; gap: 0; }
    .log-entry {
        display: flex; gap: 16px; padding: 14px 0;
        border-bottom: 1px solid var(--border);
        transition: background 0.1s;
    }
    .log-entry:last-child { border-bottom: none; }
    .log-dot-col { display: flex; flex-direction: column; align-items: center; padding-top: 4px; }
    .log-dot {
        width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;
        box-shadow: 0 0 0 3px white, 0 0 0 4px currentColor;
    }
    .log-dot.orange { color: var(--color-orange); background: var(--color-orange); }
    .log-dot.blue   { color: var(--color-blue);   background: var(--color-blue); }
    .log-dot.cyan   { color: var(--color-cyan);   background: var(--color-cyan); }
    .log-dot.amber  { color: var(--color-amber);  background: var(--color-amber); }
    .log-dot.green  { color: var(--color-green);  background: var(--color-green); }
    .log-dot.violet { color: var(--color-violet); background: var(--color-violet); }
    .log-dot.gray   { color: var(--color-gray);   background: var(--color-gray); }

    .log-line { flex: 1; width: 1px; background: var(--border); margin-top: 6px; }
    .log-body { flex: 1; min-width: 0; }
    .log-label-row { display: flex; align-items: center; gap: 8px; margin-bottom: 4px; flex-wrap: wrap; }
    .log-badge {
        font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 10px;
        text-transform: uppercase; letter-spacing: 0.4px; white-space: nowrap;
    }
    .log-badge.orange { background: var(--color-orange-bg); color: var(--color-orange); border: 1px solid var(--color-orange-border); }
    .log-badge.blue   { background: var(--color-blue-bg);   color: var(--color-blue);   border: 1px solid var(--color-blue-border); }
    .log-badge.cyan   { background: var(--color-cyan-bg);   color: var(--color-cyan);   border: 1px solid var(--color-cyan-border); }
    .log-badge.amber  { background: var(--color-amber-bg);  color: var(--color-amber);  border: 1px solid var(--color-amber-border); }
    .log-badge.green  { background: var(--color-green-bg);  color: var(--color-green);  border: 1px solid var(--color-green-border); }
    .log-badge.violet { background: var(--color-violet-bg); color: var(--color-violet); border: 1px solid var(--color-violet-border); }
    .log-badge.gray   { background: var(--color-gray-bg);   color: var(--color-gray);   border: 1px solid var(--color-gray-border); }

    .log-detail { font-size: 14px; font-weight: 600; color: #0F172A; margin-bottom: 2px; }
    .log-meta   { font-size: 12px; color: var(--text-muted); }
    .log-time   { font-size: 11px; color: #94A3B8; white-space: nowrap; margin-top: 2px; }
    .log-actor  { display: inline-flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 600; color: var(--text-muted); padding: 2px 8px; border-radius: 10px; background: #F1F5F9; }

    .pager { display: flex; align-items: center; justify-content: space-between; margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border); }
    .pager-btn { padding: 6px 14px; border-radius: 8px; border: 1px solid var(--border); font-size: 12px; font-weight: 600; text-decoration: none; color: var(--text-muted); transition: all 0.15s; }
    .pager-btn:hover { border-color: var(--accent); color: var(--accent); }
    .pager-btn.disabled { opacity: 0.4; pointer-events: none; }
</style>
@endpush

@section('content')

{{-- STATS ROW --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit,minmax(160px,1fr)); gap: 14px; margin-bottom: 24px;">
    @php
        $chips = [
            ['label'=>'All Events',       'count'=>$logTotals['all'],              'key'=>'all',              'color'=>'#2563EB'],
            ['label'=>'Incidents Created','count'=>$logTotals['incident_created'],  'key'=>'incident_created', 'color'=>'#F97316'],
            ['label'=>'Status Updates',   'count'=>$logTotals['status_update'],     'key'=>'status_update',    'color'=>'#06B6D4'],
            ['label'=>'User Signups',     'count'=>$logTotals['user_registered'],   'key'=>'user_registered',  'color'=>'#8B5CF6'],
        ];
    @endphp
    @foreach($chips as $chip)
    <div class="card" style="padding: 16px 20px; border-left: 4px solid {{ $chip['color'] }};">
        <div style="font-size: 26px; font-weight: 800; color: {{ $chip['color'] }};">{{ number_format($chip['count']) }}</div>
        <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">{{ $chip['label'] }}</div>
    </div>
    @endforeach
</div>

{{-- FILTER BAR --}}
<form method="GET" action="{{ route('admin.system-logs') }}" id="filter-form">
<div class="log-filters">
    @foreach([['all','All'],['incident_created','Incidents'],['status_update','Status Updates'],['user_registered','Signups']] as [$key,$label])
    <a href="{{ route('admin.system-logs', array_merge(request()->except(['type','page']), ['type'=>$key])) }}"
       class="filter-chip {{ ($filterType ?? 'all') === $key ? 'active' : '' }}">
        {{ $label }}
    </a>
    @endforeach

    <input type="hidden" name="type" value="{{ $filterType ?? 'all' }}">
    <input type="text" name="q" class="log-search" placeholder="Search logs…" value="{{ $filterSearch }}"
           oninput="clearTimeout(window._st); window._st=setTimeout(()=>document.getElementById('filter-form').submit(),500)">

    <input type="date" name="date" class="log-search" style="max-width:160px;" value="{{ $filterDate }}"
           onchange="document.getElementById('filter-form').submit()">

    @if($filterSearch || $filterDate)
    <a href="{{ route('admin.system-logs') }}" style="font-size:12px;color:var(--text-muted);text-decoration:none;">✕ Clear</a>
    @endif
</div>
</form>

{{-- LOG TIMELINE --}}
<div class="card" style="padding: 24px;">
    @if($paginator->isEmpty())
        <div style="text-align:center; padding: 60px 0; color: var(--text-muted);">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" style="width:48px;height:48px;margin-bottom:12px;opacity:0.3;"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg>
            <p>No log entries found.</p>
        </div>
    @else
    <div class="log-timeline">
        @foreach($paginator as $log)
        <div class="log-entry">
            <div class="log-dot-col">
                <div class="log-dot {{ $log['color'] }}"></div>
                <div class="log-line"></div>
            </div>
            <div class="log-body">
                <div class="log-label-row">
                    <span class="log-badge {{ $log['color'] }}">{{ $log['label'] }}</span>
                    <span class="log-actor">
                        {{ strtoupper(substr($log['actor'],0,1)) }} {{ $log['actor'] }}
                        <span style="opacity:0.6;">· {{ ucfirst($log['actor_role']) }}</span>
                    </span>
                </div>
                <div class="log-detail">{{ $log['detail'] }}</div>
                <div class="log-meta">{{ $log['meta'] }}</div>
                <div class="log-time">{{ \Carbon\Carbon::parse($log['time'])->format('M d, Y · H:i:s') }} — {{ \Carbon\Carbon::parse($log['time'])->diffForHumans() }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- PAGINATION --}}
    <div class="pager">
        <span style="font-size:12px;color:var(--text-muted);">
            Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }} events
        </span>
        <div style="display:flex;gap:6px;">
            <a href="{{ $paginator->previousPageUrl() ?? '#' }}" class="pager-btn {{ $paginator->onFirstPage() ? 'disabled' : '' }}">← Prev</a>
            <a href="{{ $paginator->nextPageUrl() ?? '#' }}"     class="pager-btn {{ !$paginator->hasMorePages() ? 'disabled' : '' }}">Next →</a>
        </div>
    </div>
    @endif
</div>

@endsection
