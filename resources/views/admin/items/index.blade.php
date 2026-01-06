@extends('admin.layouts.app')

@section('title', 'Items Management')
@section('page-title', 'Items Dashboard')

@section('content')
<div class="p-6">
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Items</p>
                    <h3 class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_items']) }}</h3>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-box text-blue-600 text-xl"></i>
                </div>
            </div>
            <p class="text-sm text-green-600 mt-2">+{{ $stats['items_today'] }} today</p>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Active Items</p>
                    <h3 class="text-3xl font-bold text-green-600">{{ number_format($stats['active_items']) }}</h3>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
            <p class="text-sm text-gray-600 mt-2">{{ round(($stats['active_items'] / max($stats['total_items'], 1)) * 100, 1) }}% of total</p>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Sold Items</p>
                    <h3 class="text-3xl font-bold text-purple-600">{{ number_format($stats['sold_items']) }}</h3>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-purple-600 text-xl"></i>
                </div>
            </div>
            <p class="text-sm text-gray-600 mt-2">{{ round(($stats['sold_items'] / max($stats['total_items'], 1)) * 100, 1) }}% conversion</p>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Value</p>
                    <h3 class="text-3xl font-bold text-yellow-600">₹{{ number_format($stats['total_value'], 0) }}</h3>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-rupee-sign text-yellow-600 text-xl"></i>
                </div>
            </div>
            <p class="text-sm text-gray-600 mt-2">Active listings value</p>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Items Growth Chart -->
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Items Growth (Last 30 Days)</h3>
            
            @if($itemsGrowth && $itemsGrowth->count() > 0)
                <div style="position: relative; height: 300px;">
                    <canvas id="itemsGrowthChart"></canvas>
                </div>
            @else
                <div class="flex items-center justify-center h-64 bg-gray-50 rounded-lg">
                    <p class="text-gray-500">No data available</p>
                </div>
            @endif
        </div>

        <!-- Items by Category -->
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Top Categories</h3>
            
            @if($itemsByCategory->count() > 0)
                <div class="space-y-3">
                    @foreach($itemsByCategory->take(8) as $category)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center flex-1">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                @if($category->icon)
                                    <i class="{{ $category->icon }} text-blue-600"></i>
                                @else
                                    <i class="fas fa-tag text-blue-600"></i>
                                @endif
                            </div>
                            <span class="font-medium text-gray-900">{{ $category->name }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-lg font-bold text-gray-900">{{ $category->items_count }}</span>
                            <span class="text-sm text-gray-500 ml-1">items</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-gray-500 py-8">No categories with items</p>
            @endif
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Recent Items -->
        <div class="bg-white rounded-xl shadow">
            <div class="p-6 border-b flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-900">Recent Items</h3>
                <a href="{{ route('admin.items.list') }}" class="text-sm text-blue-600 hover:text-blue-800">View All →</a>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($recentItems as $item)
                <div class="p-4 hover:bg-gray-50 transition">
                    <div class="flex items-start">
                        @if($item->primary_image_url)
                        <img src="{{ $item->primary_image_url }}" alt="{{ $item->title }}" class="w-16 h-16 object-cover rounded-lg mr-4">
                        @else
                        <div class="w-16 h-16 bg-gray-200 rounded-lg mr-4 flex items-center justify-center">
                            <i class="fas fa-image text-gray-400"></i>
                        </div>
                        @endif
                        
                        <div class="flex-1">
                            <a href="{{ route('admin.items.details', $item->id) }}" class="font-semibold text-gray-900 hover:text-blue-600">
                                {{ Str::limit($item->title, 40) }}
                            </a>
                            <p class="text-sm text-gray-600">by {{ $item->user->name }}</p>
                            <div class="flex items-center mt-1 space-x-2">
                                <span class="text-lg font-bold text-blue-600">₹{{ number_format($item->price, 0) }}</span>
                                <span class="px-2 py-0.5 rounded text-xs font-medium 
                                    {{ $item->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $item->status === 'sold' ? 'bg-purple-100 text-purple-800' : '' }}
                                    {{ $item->status === 'archived' ? 'bg-gray-100 text-gray-800' : '' }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="text-right text-sm text-gray-500">
                            {{ $item->created_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-center text-gray-500 py-8">No items yet</p>
                @endforelse
            </div>
        </div>

        <!-- Top Sellers -->
        <div class="bg-white rounded-xl shadow">
            <div class="p-6 border-b">
                <h3 class="text-xl font-bold text-gray-900">Top Sellers</h3>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($topSellers as $seller)
                <div class="p-4 hover:bg-gray-50 transition">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                                {{ substr($seller->name, 0, 1) }}
                            </div>
                            <div class="ml-3">
                                <a href="{{ route('admin.analytics.user.details', $seller->id) }}" class="font-semibold text-gray-900 hover:text-blue-600">
                                    {{ $seller->name }}
                                </a>
                                <p class="text-sm text-gray-500">{{ $seller->email }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-gray-900">{{ $seller->sold_count }}</p>
                            <p class="text-xs text-gray-500">items sold</p>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-center text-gray-500 py-8">No sellers yet</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="{{ route('admin.items.list') }}" class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-list text-blue-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">View All Items</h3>
                    <p class="text-sm text-gray-600">Browse and manage items</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.items.list', ['status' => 'active']) }}" class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Active Listings</h3>
                    <p class="text-sm text-gray-600">View live items</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.items.export') }}" class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-download text-purple-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Export Data</h3>
                    <p class="text-sm text-gray-600">Download items report</p>
                </div>
            </div>
        </a>
    </div>
</div>

@if($itemsGrowth && $itemsGrowth->count() > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('itemsGrowthChart');
    
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($itemsGrowth->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('M d'))) !!},
                datasets: [{
                    label: 'New Items',
                    data: {!! json_encode($itemsGrowth->pluck('count')) !!},
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.3,
                    fill: true,
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }
});
</script>
@endif
@endsection
