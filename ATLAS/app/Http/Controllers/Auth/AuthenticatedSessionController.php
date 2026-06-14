<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Route users to their respective workstations based on role
        $user = Auth::user();
        $userRole = strtolower(trim($user->role ?? ''));
        
        // Records Officer forcefully goes to the Intake Workstation
        if ($userRole === 'records_officer') {
            return redirect()->route('applications.index');
        }
        
        // Land Officer forcefully goes to the Processing Queue
        if ($userRole === 'land_officer') {
            return redirect()->route('processing-queue');
        }

        // Others (admin, processing) go to the Dashboard, or wherever they originally intended to go
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
