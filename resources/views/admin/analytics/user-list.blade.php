@extends('admin.layouts.app')

@section('title', 'User List')
@section('page-title', 'User Management')

@section('content')
<div class="p-6">
    <!-- Header with Filters -->
    <div class="bg-white rounded-xl shadow mb-6">
        <div class="p-6 border-b">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold text-gray-900">All Users</h2>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.analytics.export') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold">
                        <i class="fas fa-download mr-2"></i>Export
                    </a>
                </div>
            </div>

            <!-- Filters Form -->
            <form method="GET" action="{{ route('admin.analytics.users') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Name, email, or phone"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Verified -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Verified</label>
                    <select name="verified" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All</option>
                        <option value="verified" {{ request('verified') === 'verified' ? 'selected' : '' }}>Verified</option>
                        <option value="unverified" {{ request('verified') === 'unverified' ? 'selected' : '' }}>Unverified</option>
                    </select>
                </div>

                <!-- Student Verified -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Student Verified</label>
                    <select name="student_verified" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All</option>
                        <option value="yes" {{ request('student_verified') === 'yes' ? 'selected' : '' }}>Yes</option>
                        <option value="no" {{ request('student_verified') === 'no' ? 'selected' : '' }}>No</option>
                    </select>
                </div>

                <!-- University -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">University</label>
                    <input type="text" 
                           name="university" 
                           value="{{ request('university') }}"
                           placeholder="University name"
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
                        <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Join Date</option>
                        <option value="name" {{ request('sort_by') === 'name' ? 'selected' : '' }}>Name</option>
                        <option value="last_active_at" {{ request('sort_by') === 'last_active_at' ? 'selected' : '' }}>Last Active</option>
                        <option value="items_sold" {{ request('sort_by') === 'items_sold' ? 'selected' : '' }}>Items Sold</option>
                        <option value="seller_rating" {{ request('sort_by') === 'seller_rating' ? 'selected' : '' }}>Rating</option>
                    </select>
                </div>

                <!-- Filter Buttons -->
                <div class="flex items-end space-x-2">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold">
                        <i class="fas fa-filter mr-2"></i>Filter
                    </button>
                    <a href="{{ route('admin.analytics.users') }}" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg font-semibold text-center">
                        Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">University</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stats</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold flex-shrink-0">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-semibold text-gray-900">{{ $user->name }}</p>
                                    @if($user->student_verified)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>Student
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-900">{{ $user->email }}</p>
                            @if($user->phone)
                            <p class="text-xs text-gray-500">{{ $user->phone }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-900">{{ $user->university ?? 'N/A' }}</p>
                            @if($user->course)
                            <p class="text-xs text-gray-500">{{ $user->course }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($user->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <span class="w-2 h-2 bg-green-400 rounded-full mr-1"></span>Active
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <span class="w-2 h-2 bg-red-400 rounded-full mr-1"></span>Inactive
                            </span>
                            @endif
                            @if($user->is_blocked)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 mt-1">
                                Blocked
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm">
                                <p class="text-gray-900">Sold: <span class="font-semibold">{{ $user->items_sold }}</span></p>
                                <p class="text-gray-500">Rating: <span class="text-yellow-600">★ {{ number_format($user->seller_rating, 1) }}</span></p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-900">{{ $user->created_at->format('M d, Y') }}</p>
                            <p class="text-xs text-gray-500">{{ $user->created_at->diffForHumans() }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.analytics.user.details', $user->id) }}" 
                               class="text-blue-600 hover:text-blue-800 font-semibold text-sm">
                                View Details →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <p class="text-gray-600 font-medium">No users found</p>
                            <p class="text-sm text-gray-500 mt-1">Try adjusting your filters</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $users->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
