<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show the application login form.
     */
    public function showLoginForm()
    {
        // If user is already authenticated, redirect to map
        if (Auth::check()) {
            return redirect('/map');
        }
        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        // Map the username input to the 'name' column in the database
        $authCredentials = [
            'name' => $credentials['username'],
            'password' => $credentials['password']
        ];

        if (Auth::attempt($authCredentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended('/map')
                ->with('success', 'Selamat datang kembali! Anda berhasil login.');
        }

        return back()->withErrors([
            'username' => 'Username atau password yang Anda masukkan salah.',
        ])->onlyInput('username');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')
            ->with('success', 'Anda telah berhasil logout.');
    }
}
