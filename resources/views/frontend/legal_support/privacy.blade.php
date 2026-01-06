@extends('frontend.layouts.app')

@section('title', 'Privacy Policy - Swap')
@section('meta_description', 'Learn how Swap collects, uses, and protects your personal data. Your privacy is our priority.')

@section('content')

<!-- Hero Section -->
<section class="bg-gradient-to-br from-primary-600 to-secondary-600 text-white py-20">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center">
            <div class="w-20 h-20 bg-white/10 backdrop-blur rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Privacy Policy</h1>
            <p class="text-xl opacity-90">SWAP - Second-hand Product Marketplace</p>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            
            <!-- Last Updated -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-r-lg mb-12">
                <p class="text-gray-700">
                    <strong class="text-gray-900">Last Updated:</strong> October 2, 2025
                </p>
            </div>

            <!-- Introduction -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4 pb-3 border-b-2 border-primary-500">
                    1. Introduction
                </h2>
                <p class="text-gray-700 leading-relaxed">
                    Welcome to SWAP, a second-hand product marketplace primarily designed for students to buy and sell books and other items. We respect your privacy and are committed to protecting your personal data. This privacy policy explains how we collect, use, and safeguard your information when you use our mobile application and services.
                </p>
            </section>

            <!-- Information We Collect -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-6 pb-3 border-b-2 border-primary-500">
                    2. Information We Collect
                </h2>
                
                <div class="space-y-6">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">2.1 Personal Information</h3>
                        <ul class="space-y-2">
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                </svg>
                                <span class="text-gray-700">Name and email address</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                </svg>
                                <span class="text-gray-700">Phone number</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                </svg>
                                <span class="text-gray-700">Profile information and photos</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                </svg>
                                <span class="text-gray-700">Location data (with your consent)</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                </svg>
                                <span class="text-gray-700">Educational institution details (for students)</span>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">2.2 Product Information</h3>
                        <ul class="space-y-2">
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                </svg>
                                <span class="text-gray-700">Item listings and descriptions</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                </svg>
                                <span class="text-gray-700">Product photos and videos</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                </svg>
                                <span class="text-gray-700">Transaction history</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                </svg>
                                <span class="text-gray-700">Communication between buyers and sellers</span>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">2.3 Technical Information</h3>
                        <ul class="space-y-2">
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                </svg>
                                <span class="text-gray-700">Device information and identifiers</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                </svg>
                                <span class="text-gray-700">App usage data and analytics</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                </svg>
                                <span class="text-gray-700">IP address and network information</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                </svg>
                                <span class="text-gray-700">Crash reports and error logs</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- How We Use Your Information -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4 pb-3 border-b-2 border-primary-500">
                    3. How We Use Your Information
                </h2>
                <p class="text-gray-700 mb-4">We use your information to:</p>
                <ul class="space-y-2">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-primary-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                        <span class="text-gray-700">Provide and maintain our marketplace services</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-primary-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                        <span class="text-gray-700">Process transactions and facilitate communication between users</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-primary-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                        <span class="text-gray-700">Verify user identity and prevent fraud</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-primary-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                        <span class="text-gray-700">Send notifications about your transactions and account</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-primary-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                        <span class="text-gray-700">Improve our app functionality and user experience</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-primary-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                        <span class="text-gray-700">Provide customer support</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-primary-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                        <span class="text-gray-700">Comply with legal obligations</span>
                    </li>
                </ul>
            </section>

            <!-- Information Sharing -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4 pb-3 border-b-2 border-primary-500">
                    4. Information Sharing
                </h2>
                <p class="text-gray-700 mb-4">We may share your information in the following circumstances:</p>
                
                <div class="space-y-4">
                    <div class="flex items-start p-4 bg-blue-50 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                        </svg>
                        <div>
                            <strong class="text-gray-900">With Other Users:</strong>
                            <span class="text-gray-700"> Your profile information and listings are visible to other app users</span>
                        </div>
                    </div>
                    
                    <div class="flex items-start p-4 bg-yellow-50 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                        </svg>
                        <div>
                            <strong class="text-gray-900">Legal Requirements:</strong>
                            <span class="text-gray-700"> When required by law or to protect our rights</span>
                        </div>
                    </div>
                    
                    <div class="flex items-start p-4 bg-gray-50 rounded-lg">
                        <svg class="w-6 h-6 text-gray-600 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z"/>
                        </svg>
                        <div>
                            <strong class="text-gray-900">Service Providers:</strong>
                            <span class="text-gray-700"> With trusted third-party services that help us operate the app</span>
                        </div>
                    </div>
                    
                    <div class="flex items-start p-4 bg-green-50 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z"/>
                        </svg>
                        <div>
                            <strong class="text-gray-900">Business Transfers:</strong>
                            <span class="text-gray-700"> In case of merger, acquisition, or sale of assets</span>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg">
                    <p class="text-gray-900 font-semibold">
                        We do not sell your personal information to third parties for marketing purposes.
                    </p>
                </div>
            </section>

            <!-- Data Security -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4 pb-3 border-b-2 border-primary-500">
                    5. Data Security
                </h2>
                <p class="text-gray-700 mb-4">We implement appropriate security measures to protect your personal information:</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-start p-4 bg-green-50 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"/>
                        </svg>
                        <span class="text-gray-700">Encryption of data in transit and at rest</span>
                    </div>
                    
                    <div class="flex items-start p-4 bg-yellow-50 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v2H2v-4l4.257-4.257A6 6 0 1118 8zm-6-4a1 1 0 100 2 2 2 0 012 2 1 1 0 102 0 4 4 0 00-4-4z"/>
                        </svg>
                        <span class="text-gray-700">Secure authentication protocols</span>
                    </div>
                    
                    <div class="flex items-start p-4 bg-blue-50 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm3.293 1.293a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 01-1.414-1.414L7.586 10 5.293 7.707a1 1 0 010-1.414zM11 12a1 1 0 100 2h3a1 1 0 100-2h-3z"/>
                        </svg>
                        <span class="text-gray-700">Regular security audits and updates</span>
                    </div>
                    
                    <div class="flex items-start p-4 bg-purple-50 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                        </svg>
                        <span class="text-gray-700">Access controls and user permissions</span>
                    </div>
                </div>
            </section>

            <!-- Your Rights -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4 pb-3 border-b-2 border-primary-500">
                    6. Your Rights
                </h2>
                <p class="text-gray-700 mb-4">You have the right to:</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                        <svg class="w-6 h-6 text-primary-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <span class="text-gray-700">Access your personal data</span>
                    </div>
                    
                    <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span class="text-gray-700">Update or correct your information</span>
                    </div>
                    
                    <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                        <svg class="w-6 h-6 text-red-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span class="text-gray-700">Delete your account and data</span>
                    </div>
                    
                    <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        <span class="text-gray-700">Export your data</span>
                    </div>
                    
                    <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                        <span class="text-gray-700">Opt-out of communications</span>
                    </div>
                    
                    <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-gray-700">Contact us about privacy concerns</span>
                    </div>
                </div>
            </section>

            <!-- Cookies and Analytics -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4 pb-3 border-b-2 border-primary-500">
                    7. Cookies and Analytics
                </h2>
                <p class="text-gray-700 mb-4">We use cookies and similar technologies to:</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-start p-3 bg-gray-50 rounded-lg">
                        <svg class="w-5 h-5 text-green-600 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                        </svg>
                        <span class="text-gray-700">Analyze app usage and performance</span>
                    </div>
                    
                    <div class="flex items-start p-3 bg-gray-50 rounded-lg">
                        <svg class="w-5 h-5 text-primary-600 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z"/>
                        </svg>
                        <span class="text-gray-700">Remember your preferences</span>
                    </div>
                    
                    <div class="flex items-start p-3 bg-gray-50 rounded-lg">
                        <svg class="w-5 h-5 text-blue-600 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                        </svg>
                        <span class="text-gray-700">Enhance security</span>
                    </div>
                    
                    <div class="flex items-start p-3 bg-gray-50 rounded-lg">
                        <svg class="w-5 h-5 text-yellow-600 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"/>
                        </svg>
                        <span class="text-gray-700">Improve app functionality</span>
                    </div>
                </div>
            </section>

            <!-- Children's Privacy -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4 pb-3 border-b-2 border-primary-500">
                    8. Children's Privacy
                </h2>
                <p class="text-gray-700 leading-relaxed">
                    While our app is designed for students, we do not knowingly collect personal information from children under 13. If you believe we have collected information from a child under 13, please contact us immediately.
                </p>
            </section>

            <!-- Data Retention -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4 pb-3 border-b-2 border-primary-500">
                    9. Data Retention
                </h2>
                <p class="text-gray-700 leading-relaxed">
                    We retain your information for as long as necessary to provide our services and comply with legal obligations. When you delete your account, we will remove your personal information within 30 days, except for data we must retain for legal or safety reasons.
                </p>
            </section>

            <!-- Changes to This Policy -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4 pb-3 border-b-2 border-primary-500">
                    10. Changes to This Policy
                </h2>
                <p class="text-gray-700 leading-relaxed">
                    We may update this privacy policy from time to time. We will notify you of any material changes through the app or by email. Your continued use of SWAP after such changes constitutes your acceptance of the updated policy.
                </p>
            </section>

            <!-- Contact Us -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4 pb-3 border-b-2 border-primary-500">
                    11. Contact Us
                </h2>
                <div class="bg-gradient-to-br from-primary-50 to-secondary-50 p-8 rounded-xl">
                    <p class="text-gray-700 mb-4">
                        If you have questions about this privacy policy or our data practices, please contact us:
                    </p>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-primary-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <div>
                            <strong class="text-gray-900">Email:</strong>
                            <a href="mailto:swap.cubebitz@gmail.com" class="text-primary-600 hover:text-primary-700 ml-2">
                                swap.cubebitz@gmail.com
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Quick Links -->
            <div class="mt-16 pt-8 border-t border-gray-200">
                <div class="flex flex-wrap gap-4 justify-center">
                    <a href="{{ route('termsAndConditions') }}" class="text-primary-600 hover:text-primary-700 font-medium">
                        Terms of Service
                    </a>
                    <span class="text-gray-400">•</span>
                    <a href="{{ route('deletion.request') }}" class="text-primary-600 hover:text-primary-700 font-medium">
                        Delete My Data
                    </a>
                    <span class="text-gray-400">•</span>
                    <a href="{{ route('contact') }}" class="text-primary-600 hover:text-primary-700 font-medium">
                        Contact Support
                    </a>
                    <span class="text-gray-400">•</span>
                    <a href="{{ route('safety') }}" class="text-primary-600 hover:text-primary-700 font-medium">
                        Safety Tips
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
