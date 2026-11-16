@extends('layouts.app')
@section('title', 'Users')
@section('content')
    <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
        <form method="GET" action="{{ route('admin.users') }}" class="row g-2 flex-grow-1">
            <div class="col-12 col-md-4">
                <input type="search" name="q" value="{{ request('q') }}" maxlength="50" class="form-control" placeholder="Name, email or student ID" aria-label="Search">
            </div>
            <div class="col-6 col-md-3">
                <select name="role" class="form-select" aria-label="Role">
                    <option value="">All roles</option>
                    @foreach (\App\Enums\UserRole::cases() as $r)
                        <option value="{{ $r->value }}" @selected(request('role') === $r->value)>{{ $r->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select name="status" class="form-select" aria-label="Status">
                    <option value="">Any status</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Deactivated</option>
                </select>
            </div>
            <div class="col-12 col-md-2"><button class="btn btn-light w-100" type="submit">Filter</button></div>
        </form>
        <a href="{{ route('admin.users.create') }}" class="btn btn-brand"><i class="bi bi-person-plus"></i> Add user</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Name</th><th>Email</th><th>Student ID</th><th>Role</th><th>Outlet</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse ($users as $u)
                    <tr>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->student_id }}</td>
                        <td><span class="badge text-bg-secondary">{{ $u->role->label() }}</span></td>
                        <td>{{ $u->outlet?->name }}</td>
                        <td>
                            @if ($u->is_active) <span class="badge text-bg-success">Active</span>
                            @else <span class="badge text-bg-danger">Deactivated</span> @endif
                        </td>
                        <td class="text-end"><a href="{{ route('admin.users.edit', $u->id) }}" class="btn btn-light btn-sm">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No users found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $users->links('pagination::bootstrap-5') }}</div>
@endsection