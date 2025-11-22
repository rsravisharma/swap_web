<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Models\EmailVerificationCode;
use App\Models\SubscriptionPlan;
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
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * User registration
     */

    protected function getDefaultSubscriptionPlanId()
    {
        return SubscriptionPlan::where('name', 'Basic')->value('id') ?? null;
    }

    public function generateAblyToken(Request $request)
    {
        $user = $request->user();

        $ablyAuth = new AblyAuth('YOUR_ABLY_API_KEY', 'YOUR_ABLY_SECRET');
        $tokenRequest = $ablyAuth->createTokenRequest([
            'clientId' => (string) $user->id,
            'capability' => [
                "user:{$user->id}:*" => ["publish", "subscribe"]
            ]
        ]);

        return response()->json($tokenRequest);
    }

    public function register(Request $request): JsonResponse
    {
        // Dynamic validation rules based on verification status
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                function ($attribute, $value, $fail) {
                    // Check if email exists and is verified
                    $existingUser = User::where('email', $value)
                        ->where(function ($query) {
                            $query->whereNotNull('email_verified_at')
                                ->orWhereNotNull('google_id');
                        })
                        ->first();

                    if ($existingUser) {
                        $fail('The email has already been taken by a verified user.');
                    }
                },
            ],
            'password' => 'required|string|min:8|confirmed',
            'phone' => [
                'nullable',
                'string',
                'max:20',
                function ($attribute, $value, $fail) use ($request) {
                    if (empty($value)) {
                        return; // Skip validation if phone is empty
                    }

                    // Check if phone exists and is verified (excluding current email if updating)
                    $existingUser = User::where('phone', $value)
                        ->where('email', '!=', $request->email) // Allow same user to update
                        ->where(function ($query) {
                            $query->whereNotNull('email_verified_at')
                                ->orWhereNotNull('google_id');
                        })
                        ->first();

                    if ($existingUser) {
                        $fail('The phone number has already been taken by a verified user.');
                    }
                },
            ],
            'university' => 'nullable|string|max:255',
            'course' => 'nullable|string|max:255',
            'semester' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if unverified user exists with same email OR phone
            $existingUser = User::where(function ($query) use ($request) {
                $query->where('email', $request->email);

                // Also check phone if provided
                if (!empty($request->phone)) {
                    $query->orWhere('phone', $request->phone);
                }
            })
                ->where('email_verified_at', null)
                ->where('google_id', null)
                ->first();

            if ($existingUser) {
                // Clean up related verification codes
                EmailVerificationCode::where('user_id', $existingUser->id)->delete();

                // Delete the unverified user record
                $existingUser->delete();

                Log::info("Deleted unverified user record for email: {$request->email}");
            }

            $referralCode = $request->input('ref');
            $referrer = null;

            if ($referralCode) {
                $referrer = User::where('referral_code', $referralCode)->first();
            }


            // Create new user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'university' => $request->university,
                'course' => $request->course,
                'semester' => $request->semester,
                'email_verified_at' => null,
                'coins' => 50, // initial coins for new user
                'referral_code' => User::generateUniqueReferralCode(),
                'referred_by' => $referrer ? $referrer->id : null,
                // Assign default or requested subscription plan ID
                'subscription_plan_id' => $request->input('subscription_plan_id') ?? $this->getDefaultSubscriptionPlanId(),
            ]);

            // Send OTP for verification
            $this->sendVerificationOtp($user);

            // Don't create token until email is verified (optional security improvement)
            // $token = $user->createToken('auth_token')->plainTextToken;

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
                    'is_verified' => false,
                    'email_verified_at' => null,
                    'student_verified' => false,
                ],
                // 'token' => $token, // Only provide token after verification
                'requires_verification' => true,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Registration failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
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

    public function verifyOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Check if user is already verified
            if ($user->email_verified_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email is already verified'
                ], 400);
            }

            $verificationCode = EmailVerificationCode::where('user_id', $user->id)
                ->where('code', $request->otp)
                ->where('used', false)
                ->where('expires_at', '>', now())
                ->first();

            if (!$verificationCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired OTP'
                ], 400);
            }

            // Mark OTP as used
            $verificationCode->update(['used' => true]);

            // Verify user email - this makes email and phone unique
            $user->update(['email_verified_at' => now()]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Email verified successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'referral_code' => $user->referral_code,
                    'email' => $user->email,
                    'phone' => $user->phone ?? '',
                    'coins' => $user->coins,
                    'profile_image' => $user->profile_image ?? '',
                    'university' => $user->university ?? '',
                    'course' => $user->course ?? '',
                    'semester' => $user->semester ?? '',
                    'is_verified' => true,
                    'email_verified_at' => $user->email_verified_at,
                    'subscription_plan' => $user->subscriptionPlan,
                ],
                'token' => $token
            ], 200);
        } catch (\Exception $e) {
            Log::error('OTP verification failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Verification failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function resendOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            if ($user->email_verified_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email is already verified'
                ], 400);
            }

            // Send new OTP
            $this->sendVerificationOtp($user);

            return response()->json([
                'success' => true,
                'message' => 'New OTP sent successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to resend OTP',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * User login
     */
    public function login(Request $request): JsonResponse
    {
        Log::info('=== Login Attempt Started ===');
        Log::info('Email: ' . $request->email);

        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
            'fcm_token' => 'nullable|string',
            'device_type' => 'nullable|string|in:android,ios',
        ]);

        if ($validator->fails()) {
            Log::warning('Login validation failed', [
                'errors' => $validator->errors()->toArray()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            Log::info('Step 1: Finding user');
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                Log::warning('User not found');
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            Log::info('Step 2: User found', ['user_id' => $user->id]);

            // Check if user is blocked
            if ($user->is_blocked) {
                Log::warning('User is blocked', ['user_id' => $user->id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been blocked. Please contact support.',
                    'reason' => $user->blocked_reason
                ], 403);
            }

            // Check if user is active
            if (!$user->is_active) {
                Log::warning('User is inactive', ['user_id' => $user->id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is inactive. Please contact support.'
                ], 403);
            }

            Log::info('Step 3: Attempting authentication');
            // Attempt authentication
            if (!Auth::attempt($request->only('email', 'password'))) {
                Log::warning('Authentication failed - wrong password');
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            Log::info('Step 4: Authentication successful');
            $user = Auth::user();

            // Update FCM token and device info if provided
            if ($request->has('fcm_token')) {
                Log::info('Step 5: Updating FCM token');
                try {
                    $user->updateFCMToken(
                        $request->fcm_token,
                        $request->input('device_type')
                    );
                    Log::info('FCM token updated');
                } catch (\Exception $e) {
                    Log::error('FCM token update failed', ['error' => $e->getMessage()]);
                }
            }

            Log::info('Step 6: Updating login streak');
            try {
                $streakData = $user->updateLoginStreak();
                Log::info('Login streak updated', $streakData);
            } catch (\Exception $e) {
                Log::error('Login streak update failed', ['error' => $e->getMessage()]);
                $streakData = ['streak' => 0, 'coins_awarded' => 0];
            }

            Log::info('Step 7: Updating last login timestamp');
            try {
                $user->update(['last_login_at' => now()]);
                Log::info('Last login updated');
            } catch (\Exception $e) {
                Log::error('Last login update failed', ['error' => $e->getMessage()]);
            }

            Log::info('Step 8: Creating token');
            try {
                $token = $user->createToken('auth_token')->plainTextToken;
                Log::info('Token created successfully');
            } catch (\Exception $e) {
                Log::error('Token creation failed', ['error' => $e->getMessage()]);
                throw $e;
            }

            Log::info('Step 9: Loading relationships');
            try {
                $user->load('subscriptionPlan');
                Log::info('Subscription plan loaded');
            } catch (\Exception $e) {
                Log::error('Loading subscription plan failed', ['error' => $e->getMessage()]);
                // Continue without subscription plan
            }

            Log::info('Step 10: Building response');
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user->id,
                    'referral_code' => $user->referral_code,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'coins' => $user->coins,
                    'profile_image' => $user->full_profile_image_url,
                    'university' => $user->university,
                    'course' => $user->course,
                    'semester' => $user->semester,
                    'bio' => $user->bio,
                    'is_verified' => $user->email_verified_at !== null,
                    'is_phone_verified' => $user->is_phone_verified,
                    'student_verified' => $user->student_verified ?? false,
                    'subscription_plan' => $user->subscriptionPlan,
                    'login_streak_days' => $user->login_streak_days,
                    'total_listings' => $user->total_listings,
                    'items_sold' => $user->items_sold,
                    'items_bought' => $user->items_bought,
                    'seller_rating' => $user->seller_rating,
                    'followers_count' => $user->followers_count,
                    'following_count' => $user->following_count,
                ],
                'streak' => $streakData,
                'token' => $token
            ], 200);
        } catch (\Exception $e) {
            Log::error('Login exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during login',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
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
        Log::debug('Google Sign-In initiated', [
            'request_data' => $request->only(['email', 'name', 'photo_url', 'fcm_token']),
            'has_id_token' => $request->has('id_token'),
            'id_token_length' => $request->id_token ? strlen($request->id_token) : 0
        ]);

        $validator = Validator::make($request->all(), [
            'id_token' => 'required|string',
            'email' => 'required|email',
            'name' => 'required|string',
            'photo_url' => 'nullable|string|url',
            'fcm_token' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            Log::warning('Google Sign-In validation failed', [
                'errors' => $validator->errors()->toArray(),
                'request_data' => $request->only(['email', 'name', 'photo_url'])
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        Log::debug('Validation passed, starting Google token verification');

        try {
            // Verify Google ID token
            $client = new Google_Client(['client_id' => config('services.google.client_id')]);

            Log::debug('Google Client initialized', [
                'client_id' => config('services.google.client_id') ? 'Present' : 'Missing'
            ]);

            $payload = $client->verifyIdToken($request->id_token);

            if (!$payload) {
                Log::error('Google token verification failed', [
                    'id_token_preview' => substr($request->id_token, 0, 50) . '...',
                    'email' => $request->email
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Google token'
                ], 401);
            }

            Log::debug('Google token verified successfully', [
                'google_id' => $payload['sub'] ?? 'N/A',
                'email' => $payload['email'] ?? 'N/A',
                'name' => $payload['name'] ?? 'N/A'
            ]);

            $googleId = $payload['sub'];
            $email = $payload['email'];
            $name = $payload['name'];
            $profileImage = $request->photo_url ?? $payload['picture'] ?? null;

            Log::debug('Extracted user data from token', [
                'google_id' => $googleId,
                'email' => $email,
                'name' => $name,
                'has_profile_image' => !empty($profileImage)
            ]);

            // Check for existing users with this email
            $existingUser = User::where('email', $email)->first();

            if ($existingUser) {
                Log::debug('Existing user found', [
                    'user_id' => $existingUser->id,
                    'email' => $existingUser->email,
                    'has_google_id' => !empty($existingUser->google_id),
                    'email_verified' => !empty($existingUser->email_verified_at),
                    'current_google_id' => $existingUser->google_id
                ]);

                // Case 1: User exists and is verified (email_verified_at OR google_id exists)
                if ($existingUser->email_verified_at !== null || $existingUser->google_id !== null) {
                    Log::debug('User is verified, proceeding with login', [
                        'user_id' => $existingUser->id,
                        'verification_method' => $existingUser->email_verified_at ? 'email' : 'google'
                    ]);

                    // Update Google ID and profile image if this is a different Google account
                    // or if Google ID is not set yet
                    $updateData = [];

                    if (!$existingUser->google_id) {
                        $updateData['google_id'] = $googleId;
                        Log::debug('Adding Google ID to existing user', ['google_id' => $googleId]);
                    }

                    if ($profileImage && !$existingUser->profile_image) {
                        $updateData['profile_image'] = $profileImage;
                        Log::debug('Adding profile image to existing user');
                    }

                    // Ensure email is verified for Google sign-ins
                    if (!$existingUser->email_verified_at) {
                        $updateData['email_verified_at'] = now();
                        Log::debug('Setting email as verified for Google sign-in');
                    }

                    // Update FCM token if provided
                    if ($request->has('fcm_token')) {
                        $updateData['fcm_token'] = $request->fcm_token;
                        Log::debug('Updating FCM token for existing user');
                    }

                    if (!empty($updateData)) {
                        Log::debug('Updating existing user data', ['updates' => array_keys($updateData)]);
                        $existingUser->update($updateData);
                    } else {
                        Log::debug('No updates needed for existing user');
                    }

                    $token = $existingUser->createToken('auth_token')->plainTextToken;

                    Log::info('Google sign-in successful for existing user', [
                        'user_id' => $existingUser->id,
                        'email' => $existingUser->email,
                        'token_created' => true
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Google sign-in successful',
                        'user' => [
                            'id' => $existingUser->id,
                            'referral_code' => $existingUser->referral_code,
                            'name' => $existingUser->name,
                            'email' => $existingUser->email,
                            'phone' => $existingUser->phone,
                            'coins' => $existingUser->coins,
                            'profile_image' => $existingUser->profile_image,
                            'university' => $existingUser->university,
                            'course' => $existingUser->course,
                            'semester' => $existingUser->semester,
                            'is_verified' => true,
                            'student_verified' => $existingUser->student_verified ?? true,
                        ],
                        'token' => $token
                    ]);
                }

                // Case 2: User exists but is NOT verified (can be replaced)
                if ($existingUser->email_verified_at === null && $existingUser->google_id === null) {
                    Log::warning('Found unverified user, deleting and replacing', [
                        'user_id' => $existingUser->id,
                        'email' => $existingUser->email
                    ]);

                    // Clean up related verification codes
                    $deletedCodes = EmailVerificationCode::where('user_id', $existingUser->id)->delete();
                    Log::debug('Deleted verification codes', ['count' => $deletedCodes]);

                    // Delete the unverified user record
                    $existingUser->delete();

                    Log::info("Deleted unverified user record for Google sign-in with email: {$email}");
                }
            } else {
                Log::debug('No existing user found, will create new user', ['email' => $email]);
            }

            // Case 3: No existing user OR unverified user was deleted - create new user
            Log::debug('Creating new user', [
                'name' => $name,
                'email' => $email,
                'google_id' => $googleId,
                'has_profile_image' => !empty($profileImage),
                'has_fcm_token' => !empty($request->fcm_token)
            ]);

            $referralCode = $request->input('ref');

            // Find the referrer user if referral code provided
            $referrer = null;
            if ($referralCode) {
                $referrer = User::where('referral_code', $referralCode)->first();
            }

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'google_id' => $googleId,
                'profile_image' => $profileImage,
                'email_verified_at' => now(), // Google emails are pre-verified
                'password' => Hash::make(Str::random(24)), // Random password for social login
                'fcm_token' => $request->fcm_token,
                'coins' => 50,
                'referral_code' => User::generateUniqueReferralCode(),
                'referred_by' => $referrer ? $referrer->id : null,
                'subscription_plan_id' => $this->getDefaultSubscriptionPlanId(),
            ]);

            Log::info('New user created successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'google_id' => $user->google_id
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            Log::info('Google sign-in completed successfully for new user', [
                'user_id' => $user->id,
                'token_created' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Google sign-in successful',
                'user' => [
                    'id' => $user->id,
                    'referral_code' => $user->referral_code,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'coins' => $user->coins,
                    'profile_image' => $user->profile_image,
                    'university' => $user->university,
                    'course' => $user->course,
                    'semester' => $user->semester,
                    'is_verified' => true,
                    'student_verified' => $user->student_verified ?? false,
                    'subscription_plan' => $user->subscriptionPlan,
                ],
                'token' => $token
            ]);
        } catch (\Exception $e) {
            Log::error('Google sign-in failed with exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'email' => $request->email ?? 'N/A',
                'request_data' => $request->only(['email', 'name', 'photo_url'])
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Google sign-in failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $email = $googleUser->getEmail();
        $name = $googleUser->getName();
        $avatar = $googleUser->getAvatar();

        // Create or update the user
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'profile_image' => $avatar,
            ]
        );

        // Generate a token
        $token = $user->createToken('google_token')->plainTextToken;

        // Redirect to frontend with token as query param
        return redirect()->away(env('APP_URL') . "/oauth-success?token=$token");
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

    public function checkAuth(Request $request): JsonResponse
    {
        $user = Auth::user();
        $userId = Auth::id();

        return response()->json([
            'authenticated' => Auth::check(),
            'user_id' => $userId,
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ] : null,
            'guard' => Auth::getDefaultDriver(),
            'token_valid' => $request->bearerToken() ? 'Token present' : 'No token'
        ]);
    }
}
