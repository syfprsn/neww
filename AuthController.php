<?php

namespace App\Http\Controllers;

use App\Models\User; // Add this line to import the User model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            if (Auth::user()->level == 'Admin') {
                return redirect()->route('dashboard');
            }
            return redirect()->route('landing');
        }
        $title = 'Login';
        return view('admin.auth.login', compact('title'));
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if (Auth::user()->level == 'Admin') {
                return redirect()->intended('dashboard');
            } else {
                return redirect()->intended('/'); // Redirect ke landing page untuk user biasa
            }
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function register()
    {
        $title = 'Register';
        return view('admin.auth.register', compact('title'));
    }

    public function registerPost(Request $request)
    {
        // Validate the incoming data
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:4',
        ]);

        // Create a new user and save the data to the database
        $user = new User();
        $user->username = $request->input('nama');
        $user->email = $request->input('email');
        $user->password = bcrypt($request->input('password'));
        $user->level = 'Pelanggan'; // Set default level to Pelanggan
        $user->save();

        // Redirect to the login page with a success message
        return redirect()->route('login')->with('success', 'Registrasi berhasil! Silakan login.');
    }
}
