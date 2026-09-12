<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password'], 'is_active' => true], $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();
            $target = route('dashboard');
            if (! $user->isSuperAdmin() && ! $user->hasPermission('dashboard.view')) {
                if ($user->hasPermission('pos.access')) {
                    $target = route('pos.index');
                } elseif ($user->hasPermission('sales.view')) {
                    $target = route('sales.index');
                } elseif ($user->hasPermission('products.view')) {
                    $target = route('products.index');
                }
            }

            return redirect()->intended($target)
                ->with('success', 'Welcome back, '.$user->name.'!');
        }

        return back()->withErrors([
            'email' => 'Invalid email/password, or your account has been deactivated.',
        ])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('info', 'You have been successfully logged out.');
    }
}
