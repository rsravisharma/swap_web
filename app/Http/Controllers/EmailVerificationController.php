<?php

namespace App\Http\Controllers;

use App\Models\EmailVerificationCode;
use App\Notifications\EmailVerificationCodeNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class EmailVerificationController extends Controller
{
    public function notice()
    {
        return view('auth.verify-email');
    }

    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill();

        return redirect('/dashboard')->with('verified', true);
    }

    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return back()->with('message', 'Email already verified.');
        }

        $this->sendVerificationOtp($request->user());

        return back()->with('message', 'Verification OTP sent!');
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'otp' => 'required|string|size:6'
        ]);

        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email already verified.'
            ], 400);
        }

        $otpRecord = EmailVerificationCode::where('user_id', $user->id)
            ->where('otp', $request->otp)
            ->where('used', false)
            ->first();

        if (!$otpRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP.'
            ], 400);
        }

        if ($otpRecord->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'OTP has expired.'
            ], 400);
        }

        // Mark OTP as used
        $otpRecord->update(['used' => true]);

        // Mark email as verified
        $user->markEmailAsVerified();

        event(new Verified($user));

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully!'
        ]);
    }

    private function sendVerificationOtp($user)
    {
        // Invalidate any existing unused OTPs
        EmailVerificationCode::where('user_id', $user->id)
            ->where('used', false)
            ->update(['used' => true]);

        // Generate new OTP
        $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP
        EmailVerificationCode::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'code' => $otp,
            'expires_at' => now()->addMinutes(10),
            'ip_address' => request()->ip(),
        ]);

        // Generate verification URL
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // Send notification
        $user->notify(new EmailVerificationCodeNotification($otp, $verificationUrl));
    }
}
