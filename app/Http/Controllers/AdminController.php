<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Incident;
use App\Models\StatusUpdate;
use App\Models\IncidentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_users'       => User::count(),
            'total_incidents'   => Incident::count(),
            'pending_incidents' => Incident::where('status', 'pending')->count(),
            'resolved_today'    => Incident::where('status', 'resolved')->where('updated_at', '>=', today())->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    public function users()
    {
        $users = User::latest()->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function updateUserRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|in:reporter,responder,dispatcher,admin',
        ]);

        $user->update(['role' => $validated['role']]);

        return back()->with('success', 'User role updated successfully.');
    }

    public function deleteUser(User $user)
    {
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Prevent deleting the last admin
        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return back()->with('error', 'Cannot delete the last admin account.');
        }

        // Nullify any incidents they were the reporter/responder on
        \App\Models\Incident::where('user_id', $user->id)->update(['user_id' => null]);
        \App\Models\Incident::where('responder_id', $user->id)->update(['responder_id' => null, 'status' => 'pending']);

        $user->delete();

        return back()->with('success', "User '{$user->name}' has been deleted.");
    }

    /* ═══════════════════════════════
       SYSTEM LOGS
    ═══════════════════════════════ */
    public function systemLogs(Request $request)
    {
        // Combine incident events + status updates into one unified log
        $perPage = 25;
        $page    = $request->input('page', 1);

        // Filters
        $filterType   = $request->input('type');
        $filterRole   = $request->input('role');
        $filterSearch = $request->input('q');
        $filterDate   = $request->input('date');

        // ── Incident creation events ──
        $incidentQuery = Incident::with(['reporter:id,name,role', 'responder:id,name,role'])
            ->select('id', 'user_id', 'responder_id', 'type', 'severity', 'status', 'created_at', 'updated_at');

        if ($filterType && $filterType !== 'all') {
            if ($filterType === 'incident_created') $incidentQuery->whereColumn('created_at', 'created_at');
        }
        if ($filterDate) {
            $incidentQuery->whereDate('created_at', $filterDate);
        }

        // ── Status update events ──
        $statusQuery = StatusUpdate::with(['incident:id,type,user_id', 'updatedBy:id,name,role'])
            ->select('id', 'incident_id', 'status', 'notes', 'updated_by', 'created_at');

        if ($filterDate) {
            $statusQuery->whereDate('created_at', $filterDate);
        }
        if ($filterSearch) {
            $statusQuery->where('notes', 'like', "%{$filterSearch}%");
        }

        // ── User registrations ──
        $userQuery = User::select('id', 'name', 'email', 'role', 'created_at');
        if ($filterDate) {
            $userQuery->whereDate('created_at', $filterDate);
        }
        if ($filterSearch) {
            $userQuery->where(fn($q) => $q->where('name', 'like', "%{$filterSearch}%")->orWhere('email', 'like', "%{$filterSearch}%"));
        }

        // Build unified log entries
        $logs = collect();

        // Incident creations
        if (!$filterType || $filterType === 'all' || $filterType === 'incident_created') {
            $incidentQuery->get()->each(function($i) use (&$logs, $filterSearch) {
                $desc = 'INC-'.str_pad($i->id,4,'0',STR_PAD_LEFT).' — '.$i->type.' reported';
                if ($filterSearch && !str_contains(strtolower($desc), strtolower($filterSearch))) return;
                $logs->push([
                    'time'     => $i->created_at,
                    'type'     => 'incident_created',
                    'label'    => 'Incident Created',
                    'color'    => 'orange',
                    'actor'    => optional($i->reporter)->name ?? 'Anonymous',
                    'actor_role' => optional($i->reporter)->role ?? 'guest',
                    'detail'   => $desc,
                    'meta'     => ucfirst($i->severity).' severity · Status: '.ucfirst(str_replace('_',' ',$i->status)),
                    'link'     => null,
                ]);
            });
        }

        // Status updates
        if (!$filterType || $filterType === 'all' || $filterType === 'status_update') {
            $statusQuery->get()->each(function($u) use (&$logs) {
                $logs->push([
                    'time'       => $u->created_at,
                    'type'       => 'status_update',
                    'label'      => 'Status: '.ucfirst(str_replace('_',' ',$u->status)),
                    'color'      => match($u->status) {
                        'resolved'   => 'green',
                        'dispatched' => 'blue',
                        'en_route'   => 'cyan',
                        'arrived'    => 'amber',
                        default      => 'gray',
                    },
                    'actor'      => optional($u->updatedBy)->name ?? 'System',
                    'actor_role' => optional($u->updatedBy)->role ?? 'system',
                    'detail'     => 'INC-'.str_pad(optional($u->incident)->id,4,'0',STR_PAD_LEFT).' → '.ucfirst(str_replace('_',' ',$u->status)),
                    'meta'       => $u->notes ?: 'No notes',
                    'link'       => null,
                ]);
            });
        }

        // User registrations
        if (!$filterType || $filterType === 'all' || $filterType === 'user_registered') {
            $userQuery->get()->each(function($u) use (&$logs) {
                $logs->push([
                    'time'       => $u->created_at,
                    'type'       => 'user_registered',
                    'label'      => 'User Registered',
                    'color'      => 'violet',
                    'actor'      => $u->name,
                    'actor_role' => $u->role,
                    'detail'     => $u->name.' joined as '.ucfirst($u->role),
                    'meta'       => $u->email,
                    'link'       => null,
                ]);
            });
        }

        // Sort by time desc, paginate manually
        $sorted  = $logs->sortByDesc('time')->values();
        $total   = $sorted->count();
        $offset  = ($page - 1) * $perPage;
        $paginated = $sorted->slice($offset, $perPage)->values();

        // Manual paginator
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginated, $total, $perPage, $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Totals for filter bar
        $logTotals = [
            'all'              => $total,
            'incident_created' => $logs->where('type', 'incident_created')->count(),
            'status_update'    => $logs->where('type', 'status_update')->count(),
            'user_registered'  => $logs->where('type', 'user_registered')->count(),
        ];

        return view('admin.system-logs', compact('paginator', 'logTotals', 'filterType', 'filterSearch', 'filterDate'));
    }

    /* ═══════════════════════════════
       ANALYTICS
    ═══════════════════════════════ */
    public function analytics()
    {
        $now   = Carbon::now();
        $start = $now->copy()->subDays(29)->startOfDay(); // last 30 days

        // ── 1. Incidents per day (last 30 days) ──
        $incidentsPerDay = Incident::selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('created_at', '>=', $start)
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        // Fill missing days with 0
        $labels30 = [];
        $data30   = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = $now->copy()->subDays($i)->format('Y-m-d');
            $labels30[] = Carbon::parse($d)->format('M d');
            $data30[]   = $incidentsPerDay[$d] ?? 0;
        }

        // ── 2. Incidents by type ──
        $byType = Incident::selectRaw('type, COUNT(*) as total')
            ->groupBy('type')->orderByDesc('total')->get();

        // ── 3. Incidents by status ──
        $byStatus = Incident::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')->orderByDesc('total')->get();

        // ── 4. Incidents by severity ──
        $bySeverity = Incident::selectRaw('severity, COUNT(*) as total')
            ->groupBy('severity')->get();

        // ── 5. Resolution rate (last 30 days) ──
        $totalLast30   = Incident::where('created_at', '>=', $start)->count();
        $resolvedLast30 = Incident::where('created_at', '>=', $start)->where('status', 'resolved')->count();
        $resolutionRate = $totalLast30 > 0 ? round(($resolvedLast30 / $totalLast30) * 100, 1) : 0;

        // ── 6. Avg response time (created → first status update) ──
        $avgResponseMinutes = DB::table('incidents')
            ->join('status_updates', 'incidents.id', '=', 'status_updates.incident_id')
            ->where('status_updates.status', 'dispatched')
            ->where('incidents.created_at', '>=', $start)
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, incidents.created_at, status_updates.created_at)) as avg_mins')
            ->value('avg_mins');
        $avgResponseMinutes = $avgResponseMinutes ? round($avgResponseMinutes, 1) : null;

        // ── 7. Top responders (most incidents handled) ──
        $topResponders = User::where('role', 'responder')
            ->withCount(['assignedIncidents as resolved_count' => fn($q) => $q->where('status', 'resolved')])
            ->withCount('assignedIncidents as total_assigned')
            ->orderByDesc('resolved_count')
            ->limit(5)
            ->get();

        // ── 8. Users by role ──
        $usersByRole = User::selectRaw('role, COUNT(*) as total')
            ->groupBy('role')->get();

        // ── 9. Incidents by hour of day (heat map data) ──
        $byHour = Incident::selectRaw('HOUR(created_at) as hour, COUNT(*) as total')
            ->groupBy('hour')->orderBy('hour')->pluck('total', 'hour');
        $hourLabels = array_map(fn($h) => str_pad($h,2,'0',STR_PAD_LEFT).':00', range(0,23));
        $hourData   = array_map(fn($h) => (int)($byHour[$h] ?? 0), range(0,23));

        // ── 10. Weekly resolved vs created ──
        $weeklyData = [];
        for ($w = 7; $w >= 0; $w--) {
            $ws = $now->copy()->subWeeks($w)->startOfWeek();
            $we = $ws->copy()->endOfWeek();
            $weeklyData[] = [
                'label'    => 'W'.$ws->weekOfYear,
                'created'  => Incident::whereBetween('created_at', [$ws,$we])->count(),
                'resolved' => Incident::whereBetween('updated_at', [$ws,$we])->where('status','resolved')->count(),
            ];
        }

        return view('admin.analytics', compact(
            'labels30', 'data30',
            'byType', 'byStatus', 'bySeverity',
            'resolutionRate', 'totalLast30', 'resolvedLast30',
            'avgResponseMinutes',
            'topResponders',
            'usersByRole',
            'hourLabels', 'hourData',
            'weeklyData'
        ));
    }
}
