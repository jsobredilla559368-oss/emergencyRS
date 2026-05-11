@extends('layouts.reporter-public')

@section('title', 'Report an Emergency — EmergencyRS')

@section('content')
<div class="reporter-main">

    {{-- Page Header --}}
    <div class="page-header">
        <h1 class="page-title">Report an Emergency</h1>
        <p class="page-sub">Fill in the details below. Your report will be sent to the nearest response team immediately.
        </p>
    </div>

    {{-- Validation errors --}}
    @if ($errors->any())
        <div class="error-banner">
            <p>Please fix the following:</p>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('reporter.store') }}" enctype="multipart/form-data" id="report-form">
        @csrf

        {{-- ═══ STEP 1: Emergency Type ═══ --}}
        <div class="step-card">
            <p class="step-label">Step 1 &nbsp;·&nbsp; Emergency Type</p>

            <input type="hidden" name="emergency_type" id="emergency_type" value="{{ old('emergency_type') }}">

            <div class="type-grid">

                {{-- Medical --}}
                <button type="button" id="type-medical" onclick="selectType(this, 'medical')" class="type-card">
                    <div class="type-icon medical">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <span class="type-name">Medical</span>
                </button>

                {{-- Fire --}}
                <button type="button" id="type-fire" onclick="selectType(this, 'fire')" class="type-card">
                    <div class="type-icon fire">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path
                                d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                            <path d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z" />
                        </svg>
                    </div>
                    <span class="type-name">Fire</span>
                </button>

                {{-- Crime --}}
                <button type="button" id="type-crime" onclick="selectType(this, 'crime')" class="type-card">
                    <div class="type-icon crime">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <span class="type-name">Crime</span>
                </button>

                {{-- Disaster --}}
                <button type="button" id="type-disaster" onclick="selectType(this, 'disaster')" class="type-card">
                    <div class="type-icon disaster">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <span class="type-name">Disaster</span>
                </button>

                {{-- Accident --}}
                <button type="button" id="type-accident" onclick="selectType(this, 'accident')" class="type-card">
                    <div class="type-icon accident">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                           <path d="M4 16h16M4 16l2-8h12l2 8M4 16v4h16v-4M8 12h8M6 20h3M15 20h3"/>
                        </svg>
                    </div>
                    <span class="type-name">Accident</span>
                </button>

                {{-- Flood --}}
                <button type="button" id="type-flood" onclick="selectType(this, 'flood')" class="type-card">
                    <div class="type-icon flood">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                           <path d="M12 22v-6m-6 4l6-6 6 6M4 8c2-2 6-2 8 0s6 2 8 0"/>
                        </svg>
                    </div>
                    <span class="type-name">Flood</span>
                </button>

                {{-- Earthquake --}}
                <button type="button" id="type-earthquake" onclick="selectType(this, 'earthquake')" class="type-card">
                    <div class="type-icon earthquake">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                           <path d="M4 12h5l2-5 3 10 2-5h4M12 2v2M12 20v2M2 12h2M20 12h2"/>
                        </svg>
                    </div>
                    <span class="type-name">Earthquake</span>
                </button>

                {{-- HazMat --}}
                <button type="button" id="type-hazmat" onclick="selectType(this, 'hazmat')" class="type-card">
                    <div class="type-icon hazmat">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                           <path d="M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20zM8 12l4-8 4 8m-8 0h8M12 12v6"/>
                        </svg>
                    </div>
                    <span class="type-name">HazMat</span>
                </button>

                {{-- Missing Person --}}
                <button type="button" id="type-missing_person" onclick="selectType(this, 'missing_person')" class="type-card">
                    <div class="type-icon missing_person">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                           <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 4h.01"/>
                        </svg>
                    </div>
                    <span class="type-name">Missing Person</span>
                </button>

                {{-- Others --}}
                <button type="button" id="type-others" onclick="selectType(this, 'others')" class="type-card">
                    <div class="type-icon others">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                           <circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/><circle cx="5" cy="12" r="2"/>
                        </svg>
                    </div>
                    <span class="type-name">Others</span>
                </button>
            </div>

            @error('emergency_type')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- ═══ STEP 2: Severity ═══ --}}
        <div class="step-card">
            <p class="step-label">Step 2 &nbsp;·&nbsp; Severity Level</p>

            <div class="severity-grid">

                <label class="severity-label">
                    <input type="radio" name="severity" value="high" {{ old('severity') === 'high' ? 'checked' : '' }}>
                    <span class="severity-dot high"></span>
                    <div class="severity-info">
                        <p class="severity-title">High</p>
                        <p class="severity-sub">Life threatening</p>
                    </div>
                </label>

                <label class="severity-label">
                    <input type="radio" name="severity" value="medium" {{ old('severity') === 'medium' ? 'checked' : '' }}>
                    <span class="severity-dot medium"></span>
                    <div class="severity-info">
                        <p class="severity-title">Medium</p>
                        <p class="severity-sub">Needs attention</p>
                    </div>
                </label>

                <label class="severity-label">
                    <input type="radio" name="severity" value="low" {{ old('severity') === 'low' ? 'checked' : '' }}>
                    <span class="severity-dot low"></span>
                    <div class="severity-info">
                        <p class="severity-title">Low</p>
                        <p class="severity-sub">Not urgent</p>
                    </div>
                </label>
            </div>

            @error('severity')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- ═══ STEP 3: Description ═══ --}}
        <div class="step-card">
            <p class="step-label">Step 3 &nbsp;·&nbsp; Description</p>

            <textarea name="description" id="description" class="field-textarea" rows="4" maxlength="1000"
                placeholder="Describe what is happening — number of people involved, visible injuries, hazards, or anything that could help the response team."
                oninput="updateCharCount(this)">{{ old('description') }}</textarea>

            <p class="char-counter"><span id="char-count">0</span> / 1000</p>

            @error('description')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- ═══ STEP 4: Location ═══ --}}
        <div class="step-card">
            <p class="step-label">Step 4 &nbsp;·&nbsp; Location</p>

            <input type="hidden" name="latitude"  id="latitude"  value="{{ old('latitude') }}">
            <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
            <input type="hidden" name="address"   id="address"   value="{{ old('address') }}">

            {{-- Detect button --}}
            <button type="button" id="gps-btn" onclick="detectLocation()" class="gps-btn" style="margin-bottom: 14px;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span id="gps-label">Detect my location</span>
            </button>

            {{-- Mapbox map (hidden until location is set) --}}
            <div id="location-map-wrap" style="display:none; margin-bottom: 14px;">
                <div id="location-map" style="width:100%; height: 260px; border-radius: 14px; border: 2px solid #E2E8F0; overflow:hidden;"></div>
                <p style="font-size: 11px; color: var(--text-subtle, #94A3B8); margin-top: 8px; text-align: center;">
                    📌 Drag the pin to fine-tune your exact location
                </p>
            </div>

            {{-- Address search / manual override --}}
            <div id="manual-addr-wrap" style="display:none; margin-bottom: 14px;">
                <div style="position:relative;">
                    <input type="text" id="addr-search" placeholder="Search or type your address…"
                           style="width:100%; height:44px; padding: 0 44px 0 14px; border: 2px solid #E2E8F0;
                                  border-radius: 12px; font-size: 14px; font-family: inherit; outline:none;
                                  background:#F8FAFC; color:#0F172A; transition: border-color 0.2s;"
                           oninput="debounceSearch(this.value)"
                           onfocus="this.style.borderColor='#93C5FD'"
                           onblur="this.style.borderColor='#E2E8F0'">
                    <button type="button" onclick="searchAddress()" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#64748B;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width:18px;height:18px;"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                    </button>
                </div>
                <div id="addr-suggestions" style="display:none; background:#fff; border:1px solid #E2E8F0; border-radius:10px; margin-top:6px; overflow:hidden; box-shadow:0 4px 16px rgba(0,0,0,0.08);"></div>
            </div>

            {{-- Confirmed location pill --}}
            <div id="location-ok" class="location-ok" style="display:none;">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 13l4 4L19 7" />
                </svg>
                <div>
                    <p class="location-ok-title">Location confirmed</p>
                    <p class="location-ok-text" id="location-text"></p>
                </div>
            </div>

            <div id="location-err" class="location-err" style="display:none;">
                <strong>Unable to detect location.</strong><br>
                Please allow location access or search your address manually.
            </div>

            <p class="location-hint">Your location is only used to dispatch the nearest available unit.</p>

            @error('latitude')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- ═══ STEP 5: Media Upload ═══ --}}
        <div class="step-card">
            <div class="credibility-tip" style="background: #F0FDF4; border: 1px solid #86EFAC; padding: 12px 16px; border-radius: 12px; margin-bottom: 24px; display: flex; gap: 12px; align-items: flex-start;">
                <svg width="20" height="20" fill="none" stroke="#16A34A" viewBox="0 0 24 24" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div>
                    <p style="font-size: 13px; font-weight: 700; color: #166534; margin-bottom: 4px;">Increase Report Credibility</p>
                    <p style="font-size: 13px; color: #15803D; line-height: 1.4;">Adding photos/videos and confirming your location helps responders verify this is a genuine emergency faster.</p>
                </div>
            </div>

            <div class="upload-header">
                <p class="step-label" style="margin-bottom:0">Step 5 &nbsp;·&nbsp; Photo / Video</p>
                <span style="font-size:11.5px; color:var(--subtle);">Optional</span>
            </div>
            <p class="upload-note" style="margin-top:6px; margin-bottom:14px;">
                Up to 5 files &middot; JPG, PNG, MP4, MOV &middot; Max 50 MB each
            </p>

            <label for="media" id="drop-zone" class="drop-zone">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="drop-zone-text">
                    <span>Click to upload</span> or drag and drop
                </p>
                <input type="file" id="media" name="media[]" style="display:none" multiple accept="image/*,video/*"
                    onchange="previewFiles(this)">
            </label>

            <div id="preview-grid" class="preview-grid"></div>
        </div>

        {{-- ═══ SUBMIT ═══ --}}
        <div class="submit-dock">
            <div class="submit-dock-inner">
                <button type="submit" class="submit-btn">
                    Submit Emergency Report
                </button>
                <p class="submit-disclaimer">
                    By submitting you confirm this is a genuine emergency.<br>
                    False reports may result in legal consequences.
                </p>
            </div>
        </div>

    </form>

