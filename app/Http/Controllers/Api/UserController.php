<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationSetting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function getNotificationSettings(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Get or create notification settings for user
            $settings = NotificationSetting::firstOrCreate(
                ['user_id' => $user->id],
                [
                    // Default settings
                    'email_notifications' => true,
                    'push_notifications' => true,
                    'sms_notifications' => false,
                    'marketing_emails' => true,
                    'new_message_notifications' => true,
                    'new_offer_notifications' => true,
                    'security_notifications' => true,
                    'product_update_notifications' => true,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Notification settings retrieved successfully',
                'data' => $settings->only([
                    'email_notifications',
                    'push_notifications', 
                    'sms_notifications',
                    'marketing_emails',
                    'new_message_notifications',
                    'new_offer_notifications',
                    'security_notifications',
                    'product_update_notifications'
                ])
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error getting notification settings: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get notification settings',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Update user's notification settings
     */
    public function updateNotificationSettings(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Validation rules
            $validator = Validator::make($request->all(), [
                'email_notifications' => 'boolean',
                'push_notifications' => 'boolean',
                'sms_notifications' => 'boolean',
                'marketing_emails' => 'boolean',
                'new_message_notifications' => 'boolean',
                'new_offer_notifications' => 'boolean',
                'security_notifications' => 'boolean',
                'product_update_notifications' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update or create notification settings
            $settings = NotificationSetting::updateOrCreate(
                ['user_id' => $user->id],
                $request->only([
                    'email_notifications',
                    'push_notifications',
                    'sms_notifications', 
                    'marketing_emails',
                    'new_message_notifications',
                    'new_offer_notifications',
                    'security_notifications',
                    'product_update_notifications'
                ])
            );

            return response()->json([
                'success' => true,
                'message' => 'Notification settings updated successfully',
                'data' => $settings->only([
                    'email_notifications',
                    'push_notifications',
                    'sms_notifications',
                    'marketing_emails', 
                    'new_message_notifications',
                    'new_offer_notifications',
                    'security_notifications',
                    'product_update_notifications'
                ])
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error updating notification settings: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update notification settings',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
