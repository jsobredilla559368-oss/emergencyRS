<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StatusUpdateController extends Controller
{
   
    public function store(Request $request, Incident $incident)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,dispatched,en_route,arrived,resolved',
            'notes'  => 'nullable|string|max:500',
        ]);

       
        $incident->update(['status' => $validated['status']]);

       
        $incident->statusUpdates()->create([
            'updated_by' => Auth::id(),
            'status'     => $validated['status'],
            'notes'      => $validated['notes'] ?? null,
        ]);

        if ($validated['status'] === 'resolved') {
            \App\Models\IncidentNotification::create([
                'incident_id' => $incident->id,
                'user_id'     => $incident->user_id,
                'title'       => 'Incident Resolved',
                'message'     => 'Your emergency report has been marked as resolved. Notes: ' . ($validated['notes'] ?? 'No notes provided.'),
            ]);
        }

        $user = Auth::user();
        if ($user->role === 'dispatcher') {
            return redirect()->route('dispatcher.incident', $incident->id)->with('success', 'Status updated successfully.');
        } elseif ($user->role === 'responder') {
            return redirect()->route('responder.incident', $incident->id)->with('success', 'Status updated successfully.');
        }

        return redirect()->route('incidents.index')->with('success', 'Status updated successfully.');
    }
}
