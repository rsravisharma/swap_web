<?php

use Illuminate\Support\Facades\Route;
use App\Models\ItemImage;
use App\Http\Controllers\{
    EmailVerificationController,
    DeletionRequestController,
};

Route::get('/', function () {
    return view('welcome');
});


// Email verification notice
Route::get('/email/verify', [EmailVerificationController::class, 'notice'])
    ->middleware('auth')
    ->name('verification.notice');

// Email verification handler (for link clicks)
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['auth', 'signed'])
    ->name('verification.verify');

// Resend verification email/OTP
Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

// OTP verification (for API/AJAX)
Route::post('/email/verify-otp', [EmailVerificationController::class, 'verifyOtp'])
    ->middleware('auth')
    ->name('verification.verify-otp');



// Legal & Support
Route::get('/privacy-policy', function () {
    return view('frontend.legal_support.privacy');
});

Route::post('/deletion-request', 'DeletionRequestController@index');
Route::get('/termsAndConditions', 'SupportController@termsAndConditions');
Route::post('/reportIssue', 'SupportController@reportIssue');
Route::get('/faq', 'SupportController@faq');
Route::post('/contactUs', 'SupportController@contactUs');

Route::get('/item-image/{id}', function($id) {
    $image = ItemImage::findOrFail($id);
    $path = storage_path('app/' . $image->image_path);
    
    if (!file_exists($path)) {
        abort(404);
    }
    
    return response()->file($path);
})->name('item.image');

Route::get('/clear-cache', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('optimize');
    return "Cache cleared!";
});