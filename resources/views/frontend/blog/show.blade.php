@extends('frontend.layouts.app')

@section('title', $post->title . ' - Swap Blog')
@section('meta_description', $post->excerpt)
@section('og_title', $post->title)
@section('og_description', $post->excerpt)
@section('og_image', $post->featured_image ?? asset('images/og-image.jpg'))

@section('content')

<!-- Article Header -->
<article class="bg-white">
    
    <!-- Featured Image -->
    @if($post->featured_image)
    <div class="relative h-96 bg-gray-900">
        <img src="{{ $post->featured_image }}" 
             alt="{{ $post->title }}" 
             class="w-full h-full object-cover opacity-80">
        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
    </div>
    @endif
    
    <!-- Article Meta & Title -->
    <div class="container mx-auto px-4 {{ $post->featured_image ? '-mt-32 relative z-10' : 'pt-20' }}">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-2xl shadow-2xl p-8 md:p-12">
                
                <!-- Category & Reading Time -->
                <div class="flex flex-wrap items-center gap-4 mb-6">
                    @if($post->category)
                    <span class="bg-primary-100 text-primary-600 px-4 py-2 rounded-full text-sm font-semibold">
                        {{ ucfirst($post->category) }}
                    </span>
                    @endif
                    <span class="text-gray-500 text-sm">
                        {{ $post->published_at->format('F d, Y') }}
                    </span>
                    <span class="text-gray-500 text-sm">
                        • {{ $post->reading_time ?? '5' }} min read
                    </span>
                </div>
                
                <!-- Title -->
                <h1 class="text-3xl md:text-5xl font-bold text-gray-900 mb-6 leading-tight">
                    {{ $post->title }}
                </h1>
                
                <!-- Excerpt -->
                <p class="text-xl text-gray-600 mb-8 leading-relaxed">
                    {{ $post->excerpt }}
                </p>
                
                <!-- Author Info -->
                <div class="flex items-center justify-between border-t border-b border-gray-200 py-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-primary-600 text-white rounded-full flex items-center justify-center font-bold text-lg mr-4">
                            {{ substr($post->author_name ?? 'Swap Team', 0, 1) }}
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">{{ $post->author_name ?? 'Swap Team' }}</p>
                            <p class="text-sm text-gray-500">{{ $post->author_title ?? 'Content Writer' }}</p>
                        </div>
                    </div>
                    
                    <!-- Share Buttons -->
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-gray-500 mr-2 hidden sm:block">Share:</span>
                        <a href="https://twitter.com/intent/tweet?text={{ urlencode($post->title) }}&url={{ urlencode(url()->current()) }}" 
                           target="_blank"
                           class="w-10 h-10 bg-gray-100 hover:bg-blue-500 hover:text-white rounded-full flex items-center justify-center transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" 
                           target="_blank"
                           class="w-10 h-10 bg-gray-100 hover:bg-blue-600 hover:text-white rounded-full flex items-center justify-center transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(url()->current()) }}&title={{ urlencode($post->title) }}" 
                           target="_blank"
                           class="w-10 h-10 bg-gray-100 hover:bg-blue-700 hover:text-white rounded-full flex items-center justify-center transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                        </a>
                        <button onclick="copyToClipboard()" 
                                class="w-10 h-10 bg-gray-100 hover:bg-gray-300 rounded-full flex items-center justify-center transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Article Content -->
    <div class="container mx-auto px-4 py-16">
        <div class="max-w-4xl mx-auto">
            <div class="prose prose-lg max-w-none">
                {!! $post->content !!}
            </div>
            
            <!-- Tags -->
            @if($post->tags && count($post->tags) > 0)
            <div class="mt-12 pt-8 border-t border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Tags:</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($post->tags as $tag)
                    <a href="{{ route('blog.index', ['tag' => $tag]) }}" 
                       class="bg-gray-100 hover:bg-primary-100 hover:text-primary-600 text-gray-700 px-4 py-2 rounded-full text-sm transition">
                        #{{ $tag }}
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
            
            <!-- Author Bio -->
            <div class="mt-12 p-8 bg-gray-50 rounded-xl">
                <div class="flex items-start gap-6">
                    <div class="w-20 h-20 bg-primary-600 text-white rounded-full flex items-center justify-center font-bold text-2xl flex-shrink-0">
                        {{ substr($post->author_name ?? 'Swap Team', 0, 1) }}
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $post->author_name ?? 'Swap Team' }}</h3>
                        <p class="text-gray-600 mb-3">
                            {{ $post->author_bio ?? 'The Swap team is dedicated to helping students save money and make the most of their college experience. We share tips, guides, and insights on student life, budgeting, and using our marketplace effectively.' }}
                        </p>
                        @if(isset($post->author_social) && is_array($post->author_social))
                        <div class="flex gap-3">
                            @if(isset($post->author_social['twitter']))
                            <a href="{{ $post->author_social['twitter'] }}" class="text-gray-400 hover:text-primary-600 transition">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                </svg>
                            </a>
                            @endif
                            @if(isset($post->author_social['linkedin']))
                            <a href="{{ $post->author_social['linkedin'] }}" class="text-gray-400 hover:text-primary-600 transition">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                </svg>
                            </a>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            
        </div>
    </div>
    
