@extends('layouts.responder')

@section('title', 'Responder Dashboard — EmergencyRS')
@section('page-title', 'Active Assignments')

@push('styles')
<style>
    .mapboxgl-popup { z-index: 3000; }
    .mapboxgl-popup-content { background: var(--bg-panel); border: 1px solid var(--border-subtle); color: #fff; border-radius: 12px; padding: 12px; }
</style>
@endpush

@section('content')

{{-- ── STATS ROW ── --}}
<div class="stats-row">

    <div class="stat-card total">
        <div class="stat-card-left">
            <div class="stat-card-num">{{ $totalActive }}</div>
            <div class="stat-card-label">Total Active</div>
        </div>
        <div class="stat-card-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
        </div>
    </div>

    <div class="stat-card pending">
        <div class="stat-card-left">
            <div class="stat-card-num">{{ $pending }}</div>
            <div class="stat-card-label">Pending</div>
        </div>
        <div class="stat-card-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <path d="M12 6v6l4 2"/>
            </svg>
        </div>
    </div>

    <div class="stat-card dispatched">
        <div class="stat-card-left">
            <div class="stat-card-num">{{ $dispatched }}</div>
            <div class="stat-card-label">Dispatched</div>
        </div>
        <div class="stat-card-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 9m0 8V9m0 0L9 7"/>
            </svg>
        </div>
    </div>

    <div class="stat-card resolved">
        <div class="stat-card-left">
            <div class="stat-card-num">{{ $resolvedToday }}</div>
            <div class="stat-card-label">Resolved Today</div>
        </div>
        <div class="stat-card-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
    </div>
</div>

<div class="sr-only" aria-live="polite" id="a11y-announcer"></div>

{{-- ── FILTER BAR ── --}}
<div class="filter-bar" role="search" aria-label="Filter incidents">
    <div class="filter-search-wrap">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
        </svg>
        <input type="text" class="filter-search" placeholder="Search incidents…" id="search-input" oninput="filterCards()">
    </div>

    <select class="filter-select" id="filter-type" onchange="filterCards()">
        <option value="">All Types</option>
        <option value="medical">Medical</option>
        <option value="fire">Fire</option>
        <option value="crime">Crime</option>
        <option value="disaster">Disaster</option>
        <option value="accident">Accident</option>
        <option value="flood">Flood</option>
        <option value="earthquake">Earthquake</option>
        <option value="hazmat">HazMat</option>
        <option value="missing_person">Missing Person</option>
        <option value="others">Others</option>
    </select>

    <select class="filter-select" id="filter-severity" onchange="filterCards()">
        <option value="">All Severity</option>
        <option value="high">High</option>
        <option value="medium">Medium</option>
        <option value="low">Low</option>
    </select>

    <select class="filter-select" id="filter-status" onchange="filterCards()">
        <option value="">All Status</option>
        <option value="pending">Pending</option>
        <option value="dispatched">Dispatched</option>
        <option value="resolved">Resolved</option>
    </select>
</div>

{{-- ── MAIN GRID: MAP + LIST ── --}}
<div class="dashboard-grid">

    {{-- MAP --}}
    <div class="map-panel" role="region" aria-label="Live Incident Map">
        <div class="panel-header">
            <div>
                <h2 class="panel-title">Live Incident Map</h2>
                <div class="panel-subtitle">Click a marker to view details</div>
            </div>
            <span class="badge badge-pending" style="font-size:11px;">
                <span style="width:7px;height:7px;background:#DC2626;border-radius:50%;display:inline-block;animation:pulse 1.5s infinite;"></span>
                Live
            </span>
        </div>
        <div class="map-container">
            <div id="responder-map" style="width:100%;height:500px;" aria-label="Interactive map showing incidents"></div>
        </div>
    </div>

    {{-- INCIDENT LIST --}}
    <div class="incident-panel" role="region" aria-label="Incident List">
        <div class="panel-header">
            <div>
                <h2 class="panel-title">Active Incidents</h2>
                <div class="panel-subtitle">{{ $incidents->count() }} total &middot; sorted by recency</div>
            </div>
        </div>

        <div class="incident-list" id="incident-list">

            @if(auth()->user()->role === 'responder' && $myQueue->count() > 0)
                <div style="margin-bottom: 24px;">
                    <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; color: var(--accent-cyan); font-weight: 800; margin-bottom: 12px; padding: 0 4px;">My Active Assignments ({{ $myQueue->count() }})</div>
                    @foreach($myQueue as $qi)
                        <a href="{{ route('responder.incident', $qi->id) }}"
                           class="incident-card"
                           style="border-color: var(--accent-cyan); background: rgba(6, 182, 212, 0.05);"
                           id="card-{{ $qi->id }}">
                           
                            <div class="incident-card-top">
                                <div class="incident-card-meta">
                                    <span class="badge badge-{{ $qi->type }}">{{ ucfirst($qi->type) }}</span>
                                    <span class="sev-dot sev-{{ $qi->severity }}"></span>
                                </div>
                                <span class="badge badge-{{ $qi->status }}">{{ ucfirst($qi->status) }}</span>
                            </div>

                            <p class="incident-id">INC-{{ str_pad($qi->id, 4, '0', STR_PAD_LEFT) }}</p>
                            <p class="incident-desc">{{ $qi->description }}</p>

                            <div class="incident-location">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{ $qi->location_address ?? 'Location coordinates provided' }}
                            </div>
                        </a>
                    @endforeach
                </div>
                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; color: var(--text-dim); font-weight: 800; margin-bottom: 12px; padding: 0 4px;">All Incidents</div>
            @endif

            @forelse ($incidents as $i)
            <a href="{{ route('responder.incident', $i->id) }}"
               class="incident-card"
               data-type="{{ $i->type }}"
               data-severity="{{ $i->severity }}"
               data-status="{{ $i->status }}"
               data-lat="{{ $i->latitude }}"
               data-lng="{{ $i->longitude }}"
               id="card-{{ $i->id }}">

                <div class="incident-card-top">
                    <div class="incident-card-meta">
                        <span class="badge badge-{{ $i->type }}">{{ ucfirst($i->type) }}</span>
                        <span class="sev-dot sev-{{ $i->severity }}"></span>
                    </div>
                    <span class="badge badge-{{ $i->status }}">{{ ucfirst($i->status) }}</span>
                </div>

                <p class="incident-id">INC-{{ str_pad($i->id, 4, '0', STR_PAD_LEFT) }}</p>
                <p class="incident-desc">{{ $i->description }}</p>

                <div class="incident-location">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    {{ $i->location_address ?? 'Location coordinates provided' }} &middot; {{ $i->created_at->diffForHumans() }}
                </div>
            </a>
            @empty
            <div style="padding: 20px; text-align: center; color: var(--text-muted); font-size: 13px;">
                No active incidents reported.
            </div>
            @endforelse
        </div>
    </div>
