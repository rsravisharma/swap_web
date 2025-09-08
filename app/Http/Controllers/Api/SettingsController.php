<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    /**
     * Get user's language setting
     * GET /user/settings/language
     */
    public function getUserLanguage(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $setting = UserSetting::where('user_id', $user->id)
                ->where('key', 'language_code')
                ->first();

            $languageCode = $setting ? $setting->value : 'en';

            return response()->json([
                'success' => true,
                'data' => [
                    'language_code' => $languageCode,
                    'language' => $languageCode
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get user language: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve language setting'
            ], 500);
        }
    }

    /**
     * Save user's language preference
     * POST /user/settings/language
     */
    public function saveUserLanguage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'language_code' => 'required|string|size:2|in:en,es,fr,de,it,pt,ru,zh,ja,ko',
            'language' => 'sometimes|string|size:2'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid language code',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $languageCode = $request->input('language_code', $request->input('language'));

            // Validate supported languages
            $supportedLanguages = ['en', 'es', 'fr', 'de', 'it', 'pt', 'ru', 'zh', 'ja', 'ko'];
            
            if (!in_array($languageCode, $supportedLanguages)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Language not supported'
                ], 400);
            }

            UserSetting::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'key' => 'language_code'
                ],
                [
                    'value' => $languageCode
                ]
            );

            // Clear user settings cache
            Cache::forget("user_settings_{$user->id}");

            return response()->json([
                'success' => true,
                'data' => [
                    'language_code' => $languageCode,
                    'language' => $languageCode
                ],
                'message' => 'Language preference saved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to save user language: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save language preference'
            ], 500);
        }
    }

    /**
     * Get all user settings
     * GET /user/settings
     */
    public function getAllSettings(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $settings = Cache::remember("user_settings_{$user->id}", 3600, function () use ($user) {
                return UserSetting::where('user_id', $user->id)
                    ->pluck('value', 'key')
                    ->toArray();
            });

            // Set defaults for missing settings
            $defaultSettings = [
                'language_code' => 'en',
                'theme' => 'light',
                'notifications_enabled' => 'true',
                'push_notifications' => 'true',
                'email_notifications' => 'true',
                'sms_notifications' => 'false',
                'privacy_profile' => 'public',
                'privacy_items' => 'public',
                'currency' => 'USD',
                'timezone' => 'UTC'
            ];

            $settings = array_merge($defaultSettings, $settings);

            return response()->json([
                'success' => true,
                'data' => $settings
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get user settings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve settings'
            ], 500);
        }
    }

    /**
     * Update multiple user settings
     * PUT /user/settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
            'settings.language_code' => 'sometimes|string|size:2|in:en,es,fr,de,it,pt,ru,zh,ja,ko',
            'settings.theme' => 'sometimes|string|in:light,dark,system',
            'settings.notifications_enabled' => 'sometimes|boolean',
            'settings.push_notifications' => 'sometimes|boolean',
            'settings.email_notifications' => 'sometimes|boolean',
            'settings.sms_notifications' => 'sometimes|boolean',
            'settings.privacy_profile' => 'sometimes|string|in:public,private,friends',
            'settings.privacy_items' => 'sometimes|string|in:public,private,university',
            'settings.currency' => 'sometimes|string|size:3',
            'settings.timezone' => 'sometimes|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid settings data',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $settings = $request->input('settings');

            foreach ($settings as $key => $value) {
                // Convert boolean values to string for storage
                if (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                }

                UserSetting::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'key' => $key
                    ],
                    [
                        'value' => $value
                    ]
                );
            }

            // Clear cache
            Cache::forget("user_settings_{$user->id}");

            return response()->json([
                'success' => true,
                'data' => $settings,
                'message' => 'Settings updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update settings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings'
            ], 500);
        }
    }

    /**
     * Get specific user setting
     * GET /user/settings/{key}
     */
    public function getSetting(string $key): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $setting = UserSetting::where('user_id', $user->id)
                ->where('key', $key)
                ->first();

            if (!$setting) {
                // Return default values for known settings
                $defaultValues = [
                    'language_code' => 'en',
                    'theme' => 'light',
                    'notifications_enabled' => 'true',
                    'currency' => 'USD',
                    'timezone' => 'UTC'
                ];

                $value = $defaultValues[$key] ?? null;
            } else {
                $value = $setting->value;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'key' => $key,
                    'value' => $value
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get setting: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve setting'
            ], 500);
        }
    }

    /**
     * Update specific user setting
     * PUT /user/settings/{key}
     */
    public function updateSetting(Request $request, string $key): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Value is required',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $value = $request->input('value');

            // Convert boolean values to string
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }

            // Validate specific settings
            if (!$this->validateSettingValue($key, $value)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid value for setting'
                ], 400);
            }

            UserSetting::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'key' => $key
                ],
                [
                    'value' => $value
                ]
            );

            // Clear cache
            Cache::forget("user_settings_{$user->id}");

            return response()->json([
                'success' => true,
                'data' => [
                    'key' => $key,
                    'value' => $value
                ],
                'message' => 'Setting updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update setting: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update setting'
            ], 500);
        }
    }

    /**
     * Delete specific user setting (reset to default)
     * DELETE /user/settings/{key}
     */
    public function deleteSetting(string $key): JsonResponse
    {
        try {
            $user = Auth::user();
            
            UserSetting::where('user_id', $user->id)
                ->where('key', $key)
                ->delete();

            // Clear cache
            Cache::forget("user_settings_{$user->id}");

            return response()->json([
                'success' => true,
                'message' => 'Setting reset to default'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete setting: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset setting'
            ], 500);
        }
    }

    /**
     * Reset all settings to default
     * DELETE /user/settings
     */
    public function resetAllSettings(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            UserSetting::where('user_id', $user->id)->delete();

            // Clear cache
            Cache::forget("user_settings_{$user->id}");

            return response()->json([
                'success' => true,
                'message' => 'All settings reset to default'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to reset settings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset settings'
            ], 500);
        }
    }

    /**
     * Get available options for settings
     * GET /user/settings/options
     */
    public function getSettingsOptions(): JsonResponse
    {
        try {
            $options = [
                'languages' => [
                    ['code' => 'en', 'name' => 'English'],
                    ['code' => 'es', 'name' => 'Spanish'],
                    ['code' => 'fr', 'name' => 'French'],
                    ['code' => 'de', 'name' => 'German'],
                    ['code' => 'it', 'name' => 'Italian'],
                    ['code' => 'pt', 'name' => 'Portuguese'],
                    ['code' => 'ru', 'name' => 'Russian'],
                    ['code' => 'zh', 'name' => 'Chinese'],
                    ['code' => 'ja', 'name' => 'Japanese'],
                    ['code' => 'ko', 'name' => 'Korean'],
                ],
                'themes' => [
                    ['code' => 'light', 'name' => 'Light'],
                    ['code' => 'dark', 'name' => 'Dark'],
                    ['code' => 'system', 'name' => 'System'],
                ],
                'privacy_levels' => [
                    ['code' => 'public', 'name' => 'Public'],
                    ['code' => 'private', 'name' => 'Private'],
                    ['code' => 'friends', 'name' => 'Friends Only'],
                    ['code' => 'university', 'name' => 'University Only'],
                ],
                'currencies' => [
                    ['code' => 'USD', 'name' => 'US Dollar'],
                    ['code' => 'EUR', 'name' => 'Euro'],
                    ['code' => 'GBP', 'name' => 'British Pound'],
                    ['code' => 'JPY', 'name' => 'Japanese Yen'],
                    ['code' => 'CAD', 'name' => 'Canadian Dollar'],
                    ['code' => 'AUD', 'name' => 'Australian Dollar'],
                    ['code' => 'INR', 'name' => 'Indian Rupee'],
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $options
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get settings options: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve settings options'
            ], 500);
        }
    }

    /**
     * Validate setting values
     */
    private function validateSettingValue(string $key, $value): bool
    {
        switch ($key) {
            case 'language_code':
                return in_array($value, ['en', 'es', 'fr', 'de', 'it', 'pt', 'ru', 'zh', 'ja', 'ko']);
            
            case 'theme':
                return in_array($value, ['light', 'dark', 'system']);
            
            case 'privacy_profile':
            case 'privacy_items':
                return in_array($value, ['public', 'private', 'friends', 'university']);
            
            case 'notifications_enabled':
            case 'push_notifications':
            case 'email_notifications':
            case 'sms_notifications':
                return in_array($value, ['true', 'false']);
            
            case 'currency':
                return in_array($value, ['USD', 'EUR', 'GBP', 'JPY', 'CAD', 'AUD', 'INR']);
            
            default:
                return true; // Allow other settings without specific validation
        }
    }
}