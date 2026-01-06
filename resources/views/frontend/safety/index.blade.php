@extends('frontend.layouts.app')

@section('title', 'Safety Tips - Stay Safe on Swap')
@section('meta_description', 'Learn how to stay safe while buying and selling on Swap. Tips for secure transactions, meeting safely, and protecting yourself from scams.')

@section('content')

<!-- Hero Section -->
<section class="bg-gradient-to-br from-primary-50 to-secondary-50 py-20">
    <div class="container mx-auto px-4 text-center">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </div>
        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">
            Safety Tips
        </h1>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Your safety is our priority. Follow these guidelines to have secure and positive experiences on Swap.
        </p>
    </div>
</section>

<!-- Main Safety Tips -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="max-w-5xl mx-auto">
            
            <!-- For Buyers -->
            <div class="mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">Safety Tips for Buyers</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="bg-blue-50 p-8 rounded-xl">
                        <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Check Seller Ratings</h3>
                        <p class="text-gray-700">
                            Always review the seller's rating and read feedback from previous buyers. Verified students and sellers with high ratings are generally more trustworthy.
                        </p>
                    </div>
                    
                    <div class="bg-blue-50 p-8 rounded-xl">
                        <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Inspect Before Buying</h3>
                        <p class="text-gray-700">
                            Always inspect items thoroughly before completing payment. Check for damage, missing pages, or any issues not mentioned in the listing.
                        </p>
                    </div>
                    
                    <div class="bg-blue-50 p-8 rounded-xl">
                        <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Meet in Public Places</h3>
                        <p class="text-gray-700">
                            Arrange meetups in well-lit, public campus locations like the library, cafeteria, or main gates. Avoid isolated areas or meeting late at night.
                        </p>
                    </div>
                    
                    <div class="bg-blue-50 p-8 rounded-xl">
                        <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Use Secure Payments</h3>
                        <p class="text-gray-700">
                            Prefer Swap's integrated payment system for transaction protection. If paying cash, count money carefully and get a receipt if possible.
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- For Sellers -->
            <div class="mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">Safety Tips for Sellers</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="bg-green-50 p-8 rounded-xl">
                        <div class="w-12 h-12 bg-green-600 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Be Honest in Listings</h3>
                        <p class="text-gray-700">
                            Provide accurate descriptions and clear photos. Disclose any damage, highlighting, or wear. Honesty builds trust and prevents disputes.
                        </p>
                    </div>
                    
                    <div class="bg-green-50 p-8 rounded-xl">
                        <div class="w-12 h-12 bg-green-600 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Verify Buyer Identity</h3>
                        <p class="text-gray-700">
                            Check the buyer's profile and ratings before meeting. Verified student accounts are indicated with a badge on their profile.
                        </p>
                    </div>
                    
                    <div class="bg-green-50 p-8 rounded-xl">
                        <div class="w-12 h-12 bg-green-600 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Choose Safe Meeting Times</h3>
                        <p class="text-gray-700">
                            Schedule meetups during daylight hours when campus is busy. Avoid very early morning or late evening meetings.
                        </p>
                    </div>
                    
                    <div class="bg-green-50 p-8 rounded-xl">
                        <div class="w-12 h-12 bg-green-600 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Protect Personal Info</h3>
                        <p class="text-gray-700">
                            Don't share unnecessary personal information like your room number, home address, or financial details outside the app's secure system.
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Red Flags -->
            <div class="mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">Warning Signs & Red Flags</h2>
                
                <div class="bg-red-50 border-2 border-red-200 rounded-xl p-8">
                    <div class="flex items-start mb-6">
                        <div class="w-12 h-12 bg-red-600 rounded-lg flex items-center justify-center flex-shrink-0 mr-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-semibold text-red-900 mb-4">Be Cautious If Someone:</h3>
                            <ul class="space-y-3 text-gray-700">
                                <li class="flex items-start">
                                    <span class="text-red-600 mr-2">•</span>
                                    <span>Pressures you to complete transaction quickly or urgently</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-red-600 mr-2">•</span>
                                    <span>Asks you to move communication off the Swap platform</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-red-600 mr-2">•</span>
                                    <span>Offers prices that seem too good to be true</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-red-600 mr-2">•</span>
                                    <span>Refuses to meet in person for local transactions</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-red-600 mr-2">•</span>
                                    <span>Has no ratings, reviews, or verification</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-red-600 mr-2">•</span>
                                    <span>Provides vague or inconsistent information</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-red-600 mr-2">•</span>
                                    <span>Asks for payment through unusual methods</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-red-600 mr-2">•</span>
                                    <span>Refuses to provide additional photos or information</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- General Best Practices -->
            <div class="mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">General Best Practices</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-purple-50 p-6 rounded-xl text-center">
                        <div class="text-4xl mb-3">💬</div>
                        <h3 class="font-semibold text-gray-900 mb-2">Use In-App Chat</h3>
                        <p class="text-gray-700 text-sm">Keep all communication within Swap for your protection and record-keeping.</p>
                    </div>
                    
                    <div class="bg-purple-50 p-6 rounded-xl text-center">
                        <div class="text-4xl mb-3">👥</div>
                        <h3 class="font-semibold text-gray-900 mb-2">Bring a Friend</h3>
                        <p class="text-gray-700 text-sm">Consider bringing a friend to meetups, especially for high-value items.</p>
                    </div>
                    
                    <div class="bg-purple-50 p-6 rounded-xl text-center">
                        <div class="text-4xl mb-3">🔍</div>
                        <h3 class="font-semibold text-gray-900 mb-2">Trust Your Instincts</h3>
                        <p class="text-gray-700 text-sm">If something feels wrong, don't proceed. Your safety comes first.</p>
                    </div>
                </div>
            </div>
            
            <!-- Report & Contact -->
            <div class="bg-gradient-to-br from-primary-600 to-secondary-600 rounded-2xl p-8 md:p-12 text-center text-white">
                <h2 class="text-2xl md:text-3xl font-bold mb-4">Need to Report Something?</h2>
                <p class="text-lg mb-8 opacity-90">
                    If you encounter suspicious activity, scams, or safety concerns, report it immediately. Our team reviews all reports and takes action to protect our community.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('contact') }}" class="bg-white hover:bg-gray-100 text-primary-600 px-8 py-3 rounded-lg font-semibold transition inline-block">
                        Report an Issue
                    </a>
                    <a href="{{ route('faq') }}" class="bg-white/10 hover:bg-white/20 backdrop-blur text-white px-8 py-3 rounded-lg font-semibold transition inline-block border border-white/30">
                        View FAQs
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
