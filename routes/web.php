<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    EmailVerificationController
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






// Core App Routes
Route::get('/splash', 'AppController@splash');
Route::get('/home', 'AppController@home');
Route::get('/introSlider', 'AppController@introSlider');
Route::get('/forceUpdate', 'AppController@forceUpdate');
Route::get('/settings', 'AppController@settings');
Route::get('/more', 'AppController@more');
Route::get('/appInfo', 'AppController@appInfo');
Route::get('/languageSetting', 'AppController@languageSetting');
Route::get('/accountSetting', 'AppController@accountSetting');

// Authentication Routes
Route::post('/login', 'AuthController@login');
Route::post('/register', 'AuthController@register');
Route::post('/forgotPassword', 'AuthController@forgotPassword');
Route::post('/phoneSignIn', 'AuthController@phoneSignIn');
Route::post('/phoneVerify', 'AuthController@phoneVerify');
Route::post('/fbSignIn', 'AuthController@fbSignIn');
Route::post('/googleSignIn', 'AuthController@googleSignIn');
Route::post('/updatePassword', 'AuthController@updatePassword');
Route::post('/verifyEmail', 'AuthController@verifyEmail');
Route::get('/phoneCountryCode', 'AuthController@phoneCountryCode');

// Profile & User Management
Route::get('/profile', 'UserController@profile');
Route::post('/editProfile', 'UserController@editProfile');
Route::get('/userDetail', 'UserController@userDetail');
Route::get('/studentProfile', 'UserController@studentProfile');
Route::post('/verifyStudent', 'UserController@verifyStudent');
Route::post('/uploadStudentId', 'UserController@uploadStudentId');
Route::get('/wishlist', 'UserController@wishlist');
Route::get('/earningsHistory', 'UserController@earningsHistory');

// Academic Categories
Route::get('/categoryList', 'CategoryController@categoryList');
Route::get('/subCategoryList', 'CategoryController@subCategoryList');
Route::get('/subjectList', 'CategoryController@subjectList');
Route::get('/courseList', 'CategoryController@courseList');
Route::get('/universityList', 'CategoryController@universityList');
Route::get('/semesterList', 'CategoryController@semesterList');
Route::get('/entryCategoryList', 'CategoryController@entryCategoryList');

// Products/Books/Notes
Route::get('/productList', 'ProductController@productList');
Route::get('/productDetail', 'ProductController@productDetail');
Route::get('/productGrid', 'ProductController@productGrid');
Route::get('/userItemList', 'ProductController@userItemList');
Route::get('/myListings', 'ProductController@myListings');
Route::post('/editListing', 'ProductController@editListing');
Route::get('/myPurchasedBooks', 'ProductController@myPurchasedBooks');
Route::get('/relatedProductList', 'ProductController@relatedProductList');
Route::get('/favouriteProductList', 'ProductController@favouriteProductList');

// Search & Filter
Route::get('/itemSearch', 'SearchController@itemSearch');
Route::get('/homeItemSearch', 'SearchController@homeItemSearch');
Route::get('/filterProductList', 'SearchController@filterProductList');
Route::get('/filterList', 'SearchController@filterList');
Route::get('/categoryFilterList', 'SearchController@categoryFilterList');
Route::get('/itemSort', 'SearchController@itemSort');
Route::get('/searchCategory', 'SearchController@searchCategory');
Route::get('/searchSubCategory', 'SearchController@searchSubCategory');
Route::get('/searchUser', 'SearchController@searchUser');
Route::get('/allSearchResult', 'SearchController@allSearchResult');
Route::get('/searchHistoryList', 'SearchController@searchHistoryList');

// Item Entry & Listing
Route::post('/addItem', 'ProductController@addItem');
Route::get('/itemType', 'ProductController@itemType');
Route::get('/itemCondition', 'ProductController@itemCondition');
Route::get('/bookConditionList', 'ProductController@bookConditionList');
Route::get('/itemPriceType', 'ProductController@itemPriceType');
Route::get('/itemCurrencySymbol', 'ProductController@itemCurrencySymbol');
Route::get('/itemDealOption', 'ProductController@itemDealOption');
Route::post('/itemSoldOut', 'ProductController@itemSoldOut');
Route::get('/choosePrice', 'ProductController@choosePrice');

// Location
Route::get('/itemLocation', 'LocationController@itemLocation');
Route::get('/itemLocationList', 'LocationController@itemLocationList');
Route::get('/countryList', 'LocationController@countryList');
Route::get('/cityList', 'LocationController@cityList');
Route::get('/meetupLocation', 'LocationController@meetupLocation');

// Communication
Route::get('/chatList', 'ChatController@chatList');
Route::get('/chatDetail', 'ChatController@chatDetail');
Route::get('/chatImageDetail', 'ChatController@chatImageDetail');
Route::post('/bookOfferNegotiation', 'ChatController@bookOfferNegotiation');

// Offers & Transactions
Route::get('/offerList', 'OfferController@offerList');
Route::post('/makeOffer', 'OfferController@makeOffer');
Route::get('/basket', 'OrderController@basket');
Route::post('/checkout', 'OrderController@checkout');
Route::get('/checkoutSuccess', 'OrderController@checkoutSuccess');
Route::get('/orderHistory', 'OrderController@orderHistory');
Route::get('/orderDetail', 'OrderController@orderDetail');
Route::get('/orderTracking', 'OrderController@orderTracking');
Route::post('/leaveReview', 'OrderController@leaveReview');
Route::post('/refundRequest', 'OrderController@refundRequest');
Route::get('/studyMaterialRequests', 'RequestController@studyMaterialRequests');
Route::post('/createRequest', 'RequestController@createRequest');

// Payment
Route::get('/choosePayment', 'PaymentController@choosePayment');
Route::get('/paymentView', 'PaymentController@paymentView');
Route::post('/offlinePayment', 'PaymentController@offlinePayment');
Route::post('/creditCard', 'PaymentController@creditCard');

// Social Features
Route::get('/followingUsers', 'SocialController@followingUsers');
Route::get('/followers', 'SocialController@followers');
Route::get('/ratings', 'SocialController@ratings');
Route::post('/rateUser', 'SocialController@rateUser');
Route::get('/topSellers', 'SocialController@topSellers');

// Safety & Reporting
Route::get('/blockedUsers', 'SafetyController@blockedUsers');
Route::post('/reportItem', 'SafetyController@reportItem');
Route::post('/reportUser', 'SafetyController@reportUser');
Route::get('/safetyTips', 'SafetyController@safetyTips');

// Notifications
Route::get('/notifications', 'NotificationController@notifications');
Route::get('/notificationSettings', 'NotificationController@notificationSettings');

// Camera & Media
Route::post('/cameraView', 'MediaController@cameraView');

// History & Analytics
Route::get('/historyList', 'HistoryController@historyList');

// Language & Localization
Route::get('/languageList', 'AppController@languageList');

// Legal & Support
Route::get('/privacyPolicy', 'SupportController@privacyPolicy');
Route::get('/termsAndConditions', 'SupportController@termsAndConditions');
Route::post('/agreeTermsAndCondition', 'SupportController@agreeTermsAndCondition');
Route::post('/reportIssue', 'SupportController@reportIssue');
Route::get('/faq', 'SupportController@faq');
Route::post('/contactUs', 'SupportController@contactUs');

Route::get('/clear-cache', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('optimize');
    return "Cache cleared!";
});