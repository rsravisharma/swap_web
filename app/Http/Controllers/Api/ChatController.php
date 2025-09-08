<?php

namespace App\Http\Controllers\Api;

use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Ably\AblyRest;
use App\Http\Controllers\Controller;

class ChatController extends Controller
{
    protected $ably;

    public function __construct()
    {
        // Initialize Ably client
        $this->ably = new AblyRest([
            'key' => config('services.ably.key'),
        ]);
    }

    /**
     * Start or get existing chat session
     * POST /chat/session
     */
    public function startSession(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'participant_id' => 'required|integer|exists:users,id',
            'item_id' => 'nullable|integer|exists:items,id', // For item-specific chats
            'session_type' => 'in:direct,group,item_inquiry',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $currentUserId = Auth::id();
            $participantId = $request->participant_id;
            $itemId = $request->item_id;
            $sessionType = $request->session_type ?? 'direct';

            // Check if user is trying to chat with themselves
            if ($currentUserId === $participantId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot start chat session with yourself'
                ], 400);
            }

            // Check if session already exists
            $existingSession = ChatSession::where(function ($query) use ($currentUserId, $participantId, $itemId) {
                $query->where(function ($q) use ($currentUserId, $participantId) {
                    $q->where('user_one_id', $currentUserId)
                      ->where('user_two_id', $participantId);
                })->orWhere(function ($q) use ($currentUserId, $participantId) {
                    $q->where('user_one_id', $participantId)
                      ->where('user_two_id', $currentUserId);
                });
                
                if ($itemId) {
                    $query->where('item_id', $itemId);
                }
            })->first();

            if ($existingSession) {
                // Update last activity
                $existingSession->touch();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Chat session retrieved',
                    'data' => [
                        'session' => $this->formatSessionResponse($existingSession),
                        'ably_channel' => "chat:{$existingSession->id}",
                        'ably_auth_token' => $this->generateAblyToken($currentUserId, $existingSession->id)
                    ]
                ]);
            }

            // Create new session
            $session = ChatSession::create([
                'user_one_id' => $currentUserId,
                'user_two_id' => $participantId,
                'item_id' => $itemId,
                'session_type' => $sessionType,
                'status' => 'active',
                'last_message_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Chat session created',
                'data' => [
                    'session' => $this->formatSessionResponse($session),
                    'ably_channel' => "chat:{$session->id}",
                    'ably_auth_token' => $this->generateAblyToken($currentUserId, $session->id)
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start chat session',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send message
     * POST /chat/message
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|integer|exists:chat_sessions,id',
            'message' => 'required|string|max:1000',
            'message_type' => 'in:text,image,file,offer,location',
            'metadata' => 'nullable|json',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $currentUserId = Auth::id();
            $sessionId = $request->session_id;

            // Verify user is part of this session
            $session = ChatSession::where('id', $sessionId)
                ->where(function ($query) use ($currentUserId) {
                    $query->where('user_one_id', $currentUserId)
                          ->orWhere('user_two_id', $currentUserId);
                })
                ->first();

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chat session not found or access denied'
                ], 404);
            }

            // Check if session is active
            if ($session->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Chat session is not active'
                ], 400);
            }

            // Create message in database
            $message = ChatMessage::create([
                'session_id' => $sessionId,
                'sender_id' => $currentUserId,
                'message' => $request->message,
                'message_type' => $request->message_type ?? 'text',
                'metadata' => $request->metadata ? json_decode($request->metadata, true) : null,
                'status' => 'sent',
            ]);

            // Update session last message time
            $session->update([
                'last_message_at' => now(),
                'last_message' => $request->message,
            ]);

            // Format message for real-time transmission
            $messageData = $this->formatMessageResponse($message);

            // Send message via Ably (real-time)
            $channel = $this->ably->channel("chat:{$sessionId}");
            $channel->publish('new_message', $messageData);

            // Send push notification to other participant(s)
            $this->sendPushNotification($session, $message);

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => $messageData
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get messages for a session (paginated)
     * GET /chat/session/{sessionId}/messages
     */
    public function getMessages(Request $request, int $sessionId): JsonResponse
    {
        try {
            $currentUserId = Auth::id();

            // Verify user is part of this session
            $session = ChatSession::where('id', $sessionId)
                ->where(function ($query) use ($currentUserId) {
                    $query->where('user_one_id', $currentUserId)
                          ->orWhere('user_two_id', $currentUserId);
                })
                ->first();

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chat session not found or access denied'
                ], 404);
            }

            $page = $request->query('page', 1);
            $perPage = $request->query('per_page', 50);

            // Get messages with pagination (newest first)
            $messages = ChatMessage::where('session_id', $sessionId)
                ->with(['sender:id,name,profile_image'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            // Mark messages as read for current user
            ChatMessage::where('session_id', $sessionId)
                ->where('sender_id', '!=', $currentUserId)
                ->where('status', '!=', 'read')
                ->update(['status' => 'read', 'read_at' => now()]);

            return response()->json([
                'success' => true,
                'data' => [
                    'messages' => $messages->items(),
                    'pagination' => [
                        'current_page' => $messages->currentPage(),
                        'last_page' => $messages->lastPage(),
                        'per_page' => $messages->perPage(),
                        'total' => $messages->total(),
                        'has_more' => $messages->hasMorePages(),
                    ],
                    'session' => $this->formatSessionResponse($session)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve messages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all chat sessions for current user
     * GET /chat/sessions
     */
    public function getUserSessions(Request $request): JsonResponse
    {
        try {
            $currentUserId = Auth::id();
            $page = $request->query('page', 1);
            $perPage = $request->query('per_page', 20);

            $sessions = ChatSession::where(function ($query) use ($currentUserId) {
                $query->where('user_one_id', $currentUserId)
                      ->orWhere('user_two_id', $currentUserId);
            })
            ->with(['userOne:id,name,profile_image', 'userTwo:id,name,profile_image', 'item:id,title,images'])
            ->orderBy('last_message_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => [
                    'sessions' => $sessions->items(),
                    'pagination' => [
                        'current_page' => $sessions->currentPage(),
                        'last_page' => $sessions->lastPage(),
                        'per_page' => $sessions->perPage(),
                        'total' => $sessions->total(),
                        'has_more' => $sessions->hasMorePages(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve chat sessions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark session as read
     * POST /chat/session/{sessionId}/read
     */
    public function markAsRead(Request $request, int $sessionId): JsonResponse
    {
        try {
            $currentUserId = Auth::id();

            ChatMessage::where('session_id', $sessionId)
                ->where('sender_id', '!=', $currentUserId)
                ->where('status', '!=', 'read')
                ->update(['status' => 'read', 'read_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Messages marked as read'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark messages as read',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete/Archive session
     * DELETE /chat/session/{sessionId}
     */
    public function deleteSession(Request $request, int $sessionId): JsonResponse
    {
        try {
            $currentUserId = Auth::id();

            $session = ChatSession::where('id', $sessionId)
                ->where(function ($query) use ($currentUserId) {
                    $query->where('user_one_id', $currentUserId)
                          ->orWhere('user_two_id', $currentUserId);
                })
                ->first();

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chat session not found'
                ], 404);
            }

            // Soft delete - mark as archived for this user
            $session->update(['status' => 'archived']);

            return response()->json([
                'success' => true,
                'message' => 'Chat session archived'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete chat session',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate Ably auth token for user
     */
    private function generateAblyToken(int $userId, int $sessionId): string
    {
        try {
            $tokenDetails = $this->ably->auth->requestToken([
                'clientId' => (string) $userId,
                'capability' => [
                    "chat:{$sessionId}" => ['publish', 'subscribe'],
                ],
                'ttl' => 3600000, // 1 hour in milliseconds
            ]);

            return $tokenDetails->token;
        } catch (\Exception $e) {
            \Log::error('Failed to generate Ably token: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Format session response
     */
    private function formatSessionResponse(ChatSession $session): array
    {
        $currentUserId = Auth::id();
        $otherUser = $session->user_one_id === $currentUserId ? $session->userTwo : $session->userOne;

        return [
            'id' => $session->id,
            'session_type' => $session->session_type,
            'status' => $session->status,
            'last_message' => $session->last_message,
            'last_message_at' => $session->last_message_at,
            'other_user' => [
                'id' => $otherUser->id,
                'name' => $otherUser->name,
                'profile_image' => $otherUser->profile_image,
            ],
            'item' => $session->item ? [
                'id' => $session->item->id,
                'title' => $session->item->title,
                'image' => $session->item->images[0] ?? null,
            ] : null,
            'unread_count' => $session->messages()
                ->where('sender_id', '!=', $currentUserId)
                ->where('status', '!=', 'read')
                ->count(),
            'created_at' => $session->created_at,
        ];
    }

    /**
     * Format message response
     */
    private function formatMessageResponse(ChatMessage $message): array
    {
        return [
            'id' => $message->id,
            'session_id' => $message->session_id,
            'sender_id' => $message->sender_id,
            'sender' => [
                'id' => $message->sender->id,
                'name' => $message->sender->name,
                'profile_image' => $message->sender->profile_image,
            ],
            'message' => $message->message,
            'message_type' => $message->message_type,
            'metadata' => $message->metadata,
            'status' => $message->status,
            'created_at' => $message->created_at,
            'read_at' => $message->read_at,
        ];
    }

    /**
     * Send push notification to other participant
     */
    private function sendPushNotification(ChatSession $session, ChatMessage $message): void
    {
        // Implement push notification logic here
        // This would integrate with Firebase or another push service
        $currentUserId = Auth::id();
        $recipientId = $session->user_one_id === $currentUserId ? $session->user_two_id : $session->user_one_id;
        
        // Queue push notification job
        // dispatch(new SendChatNotificationJob($recipientId, $message));
    }
}
