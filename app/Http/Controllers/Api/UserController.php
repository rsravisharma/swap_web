<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationSetting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

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
            Log::error('Error getting notification settings: ' . $e->getMessage());

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
            Log::error('Error updating notification settings: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update notification settings',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        Log::debug('Request payload:', $request->all());
        $user = $request->user();

        $localizedToEnum = [
            'पुरुष' => 'male',
            'महिला' => 'female',
            'अन्य' => 'other',
            'नहीं कहना पसंद करते हैं' => 'prefer_not_to_say',
            'अनुपलब्ध' => null,
        ];

        $requestData = $request->all();

        // Map gender
        if (!empty($requestData['gender']) && isset($localizedToEnum[$requestData['gender']])) {
            $requestData['gender'] = $localizedToEnum[$requestData['gender']];
        }

        // Handle image upload separately
        if ($request->hasFile('image')) {
            Log::info('Profile update request contains an image.', ['user_id' => $user->id]);

            $image = $request->file('image');
            $filename = uniqid() . '.' . $image->getClientOriginalExtension();

            // Resize and compress (max 300px width, 75% quality)
            $resized = Image::make($image)
                ->resize(300, null, function ($constraint) {
                    $constraint->aspectRatio(); // Keep aspect ratio
                    $constraint->upsize();      // Don’t upscale small images
                })
                ->encode($image->getClientOriginalExtension(), 75); // 75% quality

            // Save to storage/app/public/profile_images
            $path = 'profile_images/' . $filename;
            Storage::disk('public')->put($path, $resized);

            // Save public URL to DB
            $user->profile_image = asset('storage/' . $path);
            $user->save();
        }

        // Map dob, pob, birth_time, country, etc.
        $fieldsToNormalize = ['date_of_birth', 'place_of_birth', 'birth_time', 'phone', 'country', 'gender'];
        foreach ($fieldsToNormalize as $field) {
            if (!empty($requestData[$field]) && in_array($requestData[$field], ['अनुपलब्ध', ''])) {
                $requestData[$field] = null;
            }
        }
        $request->replace($requestData);

        // Validate only the fields that are present
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
            'gender' => 'sometimes|nullable|in:male,female,other,prefer_not_to_say',
            'date_of_birth' => 'sometimes|nullable|date',
            'place_of_birth' => 'sometimes|nullable|string|max:255',
            'birth_time' => 'sometimes|nullable|string',
            'country' => 'sometimes|nullable|string|max:255',
        ]);

        // Update only the validated fields
        if (!empty($validated)) {
            $user->update($validated);
        }

        if (empty($validated) && !$request->hasFile('image')) {
            return response()->json([
                'success' => false,
                'message' => 'No data to update'
            ], 422);
        }

        Log::info('User updated their profile', [
            'user_id' => $user->id,
            'updated_fields' => array_keys($validated)
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }

    public function userProfile()
    {
        $user = auth()->user();

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'profile_image' => $user->profile_image,
                'phone' => $user->phone,
                'role' => $user->role,
                'gender' => $user->gender,
                'chart_preference' => $user->chart_preference,
                'place_of_birth' => $user->place_of_birth,
                'country' => $user->country,
                'date_of_birth' => $user->date_of_birth,
                'birth_time' => $user->birth_time,
                'wallet_balance' => $user->wallet_balance,
            ]
        ]);
    }

    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user = $request->user();

        $user->fcm_token = $request->fcm_token;
        $user->save();

        return response()->json(['status' => 'token_updated']);
    }
}
