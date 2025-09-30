<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

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

            $ably = new \Ably\AblyRest([
                'key' => config('ably.api_key')
            ]);

            // 🔥 FIX: Generate token with proper channel permissions
            $tokenDetails = $ably->auth->requestToken([
                'clientId' => (string) $currentUserId,
                'capability' => [
                    // Allow access to user's private channels
                    "private-chat.*" => ["*"], // All operations on all private chat channels
                    "presence-chat.*" => ["*"], // All operations on presence channels
                    "typing.*" => ["*"], // Typing indicators
                ],
                'ttl' => 3600000 // 1 hour in milliseconds
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'token' => $tokenDetails->token,
                    'expires_at' => $tokenDetails->expires / 1000, // Convert to seconds
                    'client_id' => $tokenDetails->clientId,
                    'capability' => $tokenDetails->capability,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to generate Ably token', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
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
                        'messages' => "user:{$user->id}:messages",
                        'typing' => "user:{$user->id}:typing",
                        'offers' => "user:{$user->id}:offers"
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Ably config failed', [
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
