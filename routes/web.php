<?php

use App\Http\Controllers\IncidentController;
use App\Http\Controllers\IncidentMediaController;
use App\Http\Controllers\StatusUpdateController;
use App\Http\Controllers\IncidentNotificationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ReporterController;
use App\Http\Controllers\LocationController;

// ── Reporter (public — no auth required) ──
Route::get('/', function () {
    return view('reporter.report-form');
})->name('reporter.form');

Route::post('/report', [ReporterController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('reporter.store');
Route::get('/track/{id?}', [ReporterController::class, 'track'])->name('reporter.track');

// ── Reporter Dashboard (Auth Required) ──
Route::prefix('reporter')->name('reporter.')->middleware(['auth', 'verified', 'role:reporter'])->group(function () {
    Route::get('/dashboard', [ReporterController::class, 'dashboard'])->name('dashboard');
    Route::delete('/incidents/{incident}/withdraw', [ReporterController::class, 'withdraw'])->name('incidents.withdraw');
});

use App\Http\Controllers\ResponderController;

// ── Responder UI ──
Route::prefix('responder')->name('responder.')->middleware(['auth', 'verified', 'role:responder'])->group(function () {
    Route::get('/dashboard', [ResponderController::class, 'dashboard'])->name('dashboard');
    Route::get('/incidents', [ResponderController::class, 'dashboard'])->name('incidents');
    Route::get('/incident/{id}', [ResponderController::class, 'incidentDetail'])->name('incident');
    Route::post('/location', [LocationController::class, 'update'])->name('location.update');
});

use App\Http\Controllers\DispatcherController;

// ── Dispatcher UI ──
Route::prefix('dispatcher')->name('dispatcher.')->middleware(['auth', 'verified', 'role:dispatcher'])->group(function () {
    Route::get('/dashboard', [DispatcherController::class, 'dashboard'])->name('dashboard');
    Route::get('/incident/{id}', [DispatcherController::class, 'incidentDetail'])->name('incident');
    Route::get('/incident-log', [DispatcherController::class, 'incidentLog'])->name('incident-log');
    Route::get('/unit-tracking', [DispatcherController::class, 'unitTracking'])->name('unit-tracking');
    Route::get('/live-locations', [LocationController::class, 'liveLocations'])->name('live-locations');
});

use App\Http\Controllers\AdminController;

// ── Admin UI ──
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::patch('/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('users.update-role');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');
    Route::get('/system-logs', [AdminController::class, 'systemLogs'])->name('system-logs');
    Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    } elseif ($user->role === 'dispatcher') {
        return redirect()->route('dispatcher.dashboard');
    } elseif ($user->role === 'responder') {
        return redirect()->route('responder.dashboard');
    } elseif ($user->role === 'reporter') {
        return redirect()->route('reporter.dashboard');
    }
    return redirect()->route('reporter.form'); // default for others
})->middleware(['auth', 'verified'])->name('dashboard');

// ── Profile (All authenticated roles) ──
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/my-profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/my-profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/my-profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/dashboard', function () {
        $user = auth()->user();
        if ($user->role === 'admin')        return redirect()->route('admin.dashboard');
        if ($user->role === 'dispatcher')   return redirect()->route('dispatcher.dashboard');
        if ($user->role === 'responder')    return redirect()->route('responder.dashboard');
        if ($user->role === 'reporter')     return redirect()->route('reporter.dashboard');
        return redirect()->route('reporter.form');
    })->name('dashboard');

    Route::resource('incidents', IncidentController::class);
    Route::resource('incidents.media', IncidentMediaController::class)->only(['store', 'destroy'])
        ->parameters(['media' => 'medium']);

    Route::resource('notifications', IncidentNotificationController::class)->only(['index', 'destroy']);
});

Route::middleware('auth', 'verified', 'role:responder|dispatcher')->group(function () {
    Route::resource('incidents.status-updates', StatusUpdateController::class)->only(['store']);
});

Route::middleware('auth', 'verified', 'role:dispatcher')->group(function () {
    Route::patch('/incidents/{incident}/assign', [IncidentController::class, 'assign'])->name('incidents.assign');
});

require __DIR__ . '/auth.php';
