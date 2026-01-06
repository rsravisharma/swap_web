@extends('frontend.layouts.app')

@section('title', 'Terms and Conditions - Swap')
@section('meta_description', 'Read the terms and conditions for using Swap, the student marketplace for buying and selling second-hand books and items.')

@section('content')

<!-- Hero Section -->
<section class="bg-gradient-to-br from-primary-600 to-secondary-600 text-white py-20">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center">
            <div class="w-20 h-20 bg-white/10 backdrop-blur rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Terms and Conditions</h1>
            <p class="text-xl opacity-90">SWAP - Student Marketplace Platform</p>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            
            <!-- Last Updated -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-r-lg mb-12">
                <p class="text-gray-700">
                    <strong class="text-gray-900">Last Updated:</strong> January 6, 2026
                </p>
                <p class="text-sm text-gray-600 mt-2">
                    Please read these terms and conditions carefully before using the Swap platform.
                </p>
            </div>

            <!-- 1. Acceptance of Terms -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4 pb-3 border-b-2 border-primary-500">
                    1. Acceptance of Terms
                </h2>
                <p class="text-gray-700 leading-relaxed mb-4">
                    By accessing and using Swap (the "Platform"), you accept and agree to be bound by these Terms and Conditions. If you do not agree to these terms, please do not use our Platform.
                </p>
                <p class="text-gray-700 leading-relaxed">
                    These terms apply to all users of the Platform, including but not limited to buyers, sellers, browsers, and any other contributors of content.
                </p>
            </section>

            <!-- 2. About Swap -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4 pb-3 border-b-2 border-primary-500">
                    2. About Swap
                </h2>
                <p class="text-gray-700 leading-relaxed mb-4">
                    Swap is a peer-to-peer marketplace platform designed primarily for students to buy and sell second-hand books, textbooks, and other educational items. The Platform facilitates connections between buyers and sellers but does not directly participate in transactions.
                </p>
                <div class="bg-gray-50 p-6 rounded-xl">
                    <h3 class="font-semibold text-gray-900 mb-3">We Provide:</h3>
                    <ul class="space-y-2 text-gray-700">
                        <li class="flex items-start">
                            <span class="text-primary-600 mr-2">•</span>
                            <span>A platform to list and browse items for sale</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-primary-600 mr-2">•</span>
                            <span>Communication tools between buyers and sellers</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-primary-600 mr-2">•</span>
                            <span>Optional payment processing services</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-primary-600 mr-2">•</span>
                            <span>User verification and rating systems</span>
                        </li>
                    </ul>
                </div>
            </section>

            <!-- 3. Eligibility -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4 pb-3 border-b-2 border-primary-500">
                    3. Eligibility
                </h2>
                <p class="text-gray-700 leading-relaxed mb-4">
                    To use Swap, you must:
                </p>
                <ul class="space-y-3 text-gray-700">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                        </svg>
                        <span>Be at least 13 years of age (or the minimum age required in your jurisdiction)</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                        </svg>
                        <span>Provide accurate and complete registration information</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                        </svg>
                        <span>Have the legal capacity to enter into binding contracts</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                        </svg>
                        <span>Not be prohibited from using the Platform under applicable laws</span>
                    </li>
                </ul>
            </section>

            <!-- 4. User Accounts -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4 pb-3 border-b-2 border-primary-500">
                    4. User Accounts
                </h2>
                
                <h3 class="text-xl font-semibold text-gray-900 mb-3">4.1 Account Registration</h3>
                <p class="text-gray-700 leading-relaxed mb-4">
                    You must create an account to access certain features of the Platform. You are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account.
                </p>
                
                <h3 class="text-xl font-semibold text-gray-900 mb-3">4.2 Account Security</h3>
                <p class="text-gray-700 leading-relaxed mb-4">
                    You agree to immediately notify us of any unauthorized use of your account. We are not liable for any loss or damage arising from your failure to protect your account information.
                </p>
                
                <h3 class="text-xl font-semibold text-gray-900 mb-3">4.3 Student Verification</h3>
                <p class="text-gray-700 leading-relaxed">
                    To access certain features, you may need to verify your student status. Providing false information for verification purposes is strictly prohibited and may result in account termination.
                </p>
            </section>

            <!-- 5. Listings and Transactions -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4 pb-3 border-b-2 border-primary-500">
                    5. Listings and Transactions
                </h2>
                
                <h3 class="text-xl font-semibold text-gray-900 mb-3">5.1 For Sellers</h3>
                <div class="bg-blue-50 p-6 rounded-xl mb-6">
                    <p class="text-gray-700 mb-3">As a seller, you agree to:</p>
                    <ul class="space-y-2 text-gray-700">
                        <li class="flex items-start">
                            <span class="text-primary-600 mr-2">•</span>
                            <span>Provide accurate and truthful descriptions of items</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-primary-600 mr-2">•</span>
                            <span>Use clear, recent photos of actual items being sold</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-primary-600 mr-2">•</span>
                            <span>Disclose any defects, damage, or issues with items</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-primary-600 mr-2">•</span>
                            <span>Honor the price and terms stated in your listing</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-primary-600 mr-2">•</span>
                            <span>Respond to buyer inquiries in a timely manner</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-primary-600 mr-2">•</span>
                            <span>Only sell items you legally own and have the right to sell</span>
                        </li>
                    </ul>
                </div>
                
                <h3 class="text-xl font-semibold text-gray-900 mb-3">5.2 For Buyers</h3>
                <div class="bg-green-50 p-6 rounded-xl mb-6">
                    <p class="text-gray-700 mb-3">As a buyer, you agree to:</p>
                    <ul class="space-y-2 text-gray-700">
                        <li class="flex items-start">
                            <span class="text-primary-600 mr-2">•</span>
                            <span>Make payment as agreed upon with the seller</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-primary-600 mr-2">•</span>
                            <span>Inspect items upon receipt and report issues promptly</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-primary-600 mr-2">•</span>
                            <span>Communicate respectfully with sellers</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-primary-600 mr-2">•</span>
                            <span>Complete transactions in a timely manner</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-primary-600 mr-2">•</span>
                            <span>Leave honest and fair reviews</span>
                        </li>
                    </ul>
                </div>
                
                <h3 class="text-xl font-semibold text-gray-900 mb-3">5.3 Transaction Completion</h3>
                <p class="text-gray-700 leading-relaxed">
                    Swap acts as an intermediary platform. The contract of sale is directly between the buyer and seller. We are not a party to any transaction and are not responsible for ensuring the completion of transactions or the quality of items sold.
                </p>
            </section>

            <!-- 6. Prohibited Items and Activities -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4 pb-3 border-b-2 border-primary-500">
                    6. Prohibited Items and Activities
                </h2>
                
                <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-r-xl">
                    <h3 class="text-xl font-semibold text-red-900 mb-4">You may NOT:</h3>
                    <ul class="space-y-3 text-gray-700">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-red-600 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z"/>
                            </svg>
                            <span>List counterfeit, pirated, or stolen items</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-red-600 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z"/>
                            </svg>
                            <span>Sell illegal items, weapons, drugs, or hazardous materials</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-red-600 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z"/>
                            </svg>
                            <span>Engage in fraudulent activities or scams</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-red-600 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z"/>
                            </svg>
                            <span>Harass, threaten, or abuse other users</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-red-600 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z"/>
                            </svg>
                            <span>Post inappropriate, offensive, or explicit content</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-red-600 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z"/>
                            </svg>
                            <span>Manipulate reviews or ratings</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-red-600 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z"/>
                            </svg>
                            <span>Use the Platform for commercial reselling or business purposes without authorization</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-red-600 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z"/>
                            </svg>
                            <span>Attempt to gain unauthorized access to the Platform or other users' accounts</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-red-600 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z"/>
                            </svg>
                            <span>Interfere with or disrupt the Platform's operation</span>
                        </li>
                    </ul>
                </div>
            </section>

            <!-- 7. Payments and Fees -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4 pb-3 border-b-2 border-primary-500">
                    7. Payments and Fees
                </h2>
                
                <h3 class="text-xl font-semibold text-gray-900 mb-3">7.1 Platform Fees</h3>
                <p class="text-gray-700 leading-relaxed mb-4">
                    Swap may charge service fees on successful transactions processed through our payment system. Current fees are displayed during the listing and checkout process. We reserve the right to modify fees with prior notice to users.
                </p>
                
                <h3 class="text-xl font-semibold text-gray-900 mb-3">7.2 Payment Processing</h3>
                <p class="text-gray-700 leading-relaxed mb-4">
                    Payments made through the Platform are processed by third-party payment processors (such as Razorpay). You agree to comply with their terms of service.
                </p>
                
                <h3 class="text-xl font-semibold text-gray-900 mb-3">7.3 Refunds and Disputes</h3>
                <p class="text-gray-700 leading-relaxed">
                    Refund policies are determined by sellers for individual transactions. Swap may assist in dispute resolution but is not obligated to provide refunds. For disputes involving Platform payments, please contact our support team within 7 days of the transaction.
                </p>
            </section>

            <!-- 8. Intellectual Property -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4 pb-3 border-b-2 border-primary-500">
                    8. Intellectual Property
                </h2>
                
                <h3 class="text-xl font-semibold text-gray-900 mb-3">8.1 Platform Content</h3>
                <p class="text-gray-700 leading-relaxed mb-4">
                    All content on the Platform, including but not limited to text, graphics, logos, images, software, and design, is the property of Swap or its licensors and is protected by intellectual property laws.
                </p>
                
                <h3 class="text-xl font-semibold text-gray-900 mb-3">8.2 User Content</h3>
                <p class="text-gray-700 leading-relaxed mb-4">
                    You retain ownership of content you post on the Platform. However, by posting content, you grant Swap a worldwide, non-exclusive, royalty-free license to use, display, reproduce, and distribute your content on the Platform.
                </p>
                
                <h3 class="text-xl font-semibold text-gray-900 mb-3">8.3 Copyright Infringement</h3>
                <p class="text-gray-700 leading-relaxed">
                    We respect intellectual property rights. If you believe content on the Platform infringes your copyright, please contact us with details of the alleged infringement.
                </p>
            </section>

            <!-- 9. Privacy and Data Protection -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4 pb-3 border-b-2 border-primary-500">
                    9. Privacy and Data Protection
                </h2>
                <p class="text-gray-700 leading-relaxed mb-4">
                    Your use of the Platform is also governed by our <a href="{{ route('privacy-policy') }}" class="text-primary-600 hover:text-primary-700 font-semibold">Privacy Policy</a>, which describes how we collect, use, and protect your personal information.
                </p>
                <p class="text-gray-700 leading-relaxed">
                    By using Swap, you consent to our collection and use of your data as described in the Privacy Policy.
                </p>
            </section>

            <!-- 10. Disclaimer of Warranties -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4 pb-3 border-b-2 border-primary-500">
                    10. Disclaimer of Warranties
                </h2>
                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-6 rounded-r-xl">
                    <p class="text-gray-700 leading-relaxed mb-4">
                        <strong>THE PLATFORM IS PROVIDED "AS IS" AND "AS AVAILABLE" WITHOUT WARRANTIES OF ANY KIND, EITHER EXPRESS OR IMPLIED.</strong>
                    </p>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        We do not warrant that:
                    </p>
                    <ul class="space-y-2 text-gray-700">
                        <li class="flex items-start">
                            <span class="text-yellow-600 mr-2">•</span>
                            <span>The Platform will be uninterrupted, timely, secure, or error-free</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-yellow-600 mr-2">•</span>
                            <span>Items listed will meet your requirements or expectations</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-yellow-600 mr-2">•</span>
                            <span>Users are who they claim to be</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-yellow-600 mr-2">•</span>
                            <span>Transactions will be completed successfully</span>
                        </li>
                    </ul>
                </div>
            </section>

            <!-- 11. Limitation of Liability -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4 pb-3 border-b-2 border-primary-500">
                    11. Limitation of Liability
                </h2>
                <p class="text-gray-700 leading-relaxed mb-4">
                    To the maximum extent permitted by law, Swap shall not be liable for any indirect, incidental, special, consequential, or punitive damages, or any loss of profits or revenues, whether incurred directly or indirectly, or any loss of data, use, goodwill, or other intangible losses resulting from:
                </p>
                <ul class="space-y-2 text-gray-700 pl-6">
                    <li class="flex items-start">
                        <span class="text-primary-600 mr-2">•</span>
                        <span>Your use or inability to use the Platform</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-primary-600 mr-2">•</span>
                        <span>Any conduct or content of any third party on the Platform</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-primary-600 mr-2">•</span>
                        <span>Unauthorized access to or use of our servers and/or any personal information stored therein</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-primary-600 mr-2">•</span>
                        <span>Any bugs, viruses, or malware transmitted to or through the Platform</span>
                    </li>
                </ul>
            </section>

            <!-- 12. Indemnification -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4 pb-3 border-b-2 border-primary-500">
                    12. Indemnification
                </h2>
                <p class="text-gray-700 leading-relaxed">
                    You agree to indemnify, defend, and hold harmless Swap, its officers, directors, employees, and agents from any claims, liabilities, damages, losses, and expenses, including legal fees, arising out of or in any way connected with your access to or use of the Platform, your violation of these Terms, or your violation of any rights of another party.
                </p>
            </section>

            <!-- 13. Account Termination -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4 pb-3 border-b-2 border-primary-500">
                    13. Account Termination
                </h2>
                
                <h3 class="text-xl font-semibold text-gray-900 mb-3">13.1 Termination by You</h3>
                <p class="text-gray-700 leading-relaxed mb-4">
                    You may delete your account at any time through the account settings or by submitting a <a href="{{ route('deletion.request') }}" class="text-primary-600 hover:text-primary-700 font-semibold">deletion request</a>.
                </p>
                
                <h3 class="text-xl font-semibold text-gray-900 mb-3">13.2 Termination by Swap</h3>
                <p class="text-gray-700 leading-relaxed">
                    We reserve the right to suspend or terminate your account if you violate these Terms, engage in fraudulent activity, or for any other reason at our sole discretion. Upon termination, your right to use the Platform will immediately cease.
                </p>
            </section>

            <!-- 14. Dispute Resolution -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4 pb-3 border-b-2 border-primary-500">
                    14. Dispute Resolution
                </h2>
                
                <h3 class="text-xl font-semibold text-gray-900 mb-3">14.1 Between Users</h3>
                <p class="text-gray-700 leading-relaxed mb-4">
                    Disputes between buyers and sellers should first be resolved directly between the parties. Swap may provide assistance but is not obligated to resolve disputes.
                </p>
                
                <h3 class="text-xl font-semibold text-gray-900 mb-3">14.2 Governing Law</h3>
                <p class="text-gray-700 leading-relaxed mb-4">
                    These Terms shall be governed by and construed in accordance with the laws of India, without regard to its conflict of law provisions.
                </p>
                
                <h3 class="text-xl font-semibold text-gray-900 mb-3">14.3 Jurisdiction</h3>
                <p class="text-gray-700 leading-relaxed">
                    Any disputes arising from these Terms or your use of the Platform shall be subject to the exclusive jurisdiction of the courts in Baddi, Himachal Pradesh, India.
                </p>
            </section>

            <!-- 15. Changes to Terms -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4 pb-3 border-b-2 border-primary-500">
                    15. Changes to Terms
                </h2>
                <p class="text-gray-700 leading-relaxed mb-4">
                    We reserve the right to modify these Terms at any time. We will notify users of material changes via email or through a notice on the Platform. Your continued use of the Platform after such modifications constitutes your acceptance of the updated Terms.
                </p>
                <p class="text-gray-700 leading-relaxed">
                    It is your responsibility to review these Terms periodically for changes.
                </p>
            </section>

            <!-- 16. General Provisions -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4 pb-3 border-b-2 border-primary-500">
                    16. General Provisions
                </h2>
                
                <h3 class="text-xl font-semibold text-gray-900 mb-3">16.1 Entire Agreement</h3>
                <p class="text-gray-700 leading-relaxed mb-4">
                    These Terms, along with our Privacy Policy, constitute the entire agreement between you and Swap regarding the use of the Platform.
                </p>
                
                <h3 class="text-xl font-semibold text-gray-900 mb-3">16.2 Severability</h3>
                <p class="text-gray-700 leading-relaxed mb-4">
                    If any provision of these Terms is found to be invalid or unenforceable, the remaining provisions shall remain in full force and effect.
                </p>
                
                <h3 class="text-xl font-semibold text-gray-900 mb-3">16.3 Waiver</h3>
                <p class="text-gray-700 leading-relaxed mb-4">
                    Our failure to enforce any right or provision of these Terms will not be considered a waiver of those rights.
                </p>
                
                <h3 class="text-xl font-semibold text-gray-900 mb-3">16.4 Assignment</h3>
                <p class="text-gray-700 leading-relaxed">
                    You may not assign or transfer these Terms without our prior written consent. We may assign our rights and obligations under these Terms without restriction.
                </p>
            </section>

            <!-- 17. Contact Information -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4 pb-3 border-b-2 border-primary-500">
                    17. Contact Information
                </h2>
                <div class="bg-gradient-to-br from-primary-50 to-secondary-50 p-8 rounded-xl">
                    <p class="text-gray-700 mb-4">
                        If you have any questions about these Terms and Conditions, please contact us:
                    </p>
                    <div class="space-y-3">
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
                        
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-primary-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <div>
                                <strong class="text-gray-900">Location:</strong>
                                <span class="text-gray-700 ml-2">Baddi, Himachal Pradesh, India</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Acknowledgment -->
            <div class="mt-16 p-8 bg-gray-100 rounded-2xl text-center">
                <p class="text-gray-700 text-lg">
                    By using Swap, you acknowledge that you have read, understood, and agree to be bound by these Terms and Conditions.
                </p>
            </div>

            <!-- Quick Links -->
            <div class="mt-12 pt-8 border-t border-gray-200">
                <div class="flex flex-wrap gap-4 justify-center">
                    <a href="{{ route('privacy-policy') }}" class="text-primary-600 hover:text-primary-700 font-medium">
                        Privacy Policy
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
