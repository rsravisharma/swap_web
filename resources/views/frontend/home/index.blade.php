@extends('frontend.layouts.app')

@section('title', 'Swap - Buy & Sell Second-Hand Books & Items for Students')
@section('meta_description', 'Swap is the ultimate student marketplace for buying and selling second-hand textbooks, notes, and items. Save money, meet students on campus, and trade safely with real-time chat.')

@section('content')

<!-- Hero Section -->
<section class="bg-gradient-to-br from-primary-50 to-secondary-50 py-20">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 mb-6 leading-tight">
                    Save Money on 
                    <span class="text-primary-600">Books & Items</span>
                    for Students
                </h1>
                <p class="text-xl text-gray-600 mb-8">
                    Buy and sell second-hand textbooks, notes, and items with fellow students on your campus. Safe, simple, and smart!
                </p>
                
                <!-- Stats -->
                <div class="grid grid-cols-3 gap-6 mb-8">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-primary-600">{{ number_format($stats['total_users'] ?? 5000) }}+</div>
                        <div class="text-sm text-gray-600">Active Students</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-primary-600">{{ number_format($stats['total_items'] ?? 15000) }}+</div>
                        <div class="text-sm text-gray-600">Items Listed</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-primary-600">₹{{ number_format($stats['money_saved'] ?? 2500000) }}+</div>
                        <div class="text-sm text-gray-600">Money Saved</div>
                    </div>
                </div>
                
                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="https://play.google.com/store/apps/details?id=com.cubebitz.swap&hl=en_IN" class="bg-primary-600 hover:bg-primary-700 text-white px-8 py-4 rounded-lg font-semibold text-center transition shadow-lg hover:shadow-xl">
                        Download Now
                    </a>
                    <a href="{{ route('how-it-works') }}" class="bg-white hover:bg-gray-50 text-primary-600 px-8 py-4 rounded-lg font-semibold text-center border-2 border-primary-600 transition">
                        How It Works
                    </a>
                </div>
            </div>
            
            <!-- Hero Image -->
            <div class="relative">
    <!-- Mobile Phone Mockup -->
    <div class="relative mx-auto w-[300px] md:w-[350px]">
        <!-- Device Frame -->
        <div class="relative bg-black rounded-[3rem] p-3 shadow-2xl">
            <!-- Screen Container -->
            <div class="relative bg-white rounded-[2.5rem] overflow-hidden">
                <!-- Notch -->
                <div class="absolute top-0 left-1/2 transform -translate-x-1/2 w-40 h-6 bg-black rounded-b-3xl z-10 flex items-center justify-center gap-2">
                    <div class="w-12 h-1 bg-gray-900 rounded"></div>
                    <div class="w-1.5 h-1.5 bg-gray-800 rounded-full"></div>
                </div>
                
                <!-- Screenshot -->
                <img src="{{ asset('img/hero-mockup.jpg') }}" 
                     alt="Swap App Screenshot" 
                     class="w-full h-[600px] md:h-[700px] object-cover">
                
                <!-- Home Indicator -->
                <div class="absolute bottom-2 left-1/2 transform -translate-x-1/2 w-28 h-1 bg-gray-400 rounded-full"></div>
            </div>
            
            <!-- Buttons -->
            <div class="absolute -right-1 top-32 w-1 h-14 bg-gray-800 rounded-l"></div>
            <div class="absolute -left-1 top-24 w-1 h-8 bg-gray-800 rounded-r"></div>
            <div class="absolute -left-1 top-36 w-1 h-8 bg-gray-800 rounded-r"></div>
        </div>
    </div>
    
    <!-- Floating Elements -->
    <div class="absolute top-16 -left-4 md:left-0 bg-white p-4 rounded-xl shadow-lg animate-bounce z-30">
        <p class="text-sm font-semibold text-gray-900">💰 Save up to 70%</p>
    </div>
    <div class="absolute bottom-24 -right-4 md:right-0 bg-white p-4 rounded-xl shadow-lg animate-bounce z-30" style="animation-delay: 0.5s;">
        <p class="text-sm font-semibold text-gray-900">📚 10K+ Books</p>
    </div>
