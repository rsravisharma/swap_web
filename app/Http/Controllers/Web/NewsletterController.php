<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:newsletter_subscribers,email'
        ]);
        
        NewsletterSubscriber::create([
            'email' => $request->email,
            'subscribed' => true
        ]);
        
        return back()->with('success', 'Thank you for subscribing!');
    }
}
