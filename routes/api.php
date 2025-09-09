<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    AuthController,
    CategoryController,
    ProductController,
    SearchController,
    LocationController,
    ChatController,
    CommunicationController,
    OfferController,
    PaymentController,
    SocialController,
    SafetyController,
    NotificationController,
    HistoryController,
    SupportController,
    ItemController,
    LegalController,
    ProfileController,
    SettingsController
};

// ================================
// PUBLIC ROUTES (No Authentication)
// ================================

// Authentication Routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('google', [AuthController::class, 'googleSignIn']);
    Route::post('facebook', [AuthController::class, 'fbSignIn']);
    Route::post('phone/send-otp', [AuthController::class, 'phoneSignIn']);
    Route::post('phone/verify-otp', [AuthController::class, 'phoneVerify']);
    Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('resend-otp', [AuthController::class, 'resendOtp']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::get('country-codes', [AuthController::class, 'phoneCountryCode']);
});

// Category Routes (Public)
Route::prefix('categories')->group(function () {
    Route::get('courses', [CategoryController::class, 'getCourses']);
    Route::get('entry-categories', [CategoryController::class, 'getEntryCategories']);
    Route::get('semesters', [CategoryController::class, 'getSemesters']);
    Route::get('subjects', [CategoryController::class, 'getSubjects']);
    Route::get('universities', [CategoryController::class, 'getUniversities']);
});

// Location Routes (Public)
Route::prefix('location')->group(function () {
    Route::get('countries', [LocationController::class, 'getCountries']);
    Route::get('cities', [LocationController::class, 'getCities']);
    Route::get('reverse-geocode', [LocationController::class, 'reverseGeocode']);
    Route::get('all', [LocationController::class, 'getAllLocations']);
    Route::get('campus', [LocationController::class, 'getCampusLocations']);
    Route::get('popular', [LocationController::class, 'getPopularLocations']);
    Route::get('meetup', [LocationController::class, 'getMeetupLocations']);
    Route::get('search', [LocationController::class, 'searchLocations']);
    Route::get('nearby', [LocationController::class, 'getNearbyLocations']);
});

// Legal Routes (Public)
Route::prefix('legal')->group(function () {
    Route::get('privacy-policy', [LegalController::class, 'getPrivacyPolicy']);
    Route::get('terms-and-conditions', [LegalController::class, 'getTermsAndConditions']);
    Route::get('document/{documentType}', [LegalController::class, 'getLegalDocument']);
});

// ================================
// PROTECTED ROUTES (Authentication Required)
// ================================

