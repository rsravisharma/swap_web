<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Models\UserNotificationPreference;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use App\Http\Controllers\Controller;
use App\Models\UserNotification;


class NotificationController extends Controller
{
    protected $messaging;

    public function __construct()
    {
        try {
            $credentialsPath = config('firebase.credentials');
            $projectId = config('firebase.project_id');


            if (empty($credentialsPath)) {
                throw new \Exception('Firebase credentials not configured. Please set FIREBASE_CREDENTIALS in your .env file.');
            }

            if (empty($projectId)) {
                throw new \Exception('Firebase project ID not configured. Please set FIREBASE_PROJECT_ID in your .env file.');
            }


            if (!file_exists($credentialsPath)) {
                throw new \Exception("Firebase credentials file not found at: {$credentialsPath}");
            }

            $factory = (new Factory)
                ->withServiceAccount($credentialsPath)
                ->withProjectId($projectId);

            $this->messaging = $factory->createMessaging();
        } catch (\Exception $e) {
            \Log::error('Firebase initialization failed: ' . $e->getMessage());


            $this->messaging = null;
        }
    }

    public function getNotifications()
    {
        if (!$this->messaging) {
            return response()->json([
                'success' => false,
                'message' => 'Firebase not properly configured',
                'error' => 'Push notification service unavailable'
            ], 500);
        }

        // Your notification logic here
        try {
            // Example notification logic
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting notifications: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get notifications',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Update FCM token for user
     * POST /notifications/token
     */
    public function updateToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required|string',
            'device_type' => 'in:android,ios',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $user->update([
                'fcm_token' => $request->fcm_token,
                'device_type' => $request->device_type,
                'last_token_update' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'FCM token updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update FCM token',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get notification preferences
     * GET /notifications/preferences
     */
    public function getPreferences(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $preferences = UserNotificationPreference::where('user_id', $user->id)->first();

            if (!$preferences) {
                $preferences = $this->createDefaultPreferences($user->id);
            }

            return response()->json([
                'success' => true,
                'data' => $preferences
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get preferences',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update notification preferences
     * PUT /notifications/preferences
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'chat_notifications' => 'boolean',
            'offer_notifications' => 'boolean',
            'item_notifications' => 'boolean',
            'marketing_notifications' => 'boolean',
            'sound_enabled' => 'boolean',
            'vibration_enabled' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();

            UserNotificationPreference::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'chat_notifications' => $request->boolean('chat_notifications'),
                    'offer_notifications' => $request->boolean('offer_notifications'),
                    'item_notifications' => $request->boolean('item_notifications'),
                    'marketing_notifications' => $request->boolean('marketing_notifications'),
                    'sound_enabled' => $request->boolean('sound_enabled'),
                    'vibration_enabled' => $request->boolean('vibration_enabled'),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Preferences updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update preferences',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send chat notification
     * Internal method called by chat system
     */
    public function sendChatNotification(ChatMessage $message): void
    {
        try {
            $session = $message->session;
            $sender = $message->sender;

            // Get recipient
            $recipientId = $session->user_one_id === $sender->id
                ? $session->user_two_id
                : $session->user_one_id;

            $recipient = User::find($recipientId);

            if (!$recipient || !$recipient->fcm_token) {
                return;
            }

            // Check if recipient has chat notifications enabled
            $preferences = UserNotificationPreference::where('user_id', $recipientId)->first();
            if ($preferences && !$preferences->chat_notifications) {
                return;
            }

            // Check if chat is muted
            if ($session->isMuted($recipientId)) {
                return;
            }

            $notification = Notification::create(
                "New message from {$sender->name}",
                $this->truncateMessage($message->message)
            );

            $data = [
                'type' => 'chat',
                'session_id' => (string) $session->id,
                'sender_id' => (string) $sender->id,
                'sender_name' => $sender->name,
                'message_id' => (string) $message->id,
            ];

            $cloudMessage = CloudMessage::withTarget('token', $recipient->fcm_token)
                ->withNotification($notification)
                ->withData($data);

            $this->messaging->send($cloudMessage);
        } catch (\Exception $e) {
            \Log::error('Failed to send chat notification: ' . $e->getMessage());
        }
    }

    /**
     * Send offer notification
     * Internal method called by offer system
     */
    public function sendOfferNotification(ChatOffer $offer, string $action): void
    {
        try {
            $session = $offer->session;
            $sender = $offer->sender;

            // Get recipient based on action
            $recipientId = $action === 'received'
                ? ($session->user_one_id === $sender->id ? $session->user_two_id : $session->user_one_id)
                : $sender->id;

            $recipient = User::find($recipientId);

            if (!$recipient || !$recipient->fcm_token) {
                return;
            }

            // Check preferences
            $preferences = UserNotificationPreference::where('user_id', $recipientId)->first();
            if ($preferences && !$preferences->offer_notifications) {
                return;
            }

            $title = match ($action) {
                'received' => "New offer from {$sender->name}",
                'accepted' => "Your offer was accepted!",
                'rejected' => "Your offer was rejected",
                default => "Offer update"
            };

            $body = match ($action) {
                'received' => "{$offer->currency} {$offer->amount}",
                'accepted' => "Offer for {$offer->currency} {$offer->amount} was accepted",
                'rejected' => "Offer for {$offer->currency} {$offer->amount} was rejected",
                default => "Check your offers"
            };

            $notification = Notification::create($title, $body);

            $data = [
                'type' => 'offer',
                'action' => $action,
                'offer_id' => (string) $offer->id,
                'session_id' => (string) $session->id,
                'amount' => (string) $offer->amount,
                'currency' => $offer->currency,
            ];

            $cloudMessage = CloudMessage::withTarget('token', $recipient->fcm_token)
                ->withNotification($notification)
                ->withData($data);

            $this->messaging->send($cloudMessage);
        } catch (\Exception $e) {
            \Log::error('Failed to send offer notification: ' . $e->getMessage());
        }
    }

    /**
     * Send bulk notification to topic
     * POST /notifications/send-topic
     */
    public function sendTopicNotification(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'topic' => 'required|string',
            'title' => 'required|string|max:100',
            'body' => 'required|string|max:200',
            'data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $notification = Notification::create(
                $request->title,
                $request->body
            );

            $data = $request->data ?? [];
            $data['type'] = 'announcement';

            $message = CloudMessage::withTarget('topic', $request->topic)
                ->withNotification($notification)
                ->withData($data);

            $this->messaging->send($message);

            return response()->json([
                'success' => true,
                'message' => 'Notification sent to topic successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send topic notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Subscribe to topic
     * POST /notifications/subscribe
     */
    public function subscribeToTopic(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'topic' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();

            if (!$user->fcm_token) {
                return response()->json([
                    'success' => false,
                    'message' => 'No FCM token found for user'
                ], 400);
            }

            $this->messaging->subscribeToTopic($request->topic, [$user->fcm_token]);

            return response()->json([
                'success' => true,
                'message' => 'Subscribed to topic successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to subscribe to topic',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unsubscribe from topic
     * POST /notifications/unsubscribe
     */
    public function unsubscribeFromTopic(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'topic' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();

            if (!$user->fcm_token) {
                return response()->json([
                    'success' => false,
                    'message' => 'No FCM token found for user'
                ], 400);
            }

            $this->messaging->unsubscribeFromTopic($request->topic, [$user->fcm_token]);

            return response()->json([
                'success' => true,
                'message' => 'Unsubscribed from topic successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unsubscribe from topic',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create default notification preferences
     */
    private function createDefaultPreferences($userId): UserNotificationPreference
    {
        return UserNotificationPreference::create([
            'user_id' => $userId,
            'chat_notifications' => true,
            'offer_notifications' => true,
            'item_notifications' => true,
            'marketing_notifications' => false,
            'sound_enabled' => true,
            'vibration_enabled' => true,
        ]);
    }

    /**
     * Truncate message for notification
     */
    private function truncateMessage($message, $length = 60): string
    {
        return strlen($message) > $length ? substr($message, 0, $length) . '...' : $message;
    }



    /**
     * Get notifications with filtering and pagination
     * GET /notifications
     */
    public function getUserNotifications(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'limit' => 'integer|min:1|max:100',
            'type' => 'nullable|string',
            'unread_only' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $query = UserNotification::where('user_id', $user->id);

            // Apply filters
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->boolean('unread_only')) {
                $query->where('is_read', false);
            }

            $limit = $request->input('limit', 20);
            $notifications = $query->orderBy('created_at', 'desc')
                ->paginate($limit);

            return response()->json([
                'success' => true,
                'data' => $notifications->items(),
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                    'has_more' => $notifications->hasMorePages(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get notification settings (enhanced version of getPreferences)
     * GET /user/notification-settings
     */
    public function getNotificationSettings(): JsonResponse
    {
        try {
            $user = Auth::user();
            $preferences = UserNotificationPreference::where('user_id', $user->id)->first();

            if (!$preferences) {
                $preferences = $this->createDefaultPreferences($user->id);
            }

            // Format settings to match Flutter expectations
            $settings = [
                'push_enabled' => $preferences->chat_notifications,
                'email_enabled' => $preferences->email_notifications ?? true,
                'sms_enabled' => $preferences->sms_notifications ?? false,
                'sound_enabled' => $preferences->sound_enabled,
                'vibration_enabled' => $preferences->vibration_enabled,
                'led_enabled' => $preferences->led_enabled ?? true,
                'quiet_hours_enabled' => $preferences->quiet_hours_enabled ?? false,
                'quiet_hours_start' => $preferences->quiet_hours_start ?? '22:00',
                'quiet_hours_end' => $preferences->quiet_hours_end ?? '07:00',
                'message_notifications' => $preferences->chat_notifications,
                'order_notifications' => $preferences->offer_notifications,
                'promotion_notifications' => $preferences->marketing_notifications,
                'system_notifications' => $preferences->item_notifications,
                'security_notifications' => $preferences->security_notifications ?? true,
            ];

            return response()->json([
                'success' => true,
                'data' => $settings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get notification settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save notification settings (enhanced version)
     * PUT /user/notification-settings
     */
    public function saveNotificationSettings(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'push_enabled' => 'boolean',
            'email_enabled' => 'boolean',
            'sms_enabled' => 'boolean',
            'sound_enabled' => 'boolean',
            'vibration_enabled' => 'boolean',
            'led_enabled' => 'boolean',
            'quiet_hours_enabled' => 'boolean',
            'quiet_hours_start' => 'nullable|string',
            'quiet_hours_end' => 'nullable|string',
            'message_notifications' => 'boolean',
            'order_notifications' => 'boolean',
            'promotion_notifications' => 'boolean',
            'system_notifications' => 'boolean',
            'security_notifications' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();

            UserNotificationPreference::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'chat_notifications' => $request->boolean('message_notifications'),
                    'offer_notifications' => $request->boolean('order_notifications'),
                    'item_notifications' => $request->boolean('system_notifications'),
                    'marketing_notifications' => $request->boolean('promotion_notifications'),
                    'email_notifications' => $request->boolean('email_enabled'),
                    'sms_notifications' => $request->boolean('sms_enabled'),
                    'sound_enabled' => $request->boolean('sound_enabled'),
                    'vibration_enabled' => $request->boolean('vibration_enabled'),
                    'led_enabled' => $request->boolean('led_enabled'),
                    'quiet_hours_enabled' => $request->boolean('quiet_hours_enabled'),
                    'quiet_hours_start' => $request->input('quiet_hours_start'),
                    'quiet_hours_end' => $request->input('quiet_hours_end'),
                    'security_notifications' => $request->boolean('security_notifications'),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Notification settings saved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save notification settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark single notification as read
     * PUT /notifications/{id}/read
     */
    public function markAsRead(int $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $notification = UserNotification::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found'
                ], 404);
            }

            $notification->update([
                'is_read' => true,
                'read_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark all notifications as read
     * PUT /notifications/mark-all-read
     */
    public function markAllAsRead(): JsonResponse
    {
        try {
            $user = Auth::user();

            UserNotification::where('user_id', $user->id)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all notifications as read',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete single notification
     * DELETE /notifications/{id}
     */
    public function deleteNotification(int $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $notification = UserNotification::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found'
                ], 404);
            }

            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear all notifications
     * DELETE /notifications/clear-all
     */
    public function clearAllNotifications(): JsonResponse
    {
        try {
            $user = Auth::user();

            UserNotification::where('user_id', $user->id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'All notifications cleared successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update FCM token (enhanced version)
     * PUT /user/fcm-token
     */
    public function updateFCMToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $user->update([
                'fcm_token' => $request->fcm_token,
                'last_token_update' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'FCM token updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update FCM token',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send test notification
     * POST /notifications/test
     */
    public function testNotification(): JsonResponse
    {
        try {
            $user = Auth::user();

            // Create test notification in database
            $notification = UserNotification::create([
                'user_id' => $user->id,
                'title' => 'Test Notification',
                'body' => 'This is a test notification to verify your settings.',
                'type' => 'system',
                'data' => json_encode(['test' => true]),
                'is_read' => false,
            ]);

            // Send via Firebase if FCM token exists
            if ($user->fcm_token) {
                $firebaseNotification = \Kreait\Firebase\Messaging\Notification::create(
                    'Test Notification',
                    'This is a test notification to verify your settings.'
                );

                $message = \Kreait\Firebase\Messaging\CloudMessage::withTarget('token', $user->fcm_token)
                    ->withNotification($firebaseNotification)
                    ->withData(['type' => 'test']);

                $this->messaging->send($message);
            }

            return response()->json([
                'success' => true,
                'message' => 'Test notification sent successfully',
                'data' => $notification
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enhanced default preferences creation
     */
    // private function createDefaultPreferences($userId): UserNotificationPreference
    // {
    //     return UserNotificationPreference::create([
    //         'user_id' => $userId,
    //         'chat_notifications' => true,
    //         'offer_notifications' => true,
    //         'item_notifications' => true,
    //         'marketing_notifications' => false,
    //         'email_notifications' => true,
    //         'sms_notifications' => false,
    //         'sound_enabled' => true,
    //         'vibration_enabled' => true,
    //         'led_enabled' => true,
    //         'quiet_hours_enabled' => false,
    //         'quiet_hours_start' => '22:00',
    //         'quiet_hours_end' => '07:00',
    //         'security_notifications' => true,
    //     ]);
    // }
}
