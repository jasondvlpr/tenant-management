<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('tenants.index');
        }
        
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request using Username OR Email.
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
            'captcha' => 'required|captcha',
        ], [
            'captcha.captcha' => 'Kode Captcha yang Anda masukkan tidak sesuai. Silakan coba lagi.'
        ]);

        $loginInput = $request->input('login');
        
        // Determine whether the input is an email address or username
        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $fieldType => $loginInput,
            'password' => $request->input('password')
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('tenants.index'))->with('success', 'Selamat datang kembali! Sesi Control Plane Anda telah diotorisasi dengan aman.');
        }

        return back()->withErrors([
            'login' => 'Kredensial username/email atau password yang Anda masukkan tidak terdaftar di sistem kami.',
        ])->onlyInput('login');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Anda telah berhasil keluar dari sistem Super Central Command Hub.');
    }
}
