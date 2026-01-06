<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - Swap</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @stack('styles')
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-900 text-white flex-shrink-0">
            <div class="p-6">
                <h2 class="text-2xl font-bold text-white">Swap Admin</h2>
            </div>
            
            <nav class="mt-6">
    @if(auth('admin')->user()->role === 'super_admin')
    <!-- Super Admin Navigation -->
    <a href="{{ route('admin.dashboard') }}" 
       class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition {{ request()->routeIs('admin.dashboard') ? 'bg-gray-800 text-white border-l-4 border-blue-500' : '' }}">
        <i class="fas fa-home w-5"></i>
        <span class="ml-3">Dashboard</span>
    </a>
    
    <a href="{{ route('admin.analytics.index') }}" 
       class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition {{ request()->routeIs('admin.analytics.*') ? 'bg-gray-800 text-white border-l-4 border-blue-500' : '' }}">
        <i class="fas fa-chart-line w-5"></i>
        <span class="ml-3">Analytics</span>
    </a>
    
    <a href="{{ route('admin.analytics.users') }}" 
       class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition {{ request()->routeIs('admin.analytics.users') ? 'bg-gray-800 text-white border-l-4 border-blue-500' : '' }}">
        <i class="fas fa-users w-5"></i>
        <span class="ml-3">Users</span>
    </a>
    
    <a href="{{ route('admin.analytics.violations') }}" 
       class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition {{ request()->routeIs('admin.analytics.violations') ? 'bg-gray-800 text-white border-l-4 border-blue-500' : '' }}">
        <i class="fas fa-exclamation-triangle w-5"></i>
        <span class="ml-3">Violations</span>
    </a>
    
    <a href="{{ route('admin.items.index') }}" 
       class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition {{ request()->routeIs('admin.items.*') ? 'bg-gray-800 text-white border-l-4 border-blue-500' : '' }}">
        <i class="fas fa-box w-5"></i>
        <span class="ml-3">Items Management</span>
    </a>
    
    <div class="px-6 py-3 text-xs uppercase text-gray-500 font-semibold mt-6">
        PDF Books
    </div>
    
    <a href="{{ route('admin.pdf-books.index') }}" 
       class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition {{ request()->routeIs('admin.pdf-books.*') ? 'bg-gray-800 text-white border-l-4 border-blue-500' : '' }}">
        <i class="fas fa-file-pdf w-5"></i>
        <span class="ml-3">PDF Analytics</span>
    </a>
    @endif
    
    <!-- Available to both manager and super_admin -->
    <a href="{{ route('admin.pdf-manager.index') }}" 
       class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition {{ request()->routeIs('admin.pdf-manager.*') ? 'bg-gray-800 text-white border-l-4 border-blue-500' : '' }}">
        <i class="fas fa-upload w-5"></i>
        <span class="ml-3">Upload PDF Books</span>
    </a>
</nav>

        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col">
            <!-- Top Header -->
            <header class="bg-white shadow-sm">
                <div class="px-6 py-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">
                                @yield('page-title', 'Dashboard')
                            </h1>
                            @if(isset($breadcrumbs))
                            <nav class="text-sm mt-1">
                                <ol class="flex items-center space-x-2 text-gray-600">
                                    @foreach($breadcrumbs as $breadcrumb)
                                        @if($loop->last)
                                            <li class="text-gray-900">{{ $breadcrumb['name'] }}</li>
                                        @else
                                            <li>
                                                <a href="{{ $breadcrumb['url'] }}" class="hover:text-blue-600">{{ $breadcrumb['name'] }}</a>
                                                <span class="mx-2">/</span>
                                            </li>
                                        @endif
                                    @endforeach
                                </ol>
                            </nav>
                            @endif
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            <!-- Notifications -->
                            <button class="relative text-gray-600 hover:text-gray-900">
                                <i class="fas fa-bell text-xl"></i>
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">3</span>
                            </button>
                            
                            <!-- Profile Dropdown -->
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="flex items-center space-x-3 text-gray-700 hover:text-gray-900">
                                    <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                                        {{ substr(Auth::guard('admin')->user()->name, 0, 1) }}
                                    </div>
                                    <div class="text-left hidden md:block">
                                        <p class="text-sm font-semibold">{{ Auth::guard('admin')->user()->name }}</p>
                                        <p class="text-xs text-gray-500">{{ ucfirst(Auth::guard('admin')->user()->role) }}</p>
                                    </div>
                                    <i class="fas fa-chevron-down text-sm"></i>
                                </button>
                                
                                <div x-show="open" 
                                     @click.away="open = false"
                                     x-transition
                                     class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50">
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-user-circle mr-2"></i> Profile
                                    </a>
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-cog mr-2"></i> Settings
                                    </a>
                                    <hr class="my-2">
                                    <form method="POST" action="{{ route('admin.logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto">
                @if(session('success'))
                <div class="mx-6 mt-6">
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                </div>
                @endif

                @if(session('error'))
                <div class="mx-6 mt-6">
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                </div>
                @endif

                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t py-4 px-6">
                <div class="flex justify-between items-center text-sm text-gray-600">
                    <p>&copy; {{ date('Y') }} Swap. All rights reserved.</p>
                    <p>Admin Panel v1.0</p>
                </div>
            </footer>
        </div>
    </div>

    <!-- Alpine.js for dropdown -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('scripts')
</body>
</html>
