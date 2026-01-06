@extends('admin.layouts.app')

@section('title', 'PDF Books List')
@section('page-title', 'All PDF Books')

@section('content')
<div class="p-6">
    <!-- Header with Filters -->
    <div class="bg-white rounded-xl shadow mb-6">
        <div class="p-6 border-b">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">All PDF Books</h2>
                    <p class="text-sm text-gray-600 mt-1">Showing {{ $books->firstItem() ?? 0 }} to {{ $books->lastItem() ?? 0 }} of {{ $books->total() }} books</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.pdf-books.export') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold">
                        <i class="fas fa-download mr-2"></i>Export
                    </a>
                </div>
            </div>

            <!-- Filters Form -->
            <form method="GET" action="{{ route('admin.pdf-books.list') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Title, author, or ISBN"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Availability -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Availability</label>
                    <select name="availability" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Books</option>
                        <option value="available" {{ request('availability') === 'available' ? 'selected' : '' }}>Available</option>
                        <option value="unavailable" {{ request('availability') === 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                    </select>
                </div>

                <!-- Seller -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Seller</label>
                    <select name="seller_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Sellers</option>
                        @foreach($sellers as $seller)
                        <option value="{{ $seller->id }}" {{ request('seller_id') == $seller->id ? 'selected' : '' }}>
                            {{ $seller->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Language -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Language</label>
                    <select name="language" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Languages</option>
                        <option value="en" {{ request('language') === 'en' ? 'selected' : '' }}>English</option>
                        <option value="hi" {{ request('language') === 'hi' ? 'selected' : '' }}>Hindi</option>
                        <option value="es" {{ request('language') === 'es' ? 'selected' : '' }}>Spanish</option>
                        <option value="fr" {{ request('language') === 'fr' ? 'selected' : '' }}>French</option>
                    </select>
                </div>

                <!-- Min Price -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Min Price</label>
                    <input type="number" 
                           name="min_price" 
                           value="{{ request('min_price') }}"
                           placeholder="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Max Price -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Price</label>
                    <input type="number" 
                           name="max_price" 
                           value="{{ request('max_price') }}"
                           placeholder="10000"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Date From -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                    <input type="date" 
                           name="date_from" 
                           value="{{ request('date_from') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Date To -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                    <input type="date" 
                           name="date_to" 
                           value="{{ request('date_to') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Sort By -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                    <select name="sort_by" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Date Added</option>
                        <option value="title" {{ request('sort_by') === 'title' ? 'selected' : '' }}>Title</option>
                        <option value="price" {{ request('sort_by') === 'price' ? 'selected' : '' }}>Price</option>
                        <option value="author" {{ request('sort_by') === 'author' ? 'selected' : '' }}>Author</option>
                    </select>
                </div>

                <!-- Per Page -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Items Per Page</label>
                    <select name="per_page" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="5" {{ request('per_page', 5) == 5 ? 'selected' : '' }}>5</option>
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>

                <!-- Filter Buttons -->
                <div class="flex items-end space-x-2 md:col-span-2 lg:col-span-4">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold">
                        <i class="fas fa-filter mr-2"></i>Filter
                    </button>
                    <a href="{{ route('admin.pdf-books.list') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg font-semibold">
                        Clear Filters
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Books Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($books as $book)
        <div class="bg-white rounded-xl shadow overflow-hidden hover:shadow-lg transition">
            <!-- Book Cover -->
            <div class="relative">
                @if($book->cover_image_url)
                <img src="{{ $book->cover_image_url }}" alt="{{ $book->title }}" class="w-full h-64 object-cover">
                @else
                <div class="w-full h-64 bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                    <i class="fas fa-book text-white text-6xl"></i>
                </div>
                @endif

                <!-- Availability Badge -->
                <div class="absolute top-2 right-2">
                    @if($book->is_available)
                    <span class="px-3 py-1 rounded-full bg-green-500 text-white text-xs font-bold">
                        Available
                    </span>
                    @else
                    <span class="px-3 py-1 rounded-full bg-red-500 text-white text-xs font-bold">
                        Unavailable
                    </span>
                    @endif
                </div>

                <!-- Sales Count -->
                @if($book->purchases_count > 0)
                <div class="absolute bottom-2 left-2">
                    <span class="px-2 py-1 rounded bg-black bg-opacity-70 text-white text-xs font-bold">
                        <i class="fas fa-shopping-cart mr-1"></i>{{ $book->purchases_count }} sold
                    </span>
                </div>
                @endif
            </div>

            <!-- Book Details -->
            <div class="p-4">
                <h3 class="text-lg font-bold text-gray-900 mb-1 truncate" title="{{ $book->title }}">
                    {{ Str::limit($book->title, 50) }}
                </h3>
                
                @if($book->author)
                <p class="text-sm text-gray-600 mb-2">by {{ $book->author }}</p>
                @endif

                <div class="flex items-center justify-between mb-3">
                    <span class="text-2xl font-bold text-blue-600">₹{{ number_format($book->price, 0) }}</span>
                    @if($book->file_size)
                    <span class="text-xs text-gray-500">{{ $book->formatted_file_size }}</span>
                    @endif
                </div>

                <!-- Book Info -->
                <div class="space-y-1 mb-3 text-sm text-gray-600">
                    @if($book->isbn)
                    <p class="truncate"><i class="fas fa-barcode mr-1"></i>{{ $book->isbn }}</p>
                    @endif
                    @if($book->total_pages)
                    <p><i class="fas fa-file-alt mr-1"></i>{{ $book->total_pages }} pages</p>
                    @endif
                    @if($book->publication_year)
                    <p><i class="fas fa-calendar mr-1"></i>{{ $book->publication_year }}</p>
                    @endif
                </div>

                <!-- Seller Info -->
                <div class="flex items-center mb-3 pb-3 border-b">
                    <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                        {{ substr($book->seller->name, 0, 1) }}
                    </div>
                    <div class="ml-2">
                        <p class="text-sm font-semibold text-gray-900">{{ $book->seller->name }}</p>
                        <p class="text-xs text-gray-500">{{ $book->created_at->diffForHumans() }}</p>
                    </div>
                </div>

                <!-- Stats -->
                <div class="flex items-center justify-between text-sm text-gray-600 mb-3">
                    <span><i class="fas fa-shopping-bag mr-1"></i>{{ $book->purchases_count }} sales</span>
                    <span class="text-xs text-gray-500">#{{ $book->id }}</span>
                </div>

                <!-- Actions -->
                <div class="flex space-x-2">
                    <a href="{{ route('admin.pdf-books.details', $book->id) }}" 
                       class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold text-center text-sm">
                        View Details
                    </a>
                    <button onclick="toggleAvailability({{ $book->id }}, {{ $book->is_available ? 'false' : 'true' }})"
                            class="px-4 py-2 {{ $book->is_available ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }} rounded-lg">
                        <i class="fas {{ $book->is_available ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white rounded-xl shadow p-12 text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <p class="text-gray-600 font-medium">No books found</p>
            <p class="text-sm text-gray-500 mt-1">Try adjusting your filters</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($books->hasPages())
    <div class="mt-8">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <!-- Page Info -->
            <div class="text-sm text-gray-700">
                Showing <span class="font-semibold">{{ $books->firstItem() }}</span> to 
                <span class="font-semibold">{{ $books->lastItem() }}</span> of 
                <span class="font-semibold">{{ $books->total() }}</span> results
            </div>

            <!-- Pagination Links -->
            <div>
                {{ $books->links() }}
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Availability Toggle Modal -->
<div id="availabilityModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Update Availability</h3>
        <p class="text-gray-600 mb-4">Are you sure you want to change this book's availability?</p>
        <form method="POST" id="availabilityForm">
            @csrf
            <input type="hidden" name="is_available" id="availabilityValue">
            <div class="flex space-x-3">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold">
                    Confirm
                </button>
                <button type="button" onclick="closeAvailabilityModal()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg font-semibold">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleAvailability(bookId, newValue) {
    const modal = document.getElementById('availabilityModal');
    const form = document.getElementById('availabilityForm');
    form.action = `/admin/pdf-books/${bookId}/availability`;
    document.getElementById('availabilityValue').value = newValue ? '1' : '0';
    modal.classList.remove('hidden');
}

function closeAvailabilityModal() {
    document.getElementById('availabilityModal').classList.add('hidden');
}
</script>
@endsection
