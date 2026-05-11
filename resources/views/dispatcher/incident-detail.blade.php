@extends('layouts.dispatcher')

@section('title', 'Incident Detail — Dispatcher')
@section('page-title', 'Control Center')

@push('styles')
{{-- Global incident styles are in responder.css --}}
@endpush

@section('content')
<div class="detail-container">
    <div class="detail-main">
        <div class="glass-card">
            <div class="detail-header">
                <div>
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                        <span class="badge badge-{{ $incident->type }}">{{ ucfirst($incident->type) }}</span>
                        <span class="badge badge-{{ $incident->status }}">{{ ucfirst($incident->status) }}</span>
                    </div>
                    <h2 class="incident-id" style="font-size: 24px; color: var(--text-primary);">
                        INC-{{ str_pad($incident->id, 4, '0', STR_PAD_LEFT) }}
                    </h2>
                </div>
                <div style="text-align: right;">
                    <p class="section-label">Reported At</p>
                    <p style="font-size: 14px; font-weight: 600;">{{ $incident->created_at->format('M d, Y — H:i') }}</p>
                </div>
            </div>

            <div class="detail-section">
                <h3 class="section-label">Emergency Description</h3>
                <p style="font-size: 16px; line-height: 1.6; color: var(--text-primary); margin: 0;">
                    {{ $incident->description }}
                </p>
            </div>

            <div class="detail-section">
                <h3 class="section-label">Live Scene Evidence</h3>
                <div class="media-grid">
                    @forelse($incident->media as $m)
                        <div class="media-item">
                            @if($m->type === 'image')
                                <img src="{{ Storage::url($m->file_path) }}" alt="Scene Photo">
                            @else
                                <video src="{{ Storage::url($m->file_path) }}" controls></video>
                            @endif
                        </div>
                    @empty
                        <div class="empty-media">No media evidence provided by reporter.</div>
                    @endforelse
                </div>
            </div>

            <div class="detail-section">
                <h3 class="section-label">Incident Timeline</h3>
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-dot" style="background: var(--accent-cyan);"></div>
                        <div class="timeline-content">
                            <p class="timeline-title">Report Received</p>
                            <p class="timeline-time">{{ $incident->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @foreach($incident->statusUpdates as $update)
                        <div class="timeline-item">
                            <div class="timeline-dot" style="background: {{ $update->status === 'resolved' ? 'var(--accent-green)' : 'var(--accent-orange)' }}"></div>
                            <div class="timeline-content">
                                <p class="timeline-title">Status: {{ ucfirst($update->status) }}</p>
                                <p class="timeline-text">{{ $update->note }}</p>
                                <p class="timeline-time">{{ $update->created_at->diffForHumans() }} by {{ $update->updatedBy->name }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="detail-side">
        {{-- Location Map --}}
        <div class="glass-card side-card">
            <h3 class="section-label">Precise Location</h3>
            <div id="mini-map" style="height: 180px; border-radius: 12px; margin-bottom: 12px; border: 1px solid var(--border-subtle);"></div>
            <p style="font-size: 13px; color: var(--text-primary); margin-bottom: 4px; font-weight: 600;">
                {{ $incident->location_address ?? 'Address unmapped' }}
            </p>
            <p style="font-size: 11px; color: var(--text-dim);">GPS: {{ $incident->latitude }}, {{ $incident->longitude }}</p>
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
            </div>
        </div>

        {{-- Actions --}}
        <div class="glass-card side-card actions-card">
            <h3 class="section-label">Dispatch Actions</h3>

            @if($incident->status === 'resolved')
                {{-- Resolved banner — no actions allowed --}}
                <div style="text-align: center; padding: 24px 16px;">
                    <div style="width: 52px; height: 52px; border-radius: 50%; background: rgba(16,185,129,0.15);
                                border: 2px solid rgba(16,185,129,0.3); display: flex; align-items: center;
                                justify-content: center; margin: 0 auto 14px;">
                        <svg fill="none" stroke="#10B981" viewBox="0 0 24 24" stroke-width="2.5" style="width:22px;height:22px;">
                            <path d="M20 6L9 17l-5-5"/>
                        </svg>
                    </div>
                    <p style="font-size: 14px; font-weight: 700; color: #10B981; margin-bottom: 6px;">Incident Resolved</p>
                    <p style="font-size: 12px; color: var(--text-dim); line-height: 1.5;">
                        This emergency has been resolved.<br>No further dispatch actions are available.
                    </p>
                    <div style="margin-top: 16px; padding: 10px 14px; background: rgba(16,185,129,0.06);
                                border: 1px solid rgba(16,185,129,0.2); border-radius: 10px;">
                        <p style="font-size: 11px; color: #10B981; font-weight: 600;">
                            Resolved {{ $incident->updated_at->diffForHumans() }}
                        </p>
                    </div>
                </div>
            @else
                {{-- Assign responder form --}}
                <form action="{{ route('incidents.assign', $incident->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="action-field">
                        <label>Assign Responder</label>
                        <select name="responder_id" class="action-select">
                            <option value="">Select a responder...</option>
                            @foreach($responders as $resp)
                                <option value="{{ $resp->id }}" {{ $incident->responder_id == $resp->id ? 'selected' : '' }}>
                                    {{ $resp->name }} ({{ ucfirst($resp->role) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="primary-action-btn" style="background: var(--accent-violet);">
                        Dispatch Unit
                    </button>
                </form>

                <div class="divider"></div>

                {{-- Force Resolve form --}}
                <form action="{{ route('incidents.status-updates.store', $incident->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="resolved">
                    <div class="action-field">
                        <label>Resolution Notes</label>
                        <textarea name="notes" class="action-select" style="height: 60px; padding: 10px; resize: vertical;"
                                  placeholder="Add final notes..." required></textarea>
                    </div>
                    <button type="submit" class="secondary-action-btn" style="border-color: var(--accent-green); color: var(--accent-green);">
                        Force Resolve
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection

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
    
    new mapboxgl.Marker({ color: color })
        .setLngLat([{{ $incident->longitude }}, {{ $incident->latitude }}])
        .addTo(map);
</script>
@endpush