</div>

        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                Why Students Love Swap
            </h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Everything you need to buy, sell, and trade items safely with students on your campus.
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Feature 1 -->
            <div class="bg-gray-50 p-8 rounded-xl hover:shadow-lg transition">
                <div class="w-14 h-14 bg-primary-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Real-Time Chat</h3>
                <p class="text-gray-600">
                    Negotiate prices and arrange meetups instantly with built-in messaging powered by Ably.
                </p>
            </div>
            
            <!-- Feature 2 -->
            <div class="bg-gray-50 p-8 rounded-xl hover:shadow-lg transition">
                <div class="w-14 h-14 bg-secondary-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Make Offers</h3>
                <p class="text-gray-600">
                    Send and receive counter-offers until you find the perfect price for both parties.
                </p>
            </div>
            
            <!-- Feature 3 -->
            <div class="bg-gray-50 p-8 rounded-xl hover:shadow-lg transition">
                <div class="w-14 h-14 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Safe Payments</h3>
                <p class="text-gray-600">
                    Secure transactions through Razorpay with buyer protection and seller verification.
                </p>
            </div>
            
            <!-- Feature 4 -->
            <div class="bg-gray-50 p-8 rounded-xl hover:shadow-lg transition">
                <div class="w-14 h-14 bg-yellow-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Location-Based</h3>
                <p class="text-gray-600">
                    Find items near your campus or college. Meet sellers in person for safe exchanges.
                </p>
            </div>
            
            <!-- Feature 5 -->
            <div class="bg-gray-50 p-8 rounded-xl hover:shadow-lg transition">
                <div class="w-14 h-14 bg-red-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Ratings & Reviews</h3>
                <p class="text-gray-600">
                    Build trust with verified ratings and reviews from real students and buyers.
                </p>
            </div>
            
            <!-- Feature 6 -->
            <div class="bg-gray-50 p-8 rounded-xl hover:shadow-lg transition">
                <div class="w-14 h-14 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Earn Coins</h3>
                <p class="text-gray-600">
                    Get rewarded with coins for every successful transaction and unlock premium features.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                Start Swapping in 3 Easy Steps
            </h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Getting started is quick and easy. Join thousands of students already saving money!
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            <!-- Step 1 -->
            <div class="text-center">
                <div class="w-20 h-20 bg-primary-600 text-white rounded-full flex items-center justify-center text-3xl font-bold mx-auto mb-4">
                    1
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Download & Sign Up</h3>
                <p class="text-gray-600">
                    Get the Swap app from Play Store or App Store. Create your free account in seconds.
                </p>
            </div>
            
            <!-- Step 2 -->
            <div class="text-center">
                <div class="w-20 h-20 bg-primary-600 text-white rounded-full flex items-center justify-center text-3xl font-bold mx-auto mb-4">
                    2
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">List or Browse Items</h3>
                <p class="text-gray-600">
                    Post items you want to sell or browse thousands of listings from nearby students.
                </p>
            </div>
            
            <!-- Step 3 -->
            <div class="text-center">
                <div class="w-20 h-20 bg-primary-600 text-white rounded-full flex items-center justify-center text-3xl font-bold mx-auto mb-4">
                    3
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Chat & Complete Trade</h3>
                <p class="text-gray-600">
                    Negotiate with sellers, arrange meetups, and complete secure transactions.
                </p>
            </div>
        </div>
        
        <div class="text-center mt-12">
            <a href="{{ route('how-it-works') }}" class="text-primary-600 hover:text-primary-700 font-semibold text-lg">
                Learn More About How It Works →
            </a>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                What Students Are Saying
            </h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Hear from real students who are saving money and helping the environment with Swap.
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Testimonial 1 -->
            <div class="bg-gray-50 p-8 rounded-xl">
                <div class="flex items-center mb-4">
                    <div class="flex text-yellow-400">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-gray-700 mb-4">
                    "Saved over ₹15,000 on textbooks this semester! The chat feature made negotiating super easy. Best app for students!"
                </p>
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-primary-600 text-white rounded-full flex items-center justify-center font-bold mr-3">
                        P
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">Priya Sharma</p>
                        <p class="text-sm text-gray-600">Engineering Student, Delhi</p>
                    </div>
                </div>
            </div>
            
            <!-- Testimonial 2 -->
            <div class="bg-gray-50 p-8 rounded-xl">
                <div class="flex items-center mb-4">
                    <div class="flex text-yellow-400">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-gray-700 mb-4">
                    "Love how I can find students on my campus selling exactly what I need. Location-based search is a game changer!"
                </p>
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-secondary-600 text-white rounded-full flex items-center justify-center font-bold mr-3">
                        R
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">Rahul Kumar</p>
                        <p class="text-sm text-gray-600">MBA Student, Mumbai</p>
                    </div>
                </div>
            </div>
            
            <!-- Testimonial 3 -->
            <div class="bg-gray-50 p-8 rounded-xl">
                <div class="flex items-center mb-4">
                    <div class="flex text-yellow-400">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-gray-700 mb-4">
                    "Sold all my old semester books in a week! The platform is secure and the rating system gives me peace of mind."
                </p>
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center font-bold mr-3">
                        A
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">Ananya Patel</p>
                        <p class="text-sm text-gray-600">Medical Student, Bangalore</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
@include('frontend.layouts.partials.cta')

@endsection
