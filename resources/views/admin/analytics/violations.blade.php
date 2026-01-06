@extends('admin.layouts.app')

@section('title', 'Violations Management')
@section('page-title', 'Violations & Reports')

@section('content')
<div class="p-6">
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-600 mb-1">Total Violations</p>
            <h3 class="text-3xl font-bold text-gray-900">{{ $stats['total'] }}</h3>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-600 mb-1">Pending</p>
            <h3 class="text-3xl font-bold text-yellow-600">{{ $stats['pending'] }}</h3>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-600 mb-1">High Priority</p>
            <h3 class="text-3xl font-bold text-orange-600">{{ $stats['high_priority'] }}</h3>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-600 mb-1">Critical</p>
            <h3 class="text-3xl font-bold text-red-600">{{ $stats['critical'] }}</h3>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow mb-6">
        <div class="p-6">
            <form method="GET" action="{{ route('admin.analytics.violations') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="reviewed" {{ request('status') === 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                        <option value="action_taken" {{ request('status') === 'action_taken' ? 'selected' : '' }}>Action Taken</option>
                        <option value="dismissed" {{ request('status') === 'dismissed' ? 'selected' : '' }}>Dismissed</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Severity</label>
                    <select name="severity" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <option value="">All Severity</option>
                        <option value="low" {{ request('severity') === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ request('severity') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ request('severity') === 'high' ? 'selected' : '' }}>High</option>
                        <option value="critical" {{ request('severity') === 'critical' ? 'selected' : '' }}>Critical</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <option value="">All Types</option>
                        <option value="spam" {{ request('type') === 'spam' ? 'selected' : '' }}>Spam</option>
                        <option value="fake_listing" {{ request('type') === 'fake_listing' ? 'selected' : '' }}>Fake Listing</option>
                        <option value="inappropriate_content" {{ request('type') === 'inappropriate_content' ? 'selected' : '' }}>Inappropriate Content</option>
                        <option value="fraud" {{ request('type') === 'fraud' ? 'selected' : '' }}>Fraud</option>
                        <option value="harassment" {{ request('type') === 'harassment' ? 'selected' : '' }}>Harassment</option>
                        <option value="fake_profile" {{ request('type') === 'fake_profile' ? 'selected' : '' }}>Fake Profile</option>
                        <option value="other" {{ request('type') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div class="flex items-end space-x-2">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold">
                        Filter
                    </button>
                    <a href="{{ route('admin.analytics.violations') }}" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg font-semibold text-center">
                        Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Violations List -->
    <div class="space-y-4">
        @forelse($violations as $violation)
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <div class="flex items-center space-x-2 mb-2">
                        <span class="px-3 py-1 rounded text-sm font-medium 
                            {{ $violation->severity === 'critical' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $violation->severity === 'high' ? 'bg-orange-100 text-orange-800' : '' }}
                            {{ $violation->severity === 'medium' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $violation->severity === 'low' ? 'bg-blue-100 text-blue-800' : '' }}">
                            {{ ucfirst($violation->severity) }} Priority
                        </span>
                        <span class="px-3 py-1 rounded text-sm font-medium bg-gray-100 text-gray-800">
                            {{ ucfirst(str_replace('_', ' ', $violation->violation_type)) }}
                        </span>
                        <span class="px-3 py-1 rounded text-sm font-medium 
                            {{ $violation->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $violation->status === 'reviewed' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $violation->status === 'action_taken' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $violation->status === 'dismissed' ? 'bg-gray-100 text-gray-800' : '' }}">
                            {{ ucfirst(str_replace('_', ' ', $violation->status)) }}
                        </span>
                    </div>
                    
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        Violation against: 
                        <a href="{{ route('admin.analytics.user.details', $violation->user_id) }}" class="text-blue-600 hover:text-blue-800">
                            {{ $violation->user->name }}
                        </a>
                    </h3>
                    
                    <p class="text-gray-700 mb-3">{{ $violation->description }}</p>
                    
                    <div class="grid grid-cols-3 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500">Reported By</p>
                            <p class="font-semibold text-gray-900">{{ $violation->reporter->name ?? 'System' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Reported On</p>
                            <p class="font-semibold text-gray-900">{{ $violation->created_at->format('M d, Y') }}</p>
                        </div>
                        @if($violation->admin)
                        <div>
                            <p class="text-gray-500">Reviewed By</p>
                            <p class="font-semibold text-gray-900">{{ $violation->admin->name }}</p>
                        </div>
                        @endif
                    </div>

                    @if($violation->admin_notes)
                    <div class="mt-3 p-3 bg-blue-50 rounded-lg">
                        <p class="text-sm font-semibold text-gray-900 mb-1">Admin Notes:</p>
                        <p class="text-sm text-gray-700">{{ $violation->admin_notes }}</p>
                    </div>
                    @endif
                </div>

                @if($violation->status === 'pending')
                <div class="flex flex-col space-y-2 ml-4">
                    <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold text-sm">
                        Review
                    </button>
                    <button class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg font-semibold text-sm">
                        Dismiss
                    </button>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl shadow p-12 text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-gray-600 font-medium">No violations found</p>
            <p class="text-sm text-gray-500 mt-1">All clear! No violations match your filters</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($violations->hasPages())
    <div class="mt-6">
        {{ $violations->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection
