<?php

namespace App\Http\Controllers;

use App\Models\DeletionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class DeletionRequestController extends Controller
{
    public function index()
    {
        return view('frontend.legal_support.deletion-request');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'reason' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check if request already exists for this email
        $existingRequest = DeletionRequest::where('email', $request->email)
            ->where('status', '!=', 'completed')
            ->first();

        if ($existingRequest) {
            return back()->with('error', 'A deletion request for this email already exists and is being processed.');
        }

        $deletionRequest = DeletionRequest::create([
            'email' => $request->email,
            'name' => $request->name,
            'phone' => $request->phone,
            'reason' => $request->reason,
        ]);

        // Send verification email
        $this->sendVerificationEmail($deletionRequest);

        return back()->with('success', 'Your data deletion request has been submitted. Please check your email to verify the request.');
    }

    public function verify($token)
    {
        $deletionRequest = DeletionRequest::where('verification_token', $token)->first();

        if (!$deletionRequest) {
            return view('deletion-verification', ['status' => 'invalid']);
        }

        if ($deletionRequest->verified) {
            return view('deletion-verification', ['status' => 'already_verified']);
        }

        $deletionRequest->markAsVerified();

        // Send notification to admin
        $this->notifyAdminOfVerifiedRequest($deletionRequest);

        return view('deletion-verification', ['status' => 'verified']);
    }

    private function sendVerificationEmail($deletionRequest)
    {
        // Simple email sending - you can enhance this with proper mail templates
        $verificationUrl = url('/deletion-request/verify/' . $deletionRequest->verification_token);
        
        // You would typically use Mail::send() here with proper email templates
        // For now, this is a placeholder for the email functionality
        
        // Mail::send('emails.deletion-verification', compact('deletionRequest', 'verificationUrl'), function ($message) use ($deletionRequest) {
        //     $message->to($deletionRequest->email)
        //             ->subject('Verify Your Data Deletion Request - SWAP');
        // });
    }

    private function notifyAdminOfVerifiedRequest($deletionRequest)
    {
        // Notify admin about verified deletion request
        // You can implement admin notification logic here
    }

    public function status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $deletionRequest = DeletionRequest::where('email', $request->email)->latest()->first();

        if (!$deletionRequest) {
            return back()->with('error', 'No deletion request found for this email address.');
        }

        return view('deletion-status', compact('deletionRequest'));
    }
}
