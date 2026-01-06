@extends('frontend.layouts.app')

@section('title', 'Blog - Student Tips & Marketplace Guides | Swap')
@section('meta_description', 'Read tips on saving money, buying and selling textbooks, student hacks, and marketplace guides on the Swap blog.')

@section('content')

<!-- Hero Section -->
<section class="bg-gradient-to-br from-primary-50 to-secondary-50 py-20">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">
            Swap Blog
        </h1>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Student tips, money-saving guides, and marketplace insights to help you get the most out of Swap.
        </p>
    </div>
</section>

<!-- Blog Posts Grid -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        
        @if($posts->count() > 0)
            <!-- Featured Post -->
            @if($featured = $posts->first())
            <div class="mb-16">
                <a href="{{ route('blog.show', $featured->slug) }}" class="group">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 bg-gray-50 rounded-2xl overflow-hidden hover:shadow-xl transition">
                        <div class="relative h-64 lg:h-auto">
                            <img src="{{ $featured->featured_image ?? asset('images/blog-placeholder.jpg') }}" 
                                 alt="{{ $featured->title }}" 
                                 class="w-full h-full object-cover">
                            <div class="absolute top-4 left-4 bg-primary-600 text-white px-4 py-2 rounded-full text-sm font-semibold">
                                Featured
                            </div>
                        </div>
                        <div class="p-8 lg:p-12 flex flex-col justify-center">
                            <div class="text-sm text-gray-500 mb-3">
                                {{ $featured->published_at->format('F d, Y') }} • {{ $featured->reading_time ?? '5' }} min read
                            </div>
                            <h2 class="text-3xl font-bold text-gray-900 mb-4 group-hover:text-primary-600 transition">
                                {{ $featured->title }}
                            </h2>
                            <p class="text-gray-600 text-lg mb-6">
                                {{ $featured->excerpt }}
                            </p>
                            <div class="text-primary-600 font-semibold">
                                Read More →
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            @endif
            
            <!-- Category Filter -->
            <div class="flex flex-wrap gap-3 mb-12 justify-center" x-data="{ activeCategory: 'all' }">
                <button @click="activeCategory = 'all'" 
                        :class="activeCategory === 'all' ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-700'"
                        class="px-6 py-2 rounded-full font-semibold transition hover:bg-primary-500 hover:text-white">
                    All Posts
                </button>
                <button @click="activeCategory = 'tips'" 
                        :class="activeCategory === 'tips' ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-700'"
                        class="px-6 py-2 rounded-full font-semibold transition hover:bg-primary-500 hover:text-white">
                    Student Tips
                </button>
                <button @click="activeCategory = 'guides'" 
                        :class="activeCategory === 'guides' ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-700'"
                        class="px-6 py-2 rounded-full font-semibold transition hover:bg-primary-500 hover:text-white">
                    Buying Guides
                </button>
                <button @click="activeCategory = 'selling'" 
                        :class="activeCategory === 'selling' ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-700'"
                        class="px-6 py-2 rounded-full font-semibold transition hover:bg-primary-500 hover:text-white">
                    Selling Tips
                </button>
                <button @click="activeCategory = 'news'" 
                        :class="activeCategory === 'news' ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-700'"
                        class="px-6 py-2 rounded-full font-semibold transition hover:bg-primary-500 hover:text-white">
                    News & Updates
                </button>
            </div>
            
            <!-- Blog Posts Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($posts->skip(1) as $post)
                <article class="bg-white rounded-xl overflow-hidden shadow-md hover:shadow-xl transition">
                    <a href="{{ route('blog.show', $post->slug) }}" class="group">
                        <div class="relative h-48 overflow-hidden">
                            <img src="{{ $post->featured_image ?? asset('images/blog-placeholder.jpg') }}" 
                                 alt="{{ $post->title }}" 
                                 class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                            @if($post->category)
                            <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-semibold text-gray-700">
                                {{ ucfirst($post->category) }}
                            </div>
                            @endif
                        </div>
                        <div class="p-6">
                            <div class="text-sm text-gray-500 mb-3">
                                {{ $post->published_at->format('F d, Y') }} • {{ $post->reading_time ?? '5' }} min read
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-primary-600 transition">
                                {{ $post->title }}
                            </h3>
                            <p class="text-gray-600 mb-4 line-clamp-3">
                                {{ $post->excerpt }}
                            </p>
                            <div class="text-primary-600 font-semibold text-sm">
                                Read More →
                            </div>
                        </div>
                    </a>
                </article>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="mt-12">
                {{ $posts->links() }}
            </div>
            
        @else
            <!-- Empty State -->
            <div class="text-center py-20">
                <div class="text-6xl mb-4">📝</div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">No Blog Posts Yet</h2>
                <p class="text-gray-600 mb-8">Check back soon for student tips, guides, and marketplace insights!</p>
                <a href="{{ route('home') }}" class="bg-primary-600 hover:bg-primary-700 text-white px-8 py-3 rounded-lg font-semibold transition inline-block">
                    Back to Home
                </a>
            </div>
        @endif
        
    </div>
