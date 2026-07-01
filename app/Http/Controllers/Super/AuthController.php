<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::guard('super')->check()) {
            return redirect()->route('super.organizations.index');
        }

        return view('super.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $attempted = Auth::guard('super')->attempt([
            'email'    => $credentials['email'],
            'password' => $credentials['password'],
            'is_super' => true,
        ]);

        if (!$attempted) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('super.organizations.index'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('super')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('super.login');
    }
}
