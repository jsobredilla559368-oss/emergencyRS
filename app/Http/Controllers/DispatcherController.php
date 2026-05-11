<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\User;
use Illuminate\Http\Request;

class DispatcherController extends Controller
{
    public function dashboard(Request $request)
    {
        $query = Incident::with(['reporter', 'responder'])->latest();

        // Search filter
        if ($request->filled('q')) {
            $query->where(function($q) use ($request) {
                $q->where('description', 'like', '%'.$request->q.'%')
                  ->orWhere('location_address', 'like', '%'.$request->q.'%');
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $allIncidents = Incident::with(['reporter', 'responder'])->get();
        $incidents = $query->paginate(15)->withQueryString();

        $mapData = $allIncidents->map(function($i) {
            return [
                'id'         => $i->id,
                'display_id' => 'INC-' . str_pad($i->id, 4, '0', STR_PAD_LEFT),
                'type'       => $i->type,
                'severity'   => $i->severity,
                'status'     => $i->status,
                'desc'       => $i->description,
                'lat'        => $i->latitude,
                'lng'        => $i->longitude
            ];
        });

        $totalActive   = $allIncidents->whereIn('status', ['pending', 'dispatched', 'en_route', 'arrived'])->count();
        $pending       = $allIncidents->where('status', 'pending')->count();
        $dispatched    = $allIncidents->whereIn('status', ['dispatched', 'en_route', 'arrived'])->count();
        $resolvedToday = $allIncidents->where('status', 'resolved')
                                      ->where('updated_at', '>=', today())
                                      ->count();

        return view('dispatcher.dashboard', compact('incidents', 'totalActive', 'pending', 'dispatched', 'resolvedToday', 'mapData'));
    }

    public function incidentDetail($id)
    {
        $incident   = Incident::with(['reporter', 'responder', 'statusUpdates.updatedBy', 'media'])->findOrFail($id);
        $responders = User::where('role', 'responder')->orderBy('name')->get();

        return view('dispatcher.incident-detail', compact('incident', 'responders'));
    }

    public function incidentLog(Request $request)
    {
        $query = Incident::with(['reporter', 'responder', 'statusUpdates'])->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        // Filter by severity
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }
        // Search
        if ($request->filled('q')) {
            $query->where('description', 'like', '%' . $request->q . '%')
                  ->orWhere('location_address', 'like', '%' . $request->q . '%');
        }

        $incidents = $query->paginate(20)->withQueryString();

        $totalAll       = Incident::count();
        $totalResolved  = Incident::where('status', 'resolved')->count();
        $totalActive    = Incident::whereNotIn('status', ['resolved'])->count();

        return view('dispatcher.incident-log', compact('incidents', 'totalAll', 'totalResolved', 'totalActive'));
    }

    public function unitTracking()
    {
        $responders = User::where('role', 'responder')
            ->with(['assignedIncidents' => function ($q) {
                $q->whereIn('status', ['dispatched', 'en_route', 'arrived'])
                  ->with(['statusUpdates' => fn($q) => $q->latest()->limit(1)])
                  ->latest();
            }])
            ->get();

        $mapData = $responders->flatMap(function($r) {
            return $r->assignedIncidents->map(fn($i) => [
                'responder_name'  => $r->name,
                'responder_id'    => $r->id,
                'incident_id'     => $i->id,
                'display_id'      => 'INC-' . str_pad($i->id, 4, '0', STR_PAD_LEFT),
                'status'          => $i->status,
                'type'            => $i->type,
                'lat'             => $i->latitude,
                'lng'             => $i->longitude,
                'address'         => $i->location_address,
                'last_update'     => optional($i->statusUpdates->first())->created_at?->diffForHumans() ?? 'No updates',
            ]);
        })->values();

        return view('dispatcher.unit-tracking', compact('responders', 'mapData'));
    }
}
