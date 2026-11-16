@extends('layouts.app')
@section('title', $user->exists ? 'Edit User' : 'Add User')
@section('content')
    @php $currentRole = old('role', $user->role?->value ?? 'student'); @endphp
    <div class="row justify-content-center">
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="POST" action="{{ $user->exists ? route('admin.users.update', $user->id) : route('admin.users.store') }}">
                        @csrf
                        @if ($user->exists) @method('PUT') @endif

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="name">Full name</label>
                                <input id="name" name="name" value="{{ old('name', $user->name) }}" maxlength="100" required
                                       class="form-control @error('name') is-invalid @enderror">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="email">Email</label>
                                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required
                                       class="form-control @error('email') is-invalid @enderror">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="phone">Mobile (11 digits)</label>
                                <input id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                                       class="form-control @error('phone') is-invalid @enderror">
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="role">Role</label>
                                <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
                                    @foreach (\App\Enums\UserRole::cases() as $r)
                                        <option value="{{ $r->value }}" @selected($currentRole === $r->value)>{{ $r->label() }}</option>
                                    @endforeach
                                </select>
                                @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6" id="studentBox">
                                <label class="form-label" for="student_id">Student ID (7 digits)</label>
                                <input id="student_id" name="student_id" value="{{ old('student_id', $user->student_id) }}" maxlength="7"
                                       class="form-control @error('student_id') is-invalid @enderror">
                                @error('student_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6" id="outletBox">
                                <label class="form-label" for="outlet_id">Outlet (staff)</label>
                                <select id="outlet_id" name="outlet_id" class="form-select @error('outlet_id') is-invalid @enderror">
                                    <option value="">Choose...</option>
                                    @foreach ($outlets as $o)
                                        <option value="{{ $o->id }}" @selected((int) old('outlet_id', $user->outlet_id) === $o->id)>{{ $o->name }}</option>
                                    @endforeach
                                </select>
                                @error('outlet_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="password">{{ $user->exists ? 'New password (leave blank to keep)' : 'Password (min 8)' }}</label>
                                <input id="password" type="password" name="password" autocomplete="new-password"
                                       class="form-control @error('password') is-invalid @enderror" {{ $user->exists ? '' : 'required' }}>
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            @if ($user->exists)
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                               @checked(old() ? old('is_active') : $user->is_active)>
                                        <label class="form-check-label" for="is_active">Account is active</label>
                                    </div>
                                    <div class="form-text">Deactivating an account logs the person out and blocks sign-in.</div>
                                </div>
                            @endif
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button class="btn btn-brand" type="submit">{{ $user->exists ? 'Save changes' : 'Create account' }}</button>
                            <a href="{{ route('admin.users') }}" class="btn btn-light">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const role = document.getElementById('role');
        const sync = () => {
            document.getElementById('studentBox').style.display = role.value === 'student' ? '' : 'none';
            document.getElementById('outletBox').style.display = role.value === 'staff' ? '' : 'none';
        };
        role.addEventListener('change', sync);
        sync();
    </script>
@endsection