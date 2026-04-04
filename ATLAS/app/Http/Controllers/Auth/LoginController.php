<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    // Static credentials for testing
    private const ADMIN_USERNAME = 'admin';
    private const ADMIN_PASSWORD = 'admin';

    /**
     * Show the login form
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle login attempt
     */
    public function store(Request $request)
    {
        // Validate input
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username is required',
            'password.required' => 'Password is required',
        ]);

        $username = $request->input('username');
        $password = $request->input('password');

        // Check static credentials
        if ($username === self::ADMIN_USERNAME && $password === self::ADMIN_PASSWORD) {
            // Create a session for the user
            Session::put('authenticated', true);
            Session::put('user_username', $username);
            Session::put('user_name', 'Admin User');

            return redirect()->route('dashboard')->with('success', 'Login successful!');
        }

        // Login failed
        return back()->withErrors([
            'username' => 'Invalid username or password.',
        ])->onlyInput('username');
    }

    /**
     * Handle logout
     */
    public function logout()
    {
        Session::flush();
        return redirect()->route('login')->with('success', 'Logged out successfully.');
    }
}
