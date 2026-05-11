<?php

namespace App\Http\Controllers;

use App\Models\IncidentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncidentNotificationController extends Controller
{
    
    public function index()
    {
        $notifications = IncidentNotification::where('user_id', Auth::id())
            ->with('incident')
            ->latest()
            ->get();

        return view('notifications.index', compact('notifications'));
    }

   
    public function show(IncidentNotification $notification)
    {
        
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Unauthorized.');
        }

        
        if (is_null($notification->read_at)) {
            $notification->update(['read_at' => now()]);
        }

        return view('notifications.show', compact('notification'));
    }
    public function destroy(IncidentNotification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Unauthorized.');
        }

        $notification->delete();

        return redirect()->back()->with('success', 'Notification dismissed.');
    }
}
