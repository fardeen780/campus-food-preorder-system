<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $email = Str::lower($data['email']);

        $ok = Auth::attempt(
            ['email' => $email, 'password' => $data['password'], 'is_active' => true],
            $request->boolean('remember')
        );

        if ($ok) {
            $request->session()->regenerate();
            ActivityLog::record('login', $request->user());

            return redirect()->route('dashboard');
        }

        ActivityLog::record('login_failed', null, null, null, ['email' => $email]);

        return back()
            ->withErrors(['email' => 'These credentials do not match our records, or the account is deactivated.'])
            ->onlyInput('email');
    }

    public function destroy(Request $request): RedirectResponse
    {
        ActivityLog::record('logout', $request->user());

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}