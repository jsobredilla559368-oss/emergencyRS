@extends('layouts.responder')

@section('title', 'Incident #' . str_pad($incident->id, 4, '0', STR_PAD_LEFT) . ' — EmergencyRS')
@section('page-title', 'Incident Details')

@section('content')
<div class="detail-container">
    
    {{-- ── LEFT: MAIN INFO ── --}}
    <div class="detail-main">
        
        <div class="glass-card">
            <div class="detail-header">
                <div>
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                        <span class="badge badge-{{ $incident->type }}">{{ ucfirst($incident->type) }}</span>
                        <span class="badge badge-{{ $incident->status }}">{{ ucfirst($incident->status) }}</span>
                    </div>
                    <h2 style="font-family: var(--font-heading); font-size: 24px; font-weight: 800; color: var(--text-primary);">
                        INC-{{ str_pad($incident->id, 4, '0', STR_PAD_LEFT) }}
                    </h2>
                </div>
                <div style="text-align: right;">
                    <p class="section-label">Reported At</p>
                    <p style="font-size: 14px; font-weight: 600;">{{ $incident->created_at->format('M d, Y — H:i') }}</p>
                </div>
            </div>
        </div>

        {{-- Description --}}
        <div class="glass-card">
            <h3 class="section-label">Emergency Description</h3>
            <div class="description-box">
                {{ $incident->description }}
            </div>
        </div>

        {{-- Media Gallery --}}
        <div class="glass-card">
            <h3 class="section-label">Media Evidence</h3>
            @if($incident->media->count() > 0)
                <div class="media-grid">
                    @foreach($incident->media as $m)
                        <div class="media-item">
                            @if($m->type === 'image')
                                <img src="{{ Storage::url($m->file_path) }}" alt="Evidence">
                            @else
                                <video src="{{ Storage::url($m->file_path) }}" controls></video>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="empty-state">No media attached to this report.</p>
            @endif
        </div>

        {{-- Status History --}}
        <div class="glass-card">
            <h3 class="section-label">Status Updates & Timeline</h3>
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-dot active"></div>
                    <div class="timeline-content">
                        <p class="timeline-title">Report Created</p>
                        <p class="timeline-time">{{ $incident->created_at->diffForHumans() }}</p>
                        <p class="timeline-text">Initial emergency report submitted by reporter.</p>
                    </div>
                </div>
                @foreach($incident->statusUpdates as $update)
                    <div class="timeline-item">
                        <div class="timeline-dot active"></div>
                        <div class="timeline-content">
                            <p class="timeline-title">Status: {{ ucfirst($update->status) }}</p>
                            <p class="timeline-time">{{ $update->created_at->diffForHumans() }}</p>
                            <p class="timeline-text">{{ $update->note }}</p>
                            <p style="font-size: 11px; color: var(--text-dim); margin-top: 4px;">Updated by {{ $update->updatedBy->name }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── RIGHT: SIDEBAR INFO ── --}}
    <div class="detail-side">
        
        {{-- Location Card --}}
        <div class="glass-card side-card">
            <h3 class="section-label">Precise Location</h3>
            <div id="mini-map" style="height: 180px; border-radius: 12px; margin-bottom: 12px; border: 1px solid var(--border-subtle);"></div>
            <p style="font-size: 13px; color: var(--text-primary); margin-bottom: 4px; font-weight: 600;">
                {{ $incident->location_address ?? 'Address unmapped' }}
            </p>
            <p style="font-size: 11px; color: var(--text-dim);">GPS: {{ $incident->latitude }}, {{ $incident->longitude }}</p>
            <a href="https://www.google.com/maps?q={{ $incident->latitude }},{{ $incident->longitude }}" target="_blank" 
               style="display: block; text-align: center; padding: 10px; border-radius: 10px; border: 1px solid var(--border-subtle); color: var(--text-primary); text-decoration: none; font-size: 13px; font-weight: 600; margin-top: 12px; transition: all 0.2s;">
                Open in Google Maps
            </a>
        </div>

        {{-- Reporter Info --}}
        <div class="glass-card side-card">
            <h3 class="section-label">Reporter Info</h3>
            <div class="reporter-box">
                <div class="sidebar-avatar" style="width:40px; height:40px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);">
                    {{ strtoupper(substr($incident->reporter->name ?? 'A', 0, 1)) }}
                </div>
                <div>
                    <p style="font-weight: 700; font-size: 14px;">{{ $incident->reporter->name ?? 'Anonymous' }}</p>
                    <p style="font-size: 11px; color: var(--text-dim); text-transform: uppercase;">{{ $incident->reporter->is_guest ? 'Guest Reporter' : 'Registered User' }}</p>
                </div>
            </div>
            
            <div style="margin-top: 16px; border-top: 1px solid var(--border-subtle); padding-top: 16px;">
                <p class="section-label" style="margin-bottom: 8px;">Credibility Score</p>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <span style="font-size: 14px; font-weight: 600;">{{ $incident->credibility_score }} / 100</span>
                    <span class="badge badge-{{ strtolower($incident->credibility_label) === 'high' ? 'resolved' : (strtolower($incident->credibility_label) === 'medium' ? 'dispatched' : 'pending') }}">
                        {{ $incident->credibility_label }}
                    </span>
                </div>
                <div style="height: 6px; background: var(--bg-card); border-radius: 3px; overflow: hidden; margin-bottom: 12px;">
                    <div style="height: 100%; width: {{ $incident->credibility_score }}%; background: {{ $incident->credibility_score >= 70 ? 'var(--accent-green)' : ($incident->credibility_score >= 40 ? 'var(--accent-amber)' : 'var(--accent-red)') }};"></div>
                </div>
                <ul style="font-size: 12px; color: var(--text-dim); list-style: none; padding: 0; line-height: 1.6;">
                    <li>{!! !$incident->reporter->is_guest ? '<span style="color:var(--accent-green)">✓</span> Registered User' : '<span style="color:var(--accent-red)">✗</span> Guest User' !!}</li>
                    <li>{!! ($incident->media->count() > 0) ? '<span style="color:var(--accent-green)">✓</span> Media Attached' : '<span style="color:var(--accent-red)">✗</span> No Media' !!}</li>
                    <li>{!! ($incident->latitude && $incident->longitude) ? '<span style="color:var(--accent-green)">✓</span> Location Confirmed' : '<span style="color:var(--accent-red)">✗</span> No GPS' !!}</li>
                </ul>
            </div>
        </div>

        {{-- Actions --}}
        <div class="glass-card side-card actions-card">
            <h3 class="section-label">Field Actions</h3>
            
            @if(!$incident->responder_id)
                <div class="action-field" style="margin-bottom: 12px; padding: 12px; background: rgba(255,255,255,0.05); border-radius: 8px; border: 1px dashed var(--border-subtle);">
                    <p style="font-size: 11px; color: var(--text-dim); text-transform: uppercase;">Assignment Pending</p>
                    <p style="font-size: 14px; font-weight: 600; color: var(--text-muted);">Waiting for Dispatcher...</p>
                </div>
            @elseif($incident->responder_id == auth()->id())
                <div class="action-field" style="margin-bottom: 12px; padding: 12px; background: rgba(6, 182, 212, 0.1); border-radius: 8px; border: 1px solid var(--accent-cyan);">
                    <p style="font-size: 11px; color: var(--accent-cyan); text-transform: uppercase; font-weight: 800;">Assigned To You</p>
                    <p style="font-size: 14px; font-weight: 600;">Active Response in Progress</p>
                </div>
            @else
                <div class="action-field" style="margin-bottom: 12px; padding: 12px; background: rgba(255,255,255,0.05); border-radius: 8px;">
                    <p style="font-size: 11px; color: var(--text-dim); text-transform: uppercase;">Assigned To</p>
                    <p style="font-size: 14px; font-weight: 600;">{{ $incident->responder->name ?? 'Unknown' }}</p>
                </div>
            @endif

            <div class="divider"></div>

            @if($incident->status !== 'resolved' && ($incident->responder_id == auth()->id()))
                {{-- Quick Actions --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 16px;">
                    <form action="{{ route('incidents.status-updates.store', $incident->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="en_route">
                        <input type="hidden" name="notes" value="Unit is currently en route to the location.">
                        <button type="submit" class="secondary-action-btn" style="padding: 8px; font-size: 11px; border-color: var(--accent-cyan); color: var(--accent-cyan);">
                            En Route
                        </button>
                    </form>
                    <form action="{{ route('incidents.status-updates.store', $incident->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="arrived">
                        <input type="hidden" name="notes" value="Unit has arrived at the scene.">
                        <button type="submit" class="secondary-action-btn" style="padding: 8px; font-size: 11px; border-color: var(--accent-amber); color: var(--accent-amber);">
                            Arrived
                        </button>
                    </form>
                </div>

                {{-- Detailed Progress Report --}}
                <form action="{{ route('incidents.status-updates.store', $incident->id) }}" method="POST" style="margin-bottom: 24px;">
                    @csrf
                    <input type="hidden" name="status" value="{{ $incident->status }}">
                    <div class="action-field">
                        <label>Report Progress to Dispatcher</label>
                        <textarea name="notes" class="action-select" style="height: 80px; padding: 10px; resize: vertical;" placeholder="Type your sitrep here..." required></textarea>
                    </div>
                    <button type="submit" class="primary-action-btn" style="background: var(--accent-blue); padding: 10px; font-size: 14px;">
                        Send Update
                    </button>
                </form>

                <div class="divider"></div>

                {{-- Final Resolution --}}
                <form action="{{ route('incidents.status-updates.store', $incident->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="resolved">
                    <div class="action-field">
                        <label>Final Resolution Notes</label>
                        <textarea name="notes" class="action-select" style="height: 60px; padding: 10px; resize: vertical;" placeholder="Final mission summary..." required></textarea>
                    </div>
                    <button type="submit" class="secondary-action-btn" style="border-color: var(--accent-green); color: var(--accent-green);">
                        Complete & Resolve
                    </button>
                </form>
            @elseif($incident->status === 'resolved')
                <div style="text-align: center; padding: 12px; background: rgba(16, 185, 129, 0.1); color: var(--accent-green); border-radius: 12px; font-weight: 600;">
                    ✓ Mission Completed
                </div>
            @endif
        </div>
    </div>
</div>

</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
@endpush

@push('scripts')
<script>
    mapboxgl.accessToken = '{{ config('services.mapbox.access_token') }}';
    
    const map = new mapboxgl.Map({
        container: 'mini-map',
        style: 'mapbox://styles/mapbox/dark-v11',
        center: [{{ $incident->longitude }}, {{ $incident->latitude }}],
        zoom: 14,
        interactive: false,
        attributionControl: false
    });
    
    const typeColors = { medical: '#EF4444', fire: '#F97316', crime: '#3B82F6', disaster: '#F59E0B', accident: '#6366F1', flood: '#14B8A6', earthquake: '#8B5CF6', hazmat: '#EAB308', missing_person: '#EC4899', others: '#94A3B8' };
    const color = typeColors['{{ $incident->type }}'] || '#6B7280';
    
    // Incident pin
    new mapboxgl.Marker({ color: color })
        .setLngLat([{{ $incident->longitude }}, {{ $incident->latitude }}])
        .addTo(map);

    /* ═══════════════════════════════════════════
       LIVE GPS SHARING — Responder → Dispatcher
       Sends position every 8 seconds while active
    ═══════════════════════════════════════════ */
    @if(!in_array($incident->status, ['resolved']))
    const CSRF_TOKEN  = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const INCIDENT_ID = {{ $incident->id }};
    const LOC_URL     = '{{ route('responder.location.update') }}';

    let selfMarker = null;
    let watchId    = null;
    let sharingActive = false;

    const statusIndicator = document.createElement('div');
    statusIndicator.id = 'gps-status';
    statusIndicator.style.cssText = `
        position: fixed; bottom: 24px; right: 24px; z-index: 9999;
        padding: 10px 16px; border-radius: 12px; font-size: 12px; font-weight: 700;
        display: flex; align-items: center; gap: 8px;
        background: rgba(15,23,42,0.9); border: 1px solid rgba(255,255,255,0.1);
        backdrop-filter: blur(8px); color: #94A3B8;
        transition: all 0.3s; box-shadow: 0 8px 24px rgba(0,0,0,0.4);
    `;
    statusIndicator.innerHTML = `<span id="gps-dot" style="width:8px;height:8px;border-radius:50%;background:#94A3B8;"></span><span id="gps-label">GPS: Initializing…</span>`;
    document.body.appendChild(statusIndicator);

    function setGpsStatus(state) {
        const dot   = document.getElementById('gps-dot');
        const label = document.getElementById('gps-label');
        const config = {
            active:  { color: '#10B981', text: 'GPS: Broadcasting Live',  border: 'rgba(16,185,129,0.3)',  pulse: true },
            stale:   { color: '#F59E0B', text: 'GPS: Signal Weak',        border: 'rgba(245,158,11,0.3)',  pulse: false },
            error:   { color: '#EF4444', text: 'GPS: Access Denied',      border: 'rgba(239,68,68,0.3)',   pulse: false },
            sending: { color: '#06B6D4', text: 'GPS: Syncing…',           border: 'rgba(6,182,212,0.3)',   pulse: false },
        };
        const c = config[state] || config.stale;
        dot.style.background = c.color;
        label.style.color    = c.color;
        label.textContent    = c.text;
        statusIndicator.style.borderColor = c.border;
        dot.style.animation  = c.pulse ? 'gpsPulse 1.2s ease-in-out infinite' : 'none';
    }

    // Inject pulse animation
    const style = document.createElement('style');
    style.textContent = `@keyframes gpsPulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:0.4;transform:scale(1.3)} }`;
    document.head.appendChild(style);

    function sendLocation(position) {
        const { latitude, longitude, accuracy, heading, speed } = position.coords;
        setGpsStatus('sending');

        // Update own marker on the incident map
        if (!selfMarker) {
            const el = document.createElement('div');
            el.style.cssText = 'width:18px;height:18px;border-radius:50%;background:#06B6D4;border:3px solid #fff;box-shadow:0 0 12px rgba(6,182,212,0.6);';
            selfMarker = new mapboxgl.Marker(el).setLngLat([longitude, latitude]).addTo(map);
        } else {
            selfMarker.setLngLat([longitude, latitude]);
        }

        fetch(LOC_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                latitude,
                longitude,
                accuracy,
                heading:     heading     || null,
                speed:       speed       || null,
                incident_id: INCIDENT_ID,
            }),
        })
        .then(r => r.json())
        .then(() => { setGpsStatus('active'); sharingActive = true; })
        .catch(() => setGpsStatus('stale'));
    }

    function onGpsError(err) {
        console.warn('GPS error:', err.message);
        setGpsStatus('error');
    }

    if ('geolocation' in navigator) {
        // First immediate fix
        navigator.geolocation.getCurrentPosition(sendLocation, onGpsError, {
            enableHighAccuracy: true, timeout: 10000, maximumAge: 0
        });
        // Continuous watch — updates every movement
        watchId = navigator.geolocation.watchPosition(sendLocation, onGpsError, {
            enableHighAccuracy: true, timeout: 10000, maximumAge: 5000
        });
        // Heartbeat fallback every 8s even if position hasn't changed
        setInterval(() => {
            navigator.geolocation.getCurrentPosition(sendLocation, onGpsError, {
                enableHighAccuracy: true, timeout: 8000, maximumAge: 0
            });
        }, 8000);
    } else {
        setGpsStatus('error');
    }

    // Stop sharing if page unloads
    window.addEventListener('beforeunload', () => {
        if (watchId !== null) navigator.geolocation.clearWatch(watchId);
    });
    @endif
</script>
@endpush

