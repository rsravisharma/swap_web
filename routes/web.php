<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Models\ItemImage;
use App\Http\Controllers\{
    EmailVerificationController,
    DeletionRequestController,
    SupportController,
};

use App\Http\Controllers\Web\{
    HomeController,
    AboutController,
    FeaturesController,
    HowItWorksController,
    BlogController,
    NewsletterController,
    ContactController,
    PdfBookController,
    AuthController
};

use App\Http\Controllers\Admin\{
    AdminAuthController,
    AdminDashboardController,
    UserAnalyticsController,
    ItemAnalyticsController,
    PdfBookAnalyticsController,
    PdfManagerController,
};

// Admin Authentication Routes (outside admin middleware)
Route::prefix('admin')->name('admin.')->group(function () {

    Route::get('/', function () {
        $admin = Auth::guard('admin')->user();

        if ($admin->role === 'manager') {
            return redirect()->route('admin.pdf-manager.index');
        }

        return redirect()->route('admin.dashboard');
    })->name('admin.home');


    // Guest routes (not logged in)
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    });

    // Authenticated admin routes
    Route::middleware(['admin'])->group(function () {

        // Logout (available to all admin roles)
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        Route::get('/categories/{parentId}/children', [PdfManagerController::class, 'getChildren'])->name('admin.categories.children');
        // PDF Manager Routes (accessible by manager and super_admin)
        Route::middleware(['role:manager,super_admin'])->prefix('pdf-manager')->name('pdf-manager.')->group(function () {
            Route::get('/', [PdfManagerController::class, 'index'])->name('index');
            Route::get('/create', [PdfManagerController::class, 'create'])->name('create');
            Route::post('/store', [PdfManagerController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [PdfManagerController::class, 'edit'])->name('edit');
            Route::put('/{id}', [PdfManagerController::class, 'update'])->name('update');
            Route::delete('/{id}', [PdfManagerController::class, 'destroy'])->name('destroy');

            // Bulk upload
            Route::get('/bulk-upload', [PdfManagerController::class, 'bulkCreate'])->name('bulk-create');
            Route::post('/bulk-upload', [PdfManagerController::class, 'bulkStore'])->name('bulk-store');
        });

        // Super Admin Only Routes
        Route::middleware(['role:super_admin'])->group(function () {

            // Dashboard
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

            // Analytics routes
            Route::get('/analytics', [UserAnalyticsController::class, 'index'])->name('analytics.index');
            Route::get('/analytics/users', [UserAnalyticsController::class, 'userList'])->name('analytics.users');
            Route::get('/analytics/users/{id}', [UserAnalyticsController::class, 'userDetails'])->name('analytics.user.details');
            Route::get('/analytics/violations', [UserAnalyticsController::class, 'violations'])->name('analytics.violations');
            Route::get('/analytics/export', [UserAnalyticsController::class, 'exportUsers'])->name('analytics.export');

            // Items Management
            Route::get('/items', [ItemAnalyticsController::class, 'index'])->name('items.index');
            Route::get('/items/list', [ItemAnalyticsController::class, 'itemList'])->name('items.list');
            Route::get('/items/{id}', [ItemAnalyticsController::class, 'itemDetails'])->name('items.details');
            Route::post('/items/{id}/status', [ItemAnalyticsController::class, 'updateStatus'])->name('items.update-status');
            Route::delete('/items/{id}', [ItemAnalyticsController::class, 'destroy'])->name('items.destroy');
            Route::post('/items/bulk-action', [ItemAnalyticsController::class, 'bulkAction'])->name('items.bulk-action');
            Route::get('/items-export', [ItemAnalyticsController::class, 'export'])->name('items.export');

            // PDF Books Analytics (full access)
            Route::get('/pdf-books', [PdfBookAnalyticsController::class, 'index'])->name('pdf-books.index');
            Route::get('/pdf-books/list', [PdfBookAnalyticsController::class, 'booksList'])->name('pdf-books.list');
            Route::get('/pdf-books/{id}', [PdfBookAnalyticsController::class, 'bookDetails'])->name('pdf-books.details');
            Route::post('/pdf-books/{id}/availability', [PdfBookAnalyticsController::class, 'updateAvailability'])->name('pdf-books.update-availability');
            Route::delete('/pdf-books/{id}', [PdfBookAnalyticsController::class, 'destroy'])->name('pdf-books.destroy');

            // Purchases
            Route::get('/pdf-books-purchases', [PdfBookAnalyticsController::class, 'purchasesList'])->name('pdf-books.purchases');
            Route::post('/pdf-purchases/{id}/revoke', [PdfBookAnalyticsController::class, 'revokePurchase'])->name('pdf-purchases.revoke');
            Route::post('/pdf-purchases/{id}/extend', [PdfBookAnalyticsController::class, 'extendPurchase'])->name('pdf-purchases.extend');

            // Export
            Route::get('/pdf-books-export', [PdfBookAnalyticsController::class, 'exportBooks'])->name('pdf-books.export');
            Route::get('/pdf-purchases-export', [PdfBookAnalyticsController::class, 'exportPurchases'])->name('pdf-purchases.export');
        });
    });
});

