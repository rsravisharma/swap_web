<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PdfBook;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PdfManagerController extends Controller
{
    /**
     * Show PDF upload form
     */
    public function index(Request $request)
    {
        $admin = auth('admin')->user();
        
        $perPage = $request->get('per_page', 20);
        
        // Show only books uploaded by this admin (managers see only their uploads)
        $query = PdfBook::with(['seller'])
            ->withCount('purchases')
            ->orderBy('created_at', 'desc');

        // If manager, show only their uploads
        if ($admin->role === 'manager') {
            $query->where('uploaded_by_admin_id', $admin->id);
        }
        
        $myBooks = $query->paginate($perPage)->withQueryString();

        // Stats based on role
        if ($admin->role === 'manager') {
            $stats = [
                'total_books' => PdfBook::where('uploaded_by_admin_id', $admin->id)->count(),
                'total_sales' => PdfBook::where('uploaded_by_admin_id', $admin->id)
                    ->withCount('purchases')
                    ->get()
                    ->sum('purchases_count'),
                'active_books' => PdfBook::where('uploaded_by_admin_id', $admin->id)
                    ->where('is_available', true)
                    ->count(),
            ];
        } else {
            $stats = [
                'total_books' => PdfBook::count(),
                'total_sales' => PdfBook::withCount('purchases')->get()->sum('purchases_count'),
                'active_books' => PdfBook::where('is_available', true)->count(),
            ];
        }

        return view('admin.pdf-manager.index', compact('myBooks', 'stats'));
    }

    /**
     * Show upload form
     */
    public function create()
    {
        // Get all verified users
        $users = User::whereNotNull('email_verified_at')
            ->orderBy('name')
            ->get();

        return view('admin.pdf-manager.create', compact('users'));
    }

    /**
     * Store PDF book
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'isbn' => 'nullable|string|max:255|unique:pdf_books,isbn,NULL,id,deleted_at,NULL',
            'description' => 'nullable|string',
            'publisher' => 'nullable|string|max:255',
            'publication_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'price' => 'required|numeric|min:0',
            'google_drive_file_id' => 'required|string',
            'google_drive_shareable_link' => 'nullable|url',
            'cover_image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'file_size' => 'nullable|integer',
            'total_pages' => 'nullable|integer',
            'language' => 'nullable|string|max:10',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->only([
            'title', 'author', 'isbn', 'description', 'publisher',
            'publication_year', 'price', 'google_drive_file_id',
            'google_drive_shareable_link', 'file_size', 'total_pages', 'language'
        ]);

        // Set seller to selected user
        $data['seller_id'] = $request->user_id;
        $data['uploaded_by_admin_id'] = auth('admin')->id(); // ADD THIS LINE
        $data['is_available'] = true;

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $image = $request->file('cover_image');
            $filename = time() . '_' . Str::slug($request->title) . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('pdf_covers', $filename, 'public');
            $data['cover_image'] = $path;
        }

        $book = PdfBook::create($data);

        // Log admin action (optional)
        \Log::info('PDF Book uploaded by manager', [
            'admin_id' => auth('admin')->id(),
            'admin_name' => auth('admin')->user()->name,
            'book_id' => $book->id,
            'book_title' => $book->title,
            'seller_id' => $request->user_id,
        ]);

        return redirect()
            ->route('admin.pdf-manager.index')
            ->with('success', 'PDF book uploaded successfully!');
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $book = PdfBook::findOrFail($id);
        
        // Check if manager has permission to edit this book
        $admin = auth('admin')->user();
        if ($admin->role === 'manager' && $book->uploaded_by_admin_id != $admin->id) {
            abort(403, 'You do not have permission to edit this book.');
        }

        $users = User::whereNotNull('email_verified_at')
            ->orderBy('name')
            ->get();

        return view('admin.pdf-manager.edit', compact('book', 'users'));
    }

    /**
     * Update PDF book
     */
    public function update(Request $request, $id)
    {
        $book = PdfBook::findOrFail($id);

        // Check permission
        $admin = auth('admin')->user();
        if ($admin->role === 'manager' && $book->uploaded_by_admin_id != $admin->id) {
            abort(403, 'You do not have permission to edit this book.');
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'isbn' => "nullable|string|max:255|unique:pdf_books,isbn,{$id},id,deleted_at,NULL",
            'description' => 'nullable|string',
            'publisher' => 'nullable|string|max:255',
            'publication_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'price' => 'required|numeric|min:0',
            'google_drive_file_id' => 'required|string',
            'google_drive_shareable_link' => 'nullable|url',
            'cover_image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'file_size' => 'nullable|integer',
            'total_pages' => 'nullable|integer',
            'language' => 'nullable|string|max:10',
            'is_available' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->only([
            'title', 'author', 'isbn', 'description', 'publisher',
            'publication_year', 'price', 'google_drive_file_id',
            'google_drive_shareable_link', 'file_size', 'total_pages', 'language'
        ]);

        $data['seller_id'] = $request->user_id;
        $data['is_available'] = $request->has('is_available');

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            // Delete old image
            if ($book->cover_image && \Storage::disk('public')->exists($book->cover_image)) {
                \Storage::disk('public')->delete($book->cover_image);
            }

            $image = $request->file('cover_image');
            $filename = time() . '_' . Str::slug($request->title) . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('pdf_covers', $filename, 'public');
            $data['cover_image'] = $path;
        }

        $book->update($data);

        return redirect()
            ->route('admin.pdf-manager.index')
            ->with('success', 'PDF book updated successfully!');
    }

    /**
     * Delete PDF book
     */
    public function destroy($id)
    {
        $book = PdfBook::findOrFail($id);

        // Check permission
        $admin = auth('admin')->user();
        if ($admin->role === 'manager' && $book->uploaded_by_admin_id != $admin->id) {
            abort(403, 'You do not have permission to delete this book.');
        }

        // Delete cover image
        if ($book->cover_image && \Storage::disk('public')->exists($book->cover_image)) {
            \Storage::disk('public')->delete($book->cover_image);
        }

        $book->delete();

        return redirect()
            ->route('admin.pdf-manager.index')
            ->with('success', 'PDF book deleted successfully!');
    }

    /**
     * Bulk upload form
     */
    public function bulkCreate()
    {
        $users = User::whereNotNull('email_verified_at')
            ->orderBy('name')
            ->get();

        return view('admin.pdf-manager.bulk-upload', compact('users'));
    }

    /**
     * Store bulk uploads
     */
    public function bulkStore(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'books' => 'required|array|min:1',
            'books.*.title' => 'required|string',
            'books.*.google_drive_file_id' => 'required|string',
            'books.*.price' => 'required|numeric|min:0',
        ]);

        $successCount = 0;
        $errors = [];

        foreach ($request->books as $index => $bookData) {
            try {
                PdfBook::create([
                    'seller_id' => $request->user_id,
                    'uploaded_by_admin_id' => auth('admin')->id(), // ADD THIS LINE
                    'title' => $bookData['title'],
                    'author' => $bookData['author'] ?? null,
                    'price' => $bookData['price'],
                    'google_drive_file_id' => $bookData['google_drive_file_id'],
                    'google_drive_shareable_link' => $bookData['google_drive_shareable_link'] ?? null,
                    'description' => $bookData['description'] ?? null,
                    'is_available' => true,
                ]);
                $successCount++;
            } catch (\Exception $e) {
                $errors[] = "Book #" . ($index + 1) . ": " . $e->getMessage();
            }
        }

        if ($successCount > 0) {
            $message = "$successCount book(s) uploaded successfully!";
            if (!empty($errors)) {
                $message .= " " . count($errors) . " failed.";
            }
            return redirect()
                ->route('admin.pdf-manager.index')
                ->with('success', $message);
        }

        return back()->withErrors($errors)->withInput();
    }
}
