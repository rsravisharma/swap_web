@extends('frontend.layouts.app')

@section('title', 'About Us - Swap Student Marketplace')
@section('meta_description', 'Learn about Swap, the student marketplace helping thousands of students save money on textbooks and items. Our mission is to make education affordable.')

@section('content')

<!-- Hero Section -->
<section class="bg-gradient-to-br from-primary-50 to-secondary-50 py-20">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">
            About Swap
        </h1>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            We're on a mission to make education more affordable by connecting students who want to buy and sell second-hand books and items.
        </p>
    </div>
</section>

<!-- Mission Section -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                    Our Mission
                </h2>
                <p class="text-lg text-gray-700 mb-4">
                    Education shouldn't be expensive. We believe every student deserves access to affordable learning materials without breaking the bank.
                </p>
                <p class="text-lg text-gray-700 mb-4">
                    Swap was born out of a simple idea: students buy textbooks at the start of every semester and throw them away at the end. Why not create a platform where they can sell to each other?
                </p>
                <p class="text-lg text-gray-700">
                    Today, we're proud to serve thousands of students across India, helping them save lakhs of rupees while reducing waste and helping the environment.
                </p>
            </div>
            <div>
                <img src="{{ asset('images/mission-illustration.svg') }}" alt="Our Mission" class="w-full rounded-2xl shadow-xl">
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                Our Impact
            </h2>
            <p class="text-xl text-gray-600">
                Together, we're making a difference
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="bg-white p-8 rounded-xl text-center shadow-md">
                <div class="text-4xl font-bold text-primary-600 mb-2">5K+</div>
                <div class="text-gray-600">Active Students</div>
            </div>
            <div class="bg-white p-8 rounded-xl text-center shadow-md">
                <div class="text-4xl font-bold text-primary-600 mb-2">15K+</div>
                <div class="text-gray-600">Items Sold</div>
            </div>
            <div class="bg-white p-8 rounded-xl text-center shadow-md">
                <div class="text-4xl font-bold text-primary-600 mb-2">₹25L+</div>
                <div class="text-gray-600">Money Saved</div>
            </div>
            <div class="bg-white p-8 rounded-xl text-center shadow-md">
                <div class="text-4xl font-bold text-primary-600 mb-2">50+</div>
                <div class="text-gray-600">Colleges</div>
            </div>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                Our Values
            </h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                These principles guide everything we do
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Student-First</h3>
                <p class="text-gray-600">
                    Every decision we make prioritizes the needs and safety of students using our platform.
                </p>
            </div>
            
            <div class="text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Sustainability</h3>
                <p class="text-gray-600">
                    We're helping reduce waste by giving textbooks and items a second life through peer-to-peer trading.
                </p>
            </div>
            
            <div class="text-center">
                <div class="w-16 h-16 bg-secondary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Trust & Safety</h3>
                <p class="text-gray-600">
                    Verified ratings, secure payments, and community guidelines ensure safe transactions for everyone.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Team Section (Optional) -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                Built by Students, for Students
            </h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                We're a team of students and recent graduates who understand the financial challenges of education.
            </p>
        </div>
        
        <div class="bg-white p-8 md:p-12 rounded-2xl shadow-lg max-w-4xl mx-auto text-center">
            <p class="text-lg text-gray-700 mb-6">
                Started in 2024 by a group of college students frustrated with expensive textbooks, Swap has grown into a thriving community marketplace serving students across India.
            </p>
            <p class="text-lg text-gray-700">
                We're constantly improving the platform based on feedback from our community. Got ideas? We'd love to hear from you!
            </p>
            <div class="mt-8">
                <a href="/contact" class="bg-primary-600 hover:bg-primary-700 text-white px-8 py-3 rounded-lg font-semibold transition inline-block">
                    Get in Touch
                </a>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
@include('frontend.layouts.partials.cta')

@endsection
