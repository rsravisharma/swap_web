<header class="bg-white shadow-sm sticky top-0 z-50" x-data="{ mobileMenuOpen: false, legalMenuOpen: false, userMenuOpen: false }">
    <nav class="container mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
            <!-- Logo -->
            <a href="{{ route('home') }}" class="flex items-center space-x-2">
                <img src="{{ asset('img/app_icon.png') }}" alt="Swap Logo" class="h-10 w-10">
                <span class="text-2xl font-bold text-gray-900">Swap</span>
            </a>

            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="{{ route('home') }}" class="text-gray-700 hover:text-primary-600 font-medium transition {{ request()->routeIs('home') ? 'text-primary-600' : '' }}">
                    Home
                </a>
                <a href="{{ route('pdf-books.index') }}" class="text-gray-700 hover:text-primary-600 font-medium transition {{ request()->routeIs('pdf-books.*') ? 'text-primary-600' : '' }}">
                    PDF Books
                </a>
                <a href="{{ route('features') }}" class="text-gray-700 hover:text-primary-600 font-medium transition {{ request()->routeIs('features') ? 'text-primary-600' : '' }}">
                    Features
                </a>
                <a href="{{ route('how-it-works') }}" class="text-gray-700 hover:text-primary-600 font-medium transition {{ request()->routeIs('how-it-works') ? 'text-primary-600' : '' }}">
                    How It Works
                </a>
                <a href="{{ route('blog.index') }}" class="text-gray-700 hover:text-primary-600 font-medium transition {{ request()->routeIs('blog.*') ? 'text-primary-600' : '' }}">
                    Blog
                </a>
                <a href="{{ route('about') }}" class="text-gray-700 hover:text-primary-600 font-medium transition {{ request()->routeIs('about') ? 'text-primary-600' : '' }}">
                    About
                </a>

                <!-- Legal/Support Dropdown -->
                <div class="relative" @mouseenter="legalMenuOpen = true" @mouseleave="legalMenuOpen = false">
                    <button class="text-gray-700 hover:text-primary-600 font-medium transition flex items-center {{ request()->routeIs('privacy') || request()->routeIs('deletion.*') ? 'text-primary-600' : '' }}">
                        Legal
                        <svg class="w-4 h-4 ml-1 transform transition-transform" :class="{ 'rotate-180': legalMenuOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="legalMenuOpen"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform scale-95"
                        x-transition:enter-end="opacity-100 transform scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 transform scale-100"
                        x-transition:leave-end="opacity-0 transform scale-95"
                        class="absolute top-full left-0 mt-2 w-56 bg-white rounded-lg shadow-lg py-2 border border-gray-100"
                        style="display: none;">
                        <a href="{{ route('privacy-policy') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-primary-600 transition">
                            Privacy Policy
                        </a>
                        <a href="{{ route('termsAndConditions') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-primary-600 transition">
                            Terms of Service
                        </a>
                        <div class="border-t border-gray-100 my-2"></div>
                        <a href="{{ route('deletion.request') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-primary-600 transition">
                            Delete My Data
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right Side Actions -->
            <div class="hidden md:flex items-center space-x-4">
                @auth
                <!-- User Dropdown -->
                <div class="relative" @mouseenter="userMenuOpen = true" @mouseleave="userMenuOpen = false">
                    <button class="flex items-center space-x-2 text-gray-700 hover:text-primary-600 transition">
                        <div class="w-9 h-9 rounded-full bg-primary-600 flex items-center justify-center text-white font-semibold">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <svg class="w-4 h-4 transform transition-transform" :class="{ 'rotate-180': userMenuOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- User Dropdown Menu -->
                    <div x-show="userMenuOpen"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform scale-95"
                        x-transition:enter-end="opacity-100 transform scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 transform scale-100"
                        x-transition:leave-end="opacity-0 transform scale-95"
                        class="absolute top-full right-0 mt-2 w-56 bg-white rounded-lg shadow-lg py-2 border border-gray-100"
                        style="display: none;">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <p class="text-sm font-semibold text-gray-900">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                        </div>
                        <a href="{{ route('pdf-books.my-library') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-primary-600 transition">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                                My Library
                            </span>
                        </a>
                        <div class="border-t border-gray-100 my-2"></div>
                        <form action="{{ route('user.logout') }}" method="POST" class="block">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                    Logout
                                </span>
                            </button>
                        </form>
                    </div>
                </div>
                @else
                <!-- Login/Signup Dropdown -->
                <div class="relative" @mouseenter="userMenuOpen = true" @mouseleave="userMenuOpen = false">
                    <button class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-2.5 rounded-lg font-semibold transition shadow-md hover:shadow-lg flex items-center">
                        Sign In
                        <svg class="w-4 h-4 ml-1 transform transition-transform" :class="{ 'rotate-180': userMenuOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Auth Dropdown Menu -->
                    <div x-show="userMenuOpen"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform scale-95"
                        x-transition:enter-end="opacity-100 transform scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 transform scale-100"
                        x-transition:leave-end="opacity-0 transform scale-95"
                        class="absolute top-full right-0 mt-2 w-64 bg-white rounded-lg shadow-lg py-2 border border-gray-100"
                        style="display: none;">
                        <a href="{{ route('user.login') }}" class="block px-4 py-3 text-gray-700 hover:bg-gray-50 transition">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <div>
                                    <p class="font-semibold text-sm">Sign In</p>
                                    <p class="text-xs text-gray-500">Access your account</p>
                                </div>
                            </div>
                        </a>
                        <div class="border-t border-gray-100 my-2"></div>
                        <a href="{{ route('user.register') }}" class="block px-4 py-3 text-gray-700 hover:bg-gray-50 transition">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                </svg>
                                <div>
                                    <p class="font-semibold text-sm">Create Account</p>
                                    <p class="text-xs text-gray-500">Sign up with email</p>
                                </div>
                            </div>
                        </a>
                        <a href="{{ url('/api/auth/google/redirect') }}" class="block px-4 py-3 text-gray-700 hover:bg-gray-50 transition">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24">
                                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                                </svg>
                                <div>
                                    <p class="font-semibold text-sm">Sign in with Google</p>
                                    <p class="text-xs text-gray-500">Quick and secure</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                @endauth

                <a href="https://play.google.com/store/apps/details?id=com.cubebitz.swap&hl=en_IN" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg font-semibold transition">
                    Download App
                </a>
            </div>

            <!-- Mobile Menu Button -->
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-gray-700 focus:outline-none">
                <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
                <svg x-show="mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95"
            class="md:hidden mt-4 pb-4"
            style="display: none;">
            <div class="flex flex-col space-y-3">
                @auth
                <!-- Mobile User Info -->
                <div class="bg-gray-50 rounded-lg p-4 mb-2">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full bg-primary-600 flex items-center justify-center text-white font-semibold">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500">{{ Auth::user()->email }}</p>
                        </div>
                    </div>
                    <a href="{{ route('pdf-books.my-library') }}" class="block mt-3 text-primary-600 font-medium text-sm hover:text-primary-700">
                        View My Library →
                    </a>
                </div>
                @else
                <!-- Mobile Auth Buttons -->
                <div class="bg-gray-50 rounded-lg p-4 space-y-2 mb-2">
                    <a href="{{ route('user.login') }}" class="block w-full bg-primary-600 hover:bg-primary-700 text-white px-4 py-2.5 rounded-lg font-semibold text-center transition">
                        Sign In
                    </a>
                    <a href="{{ route('user.register') }}" class="block w-full bg-white hover:bg-gray-50 text-gray-700 px-4 py-2.5 rounded-lg font-semibold text-center transition border border-gray-300">
                        Create Account
                    </a>
                    <a href="{{ url('/api/auth/google/redirect') }}" class="w-full bg-white hover:bg-gray-50 text-gray-700 px-4 py-2.5 rounded-lg font-semibold text-center transition border border-gray-300 flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                        </svg>
                        Sign in with Google
                    </a>
                </div>
                @endauth

                <a href="{{ route('home') }}" class="text-gray-700 hover:text-primary-600 font-medium py-2 {{ request()->routeIs('home') ? 'text-primary-600' : '' }}">
                    Home
                </a>
                <a href="{{ route('pdf-books.index') }}" class="text-gray-700 hover:text-primary-600 font-medium transition {{ request()->routeIs('pdf-books.*') ? 'text-primary-600' : '' }}">
                    PDF Books
                </a>
                <a href="{{ route('features') }}" class="text-gray-700 hover:text-primary-600 font-medium py-2 {{ request()->routeIs('features') ? 'text-primary-600' : '' }}">
                    Features
                </a>
                <a href="{{ route('how-it-works') }}" class="text-gray-700 hover:text-primary-600 font-medium py-2 {{ request()->routeIs('how-it-works') ? 'text-primary-600' : '' }}">
                    How It Works
                </a>
                <a href="{{ route('blog.index') }}" class="text-gray-700 hover:text-primary-600 font-medium py-2 {{ request()->routeIs('blog.*') ? 'text-primary-600' : '' }}">
                    Blog
                </a>
                <a href="{{ route('about') }}" class="text-gray-700 hover:text-primary-600 font-medium py-2 {{ request()->routeIs('about') ? 'text-primary-600' : '' }}">
                    About
                </a>

                <!-- Mobile Legal Section -->
                <div class="border-t border-gray-200 pt-3 mt-3">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Legal & Support</p>
                    <a href="{{ route('privacy-policy') }}" class="block text-gray-700 hover:text-primary-600 font-medium py-2">
                        Privacy Policy
                    </a>
                    <a href="{{ route('termsAndConditions') }}" class="block text-gray-700 hover:text-primary-600 font-medium py-2">
                        Terms of Service
                    </a>
                    <a href="{{ route('deletion.request') }}" class="block text-gray-700 hover:text-primary-600 font-medium py-2">
                        Delete My Data
                    </a>
                    <a href="{{ route('deletion.status') }}" class="block text-gray-700 hover:text-primary-600 font-medium py-2">
                        Check Deletion Status
                    </a>
                </div>

                @auth
                <form action="{{ route('user.logout') }}" method="POST" class="border-t border-gray-200 pt-3">
                    @csrf
                    <button type="submit" class="w-full text-left text-red-600 hover:text-red-700 font-medium py-2">
                        Logout
                    </button>
                </form>
                @endif

                <a href="https://play.google.com/store/apps/details?id=com.cubebitz.swap&hl=en_IN" class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-2.5 rounded-lg font-semibold text-center transition mt-4">
                    Download App
                </a>
            </div>
        </div>
    </nav>
</header>