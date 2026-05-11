<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use Illuminate\Http\Request;

class ResponderController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        
        // Fetch all incidents (in a real app, you might scope this by region or active status)
        $incidents = Incident::with(['reporter', 'responder'])->latest()->get();

        // Specific queue for responders
        $myQueue = collect();
        if ($user->role === 'responder') {
            $myQueue = Incident::with(['reporter'])
                ->where('responder_id', $user->id)
                ->whereIn('status', ['dispatched', 'en_route', 'arrived'])
                ->latest()
                ->get();
        }

        $mapData = $incidents->map(function($i) {
            return [
                'id' => $i->id,
                'display_id' => 'INC-' . str_pad($i->id, 4, '0', STR_PAD_LEFT),
                'type' => $i->type,
                'severity' => $i->severity,
                'status' => $i->status,
                'desc' => $i->description,
                'lat' => $i->latitude,
                'lng' => $i->longitude
            ];
        });

        // Calculate statistics from the collection
        $totalActive = $incidents->whereIn('status', ['pending', 'dispatched', 'en_route', 'arrived'])->count();
        $pending = $incidents->where('status', 'pending')->count();
        $dispatched = $incidents->whereIn('status', ['dispatched', 'en_route', 'arrived'])->count();
        $resolvedToday = $incidents->where('status', 'resolved')
                                   ->where('updated_at', '>=', today())
                                   ->count();

        return view('responder.dashboard', compact('incidents', 'myQueue', 'totalActive', 'pending', 'dispatched', 'resolvedToday', 'mapData'));
    }

    public function incidentDetail($id)
    {
        $incident = Incident::with(['reporter', 'responder', 'statusUpdates.updatedBy', 'media'])->findOrFail($id);
        
        return view('responder.incident-detail', compact('incident'));
    }
}