</section>

<!-- Newsletter Subscription -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="max-w-2xl mx-auto text-center">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">
                Never Miss a Post
            </h2>
            <p class="text-lg text-gray-600 mb-8">
                Subscribe to our newsletter and get student tips, money-saving hacks, and marketplace updates delivered to your inbox.
            </p>
            <form action="{{ route('newsletter.subscribe') }}" method="POST" class="flex flex-col sm:flex-row gap-4 max-w-lg mx-auto">
                @csrf
                <input type="email" 
                       name="email" 
                       placeholder="Enter your email" 
                       required
                       class="flex-1 px-6 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-200">
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white px-8 py-3 rounded-lg font-semibold transition">
                    Subscribe
                </button>
            </form>
            <p class="text-sm text-gray-500 mt-4">
                No spam. Unsubscribe anytime.
            </p>
        </div>
    </div>
</section>

<!-- Popular Topics -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">
                Popular Topics
            </h2>
        </div>
        <div class="flex flex-wrap justify-center gap-3 max-w-4xl mx-auto">
            <a href="?tag=textbooks" class="bg-gray-100 hover:bg-primary-100 hover:text-primary-600 text-gray-700 px-4 py-2 rounded-full transition">
                #textbooks
            </a>
            <a href="?tag=saving-money" class="bg-gray-100 hover:bg-primary-100 hover:text-primary-600 text-gray-700 px-4 py-2 rounded-full transition">
                #saving-money
            </a>
            <a href="?tag=college-life" class="bg-gray-100 hover:bg-primary-100 hover:text-primary-600 text-gray-700 px-4 py-2 rounded-full transition">
                #college-life
            </a>
            <a href="?tag=selling-tips" class="bg-gray-100 hover:bg-primary-100 hover:text-primary-600 text-gray-700 px-4 py-2 rounded-full transition">
                #selling-tips
            </a>
            <a href="?tag=budgeting" class="bg-gray-100 hover:bg-primary-100 hover:text-primary-600 text-gray-700 px-4 py-2 rounded-full transition">
                #budgeting
            </a>
            <a href="?tag=student-hacks" class="bg-gray-100 hover:bg-primary-100 hover:text-primary-600 text-gray-700 px-4 py-2 rounded-full transition">
                #student-hacks
            </a>
            <a href="?tag=marketplace" class="bg-gray-100 hover:bg-primary-100 hover:text-primary-600 text-gray-700 px-4 py-2 rounded-full transition">
                #marketplace
            </a>
            <a href="?tag=sustainability" class="bg-gray-100 hover:bg-primary-100 hover:text-primary-600 text-gray-700 px-4 py-2 rounded-full transition">
                #sustainability
            </a>
        </div>
    </div>
</section>

@endsection

@push('styles')
<style>
    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endpush
