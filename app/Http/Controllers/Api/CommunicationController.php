<?php

namespace App\Http\Controllers\Api;

use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Models\ChatOffer;
use App\Models\User;
use App\Models\BlockedUser;
use App\Models\ChatReport;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

// Broadcasting Events
use App\Events\MessageSentEvent;
use App\Events\MessageReadEvent;
use App\Events\OfferSentEvent;
use App\Events\OfferAcceptedEvent;
use App\Events\OfferRejectedEvent;
use App\Events\MessageDeletedEvent;
use App\Events\MessageEditedEvent;

class CommunicationController extends Controller
{

    /**
     * Get all chats for current user
     * GET /chats
     */
    public function getChats(Request $request): JsonResponse
    {
        try {
            $currentUserId = Auth::id();
            $page = $request->query('page', 1);
            $limit = $request->query('limit', 20);

            $chats = ChatSession::where(function ($query) use ($currentUserId) {
                $query->where('user_one_id', $currentUserId)
                    ->orWhere('user_two_id', $currentUserId);
            })
                ->with([
                    'userOne:id,name,profile_image',
                    'userTwo:id,name,profile_image',
                    'item:id,title,images',
                    'lastMessage'
                ])
                ->whereDoesntHave('participants', function ($query) use ($currentUserId) {
                    $query->where('user_id', $currentUserId)
                        ->where('is_archived', true);
                })
                ->orderBy('last_message_at', 'desc')
                ->paginate($limit, ['*'], 'page', $page);

            $formattedChats = $chats->items()->map(function ($chat) use ($currentUserId) {
                return $this->formatChatResponse($chat, $currentUserId);
            });

            return response()->json([
                'success' => true,
                'data' => $formattedChats,
                'pagination' => [
                    'current_page' => $chats->currentPage(),
                    'last_page' => $chats->lastPage(),
                    'total' => $chats->total(),
                    'has_more' => $chats->hasMorePages(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch chats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete chat
     * DELETE /chats/{chatId}
     */
    public function deleteChat(Request $request, string $chatId): JsonResponse
    {
        try {
            $currentUserId = Auth::id();

            $chat = ChatSession::where('id', $chatId)
                ->where(function ($query) use ($currentUserId) {
                    $query->where('user_one_id', $currentUserId)
                        ->orWhere('user_two_id', $currentUserId);
                })
                ->first();

            if (!$chat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chat not found'
                ], 404);
            }

            // Soft delete - mark as archived for this user
            $chat->participants()->updateOrCreate(
                ['user_id' => $currentUserId],
                ['is_archived' => true, 'archived_at' => now()]
            );

            return response()->json([
                'success' => true,
                'message' => 'Chat deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete chat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark chat as read
     * PUT /chats/{chatId}/mark-read
     */
    public function markChatAsRead(Request $request, string $chatId): JsonResponse
    {
        try {
            $currentUserId = Auth::id();

            // Mark all messages as read
            ChatMessage::where('session_id', $chatId)
                ->where('sender_id', '!=', $currentUserId)
                ->where('status', '!=', 'read')
                ->update(['status' => 'read', 'read_at' => now()]);

            // UPDATED: Use Laravel Broadcasting instead of direct Ably
            broadcast(new MessageReadEvent($chatId, $currentUserId))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Chat marked as read'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark chat as read',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update chat archive status
     * PUT /chats/{chatId}/archive
     */
    public function updateChatArchiveStatus(Request $request, string $chatId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'is_archived' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $currentUserId = Auth::id();
            $isArchived = $request->boolean('is_archived');

            $chat = ChatSession::where('id', $chatId)
                ->where(function ($query) use ($currentUserId) {
                    $query->where('user_one_id', $currentUserId)
                        ->orWhere('user_two_id', $currentUserId);
                })
                ->first();

            if (!$chat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chat not found'
                ], 404);
            }

            $chat->participants()->updateOrCreate(
                ['user_id' => $currentUserId],
                [
                    'is_archived' => $isArchived,
                    'archived_at' => $isArchived ? now() : null
                ]
            );

            return response()->json([
                'success' => true,
                'message' => $isArchived ? 'Chat archived' : 'Chat unarchived'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update archive status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get chat messages with pagination
     * GET /chats/{chatId}/messages
     */
    public function getChatMessages(Request $request, string $chatId): JsonResponse
    {
        try {
            $currentUserId = Auth::id();
            $page = $request->query('page', 1);
            $limit = $request->query('limit', 50);

            // Verify chat access
            $chat = ChatSession::where('id', $chatId)
                ->where(function ($query) use ($currentUserId) {
                    $query->where('user_one_id', $currentUserId)
                        ->orWhere('user_two_id', $currentUserId);
                })
                ->first();

            if (!$chat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chat not found'
                ], 404);
            }

            // Get messages
            $messages = ChatMessage::where('session_id', $chatId)
                ->with('sender:id,name,profile_image')
                ->orderBy('created_at', 'desc')
                ->paginate($limit, ['*'], 'page', $page);

            $formattedMessages = collect($messages->items())->map(function ($message) {
                return [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'sender' => $message->sender,
                    'message' => $message->message,
                    'message_type' => $message->message_type ?? 'text',
                    'status' => $message->status ?? 'sent',
                    'created_at' => $message->created_at,
                    'metadata' => $message->metadata ?? [],
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'messages' => $formattedMessages,
                    'pagination' => [
                        'current_page' => $messages->currentPage(),
                        'last_page' => $messages->lastPage(),
                        'total' => $messages->total(),
                        'has_more' => $messages->hasMorePages(),
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to retrieve chat messages', [
                'chat_id' => $chatId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve messages',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Send message
     * POST /chats/{chatId}/messages
     */
    /**
     * Send message - FINAL OPTIMIZED VERSION
     * POST /chats/{chatId}/messages
     */
    public function sendMessage(Request $request, string $chatId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:2000',
            'message_type' => 'in:text,image,file,offer,location',
            'metadata' => 'nullable|array',
            'reply_to_id' => 'nullable|exists:chat_messages,id',
            'sender_id' => 'required|string|min:1', // Add minimum length
            'session_id' => 'nullable|string',
        ], [
            'sender_id.required' => 'User authentication failed - please log in again',
            'sender_id.min' => 'Invalid user ID provided',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $currentUserId = Auth::id();

            // Verify chat access
            $chat = ChatSession::where('id', $chatId)
                ->where(function ($query) use ($currentUserId) {
                    $query->where('user_one_id', $currentUserId)
                        ->orWhere('user_two_id', $currentUserId);
                })
                ->first();

            if (!$chat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chat not found'
                ], 404);
            }

            // ADD: Check if session is active (from ChatController)
            if ($chat->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Chat session is not active'
                ], 400);
            }

            // Create message
            $message = ChatMessage::create([
                'session_id' => $chatId,
                'sender_id' => $currentUserId,
                'message' => $request->message,
                'message_type' => $request->message_type ?? 'text',
                'metadata' => $request->metadata ? json_decode($request->metadata, true) : null,
                'reply_to_id' => $request->reply_to_id,
                'status' => 'sent',
            ]);

            // Update chat last message
            $chat->update([
                'last_message' => $request->message,
                'last_message_at' => now(),
            ]);

            // Load sender relationship
            $message->load('sender:id,name,profile_image');

            // Broadcast to Ably + trigger synchronous event listener
            broadcast(new MessageSentEvent($message, $chat))->toOthers();

            // ADD: Push notification (from ChatController)
            $this->sendPushNotification($chat, $message);

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => $this->formatMessageResponse($message)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function sendPushNotification(ChatSession $session, ChatMessage $message): void
    {
        try {
            // Use your existing NotificationController method
            $notificationController = app(NotificationController::class);
            $notificationController->sendChatNotification($message);
        } catch (\Exception $e) {
            Log::error('Push notification failed: ' . $e->getMessage());
        }
    }


    /**
     * Upload chat image
     * POST /upload/chat-image
     */
    public function uploadChatImage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $image = $request->file('image');
            $path = $image->store('chat-images', 'public');
            $imageUrl = Storage::url($path);

            return response()->json([
                'success' => true,
                'image_url' => $imageUrl
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload chat files
     * POST /upload/chat-files
     */
    public function uploadChatFiles(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'files.*' => 'required|file|max:10240', // 10MB max per file
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $fileUrls = [];

            foreach ($request->file('files') as $file) {
                $path = $file->store('chat-files', 'public');
                $fileUrls[] = Storage::url($path);
            }

            return response()->json([
                'success' => true,
                'file_urls' => $fileUrls
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload files',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get offer history for chat
     * GET /chats/{chatId}/offers
     */
    public function getOfferHistory(Request $request, string $chatId): JsonResponse
    {
        try {
            $currentUserId = Auth::id();

            // Verify chat access
            $chat = ChatSession::where('id', $chatId)
                ->where(function ($query) use ($currentUserId) {
                    $query->where('user_one_id', $currentUserId)
                        ->orWhere('user_two_id', $currentUserId);
                })
                ->first();

            if (!$chat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chat not found'
                ], 404);
            }

            $offers = ChatOffer::where('session_id', $chatId)
                ->with(['sender:id,name,profile_image', 'message'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $offers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch offer history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send offer
     * POST /chats/{chatId}/offers
     */
    public function sendOffer(Request $request, string $chatId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'message' => 'nullable|string|max:500',
            'expires_at' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $currentUserId = Auth::id();

            // Verify chat access
            $chat = ChatSession::where('id', $chatId)
                ->where(function ($query) use ($currentUserId) {
                    $query->where('user_one_id', $currentUserId)
                        ->orWhere('user_two_id', $currentUserId);
                })
                ->first();

            if (!$chat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chat not found'
                ], 404);
            }

            // Create offer message first
            $message = ChatMessage::create([
                'session_id' => $chatId,
                'sender_id' => $currentUserId,
                'message' => "Offer: {$request->currency} {$request->amount}" . ($request->message ? " - {$request->message}" : ""),
                'message_type' => 'offer',
                'status' => 'sent',
            ]);

            // Create offer
            $offer = ChatOffer::create([
                'session_id' => $chatId,
                'message_id' => $message->id,
                'sender_id' => $currentUserId,
                'amount' => $request->amount,
                'currency' => $request->currency,
                'message' => $request->message,
                'expires_at' => $request->expires_at,
                'status' => 'pending',
            ]);

            // UPDATED: Use Laravel Broadcasting
            broadcast(new OfferSentEvent($offer, $chat))->toOthers();

            return response()->json([
                'success' => true,
                'data' => $offer
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send offer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Accept offer
     * PUT /chats/{chatId}/offers/{messageId}/accept
     */
    public function acceptOffer(Request $request, string $chatId, string $messageId): JsonResponse
    {
        try {
            $currentUserId = Auth::id();

            $offer = ChatOffer::where('message_id', $messageId)
                ->where('session_id', $chatId)
                ->where('sender_id', '!=', $currentUserId) // Can't accept own offer
                ->where('status', 'pending')
                ->first();

            if (!$offer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Offer not found or already processed'
                ], 404);
            }

            $offer->update([
                'status' => 'accepted',
                'accepted_by' => $currentUserId,
                'accepted_at' => now(),
            ]);

            // UPDATED: Use Laravel Broadcasting
            broadcast(new OfferAcceptedEvent($offer, $currentUserId))->toOthers();

            $notificationController = app(NotificationController::class);
            $notificationController->sendOfferNotification($offer, 'accepted');

            return response()->json([
                'success' => true,
                'message' => 'Offer accepted'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to accept offer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject offer
     * PUT /chats/{chatId}/offers/{messageId}/reject
     */
    public function rejectOffer(Request $request, string $chatId, string $messageId): JsonResponse
    {
        try {
            $currentUserId = Auth::id();

            $offer = ChatOffer::where('message_id', $messageId)
                ->where('session_id', $chatId)
                ->where('sender_id', '!=', $currentUserId) // Can't reject own offer
                ->where('status', 'pending')
                ->first();

            if (!$offer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Offer not found or already processed'
                ], 404);
            }

            $offer->update([
                'status' => 'rejected',
                'rejected_by' => $currentUserId,
                'rejected_at' => now(),
            ]);

            // UPDATED: Use Laravel Broadcasting
            broadcast(new OfferRejectedEvent($offer, $currentUserId))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Offer rejected'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject offer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete message
     * DELETE /chats/{chatId}/messages/{messageId}
     */
    public function deleteMessage(Request $request, string $chatId, string $messageId): JsonResponse
    {
        try {
            $currentUserId = Auth::id();

            $message = ChatMessage::where('id', $messageId)
                ->where('session_id', $chatId)
                ->where('sender_id', $currentUserId)
                ->first();

            if (!$message) {
                return response()->json([
                    'success' => false,
                    'message' => 'Message not found or access denied'
                ], 404);
            }

            $message->update([
                'message' => '[Message deleted]',
                'is_deleted' => true,
                'deleted_at' => now(),
            ]);

            // UPDATED: Use Laravel Broadcasting
            broadcast(new MessageDeletedEvent($messageId, $chatId, $currentUserId))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Message deleted'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Edit message
     * PUT /chats/{chatId}/messages/{messageId}
     */
    public function editMessage(Request $request, string $chatId, string $messageId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $currentUserId = Auth::id();

            $message = ChatMessage::where('id', $messageId)
                ->where('session_id', $chatId)
                ->where('sender_id', $currentUserId)
                ->first();

            if (!$message) {
                return response()->json([
                    'success' => false,
                    'message' => 'Message not found or access denied'
                ], 404);
            }

            $message->update([
                'message' => $request->text,
                'is_edited' => true,
                'edited_at' => now(),
            ]);

            // UPDATED: Use Laravel Broadcasting
            broadcast(new MessageEditedEvent($messageId, $chatId, $request->text, $currentUserId))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Message updated'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Block user
     * POST /users/{userId}/block
     */
    public function blockUser(Request $request, string $userId): JsonResponse
    {
        try {
            $currentUserId = Auth::id();

            if ($currentUserId == $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot block yourself'
                ], 400);
            }

            BlockedUser::updateOrCreate(
                ['blocker_id' => $currentUserId, 'blocked_id' => $userId],
                ['status' => 'active', 'reason' => 'Blocked via chat']
            );

            return response()->json([
                'success' => true,
                'message' => 'User blocked successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to block user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unblock user
     * POST /users/{userId}/unblock
     */
    public function unblockUser(Request $request, string $userId): JsonResponse
    {
        try {
            $currentUserId = Auth::id();

            BlockedUser::where('blocker_id', $currentUserId)
                ->where('blocked_id', $userId)
                ->update(['status' => 'resolved']);

            return response()->json([
                'success' => true,
                'message' => 'User unblocked successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unblock user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Report chat
     * POST /chats/{chatId}/report
     */
    public function reportChat(Request $request, string $chatId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $currentUserId = Auth::id();

            ChatReport::create([
                'reporter_id' => $currentUserId,
                'session_id' => $chatId,
                'reason' => $request->reason,
                'status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Chat reported successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to report chat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search messages in chat
     * GET /chats/{chatId}/messages/search
     */
    public function searchMessages(Request $request, string $chatId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:2|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $currentUserId = Auth::id();
            $query = $request->query('q');

            // Verify chat access
            $chat = ChatSession::where('id', $chatId)
                ->where(function ($q) use ($currentUserId) {
                    $q->where('user_one_id', $currentUserId)
                        ->orWhere('user_two_id', $currentUserId);
                })
                ->first();

            if (!$chat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chat not found'
                ], 404);
            }

            $messages = ChatMessage::where('session_id', $chatId)
                ->where('message', 'LIKE', "%{$query}%")
                ->where('is_deleted', false)
                ->with(['sender:id,name,profile_image'])
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $messages
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search messages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unread message count
     * GET /chats/unread-count
     */
    public function getUnreadMessageCount(Request $request): JsonResponse
    {
        try {
            $currentUserId = Auth::id();

            $count = ChatMessage::whereHas('session', function ($query) use ($currentUserId) {
                $query->where('user_one_id', $currentUserId)
                    ->orWhere('user_two_id', $currentUserId);
            })
                ->where('sender_id', '!=', $currentUserId)
                ->where('status', '!=', 'read')
                ->count();

            return response()->json([
                'success' => true,
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get unread count',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ping endpoint for connection check
     * GET /ping
     */
    public function ping(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'pong',
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Format chat response for API
     */
    private function formatChatResponse($chat, $currentUserId): array
    {
        $otherUser = $chat->user_one_id === $currentUserId ? $chat->userTwo : $chat->userOne;

        return [
            'id' => $chat->id,
            'session_type' => $chat->session_type,
            'status' => $chat->status,
            'last_message' => $chat->last_message,
            'last_message_at' => $chat->last_message_at,
            'other_user' => [
                'id' => $otherUser->id,
                'name' => $otherUser->name,
                'profile_image' => $otherUser->profile_image,
            ],
            'item' => $chat->item ? [
                'id' => $chat->item->id,
                'title' => $chat->item->title,
                'image' => $chat->item->images[0] ?? null,
            ] : null,
            'unread_count' => $chat->messages()
                ->where('sender_id', '!=', $currentUserId)
                ->where('status', '!=', 'read')
                ->count(),
        ];
    }

    /**
     * Format message response for API
     */
    private function formatMessageResponse($message): array
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
            'is_edited' => $message->is_edited ?? false,
            'is_deleted' => $message->is_deleted ?? false,
            'created_at' => $message->created_at,
            'edited_at' => $message->edited_at,
            'read_at' => $message->read_at,
        ];
    }
}
