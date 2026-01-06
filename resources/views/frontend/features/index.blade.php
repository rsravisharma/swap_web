@extends('frontend.layouts.app')

@section('title', 'Features - Swap Student Marketplace')
@section('meta_description', 'Discover all the features that make Swap the best marketplace for students: real-time chat, secure payments, location-based search, ratings, and more.')

@section('content')

<!-- Hero Section -->
<section class="bg-gradient-to-br from-primary-50 to-secondary-50 py-20">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">
            Everything You Need to Buy & Sell
        </h1>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Swap is packed with features designed to make trading textbooks and items safe, simple, and rewarding for students.
        </p>
    </div>
</section>

<!-- Main Features -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        
        <!-- Feature 1: Real-Time Chat -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center mb-20">
            <div>
                <div class="inline-block bg-primary-100 text-primary-600 px-4 py-2 rounded-full text-sm font-semibold mb-4">
                    Communication
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                    Real-Time Chat
                </h2>
                <p class="text-lg text-gray-700 mb-4">
                    Connect instantly with buyers and sellers through our built-in messaging system powered by Ably. No need for phone numbers or external apps.
                </p>
                <ul class="space-y-3">
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-gray-700">Instant message delivery and read receipts</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-gray-700">Share images of items and conditions</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-gray-700">Negotiate prices and arrange meetup locations</span>
                    </li>
                </ul>
            </div>
            <div>
                <img src="{{ asset('images/feature-chat.png') }}" alt="Real-Time Chat Feature" class="w-full rounded-2xl shadow-2xl">
            </div>
        </div>
        
        <!-- Feature 2: Offers System -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center mb-20">
            <div class="order-2 lg:order-1">
                <img src="{{ asset('images/feature-offers.png') }}" alt="Offer System Feature" class="w-full rounded-2xl shadow-2xl">
            </div>
            <div class="order-1 lg:order-2">
                <div class="inline-block bg-secondary-100 text-secondary-600 px-4 py-2 rounded-full text-sm font-semibold mb-4">
                    Negotiation
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                    Make & Receive Offers
                </h2>
                <p class="text-lg text-gray-700 mb-4">
                    Don't settle for asking prices. Use our offer system to negotiate and find deals that work for both buyers and sellers.
                </p>
                <ul class="space-y-3">
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-gray-700">Send counter-offers until you agree on a price</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-gray-700">Track all your offers in one place</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-gray-700">Get notifications for new offers and responses</span>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Feature 3: Secure Payments -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center mb-20">
            <div>
                <div class="inline-block bg-green-100 text-green-600 px-4 py-2 rounded-full text-sm font-semibold mb-4">
                    Security
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                    Secure Payment Processing
                </h2>
                <p class="text-lg text-gray-700 mb-4">
                    All transactions are processed through Razorpay, India's most trusted payment gateway. Your money is safe with us.
                </p>
                <ul class="space-y-3">
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-gray-700">UPI, cards, net banking, and wallets supported</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-gray-700">Buyer protection and refund policies</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-gray-700">Encrypted transactions and data protection</span>
                    </li>
                </ul>
            </div>
            <div>
                <img src="{{ asset('images/feature-payment.png') }}" alt="Secure Payment Feature" class="w-full rounded-2xl shadow-2xl">
            </div>
        </div>
        
        <!-- Feature 4: Location-Based -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center mb-20">
            <div class="order-2 lg:order-1">
                <img src="{{ asset('images/feature-location.png') }}" alt="Location-Based Feature" class="w-full rounded-2xl shadow-2xl">
            </div>
            <div class="order-1 lg:order-2">
                <div class="inline-block bg-yellow-100 text-yellow-600 px-4 py-2 rounded-full text-sm font-semibold mb-4">
                    Convenience
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                    Find Items Near Your Campus
                </h2>
                <p class="text-lg text-gray-700 mb-4">
                    Browse listings from students near your college or university. Meet in person for safe exchanges and avoid shipping costs.
                </p>
                <ul class="space-y-3">
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-gray-700">Filter by distance and location</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-gray-700">See items available on your campus</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-gray-700">Arrange meetups at convenient campus locations</span>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Feature 5: Ratings & Reviews -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center mb-20">
            <div>
                <div class="inline-block bg-red-100 text-red-600 px-4 py-2 rounded-full text-sm font-semibold mb-4">
                    Trust
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                    Ratings & Reviews System
                </h2>
                <p class="text-lg text-gray-700 mb-4">
                    Build your reputation as a trusted buyer or seller. Check ratings before making deals to ensure safe transactions.
                </p>
                <ul class="space-y-3">
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-gray-700">Rate buyers and sellers after transactions</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-gray-700">View detailed profiles with transaction history</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-gray-700">Report suspicious users for community safety</span>
                    </li>
                </ul>
            </div>
            <div>
                <img src="{{ asset('images/feature-ratings.png') }}" alt="Ratings Feature" class="w-full rounded-2xl shadow-2xl">
            </div>
        </div>
        
        <!-- Feature 6: Coins & Rewards -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="order-2 lg:order-1">
                <img src="{{ asset('images/feature-coins.png') }}" alt="Coins & Rewards Feature" class="w-full rounded-2xl shadow-2xl">
            </div>
            <div class="order-1 lg:order-2">
                <div class="inline-block bg-purple-100 text-purple-600 px-4 py-2 rounded-full text-sm font-semibold mb-4">
                    Rewards
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                    Earn Coins & Unlock Features
                </h2>
                <p class="text-lg text-gray-700 mb-4">
                    Get rewarded for being an active member of the Swap community. Earn coins with every transaction and unlock premium features.
                </p>
                <ul class="space-y-3">
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-gray-700">Earn coins for completing transactions</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-gray-700">Redeem coins for featured listings and boosts</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-gray-700">Access subscription plans with exclusive benefits</span>
                    </li>
                </ul>
            </div>
        </div>
        
    </div>
