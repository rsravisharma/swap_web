@extends('frontend.layouts.app')

@section('title', 'Request Account Deletion - Swap')
@section('meta_description', 'Request permanent deletion of your Swap account and associated data.')

@section('content')

<!-- Hero Section -->
<section class="bg-gradient-to-br from-red-600 to-red-700 text-white py-16">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center">
            <div class="w-20 h-20 bg-white/10 backdrop-blur rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6"/>
                </svg>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Account Deletion Request</h1>
            <p class="text-xl opacity-90">Request permanent deletion of your SWAP account and associated data</p>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            
            <!-- Success Message -->
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-r-lg mb-6 flex items-start">
                    <svg class="w-6 h-6 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                    </svg>
                    <div>
                        <p class="font-semibold">Success!</p>
                        <p>{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            <!-- Error Message -->
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-r-lg mb-6 flex items-start">
                    <svg class="w-6 h-6 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
                    </svg>
                    <div>
                        <p class="font-semibold">Error!</p>
                        <p>{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <!-- Warning Box -->
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-6 rounded-r-xl mb-6">
                <div class="flex items-start">
                    <svg class="w-8 h-8 text-yellow-600 mr-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/>
                    </svg>
                    <div>
                        <h3 class="text-xl font-bold text-yellow-800 mb-3">Important Warning</h3>
                        <p class="text-yellow-800 mb-3"><strong>Account deletion is permanent and cannot be undone.</strong> Once your deletion request is processed:</p>
                        <ul class="space-y-2 text-yellow-800">
                            <li class="flex items-start">
                                <span class="mr-2">•</span>
                                <span>Your account and profile will be permanently deleted</span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">•</span>
                                <span>All your product listings will be removed</span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">•</span>
                                <span>Your transaction history will be deleted</span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">•</span>
                                <span>You will lose access to all messages and communications</span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">•</span>
                                <span>This action cannot be reversed</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Info Box -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-r-xl mb-8">
                <div class="flex items-start">
                    <svg class="w-8 h-8 text-blue-600 mr-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"/>
                    </svg>
                    <div>
                        <h3 class="text-xl font-bold text-blue-800 mb-3">What Happens Next?</h3>
                        <ol class="space-y-2 text-blue-800 list-decimal list-inside">
                            <li><strong>Verification:</strong> You'll receive an email to verify this deletion request</li>
                            <li><strong>Review:</strong> Our team will review your request (typically within 3-5 business days)</li>
                            <li><strong>Processing:</strong> Once approved, your data will be permanently deleted within 30 days</li>
                            <li><strong>Confirmation:</strong> You'll receive a final confirmation email when deletion is complete</li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Form Section -->
            <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
                <div class="flex items-center mb-6">
                    <svg class="w-8 h-8 text-gray-700 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h2 class="text-3xl font-bold text-gray-900">Deletion Request Form</h2>
                </div>

                <form method="POST" action="{{ route('deletion.store') }}" class="space-y-6">
                    @csrf
                    
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Email Address <span class="text-red-600">*</span>
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-200 @error('email') border-red-500 @enderror">
                        <p class="text-sm text-gray-500 mt-1">Enter the email address associated with your SWAP account</p>
                        @error('email')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Full Name
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-200 @error('name') border-red-500 @enderror">
                        <p class="text-sm text-gray-500 mt-1">Your full name (optional, helps us verify your identity)</p>
                        @error('name')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                            <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            Phone Number
                        </label>
                        <input type="tel" 
                               id="phone" 
                               name="phone" 
                               value="{{ old('phone') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-200 @error('phone') border-red-500 @enderror">
                        <p class="text-sm text-gray-500 mt-1">Phone number associated with your account (optional)</p>
                        @error('phone')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Reason -->
                    <div>
                        <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                            <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                            </svg>
                            Reason for Deletion
                        </label>
                        <textarea id="reason" 
                                  name="reason" 
                                  rows="4"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-200 @error('reason') border-red-500 @enderror">{{ old('reason') }}</textarea>
                        <p class="text-sm text-gray-500 mt-1">Please let us know why you're deleting your account (optional, helps us improve)</p>
                        @error('reason')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirmation Checkbox -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <label class="flex items-start cursor-pointer">
                            <input type="checkbox" 
                                   id="confirmation" 
                                   required
                                   class="mt-1 mr-3 w-5 h-5 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                            <span class="text-gray-900">
                                <strong>I understand that this action is permanent and cannot be undone. I want to permanently delete my SWAP account and all associated data.</strong>
                            </span>
                        </label>
                    </div>

                    <!-- Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-end">
                        <a href="{{ route('home') }}" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50 transition text-center">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Cancel
                        </a>
                        <button type="submit" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Submit Deletion Request
                        </button>
                    </div>
                </form>
            </div>

            <!-- Check Status Section -->
            <div class="bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl p-8 text-center">
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Check Request Status</h3>
                <p class="text-gray-600 mb-6">Already submitted a request? Check its status here.</p>
                
                <form method="POST" action="{{ route('deletion.status.check') }}" class="flex flex-col sm:flex-row gap-3 justify-center max-w-md mx-auto">
                    @csrf
                    <input type="email" 
                           name="email" 
                           placeholder="Enter your email" 
                           required
                           class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-200">
                    <button type="submit" class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-semibold transition whitespace-nowrap">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Check Status
                    </button>
                </form>
            </div>

            <!-- Help Links -->
            <div class="mt-12 text-center">
                <p class="text-gray-600 mb-4">Need help or have questions?</p>
                <div class="flex flex-wrap gap-4 justify-center">
                    <a href="{{ route('privacy-policy') }}" class="text-primary-600 hover:text-primary-700 font-medium">
                        Privacy Policy
                    </a>
                    <span class="text-gray-400">•</span>
                    <a href="{{ route('termsAndConditions') }}" class="text-primary-600 hover:text-primary-700 font-medium">
                        Terms of Service
                    </a>
                    <span class="text-gray-400">•</span>
                    <a href="{{ route('contact') }}" class="text-primary-600 hover:text-primary-700 font-medium">
                        Contact Support
                    </a>
                    <span class="text-gray-400">•</span>
                    <a href="{{ route('faq') }}" class="text-primary-600 hover:text-primary-700 font-medium">
                        FAQ
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
