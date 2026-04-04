<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;

class LogoutController
{
    /**
     * Handle the logout.
     */
    public function __invoke(): RedirectResponse
    {
        Session::flush();
        Session::regenerate();

        return redirect('/login')->with('success', 'Logged out successfully.');
    }
}