</section>

<!-- Additional Features Grid -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                And Much More...
            </h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                We're constantly adding new features based on student feedback
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-xl text-center shadow-sm">
                <div class="text-4xl mb-3">📷</div>
                <h3 class="font-semibold text-gray-900 mb-2">Image Upload</h3>
                <p class="text-sm text-gray-600">Multiple photos per listing with compression</p>
            </div>
            
            <div class="bg-white p-6 rounded-xl text-center shadow-sm">
                <div class="text-4xl mb-3">🔍</div>
                <h3 class="font-semibold text-gray-900 mb-2">Smart Search</h3>
                <p class="text-sm text-gray-600">Find exactly what you need with filters</p>
            </div>
            
            <div class="bg-white p-6 rounded-xl text-center shadow-sm">
                <div class="text-4xl mb-3">🔔</div>
                <h3 class="font-semibold text-gray-900 mb-2">Notifications</h3>
                <p class="text-sm text-gray-600">Stay updated on messages and offers</p>
            </div>
            
            <div class="bg-white p-6 rounded-xl text-center shadow-sm">
                <div class="text-4xl mb-3">📚</div>
                <h3 class="font-semibold text-gray-900 mb-2">ISBN Scanner</h3>
                <p class="text-sm text-gray-600">Scan book barcodes for instant details</p>
            </div>
            
            <div class="bg-white p-6 rounded-xl text-center shadow-sm">
                <div class="text-4xl mb-3">⭐</div>
                <h3 class="font-semibold text-gray-900 mb-2">Wishlist</h3>
                <p class="text-sm text-gray-600">Save items and get notified of price drops</p>
            </div>
            
            <div class="bg-white p-6 rounded-xl text-center shadow-sm">
                <div class="text-4xl mb-3">📱</div>
                <h3 class="font-semibold text-gray-900 mb-2">Mobile First</h3>
                <p class="text-sm text-gray-600">Optimized for iOS and Android</p>
            </div>
            
            <div class="bg-white p-6 rounded-xl text-center shadow-sm">
                <div class="text-4xl mb-3">🌙</div>
                <h3 class="font-semibold text-gray-900 mb-2">Dark Mode</h3>
                <p class="text-sm text-gray-600">Easy on the eyes, day or night</p>
            </div>
            
            <div class="bg-white p-6 rounded-xl text-center shadow-sm">
                <div class="text-4xl mb-3">🎯</div>
                <h3 class="font-semibold text-gray-900 mb-2">Categories</h3>
                <p class="text-sm text-gray-600">Books, electronics, furniture & more</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
@include('frontend.layouts.partials.cta')

@endsection
