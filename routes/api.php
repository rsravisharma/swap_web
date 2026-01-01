<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\ChatSession;
use App\Services\EnhancedGeocodingService;
use App\Http\Controllers\Api\{
    AuthController,
    AblyAuthController,
    HomeController,
    CategoryController,
    ItemSearchController,
    CategoryItemController,
    CoinsController,
    SubscriptionController,
    SearchController,
    LocationController,
    ChatController,
    MeetupController,
    CommunicationController,
    OfferController,
    PaymentController,
    PdfBookController,
    SocialController,
    SafetyController,
    NotificationController,
    HistoryController,
    SupportController,
    ItemController,
    LegalController,
    ProfileController,
    SettingsController,
    UserController,
    ReferralController,
    TransactionController,
};

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// ================================
// PUBLIC ROUTES (No Authentication Required)
// ================================

// Authentication Routes [web:50][web:51]
Route::prefix('auth')->name('auth.')->group(function () {
    // Registration & Login
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('login', [AuthController::class, 'login'])->name('login');

    // Social Authentication
    Route::post('google', [AuthController::class, 'googleSignIn'])->name('google');
    Route::get('google/redirect', [AuthController::class, 'redirectToGoogle'])->name('google.redirect');
    Route::get('google/callback', [AuthController::class, 'handleGoogleCallback'])->name('google.callback');
    Route::post('facebook', [AuthController::class, 'fbSignIn'])->name('facebook');

    // Phone Authentication
    Route::post('phone/send-otp', [AuthController::class, 'phoneSignIn'])->name('phone.send-otp');
    Route::post('phone/verify-otp', [AuthController::class, 'phoneVerify'])->name('phone.verify-otp');

    // OTP Management
    Route::post('verify-otp', [AuthController::class, 'verifyOtp'])->name('verify-otp');
    Route::post('resend-otp', [AuthController::class, 'resendOtp'])->name('resend-otp');

    // Password Management
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot-password');

    // Utilities
    Route::get('country-codes', [AuthController::class, 'phoneCountryCode'])->name('country-codes');
    Route::post('ably-token', [AuthController::class, 'generateAblyToken'])->name('ably-token');
});

// Public Items Routes [web:52]
Route::prefix('items')->name('items.')->group(function () {
    Route::get('popular', [HomeController::class, 'popular'])->name('popular');
    Route::get('recent', [HomeController::class, 'recent'])->name('recent');
    Route::get('trending', [HomeController::class, 'trending'])->name('trending');
    Route::get('featured', [HomeController::class, 'featured'])->name('featured');
});

// Search Routes (Public) [web:52]
Route::get('search/items', [ItemSearchController::class, 'search'])->name('search.items');

// Category Routes (Public) [web:50][web:54]
Route::prefix('categories')->name('categories.')->group(function () {
    // List/Bulk Operations
    Route::get('hierarchy', [CategoryController::class, 'getCategoryHierarchy'])->name('hierarchy');
    Route::get('flat', [CategoryController::class, 'getFlatCategories'])->name('flat');
    Route::get('names', [CategoryController::class, 'getCategoryNames'])->name('names');
    Route::get('path', [CategoryController::class, 'getCategoryPath'])->name('path');
    Route::get('stats', [HomeController::class, 'categoryStats'])->name('stats');
    Route::delete('cache', [CategoryController::class, 'clearCache'])->name('cache.clear');

    // Single Category Operations [web:54]
    Route::get('{categoryId}/sub-categories', [CategoryController::class, 'getSubCategories'])->name('sub-categories');
    Route::get('{categoryId}/items', [CategoryItemController::class, 'categoryItems'])->name('items');
});

// Subcategory Routes [web:50]
Route::prefix('subcategories')->name('subcategories.')->group(function () {
    Route::get('{subcategoryId}/items', [CategoryItemController::class, 'subcategoryItems'])->name('items');
    Route::get('{subcategoryId}/children', [CategoryController::class, 'getChildSubCategories'])->name('children');
});

// Child Subcategory Routes [web:50]
Route::prefix('child-subcategories')->name('child-subcategories.')->group(function () {
    Route::get('{childSubcategoryId}/items', [CategoryItemController::class, 'childSubcategoryItems'])->name('items');
});

