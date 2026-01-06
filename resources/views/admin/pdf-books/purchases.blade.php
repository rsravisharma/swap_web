@extends('admin.layouts.app')

@section('title', 'PDF Book Purchases')
@section('page-title', 'PDF Purchases Management')

@section('content')
<div class="p-6">
    <!-- Header with Filters -->
    <div class="bg-white rounded-xl shadow mb-6">
        <div class="p-6 border-b">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold text-gray-900">PDF Book Purchases</h2>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.pdf-purchases.export') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold">
                        <i class="fas fa-download mr-2"></i>Export
                    </a>
                </div>
            </div>

            <!-- Filters Form -->
            <form method="GET" action="{{ route('admin.pdf-books.purchases') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Buyer name or email"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="revoked" {{ request('status') === 'revoked' ? 'selected' : '' }}>Revoked</option>
                    </select>
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
                        <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Purchase Date</option>
                        <option value="purchase_price" {{ request('sort_by') === 'purchase_price' ? 'selected' : '' }}>Price</option>
                        <option value="download_count" {{ request('sort_by') === 'download_count' ? 'selected' : '' }}>Downloads</option>
                    </select>
                </div>

                <!-- Filter Buttons -->
                <div class="flex items-end space-x-2 md:col-span-2 lg:col-span-3">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold">
                        <i class="fas fa-filter mr-2"></i>Filter
                    </button>
                    <a href="{{ route('admin.pdf-books.purchases') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg font-semibold">
                        Clear Filters
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Purchases Table -->
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Purchase ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Book</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Buyer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seller</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Downloads</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($purchases as $purchase)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <span class="font-mono text-sm text-gray-900">#{{ $purchase->id }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                @if($purchase->pdfBook->cover_image_url)
                                <img src="{{ $purchase->pdfBook->cover_image_url }}" 
                                     alt="{{ $purchase->pdfBook->title }}" 
                                     class="w-12 h-16 object-cover rounded mr-3">
                                @else
                                <div class="w-12 h-16 bg-gray-200 rounded mr-3 flex items-center justify-center">
                                    <i class="fas fa-book text-gray-400"></i>
                                </div>
                                @endif
                                <div>
                                    <a href="{{ route('admin.pdf-books.details', $purchase->pdfBook->id) }}" 
                                       class="font-semibold text-gray-900 hover:text-blue-600">
                                        {{ Str::limit($purchase->pdfBook->title, 40) }}
                                    </a>
                                    @if($purchase->pdfBook->author)
                                    <p class="text-xs text-gray-500">{{ $purchase->pdfBook->author }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                    {{ substr($purchase->user->name, 0, 1) }}
                                </div>
                                <div class="ml-2">
                                    <a href="{{ route('admin.analytics.user.details', $purchase->user->id) }}" 
                                       class="font-semibold text-gray-900 hover:text-blue-600">
                                        {{ $purchase->user->name }}
                                    </a>
                                    <p class="text-xs text-gray-500">{{ $purchase->user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-green-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                    {{ substr($purchase->seller->name, 0, 1) }}
                                </div>
                                <div class="ml-2">
                                    <a href="{{ route('admin.analytics.user.details', $purchase->seller->id) }}" 
                                       class="font-semibold text-gray-900 hover:text-blue-600">
                                        {{ $purchase->seller->name }}
                                    </a>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-bold text-green-600">₹{{ number_format($purchase->purchase_price, 2) }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between text-sm mb-1">
                                        <span class="font-semibold text-gray-900">{{ $purchase->download_count }}/{{ $purchase->max_downloads }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full" 
                                             style="width: {{ ($purchase->download_count / $purchase->max_downloads) * 100 }}%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                {{ $purchase->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $purchase->status === 'expired' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $purchase->status === 'revoked' ? 'bg-gray-100 text-gray-800' : '' }}">
                                {{ ucfirst($purchase->status) }}
                            </span>
                            @if($purchase->access_expires_at)
                            <p class="text-xs text-gray-500 mt-1">
                                Expires: {{ $purchase->access_expires_at->format('M d, Y') }}
                            </p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-900">{{ $purchase->created_at->format('M d, Y') }}</p>
                            <p class="text-xs text-gray-500">{{ $purchase->created_at->format('h:i A') }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                @if($purchase->status === 'active')
                                <button onclick="showExtendModal({{ $purchase->id }})" 
                                        class="text-green-600 hover:text-green-800" 
                                        title="Extend Access">
                                    <i class="fas fa-clock"></i>
                                </button>
                                <button onclick="showRevokeModal({{ $purchase->id }})" 
                                        class="text-red-600 hover:text-red-800" 
                                        title="Revoke Access">
                                    <i class="fas fa-ban"></i>
                                </button>
                                @endif
                                <button onclick="showDetailsModal({{ json_encode($purchase) }})" 
                                        class="text-blue-600 hover:text-blue-800" 
                                        title="View Details">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <p class="text-gray-600 font-medium">No purchases found</p>
                            <p class="text-sm text-gray-500 mt-1">Try adjusting your filters</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($purchases->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $purchases->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Extend Access Modal -->
<div id="extendModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Extend Access</h3>
        <p class="text-gray-600 mb-4">How many days do you want to extend the access?</p>
        <form method="POST" id="extendForm">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Number of Days</label>
                <input type="number" 
                       name="days" 
                       min="1" 
                       max="365" 
                       value="30"
                       required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex space-x-3">
                <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold">
                    Extend Access
                </button>
                <button type="button" onclick="closeExtendModal()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg font-semibold">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Revoke Access Modal -->
<div id="revokeModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Revoke Access</h3>
        <p class="text-gray-600 mb-4">Are you sure you want to revoke access to this purchase? The user will no longer be able to download this book.</p>
        <form method="POST" id="revokeForm">
            @csrf
            <div class="flex space-x-3">
                <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold">
                    Revoke Access
                </button>
                <button type="button" onclick="closeRevokeModal()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg font-semibold">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Details Modal -->
<div id="detailsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
    <div class="bg-white rounded-xl p-6 max-w-2xl w-full mx-4 my-8">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-900">Purchase Details</h3>
            <button onclick="closeDetailsModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="detailsContent" class="space-y-4">
            <!-- Content will be populated by JavaScript -->
        </div>
    </div>
</div>

<script>
function showExtendModal(purchaseId) {
    const modal = document.getElementById('extendModal');
    const form = document.getElementById('extendForm');
    form.action = `/admin/pdf-purchases/${purchaseId}/extend`;
    modal.classList.remove('hidden');
}

function closeExtendModal() {
    document.getElementById('extendModal').classList.add('hidden');
}

function showRevokeModal(purchaseId) {
    const modal = document.getElementById('revokeModal');
    const form = document.getElementById('revokeForm');
    form.action = `/admin/pdf-purchases/${purchaseId}/revoke`;
    modal.classList.remove('hidden');
}

function closeRevokeModal() {
    document.getElementById('revokeModal').classList.add('hidden');
}

function showDetailsModal(purchase) {
    const modal = document.getElementById('detailsModal');
    const content = document.getElementById('detailsContent');
    
    const firstDownloaded = purchase.first_downloaded_at 
        ? new Date(purchase.first_downloaded_at).toLocaleString() 
        : 'Never';
    const lastDownloaded = purchase.last_downloaded_at 
        ? new Date(purchase.last_downloaded_at).toLocaleString() 
        : 'Never';
    const accessExpires = purchase.access_expires_at 
        ? new Date(purchase.access_expires_at).toLocaleString() 
        : 'Never';
    
    content.innerHTML = `
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-600">Purchase ID</p>
                <p class="font-semibold text-gray-900">#${purchase.id}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Purchase Date</p>
                <p class="font-semibold text-gray-900">${new Date(purchase.created_at).toLocaleString()}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Purchase Price</p>
                <p class="font-semibold text-green-600">₹${parseFloat(purchase.purchase_price).toFixed(2)}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Status</p>
                <p class="font-semibold text-gray-900">${purchase.status.charAt(0).toUpperCase() + purchase.status.slice(1)}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Downloads</p>
                <p class="font-semibold text-gray-900">${purchase.download_count} / ${purchase.max_downloads}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Remaining Downloads</p>
                <p class="font-semibold text-blue-600">${purchase.max_downloads - purchase.download_count}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">First Downloaded</p>
                <p class="font-semibold text-gray-900">${firstDownloaded}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Last Downloaded</p>
                <p class="font-semibold text-gray-900">${lastDownloaded}</p>
            </div>
            <div class="col-span-2">
                <p class="text-sm text-gray-600">Access Expires</p>
                <p class="font-semibold text-gray-900">${accessExpires}</p>
            </div>
            <div class="col-span-2">
                <p class="text-sm text-gray-600">Download Token</p>
                <p class="font-mono text-xs text-gray-900 bg-gray-100 p-2 rounded break-all">${purchase.download_token}</p>
            </div>
        </div>
    `;
    
    modal.classList.remove('hidden');
}

function closeDetailsModal() {
    document.getElementById('detailsModal').classList.add('hidden');
}
</script>
@endsection
