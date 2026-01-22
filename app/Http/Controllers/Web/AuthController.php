<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.user-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            // Update login streak
            auth()->user()->updateLoginStreak();

            return response()->json([
                'success' => true,
                'message' => 'Login successful!',
                'redirect' => route('pdf-books.index')
            ]);
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }

    public function register(Request $request)
    {
        $credentials = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:15',
            'terms_accepted' => 'required|accepted'
        ]);

        try {
            $user = User::create([
                'name' => $credentials['name'],
                'email' => $credentials['email'],
                'phone' => $credentials['phone'],
                'password' => Hash::make($credentials['password']),
                'referral_code' => User::generateUniqueReferralCode(),
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            Auth::login($user);
            $request->session()->regenerate();

            // Generate referral code and login streak
            $user->updateLoginStreak();

            return response()->json([
                'success' => true,
                'message' => 'Registration successful! Welcome to Swap!',
                'redirect' => route('pdf-books.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.'
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    // Google Login Methods
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::where('google_id', $googleUser->id)
                ->orWhere('email', $googleUser->email)
                ->first();

            if ($user) {
                if (!$user->google_id) {
                    $user->update(['google_id' => $googleUser->id]);
                }
                if ($googleUser->avatar && !$user->profile_image) {
                    $user->update(['profile_image' => $googleUser->avatar]);
                }
                Auth::login($user);
                session()->regenerate();
                $user->updateLoginStreak();

                // Check if from web or API
                $intended = session('url.intended', route('pdf-books.index'));
                return redirect($intended);
            } else {
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'profile_image' => $googleUser->avatar,
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                    'is_active' => true,
                ]);
                Auth::login($user);
                session()->regenerate();
                return redirect()->route('pdf-books.index');
            }
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Google login failed. Please try again.');
        }
    }
}