Route::middleware('auth:sanctum')->group(function () {

    // ================================
    // AUTH PROTECTED ROUTES
    // ================================
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::put('password', [AuthController::class, 'updatePassword']);
        Route::post('verify-email', [AuthController::class, 'verifyEmail']);
    });

    Route::post('user/fcm-token', [AuthController::class, 'updateUserFcmToken']);

    // ================================
    // CHAT & COMMUNICATION ROUTES
    // ================================
    Route::prefix('chat')->group(function () {
        Route::post('session', [ChatController::class, 'startSession']);
        Route::get('sessions', [ChatController::class, 'getUserSessions']);
        Route::delete('session/{sessionId}', [ChatController::class, 'deleteSession']);
        Route::post('message', [ChatController::class, 'sendMessage']);
        Route::get('session/{sessionId}/messages', [ChatController::class, 'getMessages']);
        Route::post('session/{sessionId}/read', [ChatController::class, 'markAsRead']);
    });

    Route::prefix('chats')->group(function () {
        Route::get('/', [CommunicationController::class, 'getChats']);
        Route::delete('{chatId}', [CommunicationController::class, 'deleteChat']);
        Route::put('{chatId}/mark-read', [CommunicationController::class, 'markChatAsRead']);
        Route::put('{chatId}/archive', [CommunicationController::class, 'updateChatArchiveStatus']);

        Route::get('{chatId}/messages', [CommunicationController::class, 'getChatMessages']);
        Route::post('{chatId}/messages', [CommunicationController::class, 'sendMessage']);
        Route::delete('{chatId}/messages/{messageId}', [CommunicationController::class, 'deleteMessage']);
        Route::put('{chatId}/messages/{messageId}', [CommunicationController::class, 'editMessage']);
        Route::get('{chatId}/messages/search', [CommunicationController::class, 'searchMessages']);

        Route::post('{chatId}/typing', [CommunicationController::class, 'sendTypingIndicator']);

        Route::get('{chatId}/offers', [CommunicationController::class, 'getOfferHistory']);
        Route::post('{chatId}/offers', [CommunicationController::class, 'sendOffer']);
        Route::put('{chatId}/offers/{messageId}/accept', [CommunicationController::class, 'acceptOffer']);
        Route::put('{chatId}/offers/{messageId}/reject', [CommunicationController::class, 'rejectOffer']);

        Route::post('{chatId}/report', [CommunicationController::class, 'reportChat']);
    });

    Route::post('upload/chat-image', [CommunicationController::class, 'uploadChatImage']);
    Route::post('upload/chat-files', [CommunicationController::class, 'uploadChatFiles']);
    Route::get('chats/unread-count', [CommunicationController::class, 'getUnreadMessageCount']);
    Route::get('ping', [CommunicationController::class, 'ping']);

    // ================================
    // NOTIFICATION ROUTES
    // ================================
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'getNotifications']);
        Route::put('{id}/read', [NotificationController::class, 'markAsRead']);
        Route::put('mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('{id}', [NotificationController::class, 'deleteNotification']);
        Route::delete('clear-all', [NotificationController::class, 'clearAllNotifications']);
        Route::post('test', [NotificationController::class, 'testNotification']);

        Route::post('token', [NotificationController::class, 'updateToken']);
        Route::get('preferences', [NotificationController::class, 'getPreferences']);
        Route::put('preferences', [NotificationController::class, 'updatePreferences']);
        Route::post('subscribe', [NotificationController::class, 'subscribeToTopic']);
        Route::post('unsubscribe', [NotificationController::class, 'unsubscribeFromTopic']);
    });

    Route::post('notifications/send-topic', [NotificationController::class, 'sendTopicNotification'])
        ->middleware('admin');

    // ================================
    // HISTORY ROUTES
    // ================================
    Route::prefix('user/history')->group(function () {
        Route::get('/', [HistoryController::class, 'index']);
        Route::post('/', [HistoryController::class, 'store']);
        Route::get('stats', [HistoryController::class, 'getStats']);
        Route::get('categories', [HistoryController::class, 'getCategories']);
        Route::delete('/', [HistoryController::class, 'clear']);
        Route::delete('bulk', [HistoryController::class, 'bulkDelete']);
        Route::get('{id}', [HistoryController::class, 'show']);
        Route::delete('{id}', [HistoryController::class, 'destroy']);
    });

    // ================================
    // ITEMS ROUTES
    // ================================
    Route::prefix('items')->group(function () {
        Route::get('/', [ItemController::class, 'index']);
        Route::post('/', [ItemController::class, 'store']);
        Route::get('search', [ItemController::class, 'search']);
        Route::get('{id}', [ItemController::class, 'show']);
        Route::put('{id}', [ItemController::class, 'update']);
        Route::delete('{id}', [ItemController::class, 'destroy']);
        Route::post('{id}/promote', [ItemController::class, 'promote']);
        Route::post('{id}/mark-sold', [ItemController::class, 'markAsSold']);
        Route::post('{id}/archive', [ItemController::class, 'archive']);
    });

    // ================================
    // LEGAL PROTECTED ROUTES
    // ================================
    Route::prefix('legal')->group(function () {
        Route::post('agreement', [LegalController::class, 'submitLegalAgreement']);
        Route::post('accept-terms', [LegalController::class, 'acceptTermsAndConditions']);
        Route::post('accept-privacy', [LegalController::class, 'acceptPrivacyPolicy']);
    });

    Route::get('user/agreements', [LegalController::class, 'getUserAgreements']);

    // ================================
    // LOCATION PROTECTED ROUTES
    // ================================
    Route::prefix('user')->group(function () {
        Route::get('recent-locations', [LocationController::class, 'getRecentLocations']);
        Route::post('recent-locations', [LocationController::class, 'saveRecentLocation']);
    });

    Route::post('location/custom', [LocationController::class, 'addCustomLocation']);

    // ================================
    // OFFER & BASKET ROUTES
    // ================================
    Route::prefix('basket')->group(function () {
        Route::get('items', [OfferController::class, 'getBasketItems']);
        Route::delete('items/{basketItemId}', [OfferController::class, 'removeBasketItem']);
        Route::post('remove-multiple', [OfferController::class, 'removeMultipleBasketItems']);
        Route::delete('clear', [OfferController::class, 'clearBasket']);
    });

    Route::post('wishlist/add', [OfferController::class, 'moveToWishlist']);
    Route::get('delivery/options', [OfferController::class, 'getDeliveryOptions']);
    Route::post('checkout', [OfferController::class, 'processCheckout']);

    Route::prefix('orders')->group(function () {
        Route::get('/', [OfferController::class, 'getOrders']);
        Route::get('{orderId}', [OfferController::class, 'getOrderDetails']);
        Route::get('{orderId}/tracking', [OfferController::class, 'getOrderTracking']);
        Route::put('{orderId}/cancel', [OfferController::class, 'cancelOrder']);
    });

    Route::prefix('offers')->group(function () {
        Route::get('/', [OfferController::class, 'getOffers']);
        Route::post('/', [OfferController::class, 'sendOffer']);
        Route::put('{offerId}/accept', [OfferController::class, 'acceptOffer']);
        Route::put('{offerId}/reject', [OfferController::class, 'rejectOffer']);
        Route::delete('{offerId}', [OfferController::class, 'cancelOffer']);
    });

    Route::prefix('study-material-requests')->group(function () {
        Route::get('/', [OfferController::class, 'getStudyMaterialRequests']);
        Route::post('/', [OfferController::class, 'createStudyMaterialRequest']);
        Route::delete('{requestId}', [OfferController::class, 'deleteStudyMaterialRequest']);
        Route::put('{requestId}/fulfill', [OfferController::class, 'markRequestFulfilled']);
    });

    Route::get('user/preferences', [OfferController::class, 'getUserPreferences']);

    // ================================
    // PAYMENT ROUTES
    // ================================
    Route::prefix('payment')->group(function () {
        Route::get('methods', [PaymentController::class, 'getPaymentMethods']);
        Route::get('saved-cards', [PaymentController::class, 'getSavedPaymentMethods']);
        Route::post('add-method', [PaymentController::class, 'addPaymentMethod']);
        Route::put('methods/{cardId}', [PaymentController::class, 'updatePaymentMethod']);
        Route::delete('methods/{cardId}', [PaymentController::class, 'deletePaymentMethod']);

        Route::post('process', [PaymentController::class, 'processPayment']);
        Route::post('validate-card', [PaymentController::class, 'validateCard']);

        Route::get('history', [PaymentController::class, 'getPaymentHistory']);
        Route::get('{paymentId}', [PaymentController::class, 'getPaymentDetails']);
        Route::post('{paymentId}/refund', [PaymentController::class, 'refundPayment']);
    });

    // ================================
    // PRODUCT ROUTES
    // ================================
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'getProducts']);
        Route::get('listings/{listingId}', [ProductController::class, 'getListingDetails']);
        Route::put('listings/{listingId}', [ProductController::class, 'updateListing']);

        Route::get('favorites', [ProductController::class, 'getFavoriteProducts']);
        Route::post('{productId}/toggle-favorite', [ProductController::class, 'toggleFavorite']);
        Route::delete('favorites/clear', [ProductController::class, 'clearAllFavorites']);

        Route::get('my-listings', [ProductController::class, 'getMyListings']);
        Route::delete('listings/{itemId}', [ProductController::class, 'deleteListing']);
        Route::patch('listings/{itemId}/status', [ProductController::class, 'updateListingStatus']);

        Route::get('my-purchases', [ProductController::class, 'getMyPurchases']);
        Route::post('purchases/{purchaseId}/cancel', [ProductController::class, 'cancelOrder']);

        Route::get('{productId}/related', [ProductController::class, 'getRelatedProducts']);
    });

    // ================================
    // PROFILE ROUTES
    // ================================
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'getProfile']);
        Route::put('/', [ProfileController::class, 'updateProfile']);
        Route::get('stats/{userId?}', [ProfileController::class, 'getUserStats']);
        Route::get('earnings', [ProfileController::class, 'getEarningsHistory']);

        Route::get('student', [ProfileController::class, 'getStudentProfile']);
        Route::post('upload-student-id', [ProfileController::class, 'uploadStudentId']);
        Route::post('verification', [ProfileController::class, 'submitStudentVerification']);

        Route::get('wishlist', [ProfileController::class, 'getWishlist']);
        Route::delete('wishlist/{itemId}', [ProfileController::class, 'removeFromWishlist']);
    });

    // ================================
    // SAFETY ROUTES
    // ================================
    Route::prefix('safety')->group(function () {
        Route::get('blocked-users', [SafetyController::class, 'getBlockedUsers']);
        Route::post('block-user', [SafetyController::class, 'blockUser']);
        Route::post('unblock-user', [SafetyController::class, 'unblockUser']);
        Route::post('report-user', [SafetyController::class, 'reportUser']);
        Route::post('report-item', [SafetyController::class, 'reportItem']);
        Route::get('stats', [SafetyController::class, 'getSafetyStats']);
        Route::get('is-blocked/{userId}', [SafetyController::class, 'isUserBlocked']);
    });

    // ================================
    // SEARCH ROUTES
    // ================================
    Route::prefix('search')->group(function () {
        Route::get('all', [SearchController::class, 'getAllSearchResults']);
        Route::get('items', [SearchController::class, 'searchItems']);
        Route::get('users', [SearchController::class, 'searchUsers']);
        Route::post('filtered', [SearchController::class, 'getFilteredProducts']);

        Route::get('categories', [SearchController::class, 'getCategoriesWithCounts']);
        Route::get('categories/{categoryId}/subcategories', [SearchController::class, 'getSubCategories']);

        Route::prefix('filters')->group(function () {
            Route::get('locations', [SearchController::class, 'getAvailableLocations']);
            Route::get('universities', [SearchController::class, 'getAvailableUniversities']);
        });

        Route::get('suggestions', [SearchController::class, 'getSearchSuggestions']);
        Route::get('quick-suggestions', [SearchController::class, 'getQuickSuggestions']);
        Route::get('trending', [SearchController::class, 'getTrendingSearches']);
        Route::get('popular', [SearchController::class, 'getPopularSearches']);

        Route::prefix('history')->group(function () {
            Route::get('recent', [SearchController::class, 'getRecentSearches']);
            Route::post('save', [SearchController::class, 'saveSearchHistory']);
            Route::delete('clear', [SearchController::class, 'clearSearchHistory']);
        });

        Route::post('favorites/toggle', [SearchController::class, 'toggleFavorite']);
        Route::post('users/follow/toggle', [SearchController::class, 'toggleFollowUser']);
    });

    // ================================
    // SETTINGS ROUTES
    // ================================
    Route::prefix('user/settings')->group(function () {
        Route::get('language', [SettingsController::class, 'getUserLanguage']);
        Route::post('language', [SettingsController::class, 'saveUserLanguage']);

        Route::get('/', [SettingsController::class, 'getAllSettings']);
        Route::put('/', [SettingsController::class, 'updateSettings']);
        Route::delete('/', [SettingsController::class, 'resetAllSettings']);

        Route::get('{key}', [SettingsController::class, 'getSetting']);
        Route::put('{key}', [SettingsController::class, 'updateSetting']);
        Route::delete('{key}', [SettingsController::class, 'deleteSetting']);

        Route::get('options', [SettingsController::class, 'getSettingsOptions']);
    });

    // ================================
    // SOCIAL ROUTES
    // ================================
    Route::prefix('users')->group(function () {
        Route::get('{userId}/followers', [SocialController::class, 'getFollowers']);
        Route::get('{userId}/following', [SocialController::class, 'getFollowing']);
        Route::post('{userId}/toggle-follow', [SocialController::class, 'toggleFollow']);
        Route::delete('followers/{userId}', [SocialController::class, 'removeFollower']);

        Route::get('{userId}/ratings', [SocialController::class, 'getUserRatings']);
        Route::get('{userId}', [ProfileController::class, 'getUserDetails']);
        Route::post('{userId}/follow', [ProfileController::class, 'toggleFollow']);
        Route::post('{userId}/block', [ProfileController::class, 'toggleBlock']);
        Route::get('{userId}/items', [ProductController::class, 'getUserItems']);

        Route::get('top-sellers', [SocialController::class, 'getTopSellers']);
    });

    Route::post('ratings', [SocialController::class, 'submitRating']);
    Route::post('ratings/{ratingId}/helpful', [SocialController::class, 'markRatingHelpful']);
    Route::post('ratings/{ratingId}/report', [SocialController::class, 'reportRating']);

    Route::get('transactions/{transactionId}', [SocialController::class, 'getTransactionDetails']);

    // ================================
    // SUPPORT ROUTES
    // ================================
    Route::get('user/profile', [SupportController::class, 'getUserProfile']);

    Route::prefix('support')->group(function () {
        Route::post('requests', [SupportController::class, 'submitSupportRequest']);
        Route::get('requests/my-requests', [SupportController::class, 'getMySupportRequests']);
        Route::get('requests/{requestId}', [SupportController::class, 'getSupportRequestDetails']);
        Route::post('requests/{requestId}/rate', [SupportController::class, 'rateSupportExperience']);

        Route::get('faqs', [SupportController::class, 'getFaqs']);
        Route::get('faqs/search', [SupportController::class, 'searchFaqs']);
        Route::post('faqs/{faqId}/helpful', [SupportController::class, 'markFaqHelpful']);

        Route::post('feedback', [SupportController::class, 'submitFeedback']);
        Route::get('contact', [SupportController::class, 'getContactInfo']);

        Route::get('app-info', [SupportController::class, 'getAppInfo']);
        Route::get('system-status', [SupportController::class, 'getSystemStatus']);
        Route::get('announcements', [SupportController::class, 'getAnnouncements']);

        Route::post('bug-reports', [SupportController::class, 'submitBugReport']);
        Route::post('feature-requests', [SupportController::class, 'submitFeatureRequest']);

        Route::get('popular-topics', [SupportController::class, 'getPopularTopics']);
    });

    // User blocking/unblocking (from communication controller)
    Route::post('users/{userId}/block', [CommunicationController::class, 'blockUser']);
    Route::post('users/{userId}/unblock', [CommunicationController::class, 'unblockUser']);

    // Cache clearing route (if needed)
    Route::post('clear-cache', [CategoryController::class, 'clearCache']);
});
