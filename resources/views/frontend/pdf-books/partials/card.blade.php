<div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-shadow overflow-hidden group">
    <a href="{{ route('pdf-books.show', $book) }}">
        <div class="h-64 bg-gray-100 flex items-center justify-center overflow-hidden group-hover:scale-105 transition-transform">
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
        @if($book->category)
        <span class="inline-block bg-primary-100 text-primary-800 text-xs px-2 py-1 rounded-full mb-2">
            {{ Str::limit($book->category->name, 15) }}
        </span>
        @endif
        
        <a href="{{ route('pdf-books.show', $book) }}">
            <h3 class="font-semibold text-lg text-gray-900 mb-2 line-clamp-2 group-hover:text-primary-600 transition">
                {{ $book->title }}
            </h3>
        </a>
        <p class="text-sm text-gray-600 mb-3">{{ $book->author }}</p>
        
        <div class="flex items-center justify-between">
            <span class="text-2xl font-bold text-primary-600">₹{{ number_format($book->original_price, 2) }}</span>
            <a href="{{ route('pdf-books.show', $book) }}" 
               class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition text-sm font-medium">
                View Details
            </a>
        </div>
    </div>
</div>