// Location Routes (Public) [web:52]
Route::prefix('location')->name('location.')->group(function () {
    // Geographic Data
    Route::get('countries', [LocationController::class, 'getCountries'])->name('countries');
    Route::get('cities', [LocationController::class, 'getCities'])->name('cities');
    Route::get('all', [LocationController::class, 'getAllLocations'])->name('all');

    // Location Search & Discovery
    Route::get('search', [LocationController::class, 'searchLocations'])->name('search');
    Route::get('nearby', [LocationController::class, 'getNearbyLocations'])->name('nearby');
    Route::get('popular', [LocationController::class, 'getPopularLocations'])->name('popular');
    Route::get('reverse-geocode', [LocationController::class, 'reverseGeocode'])->name('reverse-geocode');

    // Specific Location Types
    Route::get('campus', [LocationController::class, 'getCampusLocations'])->name('campus');
    Route::get('meetup', [LocationController::class, 'getMeetupLocations'])->name('meetup');
    Route::get('universities', [LocationController::class, 'getUniversities'])->name('universities');

    // University Routes [web:54]
    Route::prefix('university')->name('university.')->group(function () {
        Route::get('/', [LocationController::class, 'getUniversities'])->name('index');
        Route::get('{identifier}', [LocationController::class, 'getUniversity'])->name('show');
    });
});

// Legal Routes (Public) [web:52]
Route::prefix('legal')->name('legal.')->group(function () {
    Route::get('privacy-policy', [LegalController::class, 'getPrivacyPolicy'])->name('privacy-policy');
    Route::get('terms-and-conditions', [LegalController::class, 'getTermsAndConditions'])->name('terms');
    Route::get('document/{documentType}', [LegalController::class, 'getLegalDocument'])->name('document');
});

