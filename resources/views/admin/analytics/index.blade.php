@extends('admin.layouts.app')

@section('title', 'User Analytics Dashboard')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">User Analytics Dashboard</h1>
        <a href="{{ route('admin.analytics.export') }}" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold">
            Export Data
        </a>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Users -->
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Users</p>
                    <h3 class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_users']) }}</h3>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-sm text-green-600 mt-2">+{{ $stats['new_users_today'] }} today</p>
        </div>

        <!-- Active Today -->
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Active Today</p>
                    <h3 class="text-3xl font-bold text-gray-900">{{ number_format($stats['active_users_today']) }}</h3>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-sm text-gray-600 mt-2">{{ round(($stats['active_users_today'] / $stats['total_users']) * 100, 1) }}% of total</p>
        </div>

        <!-- Verified Users -->
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Verified Users</p>
                    <h3 class="text-3xl font-bold text-gray-900">{{ number_format($stats['verified_users']) }}</h3>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
            </div>
            <p class="text-sm text-gray-600 mt-2">{{ $stats['student_verified'] }} students verified</p>
        </div>

        <!-- Pending Violations -->
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Pending Violations</p>
                    <h3 class="text-3xl font-bold text-gray-900">{{ number_format($stats['pending_violations']) }}</h3>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
            <p class="text-sm text-red-600 mt-2">{{ $stats['high_priority_violations'] }} high priority</p>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- User Growth Chart -->
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-4">User Growth (Last 30 Days)</h3>
        
            @if($userGrowth && $userGrowth->count() > 0)
                <div style="position: relative; height: 300px;">
                    <canvas id="userGrowthChart"></canvas>
                </div>
            @else
                <div class="flex items-center justify-center h-64 bg-gray-50 rounded-lg">
                    <div class="text-center">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <p class="text-gray-600 font-medium">No user growth data yet</p>
                        <p class="text-sm text-gray-500 mt-1">Data will appear once users register</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Activity Distribution -->
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-4">New Users This Month</h3>
            <div class="flex items-center justify-center h-64">
                <div class="text-center">
                    <p class="text-6xl font-bold text-blue-600">{{ $stats['new_users_this_month'] ?? 0 }}</p>
                    <p class="text-gray-600 mt-2">New registrations</p>
                    <p class="text-sm text-gray-500 mt-1">{{ $stats['new_users_this_week'] ?? 0 }} this week</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Users Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Top Sellers -->
<div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="p-6 border-b">
        <h3 class="text-xl font-bold text-gray-900">Top Sellers</h3>
    </div>
    
    @if($topSellers->count() > 0)
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sold</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rating</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Earnings</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($topSellers as $seller)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <a href="{{ route('admin.analytics.user.details', $seller->id) }}" class="text-blue-600 hover:text-blue-800">
                            {{ $seller->name }}
                        </a>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $seller->items_sold }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 text-yellow-400 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            {{ number_format($seller->seller_rating, 1) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">₹{{ number_format($seller->total_earnings, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="flex items-center justify-center py-12">
        <div class="text-center">
            <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
            <p class="text-gray-600">No sellers yet</p>
            <p class="text-sm text-gray-500 mt-1">Data will appear after transactions</p>
        </div>
    </div>
    @endif
</div>

<!-- Recent Activities -->
<div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="p-6 border-b">
        <h3 class="text-xl font-bold text-gray-900">Recent Activities</h3>
    </div>
    
    @if($recentActivities->count() > 0)
    <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
        @foreach($recentActivities->take(10) as $activity)
        <div class="p-4 hover:bg-gray-50">
            <div class="flex items-start">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900">
                        {{ $activity->user->name }}
                    </p>
                    <p class="text-sm text-gray-600">{{ $activity->action }} - {{ $activity->description }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $activity->created_at->diffForHumans() }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="flex items-center justify-center py-12">
        <div class="text-center">
            <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-gray-600">No activities yet</p>
            <p class="text-sm text-gray-500 mt-1">User activities will appear here</p>
        </div>
    </div>
    @endif
</div>

    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="{{ route('admin.analytics.users') }}" class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">View All Users</h3>
            <p class="text-sm text-gray-600">Browse and filter all users</p>
        </a>

        <a href="{{ route('admin.analytics.violations') }}" class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Review Violations</h3>
            <p class="text-sm text-gray-600">Manage user reports and violations</p>
        </a>

        <a href="{{ route('admin.analytics.export') }}" class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Export Reports</h3>
            <p class="text-sm text-gray-600">Download user data and analytics</p>
        </a>
    </div>
</div>

@if($userGrowth && $userGrowth->count() > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('userGrowthChart');
    
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($userGrowth->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('M d'))) !!},
                datasets: [{
                    label: 'New Users',
                    data: {!! json_encode($userGrowth->pluck('count')) !!},
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.3,
                    fill: true,
                    borderWidth: 2,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: 'rgb(59, 130, 246)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 13
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            callback: function(value) {
                                return Number.isInteger(value) ? value : null;
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    }
                }
            }
        });
    }
});
</script>
@else
<!-- Chart.js not needed when no data -->
@endif
@endsection
