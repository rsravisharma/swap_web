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
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Get Ably API key from environment - FIXED CONFIG KEY
            $ablyApiKey = config('services.ably.key');
            
            if (!$ablyApiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ably API key not configured'
                ], 500);
            }

            // Split API key into key name and secret
            $apiKeyCredentials = explode(':', $ablyApiKey);
            
            if (count($apiKeyCredentials) !== 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Ably API key format'
                ], 500);
            }

            $keyName = $apiKeyCredentials[0];
            $keySecret = $apiKeyCredentials[1];

            // Create JWT payload
            $currentTime = time();
            $payload = [
                'iat' => $currentTime,
                'exp' => $currentTime + 3600, // 1 hour expiry
                'x-ably-capability' => json_encode([
                    "user:{$user->id}:*" => ["*"], // User-specific channels
                    "private-chat.*" => ["subscribe", "publish"], // Chat channels
                ]),
                'x-ably-clientId' => (string) $user->id, // Set client ID to user ID
            ];

            // Generate JWT token
            $jwt = JWT::encode(
                $payload,
                $keySecret,
                'HS256',
                $keyName
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'token' => $jwt,
                    'client_id' => (string) $user->id,
                    'expires_at' => $currentTime + 3600,
                    'channels' => [
                        "user:{$user->id}:messages",
                        "user:{$user->id}:typing",
                        "user:{$user->id}:offers"
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Ably token generation failed', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate Ably token',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
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
