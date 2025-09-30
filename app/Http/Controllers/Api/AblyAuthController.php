<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AblyAuthController extends Controller
{
    /**
     * Generate Ably JWT token for authenticated user
     * GET /auth/ably-token
     */
    public function getAblyToken(Request $request): JsonResponse
    {
        try {
            $currentUserId = Auth::id();

            if (!$currentUserId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }

            // 🔥 FIX: Use the correct config path
            $ablyKey = config('services.ably.key');
            
            if (!$ablyKey) {
                Log::error('Ably key not found in config', [
                    'config_value' => $ablyKey,
                    'env_value' => env('ABLY_KEY')
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Ably configuration missing'
                ], 500);
            }

            Log::info('Generating Ably token', [
                'user_id' => $currentUserId,
                'key_length' => strlen($ablyKey)
            ]);

            $ably = new \Ably\AblyRest([
                'key' => $ablyKey  // 🔥 Use the correct key
            ]);

            // Generate token with proper channel permissions
            $tokenDetails = $ably->auth->requestToken([
                'clientId' => (string) $currentUserId,
                'capability' => [
                    "private-chat.*" => ["*"], // All operations on all private chat channels
                    "presence-chat.*" => ["*"], // All operations on presence channels  
                    "typing.*" => ["*"], // Typing indicators
                ],
                'ttl' => 3600000 // 1 hour in milliseconds
            ]);

            Log::info('Ably token generated successfully', [
                'user_id' => $currentUserId,
                'client_id' => $tokenDetails->clientId,
                'expires' => $tokenDetails->expires
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'token' => $tokenDetails->token,
                    'expires_at' => intval($tokenDetails->expires / 1000), // Convert to seconds
                    'client_id' => $tokenDetails->clientId,
                    'capability' => $tokenDetails->capability,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to generate Ably token', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate authentication token',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get Ably configuration for client  
     * GET /auth/ably-config
     */
    public function getAblyConfig(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'auth_url' => url('/api/auth/ably-token'),
                    'client_id' => (string) $user->id,
                    'channels' => [
                        'messages' => "private-chat.*",  // 🔥 Updated to match Laravel broadcast
                        'typing' => "typing.*",
                        'offers' => "offers.*"
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Ably config failed', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get Ably config',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
