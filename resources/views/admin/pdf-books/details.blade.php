@extends('admin.layouts.app')

@section('title', 'Book Details - ' . $book->title)
@section('page-title', 'Book Details')

@section('content')
<div class="p-6">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('admin.pdf-books.list') }}" class="text-blue-600 hover:text-blue-800 font-semibold">
            <i class="fas fa-arrow-left mr-2"></i>Back to Books
        </a>
    </div>

    <!-- Book Header -->
    <div class="bg-white rounded-xl shadow mb-6">
        <div class="p-6">
            <div class="flex items-start justify-between">
                <div class="flex">
                    <!-- Book Cover -->
                    @if($book->cover_image_url)
                    <img src="{{ $book->cover_image_url }}" alt="{{ $book->title }}" class="w-40 h-56 object-cover rounded-lg shadow-lg">
                    @else
                    <div class="w-40 h-56 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg shadow-lg flex items-center justify-center">
                        <i class="fas fa-book text-white text-5xl"></i>
                    </div>
                    @endif

                    <!-- Book Info -->
                    <div class="ml-6 flex-1">
                        <div class="flex items-center space-x-3 mb-2">
                            <h1 class="text-3xl font-bold text-gray-900">{{ $book->title }}</h1>
                            @if($book->is_available)
                            <span class="px-3 py-1 rounded-full bg-green-100 text-green-800 text-sm font-semibold">
                                Available
                            </span>
                            @else
                            <span class="px-3 py-1 rounded-full bg-red-100 text-red-800 text-sm font-semibold">
                                Unavailable
                            </span>
                            @endif
                        </div>
                        
                        @if($book->author)
                        <p class="text-xl text-gray-600 mb-2">by {{ $book->author }}</p>
                        @endif

                        @if($book->publisher)
                        <p class="text-gray-600 mb-2"><i class="fas fa-building mr-2"></i>{{ $book->publisher }}</p>
                        @endif

                        <div class="flex items-center space-x-6 mb-4">
                            <span class="text-4xl font-bold text-blue-600">₹{{ number_format($book->price, 2) }} (₹{{ number_format($book->original_price, 2) }})</span>
                            @if($book->isbn)
                            <span class="text-gray-600"><i class="fas fa-barcode mr-1"></i>{{ $book->isbn }}</span>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-4 text-sm">
                            @if($book->publication_year)
                            <div>
                                <p class="text-gray-500">Publication Year</p>
                                <p class="font-semibold text-gray-900">{{ $book->publication_year }}</p>
                            </div>
                            @endif
                            @if($book->total_pages)
                            <div>
                                <p class="text-gray-500">Total Pages</p>
                                <p class="font-semibold text-gray-900">{{ $book->total_pages }}</p>
                            </div>
                            @endif
                            @if($book->language)
                            <div>
                                <p class="text-gray-500">Language</p>
                                <p class="font-semibold text-gray-900">{{ strtoupper($book->language) }}</p>
                            </div>
                            @endif
                            @if($book->file_size)
                            <div>
                                <p class="text-gray-500">File Size</p>
                                <p class="font-semibold text-gray-900">{{ $book->formatted_file_size }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col space-y-2">
                    <button onclick="toggleAvailability()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold">
                        <i class="fas fa-edit mr-2"></i>Toggle Availability
                    </button>
                    <a href="{{ $book->getPreviewLink() }}" target="_blank" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold text-center">
                        <i class="fas fa-eye mr-2"></i>Preview Book
                    </a>
                    <form method="POST" action="{{ route('admin.pdf-books.destroy', $book->id) }}" onsubmit="return confirm('Are you sure you want to delete this book?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-semibold">
                            <i class="fas fa-trash mr-2"></i>Delete Book
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Sales</p>
                    <h3 class="text-3xl font-bold text-gray-900">{{ $purchaseStats['total_purchases'] }}</h3>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-purple-600 text-xl"></i>
                </div>
            </div>
            <p class="text-sm text-green-600 mt-2">+{{ $purchaseStats['purchases_today'] }} today</p>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Revenue</p>
                    <h3 class="text-3xl font-bold text-green-600">₹{{ number_format($purchaseStats['total_revenue'], 0) }}</h3>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-rupee-sign text-green-600 text-xl"></i>
                </div>
            </div>
            <p class="text-sm text-gray-600 mt-2">From all sales</p>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Active Purchases</p>
                    <h3 class="text-3xl font-bold text-blue-600">{{ $purchaseStats['active_purchases'] }}</h3>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-blue-600 text-xl"></i>
                </div>
            </div>
            <p class="text-sm text-gray-600 mt-2">Currently active</p>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Downloads</p>
                    <h3 class="text-3xl font-bold text-red-600">{{ $purchaseStats['total_downloads'] }}</h3>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-download text-red-600 text-xl"></i>
                </div>
            </div>
            <p class="text-sm text-gray-600 mt-2">Avg: {{ number_format($purchaseStats['avg_downloads'], 1) }}</p>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Book Description & Seller Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Description -->
            @if($book->description)
            <div class="bg-white rounded-xl shadow">
                <div class="p-6 border-b">
                    <h3 class="text-xl font-bold text-gray-900">Description</h3>
                </div>
                <div class="p-6">
                    <p class="text-gray-700 whitespace-pre-wrap">{{ $book->description }}</p>
                </div>
            </div>
            @endif

            <!-- Purchase Trend Chart -->
            @if($purchaseTrend && $purchaseTrend->count() > 0)
            <div class="bg-white rounded-xl shadow">
                <div class="p-6 border-b">
                    <h3 class="text-xl font-bold text-gray-900">Sales Trend (Last 30 Days)</h3>
                </div>
                <div class="p-6">
                    <div style="position: relative; height: 300px;">
                        <canvas id="purchaseTrendChart"></canvas>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Seller Information -->
        <div class="bg-white rounded-xl shadow h-fit">
            <div class="p-6 border-b">
                <h3 class="text-xl font-bold text-gray-900">Seller Information</h3>
            </div>
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center text-white text-2xl font-bold">
                        {{ substr($book->seller->name, 0, 1) }}
                    </div>
                    <div class="ml-4">
                        <a href="{{ route('admin.analytics.user.details', $book->seller->id) }}" 
                           class="text-lg font-bold text-blue-600 hover:text-blue-800">
                            {{ $book->seller->name }}
                        </a>
                        <p class="text-sm text-gray-600">{{ $book->seller->email }}</p>
                    </div>
                </div>

                <div class="space-y-3">
    <div>
        <p class="text-sm text-gray-600">Phone</p>
        <p class="font-semibold text-gray-900">{{ $book->seller->phone ?? 'N/A' }}</p>
    </div>
    <div>
        <p class="text-sm text-gray-600">Total Books</p>
        <p class="font-semibold text-gray-900">{{ $book->seller->pdf_books_count ?? 0 }}</p>
    </div>
    <div>
        <p class="text-sm text-gray-600">Total Sales</p>
        <p class="font-semibold text-gray-900">{{ $book->seller->sold_pdf_books_count ?? 0 }}</p>
    </div>
    <div>
        <p class="text-sm text-gray-600">Member Since</p>
        <p class="font-semibold text-gray-900">{{ $book->seller->created_at->format('M Y') }}</p>
    </div>
    <div>
        <p class="text-sm text-gray-600">Book Added</p>
        <p class="font-semibold text-gray-900">{{ $book->created_at->format('M d, Y') }}</p>
    </div>
</div>


                <a href="{{ route('admin.analytics.user.details', $book->seller->id) }}" 
                   class="block mt-6 bg-blue-600 hover:bg-blue-700 text-white text-center px-4 py-2 rounded-lg font-semibold">
                    View Seller Profile
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Purchases -->
    <div class="bg-white rounded-xl shadow">
        <div class="p-6 border-b">
            <h3 class="text-xl font-bold text-gray-900">Recent Purchases</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Buyer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Downloads</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($recentPurchases as $purchase)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                                    {{ substr($purchase->user->name, 0, 1) }}
                                </div>
                                <div class="ml-3">
                                    <p class="font-semibold text-gray-900">{{ $purchase->user->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $purchase->user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">₹{{ number_format($purchase->purchase_price, 2) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $purchase->download_count }} / {{ $purchase->max_downloads }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-medium
                                {{ $purchase->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $purchase->status === 'expired' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $purchase->status === 'revoked' ? 'bg-gray-100 text-gray-800' : '' }}">
                                {{ ucfirst($purchase->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $purchase->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.analytics.user.details', $purchase->user->id) }}" 
                               class="text-blue-600 hover:text-blue-800 font-semibold text-sm">
                                View User
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">No purchases yet</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Availability Toggle Modal -->
<div id="availabilityModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Update Availability</h3>
        <p class="text-gray-600 mb-4">Change this book's availability status?</p>
        <form method="POST" action="{{ route('admin.pdf-books.update-availability', $book->id) }}">
            @csrf
            <input type="hidden" name="is_available" value="{{ $book->is_available ? '0' : '1' }}">
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

@if($purchaseTrend && $purchaseTrend->count() > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('purchaseTrendChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($purchaseTrend->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('M d'))) !!},
                datasets: [{
                    label: 'Sales',
                    data: {!! json_encode($purchaseTrend->pluck('count')) !!},
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.3,
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }
});

function toggleAvailability() {
    document.getElementById('availabilityModal').classList.remove('hidden');
}

function closeAvailabilityModal() {
    document.getElementById('availabilityModal').classList.add('hidden');
}
</script>
@endif
@endsection
