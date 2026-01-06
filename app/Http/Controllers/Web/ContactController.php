<?php

namespace App\Http\Controllers\Web;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use App\Http\Controllers\Controller;

class ContactController extends Controller
{
    /**
     * Display the contact form
     */
    public function index()
    {
        return view('frontend.contact.index');
    }

    /**
     * Handle the contact form submission
     */
    public function submit(Request $request)
    {
        // Rate limiting - 3 messages per hour per IP
        $key = 'contact-form:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            
            return back()->with('error', 
                'Too many contact requests. Please try again in ' . 
                ceil($seconds / 60) . ' minutes.'
            );
        }

        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|in:general,support,bug,feature,account,payment,safety,other',
            'message' => 'required|string|max:5000',
        ]);

        try {
            // Create the contact message
            $contactMessage = ContactMessage::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'subject' => $validated['subject'],
                'message' => $validated['message'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id' => Auth::id(), // Will be null if not logged in
                'status' => 'pending',
            ]);

            // Increment rate limiter
            RateLimiter::hit($key, 3600); // 1 hour

            // Send email notification to admin (optional)
            $this->sendAdminNotification($contactMessage);

            // Send confirmation email to user (optional)
            $this->sendUserConfirmation($contactMessage);

            return back()->with('success', 
                'Thank you for contacting us! We have received your message and will get back to you within 24 hours.'
            );

        } catch (\Exception $e) {
            \Log::error('Contact form submission error: ' . $e->getMessage());
            
            return back()->with('error', 
                'There was an error submitting your message. Please try again later or email us directly at swap.cubebitz@gmail.com'
            );
        }
    }

    /**
     * Send notification to admin
     */
    protected function sendAdminNotification($contactMessage)
    {
        try {
            // Uncomment when you set up mail configuration
            /*
            Mail::send('emails.contact-admin-notification', 
                ['message' => $contactMessage], 
                function($mail) use ($contactMessage) {
                    $mail->to('swap.cubebitz@gmail.com')
                         ->subject('New Contact Message: ' . $contactMessage->subject_label);
                }
            );
            */
        } catch (\Exception $e) {
            \Log::error('Failed to send admin notification: ' . $e->getMessage());
        }
    }

    /**
     * Send confirmation to user
     */
    protected function sendUserConfirmation($contactMessage)
    {
        try {
            // Uncomment when you set up mail configuration
            /*
            Mail::send('emails.contact-user-confirmation', 
                ['message' => $contactMessage], 
                function($mail) use ($contactMessage) {
                    $mail->to($contactMessage->email)
                         ->subject('We received your message - Swap Support');
                }
            );
            */
        } catch (\Exception $e) {
            \Log::error('Failed to send user confirmation: ' . $e->getMessage());
        }
    }
}
