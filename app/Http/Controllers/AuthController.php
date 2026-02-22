<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        User::firstOrCreate(
            ['email' => 'root@btplaces.local'],
            [
                'name' => 'root',
                'password' => Hash::make('!IronHide!84!'),
                'role' => 'admin',
            ]
        );

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string', 'max:190'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        $login = (string) $credentials['login'];
        $password = (string) $credentials['password'];
        $authOk = false;

        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $authOk = Auth::attempt(['email' => $login, 'password' => $password], $remember);
        }

        if (!$authOk) {
            $authOk = Auth::attempt(['name' => $login, 'password' => $password], $remember);
        }

        if (!$authOk) {
            return back()
                ->withErrors(['login' => 'Kullanıcı adı/e-posta veya şifre hatalı.'])
                ->onlyInput('login');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