</div>

<style>
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50%       { opacity: 0.4; }
}
</style>

@endsection

@push('scripts')
<script src="https://api.mapbox.com/mapbox-gl-js/v3.0.0/mapbox-gl.js"></script>
<link href="https://api.mapbox.com/mapbox-gl-js/v3.0.0/mapbox-gl.css" rel="stylesheet">
<script>
/* ── Mapbox Setup ── */
mapboxgl.accessToken = '{{ config('services.mapbox.access_token') }}';

const map = new mapboxgl.Map({
    container: 'responder-map',
    style: 'mapbox://styles/mapbox/dark-v11',
    center: [120.9842, 14.5995],
    zoom: 12,
    pitch: 45
});

map.addControl(new mapboxgl.NavigationControl());

const typeColors = {
    medical:  '#DC2626', fire: '#EA580C', crime: '#2563EB', disaster: '#D97706',
    accident: '#4F46E5', flood: '#0D9488', earthquake:'#7C3AED', hazmat: '#CA8A04',
    missing_person: '#DB2777', others: '#475569',
};

const mappedIncidents = @json($mapData);
const markers = {};

mappedIncidents.forEach(inc => {
    if (!inc.lat || !inc.lng) return;

    const el = document.createElement('div');
    el.className = 'custom-marker';
    el.style.backgroundColor = typeColors[inc.type] || '#6B7280';
    el.style.width = '24px';
    el.style.height = '24px';
    el.style.borderRadius = '50% 50% 50% 0';
    el.style.transform = 'rotate(-45deg)';
    el.style.border = '2px solid white';
    el.style.cursor = 'pointer';

    const popupContent = `
        <div style="font-family:Inter,sans-serif; min-width:180px;">
            <div style="font-size:11px;font-weight:700;color:var(--text-dim);margin-bottom:4px;">${inc.display_id}</div>
            <div style="font-size:13px;font-weight:600;color:#fff;line-height:1.4;">${inc.desc}</div>
            <div style="margin-top:10px; border-top:1px solid var(--border-subtle); padding-top:8px;">
                <a href="/responder/incident/${inc.id}" 
                   style="display:block;text-align:center;padding:6px;background:var(--accent-cyan);color:#fff;
                          border-radius:6px;font-size:11px;font-weight:700;text-decoration:none;">
                    VIEW ASSIGNMENT
                </a>
            </div>
        </div>
    `;

    const marker = new mapboxgl.Marker(el)
        .setLngLat([inc.lng, inc.lat])
        .setPopup(new mapboxgl.Popup({ offset: 25 }).setHTML(popupContent))
        .addTo(map);

    el.addEventListener('click', () => highlightCard(inc.id));
    markers[inc.id] = marker;
});

function highlightCard(id) {
    document.querySelectorAll('.incident-card').forEach(c => c.classList.remove('highlighted'));
    const card = document.getElementById('card-' + id);
    if (card) {
        card.classList.add('highlighted');
        card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

function filterCards() {
    const q    = document.getElementById('search-input').value.toLowerCase();
    const type = document.getElementById('filter-type').value;
    const sev  = document.getElementById('filter-severity').value;
    const stat = document.getElementById('filter-status').value;
    let visibleCount = 0;

    document.querySelectorAll('.incident-card').forEach(card => {
        const t = card.dataset.type;
        const s = card.dataset.severity;
        const st = card.dataset.status;
        const text = card.textContent.toLowerCase();
        const id = card.id.replace('card-', '');

        const show = (!q || text.includes(q))
                  && (!type || t === type)
                  && (!sev  || s === sev)
                  && (!stat || st === stat);

        card.style.display = show ? 'block' : 'none';
        if (show) visibleCount++;

        if (markers[id]) {
            show ? markers[id].addTo(map) : markers[id].remove();
        }
    });
    
    document.getElementById('a11y-announcer').textContent = `Showing ${visibleCount} incidents after filtering.`;
}
</script>
@endpush
