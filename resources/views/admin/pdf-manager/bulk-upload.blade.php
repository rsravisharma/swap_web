@extends('admin.layouts.app')

@section('title', 'Bulk Upload PDF Books')
@section('page-title', 'Bulk Upload PDF Books')

@section('content')
<div class="p-6">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('admin.pdf-manager.index') }}" class="text-blue-600 hover:text-blue-800 font-semibold">
            <i class="fas fa-arrow-left mr-2"></i>Back to My Books
        </a>
    </div>

    <!-- Instructions -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-6">
        <div class="flex items-start">
            <i class="fas fa-info-circle text-blue-600 text-xl mr-3 mt-1"></i>
            <div>
                <h3 class="text-lg font-bold text-blue-900 mb-2">Bulk Upload Instructions</h3>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li>• Select a user to assign all books to</li>
                    <li>• Add multiple books by clicking "Add Another Book"</li>
                    <li>• Required fields: Title, Google Drive File ID, and Price</li>
                    <li>• You can add up to 20 books at once</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-xl shadow">
        <div class="p-6 border-b">
            <h2 class="text-2xl font-bold text-gray-900">Bulk Upload PDF Books</h2>
            <p class="text-sm text-gray-600 mt-1">Upload multiple books at once</p>
        </div>

        <form method="POST" action="{{ route('admin.pdf-manager.bulk-store') }}" class="p-6" id="bulkUploadForm">
            @csrf

            <!-- User Selection -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Assign All Books to User <span class="text-red-500">*</span>
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
            </div>

            <!-- Books Container -->
            <div id="booksContainer" class="space-y-6">
                <!-- Book 1 (Template) -->
                <div class="book-entry border border-gray-300 rounded-lg p-6 relative">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Book #<span class="book-number">1</span></h3>
                        <button type="button" class="remove-book text-red-600 hover:text-red-800 hidden">
                            <i class="fas fa-times-circle text-xl"></i>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Title -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Title <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="books[0][title]" 
                                   required
                                   placeholder="Enter book title"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Author -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Author</label>
                            <input type="text" 
                                   name="books[0][author]" 
                                   placeholder="Author name"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Original Price -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Original Price (₹) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   name="books[0][original_price]" 
                                   required
                                   min="0"
                                   step="0.01"
                                   placeholder="299.00"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Price -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Price (₹) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   name="books[0][price]" 
                                   required
                                   min="0"
                                   step="0.01"
                                   placeholder="299.00"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Google Drive File ID -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Google Drive File ID <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="books[0][google_drive_file_id]" 
                                   required
                                   placeholder="1abc2def3ghi4jkl5mno6pqr7stu8vwx9yz"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Google Drive Shareable Link -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Google Drive Shareable Link</label>
                            <input type="url" 
                                   name="books[0][google_drive_shareable_link]" 
                                   placeholder="https://drive.google.com/file/d/..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="books[0][description]" 
                                      rows="2"
                                      placeholder="Enter book description..."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Book Button -->
            <div class="mt-6">
                <button type="button" 
                        id="addBookBtn"
                        class="w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-semibold border-2 border-dashed border-gray-300">
                    <i class="fas fa-plus mr-2"></i>Add Another Book
                </button>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-between mt-8 pt-6 border-t">
                <p class="text-sm text-gray-600">
                    Total Books: <span id="bookCount" class="font-bold text-blue-600">1</span>
                </p>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.pdf-manager.index') }}" 
                       class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold">
                        <i class="fas fa-upload mr-2"></i>Upload All Books
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
let bookIndex = 1;
const maxBooks = 20;

document.getElementById('addBookBtn').addEventListener('click', function() {
    if (bookIndex >= maxBooks) {
        alert(`Maximum ${maxBooks} books allowed at once`);
        return;
    }

    const container = document.getElementById('booksContainer');
    const newBook = createBookEntry(bookIndex);
    container.insertAdjacentHTML('beforeend', newBook);
    
    bookIndex++;
    updateBookCount();
    updateRemoveButtons();
});

document.getElementById('booksContainer').addEventListener('click', function(e) {
    if (e.target.closest('.remove-book')) {
        e.target.closest('.book-entry').remove();
        updateBookNumbers();
        updateBookCount();
        updateRemoveButtons();
    }
});

function createBookEntry(index) {
    return `
        <div class="book-entry border border-gray-300 rounded-lg p-6 relative">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900">Book #<span class="book-number">${index + 1}</span></h3>
                <button type="button" class="remove-book text-red-600 hover:text-red-800">
                    <i class="fas fa-times-circle text-xl"></i>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="books[${index}][title]" 
                           required
                           placeholder="Enter book title"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Author</label>
                    <input type="text" 
                           name="books[${index}][author]" 
                           placeholder="Author name"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Original Price (₹) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           name="books[${index}][original_price]" 
                           required
                           min="0"
                           step="0.01"
                           placeholder="299.00"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Price (₹) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           name="books[${index}][price]" 
                           required
                           min="0"
                           step="0.01"
                           placeholder="299.00"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Google Drive File ID <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="books[${index}][google_drive_file_id]" 
                           required
                           placeholder="1abc2def3ghi4jkl5mno6pqr7stu8vwx9yz"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Google Drive Shareable Link</label>
                    <input type="url" 
                           name="books[${index}][google_drive_shareable_link]" 
                           placeholder="https://drive.google.com/file/d/..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="books[${index}][description]" 
                              rows="2"
                              placeholder="Enter book description..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
            </div>
        </div>
    `;
}

function updateBookNumbers() {
    const entries = document.querySelectorAll('.book-entry');
    entries.forEach((entry, index) => {
        entry.querySelector('.book-number').textContent = index + 1;
    });
}

function updateBookCount() {
    const count = document.querySelectorAll('.book-entry').length;
    document.getElementById('bookCount').textContent = count;
}

function updateRemoveButtons() {
    const entries = document.querySelectorAll('.book-entry');
    entries.forEach((entry, index) => {
        const removeBtn = entry.querySelector('.remove-book');
        if (entries.length === 1) {
            removeBtn.classList.add('hidden');
        } else {
            removeBtn.classList.remove('hidden');
        }
    });
}
</script>
@endsection
