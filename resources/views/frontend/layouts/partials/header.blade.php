<header class="bg-white shadow-sm sticky top-0 z-50" x-data="{ mobileMenuOpen: false, legalMenuOpen: false }">
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
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
            
            <!-- CTA Button -->
            <div class="hidden md:block">
                <a href="https://play.google.com/store/apps/details?id=com.cubebitz.swap&hl=en_IN" class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-2.5 rounded-lg font-semibold transition shadow-md hover:shadow-lg">
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
                <a href="{{ route('home') }}" class="text-gray-700 hover:text-primary-600 font-medium py-2 {{ request()->routeIs('home') ? 'text-primary-600' : '' }}">
                    Home
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
                
                <a href="https://play.google.com/store/apps/details?id=com.cubebitz.swap&hl=en_IN" class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-2.5 rounded-lg font-semibold text-center transition mt-4">
                    Download App
                </a>
            </div>
        </div>
    </nav>
</header>