// User Authentication Routes
Route::middleware('guest')->group(function () {

    Route::get('/register', function () {
        return view('auth.user-register');
    })->name('user.register');

    Route::post('/register', [AuthController::class, 'register'])->name('user.register.submit');

    Route::get('/login', function () {
        return view('auth.user-login');
    })->name('user.login');

    Route::post('/login', [AuthController::class, 'login'])->name('user.login.submit');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('user.logout');
});



Route::middleware('auth')->prefix('pdf-books')->name('pdf-books.')->group(function () {
    Route::get('/my-library', [PdfBookController::class, 'myLibrary'])->name('my-library');
    Route::get('/purchase/{purchase}/download', [PdfBookController::class, 'download'])->name('download');
});

Route::prefix('pdf-books')->name('pdf-books.')->group(function () {
    Route::get('/', [PdfBookController::class, 'index'])->name('index');
    Route::get('/category/{pdfCategory}', [PdfBookController::class, 'category'])->name('category');
    Route::get('/categories', [PdfBookController::class, 'categories'])->name('categories');
    Route::post('/{pdfBook}/initiate-payment', [PdfBookController::class, 'initiatePayment'])->name('initiate-payment');
    Route::post('/payment/verify', [PdfBookController::class, 'verifyPayment'])->name('verify-payment');
    Route::get('/{pdfBook}', [PdfBookController::class, 'show'])->name('show');
});

// Homepage
Route::get('/', [HomeController::class, 'index'])->name('home');

// Main pages
Route::get('/about', [AboutController::class, 'index'])->name('about');
Route::get('/features', [FeaturesController::class, 'index'])->name('features');
Route::get('/how-it-works', [HowItWorksController::class, 'index'])->name('how-it-works');

// Blog
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

// Newsletter
Route::post('/newsletter', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');

// Email verification
Route::get('/email/verify', [EmailVerificationController::class, 'notice'])
    ->middleware('auth')
    ->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['auth', 'signed'])
    ->name('verification.verify');

Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

Route::post('/email/verify-otp', [EmailVerificationController::class, 'verifyOtp'])
    ->middleware('auth')
    ->name('verification.verify-otp');

// Legal & Support
Route::get('/privacy-policy', function () {
    return view('frontend.legal_support.privacy');
})->name('privacy-policy');

Route::get('/terms-and-conditions', [SupportController::class, 'termsAndConditions'])->name('termsAndConditions');

Route::get('/faq', [SupportController::class, 'faq'])->name('faq');
Route::post('/report-issue', [SupportController::class, 'reportIssue'])->name('report.issue');

// Contact page
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');

Route::get('/safety', function () {
    return view('frontend.safety.index');
})->name('safety');

// Data deletion routes
Route::get('/deletion-request', [DeletionRequestController::class, 'index'])->name('deletion.request');
Route::post('/deletion-request', [DeletionRequestController::class, 'store'])->name('deletion.store');
Route::get('/deletion-request/verify/{token}', [DeletionRequestController::class, 'verify'])->name('deletion.verify');
Route::get('/deletion-status', function () {
    return view('frontend.legal_support.deletion-status');
})->name('deletion.status');
Route::post('/deletion-status', [DeletionRequestController::class, 'status'])->name('deletion.status.check');

// Item images
Route::get('/item-image/{id}', function ($id) {
    $image = ItemImage::findOrFail($id);
    $path = storage_path('app/' . $image->image_path);

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path);
})->name('item.image');

// Cache clearing (remove this in production!)
Route::get('/clear-cache', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('optimize');
    return "Cache cleared!";
});
