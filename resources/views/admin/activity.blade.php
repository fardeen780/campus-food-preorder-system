@extends('layouts.app')
@section('title', 'Activity Log')
@section('content')
    <form method="GET" action="{{ route('admin.activity') }}" class="row g-2 mb-3">
        <div class="col-12 col-md-4">
            <select name="action" class="form-select" aria-label="Action">
                <option value="">All actions</option>
                @foreach ($actions as $a)
                    <option value="{{ $a }}" @selected(request('action') === $a)>{{ $a }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-2"><button class="btn btn-light w-100" type="submit">Filter</button></div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0 small">
                <thead><tr><th>Time</th><th>User</th><th>Action</th><th>Details</th><th>IP</th></tr></thead>
                <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td class="text-nowrap">{{ $log->created_at->format('j M, g:i:s A') }}</td>
                        <td>{{ $log->user?->name ?? '—' }}</td>
                        <td><span class="badge text-bg-{{ str_contains($log->action, 'failed') ? 'danger' : 'secondary' }}">{{ $log->action }}</span></td>
                        <td>{{ $log->description }} @if ($log->properties) <span class="text-muted">{{ \Illuminate\Support\Str::limit(json_encode($log->properties), 80) }}</span> @endif</td>
                        <td>{{ $log->ip_address }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No activity recorded.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $logs->links('pagination::bootstrap-5') }}</div>
@endsection