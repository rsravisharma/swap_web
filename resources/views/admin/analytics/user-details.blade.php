@extends('admin.layouts.app')

@section('title', 'User Details - ' . $user->name)
@section('page-title', 'User Details')

@section('content')
<div class="p-6">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('admin.analytics.users') }}" class="text-blue-600 hover:text-blue-800 font-semibold">
            <i class="fas fa-arrow-left mr-2"></i>Back to Users
        </a>
    </div>

    <!-- User Profile Card -->
    <div class="bg-white rounded-xl shadow mb-6">
        <div class="p-6">
            <div class="flex items-start justify-between">
                <div class="flex items-center">
                    <div class="w-20 h-20 bg-blue-600 rounded-full flex items-center justify-center text-white text-3xl font-bold">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    <div class="ml-6">
                        <h2 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h2>
                        <p class="text-gray-600">{{ $user->email }}</p>
                        @if($user->phone)
                        <p class="text-gray-600">{{ $user->phone }}</p>
                        @endif
                        <div class="flex items-center space-x-2 mt-2">
                            @if($user->is_active)
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                            @else
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Inactive</span>
                            @endif
                            
                            @if($user->email_verified_at)
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Email Verified</span>
                            @endif
                            
                            @if($user->student_verified)
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Student Verified</span>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="flex space-x-3">
                    @if($user->is_blocked)
                    <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold">
                        <i class="fas fa-unlock mr-2"></i>Unblock
                    </button>
                    @else
                    <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold">
                        <i class="fas fa-ban mr-2"></i>Block User
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Items Sold</p>
                    <h3 class="text-3xl font-bold text-gray-900">{{ $user->items_sold }}</h3>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-box text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Seller Rating</p>
                    <h3 class="text-3xl font-bold text-gray-900">{{ number_format($user->seller_rating, 1) }}</h3>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-star text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Earnings</p>
                    <h3 class="text-3xl font-bold text-gray-900">₹{{ number_format($user->total_earnings, 0) }}</h3>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-rupee-sign text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Coins Balance</p>
                    <h3 class="text-3xl font-bold text-gray-900">{{ $user->coins }}</h3>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-coins text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- User Information -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow">
            <div class="p-6 border-b">
                <h3 class="text-xl font-bold text-gray-900">User Information</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">University</p>
                        <p class="font-semibold text-gray-900">{{ $user->university ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Course</p>
                        <p class="font-semibold text-gray-900">{{ $user->course ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Semester</p>
                        <p class="font-semibold text-gray-900">{{ $user->semester ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-1">City</p>
                        <p class="font-semibold text-gray-900">{{ $user->city ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-1">State</p>
                        <p class="font-semibold text-gray-900">{{ $user->state ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Joined</p>
                        <p class="font-semibold text-gray-900">{{ $user->created_at->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Last Active</p>
                        <p class="font-semibold text-gray-900">{{ $user->last_active_at ? $user->last_active_at->diffForHumans() : 'Never' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Login Streak</p>
                        <p class="font-semibold text-gray-900">{{ $user->login_streak_days }} days</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Summary -->
        <div class="bg-white rounded-xl shadow">
            <div class="p-6 border-b">
                <h3 class="text-xl font-bold text-gray-900">Activity Summary</h3>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Logins</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $activitySummary['total_logins'] ?? 0 }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Activities</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $activitySummary['total_activities'] ?? 0 }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Last 30 Days</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $activitySummary['last_30_days_activities'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Section -->
    <div class="bg-white rounded-xl shadow" x-data="{ tab: 'activities' }">
        <div class="border-b">
            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                <button @click="tab = 'activities'" 
                        :class="tab === 'activities' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-4 px-1 border-b-2 font-medium text-sm">
                    Recent Activities
                </button>
                <button @click="tab = 'coins'" 
                        :class="tab === 'coins' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-4 px-1 border-b-2 font-medium text-sm">
                    Coin History
                </button>
                <button @click="tab = 'violations'" 
                        :class="tab === 'violations' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-4 px-1 border-b-2 font-medium text-sm">
                    Violations ({{ $violations->count() }})
                </button>
            </nav>
        </div>

        <!-- Activities Tab -->
        <div x-show="tab === 'activities'" class="p-6">
            <div class="space-y-4">
                @forelse($recentActivities as $activity)
                <div class="flex items-start p-4 bg-gray-50 rounded-lg">
                    <div class="flex-1">
                        <p class="font-semibold text-gray-900">{{ $activity->action }}</p>
                        <p class="text-sm text-gray-600">{{ $activity->description }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $activity->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                </div>
                @empty
                <p class="text-center text-gray-500 py-8">No activities found</p>
                @endforelse
            </div>
        </div>

        <!-- Coins Tab -->
        <div x-show="tab === 'coins'" class="p-6">
            <div class="space-y-4">
                @forelse($coinHistory as $transaction)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-semibold text-gray-900">{{ $transaction->type }}</p>
                        <p class="text-xs text-gray-500">{{ $transaction->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold {{ $transaction->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $transaction->amount > 0 ? '+' : '' }}{{ $transaction->amount }}
                        </p>
                        <p class="text-xs text-gray-500">Balance: {{ $transaction->balance_after }}</p>
                    </div>
                </div>
                @empty
                <p class="text-center text-gray-500 py-8">No coin transactions</p>
                @endforelse
            </div>
        </div>

        <!-- Violations Tab -->
        <div x-show="tab === 'violations'" class="p-6">
            <div class="space-y-4">
                @forelse($violations as $violation)
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <span class="px-2 py-1 rounded text-xs font-medium 
                                {{ $violation->severity === 'critical' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $violation->severity === 'high' ? 'bg-orange-100 text-orange-800' : '' }}
                                {{ $violation->severity === 'medium' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $violation->severity === 'low' ? 'bg-blue-100 text-blue-800' : '' }}">
                                {{ ucfirst($violation->severity) }}
                            </span>
                            <span class="ml-2 px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                {{ ucfirst(str_replace('_', ' ', $violation->violation_type)) }}
                            </span>
                        </div>
                        <span class="px-2 py-1 rounded text-xs font-medium 
                            {{ $violation->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $violation->status === 'reviewed' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $violation->status === 'action_taken' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $violation->status === 'dismissed' ? 'bg-gray-100 text-gray-800' : '' }}">
                            {{ ucfirst(str_replace('_', ' ', $violation->status)) }}
                        </span>
                    </div>
                    <p class="text-gray-700 mb-2">{{ $violation->description }}</p>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span>Reported by: {{ $violation->reporter->name ?? 'System' }}</span>
                        <span>{{ $violation->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
                @empty
                <p class="text-center text-gray-500 py-8">No violations</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endsection
