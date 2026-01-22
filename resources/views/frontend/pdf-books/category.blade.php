@extends('frontend.layouts.app')

@section('title', $pdfCategory->name . ' - PDF Books')

@section('content')
<div class="bg-gradient-to-br from-primary-50 to-white py-12">
    <div class="container mx-auto px-4">
        <!-- Breadcrumb -->
        <nav class="mb-8">
            <div class="inline-flex items-center space-x-1 text-sm text-gray-600 bg-white px-4 py-2 rounded-lg shadow-sm">
                <a href="{{ route('pdf-books.index') }}" class="hover:text-primary-600">All Books</a>
                @foreach($breadcrumb as $index => $cat)
                    <span>›</span>
                    <a href="{{ route('pdf-books.category', $cat) }}" 
                       class="hover:text-primary-600 {{ $loop->last ? 'font-semibold text-primary-600' : '' }}">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </div>
        </nav>

        <!-- Category Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $pdfCategory->name }}</h1>
            <p class="text-xl text-gray-600">
                {{ $books->total() }} books available 
                @if($pdfCategory->parent)
                    in {{ $pdfCategory->parent->name }}
                @endif
            </p>
        </div>

        <!-- Books Grid -->
        @if($books->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-8">
                @foreach($books as $book)
                    @include('frontend.pdf-books.partials.card', ['book' => $book])
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $books->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-20 bg-white rounded-xl shadow-md">
                <svg class="w-24 h-24 text-gray-300 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <h3 class="text-2xl font-semibold text-gray-700 mb-2">No books found</h3>
                <p class="text-gray-500 mb-6">No books available in this category yet</p>
                <a href="{{ route('pdf-books.index') }}" class="bg-primary-600 text-white px-8 py-3 rounded-lg hover:bg-primary-700 transition font-semibold">
                    Browse All Books
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
