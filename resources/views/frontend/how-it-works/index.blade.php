@extends('frontend.layouts.app')

@section('title', 'How It Works - Swap Student Marketplace')
@section('meta_description', 'Learn how to buy and sell textbooks and items on Swap in 3 easy steps. Download, list or browse, and complete safe transactions with fellow students.')

@section('content')

<!-- Hero Section -->
<section class="bg-gradient-to-br from-primary-50 to-secondary-50 py-20">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">
            How Swap Works
        </h1>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Getting started is simple. Follow these easy steps to start buying and selling with students on your campus.
        </p>
    </div>
</section>

<!-- Main Steps Section -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        
        <!-- Step 1 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center mb-24">
            <div class="order-2 lg:order-1">
                <img src="{{ asset('images/how-it-works-1.png') }}" alt="Download Swap App" class="w-full max-w-md mx-auto rounded-2xl shadow-2xl">
            </div>
            <div class="order-1 lg:order-2">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-primary-600 text-white rounded-full text-2xl font-bold mb-6">
                    1
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                    Download & Create Your Account
                </h2>
                <p class="text-lg text-gray-700 mb-6">
                    Get the Swap app from Google Play Store or Apple App Store. Sign up using your email or phone number in just a few seconds.
                </p>
                <ul class="space-y-4">
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <div>
                            <span class="font-semibold text-gray-900">Free to download</span>
                            <p class="text-gray-600">No subscription or hidden fees</p>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <div>
                            <span class="font-semibold text-gray-900">Quick registration</span>
                            <p class="text-gray-600">Sign up with email, phone, or Google account</p>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <div>
                            <span class="font-semibold text-gray-900">Verify your profile</span>
                            <p class="text-gray-600">Add your college details for campus-specific listings</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Step 2 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center mb-24">
            <div>
                <div class="inline-flex items-center justify-center w-16 h-16 bg-primary-600 text-white rounded-full text-2xl font-bold mb-6">
                    2
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                    List Your Items or Browse Listings
                </h2>
                <p class="text-lg text-gray-700 mb-6">
                    Selling? Post your textbooks, notes, or items with photos and descriptions. Buying? Browse thousands of listings from students near you.
                </p>
                
                <!-- Tabs for Sellers and Buyers -->
                <div x-data="{ activeTab: 'seller' }" class="mt-8">
                    <div class="flex gap-4 mb-6">
                        <button @click="activeTab = 'seller'" 
                                :class="activeTab === 'seller' ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-700'"
                                class="px-6 py-2 rounded-lg font-semibold transition">
                            For Sellers
                        </button>
                        <button @click="activeTab = 'buyer'" 
                                :class="activeTab === 'buyer' ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-700'"
                                class="px-6 py-2 rounded-lg font-semibold transition">
                            For Buyers
                        </button>
                    </div>
                    
                    <!-- Seller Content -->
                    <div x-show="activeTab === 'seller'" class="space-y-4">
                        <div class="flex items-start">
                            <span class="bg-primary-100 text-primary-600 rounded-full w-8 h-8 flex items-center justify-center font-bold mr-3 flex-shrink-0">1</span>
                            <p class="text-gray-700">Take clear photos of your item from multiple angles</p>
                        </div>
                        <div class="flex items-start">
                            <span class="bg-primary-100 text-primary-600 rounded-full w-8 h-8 flex items-center justify-center font-bold mr-3 flex-shrink-0">2</span>
                            <p class="text-gray-700">Add title, description, condition, and asking price</p>
                        </div>
                        <div class="flex items-start">
                            <span class="bg-primary-100 text-primary-600 rounded-full w-8 h-8 flex items-center justify-center font-bold mr-3 flex-shrink-0">3</span>
                            <p class="text-gray-700">Select category and set your location for local buyers</p>
                        </div>
                        <div class="flex items-start">
                            <span class="bg-primary-100 text-primary-600 rounded-full w-8 h-8 flex items-center justify-center font-bold mr-3 flex-shrink-0">4</span>
                            <p class="text-gray-700">Publish and wait for interested buyers to message you</p>
                        </div>
                    </div>
                    
                    <!-- Buyer Content -->
                    <div x-show="activeTab === 'buyer'" class="space-y-4" style="display: none;">
                        <div class="flex items-start">
                            <span class="bg-secondary-100 text-secondary-600 rounded-full w-8 h-8 flex items-center justify-center font-bold mr-3 flex-shrink-0">1</span>
                            <p class="text-gray-700">Search by keywords or browse categories like Books, Electronics, etc.</p>
                        </div>
                        <div class="flex items-start">
                            <span class="bg-secondary-100 text-secondary-600 rounded-full w-8 h-8 flex items-center justify-center font-bold mr-3 flex-shrink-0">2</span>
                            <p class="text-gray-700">Filter by price, condition, location, and distance</p>
                        </div>
                        <div class="flex items-start">
                            <span class="bg-secondary-100 text-secondary-600 rounded-full w-8 h-8 flex items-center justify-center font-bold mr-3 flex-shrink-0">3</span>
                            <p class="text-gray-700">Check seller ratings and reviews before contacting</p>
                        </div>
                        <div class="flex items-start">
                            <span class="bg-secondary-100 text-secondary-600 rounded-full w-8 h-8 flex items-center justify-center font-bold mr-3 flex-shrink-0">4</span>
                            <p class="text-gray-700">Save items to your wishlist for later</p>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <img src="{{ asset('images/how-it-works-2.png') }}" alt="List or Browse Items" class="w-full max-w-md mx-auto rounded-2xl shadow-2xl">
            </div>
        </div>
        
        <!-- Step 3 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="order-2 lg:order-1">
                <img src="{{ asset('images/how-it-works-3.png') }}" alt="Chat and Complete Transaction" class="w-full max-w-md mx-auto rounded-2xl shadow-2xl">
            </div>
            <div class="order-1 lg:order-2">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-primary-600 text-white rounded-full text-2xl font-bold mb-6">
                    3
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                    Chat, Negotiate & Complete Transaction
                </h2>
                <p class="text-lg text-gray-700 mb-6">
                    Use our real-time chat to discuss details, negotiate prices, and arrange meetups. Complete the transaction safely with our secure payment system.
                </p>
                <ul class="space-y-4">
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <div>
                            <span class="font-semibold text-gray-900">Real-time messaging</span>
                            <p class="text-gray-600">Chat instantly with buyers/sellers through the app</p>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <div>
                            <span class="font-semibold text-gray-900">Make offers</span>
                            <p class="text-gray-600">Send and receive counter-offers until you agree on a price</p>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <div>
                            <span class="font-semibold text-gray-900">Secure payments</span>
                            <p class="text-gray-600">Pay through Razorpay with UPI, cards, or wallets</p>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <div>
                            <span class="font-semibold text-gray-900">Meet safely</span>
                            <p class="text-gray-600">Arrange meetups in public campus locations</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        
    </div>
