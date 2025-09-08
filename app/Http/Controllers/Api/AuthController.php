<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Models\EmailVerificationCode;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use App\Notifications\EmailVerificationCodeNotification;
use Illuminate\Auth\Events\Verified;
use Google_Client;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * User registration
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            // 'phone' => 'nullable|string|max:20',
            // 'university' => 'nullable|string|max:255',
            // 'course' => 'nullable|string|max:255',
            // 'semester' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'university' => $request->university,
                'course' => $request->course,
                'semester' => $request->semester,
                'email_verified_at' => null,
            ]);

            // Send OTP for verification
            $this->sendVerificationOtp($user);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully. Please check your email for verification OTP.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'profile_image' => $user->profile_image,
                    'university' => $user->university,
                    'course' => $user->course,
                    'semester' => $user->semester,
                    'is_verified' => $user->email_verified_at !== null,
                    'student_verified' => $user->student_verified ?? false,
                ],
                'token' => $token
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function sendVerificationOtp($user)
    {
        // Same implementation as in EmailVerificationController
        EmailVerificationCode::where('user_id', $user->id)
            ->where('used', false)
            ->update(['used' => true]);

        $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        EmailVerificationCode::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'code' => $otp,
            'expires_at' => now()->addMinutes(10),
            'ip_address' => request()->ip(),
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $user->notify(new EmailVerificationCodeNotification($otp, $verificationUrl));
    }

    /**
     * User login
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        // Update FCM token if provided
        if ($request->has('fcm_token')) {
            $user->update(['fcm_token' => $request->fcm_token]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'profile_image' => $user->profile_image,
                'university' => $user->university,
                'course' => $user->course,
                'semester' => $user->semester,
                'is_verified' => $user->email_verified_at !== null,
                'student_verified' => $user->student_verified ?? false,
            ],
            'token' => $token
        ]);
    }

    /**
     * User logout
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Google Sign-In
     */
    public function googleSignIn(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_token' => 'required|string',
            'email' => 'required|email',
            'name' => 'required|string',
            'photo_url' => 'nullable|string|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Verify Google ID token
            $client = new Google_Client(['client_id' => config('services.google.client_id')]);
            $payload = $client->verifyIdToken($request->id_token);

            if (!$payload) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Google token'
                ], 401);
            }

            $googleId = $payload['sub'];
            $email = $payload['email'];
            $name = $payload['name'];
            $profileImage = $request->photo_url ?? $payload['picture'] ?? null;

            // Find or create user
            $user = User::where('email', $email)->first();

            if (!$user) {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'google_id' => $googleId,
                    'profile_image' => $profileImage,
                    'email_verified_at' => now(),
                    'password' => Hash::make(Str::random(24)), // Random password for social login
                ]);
            } else {
                // Update Google ID and profile image if not set
                $user->update([
                    'google_id' => $googleId,
                    'profile_image' => $profileImage ?: $user->profile_image,
                    'email_verified_at' => $user->email_verified_at ?: now(),
                ]);
            }

            // Update FCM token if provided
            if ($request->has('fcm_token')) {
                $user->update(['fcm_token' => $request->fcm_token]);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Google sign-in successful',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'profile_image' => $user->profile_image,
                    'university' => $user->university,
                    'course' => $user->course,
                    'semester' => $user->semester,
                    'is_verified' => $user->email_verified_at !== null,
                    'student_verified' => $user->student_verified ?? false,
                ],
                'token' => $token
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Google sign-in failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Facebook Sign-In
     */
    public function fbSignIn(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'access_token' => 'required|string',
            'email' => 'required|email',
            'name' => 'required|string',
            'facebook_id' => 'required|string',
            'photo_url' => 'nullable|string|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Verify Facebook access token (you can implement Facebook Graph API verification)
            $email = $request->email;
            $name = $request->name;
            $facebookId = $request->facebook_id;
            $profileImage = $request->photo_url;

            // Find or create user
            $user = User::where('email', $email)->first();

            if (!$user) {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'facebook_id' => $facebookId,
                    'profile_image' => $profileImage,
                    'email_verified_at' => now(),
                    'password' => Hash::make(Str::random(24)),
                ]);
            } else {
                $user->update([
                    'facebook_id' => $facebookId,
                    'profile_image' => $profileImage ?: $user->profile_image,
                    'email_verified_at' => $user->email_verified_at ?: now(),
                ]);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Facebook sign-in successful',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'profile_image' => $user->profile_image,
                    'university' => $user->university,
                    'course' => $user->course,
                    'semester' => $user->semester,
                    'is_verified' => $user->email_verified_at !== null,
                    'student_verified' => $user->student_verified ?? false,
                ],
                'token' => $token
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Facebook sign-in failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Phone Sign-In (Send OTP)
     */
    public function phoneSignIn(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $phone = $request->phone;
            $otp = rand(100000, 999999); // Generate 6-digit OTP

            // Store OTP in database or cache
            DB::table('phone_otps')->updateOrInsert(
                ['phone' => $phone],
                [
                    'otp' => $otp,
                    'expires_at' => now()->addMinutes(10),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // Send OTP via SMS (implement your SMS service here)
            // $this->sendSMS($phone, "Your OTP is: $otp");

            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully',
                'otp' => config('app.debug') ? $otp : null, // Only show OTP in debug mode
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Phone Verify (Verify OTP)
     */
    public function phoneVerify(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'otp' => 'required|string|size:6',
            'name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $phone = $request->phone;
            $otp = $request->otp;

            // Verify OTP
            $otpRecord = DB::table('phone_otps')
                ->where('phone', $phone)
                ->where('otp', $otp)
                ->where('expires_at', '>', now())
                ->first();

            if (!$otpRecord) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired OTP'
                ], 401);
            }

            // Find or create user
            $user = User::where('phone', $phone)->first();

            if (!$user) {
                $user = User::create([
                    'name' => $request->name ?: 'User',
                    'phone' => $phone,
                    'phone_verified_at' => now(),
                    'password' => Hash::make(Str::random(24)),
                ]);
            } else {
                $user->update(['phone_verified_at' => now()]);
            }

            // Delete used OTP
            DB::table('phone_otps')->where('phone', $phone)->delete();

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Phone verified successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'profile_image' => $user->profile_image,
                    'university' => $user->university,
                    'course' => $user->course,
                    'semester' => $user->semester,
                    'is_verified' => $user->email_verified_at !== null,
                    'phone_verified' => $user->phone_verified_at !== null,
                    'student_verified' => $user->student_verified ?? false,
                ],
                'token' => $token
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Phone verification failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Forgot Password
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $status = Password::sendResetLink($request->only('email'));

            if ($status === Password::RESET_LINK_SENT) {
                return response()->json([
                    'success' => true,
                    'message' => 'Password reset link sent to your email'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send password reset link'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send password reset link',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update Password
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 401);
            }

            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update password',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify Email
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'verification_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();

            // Implement your email verification logic here
            // This is a simplified version
            if ($user->email_verified_at === null) {
                $user->markEmailAsVerified();
                event(new Verified($user));

                return response()->json([
                    'success' => true,
                    'message' => 'Email verified successfully'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Email already verified'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Email verification failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Phone Country Codes
     */
    public function phoneCountryCode(): JsonResponse
    {
        $countryCodes = [
            ['code' => '+1', 'country' => 'United States', 'flag' => '🇺🇸'],
            ['code' => '+1', 'country' => 'Canada', 'flag' => '🇨🇦'],
            ['code' => '+44', 'country' => 'United Kingdom', 'flag' => '🇬🇧'],
            ['code' => '+91', 'country' => 'India', 'flag' => '🇮🇳'],
            ['code' => '+86', 'country' => 'China', 'flag' => '🇨🇳'],
            ['code' => '+81', 'country' => 'Japan', 'flag' => '🇯🇵'],
            ['code' => '+49', 'country' => 'Germany', 'flag' => '🇩🇪'],
            ['code' => '+33', 'country' => 'France', 'flag' => '🇫🇷'],
            ['code' => '+39', 'country' => 'Italy', 'flag' => '🇮🇹'],
            ['code' => '+34', 'country' => 'Spain', 'flag' => '🇪🇸'],
            ['code' => '+7', 'country' => 'Russia', 'flag' => '🇷🇺'],
            ['code' => '+55', 'country' => 'Brazil', 'flag' => '🇧🇷'],
            ['code' => '+61', 'country' => 'Australia', 'flag' => '🇦🇺'],
            // Add more countries as needed
        ];

        return response()->json([
            'success' => true,
            'data' => $countryCodes
        ]);
    }

    /**
     * Update FCM Token
     */
    public function updateUserFcmToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();
            $user->update(['fcm_token' => $request->fcm_token]);

            return response()->json([
                'success' => true,
                'message' => 'FCM token updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update FCM token',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
