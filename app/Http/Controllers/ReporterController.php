<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ReporterController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'emergency_type' => 'required|in:medical,fire,crime,disaster,accident,flood,earthquake,hazmat,missing_person,others',
            'severity' => 'required|in:low,medium,high',
            'description' => 'required|string|max:1000',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'address' => 'required|string|max:255',
            'media.*' => 'nullable|file|mimes:jpg,jpeg,png,mp4|max:20480',
        ]);

        // If the user isn't logged in, we need a Guest account to satisfy user_id foreign key constraint
        $userId = auth()->id();

        if (!$userId) {
            // Check if there's already a guest ID in the session for this reporter
            $guestId = session('guest_reporter_id');

            if (!$guestId) {
                // Create a new anonymous guest user
                $guestUser = User::create([
                    'name' => 'Anonymous Reporter',
                    'email' => 'guest_' . Str::random(10) . '@emergency.local',
                    'password' => Hash::make(Str::random(16)),
                    'role' => 'reporter',
                ]);
                // Add is_guest if migration supports it, otherwise role is enough
                $guestId = $guestUser->id;
                session(['guest_reporter_id' => $guestId]);
            }
            $userId = $guestId;
        }

        // Create the incident
        $incident = Incident::create([
            'user_id' => $userId,
            'type' => $validated['emergency_type'],
            'severity' => $validated['severity'],
            'description' => $validated['description'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'location_address' => $validated['address'] ?? null,
            'status' => 'pending',
        ]);

        // Handle Media Uploads
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $path = $file->store('incidents', 'public');
                $incident->media()->create([
                    'file_path' => $path,
                    'type' => str_starts_with($file->getMimeType(), 'video') ? 'video' : 'image',
                ]);
            }
        }

        // Notify Dispatchers and Admins
        $dispatchers = User::whereIn('role', ['admin', 'dispatcher'])->get();
        foreach ($dispatchers as $dispatcher) {
            \App\Models\IncidentNotification::create([
                'incident_id' => $incident->id,
                'user_id'     => $dispatcher->id,
                'title'       => 'New Emergency Report',
                'message'     => strtoupper($incident->type) . ' reported at ' . ($incident->location_address ?? 'Unknown Location'),
            ]);
        }

        return redirect()->route('reporter.track', ['id' => 'INC-' . str_pad($incident->id, 4, '0', STR_PAD_LEFT)])
            ->with('success', 'Emergency report submitted successfully.');
    }

    public function track(Request $request, $id = null)
    {
        $searchId = $id ?? $request->get('id');
        $incident = null;

        if ($searchId) {
            // Support both numeric ID and "INC-xxxx" format
            $numericId = (int) str_replace('INC-', '', $searchId);
            $incident = Incident::with(['media', 'statusUpdates'])->find($numericId);
        }

        return view('reporter.track', compact('incident'));
    }

    public function dashboard()
    {
        $user = auth()->user();
        $incidents = Incident::where('user_id', $user->id)
            ->with(['responder', 'statusUpdates'])
            ->latest()
            ->get();

        $totalReports = $incidents->count();
        $resolvedReports = $incidents->where('status', 'resolved')->count();
        $pendingReports = $incidents->where('status', 'pending')->count();
        
        // Calculate average credibility score
        $avgCredibility = $incidents->avg(function($incident) {
            return $incident->credibility_score;
        }) ?? 0;

        return view('reporter.dashboard', compact(
            'incidents', 
            'totalReports', 
            'resolvedReports', 
            'pendingReports',
            'avgCredibility'
        ));
    }
    public function withdraw(\App\Models\Incident $incident)
    {
        // Only the owner can withdraw, and only if still pending
        if ($incident->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        if ($incident->status !== 'pending') {
            return back()->with('error', 'Only pending reports can be withdrawn. Contact the dispatcher if a unit is already dispatched.');
        }

        $incident->delete();

        return redirect()->route('reporter.dashboard')
            ->with('success', 'Your report has been withdrawn successfully.');
    }
}