</section>

<!-- Payment Methods -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                Multiple Payment Options
            </h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Choose the payment method that works best for you
            </p>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 max-w-4xl mx-auto">
            <div class="bg-white p-6 rounded-xl text-center shadow-sm">
                <div class="text-4xl mb-3">💳</div>
                <h3 class="font-semibold text-gray-900">Credit/Debit Cards</h3>
            </div>
            <div class="bg-white p-6 rounded-xl text-center shadow-sm">
                <div class="text-4xl mb-3">📱</div>
                <h3 class="font-semibold text-gray-900">UPI</h3>
            </div>
            <div class="bg-white p-6 rounded-xl text-center shadow-sm">
                <div class="text-4xl mb-3">🏦</div>
                <h3 class="font-semibold text-gray-900">Net Banking</h3>
            </div>
            <div class="bg-white p-6 rounded-xl text-center shadow-sm">
                <div class="text-4xl mb-3">👛</div>
                <h3 class="font-semibold text-gray-900">Wallets</h3>
            </div>
        </div>
    </div>
</section>

<!-- Safety Tips -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                Stay Safe While Trading
            </h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Follow these simple tips for safe and successful transactions
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
            <div class="bg-gray-50 p-8 rounded-xl">
                <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Check Ratings</h3>
                <p class="text-gray-600">
                    Always check the seller's ratings and reviews before making a purchase.
                </p>
            </div>
            
            <div class="bg-gray-50 p-8 rounded-xl">
                <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Meet in Public</h3>
                <p class="text-gray-600">
                    Arrange meetups in public campus locations like library or cafeteria.
                </p>
            </div>
            
            <div class="bg-gray-50 p-8 rounded-xl">
                <div class="w-14 h-14 bg-yellow-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Inspect Items</h3>
                <p class="text-gray-600">
                    Always inspect the item carefully before completing the payment.
                </p>
            </div>
            
            <div class="bg-gray-50 p-8 rounded-xl">
                <div class="w-14 h-14 bg-purple-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Use In-App Chat</h3>
                <p class="text-gray-600">
                    Keep all communication within the app for your safety and security.
                </p>
            </div>
            
            <div class="bg-gray-50 p-8 rounded-xl">
                <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Report Suspicious Activity</h3>
                <p class="text-gray-600">
                    Report any suspicious users or listings immediately to our support team.
                </p>
            </div>
            
            <div class="bg-gray-50 p-8 rounded-xl">
                <div class="w-14 h-14 bg-indigo-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Trust Your Instincts</h3>
                <p class="text-gray-600">
                    If something feels off, trust your gut and don't proceed with the transaction.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                Frequently Asked Questions
            </h2>
        </div>
        
        <div class="max-w-3xl mx-auto space-y-4" x-data="{ openFaq: null }">
            <!-- FAQ 1 -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <button @click="openFaq = openFaq === 1 ? null : 1" 
                        class="w-full px-6 py-5 text-left flex items-center justify-between hover:bg-gray-50 transition">
                    <span class="font-semibold text-gray-900">Is Swap free to use?</span>
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
                        Yes! Downloading and using Swap is completely free. We don't charge any listing fees or monthly subscriptions. We only charge a small transaction fee when you complete a sale through our secure payment system.
                    </p>
                </div>
            </div>
            
            <!-- FAQ 2 -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <button @click="openFaq = openFaq === 2 ? null : 2" 
                        class="w-full px-6 py-5 text-left flex items-center justify-between hover:bg-gray-50 transition">
                    <span class="font-semibold text-gray-900">How do I get paid as a seller?</span>
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
                        Payments are processed through Razorpay. Once the buyer confirms receipt of the item, the payment is released to your registered bank account or UPI. You can also choose cash payments for in-person meetups.
                    </p>
                </div>
            </div>
            
            <!-- FAQ 3 -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <button @click="openFaq = openFaq === 3 ? null : 3" 
                        class="w-full px-6 py-5 text-left flex items-center justify-between hover:bg-gray-50 transition">
                    <span class="font-semibold text-gray-900">What if I receive a damaged item?</span>
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
                        If you receive an item that doesn't match the description, contact our support team immediately. We have buyer protection policies in place and will help resolve the issue. Always inspect items before completing in-person transactions.
                    </p>
                </div>
            </div>
            
            <!-- FAQ 4 -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <button @click="openFaq = openFaq === 4 ? null : 4" 
                        class="w-full px-6 py-5 text-left flex items-center justify-between hover:bg-gray-50 transition">
                    <span class="font-semibold text-gray-900">Can I use Swap if I'm not a student?</span>
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
                        Swap is designed specifically for students, and you'll need to verify your student status to use certain features. However, recent graduates and educators can also join with appropriate verification.
                    </p>
                </div>
            </div>
            
            <!-- FAQ 5 -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <button @click="openFaq = openFaq === 5 ? null : 5" 
                        class="w-full px-6 py-5 text-left flex items-center justify-between hover:bg-gray-50 transition">
                    <span class="font-semibold text-gray-900">How are items shipped?</span>
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
                        Most transactions on Swap are done through local meetups on campus. However, sellers can choose to ship items if they prefer. Shipping costs and arrangements are handled between buyer and seller.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-12">
            <p class="text-gray-600 mb-4">Still have questions?</p>
            <a href="/contact" class="text-primary-600 hover:text-primary-700 font-semibold">
                Contact Our Support Team →
            </a>
        </div>
    </div>
</section>

<!-- CTA Section -->
@include('frontend.layouts.partials.cta')

@endsection
