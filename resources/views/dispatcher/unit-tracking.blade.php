@extends('layouts.dispatcher')

@section('title', 'Unit Tracking — Dispatcher')
@section('page-title', 'Live Unit Tracking')

@push('styles')
<style>
    .tracking-layout {
        display: grid;
        grid-template-columns: 380px 1fr;
        gap: 20px;
        height: calc(100vh - 180px);
        min-height: 500px;
    }
    .unit-list-panel {
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 12px;
        padding-right: 4px;
    }
    .unit-list-panel::-webkit-scrollbar { width: 4px; }
    .unit-list-panel::-webkit-scrollbar-track { background: transparent; }
    .unit-list-panel::-webkit-scrollbar-thumb { background: var(--border-subtle); border-radius: 2px; }

    .unit-map-panel {
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid var(--border-subtle);
        position: relative;
    }
    #unit-map { width: 100%; height: 100%; }

    .unit-card {
        background: var(--bg-panel);
        border: 1.5px solid var(--border-subtle);
        border-radius: 14px;
        padding: 16px;
        cursor: pointer;
        transition: border-color 0.2s, transform 0.15s, box-shadow 0.2s;
    }
    .unit-card:hover { border-color: var(--accent-cyan); transform: translateY(-1px); box-shadow: 0 4px 20px rgba(0,0,0,0.3); }
    .unit-card.focused { border-color: var(--accent-cyan); box-shadow: 0 0 0 2px rgba(6,182,212,0.2); }

    .unit-avatar {
        width: 42px; height: 42px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px; font-weight: 800; flex-shrink: 0;
    }

    .live-dot {
        width: 8px; height: 8px; border-radius: 50%;
        display: inline-block; flex-shrink: 0;
    }
    .live-dot.live    { background: #10B981; animation: liveFlash 1.2s ease-in-out infinite; }
    .live-dot.offline { background: #475569; }
    .live-dot.stale   { background: #F59E0B; animation: liveFlash 2s ease-in-out infinite; }

    @keyframes liveFlash { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:0.5;transform:scale(1.4)} }

    .map-overlay-card {
        position: absolute;
        top: 16px; left: 16px;
        background: rgba(10,15,30,0.88);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 12px;
        padding: 12px 16px;
        z-index: 10;
        min-width: 180px;
    }
    .map-overlay-stat { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; font-size: 12px; }
    .map-overlay-stat:last-child { margin-bottom: 0; }
    .map-overlay-num { font-weight: 800; font-size: 15px; }

    .speed-badge {
        font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 6px;
        background: rgba(6,182,212,0.15); color: var(--accent-cyan);
        border: 1px solid rgba(6,182,212,0.3);
    }

    @media (max-width: 900px) {
        .tracking-layout { grid-template-columns: 1fr; height: auto; }
        .unit-map-panel  { height: 380px; }
    }
</style>
@endpush

@section('content')

{{-- ── HEADER STATS ── --}}
<div class="stats-row" style="margin-bottom: 20px;">
    <div class="stat-card total">
        <div class="stat-card-left">
            <div class="stat-card-num" id="stat-total">{{ $responders->count() }}</div>
            <div class="stat-card-label">Total Responders</div>
        </div>
        <div class="stat-card-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>
            </svg>
        </div>
    </div>
    <div class="stat-card dispatched">
        <div class="stat-card-left">
            <div class="stat-card-num" id="stat-live">—</div>
            <div class="stat-card-label">Broadcasting GPS</div>
        </div>
        <div class="stat-card-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path d="M3 12a9 9 0 1018 0A9 9 0 003 12zM12 8v4l3 3"/>
            </svg>
        </div>
    </div>
    <div class="stat-card pending">
        <div class="stat-card-left">
            <div class="stat-card-num" id="stat-stale">—</div>
            <div class="stat-card-label">Signal Weak / Offline</div>
        </div>
        <div class="stat-card-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
    </div>
    <div class="stat-card resolved">
        <div class="stat-card-left">
            <div class="stat-card-num" id="stat-refresh" style="font-size: 14px; color: var(--accent-green);">LIVE</div>
            <div class="stat-card-label">Last Refresh</div>
        </div>
        <div class="stat-card-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
        </div>
    </div>
</div>

{{-- ── MAIN SPLIT LAYOUT ── --}}
<div class="tracking-layout">

    {{-- UNIT CARDS PANEL --}}
    <div class="unit-list-panel" id="unit-list-panel">
        <div style="margin-bottom: 8px;">
            <h2 class="panel-title">Field Units</h2>
            <p class="panel-subtitle" style="margin-top: 4px;">Updates every 8 seconds</p>
        </div>

        {{-- Cards are rendered server-side initially, then updated by JS --}}
        @forelse($responders as $r)
        @php $isActive = $r->assignedIncidents->count() > 0; @endphp
        <div class="unit-card" id="unit-card-{{ $r->id }}" onclick="focusUnit({{ $r->id }})">
            <div style="display: flex; align-items: flex-start; gap: 12px;">
                <div class="unit-avatar"
                     style="background: {{ $isActive ? 'rgba(6,182,212,0.15)' : 'rgba(255,255,255,0.05)' }};
                            border: 1px solid {{ $isActive ? 'var(--accent-cyan)' : 'var(--border-subtle)' }};
                            color: {{ $isActive ? 'var(--accent-cyan)' : 'var(--text-dim)' }};">
                    {{ strtoupper(substr($r->name, 0, 1)) }}
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                        <p style="font-weight: 700; font-size: 14px; color: var(--text-primary);">{{ $r->name }}</p>
                        <span style="display: flex; align-items: center; gap: 5px; font-size: 11px; color: var(--text-dim);"
                              id="unit-status-{{ $r->id }}">
                            <span class="live-dot offline"></span>
                            <span>Awaiting GPS</span>
                        </span>
                    </div>

                    @if($isActive)
                        @foreach($r->assignedIncidents as $inc)
                        <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-subtle); border-radius: 8px; padding: 8px 10px; margin-bottom: 6px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                <span style="font-family: monospace; font-size: 11px; font-weight: 700; color: var(--accent-cyan);">
                                    INC-{{ str_pad($inc->id, 4, '0', STR_PAD_LEFT) }}
                                </span>
                                <span class="badge badge-{{ $inc->status }}" style="font-size: 10px;">
                                    {{ ucfirst(str_replace('_', ' ', $inc->status)) }}
                                </span>
                            </div>
                            <p style="font-size: 11px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                📍 {{ $inc->location_address ?? 'Unknown location' }}
                            </p>
                        </div>
                        @endforeach
                    @else
                        <p style="font-size: 12px; color: var(--text-dim); font-style: italic;">No active assignments</p>
                    @endif

                    {{-- Live GPS info injected by JS --}}
                    <div id="unit-gps-info-{{ $r->id }}" style="margin-top: 6px; display: none;
                         font-size: 11px; color: var(--text-dim); padding: 6px 8px;
                         background: rgba(6,182,212,0.05); border-radius: 8px; border: 1px solid rgba(6,182,212,0.15);">
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="glass-card" style="text-align: center; padding: 40px;">
            <p style="color: var(--text-dim);">No responders registered.</p>
        </div>
        @endforelse
    </div>

    {{-- MAP PANEL --}}
    <div class="unit-map-panel">
        <div id="unit-map"></div>

        {{-- Overlay stats on map --}}
        <div class="map-overlay-card">
            <div class="map-overlay-stat">
                <span style="color: var(--text-dim);">Live Units</span>
                <span class="map-overlay-num" id="map-live-count" style="color: #10B981;">0</span>
            </div>
            <div class="map-overlay-stat">
                <span style="color: var(--text-dim);">Last Update</span>
                <span style="color: var(--text-primary); font-size: 12px;" id="map-last-update">—</span>
            </div>
            <div class="map-overlay-stat" style="margin-top: 8px; padding-top: 8px; border-top: 1px solid var(--border-subtle);">
                <div style="display:flex; align-items:center; gap:6px;">
                    <div class="live-dot live"></div>
                    <span style="color: var(--text-dim);">Live Feed Active</span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
mapboxgl.accessToken = '{{ config('services.mapbox.access_token') }}';

const map = new mapboxgl.Map({
    container: 'unit-map',
    style: 'mapbox://styles/mapbox/dark-v11',
    center: [120.9842, 14.5995],
    zoom: 11,
    pitch: 40,
});
map.addControl(new mapboxgl.NavigationControl(), 'bottom-right');

// Incident pins from server data (static anchor points)
const incidentData = @json($mapData);
const typeColors = {
    medical: '#EF4444', fire: '#F97316', crime: '#3B82F6', disaster: '#F59E0B',
    accident: '#6366F1', flood: '#14B8A6', earthquake: '#8B5CF6', hazmat: '#EAB308',
    missing_person: '#EC4899', others: '#94A3B8',
};

incidentData.forEach(inc => {
    if (!inc.lat || !inc.lng) return;
    const el = document.createElement('div');
    el.style.cssText = `
        width:12px; height:12px; border-radius:50%;
        background:${typeColors[inc.type]||'#6B7280'};
        border:2px solid rgba(255,255,255,0.4);
        box-shadow:0 0 8px ${typeColors[inc.type]||'#6B7280'}80;
    `;
    new mapboxgl.Marker(el)
        .setLngLat([inc.lng, inc.lat])
        .setPopup(new mapboxgl.Popup({offset:14}).setHTML(`
            <div style="font-family:Inter,sans-serif;min-width:160px;">
                <div style="font-size:11px;color:rgba(255,255,255,0.5);margin-bottom:2px;">${inc.display_id}</div>
                <div style="font-size:13px;font-weight:700;color:#fff;margin-bottom:6px;">Incident Site</div>
                <div style="font-size:11px;color:rgba(255,255,255,0.7);">📍 ${inc.address||'Unknown'}</div>
            </div>
        `))
        .addTo(map);
});

/* ═══════════════════════════════════
   REAL-TIME RESPONDER TRACKING
   Polls /dispatcher/live-locations every 8s
═══════════════════════════════════ */
const POLL_URL   = '{{ route('dispatcher.live-locations') }}';
const POLL_MS    = 8000;
const liveMarkers = {}; // keyed by responder_id
let focused = null;

function buildMarkerEl(name, isStale) {
    const el = document.createElement('div');
    el.style.cssText = `
        width: 40px; height: 40px; border-radius: 50%;
        background: ${isStale ? '#475569' : '#06B6D4'};
        border: 3px solid ${isStale ? '#64748B' : '#fff'};
        display: flex; align-items: center; justify-content: center;
        font-size: 14px; font-weight: 800; color: #fff; cursor: pointer;
        box-shadow: 0 0 ${isStale ? '4px rgba(0,0,0,0.3)' : '16px rgba(6,182,212,0.5)'};
        transition: all 0.3s;
        position: relative;
    `;
    el.textContent = name.charAt(0).toUpperCase();

    // Pulse ring for live markers
    if (!isStale) {
        const ring = document.createElement('div');
        ring.style.cssText = `
            position: absolute; inset: -6px; border-radius: 50%;
            border: 2px solid rgba(6,182,212,0.4);
            animation: unitPulse 1.5s ease-out infinite;
        `;
        el.appendChild(ring);
        const style = document.createElement('style');
        style.textContent = `@keyframes unitPulse{0%{transform:scale(1);opacity:0.8}100%{transform:scale(1.8);opacity:0}}`;
        if (!document.getElementById('unit-pulse-style')) {
            style.id = 'unit-pulse-style';
            document.head.appendChild(style);
        }
    }
    return el;
}

function buildPopupHtml(unit) {
    const speedStr = unit.speed ? `<br>⚡ ${unit.speed} km/h` : '';
    const accStr   = unit.accuracy ? ` ±${Math.round(unit.accuracy)}m` : '';
    const incStr   = unit.display_id
        ? `<div style="margin-top:8px;padding-top:8px;border-top:1px solid rgba(255,255,255,0.1);">
               Mission: <strong style="color:#06B6D4;">${unit.display_id}</strong><br>
               <span style="color:rgba(255,255,255,0.6);">${unit.incident_addr||''}</span>
           </div>`
        : '';

    return `
        <div style="font-family:Inter,sans-serif;min-width:200px;">
            <div style="font-size:14px;font-weight:800;color:#fff;margin-bottom:4px;">🧑‍🚒 ${unit.responder_name}</div>
            <div style="font-size:11px;color:rgba(255,255,255,0.5);">
                📍 ${unit.latitude.toFixed(5)}, ${unit.longitude.toFixed(5)}${accStr}
                ${speedStr}
                <br>🕐 ${unit.last_seen}
            </div>
            ${incStr}
            ${unit.incident_id ? `
                <a href="/dispatcher/incident/${unit.incident_id}"
                   style="display:block;text-align:center;margin-top:10px;padding:6px;background:#8B5CF6;color:#fff;border-radius:6px;font-size:11px;font-weight:700;text-decoration:none;">
                    OPEN MISSION CONTROL
                </a>` : ''}
        </div>
    `;
}

function pollLocations() {
    fetch(POLL_URL, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(units => {
            const now = new Date().toLocaleTimeString();
            let liveCount = 0;

            // Track which responders appear in this poll
            const seenIds = new Set();

            units.forEach(unit => {
                seenIds.add(unit.responder_id);
                const isStale = unit.is_stale;
                if (!isStale) liveCount++;

                if (liveMarkers[unit.responder_id]) {
                    // Update existing marker position
                    liveMarkers[unit.responder_id].marker.setLngLat([unit.longitude, unit.latitude]);
                    liveMarkers[unit.responder_id].popup.setHTML(buildPopupHtml(unit));
                    // Update marker appearance if stale state changed
                    const el = liveMarkers[unit.responder_id].el;
                    el.style.background = isStale ? '#475569' : '#06B6D4';
                    el.style.borderColor = isStale ? '#64748B' : '#fff';
                    el.style.boxShadow = isStale ? '0 0 4px rgba(0,0,0,0.3)' : '0 0 16px rgba(6,182,212,0.5)';
                } else {
                    // Create new marker
                    const el = buildMarkerEl(unit.responder_name, isStale);
                    const popup = new mapboxgl.Popup({ offset: 24 }).setHTML(buildPopupHtml(unit));
                    const marker = new mapboxgl.Marker(el)
                        .setLngLat([unit.longitude, unit.latitude])
                        .setPopup(popup)
                        .addTo(map);

                    el.addEventListener('click', () => {
                        focusUnit(unit.responder_id, unit.longitude, unit.latitude);
                    });

                    liveMarkers[unit.responder_id] = { marker, popup, el };
                }

                // Update card GPS info
                const gpsInfo = document.getElementById('unit-gps-info-' + unit.responder_id);
                if (gpsInfo) {
                    gpsInfo.style.display = 'block';
                    gpsInfo.innerHTML = `
                        <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;">
                            <span class="live-dot ${isStale ? 'stale' : 'live'}"></span>
                            <strong style="color:${isStale ? '#F59E0B' : '#10B981'};">${isStale ? 'Signal Stale' : 'Broadcasting Live'}</strong>
                        </div>
                        <div style="display:flex;gap:10px;flex-wrap:wrap;">
                            <span>📍 ${unit.latitude.toFixed(4)}, ${unit.longitude.toFixed(4)}</span>
                            ${unit.speed ? `<span class="speed-badge">${unit.speed} km/h</span>` : ''}
                        </div>
                        <div style="margin-top:3px;">🕐 ${unit.last_seen}</div>
                    `;
                }

                // Update card status badge
                const statusEl = document.getElementById('unit-status-' + unit.responder_id);
                if (statusEl) {
                    statusEl.innerHTML = `
                        <span class="live-dot ${isStale ? 'stale' : 'live'}"></span>
                        <span style="color:${isStale ? '#F59E0B' : '#10B981'};">${isStale ? 'Stale' : 'Live'}</span>
                    `;
                }
            });

            // Remove markers for responders no longer reporting
            Object.keys(liveMarkers).forEach(id => {
                if (!seenIds.has(parseInt(id))) {
                    liveMarkers[id].marker.remove();
                    delete liveMarkers[id];
                    const statusEl = document.getElementById('unit-status-' + id);
                    if (statusEl) statusEl.innerHTML = `<span class="live-dot offline"></span><span>Offline</span>`;
                    const gpsInfo = document.getElementById('unit-gps-info-' + id);
                    if (gpsInfo) gpsInfo.style.display = 'none';
                }
            });

            // Update stats
            document.getElementById('stat-live').textContent = liveCount;
            document.getElementById('stat-stale').textContent = units.length - liveCount;
            document.getElementById('stat-refresh').textContent = now;
            document.getElementById('map-live-count').textContent = liveCount;
            document.getElementById('map-last-update').textContent = now;
        })
        .catch(err => console.error('Location poll failed:', err));
}

function focusUnit(responderId, lng, lat) {
    // Remove previous focus
    document.querySelectorAll('.unit-card').forEach(c => c.classList.remove('focused'));
    const card = document.getElementById('unit-card-' + responderId);
    if (card) {
        card.classList.add('focused');
        card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // Fly map to unit if coords provided; else use last known from marker
    if (lng && lat) {
        map.flyTo({ center: [lng, lat], zoom: 16, speed: 1.5, pitch: 50 });
        if (liveMarkers[responderId]) liveMarkers[responderId].marker.getPopup().addTo(map);
    } else if (liveMarkers[responderId]) {
        const lngLat = liveMarkers[responderId].marker.getLngLat();
        map.flyTo({ center: [lngLat.lng, lngLat.lat], zoom: 16, speed: 1.5, pitch: 50 });
        liveMarkers[responderId].marker.getPopup().addTo(map);
    }
}

// Initial poll + recurring
pollLocations();
setInterval(pollLocations, POLL_MS);
</script>
@endpush
