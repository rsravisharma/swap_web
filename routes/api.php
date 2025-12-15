<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Services\EnhancedGeocodingService;
use Illuminate\Support\Facades\Auth;
use App\Models\ChatSession;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\{
    AuthController,
    AblyAuthController,
    HomeController,
    CategoryController,
    ItemSearchController,
    CategoryItemController,
    SearchController,
    LocationController,
    ChatController,
    MeetupController,
    CommunicationController,
    OfferController,
    PaymentController,
    SocialController,
    SafetyController,
    NotificationController,
    HistoryController,
    // SupportController,
    ItemController,
    LegalController,
    ProfileController,
    SettingsController,
    UserController
};

// ================================
// PUBLIC ROUTES (No Authentication)
// ================================

// Authentication Routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('google', [AuthController::class, 'googleSignIn']);
    Route::get('google/redirect', [AuthController::class, 'redirectToGoogle']);
    Route::get('google/callback', [AuthController::class, 'handleGoogleCallback']);
    Route::post('facebook', [AuthController::class, 'fbSignIn']);
    Route::post('phone/send-otp', [AuthController::class, 'phoneSignIn']);
    Route::post('phone/verify-otp', [AuthController::class, 'phoneVerify']);
    Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('resend-otp', [AuthController::class, 'resendOtp']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::get('country-codes', [AuthController::class, 'phoneCountryCode']);
    Route::post('ably-token', [AuthController::class, 'generateAblyToken']);
});

Route::prefix('items')->group(function () {
    Route::get('/popular', [HomeController::class, 'popular']);
    Route::get('/recent', [HomeController::class, 'recent']);
    Route::get('/trending', [HomeController::class, 'trending']);
    Route::get('/featured', [HomeController::class, 'featured']);
});

// Search endpoints (put early to avoid conflicts)
Route::get('/search/items', [ItemSearchController::class, 'search']);

// Category Routes (Public)
Route::prefix('categories')->group(function () {
    // Specific endpoints first
    Route::get('hierarchy', [CategoryController::class, 'getCategoryHierarchy']);
    Route::get('flat', [CategoryController::class, 'getFlatCategories']);
    Route::get('names', [CategoryController::class, 'getCategoryNames']);
    Route::get('path', [CategoryController::class, 'getCategoryPath']);
    Route::get('stats', [HomeController::class, 'categoryStats']);
    Route::delete('cache', [CategoryController::class, 'clearCache']);

    // Dynamic parameter routes last
    Route::get('{categoryId}/sub-categories', [CategoryController::class, 'getSubCategories']);
    Route::get('{categoryId}/items', [CategoryItemController::class, 'categoryItems']);
});

// Subcategory Routes
Route::prefix('subcategories')->group(function () {
    Route::get('{subcategoryId}/items', [CategoryItemController::class, 'subcategoryItems']);
    Route::get('{subcategoryId}/children', [CategoryController::class, 'getChildSubCategories']);
});

// Child Subcategory Routes  
Route::prefix('child-subcategories')->group(function () {
    Route::get('{childSubcategoryId}/items', [CategoryItemController::class, 'childSubcategoryItems']);
});

// Location Routes (Public)
Route::prefix('location')->group(function () {
    // Public location routes
    Route::get('countries', [LocationController::class, 'getCountries']);
    Route::get('cities', [LocationController::class, 'getCities']);
    Route::get('reverse-geocode', [LocationController::class, 'reverseGeocode']);
    Route::get('all', [LocationController::class, 'getAllLocations']);
    Route::get('campus', [LocationController::class, 'getCampusLocations']);
    Route::get('popular', [LocationController::class, 'getPopularLocations']);
    Route::get('meetup', [LocationController::class, 'getMeetupLocations']);
    Route::get('search', [LocationController::class, 'searchLocations']);
    Route::get('nearby', [LocationController::class, 'getNearbyLocations']);
    Route::get('universities', [LocationController::class, 'getUniversities']);
});

