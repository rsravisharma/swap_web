@extends('admin.layouts.app')

@section('title', 'Upload PDF Book')
@section('page-title', 'Upload New PDF Book')

@section('content')
<div class="p-6">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('admin.pdf-manager.index') }}" class="text-blue-600 hover:text-blue-800 font-semibold">
            <i class="fas fa-arrow-left mr-2"></i>Back to My Books
        </a>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-xl shadow">
        <div class="p-6 border-b">
            <h2 class="text-2xl font-bold text-gray-900">Upload PDF Book</h2>
            <p class="text-sm text-gray-600 mt-1">Fill in the details to upload a new PDF book</p>
        </div>

        <form method="POST" action="{{ route('admin.pdf-manager.store') }}" enctype="multipart/form-data" class="p-6">
            @csrf

            <!-- User Selection -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Assign to User <span class="text-red-500">*</span>
                </label>
                <select name="user_id" 
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('user_id') border-red-500 @enderror">
                    <option value="">Select a user</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }} ({{ $user->email }})
                    </option>
                    @endforeach
                </select>
                @error('user_id')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 mt-1">This book will appear in the selected user's account</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Title -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Book Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="title" 
                           value="{{ old('title') }}"
                           required
                           placeholder="Enter book title"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('title') border-red-500 @enderror">
                    @error('title')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Author -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Author</label>
                    <input type="text" 
                           name="author" 
                           value="{{ old('author') }}"
                           placeholder="Author name"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('author') border-red-500 @enderror">
                    @error('author')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- ISBN -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">ISBN</label>
                    <input type="text" 
                           name="isbn" 
                           value="{{ old('isbn') }}"
                           placeholder="978-3-16-148410-0"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('isbn') border-red-500 @enderror">
                    @error('isbn')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Publisher -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Publisher</label>
                    <input type="text" 
                           name="publisher" 
                           value="{{ old('publisher') }}"
                           placeholder="Publisher name"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('publisher') border-red-500 @enderror">
                    @error('publisher')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Publication Year -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Publication Year</label>
                    <input type="number" 
                           name="publication_year" 
                           value="{{ old('publication_year') }}"
                           min="1900"
                           max="{{ date('Y') }}"
                           placeholder="{{ date('Y') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('publication_year') border-red-500 @enderror">
                    @error('publication_year')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Price -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Price (₹) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           name="price" 
                           value="{{ old('price') }}"
                           required
                           min="0"
                           step="0.01"
                           placeholder="299.00"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('price') border-red-500 @enderror">
                    @error('price')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Total Pages -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Total Pages</label>
                    <input type="number" 
                           name="total_pages" 
                           value="{{ old('total_pages') }}"
                           min="1"
                           placeholder="250"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('total_pages') border-red-500 @enderror">
                    @error('total_pages')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Language -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Language</label>
                    <select name="language" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="en" {{ old('language') === 'en' ? 'selected' : '' }}>English</option>
                        <option value="hi" {{ old('language') === 'hi' ? 'selected' : '' }}>Hindi</option>
                        <option value="es" {{ old('language') === 'es' ? 'selected' : '' }}>Spanish</option>
                        <option value="fr" {{ old('language') === 'fr' ? 'selected' : '' }}>French</option>
                    </select>
                </div>

                <!-- File Size -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">File Size (bytes)</label>
                    <input type="number" 
                           name="file_size" 
                           value="{{ old('file_size') }}"
                           min="1"
                           placeholder="5242880"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('file_size') border-red-500 @enderror">
                    @error('file_size')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Optional: Enter file size in bytes</p>
                </div>

                <!-- Google Drive File ID -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Google Drive File ID <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="google_drive_file_id" 
                           value="{{ old('google_drive_file_id') }}"
                           required
                           placeholder="1abc2def3ghi4jkl5mno6pqr7stu8vwx9yz"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('google_drive_file_id') border-red-500 @enderror">
                    @error('google_drive_file_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">The unique file ID from Google Drive URL</p>
                </div>

                <!-- Google Drive Shareable Link -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Google Drive Shareable Link</label>
                    <input type="url" 
                           name="google_drive_shareable_link" 
                           value="{{ old('google_drive_shareable_link') }}"
                           placeholder="https://drive.google.com/file/d/..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('google_drive_shareable_link') border-red-500 @enderror">
                    @error('google_drive_shareable_link')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Cover Image -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cover Image</label>
                    <input type="file" 
                           name="cover_image" 
                           accept="image/jpeg,image/jpg,image/png"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('cover_image') border-red-500 @enderror">
                    @error('cover_image')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Accepted formats: JPEG, JPG, PNG (Max: 2MB)</p>
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" 
                              rows="4"
                              placeholder="Enter book description..."
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                    @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end space-x-3 mt-8 pt-6 border-t">
                <a href="{{ route('admin.pdf-manager.index') }}" 
                   class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold">
                    <i class="fas fa-upload mr-2"></i>Upload Book
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
