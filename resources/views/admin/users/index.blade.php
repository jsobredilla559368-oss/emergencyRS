@extends('layouts.admin')

@section('title', 'User Management — EmergencyRS')
@section('page-title', 'Manage Users')

@push('styles')
<style>
    .role-badge {
        padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.4px;
    }
    .role-reporter   { background:#EFF6FF; color:#2563EB; }
    .role-responder  { background:#ECFDF5; color:#059669; }
    .role-dispatcher { background:#F5F3FF; color:#7C3AED; }
    .role-admin      { background:#FFF7ED; color:#EA580C; }

    .alert-success { background:#ECFDF5; border:1px solid #A7F3D0; color:#065F46; padding:12px 16px; border-radius:10px; margin-bottom:20px; font-size:13px; font-weight:600; }
    .alert-error   { background:#FEF2F2; border:1px solid #FECACA; color:#991B1B; padding:12px 16px; border-radius:10px; margin-bottom:20px; font-size:13px; font-weight:600; }

    table { width:100%; border-collapse:collapse; }
    thead tr { border-bottom:2px solid var(--border); }
    tbody tr { border-bottom:1px solid var(--border); transition:background 0.1s; }
    tbody tr:hover { background:#F8FAFC; }
    th, td { padding:12px 14px; text-align:left; }
    th { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:var(--text-muted); }

    .action-row { display:inline-flex; align-items:center; gap:8px; }
    select.role-select { padding:4px 8px; border-radius:6px; border:1px solid var(--border); font-size:12px; font-family:inherit; }
    .btn-update { padding:5px 12px; background:var(--accent); color:#fff; border:none; border-radius:6px; font-size:12px; font-weight:700; cursor:pointer; }
    .btn-update:hover { opacity:0.85; }
    .btn-delete { padding:5px 10px; background:#FEF2F2; color:#EF4444; border:1px solid #FECACA; border-radius:6px; font-size:12px; font-weight:700; cursor:pointer; transition:all 0.15s; }
    .btn-delete:hover { background:#EF4444; color:#fff; border-color:#EF4444; }

    .you-badge { font-size:10px; background:#FEF9C3; color:#92400E; padding:2px 6px; border-radius:4px; font-weight:700; }
</style>
@endpush

@section('content')

@if(session('success'))
    <div class="alert-success">✓ {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert-error">⚠ {{ session('error') }}</div>
@endif

{{-- Stats row --}}
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); gap:14px; margin-bottom:24px;">
@foreach([
    ['Reporter',   \App\Models\User::where('role','reporter')->count(),   '#2563EB'],
    ['Responder',  \App\Models\User::where('role','responder')->count(),  '#059669'],
    ['Dispatcher', \App\Models\User::where('role','dispatcher')->count(), '#7C3AED'],
    ['Admin',      \App\Models\User::where('role','admin')->count(),      '#EA580C'],
] as [$label, $count, $color])
<div class="card" style="padding:16px 20px; border-left:4px solid {{ $color }};">
    <div style="font-size:24px;font-weight:800;color:{{ $color }};">{{ $count }}</div>
    <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;">{{ $label }}s</div>
</div>
@endforeach
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div style="overflow-x: auto;">
        <table style="min-width: 800px;">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td style="color:var(--text-muted);font-size:12px;">{{ $user->id }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,#2563EB,#7C3AED);
                                        display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:13px;flex-shrink:0;">
                                {{ strtoupper(substr($user->name,0,1)) }}
                            </div>
                            <span style="font-weight:600;">{{ $user->name }}</span>
                            @if($user->id === auth()->id())
                                <span class="you-badge">YOU</span>
                            @endif
                        </div>
                    </td>
                    <td style="font-size:13px;color:var(--text-muted);">{{ $user->email }}</td>
                    <td><span class="role-badge role-{{ $user->role }}">{{ $user->role }}</span></td>
                    <td style="font-size:12px;color:var(--text-muted);">{{ $user->created_at->format('M d, Y') }}</td>
                    <td>
                        <div class="action-row">
                            {{-- Role Update --}}
                            <form action="{{ route('admin.users.update-role', $user->id) }}" method="POST" style="display:inline-flex;gap:6px;">
                                @csrf
                                @method('PATCH')
                                <select name="role" class="role-select">
                                    <option value="reporter"   {{ $user->role==='reporter'   ? 'selected':'' }}>Reporter</option>
                                    <option value="responder"  {{ $user->role==='responder'  ? 'selected':'' }}>Responder</option>
                                    <option value="dispatcher" {{ $user->role==='dispatcher' ? 'selected':'' }}>Dispatcher</option>
                                    <option value="admin"      {{ $user->role==='admin'      ? 'selected':'' }}>Admin</option>
                                </select>
                                <button type="submit" class="btn-update">Update</button>
                            </form>

                            {{-- Delete --}}
                            @if($user->id !== auth()->id())
                            <form action="{{ route('admin.users.delete', $user->id) }}" method="POST"
                                  onsubmit="return confirm('Delete {{ addslashes($user->name) }}? Any incidents they reported will be anonymized. This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-delete">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width:13px;height:13px;display:inline;">
                                        <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Delete
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="padding: 20px; border-top: 1px solid var(--border);">
        {{ $users->links() }}
    </div>
</div>
@endsection
