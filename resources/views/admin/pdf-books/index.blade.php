@extends('admin.layouts.app')

@section('title', 'PDF Books Management')
@section('page-title', 'PDF Books Dashboard')

@section('content')
<div class="p-6">
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Books</p>
                    <h3 class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_books']) }}</h3>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-book text-blue-600 text-xl"></i>
                </div>
            </div>
            <p class="text-sm text-green-600 mt-2">+{{ $stats['books_today'] }} today</p>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Available</p>
                    <h3 class="text-3xl font-bold text-green-600">{{ number_format($stats['available_books']) }}</h3>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
            <p class="text-sm text-gray-600 mt-2">{{ round(($stats['available_books'] / max($stats['total_books'], 1)) * 100, 1) }}% active</p>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Sales</p>
                    <h3 class="text-3xl font-bold text-purple-600">{{ number_format($stats['total_purchases']) }}</h3>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-purple-600 text-xl"></i>
                </div>
            </div>
            <p class="text-sm text-green-600 mt-2">+{{ $stats['purchases_today'] }} today</p>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Revenue</p>
                    <h3 class="text-3xl font-bold text-yellow-600">₹{{ number_format($stats['total_revenue'], 0) }}</h3>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-rupee-sign text-yellow-600 text-xl"></i>
                </div>
            </div>
            <p class="text-sm text-green-600 mt-2">+₹{{ number_format($stats['revenue_today'], 0) }} today</p>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Downloads</p>
                    <h3 class="text-3xl font-bold text-red-600">{{ number_format($stats['total_downloads']) }}</h3>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-download text-red-600 text-xl"></i>
                </div>
            </div>
            <p class="text-sm text-gray-600 mt-2">All time</p>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Books Growth Chart -->
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Books Added (Last 30 Days)</h3>
            
            @if($booksGrowth && $booksGrowth->count() > 0)
                <div style="position: relative; height: 300px;">
                    <canvas id="booksGrowthChart"></canvas>
                </div>
            @else
                <div class="flex items-center justify-center h-64 bg-gray-50 rounded-lg">
                    <p class="text-gray-500">No data available</p>
                </div>
            @endif
        </div>

        <!-- Revenue Growth Chart -->
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Revenue (Last 30 Days)</h3>
            
            @if($revenueGrowth && $revenueGrowth->count() > 0)
                <div style="position: relative; height: 300px;">
                    <canvas id="revenueGrowthChart"></canvas>
                </div>
            @else
                <div class="flex items-center justify-center h-64 bg-gray-50 rounded-lg">
                    <p class="text-gray-500">No data available</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Top Selling Books -->
        <div class="bg-white rounded-xl shadow">
            <div class="p-6 border-b flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-900">Top Selling Books</h3>
                <a href="{{ route('admin.pdf-books.list') }}" class="text-sm text-blue-600 hover:text-blue-800">View All →</a>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($topSellingBooks as $book)
                <div class="p-4 hover:bg-gray-50 transition">
                    <div class="flex items-start">
                        @if($book->cover_image_url)
                        <img src="{{ $book->cover_image_url }}" alt="{{ $book->title }}" class="w-16 h-20 object-cover rounded mr-4">
                        @else
                        <div class="w-16 h-20 bg-gray-200 rounded mr-4 flex items-center justify-center">
                            <i class="fas fa-book text-gray-400"></i>
                        </div>
                        @endif
                        
                        <div class="flex-1">
                            <a href="{{ route('admin.pdf-books.details', $book->id) }}" class="font-semibold text-gray-900 hover:text-blue-600">
                                {{ Str::limit($book->title, 40) }}
                            </a>
                            <p class="text-sm text-gray-600">by {{ $book->author ?? 'Unknown' }}</p>
                            <div class="flex items-center mt-1 space-x-2">
                                <span class="text-lg font-bold text-blue-600">₹{{ number_format($book->price, 0) }}</span>
                                <span class="text-sm text-gray-500">{{ $book->purchases_count }} sales</span>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-center text-gray-500 py-8">No sales yet</p>
                @endforelse
            </div>
        </div>

        <!-- Recent Purchases -->
        <div class="bg-white rounded-xl shadow">
            <div class="p-6 border-b flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-900">Recent Purchases</h3>
                <a href="{{ route('admin.pdf-books.purchases') }}" class="text-sm text-blue-600 hover:text-blue-800">View All →</a>
            </div>
            <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                @forelse($recentPurchases as $purchase)
                <div class="p-4 hover:bg-gray-50 transition">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="font-semibold text-gray-900">{{ $purchase->user->name }}</p>
                            <p class="text-sm text-gray-600">{{ Str::limit($purchase->pdfBook->title, 40) }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $purchase->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-green-600">₹{{ number_format($purchase->purchase_price, 0) }}</p>
                            <span class="text-xs px-2 py-1 rounded
                                {{ $purchase->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $purchase->status === 'expired' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $purchase->status === 'revoked' ? 'bg-gray-100 text-gray-800' : '' }}">
                                {{ ucfirst($purchase->status) }}
                            </span>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-center text-gray-500 py-8">No purchases yet</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Top Sellers -->
    <div class="bg-white rounded-xl shadow mb-8">
        <div class="p-6 border-b">
            <h3 class="text-xl font-bold text-gray-900">Top Sellers</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seller</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Books Listed</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Sales</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($topSellers as $seller)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                                    {{ substr($seller->name, 0, 1) }}
                                </div>
                                <div class="ml-3">
                                    <p class="font-semibold text-gray-900">{{ $seller->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $seller->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $seller->books_count }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $seller->sales_count }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.analytics.user.details', $seller->id) }}" class="text-blue-600 hover:text-blue-800 font-semibold text-sm">
                                View Profile →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">No sellers yet</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="{{ route('admin.pdf-books.list') }}" class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-list text-blue-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">View All Books</h3>
                    <p class="text-sm text-gray-600">Browse and manage PDF books</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.pdf-books.purchases') }}" class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-shopping-bag text-green-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">View Purchases</h3>
                    <p class="text-sm text-gray-600">Manage customer purchases</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.pdf-books.export') }}" class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-download text-purple-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Export Data</h3>
                    <p class="text-sm text-gray-600">Download books report</p>
                </div>
            </div>
        </a>
    </div>
</div>

@if(($booksGrowth && $booksGrowth->count() > 0) || ($revenueGrowth && $revenueGrowth->count() > 0))
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if($booksGrowth && $booksGrowth->count() > 0)
    const booksCtx = document.getElementById('booksGrowthChart');
    if (booksCtx) {
        new Chart(booksCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($booksGrowth->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('M d'))) !!},
                datasets: [{
                    label: 'Books Added',
                    data: {!! json_encode($booksGrowth->pluck('count')) !!},
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
    @endif

    @if($revenueGrowth && $revenueGrowth->count() > 0)
    const revenueCtx = document.getElementById('revenueGrowthChart');
    if (revenueCtx) {
        new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($revenueGrowth->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('M d'))) !!},
                datasets: [{
                    label: 'Revenue',
                    data: {!! json_encode($revenueGrowth->pluck('total')) !!},
                    backgroundColor: 'rgba(234, 179, 8, 0.8)',
                    borderColor: 'rgb(234, 179, 8)',
                    borderWidth: 1,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }
    @endif
});
</script>
@endif
@endsection
