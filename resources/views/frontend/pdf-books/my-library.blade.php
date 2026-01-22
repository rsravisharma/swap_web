@extends('frontend.layouts.app')

@section('title', 'My Library')

@section('content')
<div class="bg-gray-50 py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">My Library</h1>

            @if($purchases->count() > 0)
            <div class="space-y-6">
                @foreach($purchases as $purchase)
                <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
                    <div class="flex flex-col md:flex-row gap-6">
                        <!-- Book Cover -->
                        <div class="flex-shrink-0">
                            <div class="w-32 h-48 bg-gray-100 rounded-lg overflow-hidden">
                                @if($purchase->pdfBook->cover_image_url)
                                <img src="{{ $purchase->pdfBook->cover_image_url }}"
                                    alt="{{ $purchase->pdfBook->title }}"
                                    class="w-full h-full object-cover">
                                @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Book Info -->
                        <div class="flex-grow">
                            <h2 class="text-xl font-bold text-gray-900 mb-2">{{ $purchase->pdfBook->title }}</h2>
                            <p class="text-gray-600 mb-4">{{ $purchase->pdfBook->author }}</p>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4 text-sm">
                                <div>
                                    <span class="text-gray-500">Purchased:</span>
                                    <p class="font-medium">{{ $purchase->created_at->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <span class="text-gray-500">Downloads:</span>
                                    <p class="font-medium">{{ $purchase->download_count }}/{{ $purchase->max_downloads }}</p>
                                </div>
                                <div>
                                    <span class="text-gray-500">Remaining:</span>
                                    <p class="font-medium text-primary-600">{{ $purchase->remaining_downloads }}</p>
                                </div>
                                <div>
                                    <span class="text-gray-500">Access Until:</span>
                                    <p class="font-medium">
                                        @if($purchase->access_expires_at)
                                        {{ $purchase->access_expires_at->format('M d, Y') }}
                                        @else
                                        Never expires
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                @if($purchase->canDownload())
                                <a href="{{ route('pdf-books.download', $purchase) }}"
                                    class="bg-primary-600 text-white px-6 py-2 rounded-lg hover:bg-primary-700 transition font-medium">
                                    Download PDF
                                </a>
                                @else
                                <button disabled
                                    class="bg-gray-300 text-gray-500 px-6 py-2 rounded-lg cursor-not-allowed font-medium">
                                    Download Limit Reached
                                </button>
                                @endif

                                <a href="{{ $purchase->pdfBook->getPreviewLink() }}"
                                    target="_blank"
                                    class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition font-medium">
                                    Preview
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $purchases->links() }}
            </div>
            @else
            <div class="text-center py-12 bg-white rounded-xl shadow-md">
                <svg class="w-24 h-24 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">Your library is empty</h3>
                <p class="text-gray-500 mb-6">Browse our collection and purchase your first book</p>
                <a href="{{ route('pdf-books.index') }}"
                    class="inline-block bg-primary-600 text-white px-6 py-3 rounded-lg hover:bg-primary-700 transition font-semibold">
                    Browse Books
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection