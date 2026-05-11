@extends('layouts.admin')
@section('title', 'Analytics — Admin')
@section('page-title', 'Analytics')

@push('styles')
<style>
    .analytics-grid {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: 20px;
    }
    .col-12 { grid-column: span 12; }
    .col-8  { grid-column: span 8; }
    .col-4  { grid-column: span 4; }
    .col-6  { grid-column: span 6; }
    .col-3  { grid-column: span 3; }
    @media(max-width:1200px) { .col-8,.col-4 { grid-column: span 12; } }
    @media(max-width:1024px) { .col-6 { grid-column: span 12; } }
    @media(max-width:800px)  { .col-3 { grid-column: span 6; } }
    @media(max-width:600px)  { .col-3 { grid-column: span 12; } }

    .kpi-card {
        background: #fff; border: 1px solid var(--border); border-radius: 14px;
        padding: 20px 22px; display: flex; flex-direction: column; gap: 6px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    }
    .kpi-num  { font-size: 32px; font-weight: 800; letter-spacing: -1px; font-family: 'Plus Jakarta Sans', sans-serif; }
    .kpi-label{ font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
    .kpi-sub  { font-size: 12px; color: var(--text-muted); }

    .chart-card {
        background: #fff; border: 1px solid var(--border); border-radius: 14px;
        padding: 20px 24px; box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    }
    .chart-title { font-size: 14px; font-weight: 700; color: #0F172A; margin-bottom: 4px; font-family: 'Plus Jakarta Sans', sans-serif; }
    .chart-sub   { font-size: 12px; color: var(--text-muted); margin-bottom: 16px; }
    .chart-wrap  { position: relative; }

    .responder-row { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid var(--border); }
    .responder-row:last-child { border-bottom: none; }
    .responder-avatar { width: 34px; height: 34px; border-radius: 10px; background: #EFF6FF; color: #2563EB; font-weight: 800; font-size: 13px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .responder-bar-wrap { flex: 1; }
    .responder-bar-bg { height: 6px; background: #F1F5F9; border-radius: 4px; overflow: hidden; }
    .responder-bar { height: 100%; border-radius: 4px; background: linear-gradient(90deg,#2563EB,#7C3AED); transition: width 0.8s ease; }
    .tab-bar { display: flex; gap: 0; margin-bottom: 16px; border-bottom: 2px solid var(--border); }
    .tab-btn { padding: 8px 16px; font-size: 13px; font-weight: 600; color: var(--text-muted); border: none; background: none; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all 0.15s; }
    .tab-btn.active { color: var(--accent); border-bottom-color: var(--accent); }
</style>
@endpush

@section('content')

{{-- ── KPI ROW ── --}}
<div class="analytics-grid" style="margin-bottom: 20px;">

    <div class="kpi-card col-3">
        <div class="kpi-label">Total Incidents</div>
        <div class="kpi-num" style="color:#2563EB;">{{ Illuminate\Support\Facades\DB::table('incidents')->count() }}</div>
        <div class="kpi-sub">All time</div>
    </div>

    <div class="kpi-card col-3">
        <div class="kpi-label">Resolution Rate</div>
        <div class="kpi-num" style="color:{{ $resolutionRate >= 70 ? '#10B981' : ($resolutionRate >= 40 ? '#F59E0B' : '#EF4444') }};">{{ $resolutionRate }}%</div>
        <div class="kpi-sub">Last 30 days ({{ $resolvedLast30 }}/{{ $totalLast30 }})</div>
    </div>

    <div class="kpi-card col-3">
        <div class="kpi-label">Avg Response Time</div>
        <div class="kpi-num" style="color:#8B5CF6;">
            {{ $avgResponseMinutes !== null ? $avgResponseMinutes.'m' : 'N/A' }}
        </div>
        <div class="kpi-sub">Incident → Dispatch (last 30d)</div>
    </div>

    <div class="kpi-card col-3">
        <div class="kpi-label">Total Users</div>
        <div class="kpi-num" style="color:#F97316;">{{ App\Models\User::count() }}</div>
        <div class="kpi-sub">Registered accounts</div>
    </div>

</div>

{{-- ── ROW 2: Area chart + Donut ── --}}
<div class="analytics-grid" style="margin-bottom: 20px;">

    {{-- Incidents over time --}}
    <div class="chart-card col-8">
        <div class="chart-title">Incident Volume — Last 30 Days</div>
        <div class="chart-sub">Daily incident reports submitted to the system</div>
        <div class="chart-wrap" style="height:220px;">
            <canvas id="chart-daily"></canvas>
        </div>
    </div>

    {{-- Donut: by type --}}
    <div class="chart-card col-4">
        <div class="chart-title">Incidents by Type</div>
        <div class="chart-sub">Distribution across emergency categories</div>
        <div class="chart-wrap" style="height:220px;">
            <canvas id="chart-type"></canvas>
        </div>
    </div>

</div>

{{-- ── ROW 3: Status + Severity + Users by role ── --}}
<div class="analytics-grid" style="margin-bottom: 20px;">

    <div class="chart-card col-4">
        <div class="chart-title">Current Status Breakdown</div>
        <div class="chart-sub">Where incidents are in the workflow</div>
        <div class="chart-wrap" style="height:200px;">
            <canvas id="chart-status"></canvas>
        </div>
    </div>

    <div class="chart-card col-4">
        <div class="chart-title">Severity Distribution</div>
        <div class="chart-sub">Reported emergency severity levels</div>
        <div class="chart-wrap" style="height:200px;">
            <canvas id="chart-severity"></canvas>
        </div>
    </div>

    <div class="chart-card col-4">
        <div class="chart-title">Users by Role</div>
        <div class="chart-sub">Platform user distribution</div>
        <div class="chart-wrap" style="height:200px;">
            <canvas id="chart-roles"></canvas>
        </div>
    </div>

</div>

{{-- ── ROW 4: Weekly trend + Hour heatmap ── --}}
<div class="analytics-grid" style="margin-bottom: 20px;">

    <div class="chart-card col-6">
        <div class="chart-title">Weekly Created vs Resolved</div>
        <div class="chart-sub">8-week comparison of incoming vs resolved incidents</div>
        <div class="chart-wrap" style="height:220px;">
            <canvas id="chart-weekly"></canvas>
        </div>
    </div>

    <div class="chart-card col-6">
        <div class="chart-title">Incidents by Hour of Day</div>
        <div class="chart-sub">When emergencies are most commonly reported (24h)</div>
        <div class="chart-wrap" style="height:220px;">
            <canvas id="chart-hour"></canvas>
        </div>
    </div>

</div>

{{-- ── ROW 5: Top Responders ── --}}
<div class="chart-card col-12" style="margin-bottom: 20px;">
    <div class="chart-title">Top Responders</div>
    <div class="chart-sub">Ranked by number of resolved incidents</div>

    @if($topResponders->isEmpty())
        <p style="color:var(--text-muted);font-size:13px;">No responder data yet.</p>
    @else
    @php $maxResolved = $topResponders->max('resolved_count') ?: 1; @endphp
    @foreach($topResponders as $rank => $r)
    <div class="responder-row">
        <div style="font-size:11px;font-weight:700;color:#94A3B8;width:18px;">{{ $rank+1 }}</div>
        <div class="responder-avatar">{{ strtoupper(substr($r->name,0,1)) }}</div>
        <div class="responder-bar-wrap">
            <div style="display:flex;justify-content:space-between;margin-bottom:5px;">
                <span style="font-size:13px;font-weight:600;color:#0F172A;">{{ $r->name }}</span>
                <span style="font-size:12px;color:var(--text-muted);">{{ $r->resolved_count }} resolved · {{ $r->total_assigned }} total</span>
            </div>
            <div class="responder-bar-bg">
                <div class="responder-bar" style="width:{{ round(($r->resolved_count/$maxResolved)*100) }}%;"></div>
            </div>
        </div>
    </div>
    @endforeach
    @endif
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
/* ── Shared defaults ── */
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.font.size   = 12;
Chart.defaults.color       = '#64748B';
Chart.defaults.plugins.legend.labels.boxWidth = 10;
Chart.defaults.plugins.legend.labels.padding  = 16;

const typeColorMap = {
    medical:'#EF4444', fire:'#F97316', crime:'#3B82F6', disaster:'#F59E0B',
    accident:'#6366F1', flood:'#14B8A6', earthquake:'#8B5CF6',
    hazmat:'#EAB308', missing_person:'#EC4899', others:'#94A3B8',
};
const statusColorMap = {
    pending:'#F59E0B', dispatched:'#3B82F6', en_route:'#06B6D4',
    arrived:'#F97316', resolved:'#10B981',
};

/* ── 1. Daily incidents area chart ── */
new Chart(document.getElementById('chart-daily'), {
    type: 'line',
    data: {
        labels: @json($labels30),
        datasets: [{
            label: 'Incidents',
            data:  @json($data30),
            borderColor: '#2563EB',
            backgroundColor: 'rgba(37,99,235,0.08)',
            fill: true,
            tension: 0.4,
            pointRadius: 3,
            pointHoverRadius: 6,
            pointBackgroundColor: '#2563EB',
            borderWidth: 2,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { maxTicksLimit: 8 } },
            y: { beginAtZero: true, grid: { color: '#F1F5F9' }, ticks: { stepSize: 1 } },
        },
    }
});

/* ── 2. Type donut ── */
const typeData = @json($byType);
new Chart(document.getElementById('chart-type'), {
    type: 'doughnut',
    data: {
        labels: typeData.map(t => t.type.charAt(0).toUpperCase() + t.type.slice(1).replace('_',' ')),
        datasets: [{ data: typeData.map(t => t.total), backgroundColor: typeData.map(t => typeColorMap[t.type]||'#94A3B8'), borderWidth: 2, borderColor: '#fff', hoverOffset: 8 }]
    },
    options: {
        responsive: true, maintainAspectRatio: false, cutout: '65%',
        plugins: { legend: { position: 'right', labels: { font: { size: 11 } } } },
    }
});

/* ── 3. Status bar chart ── */
const statusData = @json($byStatus);
new Chart(document.getElementById('chart-status'), {
    type: 'bar',
    data: {
        labels: statusData.map(s => s.status.charAt(0).toUpperCase() + s.status.slice(1).replace('_',' ')),
        datasets: [{ data: statusData.map(s => s.total), backgroundColor: statusData.map(s => statusColorMap[s.status]||'#94A3B8'), borderRadius: 8, borderSkipped: false }]
    },
    options: {
        responsive: true, maintainAspectRatio: false, indexAxis: 'y',
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true, grid: { color: '#F1F5F9' } }, y: { grid: { display: false } } },
    }
});

/* ── 4. Severity polar area ── */
const sevData = @json($bySeverity);
const sevColors = { high: '#EF4444', medium: '#F59E0B', low: '#10B981' };
new Chart(document.getElementById('chart-severity'), {
    type: 'polarArea',
    data: {
        labels: sevData.map(s => s.severity.charAt(0).toUpperCase()+s.severity.slice(1)),
        datasets: [{ data: sevData.map(s=>s.total), backgroundColor: sevData.map(s => sevColors[s.severity]||'#94A3B8'), borderWidth: 1, borderColor: '#fff' }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'right', labels: { font: { size: 11 } } } },
        scales: { r: { ticks: { display: false }, grid: { color: '#F1F5F9' } } },
    }
});

/* ── 5. Users by role donut ── */
const roleData = @json($usersByRole);
const roleColors = { reporter:'#3B82F6', responder:'#10B981', dispatcher:'#8B5CF6', admin:'#F97316' };
new Chart(document.getElementById('chart-roles'), {
    type: 'doughnut',
    data: {
        labels: roleData.map(r => r.role.charAt(0).toUpperCase()+r.role.slice(1)),
        datasets: [{ data: roleData.map(r=>r.total), backgroundColor: roleData.map(r=>roleColors[r.role]||'#94A3B8'), borderWidth: 2, borderColor: '#fff', hoverOffset: 8 }]
    },
    options: {
        responsive: true, maintainAspectRatio: false, cutout: '60%',
        plugins: { legend: { position: 'right', labels: { font: { size: 11 } } } },
    }
});

/* ── 6. Weekly grouped bar ── */
const wkData = @json($weeklyData);
new Chart(document.getElementById('chart-weekly'), {
    type: 'bar',
    data: {
        labels: wkData.map(w=>w.label),
        datasets: [
            { label: 'Created',  data: wkData.map(w=>w.created),  backgroundColor: 'rgba(37,99,235,0.7)',   borderRadius: 6, borderSkipped: false },
            { label: 'Resolved', data: wkData.map(w=>w.resolved), backgroundColor: 'rgba(16,185,129,0.7)', borderRadius: 6, borderSkipped: false },
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'top', align: 'end' } },
        scales: { x: { grid: { display: false } }, y: { beginAtZero: true, grid: { color: '#F1F5F9' }, ticks: { stepSize: 1 } } },
    }
});

/* ── 7. Hour of day bar ── */
new Chart(document.getElementById('chart-hour'), {
    type: 'bar',
    data: {
        labels: @json($hourLabels),
        datasets: [{
            label: 'Incidents',
            data:  @json($hourData),
            backgroundColor: @json($hourData).map(v => {
                const max = Math.max(...@json($hourData), 1);
                const alpha = 0.2 + (v/max)*0.75;
                return `rgba(139,92,246,${alpha})`;
            }),
            borderRadius: 4, borderSkipped: false,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { maxTicksLimit: 12 } },
            y: { beginAtZero: true, grid: { color: '#F1F5F9' }, ticks: { stepSize: 1 } },
        },
    }
});
</script>
@endpush
