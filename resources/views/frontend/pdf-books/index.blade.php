@extends('frontend.layouts.app')

@section('title', 'PDF Books Store')

@section('content')
<div class="bg-gradient-to-br from-primary-50 to-white py-12">
    <div class="container mx-auto px-4">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">PDF Books Store</h1>
            <p class="text-lg text-gray-600">Browse our collection of digital books</p>
        </div>

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
                    <div class="h-64 bg-gray-100 flex items-center justify-center overflow-hidden">
                        @if($book->cover_image_url)
                        <img src="{{ $book->cover_image_url }}"
                            alt="{{ $book->title }}"
                            class="w-full h-full object-cover">
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