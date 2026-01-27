@extends('frontend.layouts.app')

@section('title', 'PDF Books Store')

@section('content')
<div class="bg-gradient-to-br from-primary-50 to-white py-12">
    <div class="container mx-auto px-4">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">PDF Books Store</h1>
            <p class="text-lg text-gray-600">Browse our collection of digital books</p>
        </div>

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


        <!-- Search and Filters -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <form method="GET" action="{{ route('pdf-books.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div class="md:col-span-2">
                        <input type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search by title, author, ISBN..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>

                    <!-- Category Filter -->
                    <div>
                        <select name="category_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                            @if($category->children->count() > 0)
                            @foreach($category->children as $child)
                            <option value="{{ $child->id }}" {{ request('category_id') == $child->id ? 'selected' : '' }}>
                                &nbsp;&nbsp;{{ $child->name }}
                            </option>
                            @if($child->children->count() > 0)
                            @foreach($child->children as $grandchild)
                            <option value="{{ $grandchild->id }}" {{ request('category_id') == $grandchild->id ? 'selected' : '' }}>
                                &nbsp;&nbsp;&nbsp;&nbsp;{{ $grandchild->name }}
                            </option>
                            @endforeach
                            @endif
                            @endforeach
                            @endif
                            @endforeach
                        </select>
                    </div>

                    <!-- Language Filter -->
                    <div>
                        <select name="language" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                            <option value="">All Languages</option>
                            @foreach($languages as $language)
                            <option value="{{ $language }}" {{ request('language') == $language ? 'selected' : '' }}>
                                {{ $language }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                {{-- Rest of filters remain same --}}
            </form>
        </div>

        {{-- Add Category Chips below book cards --}}
        @if($books->count() > 0)
        <div class="mb-8">
            <h3 class="text-lg font-semibold mb-4">Browse by Category:</h3>
            <div class="flex flex-wrap gap-2">
                @foreach($categories as $category)
                <a href="{{ route('pdf-books.category', $category) }}"
                    class="px-4 py-2 bg-gray-100 hover:bg-primary-100 text-sm rounded-lg transition {{ request()->routeIs('pdf-books.category') && request()->route('pdfCategory')->id == $category->id ? 'bg-primary-600 text-white' : '' }}">
                    {{ $category->name }}
                    <span class="ml-1 text-xs">({{ $category->pdfBooks()->available()->count() }})</span>
                </a>
                @if($category->children->count() > 0)
                @foreach($category->children as $child)
                <a href="{{ route('pdf-books.category', $child) }}"
                    class="px-3 py-2 bg-blue-50 hover:bg-primary-100 text-xs rounded transition {{ request()->routeIs('pdf-books.category') && request()->route('pdfCategory')->id == $child->id ? 'bg-primary-600 text-white' : '' }}">
                    {{ $child->name }}
                    <span class="ml-1">({{ $child->pdfBooks()->available()->count() }})</span>
                </a>
                @endforeach
                @endif
                @endforeach
            </div>
        </div>
        @endif

        <!-- Books Grid -->
        @if($books->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-8">
            @foreach($books as $book)
            <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-shadow overflow-hidden">
                <a href="{{ route('pdf-books.show', $book) }}">
                    <div class="relative h-80 bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center overflow-hidden group">
                        @if($book->cover_image_url)
                        <img src="{{ $book->cover_image_url }}"
                            alt="{{ $book->title }}"
                            class="max-w-full max-h-full object-contain p-4 transition-transform duration-300 group-hover:scale-105 drop-shadow-xl">
                        @else
                        <svg class="w-20 h-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        @endif
                    </div>
                </a>

                <div class="p-4">
                    <a href="{{ route('pdf-books.show', $book) }}">
                        <h3 class="font-semibold text-lg text-gray-900 mb-2 line-clamp-2 hover:text-primary-600">
                            {{ $book->title }}
                        </h3>
                    </a>
                    <p class="text-sm text-gray-600 mb-1">{{ $book->author }}</p>

                    <div class="flex items-center justify-between mt-4">
                        <span class="text-2xl font-bold text-primary-600">₹{{ number_format($book->original_price, 2) }}</span>
                        <a href="{{ route('pdf-books.show', $book) }}"
                            class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition text-sm font-medium">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $books->links() }}
        </div>
        @else
        <div class="text-center py-12">
            <svg class="w-24 h-24 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">No books found</h3>
            <p class="text-gray-500">Try adjusting your search or filters</p>
        </div>
        @endif
    </div>
</div>
@endsection