@extends('frontend.layouts.app')

@section('title', $pdfBook->title)

@section('content')
<div class="bg-gray-50 py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
            <!-- Back Button -->
            <a href="{{ route('pdf-books.index') }}" class="inline-flex items-center text-primary-600 hover:text-primary-700 mb-6">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Books
            </a>

            {{-- ✅ FIXED: App Download Promotion Banner - Dark Background Throughout --}}
            <div class="bg-gradient-to-br from-primary-700 via-primary-600 to-primary-800 rounded-2xl shadow-2xl p-8 mb-8 relative overflow-hidden">
                {{-- Decorative Background Pattern --}}
                <div class="absolute inset-0 opacity-5">
                    <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                        <path d="M0,0 L100,0 L100,100 Z" fill="white" />
                    </svg>
                </div>

                <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
                    {{-- Left Content --}}
                    <div class="flex-1 text-white">
                        <div class="flex items-center mb-3">
                            <svg class="w-10 h-10 mr-3 text-yellow-300" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <span class="bg-yellow-400 text-primary-900 px-3 py-1 rounded-full text-sm font-bold uppercase tracking-wide">
                                Exclusive Offer
                            </span>
                        </div>

                        <h2 class="text-3xl md:text-4xl font-bold mb-3 text-white">
                            Get 20% Off on All Books!
                        </h2>
                        <p class="text-lg md:text-xl text-white/90 mb-4">
                            Download our mobile app and enjoy instant access to thousands of books with exclusive app-only discount
                        </p>

                        {{-- Features List --}}
                        <ul class="space-y-2 mb-6">
                            <li class="flex items-center text-white">
                                <svg class="w-5 h-5 mr-2 text-green-300 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Instant 20% discount on purchase
                            </li>
                            <li class="flex items-center text-white">
                                <svg class="w-5 h-5 mr-2 text-green-300 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Read offline anytime, anywhere
                            </li>
                            <li class="flex items-center text-white">
                                <svg class="w-5 h-5 mr-2 text-green-300 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Sync across all your devices
                            </li>
                        </ul>
                    </div>

                    {{-- Right Content - Download Button --}}
                    <div class="flex-shrink-0 text-center">
                        <a href="https://play.google.com/store/apps/details?id=com.cubebitz.swap&hl=en_IN"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-block bg-white hover:bg-gray-100 text-primary-700 font-bold px-8 py-4 rounded-xl shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105">
                            <div class="flex items-center gap-3">
                                {{-- Google Play Icon --}}
                                <svg class="w-8 h-8" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M3,20.5V3.5C3,2.91 3.34,2.39 3.84,2.15L13.69,12L3.84,21.85C3.34,21.6 3,21.09 3,20.5M16.81,15.12L6.05,21.34L14.54,12.85L16.81,15.12M20.16,10.81C20.5,11.08 20.75,11.5 20.75,12C20.75,12.5 20.53,12.9 20.18,13.18L17.89,14.5L15.39,12L17.89,9.5L20.16,10.81M6.05,2.66L16.81,8.88L14.54,11.15L6.05,2.66Z" />
                                </svg>
                                <div class="text-left">
                                    <div class="text-xs uppercase tracking-wide text-gray-600">Download on</div>
                                    <div class="text-xl font-bold leading-tight text-primary-700">Google Play</div>
                                </div>
                            </div>
                        </a>

                        {{-- App Rating --}}
                        <div class="mt-3 text-white text-sm">
                            <div class="flex items-center justify-center gap-1 mb-1">
                                <svg class="w-4 h-4 text-yellow-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <svg class="w-4 h-4 text-yellow-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <svg class="w-4 h-4 text-yellow-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <svg class="w-4 h-4 text-yellow-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <svg class="w-4 h-4 text-yellow-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            </div>
                            <span class="text-white/90 font-medium">Rated 4.5 stars by students</span>
                        </div>
                    </div>
                </div>
            </div>
            {{-- ✅ END: App Download Banner --}}


            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 p-8">
                    <!-- Book Cover -->
                    <div class="md:col-span-1">
                        <div class="bg-gray-100 rounded-lg overflow-hidden shadow-md">
                            @if($pdfBook->cover_image_url)
                            <img src="{{ $pdfBook->cover_image_url }}"
                                alt="{{ $pdfBook->title }}"
                                class="w-full h-auto">
                            @else
                            <div class="h-96 flex items-center justify-center">
                                <svg class="w-32 h-32 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Book Details -->
                    <div class="md:col-span-2">
                        <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $pdfBook->title }}</h1>

                        <div class="space-y-3 mb-6">
                            <div class="flex items-center text-gray-700">
                                <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span><strong>Author:</strong> {{ $pdfBook->author }}</span>
                            </div>

                            @if($pdfBook->publisher)
                            <div class="flex items-center text-gray-700">
                                <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                <span><strong>Publisher:</strong> {{ $pdfBook->publisher }}</span>
                            </div>
                            @endif

                            @if($pdfBook->category)
                            <div class="flex items-center text-gray-700 mb-2">
                                <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                <span><strong>Category:</strong>
                                    <a href="{{ route('pdf-books.category', $pdfBook->category) }}"
                                        class="text-primary-600 hover:underline font-medium">
                                        {{ $pdfBook->category->full_name }}
                                    </a>
                                </span>
                            </div>
                            @endif

                            @if($pdfBook->publication_year)
                            <div class="flex items-center text-gray-700">
                                <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span><strong>Year:</strong> {{ $pdfBook->publication_year }}</span>
                            </div>
                            @endif

                            @if($pdfBook->language)
                            <div class="flex items-center text-gray-700">
                                <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                                </svg>
                                <span><strong>Language:</strong> {{ $pdfBook->language }}</span>
                            </div>
                            @endif

                            @if($pdfBook->total_pages)
                            <div class="flex items-center text-gray-700">
                                <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span><strong>Pages:</strong> {{ $pdfBook->total_pages }}</span>
                            </div>
                            @endif

                            <div class="flex items-center text-gray-700">
                                <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <span><strong>File Size:</strong> {{ $pdfBook->formatted_file_size }}</span>
                            </div>
                        </div>

                        <!-- Description -->
                        @if($pdfBook->description)
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Description</h3>
                            <p class="text-gray-700 leading-relaxed">{!! $pdfBook->description !!}</p>
                        </div>
                        @endif

                        <!-- Price and Purchase -->
                        <div class="border-t pt-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Price</p>
                                    <p class="text-4xl font-bold text-primary-600">₹{{ number_format($pdfBook->original_price, 2) }}</p>
                                </div>

                                @if($hasPurchased)
                                <div class="text-right">
                                    <div class="bg-green-100 text-green-800 px-4 py-2 rounded-lg mb-3">
                                        <svg class="w-5 h-5 inline-block mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        Already Purchased
                                    </div>
                                    <a href="{{ route('pdf-books.my-library') }}"
                                        class="inline-block bg-primary-600 text-white px-8 py-3 rounded-lg hover:bg-primary-700 transition font-semibold">
                                        View in Library
                                    </a>
                                </div>
                                @else
                                <button id="buyNowBtn"
                                    class="bg-primary-600 text-white px-8 py-3 rounded-lg hover:bg-primary-700 transition font-semibold text-lg shadow-lg hover:shadow-xl">
                                    Buy Now
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Similar Books -->
            @if($similarBooks->count() > 0)
            <div class="mt-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Similar Books</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    @foreach($similarBooks as $similar)
                    <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-shadow overflow-hidden">
                        <a href="{{ route('pdf-books.show', $similar) }}">
                            <div class="h-48 bg-gray-100 flex items-center justify-center overflow-hidden">
                                @if($similar->cover_image_url)
                                <img src="{{ $similar->cover_image_url }}"
                                    alt="{{ $similar->title }}"
                                    class="w-full h-full object-cover">
                                @else
                                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                                @endif
                            </div>
                        </a>
                        <div class="p-4">
                            <a href="{{ route('pdf-books.show', $similar) }}">
                                <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2 hover:text-primary-600">
                                    {{ $similar->title }}
                                </h3>
                            </a>
                            <p class="text-sm text-gray-600 mb-2">{{ $similar->author }}</p>
                            <p class="text-xl font-bold text-primary-600">₹{{ number_format($similar->original_price, 2) }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@if(!$hasPurchased)
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    let currentOrderData = null; // ✅ Global storage for payment data

    document.getElementById('buyNowBtn').addEventListener('click', async function() {
        this.disabled = true;
        this.textContent = 'Processing...';

        try {
            // 1. Initiate payment
            const response = await fetch('{{ route("pdf-books.initiate-payment", $pdfBook) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const data = await response.json();

            if (!data.success) {
                if (data.redirect) {
                    window.location.href = data.redirect;
                    return;
                }
                alert(data.message);
                this.disabled = false;
                this.textContent = 'Buy Now';
                return;
            }

            // ✅ 2. Store payment data globally
            currentOrderData = data;

            // 3. Razorpay options
            const options = {
                key: data.key,
                amount: data.amount,
                currency: data.currency,
                name: data.name,
                description: data.description,
                image: data.image,
                order_id: data.order_id,
                prefill: data.prefill,
                theme: {
                    color: '#4F46E5'
                },
                handler: async function(response) {
                    try {
                        console.log('Payment response:', response); // ✅ Debug log

                        // ✅ 4. Verify payment using STORED data
                        const verifyResponse = await fetch('{{ route("pdf-books.verify-payment") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                razorpay_payment_id: response.razorpay_payment_id,
                                razorpay_order_id: response.razorpay_order_id,
                                razorpay_signature: response.razorpay_signature
                                // ✅ NO order_id needed - controller finds by razorpay_order_id
                            })
                        });

                        const verifyData = await verifyResponse.json();
                        console.log('Verify response:', verifyData); // ✅ Debug log

                        if (verifyData.success) {
                            // ✅ 5. Use SERVER redirect - 100% reliable
                            window.location.href = verifyData.data.redirect;
                        } else {
                            alert(verifyData.message || 'Payment verification failed');
                            this.disabled = false;
                            this.textContent = 'Buy Now';
                        }
                    } catch (error) {
                        console.error('Verification error:', error);
                        alert('Payment processing failed. Please contact support.');
                        this.disabled = false;
                        this.textContent = 'Buy Now';
                    }
                },
                modal: {
                    ondismiss: function() {
                        document.getElementById('buyNowBtn').disabled = false;
                        document.getElementById('buyNowBtn').textContent = 'Buy Now';
                    }
                }
            };

            const razorpay = new Razorpay(options);
            razorpay.open();

        } catch (error) {
            console.error('Payment error:', error);
            alert('Payment initiation failed. Please try again.');
            this.disabled = false;
            this.textContent = 'Buy Now';
        }
    });
</script>
@endif
@endsection