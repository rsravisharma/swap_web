<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    /**
     * Show admin login form
     */
    public function showLoginForm()
    {
        if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            
            // Redirect based on role
            if ($admin->role === 'manager') {
                return redirect()->route('admin.pdf-manager.index');
            }
            
            return redirect()->route('admin.dashboard');
        }
        
        return view('admin.auth.login');
    }

    /**
     * Handle admin login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Rate limiting
        $key = Str::lower($request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            
            throw ValidationException::withMessages([
                'email' => ['Too many login attempts. Please try again in ' . ceil($seconds / 60) . ' minutes.'],
            ]);
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        // Check if admin exists and is active
        $admin = Admin::where('email', $credentials['email'])->first();

        if (!$admin) {
            RateLimiter::hit($key, 300); // 5 minutes
            
            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        if (!$admin->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated. Please contact the system administrator.'],
            ]);
        }

        // Attempt login
        if (Auth::guard('admin')->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            RateLimiter::clear($key);

            // Update last login
            $admin->updateLastLogin($request->ip());

            // Redirect based on role
            return $this->redirectBasedOnRole($admin);
        }

        RateLimiter::hit($key, 300); // 5 minutes

        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    /**
     * Redirect admin based on role
     */
    protected function redirectBasedOnRole($admin)
    {
        switch ($admin->role) {
            case 'manager':
                return redirect()->intended(route('admin.pdf-manager.index'));
            
            case 'super_admin':
                return redirect()->intended(route('admin.dashboard'));
            
            default:
                // Fallback for any other roles
                return redirect()->intended(route('admin.pdf-manager.index'));
        }
    }

    /**
     * Handle admin logout
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
