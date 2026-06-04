<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Faol foydalanuvchi tekshiruvi
        $user = \App\Models\User::where('email', $credentials['email'])->first();

        if ($user && !$user->is_active) {
            return back()
                ->withErrors(['email' => 'Hisobingiz admin tomonidan bloklangan.'])
                ->withInput($request->only('email'));
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            auth()->user()->update(['last_login_at' => now()]);

            ActivityLog::log(
                'user.login',
                auth()->user(),
                auth()->user()->name . ' tizimga kirdi'
            );

            return redirect()->intended(route('dashboard'));
        }

        return back()
            ->withErrors(['email' => "Email yoki parol noto'g'ri."])
            ->withInput($request->only('email'));
    }

    public function logout(Request $request): RedirectResponse
    {
        if (auth()->check()) {
            ActivityLog::log(
                'user.logout',
                auth()->user(),
                auth()->user()->name . ' tizimdan chiqdi'
            );
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
