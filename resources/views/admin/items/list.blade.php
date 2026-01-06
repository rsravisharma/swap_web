@extends('admin.layouts.app')

@section('title', 'Items List')
@section('page-title', 'All Items')

@section('content')
<div class="p-6">
    <!-- Header with Actions -->
    <div class="bg-white rounded-xl shadow mb-6">
        <div class="p-6 border-b">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold text-gray-900">All Items</h2>
                <div class="flex space-x-3">
                    <button onclick="toggleBulkActions()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg font-semibold">
                        <i class="fas fa-tasks mr-2"></i>Bulk Actions
                    </button>
                    <a href="{{ route('admin.items.export') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold">
                        <i class="fas fa-download mr-2"></i>Export
                    </a>
                </div>
            </div>

            <!-- Bulk Actions Bar (Hidden by default) -->
            <div id="bulkActionsBar" class="hidden mb-4 p-4 bg-blue-50 rounded-lg">
                <form method="POST" action="{{ route('admin.items.bulk-action') }}" id="bulkActionForm">
                    @csrf
                    <div class="flex items-center space-x-4">
                        <span class="text-sm font-semibold text-gray-700">
                            <span id="selectedCount">0</span> items selected
                        </span>
                        <select name="action" required class="px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="">Select Action</option>
                            <option value="activate">Activate</option>
                            <option value="deactivate">Deactivate</option>
                            <option value="archive">Archive</option>
                            <option value="delete">Delete</option>
                        </select>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold">
                            Apply
                        </button>
                        <button type="button" onclick="deselectAll()" class="text-gray-600 hover:text-gray-800">
                            Deselect All
                        </button>
                    </div>
                </form>
            </div>

            <!-- Filters Form -->
            <form method="GET" action="{{ route('admin.items.list') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Title or description"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="sold" {{ request('status') === 'sold' ? 'selected' : '' }}>Sold</option>
                        <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="reserved" {{ request('status') === 'reserved' ? 'selected' : '' }}>Reserved</option>
                    </select>
                </div>

                <!-- Category -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="category_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Condition -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Condition</label>
                    <select name="condition" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Conditions</option>
                        <option value="new" {{ request('condition') === 'new' ? 'selected' : '' }}>New</option>
                        <option value="like_new" {{ request('condition') === 'like_new' ? 'selected' : '' }}>Like New</option>
                        <option value="good" {{ request('condition') === 'good' ? 'selected' : '' }}>Good</option>
                        <option value="fair" {{ request('condition') === 'fair' ? 'selected' : '' }}>Fair</option>
                        <option value="poor" {{ request('condition') === 'poor' ? 'selected' : '' }}>Poor</option>
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
                           placeholder="100000"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Promoted -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Promoted</label>
                    <select name="promoted" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Items</option>
                        <option value="yes" {{ request('promoted') === 'yes' ? 'selected' : '' }}>Promoted Only</option>
                    </select>
                </div>

                <!-- Sort By -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                    <select name="sort_by" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Date Listed</option>
                        <option value="price" {{ request('sort_by') === 'price' ? 'selected' : '' }}>Price</option>
                        <option value="title" {{ request('sort_by') === 'title' ? 'selected' : '' }}>Title</option>
                    </select>
                </div>

                <!-- Filter Buttons -->
                <div class="flex items-end space-x-2 md:col-span-2 lg:col-span-4">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold">
                        <i class="fas fa-filter mr-2"></i>Filter
                    </button>
                    <a href="{{ route('admin.items.list') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg font-semibold">
                        Clear Filters
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Items Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($items as $item)
        <div class="bg-white rounded-xl shadow overflow-hidden hover:shadow-lg transition">
            <!-- Item Image -->
            <div class="relative">
                @if($item->primary_image_url)
                <img src="{{ $item->primary_image_url }}" alt="{{ $item->title }}" class="w-full h-48 object-cover">
                @else
                <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                    <i class="fas fa-image text-gray-400 text-4xl"></i>
                </div>
                @endif

                <!-- Checkbox for bulk actions -->
                <div class="absolute top-2 left-2">
                    <input type="checkbox" 
                           name="item_ids[]" 
                           value="{{ $item->id }}" 
                           class="item-checkbox w-5 h-5 rounded border-gray-300"
                           onchange="updateBulkSelection()">
                </div>

                <!-- Status Badge -->
                <div class="absolute top-2 right-2">
                    <span class="px-3 py-1 rounded-full text-xs font-bold
                        {{ $item->status === 'active' ? 'bg-green-500 text-white' : '' }}
                        {{ $item->status === 'sold' ? 'bg-purple-500 text-white' : '' }}
                        {{ $item->status === 'archived' ? 'bg-gray-500 text-white' : '' }}
                        {{ $item->status === 'inactive' ? 'bg-red-500 text-white' : '' }}
                        {{ $item->status === 'reserved' ? 'bg-yellow-500 text-white' : '' }}">
                        {{ ucfirst($item->status) }}
                    </span>
                </div>

                @if($item->is_promoted)
                <div class="absolute bottom-2 left-2">
                    <span class="px-2 py-1 rounded bg-yellow-400 text-yellow-900 text-xs font-bold">
                        <i class="fas fa-star mr-1"></i>Promoted
                    </span>
                </div>
                @endif
            </div>

            <!-- Item Details -->
            <div class="p-4">
                <h3 class="text-lg font-bold text-gray-900 mb-1 truncate">{{ $item->title }}</h3>
                <p class="text-sm text-gray-600 mb-2">{{ $item->category_display }}</p>
                
                <div class="flex items-center justify-between mb-3">
                    <span class="text-2xl font-bold text-blue-600">₹{{ number_format($item->price, 0) }}</span>
                    <span class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-700">
                        {{ ucfirst(str_replace('_', ' ', $item->condition)) }}
                    </span>
                </div>

                <!-- Seller Info -->
                <div class="flex items-center mb-3 pb-3 border-b">
                    <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                        {{ substr($item->user->name, 0, 1) }}
                    </div>
                    <div class="ml-2">
                        <p class="text-sm font-semibold text-gray-900">{{ $item->user->name }}</p>
                        <p class="text-xs text-gray-500">{{ $item->created_at->diffForHumans() }}</p>
                    </div>
                </div>

                <!-- Stats -->
                <div class="flex items-center justify-between text-sm text-gray-600 mb-3">
                    <span><i class="fas fa-eye mr-1"></i>{{ $item->views->count() }} views</span>
                    <span><i class="fas fa-heart mr-1"></i>{{ $item->wishlists->count() }} wishlists</span>
                </div>

                <!-- Actions -->
                <div class="flex space-x-2">
                    <a href="{{ route('admin.items.details', $item->id) }}" 
                       class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold text-center text-sm">
                        View Details
                    </a>
                    <button onclick="showStatusModal({{ $item->id }}, '{{ $item->status }}')"
                            class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg">
                        <i class="fas fa-edit"></i>
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white rounded-xl shadow p-12 text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <p class="text-gray-600 font-medium">No items found</p>
            <p class="text-sm text-gray-500 mt-1">Try adjusting your filters</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($items->hasPages())
    <div class="mt-6">
        {{ $items->appends(request()->query())->links() }}
    </div>
    @endif