</article>

<!-- Related Posts -->
@if(isset($relatedPosts) && $relatedPosts->count() > 0)
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-3xl font-bold text-gray-900 mb-8">Related Articles</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach($relatedPosts as $relatedPost)
                <article class="bg-white rounded-xl overflow-hidden shadow-md hover:shadow-xl transition">
                    <a href="{{ route('blog.show', $relatedPost->slug) }}" class="group">
                        <div class="relative h-48 overflow-hidden">
                            <img src="{{ $relatedPost->featured_image ?? asset('images/blog-placeholder.jpg') }}" 
                                 alt="{{ $relatedPost->title }}" 
                                 class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                        </div>
                        <div class="p-6">
                            <div class="text-sm text-gray-500 mb-2">
                                {{ $relatedPost->published_at->format('F d, Y') }}
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-primary-600 transition">
                                {{ $relatedPost->title }}
                            </h3>
                            <p class="text-gray-600 text-sm line-clamp-2">
                                {{ $relatedPost->excerpt }}
                            </p>
                        </div>
                    </a>
                </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif

<!-- CTA Section -->
<section class="bg-gradient-to-r from-primary-600 to-secondary-600 py-16">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
            Ready to Start Saving?
        </h2>
        <p class="text-xl text-white/90 mb-8 max-w-2xl mx-auto">
            Join thousands of students already using Swap to buy and sell textbooks and items.
        </p>
        <a href="https://play.google.com/store/apps/details?id=com.cubebitz.swap&hl=en_IN" class="bg-white hover:bg-gray-100 text-primary-600 px-8 py-4 rounded-lg font-semibold text-lg transition shadow-lg hover:shadow-xl inline-block">
            Download the App
        </a>
    </div>
</section>

@endsection

@push('styles')
<style>
    /* Prose styles for blog content */
    .prose {
        color: #374151;
    }
    
    .prose h2 {
        font-size: 2rem;
        font-weight: 700;
        margin-top: 2rem;
        margin-bottom: 1rem;
        color: #111827;
    }
    
    .prose h3 {
        font-size: 1.5rem;
        font-weight: 600;
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
        color: #111827;
    }
    
    .prose h4 {
        font-size: 1.25rem;
        font-weight: 600;
        margin-top: 1.25rem;
        margin-bottom: 0.5rem;
        color: #111827;
    }
    
    .prose p {
        margin-bottom: 1.25rem;
        line-height: 1.75;
        font-size: 1.125rem;
    }
    
    .prose ul, .prose ol {
        margin-bottom: 1.25rem;
        padding-left: 1.5rem;
    }
    
    .prose li {
        margin-bottom: 0.5rem;
        line-height: 1.75;
    }
    
    .prose a {
        color: #0ea5e9;
        text-decoration: underline;
    }
    
    .prose a:hover {
        color: #0284c7;
    }
    
    .prose img {
        border-radius: 0.5rem;
        margin: 2rem auto;
        max-width: 100%;
        height: auto;
    }
    
    .prose blockquote {
        border-left: 4px solid #0ea5e9;
        padding-left: 1rem;
        font-style: italic;
        color: #6b7280;
        margin: 1.5rem 0;
        font-size: 1.125rem;
    }
    
    .prose code {
        background-color: #f3f4f6;
        padding: 0.2rem 0.4rem;
        border-radius: 0.25rem;
        font-size: 0.875rem;
        font-family: 'Courier New', monospace;
    }
    
    .prose pre {
        background-color: #1f2937;
        color: #f9fafb;
        padding: 1rem;
        border-radius: 0.5rem;
        overflow-x: auto;
        margin: 1.5rem 0;
    }
    
    .prose pre code {
        background-color: transparent;
        padding: 0;
        color: inherit;
    }
    
    .prose strong {
        font-weight: 600;
        color: #111827;
    }
    
    .prose em {
        font-style: italic;
    }
    
    .prose hr {
        border: 0;
        border-top: 1px solid #e5e7eb;
        margin: 2rem 0;
    }
    
    .prose table {
        width: 100%;
        border-collapse: collapse;
        margin: 1.5rem 0;
    }
    
    .prose th, .prose td {
        border: 1px solid #e5e7eb;
        padding: 0.75rem;
        text-align: left;
    }
    
    .prose th {
        background-color: #f9fafb;
        font-weight: 600;
    }
    
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endpush

@push('scripts')
<script>
    function copyToClipboard() {
        const url = window.location.href;
        navigator.clipboard.writeText(url).then(() => {
            // Show notification
            window.dispatchEvent(new CustomEvent('notify', { 
                detail: 'Link copied to clipboard!' 
            }));
        }).catch(err => {
            console.error('Failed to copy:', err);
            alert('Link copied!');
        });
    }
</script>
@endpush
