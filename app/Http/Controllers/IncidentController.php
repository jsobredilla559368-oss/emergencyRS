<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class IncidentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        $query = Incident::with(['reporter', 'responder', 'statusUpdates'])->latest();

        // Reporters only see their own incidents
        if ($user->role === 'reporter') {
            $query->where('user_id', $user->id);
        }
        // Responders only see assigned incidents
        elseif ($user->role === 'responder') {
            $query->where('responder_id', $user->id);
        }
        // Admins and dispatchers see all

        $incidents = $query->get();

        return view('incidents.index', compact('incidents'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('incidents.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:medical,fire,crime,disaster,accident,flood,earthquake,hazmat,missing_person,others|max:255',
            'severity' => 'required|in:low,medium,high',
            'description' => 'required|string|max:1000',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'location_address' => 'required|string|max:255',
            'media.*' => 'nullable|file|mimes:jpg,jpeg,png,mp4,|max:20480',
        ]);

        $incident = Incident::create([
            'user_id' => Auth::id(),
            'type' => $validated['type'],
            'severity' => $validated['severity'],
            'description' => $validated['description'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'location_address' => $validated['location_address'] ?? null,
            'status' => 'pending',
        ]);

        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $path = $file->store('incidents', 'public');
                $incident->media()->create([
                    'file_path' => $path,
                    'type' => str_starts_with($file->getMimeType(), 'video') ? 'video' : 'image',
                ]);
            }
        }

        return $this->redirectToIncidentDetail($incident)->with('success', 'Emergency report created successfully.');
    }

    private function redirectToIncidentDetail($incident)
    {
        $user = Auth::user();
        if ($user->role === 'dispatcher') {
            return redirect()->route('dispatcher.incident', $incident->id);
        } elseif ($user->role === 'responder') {
            return redirect()->route('responder.incident', $incident->id);
        } else {
            return redirect()->route('reporter.track', ['id' => 'INC-' . str_pad($incident->id, 4, '0', STR_PAD_LEFT)]);
        }
    }


    public function show(Incident $incident)
    {
        $this->authorizeView($incident);
        return $this->redirectToIncidentDetail($incident);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(incident $incident)
    {
            $this->authorizeView($incident);
    
            return view('incidents.edit', compact('incident'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, incident $incident)
    {
        $this->authorizeView($incident);

        $validated = $request->validate([
            'type' => 'required|in:medical,fire,crime,disaster,accident,flood,earthquake,hazmat,missing_person,others|max:255',
            'severity' => 'required|in:low,medium,high',
            'description' => 'required|string|max:1000',
            'location_address' => 'required|string|max:255',
        ]);

        $incident->update($validated);

        return $this->redirectToIncidentDetail($incident)->with('success', 'Incident updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Incident $incident)
    {
        $this->authorizeView($incident);

        Incident::destroy($incident->id);

        return redirect()->route('incidents.index')->with('success', 'Incident deleted successfully.');
    }

    public function assign(Request $request, Incident $incident)
    {
        // Guard: cannot re-dispatch a resolved incident
        if ($incident->status === 'resolved') {
            return redirect()->back()
                ->with('error', 'This incident is already resolved and cannot be dispatched again.');
        }

        $validated = $request->validate([
            'responder_id' => [
                'required',
                Rule::exists('users', 'id')->where('role', 'responder'),
            ],
        ]);

        $incident->update([
            'responder_id' => $validated['responder_id'],
            'status' => 'dispatched',
        ]);

        $incident->statusUpdates()->create([
            'updated_by' => Auth::id(),
            'status'     => 'dispatched',
            'notes'      => 'Responder assigned and Dispatched.',
        ]);

        \App\Models\IncidentNotification::create([
            'incident_id' => $incident->id,
            'user_id' => $incident->user_id, // Notify the reporter
            'title' => 'Unit Dispatched',
            'message' => 'A responder has been assigned to your emergency report.',
        ]);

        if ($validated['responder_id'] != Auth::id()) {
            \App\Models\IncidentNotification::create([
                'incident_id' => $incident->id,
                'user_id' => $validated['responder_id'], // Notify the responder
                'title' => 'New Assignment',
                'message' => 'You have been assigned to a new emergency incident.',
            ]);
        }

        return $this->redirectToIncidentDetail($incident)
        ->with('success', 'Responder assigned successfully.');
    }

    private function authorizeView(Incident $incident)
    {
        $user = Auth::user();

        // Admin and dispatcher can view all incidents
        if (in_array($user->role, ['admin', 'dispatcher'])) {
            return;
        }

        // Responders can only view their assigned incidents
        if ($user->role === 'responder' && $incident->responder_id !== $user->id) {
            abort(403, 'You are not assigned to this incident.');
        }

        // Reporters can only view their own reported incidents
        if ($user->role === 'reporter' && $incident->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }
    }
}
