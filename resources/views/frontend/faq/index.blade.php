@extends('frontend.layouts.app')

@section('title', 'FAQ - Frequently Asked Questions | Swap')
@section('meta_description', 'Find answers to frequently asked questions about using Swap, buying and selling textbooks, payments, and more.')

@section('content')

<!-- Hero Section -->
<section class="bg-gradient-to-br from-primary-50 to-secondary-50 py-20">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">
            Frequently Asked Questions
        </h1>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Find quick answers to common questions about using Swap.
        </p>
    </div>
</section>

<!-- FAQ Categories -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            
            <!-- Search Box -->
            <div class="mb-12">
                <div class="relative">
                    <input type="text" 
                           placeholder="Search for answers..." 
                           class="w-full px-6 py-4 pl-12 border border-gray-300 rounded-lg focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-200">
                    <svg class="w-5 h-5 text-gray-400 absolute left-4 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
            
            <div x-data="{ openFaq: null }">
                
                <!-- Getting Started -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Getting Started</h2>
                    <div class="space-y-4">
                        
                        <div class="bg-gray-50 rounded-xl overflow-hidden">
                            <button @click="openFaq = openFaq === 1 ? null : 1" 
                                    class="w-full px-6 py-5 text-left flex items-center justify-between hover:bg-gray-100 transition">
                                <span class="font-semibold text-gray-900">How do I create an account on Swap?</span>
                                <svg class="w-5 h-5 text-gray-500 transform transition-transform" 
                                     :class="{ 'rotate-180': openFaq === 1 }"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="openFaq === 1" 
                                 x-transition
                                 class="px-6 pb-5"
                                 style="display: none;">
                                <p class="text-gray-600">
                                    Download the Swap app from Google Play Store or Apple App Store. Tap "Sign Up" and enter your email or phone number. Verify your account using the OTP sent to you, and complete your profile with your college details. That's it – you're ready to start buying and selling!
                                </p>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 rounded-xl overflow-hidden">
                            <button @click="openFaq = openFaq === 2 ? null : 2" 
                                    class="w-full px-6 py-5 text-left flex items-center justify-between hover:bg-gray-100 transition">
                                <span class="font-semibold text-gray-900">Is Swap free to use?</span>
                                <svg class="w-5 h-5 text-gray-500 transform transition-transform" 
                                     :class="{ 'rotate-180': openFaq === 2 }"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="openFaq === 2" 
                                 x-transition
                                 class="px-6 pb-5"
                                 style="display: none;">
                                <p class="text-gray-600">
                                    Yes! Downloading and using Swap is completely free. There are no monthly subscriptions or listing fees. We only charge a small transaction fee when you complete a sale through our secure payment system to cover payment processing costs.
                                </p>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 rounded-xl overflow-hidden">
                            <button @click="openFaq = openFaq === 3 ? null : 3" 
                                    class="w-full px-6 py-5 text-left flex items-center justify-between hover:bg-gray-100 transition">
                                <span class="font-semibold text-gray-900">Can I use Swap if I'm not a student?</span>
                                <svg class="w-5 h-5 text-gray-500 transform transition-transform" 
                                     :class="{ 'rotate-180': openFaq === 3 }"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="openFaq === 3" 
                                 x-transition
                                 class="px-6 pb-5"
                                 style="display: none;">
                                <p class="text-gray-600">
                                    Swap is designed specifically for students and requires student verification to access certain features. However, recent graduates and educators with valid credentials can also join the platform.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Buying -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Buying</h2>
                    <div class="space-y-4">
                        
                        <div class="bg-gray-50 rounded-xl overflow-hidden">
                            <button @click="openFaq = openFaq === 4 ? null : 4" 
                                    class="w-full px-6 py-5 text-left flex items-center justify-between hover:bg-gray-100 transition">
                                <span class="font-semibold text-gray-900">How do I search for textbooks?</span>
                                <svg class="w-5 h-5 text-gray-500 transform transition-transform" 
                                     :class="{ 'rotate-180': openFaq === 4 }"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="openFaq === 4" 
                                 x-transition
                                 class="px-6 pb-5"
                                 style="display: none;">
                                <p class="text-gray-600">
                                    Use the search bar at the top of the app to enter the book title, author, or ISBN. You can filter results by price, condition, location, and distance from your campus. Save searches to get notified when new listings match your criteria.
                                </p>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 rounded-xl overflow-hidden">
                            <button @click="openFaq = openFaq === 5 ? null : 5" 
                                    class="w-full px-6 py-5 text-left flex items-center justify-between hover:bg-gray-100 transition">
                                <span class="font-semibold text-gray-900">How do I know if a seller is trustworthy?</span>
                                <svg class="w-5 h-5 text-gray-500 transform transition-transform" 
                                     :class="{ 'rotate-180': openFaq === 5 }"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="openFaq === 5" 
                                 x-transition
                                 class="px-6 pb-5"
                                 style="display: none;">
                                <p class="text-gray-600">
                                    Check the seller's rating and read reviews from previous buyers. Verified student accounts have a badge. Always communicate through the app's chat system, meet in public campus locations, and inspect items before paying.
                                </p>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 rounded-xl overflow-hidden">
                            <button @click="openFaq = openFaq === 6 ? null : 6" 
                                    class="w-full px-6 py-5 text-left flex items-center justify-between hover:bg-gray-100 transition">
                                <span class="font-semibold text-gray-900">What if I receive a damaged item?</span>
                                <svg class="w-5 h-5 text-gray-500 transform transition-transform" 
                                     :class="{ 'rotate-180': openFaq === 6 }"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="openFaq === 6" 
                                 x-transition
                                 class="px-6 pb-5"
                                 style="display: none;">
                                <p class="text-gray-600">
                                    If an item doesn't match the description, contact our support team immediately with photos and details. For in-person transactions, always inspect items before completing payment. For online payments through the app, our buyer protection policy can help resolve disputes.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Selling -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Selling</h2>
                    <div class="space-y-4">
                        
                        <div class="bg-gray-50 rounded-xl overflow-hidden">
                            <button @click="openFaq = openFaq === 7 ? null : 7" 
                                    class="w-full px-6 py-5 text-left flex items-center justify-between hover:bg-gray-100 transition">
                                <span class="font-semibold text-gray-900">How do I list an item for sale?</span>
                                <svg class="w-5 h-5 text-gray-500 transform transition-transform" 
                                     :class="{ 'rotate-180': openFaq === 7 }"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="openFaq === 7" 
                                 x-transition
                                 class="px-6 pb-5"
                                 style="display: none;">
                                <p class="text-gray-600">
                                    Tap the "+" button in the app, select "Sell Item," take clear photos, add title, description, condition, and price. Choose the appropriate category and publish. Your listing will be visible to students in your area immediately.
                                </p>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 rounded-xl overflow-hidden">
                            <button @click="openFaq = openFaq === 8 ? null : 8" 
                                    class="w-full px-6 py-5 text-left flex items-center justify-between hover:bg-gray-100 transition">
                                <span class="font-semibold text-gray-900">How do I get paid as a seller?</span>
                                <svg class="w-5 h-5 text-gray-500 transform transition-transform" 
                                     :class="{ 'rotate-180': openFaq === 8 }"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="openFaq === 8" 
                                 x-transition
                                 class="px-6 pb-5"
                                 style="display: none;">
                                <p class="text-gray-600">
                                    For online transactions through Swap's payment system, funds are processed via Razorpay and transferred to your registered bank account or UPI after the buyer confirms receipt. For in-person meetups, you can accept cash directly. Payments typically process within 2-3 business days.
                                </p>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 rounded-xl overflow-hidden">
                            <button @click="openFaq = openFaq === 9 ? null : 9" 
                                    class="w-full px-6 py-5 text-left flex items-center justify-between hover:bg-gray-100 transition">
                                <span class="font-semibold text-gray-900">What fees does Swap charge?</span>
                                <svg class="w-5 h-5 text-gray-500 transform transition-transform" 
                                     :class="{ 'rotate-180': openFaq === 9 }"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="openFaq === 9" 
                                 x-transition
                                 class="px-6 pb-5"
                                 style="display: none;">
                                <p class="text-gray-600">
                                    There are no listing fees or monthly charges. Swap charges a small service fee (typically 5-10%) on successful sales made through the app's payment system to cover payment processing and platform maintenance. Cash transactions arranged through the app have no fees.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Payment & Safety -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Payment & Safety</h2>
                    <div class="space-y-4">
                        
                        <div class="bg-gray-50 rounded-xl overflow-hidden">
                            <button @click="openFaq = openFaq === 10 ? null : 10" 
                                    class="w-full px-6 py-5 text-left flex items-center justify-between hover:bg-gray-100 transition">
                                <span class="font-semibold text-gray-900">What payment methods are accepted?</span>
                                <svg class="w-5 h-5 text-gray-500 transform transition-transform" 
                                     :class="{ 'rotate-180': openFaq === 10 }"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="openFaq === 10" 
                                 x-transition
                                 class="px-6 pb-5"
                                 style="display: none;">
                                <p class="text-gray-600">
                                    Swap supports UPI, credit/debit cards, net banking, and digital wallets through our secure Razorpay integration. For local in-person transactions, cash payment is also an option.
                                </p>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 rounded-xl overflow-hidden">
                            <button @click="openFaq = openFaq === 11 ? null : 11" 
                                    class="w-full px-6 py-5 text-left flex items-center justify-between hover:bg-gray-100 transition">
                                <span class="font-semibold text-gray-900">Is my payment information secure?</span>
                                <svg class="w-5 h-5 text-gray-500 transform transition-transform" 
                                     :class="{ 'rotate-180': openFaq === 11 }"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="openFaq === 11" 
                                 x-transition
                                 class="px-6 pb-5"
                                 style="display: none;">
                                <p class="text-gray-600">
                                    Yes! All payment transactions are processed through Razorpay, a PCI-DSS compliant payment gateway. We never store your complete card details or banking information. All data is encrypted using industry-standard security protocols.
                                </p>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 rounded-xl overflow-hidden">
                            <button @click="openFaq = openFaq === 12 ? null : 12" 
                                    class="w-full px-6 py-5 text-left flex items-center justify-between hover:bg-gray-100 transition">
                                <span class="font-semibold text-gray-900">How do I report a suspicious user or listing?</span>
                                <svg class="w-5 h-5 text-gray-500 transform transition-transform" 
                                     :class="{ 'rotate-180': openFaq === 12 }"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="openFaq === 12" 
                                 x-transition
                                 class="px-6 pb-5"
                                 style="display: none;">
                                <p class="text-gray-600">
                                    Tap the three dots (...) on any listing or user profile and select "Report." Choose the reason for reporting and provide any additional details. Our team reviews all reports within 24 hours and takes appropriate action to keep the community safe.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Account & Technical -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Account & Technical</h2>
                    <div class="space-y-4">
                        
                        <div class="bg-gray-50 rounded-xl overflow-hidden">
                            <button @click="openFaq = openFaq === 13 ? null : 13" 
                                    class="w-full px-6 py-5 text-left flex items-center justify-between hover:bg-gray-100 transition">
                                <span class="font-semibold text-gray-900">How do I delete my account?</span>
                                <svg class="w-5 h-5 text-gray-500 transform transition-transform" 
                                     :class="{ 'rotate-180': openFaq === 13 }"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="openFaq === 13" 
                                 x-transition
                                 class="px-6 pb-5"
                                 style="display: none;">
                                <p class="text-gray-600">
                                    Visit our <a href="{{ route('deletion.request') }}" class="text-primary-600 hover:underline">Data Deletion Request</a> page. Submit your request with your email, and we'll send you a verification link. Once verified, your account and data will be permanently deleted within 30 days according to our privacy policy.
                                </p>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 rounded-xl overflow-hidden">
                            <button @click="openFaq = openFaq === 14 ? null : 14" 
                                    class="w-full px-6 py-5 text-left flex items-center justify-between hover:bg-gray-100 transition">
                                <span class="font-semibold text-gray-900">The app isn't working properly. What should I do?</span>
                                <svg class="w-5 h-5 text-gray-500 transform transition-transform" 
                                     :class="{ 'rotate-180': openFaq === 14 }"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="openFaq === 14" 
                                 x-transition
                                 class="px-6 pb-5"
                                 style="display: none;">
                                <p class="text-gray-600">
                                    First, try closing and reopening the app. If issues persist, check for app updates in your app store. Clear the app cache from your phone's settings. If problems continue, contact our support team with details about the issue, your device model, and app version.
                                </p>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 rounded-xl overflow-hidden">
                            <button @click="openFaq = openFaq === 15 ? null : 15" 
                                    class="w-full px-6 py-5 text-left flex items-center justify-between hover:bg-gray-100 transition">
                                <span class="font-semibold text-gray-900">Can I use Swap on multiple devices?</span>
                                <svg class="w-5 h-5 text-gray-500 transform transition-transform" 
                                     :class="{ 'rotate-180': openFaq === 15 }"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="openFaq === 15" 
                                 x-transition
                                 class="px-6 pb-5"
                                 style="display: none;">
                                <p class="text-gray-600">
                                    Yes! You can log into your Swap account on multiple devices. Your listings, messages, and account information will sync across all devices. However, for security reasons, you'll need to verify new devices using OTP.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
            
            <!-- Still Need Help -->
            <div class="mt-16 text-center p-8 bg-gradient-to-br from-primary-50 to-secondary-50 rounded-2xl">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Still Have Questions?</h2>
                <p class="text-gray-600 mb-6">
                    Can't find what you're looking for? Our support team is here to help.
                </p>
                <a href="{{ route('contact') }}" class="inline-block bg-primary-600 hover:bg-primary-700 text-white px-8 py-3 rounded-lg font-semibold transition">
                    Contact Support
                </a>
            </div>
        </div>
    </div>
</section>

@endsection
