@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="p-6">
    <!-- Welcome Card -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl shadow-lg p-8 mb-8 text-white">
        <h2 class="text-3xl font-bold mb-2">Welcome back, {{ Auth::guard('admin')->user()->name }}! 👋</h2>
        <p class="text-blue-100">Here's what's happening with your platform today.</p>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Users -->
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
                <span class="text-xs text-green-600 font-semibold">+12.5%</span>
            </div>
            <h3 class="text-gray-600 text-sm font-medium">Total Users</h3>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ \App\Models\User::count() }}</p>
        </div>

        <!-- Active Users Today -->
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-check text-green-600 text-xl"></i>
                </div>
                <span class="text-xs text-green-600 font-semibold">+8.3%</span>
            </div>
            <h3 class="text-gray-600 text-sm font-medium">Active Today</h3>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ \App\Models\User::whereDate('last_active_at', today())->count() }}</p>
        </div>

        <!-- Total Items -->
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-box text-purple-600 text-xl"></i>
                </div>
                <span class="text-xs text-green-600 font-semibold">+15.2%</span>
            </div>
            <h3 class="text-gray-600 text-sm font-medium">Total Items</h3>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ \App\Models\Item::count() }}</p>
        </div>

        <!-- Pending Reports -->
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <span class="text-xs text-red-600 font-semibold">Urgent</span>
            </div>
            <h3 class="text-gray-600 text-sm font-medium">Pending Violations</h3>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ \App\Models\UserViolation::where('status', 'pending')->count() }}</p>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Recent Users -->
        <div class="bg-white rounded-xl shadow">
            <div class="p-6 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900">Recent Users</h3>
                    <a href="{{ route('admin.analytics.users') }}" class="text-sm text-blue-600 hover:text-blue-800">View All →</a>
                </div>
            </div>
            <div class="divide-y divide-gray-200">
                @foreach(\App\Models\User::orderBy('created_at', 'desc')->limit(5)->get() as $user)
                <div class="p-4 hover:bg-gray-50 transition">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="text-sm font-semibold text-gray-900">{{ $user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $user->email }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-500">{{ $user->created_at->diffForHumans() }}</p>
                            @if($user->email_verified_at)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                Verified
                            </span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                Pending
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-900">Quick Actions</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-4">
                    <a href="{{ route('admin.analytics.users') }}" class="flex flex-col items-center justify-center p-6 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                        <i class="fas fa-users text-blue-600 text-3xl mb-3"></i>
                        <span class="text-sm font-semibold text-gray-900">View Users</span>
                    </a>
                    
                    <a href="{{ route('admin.analytics.violations') }}" class="flex flex-col items-center justify-center p-6 bg-red-50 rounded-lg hover:bg-red-100 transition">
                        <i class="fas fa-flag text-red-600 text-3xl mb-3"></i>
                        <span class="text-sm font-semibold text-gray-900">Violations</span>
                    </a>
                    
                    <a href="{{ route('admin.analytics.index') }}" class="flex flex-col items-center justify-center p-6 bg-green-50 rounded-lg hover:bg-green-100 transition">
                        <i class="fas fa-chart-bar text-green-600 text-3xl mb-3"></i>
                        <span class="text-sm font-semibold text-gray-900">Analytics</span>
                    </a>
                    
                    <a href="{{ route('admin.analytics.export') }}" class="flex flex-col items-center justify-center p-6 bg-purple-50 rounded-lg hover:bg-purple-100 transition">
                        <i class="fas fa-download text-purple-600 text-3xl mb-3"></i>
                        <span class="text-sm font-semibold text-gray-900">Export Data</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- System Status -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Storage -->
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-900">Storage Usage</h3>
                <i class="fas fa-database text-gray-400"></i>
            </div>
            <div class="mb-2">
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-600">Used</span>
                    <span class="font-semibold text-gray-900">2.4 GB / 10 GB</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: 24%"></div>
                </div>
            </div>
        </div>

        <!-- Server Status -->
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-900">Server Status</h3>
                <i class="fas fa-server text-gray-400"></i>
            </div>
            <div class="flex items-center">
                <div class="w-3 h-3 bg-green-500 rounded-full mr-2 animate-pulse"></div>
                <span class="text-sm text-gray-600">All systems operational</span>
            </div>
        </div>

        <!-- Database -->
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-900">Database</h3>
                <i class="fas fa-database text-gray-400"></i>
            </div>
            <div class="flex items-center">
                <div class="w-3 h-3 bg-green-500 rounded-full mr-2 animate-pulse"></div>
                <span class="text-sm text-gray-600">Connected</span>
            </div>
        </div>
    </div>
</div>
@endsection
