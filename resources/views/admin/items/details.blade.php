@extends('admin.layouts.app')

@section('title', 'Item Details - ' . $item->title)
@section('page-title', 'Item Details')

@section('content')
<div class="p-6">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('admin.items.list') }}" class="text-blue-600 hover:text-blue-800 font-semibold">
            <i class="fas fa-arrow-left mr-2"></i>Back to Items
        </a>
    </div>

    <!-- Item Header -->
    <div class="bg-white rounded-xl shadow mb-6">
        <div class="p-6">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-2">
                        <h1 class="text-3xl font-bold text-gray-900">{{ $item->title }}</h1>
                        @if($item->is_promoted)
                        <span class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-800 text-sm font-semibold">
                            <i class="fas fa-star mr-1"></i>Promoted
                        </span>
                        @endif
                    </div>
                    
                    <p class="text-gray-600 mb-4">{{ $item->category_path }}</p>
                    
                    <div class="flex items-center space-x-4">
                        <span class="text-4xl font-bold text-blue-600">₹{{ number_format($item->price, 2) }}</span>
                        <span class="px-4 py-2 rounded-lg text-sm font-semibold
                            {{ $item->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $item->status === 'sold' ? 'bg-purple-100 text-purple-800' : '' }}
                            {{ $item->status === 'archived' ? 'bg-gray-100 text-gray-800' : '' }}
                            {{ $item->status === 'inactive' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $item->status === 'reserved' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                            {{ ucfirst($item->status) }}
                        </span>
                        <span class="px-4 py-2 rounded-lg text-sm font-semibold bg-gray-100 text-gray-800">
                            {{ ucfirst(str_replace('_', ' ', $item->condition)) }}
                        </span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col space-y-2">
                    <button onclick="showStatusModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold">
                        <i class="fas fa-edit mr-2"></i>Change Status
                    </button>
                    <form method="POST" action="{{ route('admin.items.destroy', $item->id) }}" onsubmit="return confirm('Are you sure you want to delete this item?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-semibold">
                            <i class="fas fa-trash mr-2"></i>Delete Item
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
                    <p class="text-sm text-gray-600 mb-1">Total Views</p>
                    <h3 class="text-3xl font-bold text-gray-900">{{ $viewStats['total_views'] }}</h3>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-eye text-blue-600 text-xl"></i>
                </div>
            </div>
            <p class="text-sm text-gray-600 mt-2">{{ $viewStats['unique_views'] }} unique</p>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Wishlists</p>
                    <h3 class="text-3xl font-bold text-red-600">{{ $wishlistStats['total_wishlists'] }}</h3>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-heart text-red-600 text-xl"></i>
                </div>
            </div>
            <p class="text-sm text-gray-600 mt-2">{{ $wishlistStats['wishlists_today'] }} today</p>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Views Today</p>
                    <h3 class="text-3xl font-bold text-green-600">{{ $viewStats['views_today'] }}</h3>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-green-600 text-xl"></i>
                </div>
            </div>
            <p class="text-sm text-gray-600 mt-2">{{ $viewStats['views_this_week'] }} this week</p>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Listed</p>
                    <h3 class="text-lg font-bold text-gray-900">{{ $item->created_at->format('M d, Y') }}</h3>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-calendar text-purple-600 text-xl"></i>
                </div>
            </div>
            <p class="text-sm text-gray-600 mt-2">{{ $item->created_at->diffForHumans() }}</p>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Item Images -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow">
            <div class="p-6 border-b">
                <h3 class="text-xl font-bold text-gray-900">Item Images</h3>
            </div>
            <div class="p-6">
                @if($item->images->count() > 0)
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($item->images as $image)
                    <div class="relative group">
                        <img src="{{ asset('storage/' . $image->image_path) }}" 
                             alt="Item image" 
                             class="w-full h-48 object-cover rounded-lg">
                        @if($image->is_primary)
                        <div class="absolute top-2 left-2">
                            <span class="px-2 py-1 rounded bg-blue-500 text-white text-xs font-bold">
                                Primary
                            </span>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <div class="flex items-center justify-center h-48 bg-gray-50 rounded-lg">
                    <div class="text-center">
                        <i class="fas fa-image text-gray-400 text-4xl mb-2"></i>
                        <p class="text-gray-600">No images uploaded</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Seller Information -->
        <div class="bg-white rounded-xl shadow">
            <div class="p-6 border-b">
                <h3 class="text-xl font-bold text-gray-900">Seller Information</h3>
            </div>
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center text-white text-2xl font-bold">
                        {{ substr($item->user->name, 0, 1) }}
                    </div>
                    <div class="ml-4">
                        <a href="{{ route('admin.analytics.user.details', $item->user->id) }}" 
                           class="text-lg font-bold text-blue-600 hover:text-blue-800">
                            {{ $item->user->name }}
                        </a>
                        <p class="text-sm text-gray-600">{{ $item->user->email }}</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-600">Phone</p>
                        <p class="font-semibold text-gray-900">{{ $item->user->phone ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Seller Rating</p>
                        <p class="font-semibold text-gray-900">
                            <i class="fas fa-star text-yellow-400"></i> {{ number_format($item->user->seller_rating, 1) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Items Sold</p>
                        <p class="font-semibold text-gray-900">{{ $item->user->items_sold }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Member Since</p>
                        <p class="font-semibold text-gray-900">{{ $item->user->created_at->format('M Y') }}</p>
                    </div>
                </div>

                <a href="{{ route('admin.analytics.user.details', $item->user->id) }}" 
                   class="block mt-6 bg-blue-600 hover:bg-blue-700 text-white text-center px-4 py-2 rounded-lg font-semibold">
                    View Seller Profile
                </a>
            </div>
        </div>
    </div>

    <!-- Item Details -->
    <div class="bg-white rounded-xl shadow mb-6">
        <div class="p-6 border-b">
            <h3 class="text-xl font-bold text-gray-900">Item Details</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Location</p>
                    <p class="font-semibold text-gray-900">{{ $item->location_display }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Contact Method</p>
                    <p class="font-semibold text-gray-900">{{ ucfirst($item->contact_method) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Listed On</p>
                    <p class="font-semibold text-gray-900">{{ $item->created_at->format('F d, Y h:i A') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Last Updated</p>
                    <p class="font-semibold text-gray-900">{{ $item->updated_at->format('F d, Y h:i A') }}</p>
                </div>
                @if($item->sold_at)
                <div>
                    <p class="text-sm text-gray-600 mb-1">Sold On</p>
                    <p class="font-semibold text-gray-900">{{ $item->sold_at->format('F d, Y h:i A') }}</p>
                </div>
                @endif
                @if($item->tags && count($item->tags) > 0)
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-600 mb-2">Tags</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($item->tags as $tag)
                        <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-800 text-sm">
                            {{ $tag }}
                        </span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <div>
                <p class="text-sm text-gray-600 mb-2">Description</p>
                <div class="prose max-w-none">
                    <p class="text-gray-900 whitespace-pre-wrap">{{ $item->description }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Similar Items -->
    @if($similarItems->count() > 0)
    <div class="bg-white rounded-xl shadow">
        <div class="p-6 border-b">
            <h3 class="text-xl font-bold text-gray-900">Similar Items</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                @foreach($similarItems as $similar)
                <a href="{{ route('admin.items.details', $similar->id) }}" class="group">
                    <div class="border rounded-lg overflow-hidden hover:shadow-lg transition">
                        @if($similar->primary_image_url)
                        <img src="{{ $similar->primary_image_url }}" alt="{{ $similar->title }}" class="w-full h-32 object-cover">
                        @else
                        <div class="w-full h-32 bg-gray-200 flex items-center justify-center">
                            <i class="fas fa-image text-gray-400"></i>
                        </div>
                        @endif
                        <div class="p-3">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $similar->title }}</p>
                            <p class="text-lg font-bold text-blue-600">₹{{ number_format($similar->price, 0) }}</p>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Status Update Modal -->
<div id="statusModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Update Item Status</h3>
        <form method="POST" action="{{ route('admin.items.update-status', $item->id) }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Status</label>
                <select name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="active" {{ $item->status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="sold" {{ $item->status === 'sold' ? 'selected' : '' }}>Sold</option>
                    <option value="archived" {{ $item->status === 'archived' ? 'selected' : '' }}>Archived</option>
                    <option value="inactive" {{ $item->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="reserved" {{ $item->status === 'reserved' ? 'selected' : '' }}>Reserved</option>
                </select>
            </div>
            <div class="flex space-x-3">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold">
                    Update Status
                </button>
                <button type="button" onclick="closeStatusModal()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg font-semibold">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showStatusModal() {
    document.getElementById('statusModal').classList.remove('hidden');
}

function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
}
</script>
@endsection
