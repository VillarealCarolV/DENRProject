<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Define available roles
     */
    public static function getRoles()
    {
        return [
            'admin' => 'Administrator',
            'records_officer' => 'Records Officer',
            'land_officer' => 'Land Officer',
            'user' => 'Regular User',
        ];
    }

    /**
     * Display a listing of all users.
     */
    public function index()
    {
        // Only admins can manage users
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $users = User::paginate(15);
        $roles = self::getRoles();

        return view('users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        // Only admins can create users
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $roles = self::getRoles();

        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created user in database.
     */
    public function store(Request $request)
    {
        // Only admins can create users
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'in:admin,records_officer,land_officer,user'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        // Only admins or the user themselves can view
        if (Auth::user()->role !== 'admin' && Auth::user()->id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $roles = self::getRoles();

        return view('users.show', compact('user', 'roles'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        // Only admins or the user themselves can edit
        if (Auth::user()->role !== 'admin' && Auth::user()->id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $roles = self::getRoles();

        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user in database.
     */
    public function update(Request $request, User $user)
    {
        // Only admins can change roles
        // Users can only update their own name and email
        if (Auth::user()->role !== 'admin' && Auth::user()->id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'in:admin,records_officer,land_officer,user'],
        ]);

        // Non-admin users cannot change their role
        if (Auth::user()->role !== 'admin') {
            unset($validated['role']);
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        if (isset($validated['role'])) {
            $user->role = $validated['role'];
        }

        $user->save();

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from database.
     */
    public function destroy(User $user)
    {
        // Only admins can delete users
        if (Auth::user()->role !== 'admin') {
            $message = 'Unauthorized: Only admins can delete users.';
            if (request()->wantsJson() || request()->header('Accept') === 'application/json') {
                return response()->json(['success' => false, 'message' => $message, 'error' => $message], 403);
            }
            abort(403, $message);
        }

        // Prevent deleting the current user
        if (Auth::user()->id === $user->id) {
            $message = 'You cannot delete your own account.';
            if (request()->wantsJson() || request()->header('Accept') === 'application/json') {
                return response()->json(['success' => false, 'message' => $message, 'error' => $message], 422);
            }
            return redirect()->route('users.index')->with('error', $message);
        }

        try {
            $userName = $user->name;

            // Log deletion attempt
            \Log::info('Deleting user', [
                'user_id' => $user->id,
                'user_name' => $userName,
                'deleted_by' => Auth::user()->name,
                'deleted_by_id' => Auth::id(),
                'timestamp' => now()
            ]);

            $user->delete();

            \Log::info('User successfully deleted', [
                'user_id' => $user->id,
                'user_name' => $userName
            ]);

            // Return JSON response for AJAX requests
            if (request()->wantsJson() || request()->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => true,
                    'message' => 'User deleted successfully',
                    'user_id' => $user->id
                ]);
            }

            // Redirect for normal requests
            return redirect()->route('users.index')->with('success', 'User deleted successfully.');

        } catch (\Exception $e) {
            \Log::error('Error deleting user', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'deleted_by' => Auth::user()->name
            ]);

            $message = 'An error occurred while deleting the user: ' . $e->getMessage();

            if (request()->wantsJson() || request()->header('Accept') === 'application/json') {
                return response()->json(['success' => false, 'message' => $message, 'error' => $e->getMessage()], 500);
            }

            return back()->with('error', $message);
        }
    }
}