// University routes
Route::prefix('location/university')->group(function () {
    Route::get('/', [LocationController::class, 'getUniversities']);
    Route::get('/{identifier}', [LocationController::class, 'getUniversity']);
});

// Legal Routes (Public)
Route::prefix('legal')->group(function () {
    Route::get('privacy-policy', [LegalController::class, 'getPrivacyPolicy']);
    Route::get('terms-and-conditions', [LegalController::class, 'getTermsAndConditions']);
    Route::get('document/{documentType}', [LegalController::class, 'getLegalDocument']);
});

Route::get('/debug-ably-token', function () {
    try {
        $token = request()->header('X-Ably-Token');
        if (!$token) {
            return response()->json(['error' => 'No token provided']);
        }

        // Decode the token (if it's JWT)
        $parts = explode('.', $token);
        if (count($parts) === 3) {
            $payload = json_decode(base64_decode($parts[1]), true);
            return response()->json([
                'token_payload' => $payload,
                'capability' => $payload['x-ably-capability'] ?? 'not found'
            ]);
        }

        return response()->json(['error' => 'Invalid token format']);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
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
        Route::get('/debug/auth', [AuthController::class, 'checkAuth']);
    });

    Route::post('user/fcm-token', [AuthController::class, 'updateUserFcmToken']);

    Route::prefix('location')->group(function () {
        Route::get('user/recent', [LocationController::class, 'getRecentLocations']);
        Route::post('user/recent', [LocationController::class, 'saveRecentLocation']);
        Route::post('custom', [LocationController::class, 'addCustomLocation']);
        Route::get('geocoding-stats', [LocationController::class, 'getGeocodingStats']);
        Route::put('update/{id}', [LocationController::class, 'updateLocation']);
    });

    // University routes
    Route::prefix('location/university')->group(function () {
        Route::post('/', [LocationController::class, 'createUniversity']);
        Route::put('/{id}', [LocationController::class, 'updateUniversity']);
        Route::delete('/{id}', [LocationController::class, 'deleteUniversity']);
    });

    Route::prefix('auth')->group(function () {
        Route::get('ably-token', [AblyAuthController::class, 'getAblyToken']);
        Route::get('ably-config', [AblyAuthController::class, 'getAblyConfig']);
    });

    // ================================
    // CHAT & COMMUNICATION ROUTES
    // ================================
    Route::prefix('chat')->group(function () {
        Route::get('sessions', [ChatController::class, 'getUserSessions']);
        Route::post('session', [ChatController::class, 'startSession']);
        Route::post('store-ably-message', [ChatController::class, 'storeAblyMessage']);
        Route::get('session/{sessionId}/messages', [ChatController::class, 'getMessages']);
        Route::post('session/{sessionId}/read', [ChatController::class, 'markAsRead']);
        Route::delete('session/{sessionId}', [ChatController::class, 'deleteSession']);
    });

    Route::prefix('chats')->group(function () {
        Route::get('/', [CommunicationController::class, 'getChats']);
        Route::get('unread-count', [CommunicationController::class, 'getUnreadMessageCount']);

        Route::get('{chatId}/messages', [CommunicationController::class, 'getChatMessages']);
        Route::get('{chatId}/messages/search', [CommunicationController::class, 'searchMessages']);
        Route::get('{chatId}/offers', [CommunicationController::class, 'getOfferHistory']);

        Route::post('{chatId}/messages', [CommunicationController::class, 'sendMessage']);
        Route::post('{chatId}/offers', [CommunicationController::class, 'sendOffer']);
        Route::post('{chatId}/report', [CommunicationController::class, 'reportChat']);

        Route::put('{chatId}/mark-read', [CommunicationController::class, 'markChatAsRead']);
        Route::put('{chatId}/archive', [CommunicationController::class, 'updateChatArchiveStatus']);
        Route::put('{chatId}/messages/{messageId}', [CommunicationController::class, 'editMessage']);
        Route::put('{chatId}/offers/{messageId}/accept', [CommunicationController::class, 'acceptOffer']);
        Route::put('{chatId}/offers/{messageId}/reject', [CommunicationController::class, 'rejectOffer']);

        Route::delete('{chatId}', [CommunicationController::class, 'deleteChat']);
        Route::delete('{chatId}/messages/{messageId}', [CommunicationController::class, 'deleteMessage']);
    });

    Route::post('upload/chat-image', [CommunicationController::class, 'uploadChatImage']);
    Route::post('upload/chat-files', [CommunicationController::class, 'uploadChatFiles']);
    Route::get('ping', [CommunicationController::class, 'ping']);

    Route::post('/broadcasting/auth', function (Request $request) {
        try {
            $currentUserId = Auth::id();

            if (!$currentUserId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Get the channel name from request
            $channelName = $request->input('channel_name');

            if (!$channelName) {
                return response()->json(['error' => 'Channel name required'], 400);
            }

            // Verify user has access to this channel
            if (preg_match('/^private-chat\.(\d+)$/', $channelName, $matches)) {
                $chatId = $matches[1];

                // Check if user is participant in this chat
                $hasAccess = ChatSession::where('id', $chatId)
                    ->where(function ($query) use ($currentUserId) {
                        $query->where('user_one_id', $currentUserId)
                            ->orWhere('user_two_id', $currentUserId);
                    })
                    ->exists();

                if (!$hasAccess) {
                    return response()->json(['error' => 'Channel access denied'], 403);
                }
            }

            // Generate Ably token with channel-specific permissions
            $ably = new \Ably\AblyRest([
                'key' => config('services.ably.key')
            ]);

            $tokenDetails = $ably->auth->requestToken([
                'clientId' => (string) $currentUserId,
                'capability' => [
                    $channelName => ["*"], // Grant access to specific channel
                ],
                'ttl' => 3600000 // 1 hour
            ]);

            return response()->json([
                'token' => $tokenDetails->token,
                'expires' => $tokenDetails->expires,
                'clientId' => $tokenDetails->clientId,
            ]);
        } catch (\Exception $e) {
            Log::error('Broadcasting auth failed', [
                'user_id' => Auth::id(),
                'channel' => $request->input('channel_name'),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Authentication failed'], 500);
        }
    })->middleware('auth:sanctum');

    Route::post('/auth/ably-channel-token', function (Request $request) {
        try {
            $currentUserId = Auth::id();
            $channelName = $request->input('channel');

            if (!$currentUserId || !$channelName) {
                return response()->json(['success' => false, 'message' => 'Invalid request'], 400);
            }

            // Verify user has access to this specific channel
            if (preg_match('/^private-chat\.(\d+)$/', $channelName, $matches)) {
                $chatId = $matches[1];

                $hasAccess = ChatSession::where('id', $chatId)
                    ->where(function ($query) use ($currentUserId) {
                        $query->where('user_one_id', $currentUserId)
                            ->orWhere('user_two_id', $currentUserId);
                    })
                    ->exists();

                if (!$hasAccess) {
                    return response()->json(['success' => false, 'message' => 'Access denied'], 403);
                }
            }

            $ably = new \Ably\AblyRest(['key' => config('services.ably.key')]);

            // Generate token with access to specific channel
            $tokenDetails = $ably->auth->requestToken([
                'clientId' => (string) $currentUserId,
                'capability' => json_encode([
                    $channelName => ['*']  // Access to specific channel only
                ]),
                'ttl' => 3600000
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'token' => $tokenDetails->token,
                    'expires_at' => intval($tokenDetails->expires / 1000),
                    'client_id' => $tokenDetails->clientId,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Channel-specific token generation failed', [
                'user_id' => Auth::id(),
                'channel' => $request->input('channel'),
                'error' => $e->getMessage()
            ]);

            return response()->json(['success' => false, 'message' => 'Token generation failed'], 500);
        }
    })->middleware('auth:sanctum');


    // ================================
    // NOTIFICATION ROUTES
    // ================================
    Route::middleware('auth:sanctum')->group(function () {
        // Notification routes
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'getUserNotifications']);
            Route::post('token', [NotificationController::class, 'updateToken']);
            Route::get('preferences', [NotificationController::class, 'getPreferences']);
            Route::put('preferences', [NotificationController::class, 'updatePreferences']);
            Route::post('subscribe', [NotificationController::class, 'subscribeToTopic']);
            Route::post('unsubscribe', [NotificationController::class, 'unsubscribeFromTopic']);
            Route::post('test', [NotificationController::class, 'testNotification']);
            Route::put('{id}/read', [NotificationController::class, 'markAsRead']);
            Route::put('mark-all-read', [NotificationController::class, 'markAllAsRead']);
            Route::post('send-topic', [NotificationController::class, 'sendTopicNotification'])->middleware('admin');
            Route::delete('{id}', [NotificationController::class, 'deleteNotification']);
            Route::delete('clear-all', [NotificationController::class, 'clearAllNotifications']);
        });

        // User notification settings
        Route::prefix('user')->group(function () {
            Route::get('notification-settings', [NotificationController::class, 'getNotificationSettings']);
            Route::put('notification-settings', [NotificationController::class, 'saveNotificationSettings']);
            Route::put('fcm-token', [NotificationController::class, 'updateFCMToken']);
        });
    });

    // ================================
    // HISTORY ROUTES - FIXED ORDER
    // ================================
    Route::prefix('user/history')->group(function () {
        Route::get('/', [HistoryController::class, 'index']);
        Route::get('stats', [HistoryController::class, 'getStats']);
        Route::get('categories', [HistoryController::class, 'getCategories']);

        Route::post('/', [HistoryController::class, 'store']);

        Route::delete('/', [HistoryController::class, 'clear']);
        Route::delete('bulk', [HistoryController::class, 'bulkDelete']);

        // Parameter routes last
        Route::get('{id}', [HistoryController::class, 'show']);
        Route::delete('{id}', [HistoryController::class, 'destroy']);
    });

    // ================================
    // ITEMS ROUTES - FIXED ORDER
    // ================================
    Route::prefix('items')->group(function () {
        // Static routes first
        Route::get('/', [ItemController::class, 'index']);
        Route::get('search', [ItemController::class, 'search']);
        Route::get('my-listings', [ItemController::class, 'getMyListings']);
        Route::get('favorites', [ItemController::class, 'getFavorites']);
        Route::get('my-purchases', [ItemController::class, 'getMyPurchases']);

        Route::post('/', [ItemController::class, 'store']);

        Route::delete('favorites/clear', [ItemController::class, 'clearAllFavorites']);
        Route::post('purchases/{purchaseId}/cancel', [ItemController::class, 'cancelOrder']);

        // Multi-segment parameter routes
        Route::get('{item}/edit', [ItemController::class, 'edit']);
        Route::get('{item}/related', [ItemController::class, 'getRelated']);

        Route::post('{item}/promote', [ItemController::class, 'promote']);
        Route::post('{item}/mark-sold', [ItemController::class, 'markAsSold']);
        Route::post('{item}/archive', [ItemController::class, 'archive']);
        Route::post('{item}/toggle-favorite', [ItemController::class, 'toggleFavorite']);

        Route::post('{item}/update', [ItemController::class, 'update']);
        Route::patch('{item}/status', [ItemController::class, 'updateStatus']);

        Route::delete('{item}', [ItemController::class, 'destroy']);

        // Single parameter route last
        Route::get('{item}', [ItemController::class, 'show']);
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
    // LOCATION & USER ROUTES
    // ================================
    Route::prefix('user')->group(function () {
        Route::get('recent-locations', [LocationController::class, 'getRecentLocations']);
        Route::get('notification-settings', [UserController::class, 'getNotificationSettings']);
        Route::get('preferences', [OfferController::class, 'getUserPreferences']);
        Route::get('profile', [SupportController::class, 'getUserProfile']);

        Route::post('recent-locations', [LocationController::class, 'saveRecentLocation']);
        Route::put('notification-settings', [UserController::class, 'updateNotificationSettings']);
    });

    Route::post('location/custom', [LocationController::class, 'addCustomLocation']);

    // ================================
    // OFFER & BASKET ROUTES
    // ================================
    Route::prefix('basket')->group(function () {
        Route::get('items', [OfferController::class, 'getBasketItems']);
        Route::post('remove-multiple', [OfferController::class, 'removeMultipleBasketItems']);
        Route::delete('clear', [OfferController::class, 'clearBasket']);
        Route::delete('items/{basketItemId}', [OfferController::class, 'removeBasketItem']);
    });

    Route::get('delivery/options', [OfferController::class, 'getDeliveryOptions']);
    Route::post('checkout', [OfferController::class, 'processCheckout']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/meetups/confirm', [MeetupController::class, 'confirmMeetup']);
        Route::get('/meetups', [MeetupController::class, 'index']);
        Route::get('/meetups/{id}', [MeetupController::class, 'show']);
        Route::put('/meetups/{id}/complete', [MeetupController::class, 'complete']);
        Route::put('/meetups/{id}/cancel', [MeetupController::class, 'cancel']);
    });

    Route::prefix('orders')->group(function () {
        Route::get('/', [OfferController::class, 'getOrders']);
        Route::get('{orderId}', [OfferController::class, 'getOrderDetails']);
        Route::get('{orderId}/tracking', [OfferController::class, 'getOrderTracking']);
        Route::put('{orderId}/cancel', [OfferController::class, 'cancelOrder']);
    });

    Route::prefix('offers')->group(function () {
        Route::get('/', [OfferController::class, 'getOffers']);
        Route::post('/', [OfferController::class, 'sendOffer']);
        Route::get('statistics', [OfferController::class, 'getOfferStatistics']);
        Route::post('{offerId}/counter', [OfferController::class, 'sendCounterOffer']);
        Route::get('{offerId}/chain', [OfferController::class, 'getOfferChain']);
        Route::put('{offerId}/accept', [OfferController::class, 'acceptOffer']);
        Route::put('{offerId}/reject', [OfferController::class, 'rejectOffer']);
        Route::delete('{offerId}', [OfferController::class, 'cancelOffer']);
    });

    Route::prefix('study-material-requests')->group(function () {
        Route::get('/', [OfferController::class, 'getStudyMaterialRequests']);
        Route::post('/', [OfferController::class, 'createStudyMaterialRequest']);
        Route::put('{requestId}/fulfill', [OfferController::class, 'markRequestFulfilled']);
        Route::delete('{requestId}', [OfferController::class, 'deleteStudyMaterialRequest']);
    });

    // ================================
    // PAYMENT ROUTES - FIXED ORDER
    // ================================
    Route::prefix('payment')->group(function () {
        Route::get('methods', [PaymentController::class, 'getPaymentMethods']);
        Route::get('saved-cards', [PaymentController::class, 'getSavedPaymentMethods']);
        Route::get('history', [PaymentController::class, 'getPaymentHistory']);

        Route::post('add-method', [PaymentController::class, 'addPaymentMethod']);
        Route::post('process', [PaymentController::class, 'processPayment']);
        Route::post('validate-card', [PaymentController::class, 'validateCard']);

        Route::put('methods/{cardId}', [PaymentController::class, 'updatePaymentMethod']);
        Route::delete('methods/{cardId}', [PaymentController::class, 'deletePaymentMethod']);

        Route::get('{paymentId}', [PaymentController::class, 'getPaymentDetails']);
        Route::post('{paymentId}/refund', [PaymentController::class, 'refundPayment']);
    });

    Route::prefix('wishlist')->group(function () {
        Route::get('/', [ProfileController::class, 'getWishlist']);
        Route::get('/count', [ProfileController::class, 'getWishlistCount']);
        Route::post('/', [ProfileController::class, 'addToWishlist']);
        Route::delete('/clear', [ProfileController::class, 'clearWishlist']);
        Route::delete('/{itemId}', [ProfileController::class, 'removeFromWishlist']);
    });

    // ================================
    // PROFILE ROUTES
    // ================================
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'getProfile']);
        Route::get('student', [ProfileController::class, 'getStudentProfile']);
        Route::get('earnings', [ProfileController::class, 'getEarningsHistory']);
        Route::post('/', [ProfileController::class, 'updateProfile']);
        Route::post('upload-student-id', [ProfileController::class, 'uploadStudentId']);
        Route::post('verification', [ProfileController::class, 'submitStudentVerification']);

        // Stats routes with rate limiting
        Route::get('stats', [ProfileController::class, 'getCurrentUserStats']);
        Route::get('stats/{userId}', [ProfileController::class, 'getUserStats']);
        Route::get('stats/realtime', [ProfileController::class, 'getUserStatsRealtime'])
            ->middleware('throttle:10,1');
        Route::get('stats/compare', [ProfileController::class, 'compareUserStats'])
            ->middleware('throttle:5,1');

        // Wishlist nested routes
        Route::prefix('wishlist')->group(function () {
            Route::get('/', [ProfileController::class, 'getWishlist']);
            Route::get('/count', [ProfileController::class, 'getWishlistCount']);
            Route::post('/', [ProfileController::class, 'addToWishlist']);
            Route::delete('/clear', [ProfileController::class, 'clearWishlist']);
            Route::delete('/{itemId}', [ProfileController::class, 'removeFromWishlist']);
        });
    });

    // ================================
    // SAFETY ROUTES
    // ================================
    Route::prefix('safety')->group(function () {
        Route::get('blocked-users', [SafetyController::class, 'getBlockedUsers']);
        Route::get('stats', [SafetyController::class, 'getSafetyStats']);
        Route::get('is-blocked/{userId}', [SafetyController::class, 'isUserBlocked']);

        Route::post('block-user', [SafetyController::class, 'blockUser']);
        Route::post('unblock-user', [SafetyController::class, 'unblockUser']);
        Route::post('report-user', [SafetyController::class, 'reportUser']);
        Route::post('report-item', [SafetyController::class, 'reportItem']);
    });

    // ================================
    // SEARCH ROUTES - FIXED ORDER
    // ================================
    Route::prefix('search')->group(function () {
        Route::get('all', [SearchController::class, 'getAllSearchResults']);
        Route::get('items', [SearchController::class, 'searchItems']);
        Route::get('users', [SearchController::class, 'searchUsers']);
        Route::get('users/suggested', [SearchController::class, 'getSuggestedUsers']);
        Route::get('categories', [SearchController::class, 'getCategoriesWithCounts']);
        Route::get('suggestions', [SearchController::class, 'getSearchSuggestions']);
        Route::get('quick-suggestions', [SearchController::class, 'getQuickSuggestions']);
        Route::get('trending', [SearchController::class, 'getTrendingSearches']);
        Route::get('popular', [SearchController::class, 'getPopularSearches']);

        Route::post('filtered', [SearchController::class, 'getFilteredItems']);
        Route::post('record', [SearchController::class, 'recordSearch']);
        Route::post('favorites/toggle', [SearchController::class, 'toggleFavorite']);
        Route::post('users/follow/toggle', [SearchController::class, 'toggleFollowUser']);

        Route::get('categories/{categoryId}/subcategories', [SearchController::class, 'getSubCategories']);

        Route::prefix('filters')->group(function () {
            Route::get('locations', [SearchController::class, 'getAvailableLocations']);
            Route::get('universities', [SearchController::class, 'getAvailableUniversities']);
        });

        Route::prefix('history')->group(function () {
            Route::get('recent', [SearchController::class, 'getRecentSearches']);
            Route::post('save', [SearchController::class, 'saveSearchHistory']);
            Route::delete('clear', [SearchController::class, 'clearSearchHistory']);
        });
    });

    // ================================
    // SETTINGS ROUTES - FIXED ORDER
    // ================================
    Route::prefix('user/settings')->group(function () {
        Route::get('/', [SettingsController::class, 'getAllSettings']);
        Route::get('language', [SettingsController::class, 'getUserLanguage']);
        Route::get('options', [SettingsController::class, 'getSettingsOptions']);

        Route::post('language', [SettingsController::class, 'saveUserLanguage']);

        Route::put('/', [SettingsController::class, 'updateSettings']);

        Route::delete('/', [SettingsController::class, 'resetAllSettings']);

        // Parameter routes last
        Route::get('{key}', [SettingsController::class, 'getSetting']);
        Route::put('{key}', [SettingsController::class, 'updateSetting']);
        Route::delete('{key}', [SettingsController::class, 'deleteSetting']);
    });

    // ================================
    // SOCIAL ROUTES - ALREADY FIXED
    // ================================
    Route::prefix('users')->group(function () {
        // Static routes first
        Route::get('top-sellers', [SocialController::class, 'getTopSellers']);

        // Multi-segment parameter routes
        Route::get('{userId}/followers', [SocialController::class, 'getFollowers']);
        Route::get('{userId}/following', [SocialController::class, 'getFollowing']);
        Route::get('{userId}/ratings', [SocialController::class, 'getUserRatings']);
        Route::get('{userId}/items', [ItemController::class, 'getUserItems']);

        // ✅ SOCIAL ACTIONS: Both patterns are needed
        Route::post('{userId}/toggle-follow', [SocialController::class, 'toggleFollow']);
        Route::post('{userId}/follow', [SocialController::class, 'toggleFollow']);

        // ✅ BLOCKING: Keep separate block/unblock OR single toggle
        Route::post('{userId}/block', [ProfileController::class, 'toggleBlock']);
        Route::post('{userId}/unblock', [ProfileController::class, 'toggleBlock']);

        // ✅ ALTERNATIVE: If CommunicationController has separate methods
        // Route::post('{userId}/block', [CommunicationController::class, 'blockUser']);
        // Route::post('{userId}/unblock', [CommunicationController::class, 'unblockUser']);

        Route::delete('followers/{userId}', [SocialController::class, 'removeFollower']);

        // Single parameter route last
        Route::get('{userId}', [ProfileController::class, 'getUserDetails']);
    });

    // Rating routes
    Route::prefix('ratings')->group(function () {
        Route::post('/', [SocialController::class, 'submitRating']);
        Route::post('{ratingId}/helpful', [SocialController::class, 'markRatingHelpful']);
        Route::post('{ratingId}/report', [SocialController::class, 'reportRating']);
    });

    Route::get('transactions/{transactionId}', [SocialController::class, 'getTransactionDetails']);

    // ================================
    // SUPPORT ROUTES - FIXED ORDER
    // ================================
    // Route::prefix('support')->group(function () {
    //     Route::get('faqs', [SupportController::class, 'getFaqs']);
    //     Route::get('faqs/search', [SupportController::class, 'searchFaqs']);
    //     Route::get('contact', [SupportController::class, 'getContactInfo']);
    //     Route::get('app-info', [SupportController::class, 'getAppInfo']);
    //     Route::get('system-status', [SupportController::class, 'getSystemStatus']);
    //     Route::get('announcements', [SupportController::class, 'getAnnouncements']);
    //     Route::get('popular-topics', [SupportController::class, 'getPopularTopics']);
    //     Route::get('requests/my-requests', [SupportController::class, 'getMySupportRequests']);

    //     Route::post('requests', [SupportController::class, 'submitSupportRequest']);
    //     Route::post('feedback', [SupportController::class, 'submitFeedback']);
    //     Route::post('bug-reports', [SupportController::class, 'submitBugReport']);
    //     Route::post('feature-requests', [SupportController::class, 'submitFeatureRequest']);
    //     Route::post('faqs/{faqId}/helpful', [SupportController::class, 'markFaqHelpful']);

    //     Route::get('requests/{requestId}', [SupportController::class, 'getSupportRequestDetails']);
    //     Route::post('requests/{requestId}/rate', [SupportController::class, 'rateSupportExperience']);
    // });

    // Cache clearing route
    Route::post('clear-cache', [CategoryController::class, 'clearCache']);
});
