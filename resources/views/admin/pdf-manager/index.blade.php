@extends('admin.layouts.app')

@section('title', 'PDF Manager')
@section('page-title', 'My PDF Books')

@section('content')
<div class="p-6">
    <!-- Success Message -->
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center justify-between">
        <span>{{ session('success') }}</span>
        <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    <!-- Error Message -->
    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center justify-between">
        <span>{{ session('error') }}</span>
        <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Books</p>
                    <h3 class="text-3xl font-bold text-gray-900">{{ $stats['total_books'] }}</h3>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-book text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Active Books</p>
                    <h3 class="text-3xl font-bold text-green-600">{{ $stats['active_books'] }}</h3>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Sales</p>
                    <h3 class="text-3xl font-bold text-purple-600">{{ $stats['total_sales'] }}</h3>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Header -->
<div class="bg-white rounded-xl shadow mb-6">
    <div class="p-6 border-b">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">My Uploaded Books</h2>
                <p class="text-sm text-gray-600 mt-1">
                    @if($myBooks->total() > 0)
                        Showing {{ $myBooks->firstItem() }} to {{ $myBooks->lastItem() }} of {{ $myBooks->total() }} books
                    @else
                        No books found
                    @endif
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <!-- Per Page Selector -->
                <div class="flex items-center space-x-2">
                    <label class="text-sm text-gray-700">Show:</label>
                    <select onchange="window.location.href='{{ route('admin.pdf-manager.index') }}?per_page=' + this.value" 
                            class="px-3 py-1 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="5" {{ request('per_page', 5) == 5 ? 'selected' : '' }}>5</option>
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
                
                <a href="{{ route('admin.pdf-manager.bulk-create') }}" 
                   class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-semibold">
                    <i class="fas fa-layer-group mr-2"></i>Bulk Upload
                </a>
                <a href="{{ route('admin.pdf-manager.create') }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold">
                    <i class="fas fa-plus mr-2"></i>Upload New Book
                </a>
            </div>
        </div>
    </div>
</div>


    <!-- Books List -->
    @if($myBooks->count() > 0)
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Book</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assigned To</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Original Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sales</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($myBooks as $book)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                @if($book->cover_image_url)
                                <img src="{{ $book->cover_image_url }}" 
                                     alt="{{ $book->title }}" 
                                     class="w-12 h-16 object-cover rounded mr-3">
                                @else
                                <div class="w-12 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded mr-3 flex items-center justify-center">
                                    <i class="fas fa-book text-white"></i>
                                </div>
                                @endif
                                <div>
                                    <p class="font-semibold text-gray-900">{{ Str::limit($book->title, 40) }}</p>
                                    @if($book->author)
                                    <p class="text-xs text-gray-500">{{ $book->author }}</p>
                                    @endif
                                    @if($book->isbn)
                                    <p class="text-xs text-gray-400">ISBN: {{ $book->isbn }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                    {{ substr($book->seller->name, 0, 1) }}
                                </div>
                                <div class="ml-2">
                                    <p class="font-semibold text-gray-900">{{ $book->seller->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $book->seller->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-bold text-green-600">₹{{ number_format($book->original_price, 0) }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-bold text-green-600">₹{{ number_format($book->price, 0) }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-semibold text-gray-900">{{ $book->purchases_count }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($book->is_available)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i>Available
                            </span>
                            @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <i class="fas fa-times-circle mr-1"></i>Unavailable
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-900">{{ $book->created_at->format('M d, Y') }}</p>
                            <p class="text-xs text-gray-500">{{ $book->created_at->diffForHumans() }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('admin.pdf-manager.edit', $book->id) }}" 
                                   class="text-blue-600 hover:text-blue-800" 
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ $book->getPreviewLink() }}" 
                                   target="_blank"
                                   class="text-green-600 hover:text-green-800" 
                                   title="Preview">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <form method="POST" 
                                      action="{{ route('admin.pdf-manager.destroy', $book->id) }}" 
                                      class="inline"
                                      onsubmit="return confirm('Are you sure you want to delete this book?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-600 hover:text-red-800" 
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Enhanced Pagination -->
        @if($myBooks->hasPages())
        <div class="px-6 py-4 border-t bg-gray-50">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <!-- Page Info -->
                <div class="text-sm text-gray-700">
                    Showing <span class="font-semibold">{{ $myBooks->firstItem() }}</span> to 
                    <span class="font-semibold">{{ $myBooks->lastItem() }}</span> of 
                    <span class="font-semibold">{{ $myBooks->total() }}</span> results
                </div>

                <!-- Pagination Links -->
                <div>
                    {{ $myBooks->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
    @else
    <!-- Empty State -->
    <div class="bg-white rounded-xl shadow p-12 text-center">
        <svg class="w-20 h-20 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
        </svg>
        <h3 class="text-xl font-bold text-gray-900 mb-2">No Books Uploaded Yet</h3>
        <p class="text-gray-600 mb-6">Start uploading PDF books to make them available to users</p>
        <a href="{{ route('admin.pdf-manager.create') }}" 
           class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold">
            <i class="fas fa-plus mr-2"></i>Upload Your First Book
        </a>
    </div>
    @endif
</div>
@endsection