// Debug Route (Remove in production)
Route::get('debug-ably-token', function () {
    try {
        $token = request()->header('X-Ably-Token');
        if (!$token) {
            return response()->json(['error' => 'No token provided']);
        }

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
})->name('debug.ably-token');

// ================================
// PROTECTED ROUTES (Authentication Required) [web:50][web:51]
// ================================

Route::middleware('auth:sanctum')->group(function () {

    // ================================
    // AUTHENTICATION & USER MANAGEMENT
    // ================================

    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::put('password', [AuthController::class, 'updatePassword'])->name('password.update');
        Route::post('verify-email', [AuthController::class, 'verifyEmail'])->name('verify-email');
        Route::get('debug/auth', [AuthController::class, 'checkAuth'])->name('debug.auth');

        // Ably Token Management
        Route::get('ably-token', [AblyAuthController::class, 'getAblyToken'])->name('ably-token.get');
        Route::get('ably-config', [AblyAuthController::class, 'getAblyConfig'])->name('ably-config');
        Route::post('ably-channel-token', function (Request $request) {
            try {
                $currentUserId = Auth::id();
                $channelName = $request->input('channel');

                if (!$currentUserId || !$channelName) {
                    return response()->json(['success' => false, 'message' => 'Invalid request'], 400);
                }

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
                $tokenDetails = $ably->auth->requestToken([
                    'clientId' => (string) $currentUserId,
                    'capability' => json_encode([$channelName => ['*']]),
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
        })->name('ably-channel-token');
    });

    // FCM Token Update
    Route::post('user/fcm-token', [AuthController::class, 'updateUserFcmToken'])->name('user.fcm-token');

    // ================================
    // LOCATION MANAGEMENT (Protected)
    // ================================

    Route::prefix('location')->name('location.')->group(function () {
        // User Locations
        Route::get('user/recent', [LocationController::class, 'getRecentLocations'])->name('user.recent');
        Route::post('user/recent', [LocationController::class, 'saveRecentLocation'])->name('user.recent.save');
        Route::post('custom', [LocationController::class, 'addCustomLocation'])->name('custom');
        Route::put('update/{id}', [LocationController::class, 'updateLocation'])->name('update');

        // Statistics
        Route::get('geocoding-stats', [LocationController::class, 'getGeocodingStats'])->name('geocoding-stats');

        // University Management [web:54]
        Route::prefix('university')->name('university.')->group(function () {
            Route::post('/', [LocationController::class, 'createUniversity'])->name('create');
            Route::put('{id}', [LocationController::class, 'updateUniversity'])->name('update');
            Route::delete('{id}', [LocationController::class, 'deleteUniversity'])->name('delete');
        });
    });

    // ================================
    // CHAT & COMMUNICATION [web:52]
    // ================================

    // Chat Sessions
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('sessions', [ChatController::class, 'getUserSessions'])->name('sessions');
        Route::post('session', [ChatController::class, 'startSession'])->name('session.start');
        Route::post('store-ably-message', [ChatController::class, 'storeAblyMessage'])->name('store-ably-message');
        Route::get('session/{sessionId}/messages', [ChatController::class, 'getMessages'])->name('session.messages');
        Route::post('session/{sessionId}/read', [ChatController::class, 'markAsRead'])->name('session.mark-read');
        Route::delete('session/{sessionId}', [ChatController::class, 'deleteSession'])->name('session.delete');
    });

    // Chat Messages & Communication [web:50][web:54]
    Route::prefix('chats')->name('chats.')->group(function () {
        // List Operations
        Route::get('/', [CommunicationController::class, 'getChats'])->name('index');
        Route::get('unread-count', [CommunicationController::class, 'getUnreadMessageCount'])->name('unread-count');

        // Single Chat Operations
        Route::prefix('{chatId}')->name('show.')->group(function () {
            // Read Operations
            Route::get('messages', [CommunicationController::class, 'getChatMessages'])->name('messages');
            Route::get('messages/search', [CommunicationController::class, 'searchMessages'])->name('messages.search');
            Route::get('offers', [CommunicationController::class, 'getOfferHistory'])->name('offers');

            // Create Operations
            Route::post('messages', [CommunicationController::class, 'sendMessage'])->name('messages.send');
            Route::post('offers', [CommunicationController::class, 'sendOffer'])->name('offers.send');
            Route::post('report', [CommunicationController::class, 'reportChat'])->name('report');

            // Update Operations
            Route::put('mark-read', [CommunicationController::class, 'markChatAsRead'])->name('mark-read');
            Route::put('archive', [CommunicationController::class, 'updateChatArchiveStatus'])->name('archive');
            Route::put('messages/{messageId}', [CommunicationController::class, 'editMessage'])->name('messages.edit');
            Route::put('offers/{messageId}/accept', [CommunicationController::class, 'acceptOffer'])->name('offers.accept');
            Route::put('offers/{messageId}/reject', [CommunicationController::class, 'rejectOffer'])->name('offers.reject');

            // Delete Operations
            Route::delete('/', [CommunicationController::class, 'deleteChat'])->name('delete');
            Route::delete('messages/{messageId}', [CommunicationController::class, 'deleteMessage'])->name('messages.delete');
        });
    });

    // Chat File Uploads
    Route::post('upload/chat-image', [CommunicationController::class, 'uploadChatImage'])->name('upload.chat-image');
    Route::post('upload/chat-files', [CommunicationController::class, 'uploadChatFiles'])->name('upload.chat-files');
    Route::get('ping', [CommunicationController::class, 'ping'])->name('ping');

    // Broadcasting Authentication [web:51]
    Route::post('broadcasting/auth', function (Request $request) {
        try {
            $currentUserId = Auth::id();

            if (!$currentUserId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $channelName = $request->input('channel_name');

            if (!$channelName) {
                return response()->json(['error' => 'Channel name required'], 400);
            }

            if (preg_match('/^private-chat\.(\d+)$/', $channelName, $matches)) {
                $chatId = $matches[1];
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

            $ably = new \Ably\AblyRest(['key' => config('services.ably.key')]);
            $tokenDetails = $ably->auth->requestToken([
                'clientId' => (string) $currentUserId,
                'capability' => [$channelName => ["*"]],
                'ttl' => 3600000
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
    })->name('broadcasting.auth');

    // ================================
    // NOTIFICATIONS [web:52]
    // ================================

    Route::prefix('notifications')->name('notifications.')->group(function () {
        // List & Bulk Operations
        Route::get('/', [NotificationController::class, 'getUserNotifications'])->name('index');
        Route::get('preferences', [NotificationController::class, 'getPreferences'])->name('preferences');
        Route::put('preferences', [NotificationController::class, 'updatePreferences'])->name('preferences.update');
        Route::put('mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('clear-all', [NotificationController::class, 'clearAllNotifications'])->name('clear-all');

        // Token Management
        Route::post('token', [NotificationController::class, 'updateToken'])->name('token.update');

        // Topic Subscriptions
        Route::post('subscribe', [NotificationController::class, 'subscribeToTopic'])->name('subscribe');
        Route::post('unsubscribe', [NotificationController::class, 'unsubscribeFromTopic'])->name('unsubscribe');

        // Testing & Admin
        Route::post('test', [NotificationController::class, 'testNotification'])->name('test');
        Route::post('send-topic', [NotificationController::class, 'sendTopicNotification'])
            ->middleware('admin')->name('send-topic');

        // Single Notification Operations
        Route::put('{id}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::delete('{id}', [NotificationController::class, 'deleteNotification'])->name('delete');
    });

    // User Notification Settings
    Route::prefix('user')->name('user.')->group(function () {
        Route::get('notification-settings', [NotificationController::class, 'getNotificationSettings'])->name('notification-settings');
        Route::put('notification-settings', [NotificationController::class, 'saveNotificationSettings'])->name('notification-settings.save');
        Route::put('fcm-token', [NotificationController::class, 'updateFCMToken'])->name('fcm-token.update');
    });

    // ================================
    // USER HISTORY [web:50][web:54]
    // ================================

    Route::prefix('user/history')->name('user.history.')->group(function () {
        // Resource Routes
        Route::get('/', [HistoryController::class, 'index'])->name('index');
        Route::post('/', [HistoryController::class, 'store'])->name('store');
        Route::get('{id}', [HistoryController::class, 'show'])->name('show');
        Route::delete('{id}', [HistoryController::class, 'destroy'])->name('destroy');

        // Statistics & Analytics
        Route::get('stats', [HistoryController::class, 'getStats'])->name('stats');
        Route::get('categories', [HistoryController::class, 'getCategories'])->name('categories');

        // Bulk Operations
        Route::delete('/', [HistoryController::class, 'clear'])->name('clear');
        Route::delete('bulk', [HistoryController::class, 'bulkDelete'])->name('bulk-delete');
    });

    // ================================
    // ITEMS MANAGEMENT [web:50][web:54]
    // ================================

    Route::prefix('items')->name('items.')->group(function () {
        // List Operations
        Route::get('/', [ItemController::class, 'index'])->name('index');
        Route::get('search', [ItemController::class, 'search'])->name('search');
        Route::get('my-listings', [ItemController::class, 'getMyListings'])->name('my-listings');
        Route::get('favorites', [ItemController::class, 'getFavorites'])->name('favorites');
        Route::get('my-purchases', [ItemController::class, 'getMyPurchases'])->name('my-purchases');

        // Create
        Route::post('/', [ItemController::class, 'store'])->name('store');

        // Bulk Operations
        Route::delete('favorites/clear', [ItemController::class, 'clearAllFavorites'])->name('favorites.clear');
        Route::post('purchases/{purchaseId}/cancel', [ItemController::class, 'cancelOrder'])->name('purchases.cancel');

        // Single Item Operations [web:54]
        Route::prefix('{item}')->name('show.')->group(function () {
            Route::get('/', [ItemController::class, 'show'])->name('index');
            Route::get('edit', [ItemController::class, 'edit'])->name('edit');
            Route::get('related', [ItemController::class, 'getRelated'])->name('related');

            // Item Actions
            Route::post('promote', [ItemController::class, 'promote'])->name('promote');
            Route::post('mark-sold', [ItemController::class, 'markAsSold'])->name('mark-sold');
            Route::post('archive', [ItemController::class, 'archive'])->name('archive');
            Route::post('toggle-favorite', [ItemController::class, 'toggleFavorite'])->name('toggle-favorite');

            // Update & Delete
            Route::post('update', [ItemController::class, 'update'])->name('update');
            Route::patch('status', [ItemController::class, 'updateStatus'])->name('status');
            Route::delete('/', [ItemController::class, 'destroy'])->name('destroy');
        });
    });

    // ================================
    // LEGAL & AGREEMENTS
    // ================================

    Route::prefix('legal')->name('legal.')->group(function () {
        Route::post('agreement', [LegalController::class, 'submitLegalAgreement'])->name('agreement');
        Route::post('accept-terms', [LegalController::class, 'acceptTermsAndConditions'])->name('accept-terms');
        Route::post('accept-privacy', [LegalController::class, 'acceptPrivacyPolicy'])->name('accept-privacy');
    });

    Route::get('user/agreements', [LegalController::class, 'getUserAgreements'])->name('user.agreements');

    // ================================
    // USER PREFERENCES & SETTINGS [web:52]
    // ================================

    Route::prefix('user')->name('user.')->group(function () {
        Route::get('recent-locations', [LocationController::class, 'getRecentLocations'])->name('recent-locations');
        Route::post('recent-locations', [LocationController::class, 'saveRecentLocation'])->name('recent-locations.save');
        Route::get('notification-settings', [UserController::class, 'getNotificationSettings'])->name('notification-settings.get');
        Route::put('notification-settings', [UserController::class, 'updateNotificationSettings'])->name('notification-settings.update');
        Route::get('preferences', [OfferController::class, 'getUserPreferences'])->name('preferences');
        Route::get('profile', [SupportController::class, 'getUserProfile'])->name('profile');
    });

    // ================================
    // BASKET & CHECKOUT [web:50][web:54]
    // ================================

    Route::prefix('basket')->name('basket.')->group(function () {
        Route::get('items', [OfferController::class, 'getBasketItems'])->name('items');
        Route::post('remove-multiple', [OfferController::class, 'removeMultipleBasketItems'])->name('remove-multiple');
        Route::delete('clear', [OfferController::class, 'clearBasket'])->name('clear');
        Route::delete('items/{basketItemId}', [OfferController::class, 'removeBasketItem'])->name('items.remove');
    });

    Route::get('delivery/options', [OfferController::class, 'getDeliveryOptions'])->name('delivery.options');
    Route::post('checkout', [OfferController::class, 'processCheckout'])->name('checkout');

    // ================================
    // ORDERS [web:50][web:54]
    // ================================

    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OfferController::class, 'getOrders'])->name('index');
        Route::get('{orderId}', [OfferController::class, 'getOrderDetails'])->name('show');
        Route::get('{orderId}/tracking', [OfferController::class, 'getOrderTracking'])->name('tracking');
        Route::put('{orderId}/cancel', [OfferController::class, 'cancelOrder'])->name('cancel');
    });

    // ================================
    // OFFERS & NEGOTIATIONS [web:50][web:54]
    // ================================

    Route::prefix('offers')->name('offers.')->group(function () {
        Route::get('/', [OfferController::class, 'getOffers'])->name('index');
        Route::post('/', [OfferController::class, 'sendOffer'])->name('send');
        Route::get('statistics', [OfferController::class, 'getOfferStatistics'])->name('statistics');

        // Single Offer Operations
        Route::prefix('{offerId}')->name('show.')->group(function () {
            Route::post('counter', [OfferController::class, 'sendCounterOffer'])->name('counter');
            Route::get('chain', [OfferController::class, 'getOfferChain'])->name('chain');
            Route::put('accept', [OfferController::class, 'acceptOffer'])->name('accept');
            Route::put('reject', [OfferController::class, 'rejectOffer'])->name('reject');
            Route::delete('/', [OfferController::class, 'cancelOffer'])->name('cancel');

            // Meetup for Offer
            Route::post('meetup', [MeetupController::class, 'createOrUpdate'])->name('meetup');
        });
    });

    // ================================
    // MEETUPS [web:50][web:54]
    // ================================

    Route::prefix('meetups')->name('meetups.')->group(function () {
        Route::get('/', [MeetupController::class, 'index'])->name('index');
        Route::get('{meetup}', [MeetupController::class, 'show'])->name('show');
        Route::put('{meetup}', [MeetupController::class, 'update'])->name('update');
        Route::put('{meetup}/confirm', [MeetupController::class, 'confirm'])->name('confirm');
        Route::put('{meetup}/fail', [MeetupController::class, 'markFailed'])->name('fail');
        Route::put('{meetup}/cancel', [MeetupController::class, 'cancel'])->name('cancel');
    });

    // ================================
    // TRANSACTIONS [web:50]
    // ================================

    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/', [TransactionController::class, 'index'])->name('index');
        Route::get('{id}', [TransactionController::class, 'show'])->name('show');
        Route::get('monthly-summary', [TransactionController::class, 'monthlySummary'])->name('monthly-summary');
        Route::get('yearly-overview', [TransactionController::class, 'yearlyOverview'])->name('yearly-overview');
    });

    // ================================
    // STUDY MATERIAL REQUESTS [web:54]
    // ================================

    Route::prefix('study-material-requests')->name('study-material-requests.')->group(function () {
        Route::get('/', [OfferController::class, 'getStudyMaterialRequests'])->name('index');
        Route::post('/', [OfferController::class, 'createStudyMaterialRequest'])->name('create');
        Route::put('{requestId}/fulfill', [OfferController::class, 'markRequestFulfilled'])->name('fulfill');
        Route::delete('{requestId}', [OfferController::class, 'deleteStudyMaterialRequest'])->name('delete');
    });

    // ================================
    // PAYMENT MANAGEMENT [web:50][web:54]
    // ================================

    Route::prefix('payment')->group(function () {
        Route::get('/methods', [PaymentController::class, 'getPaymentMethods']);
        Route::get('/history', [PaymentController::class, 'getPaymentHistory']);
        Route::post('/create-order', [PaymentController::class, 'createOrder']);
        Route::post('/verify', [PaymentController::class, 'verifyPayment']);
        Route::post('/failed', [PaymentController::class, 'handlePaymentFailure']);
        Route::get('/{paymentId}', [PaymentController::class, 'getPaymentDetails']);
        Route::post('/{paymentId}/refund', [PaymentController::class, 'refundPayment']);
    });

    // PDF Book routes
     Route::prefix('pdf-books')->group(function () {
        Route::get('/', [PdfBookController::class, 'index']); 
        Route::post('/orders/create', [PdfBookController::class, 'createOrder']);
        Route::get('/my-purchases', [PdfBookController::class, 'myPurchases']);
        Route::get('/my-books', [PdfBookController::class, 'myBooks']);
        Route::post('/deliver', [PdfBookController::class, 'deliverBook']);
        Route::get('/download/{token}', [PdfBookController::class, 'downloadByToken']);
        Route::get('/{id}', [PdfBookController::class, 'show']); 
    });


    // ================================
    // WISHLIST [web:54]
    // ================================

    Route::prefix('wishlist')->name('wishlist.')->group(function () {
        Route::get('/', [ProfileController::class, 'getWishlist'])->name('index');
        Route::get('count', [ProfileController::class, 'getWishlistCount'])->name('count');
        Route::post('/', [ProfileController::class, 'addToWishlist'])->name('add');
        Route::post('add', [ProfileController::class, 'addToWishlist'])->name('wishlist.add');
        Route::delete('clear', [ProfileController::class, 'clearWishlist'])->name('clear');
        Route::delete('{itemId}', [ProfileController::class, 'removeFromWishlist'])->name('remove');
    });

    // ================================
    // PROFILE MANAGEMENT [web:50][web:52]
    // ================================

    Route::prefix('profile')->name('profile.')->group(function () {
        // Profile Data
        Route::get('/', [ProfileController::class, 'getProfile'])->name('index');
        Route::post('/', [ProfileController::class, 'updateProfile'])->name('update');
        Route::get('student', [ProfileController::class, 'getStudentProfile'])->name('student');
        Route::get('earnings', [ProfileController::class, 'getEarningsHistory'])->name('earnings');

        // Student Verification
        Route::post('upload-student-id', [ProfileController::class, 'uploadStudentId'])->name('upload-student-id');
        Route::post('verification', [ProfileController::class, 'submitStudentVerification'])->name('verification');

        // Statistics
        Route::get('stats', [ProfileController::class, 'getCurrentUserStats'])->name('stats');
        Route::get('stats/{userId}', [ProfileController::class, 'getUserStats'])->name('stats.user');
        Route::get('stats/realtime', [ProfileController::class, 'getUserStatsRealtime'])
            ->middleware('throttle:10,1')->name('stats.realtime');
        Route::get('stats/compare', [ProfileController::class, 'compareUserStats'])
            ->middleware('throttle:5,1')->name('stats.compare');

        // Profile Wishlist (Nested)
        Route::prefix('wishlist')->name('wishlist.')->group(function () {
            Route::get('/', [ProfileController::class, 'getWishlist'])->name('index');
            Route::get('count', [ProfileController::class, 'getWishlistCount'])->name('count');
            Route::post('/', [ProfileController::class, 'addToWishlist'])->name('add');
            Route::delete('clear', [ProfileController::class, 'clearWishlist'])->name('clear');
            Route::delete('{itemId}', [ProfileController::class, 'removeFromWishlist'])->name('remove');
        });
    });

    // ================================
    // SAFETY & MODERATION [web:52]
    // ================================

    Route::prefix('safety')->name('safety.')->group(function () {
        Route::get('blocked-users', [SafetyController::class, 'getBlockedUsers'])->name('blocked-users');
        Route::get('stats', [SafetyController::class, 'getSafetyStats'])->name('stats');
        Route::get('is-blocked/{userId}', [SafetyController::class, 'isUserBlocked'])->name('is-blocked');

        Route::post('block-user', [SafetyController::class, 'blockUser'])->name('block-user');
        Route::post('unblock-user', [SafetyController::class, 'unblockUser'])->name('unblock-user');
        Route::post('report-user', [SafetyController::class, 'reportUser'])->name('report-user');
        Route::post('report-item', [SafetyController::class, 'reportItem'])->name('report-item');
    });

    // ================================
    // SEARCH (Protected Features) [web:52]
    // ================================

    Route::prefix('search')->name('search.')->group(function () {
        // Search Operations
        Route::get('all', [SearchController::class, 'getAllSearchResults'])->name('all');
        Route::get('items', [SearchController::class, 'searchItems'])->name('items');
        Route::get('users', [SearchController::class, 'searchUsers'])->name('users');
        Route::get('users/suggested', [SearchController::class, 'getSuggestedUsers'])->name('users.suggested');
        Route::get('categories', [SearchController::class, 'getCategoriesWithCounts'])->name('categories');

        // Suggestions & Trends
        Route::get('suggestions', [SearchController::class, 'getSearchSuggestions'])->name('suggestions');
        Route::get('quick-suggestions', [SearchController::class, 'getQuickSuggestions'])->name('quick-suggestions');
        Route::get('trending', [SearchController::class, 'getTrendingSearches'])->name('trending');
        Route::get('popular', [SearchController::class, 'getPopularSearches'])->name('popular');

        // Actions
        Route::post('filtered', [SearchController::class, 'getFilteredItems'])->name('filtered');
        Route::post('record', [SearchController::class, 'recordSearch'])->name('record');
        Route::post('favorites/toggle', [SearchController::class, 'toggleFavorite'])->name('favorites.toggle');
        Route::post('users/follow/toggle', [SearchController::class, 'toggleFollowUser'])->name('users.follow.toggle');

        // Category Subcategories
        Route::get('categories/{categoryId}/subcategories', [SearchController::class, 'getSubCategories'])->name('categories.subcategories');

        // Filters
        Route::prefix('filters')->name('filters.')->group(function () {
            Route::get('locations', [SearchController::class, 'getAvailableLocations'])->name('locations');
            Route::get('universities', [SearchController::class, 'getAvailableUniversities'])->name('universities');
        });

        // Search History
        Route::prefix('history')->name('history.')->group(function () {
            Route::get('recent', [SearchController::class, 'getRecentSearches'])->name('recent');
            Route::post('save', [SearchController::class, 'saveSearchHistory'])->name('save');
            Route::delete('clear', [SearchController::class, 'clearSearchHistory'])->name('clear');
        });
    });

    // ================================
    // USER SETTINGS [web:52]
    // ================================

    Route::prefix('user/settings')->name('user.settings.')->group(function () {
        // Bulk Settings
        Route::get('/', [SettingsController::class, 'getAllSettings'])->name('index');
        Route::put('/', [SettingsController::class, 'updateSettings'])->name('update');
        Route::delete('/', [SettingsController::class, 'resetAllSettings'])->name('reset');

        // Specific Settings
        Route::get('language', [SettingsController::class, 'getUserLanguage'])->name('language');
        Route::post('language', [SettingsController::class, 'saveUserLanguage'])->name('language.save');
        Route::get('options', [SettingsController::class, 'getSettingsOptions'])->name('options');

        // Dynamic Setting (Parameter routes last) [web:54]
        Route::get('{key}', [SettingsController::class, 'getSetting'])->name('show');
        Route::put('{key}', [SettingsController::class, 'updateSetting'])->name('update-key');
        Route::delete('{key}', [SettingsController::class, 'deleteSetting'])->name('delete');
    });


    // ================================
    // COINS MANAGEMENT (user prefix - singular)
    // ================================
    Route::prefix('user/coins')->name('user.coins.')->group(function () {
        Route::get('balance', [CoinsController::class, 'getBalance'])->name('balance');
        Route::get('packages', [CoinsController::class, 'getPackages'])->name('packages');
        Route::get('transactions', [CoinsController::class, 'getTransactionHistory'])->name('transactions');
        Route::post('purchase', [CoinsController::class, 'purchaseCoins'])->name('purchase');
        Route::post('deduct', [CoinsController::class, 'deductCoins'])->name('deduct');
        Route::post('add', [CoinsController::class, 'addCoins'])->name('add');

        // Admin only - Award bonus coins
        Route::post('award-bonus', [CoinsController::class, 'awardBonus'])
            ->middleware('admin')->name('award-bonus');
    });

    // ================================
    // SUBSCRIPTION MANAGEMENT (user prefix - singular)
    // ================================
    Route::prefix('user/subscription')->name('user.subscription.')->group(function () {
        Route::get('status', [SubscriptionController::class, 'getStatus'])->name('status');
        Route::post('purchase', [SubscriptionController::class, 'purchaseSubscription'])->name('purchase');
        Route::post('cancel', [SubscriptionController::class, 'cancelSubscription'])->name('cancel');
        Route::post('toggle-renewal', [SubscriptionController::class, 'toggleAutoRenewal'])->name('toggle-renewal');
    });

    // ================================
    // SOCIAL & USER INTERACTIONS (users prefix - plural)
    // ================================
    Route::prefix('users')->name('users.')->group(function () {
        // Top Users
        Route::get('top-sellers', [SocialController::class, 'getTopSellers'])->name('top-sellers');

        // User-specific Operations
        Route::prefix('{userId}')->name('show.')->group(function () {
            Route::get('/', [ProfileController::class, 'getUserDetails'])->name('index');
            Route::get('followers', [SocialController::class, 'getFollowers'])->name('followers');
            Route::get('following', [SocialController::class, 'getFollowing'])->name('following');
            Route::get('ratings', [SocialController::class, 'getUserRatings'])->name('ratings');
            Route::get('items', [ItemController::class, 'getUserItems'])->name('items');

            // Social Actions
            Route::post('toggle-follow', [SocialController::class, 'toggleFollow'])->name('toggle-follow');
            Route::post('follow', [SocialController::class, 'toggleFollow'])->name('follow');
            Route::post('block', [ProfileController::class, 'toggleBlock'])->name('block');
            Route::post('unblock', [ProfileController::class, 'toggleBlock'])->name('unblock');
        });

        // Remove Follower
        Route::delete('followers/{userId}', [SocialController::class, 'removeFollower'])->name('followers.remove');
    });

    // ================================
    // RATINGS & REVIEWS [web:50][web:54]
    // ================================

    Route::prefix('ratings')->name('ratings.')->group(function () {
        Route::post('/', [SocialController::class, 'submitRating'])->name('submit');
        Route::put('{ratingId}', [SocialController::class, 'updateRating'])->name('update');
        Route::post('{ratingId}/helpful', [SocialController::class, 'markRatingHelpful'])->name('helpful');
        Route::post('{ratingId}/report', [SocialController::class, 'reportRating'])->name('report');
    });

    // ================================
    // REFERRAL SYSTEM [web:54]
    // ================================

    Route::prefix('referral')->name('referral.')->group(function () {
        Route::post('validate', [ReferralController::class, 'validateCode'])->name('validate');
        Route::post('apply', [ReferralController::class, 'applyCode'])->name('apply');
        Route::get('stats', [ReferralController::class, 'getStats'])->name('stats');
        Route::get('my-referrals', [ReferralController::class, 'getReferredUsers'])->name('my-referrals');
        Route::get('transactions', [ReferralController::class, 'getTransactionHistory'])->name('transactions');
    });


    // ================================
    // SUPPORT & HELP [web:52]
    // ================================

    Route::prefix('support')->name('support.')->group(function () {
        // Information
        Route::get('faqs', [SupportController::class, 'getFaqs'])->name('faqs');
        Route::get('faqs/search', [SupportController::class, 'searchFaqs'])->name('faqs.search');
        Route::get('contact', [SupportController::class, 'getContactInfo'])->name('contact');
        Route::get('app-info', [SupportController::class, 'getAppInfo'])->name('app-info');
        Route::get('system-status', [SupportController::class, 'getSystemStatus'])->name('system-status');
        Route::get('announcements', [SupportController::class, 'getAnnouncements'])->name('announcements');
        Route::get('popular-topics', [SupportController::class, 'getPopularTopics'])->name('popular-topics');

        // User Requests
        Route::get('requests/my-requests', [SupportController::class, 'getMySupportRequests'])->name('requests.my');
        Route::get('requests/{requestId}', [SupportController::class, 'getSupportRequestDetails'])->name('requests.show');

        // Submit Requests
        Route::post('requests', [SupportController::class, 'submitSupportRequest'])->name('requests.submit');
        Route::post('feedback', [SupportController::class, 'submitFeedback'])->name('feedback');
        Route::post('bug-reports', [SupportController::class, 'submitBugReport'])->name('bug-reports');
        Route::post('feature-requests', [SupportController::class, 'submitFeatureRequest'])->name('feature-requests');
        Route::post('faqs/{faqId}/helpful', [SupportController::class, 'markFaqHelpful'])->name('faqs.helpful');
        Route::post('requests/{requestId}/rate', [SupportController::class, 'rateSupportExperience'])->name('requests.rate');
    });

    // ================================
    // CACHE MANAGEMENT
    // ================================

    Route::post('clear-cache', [CategoryController::class, 'clearCache'])->name('cache.clear');
    Route::get('/test-razorpay-config', function () {
        return response()->json([
            'key_exists' => !empty(config('services.razorpay.key')),
            'secret_exists' => !empty(config('services.razorpay.secret')),
            'key_preview' => substr(config('services.razorpay.key'), 0, 10) . '...',
        ]);
    })->middleware('auth:sanctum');
});