</div>{{-- /.reporter-main --}}
@endsection

@push('scripts')
    <script>
        /* ── Mapbox token ── */
        mapboxgl.accessToken = '{{ config('services.mapbox.access_token') }}';
        let reportMap   = null;
        let reportMarker = null;
        let searchTimer  = null;

        /* ── Initialize the location map ── */
        function initMap(lat, lng) {
            const wrap = document.getElementById('location-map-wrap');
            wrap.style.display = 'block';
            document.getElementById('manual-addr-wrap').style.display = 'block';

            if (reportMap) {
                // Already initialised — just fly and move marker
                reportMap.flyTo({ center: [lng, lat], zoom: 15 });
                reportMarker.setLngLat([lng, lat]);
                return;
            }

            reportMap = new mapboxgl.Map({
                container: 'location-map',
                style: 'mapbox://styles/mapbox/streets-v12',
                center: [lng, lat],
                zoom: 15,
                attributionControl: false,
            });

            reportMap.addControl(new mapboxgl.NavigationControl({ showCompass: false }), 'top-right');

            // Custom pulsing marker element
            const el = document.createElement('div');
            el.style.cssText = `
                width: 28px; height: 28px; border-radius: 50%;
                background: #EF4444; border: 3px solid #fff;
                box-shadow: 0 0 0 4px rgba(239,68,68,0.25), 0 4px 12px rgba(0,0,0,0.2);
                cursor: grab;
            `;

            reportMarker = new mapboxgl.Marker(el, { draggable: true })
                .setLngLat([lng, lat])
                .addTo(reportMap);

            // When the pin is dragged — reverse-geocode new position
            reportMarker.on('dragend', () => {
                const lngLat = reportMarker.getLngLat();
                setLocation(lngLat.lat, lngLat.lng);
                reverseGeocode(lngLat.lat, lngLat.lng);
            });
        }

        /* ── Set hidden inputs ── */
        function setLocation(lat, lng) {
            document.getElementById('latitude').value  = parseFloat(lat).toFixed(6);
            document.getElementById('longitude').value = parseFloat(lng).toFixed(6);
        }

        /* ── Reverse geocode using Mapbox ── */
        async function reverseGeocode(lat, lng) {
            const token = mapboxgl.accessToken;
            try {
                const res = await fetch(
                    `https://api.mapbox.com/geocoding/v5/mapbox.places/${lng},${lat}.json?access_token=${token}&language=en&types=address,place`
                );
                const data = await res.json();
                const place = data.features?.[0]?.place_name ?? `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                updateAddressDisplay(place);
            } catch {
                updateAddressDisplay(`${lat.toFixed(5)}, ${lng.toFixed(5)}`);
            }
        }

        function updateAddressDisplay(addr) {
            document.getElementById('address').value       = addr;
            document.getElementById('location-text').textContent = addr;
            document.getElementById('location-ok').style.display = 'flex';
            if (document.getElementById('addr-search')) {
                document.getElementById('addr-search').value = addr;
            }
        }

        /* ── GPS Detect Button ── */
        function detectLocation() {
            const btn   = document.getElementById('gps-btn');
            const label = document.getElementById('gps-label');
            const err   = document.getElementById('location-err');

            label.textContent = 'Detecting…';
            btn.disabled = true;
            err.style.display = 'none';

            if (!navigator.geolocation) {
                label.textContent = 'Not supported';
                btn.disabled = false;
                err.style.display = 'block';
                return;
            }

            navigator.geolocation.getCurrentPosition(
                async (pos) => {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    setLocation(lat, lng);
                    initMap(lat, lng);
                    await reverseGeocode(lat, lng);
                    label.textContent = 'Location detected ✓';
                    btn.style.background = '#15803D';
                    btn.disabled = false;
                },
                () => {
                    label.textContent = 'Detect my location';
                    btn.disabled = false;
                    err.style.display = 'block';
                    // Still show map with Manila as default so they can drag
                    initMap(14.5995, 120.9842);
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        }

        /* ── Address Forward Geocode Search ── */
        let debounceTimer;
        function debounceSearch(val) {
            clearTimeout(debounceTimer);
            if (val.length < 3) { hideSuggestions(); return; }
            debounceTimer = setTimeout(() => forwardGeocode(val), 400);
        }

        async function forwardGeocode(q) {
            const token = mapboxgl.accessToken;
            try {
                const res  = await fetch(
                    `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(q)}.json?access_token=${token}&language=en&types=address,place,locality&limit=5&country=PH`
                );
                const data = await res.json();
                showSuggestions(data.features || []);
            } catch { hideSuggestions(); }
        }

        function showSuggestions(features) {
            const box = document.getElementById('addr-suggestions');
            if (!features.length) { hideSuggestions(); return; }
            box.innerHTML = features.map((f, i) => `
                <div onclick="pickSuggestion(${f.center[1]}, ${f.center[0]}, '${f.place_name.replace(/'/g, "&apos;")}')"
                     style="padding:10px 14px;font-size:13px;cursor:pointer;border-bottom:1px solid #F1F5F9;
                            color:#1E293B;line-height:1.4;transition:background 0.15s;"
                     onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='#fff'">
                    📍 ${f.place_name}
                </div>
            `).join('');
            box.style.display = 'block';
        }

        function pickSuggestion(lat, lng, name) {
            hideSuggestions();
            document.getElementById('addr-search').value = name;
            setLocation(lat, lng);
            updateAddressDisplay(name);
            if (reportMap) {
                reportMap.flyTo({ center: [lng, lat], zoom: 16 });
                reportMarker.setLngLat([lng, lat]);
            } else {
                initMap(lat, lng);
            }
        }

        function hideSuggestions() {
            const box = document.getElementById('addr-suggestions');
            if (box) box.style.display = 'none';
        }

        // Close suggestions on outside click
        document.addEventListener('click', e => {
            if (!e.target.closest('#manual-addr-wrap')) hideSuggestions();
        });

        /* ── Type Card Selection ── */
        function selectType(el, type) {
            document.querySelectorAll('.type-card').forEach(c => { c.className = 'type-card'; });
            el.classList.add('selected-' + type);
            document.getElementById('emergency_type').value = type;
        }
        const saved = document.getElementById('emergency_type').value;
        if (saved) { const btn = document.getElementById('type-' + saved); if (btn) selectType(btn, saved); }

        /* ── Char Counter ── */
        function updateCharCount(el) { document.getElementById('char-count').textContent = el.value.length; }
        const desc = document.getElementById('description');
        if (desc && desc.value) updateCharCount(desc);

        /* ── File Preview ── */
        function previewFiles(input) {
            const grid = document.getElementById('preview-grid');
            grid.innerHTML = '';
            Array.from(input.files).slice(0, 5).forEach(file => {
                const wrap = document.createElement('div');
                wrap.className = 'preview-item';
                if (file.type.startsWith('image/')) {
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    wrap.appendChild(img);
                } else {
                    const p = document.createElement('div');
                    p.className = 'preview-video-placeholder';
                    p.textContent = 'VIDEO';
                    wrap.appendChild(p);
                }
                grid.appendChild(wrap);
            });
        }

        /* ── Drag & Drop ── */
        const zone = document.getElementById('drop-zone');
        zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
        zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
        zone.addEventListener('drop', e => {
            e.preventDefault();
            zone.classList.remove('dragover');
            const input = document.getElementById('media');
            input.files = e.dataTransfer.files;
            previewFiles(input);
        });

        /* ── Restore on validation fail ── */
        @if(old('latitude') && old('longitude'))
        (async () => {
            const lat = {{ old('latitude') }};
            const lng = {{ old('longitude') }};
            initMap(lat, lng);
            await reverseGeocode(lat, lng);
        })();
        @endif
    </script>
@endpush