</div>

<!-- Status Update Modal -->
<div id="statusModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Update Item Status</h3>
        <form method="POST" id="statusForm">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Status</label>
                <select name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="active">Active</option>
                    <option value="sold">Sold</option>
                    <option value="archived">Archived</option>
                    <option value="inactive">Inactive</option>
                    <option value="reserved">Reserved</option>
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
function toggleBulkActions() {
    const bar = document.getElementById('bulkActionsBar');
    bar.classList.toggle('hidden');
}

function updateBulkSelection() {
    const checkboxes = document.querySelectorAll('.item-checkbox:checked');
    document.getElementById('selectedCount').textContent = checkboxes.length;
    
    if (checkboxes.length > 0) {
        document.getElementById('bulkActionsBar').classList.remove('hidden');
    }
}

function deselectAll() {
    document.querySelectorAll('.item-checkbox').forEach(cb => cb.checked = false);
    updateBulkSelection();
    document.getElementById('bulkActionsBar').classList.add('hidden');
}

// Bulk action form submission
document.getElementById('bulkActionForm').addEventListener('submit', function(e) {
    const checkboxes = document.querySelectorAll('.item-checkbox:checked');
    checkboxes.forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'item_ids[]';
        input.value = cb.value;
        this.appendChild(input);
    });
});

function showStatusModal(itemId, currentStatus) {
    const modal = document.getElementById('statusModal');
    const form = document.getElementById('statusForm');
    form.action = `/admin/items/${itemId}/status`;
    form.querySelector('select[name="status"]').value = currentStatus;
    modal.classList.remove('hidden');
}

function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
}
</script>
@endsection
