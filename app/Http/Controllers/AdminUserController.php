<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    private function rules(?int $ignoreId = null): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($ignoreId)],
            'phone' => ['nullable', 'regex:/^01\d{9}$/'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'student_id' => ['required_if:role,student', 'nullable', 'regex:/^\d{7}$/', Rule::unique('users', 'student_id')->ignore($ignoreId)],
            'outlet_id' => ['required_if:role,staff', 'nullable', Rule::exists('outlets', 'id')],
        ];
    }

    private function normalizeEmail(Request $request): void
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);
    }

    public function index(Request $request): View
    {
        $f = $request->validate([
            'q' => ['nullable', 'string', 'max:50'],
            'role' => ['nullable', Rule::enum(UserRole::class)],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        $query = User::query()->with('outlet');

        if (! empty($f['q'])) {
            $term = '%'.addcslashes($f['q'], '%_\\').'%';
            $query->where(fn ($q) => $q
                ->whereRaw('name ilike ?', [$term])
                ->orWhereRaw('email ilike ?', [$term])
                ->orWhereRaw('student_id ilike ?', [$term]));
        }
        if (! empty($f['role'])) {
            $query->where('role', $f['role']);
        }
        if (($f['status'] ?? null) === 'active') {
            $query->where('is_active', true);
        } elseif (($f['status'] ?? null) === 'inactive') {
            $query->where('is_active', false);
        }

        return view('admin.users', [
            'users' => $query->orderBy('name')->paginate(15)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.user-form', [
            'user' => new User(['role' => 'student', 'is_active' => true]),
            'outlets' => Outlet::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->normalizeEmail($request);
        $data = $request->validate($this->rules() + ['password' => ['required', Password::min(8)]]);
        $role = UserRole::from($data['role']);

        try {
            $user = (new User)->forceFill([
                'name' => trim($data['name']),
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'role' => $role,
                'student_id' => $role === UserRole::Student ? $data['student_id'] : null,
                'outlet_id' => $role === UserRole::Staff ? $data['outlet_id'] : null,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
            $user->save();
        } catch (UniqueConstraintViolationException) {
            return back()->withInput()->with('error', 'That email or student ID is already in use.');
        }

        ActivityLog::record('user.created', $request->user(), $user, 'Account created for '.$user->email, ['role' => $role->value]);

        return redirect()->route('admin.users')->with('status', 'Account created for '.$user->name.'.');
    }

    public function edit(int $id): View
    {
        return view('admin.user-form', [
            'user' => User::findOrFail($id),
            'outlets' => Outlet::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->normalizeEmail($request);
        $data = $request->validate($this->rules($id) + ['password' => ['nullable', Password::min(8)]]);

        $actor = $request->user();
        $newRole = UserRole::from($data['role']);
        $active = $request->boolean('is_active');

        try {
            $result = DB::transaction(function () use ($id, $data, $actor, $newRole, $active) {
                $user = User::query()->lockForUpdate()->findOrFail($id);
                $oldRole = $user->role;
                $wasActive = $user->is_active;

                if ($user->id === $actor->id && ($newRole !== UserRole::Admin || ! $active)) {
                    throw new \DomainException('You cannot remove your own administrator access or deactivate your own account.');
                }

                if ($oldRole === UserRole::Admin && $wasActive && ($newRole !== UserRole::Admin || ! $active)) {
                    $others = User::query()
                        ->where('role', UserRole::Admin->value)
                        ->where('is_active', true)
                        ->where('id', '!=', $user->id)
                        ->lockForUpdate()
                        ->get(['id']);

                    if ($others->isEmpty()) {
                        throw new \DomainException('At least one active administrator must remain.');
                    }
                }

                if ($oldRole === UserRole::Student && $newRole !== UserRole::Student
                    && ($user->placedOrders()->exists() || $user->heldTokens()->exists()
                        || $user->sentTransfers()->exists() || $user->receivedTransfers()->exists())) {
                    throw new \DomainException('This student has order history, so their role cannot be changed. Deactivate the account instead.');
                }

                $attributes = [
                    'name' => trim($data['name']),
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'role' => $newRole,
                    'student_id' => $newRole === UserRole::Student ? $data['student_id'] : null,
                    'outlet_id' => $newRole === UserRole::Staff ? $data['outlet_id'] : null,
                    'is_active' => $active,
                ];

                $passwordChanged = ! empty($data['password']);

                if ($passwordChanged) {
                    $attributes['password'] = $data['password'];
                }

                if ($passwordChanged || ($wasActive && ! $active)) {
                    $attributes['remember_token'] = Str::random(60);
                }

                $user->forceFill($attributes)->save();

                return [$user, $oldRole, $wasActive, $passwordChanged];
            });
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (UniqueConstraintViolationException) {
            return back()->withInput()->with('error', 'That email or student ID is already in use.');
        }

        [$user, $oldRole, $wasActive, $passwordChanged] = $result;

        if ($oldRole !== $newRole) {
            ActivityLog::record('user.role_changed', $actor, $user, 'Role changed for '.$user->email,
                ['from' => $oldRole->value, 'to' => $newRole->value]);
        }

        if ($wasActive !== $active) {
            ActivityLog::record($active ? 'user.reactivated' : 'user.deactivated', $actor, $user,
                ($active ? 'Account reactivated: ' : 'Account deactivated: ').$user->email);
        }

        if ($passwordChanged) {
            ActivityLog::record('user.password_reset', $actor, $user, 'Password reset for '.$user->email);
        }

        ActivityLog::record('user.updated', $actor, $user, 'Account details saved for '.$user->email);

        if (($wasActive && ! $active) || $passwordChanged) {
            if (config('session.driver') === 'database') {
                DB::table('sessions')->where('user_id', $user->id)->delete();
            }
        }

        return redirect()->route('admin.users')->with('status', 'Account for '.$user->name.' was saved.');
    }
}