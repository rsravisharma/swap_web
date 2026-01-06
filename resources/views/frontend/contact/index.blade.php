@extends('frontend.layouts.app')

@section('title', 'Contact Us - Swap')
@section('meta_description', 'Get in touch with the Swap team. We are here to help with any questions or issues you may have.')

@section('content')

<!-- Hero Section -->
<section class="bg-gradient-to-br from-primary-50 to-secondary-50 py-20">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">
            Contact Us
        </h1>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Have questions? We're here to help. Reach out to us and we'll get back to you as soon as possible.
        </p>
    </div>
</section>

<!-- Contact Form & Info -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 max-w-6xl mx-auto">
            
            <!-- Contact Form -->
            <div class="bg-gray-50 p-8 rounded-2xl">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Send us a Message</h2>
                
                @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    {{ session('success') }}
                </div>
                @endif
                
                @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    {{ session('error') }}
                </div>
                @endif
                
                <form action="{{ route('contact.submit') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-200">
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-200">
                    </div>
                    
                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Subject *</label>
                        <select id="subject" 
                                name="subject" 
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-200">
                            <option value="">Select a subject</option>
                            <option value="general">General Inquiry</option>
                            <option value="support">Technical Support</option>
                            <option value="bug">Report a Bug</option>
                            <option value="feature">Feature Request</option>
                            <option value="account">Account Issues</option>
                            <option value="payment">Payment Issues</option>
                            <option value="safety">Safety Concerns</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Message *</label>
                        <textarea id="message" 
                                  name="message" 
                                  rows="6" 
                                  required
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-200"></textarea>
                    </div>
                    
                    <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white px-8 py-3 rounded-lg font-semibold transition">
                        Send Message
                    </button>
                </form>
            </div>
            
            <!-- Contact Information -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Get in Touch</h2>
                <p class="text-gray-600 mb-8">
                    We're always here to help. Whether you have a question about features, need technical support, or just want to say hi, feel free to reach out.
                </p>
                
                <div class="space-y-6">
                    <!-- Email -->
                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="font-semibold text-gray-900 mb-1">Email</h3>
                            <p class="text-gray-600">support@swapapp.com</p>
                            <p class="text-sm text-gray-500">We'll respond within 24 hours</p>
                        </div>
                    </div>
                    
                    <!-- Phone -->
                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="font-semibold text-gray-900 mb-1">Phone</h3>
                            <p class="text-gray-600">+91 1800-XXX-XXXX</p>
                            <p class="text-sm text-gray-500">Mon-Fri, 9AM-6PM IST</p>
                        </div>
                    </div>
                    
                    <!-- Social Media -->
                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="font-semibold text-gray-900 mb-1">Social Media</h3>
                            <div class="flex space-x-3 mt-2">
                                <a href="https://www.facebook.com/profile.php?id=61580492232091" target="_blank" class="text-gray-600 hover:text-primary-600">
                                    Facebook
                                </a>
                                <!-- <span class="text-gray-400">•</span>
                                <a href="https://twitter.com/swapapp" target="_blank" class="text-gray-600 hover:text-primary-600">
                                    Twitter
                                </a> -->
                                <span class="text-gray-400">•</span>
                                <a href="https://www.instagram.com/_swap_official_/" target="_blank" class="text-gray-600 hover:text-primary-600">
                                    Instagram
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- FAQ Link -->
                <div class="mt-12 p-6 bg-blue-50 rounded-xl">
                    <h3 class="font-semibold text-gray-900 mb-2">Looking for Quick Answers?</h3>
                    <p class="text-gray-600 mb-4">Check out our FAQ page for instant answers to common questions.</p>
                    <a href="{{ route('faq') }}" class="text-primary-600 hover:text-primary-700 font-semibold">
                        Visit FAQ →
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
