<?php

namespace App\Http\Controllers;

use App\Models\ResponderLocation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    /**
     * Responder pushes their live GPS position.
     * POST /responder/location
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'latitude'    => 'required|numeric|between:-90,90',
            'longitude'   => 'required|numeric|between:-180,180',
            'accuracy'    => 'nullable|numeric|min:0',
            'heading'     => 'nullable|numeric|between:0,360',
            'speed'       => 'nullable|numeric|min:0',
            'incident_id' => 'nullable|integer|exists:incidents,id',
        ]);

        // Upsert — one row per responder (most recent position only)
        ResponderLocation::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'incident_id' => $validated['incident_id'] ?? null,
                'latitude'    => $validated['latitude'],
                'longitude'   => $validated['longitude'],
                'accuracy'    => $validated['accuracy'] ?? null,
                'heading'     => $validated['heading'] ?? null,
                'speed'       => $validated['speed'] ?? null,
                'recorded_at' => now(),
            ]
        );

        return response()->json(['status' => 'ok']);
    }

    /**
     * Dispatcher polls this endpoint to get all live responder positions.
     * GET /dispatcher/live-locations
     * Returns JSON — no blade view needed.
     */
    public function liveLocations()
    {
        $locations = ResponderLocation::with(['user:id,name', 'incident:id,type,status,location_address'])
            ->whereHas('user', fn($q) => $q->where('role', 'responder'))
            ->where('recorded_at', '>=', now()->subMinutes(5)) // only "fresh" pings (last 5 min)
            ->get()
            ->map(fn($loc) => [
                'responder_id'   => $loc->user_id,
                'responder_name' => $loc->user->name,
                'incident_id'    => $loc->incident_id,
                'incident_type'  => optional($loc->incident)->type,
                'incident_status'=> optional($loc->incident)->status,
                'incident_addr'  => optional($loc->incident)->location_address,
                'display_id'     => $loc->incident_id ? 'INC-'.str_pad($loc->incident_id,4,'0',STR_PAD_LEFT) : null,
                'latitude'       => (float) $loc->latitude,
                'longitude'      => (float) $loc->longitude,
                'accuracy'       => (float) $loc->accuracy,
                'heading'        => $loc->heading ? (float) $loc->heading : null,
                'speed'          => $loc->speed ? round((float)$loc->speed * 3.6, 1) : null, // m/s → km/h
                'last_seen'      => $loc->recorded_at->diffForHumans(),
                'is_stale'       => $loc->recorded_at->lt(now()->subMinutes(2)),
            ]);

        return response()->json($locations);
    }
}
