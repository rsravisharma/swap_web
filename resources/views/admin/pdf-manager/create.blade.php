@extends('admin.layouts.app')

@section('title', 'Upload PDF Book')
@section('page-title', 'Upload New PDF Book')

@section('content')


<style>
    .category-select:focus {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
    }
</style>


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

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Category <span class="text-orange-500">*</span>
                </label>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Level 1: Main Categories --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Main Category</label>
                        <select name="main_category_id" id="mainCategorySelect"
                            class="category-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Main Category</option>
                            @foreach($categories as $mainCategory)
                            <option value="{{ $mainCategory->id }}">
                                {{ $mainCategory->name }}
                                <span class="text-xs text-gray-400">({{ $mainCategory->pdfBooks()->count() }} books)</span>
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Level 2: Sub Categories --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Sub Category</label>
                        <select name="sub_category_id" id="subCategorySelect"
                            class="category-select w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50" disabled>
                            <option value="">Select Sub Category</option>
                        </select>
                    </div>

                    {{-- Level 3: Child Categories --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Subject/Topic</label>
                        <select name="child_category_id" id="childCategorySelect"
                            class="category-select w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50" disabled>
                            <option value="">Select Subject (Optional)</option>
                        </select>
                    </div>
                </div>

                {{-- Hidden final category_id --}}
                <input type="hidden" name="category_id" id="finalCategoryId" value="{{ old('category_id') }}">

                {{-- Selected Category Preview --}}
                <div id="categoryPreview" class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg hidden">
                    <div class="flex items-center justify-between">
                        <span id="previewText" class="font-medium text-blue-800"></span>
                        <button type="button" id="clearCategoryBtn" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            <i class="fas fa-times mr-1"></i>Change
                        </button>
                    </div>
                </div>

                @error('category_id')
                <p class="text-red-500 text-xs mt-2 bg-red-50 p-2 rounded">{{ $message }}</p>
                @enderror
            </div>


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

                <!-- Original Price -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Original Price (₹) <span class="text-red-500">*</span>
                    </label>
                    <input type="number"
                        name="original_price"
                        value="{{ old('original_price') }}"
                        required
                        min="0"
                        step="0.01"
                        placeholder="299.00"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('original_price') border-red-500 @enderror">
                    @error('original_price')
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

                    {{-- Visible Quill editor --}}
                    <div id="quill-description" class="border border-gray-300 rounded-lg" style="min-height: 200px;"></div>

                    {{-- Hidden textarea that will receive HTML from Quill --}}
                    <textarea name="description"
                        id="description"
                        class="hidden @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>

                    @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror

                    <p class="text-xs text-gray-500 mt-1">
                        You can format the text (bold, bullets, numbered lists, etc.).
                    </p>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end space-x-3 mt-10 pt-10 border-t">
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

{{-- Quill CSS --}}
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">

{{-- Quill JS --}}
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get DOM elements
        const mainSelect = document.getElementById('mainCategorySelect');
        const subSelect = document.getElementById('subCategorySelect');
        const childSelect = document.getElementById('childCategorySelect');
        const finalCategoryInput = document.getElementById('finalCategoryId');
        const previewDiv = document.getElementById('categoryPreview');
        const previewText = document.getElementById('previewText');
        const clearBtn = document.getElementById('clearCategoryBtn');

        // Category data from server-side data attributes
        const mainCategories = @json($categories);

         const quill = new Quill('#quill-description', {
            theme: 'snow',
            placeholder: 'Enter book description...',
            modules: {
                toolbar: [
                    [{ header: [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline'],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    ['link', 'blockquote', 'clean']
                ]
            }
        });

        const hiddenTextarea = document.getElementById('description');

        // If editing (old value), load HTML into Quill
        if (hiddenTextarea.value) {
            quill.root.innerHTML = hiddenTextarea.value;
        }

        // On submit, put Quill HTML into textarea
        const form = hiddenTextarea.closest('form');
        form.addEventListener('submit', function () {
            hiddenTextarea.value = quill.root.innerHTML;
        });

        // Get children of a category via AJAX
        function loadChildren(parentId, targetSelect) {
            if (!parentId) return;

            fetch(`/admin/categories/${parentId}/children`)
                .then(response => response.json())
                .then(children => {
                    targetSelect.innerHTML = '<option value="">Select...</option>';
                    children.forEach(child => {
                        targetSelect.innerHTML += `<option value="${child.id}">${child.name}</option>`;
                    });
                    targetSelect.disabled = children.length === 0;
                })
                .catch(error => {
                    console.error('Error loading children:', error);
                    targetSelect.innerHTML = '<option value="">Error loading</option>';
                });
        }

        // Main category change handler
        mainSelect.addEventListener('change', function() {
            const mainId = this.value;
            subSelect.innerHTML = '<option value="">Select Sub Category</option>';
            childSelect.innerHTML = '<option value="">Select Subject</option>';
            childSelect.disabled = true;
            previewDiv.classList.add('hidden');
            finalCategoryInput.value = '';

            subSelect.disabled = !mainId;
            childSelect.disabled = true;

            if (mainId) {
                finalCategoryInput.value = mainId;
                previewText.textContent = `📂 ${mainSelect.options[mainSelect.selectedIndex].text}`;
                previewDiv.classList.remove('hidden');

                // Load subcategories
                loadChildren(mainId, subSelect);
            }
        });

        // Sub category change handler
        subSelect.addEventListener('change', function() {
            const subId = this.value;
            childSelect.innerHTML = '<option value="">Select Subject</option>';
            previewDiv.classList.add('hidden');
            finalCategoryInput.value = '';

            childSelect.disabled = !subId;

            if (subId) {
                finalCategoryInput.value = subId;
                previewText.textContent = `📋 ${subSelect.options[subSelect.selectedIndex].text}`;
                previewDiv.classList.remove('hidden');

                // Load child categories
                loadChildren(subId, childSelect);
            }
        });

        // Child category change handler
        childSelect.addEventListener('change', function() {
            const childId = this.value;
            previewDiv.classList.add('hidden');

            if (childId) {
                finalCategoryInput.value = childId;
                previewText.textContent = `📚 ${childSelect.options[childSelect.selectedIndex].text}`;
                previewDiv.classList.remove('hidden');
            }
        });

        // Clear all selections
        clearBtn.addEventListener('click', function() {
            mainSelect.value = '';
            subSelect.innerHTML = '<option value="">Select Sub Category</option>';
            childSelect.innerHTML = '<option value="">Select Subject</option>';
            finalCategoryInput.value = '';
            subSelect.disabled = true;
            childSelect.disabled = true;
            previewDiv.classList.add('hidden');
        });
    });
</script>
@endsection