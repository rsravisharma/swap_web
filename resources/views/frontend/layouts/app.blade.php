<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', 'Swap - The student marketplace for buying and selling second-hand books and items. Save money, help the environment, and connect with students on your campus.')">
    <meta name="keywords" content="student marketplace, second hand books, buy sell textbooks, college books, used books">
    
    <!-- Open Graph / Social Media -->
    <meta property="og:title" content="@yield('og_title', 'Swap - Student Marketplace for Books & Items')">
    <meta property="og:description" content="@yield('og_description', 'Buy and sell second-hand books and items with fellow students. Download the Swap app today!')">
    <meta property="og:image" content="@yield('og_image', asset('images/og-image.jpg'))">
    <meta property="og:url" content="{{ url()->current() }}">
    
    <title>@yield('title', 'Swap - Student Marketplace for Books & Items')</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                        },
                        secondary: {
                            500: '#8b5cf6',
                            600: '#7c3aed',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js for interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50">
    
    <!-- Header -->
    @include('frontend.layouts.partials.header')
    
    <!-- Main Content -->
    <main class="min-h-screen">
        @yield('content')
    </main>
    
    <!-- Footer -->
    @include('frontend.layouts.partials.footer')
    
    
    @stack('scripts')
    
    <!-- Toast Notifications (if needed) -->
    <div x-data="{ show: false, message: '' }" 
         @notify.window="show = true; message = $event.detail; setTimeout(() => show = false, 3000)"
         x-show="show" 
         x-transition
         class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50"
         style="display: none;">
        <p x-text="message"></p>
    </div>
</body>
</html>
