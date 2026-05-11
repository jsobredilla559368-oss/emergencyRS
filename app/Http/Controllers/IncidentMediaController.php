<?php

namespace App\Http\Controllers;
use App\Models\Incident;
use App\Models\IncidentMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class IncidentMediaController extends Controller
{
   
 
    public function store(Request $request, Incident $incident)
    {
        $request->validate([
            'media.*' => 'required|file|mimes:jpg,jpeg,png,mp4|max:20480',
        ]);

        foreach ($request->file('media') as $file) {
            $path = $file->store('incidents', 'public');
            $incident->media()->create([
                'file_path' => $path,
                'type' => str_starts_with($file->getMimeType(), 'video') ? 'video' : 'image',
            ]);
        }

        return redirect()->route('incidents.show', $incident)->with('success', 'Media uploaded successfully.');
    }

    

    public function destroy(Incident $incident, IncidentMedia $medium)
    {
        $user = Auth::user();

        // Ensure the media belongs to the incident being addressed
        if ((int) $medium->incident_id !== (int) $incident->id) {
            abort(404, 'Media not found for this incident.');
        }

        // Reporter can only delete their own incident's media
        if ($user->role === 'reporter' && (int) $incident->user_id !== (int) $user->id) {
            abort(403, 'Unauthorized action.');
        }

        Storage::disk('public')->delete($medium->file_path);
        $medium->delete();

        return redirect()->route('incidents.show', $incident)->with('success', 'Media deleted successfully.');
    }
}
