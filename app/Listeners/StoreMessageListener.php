<?php

namespace App\Listeners;

use App\Events\MessageSentEvent;
use App\Models\ChatParticipant;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\UserNotification;
use App\Http\Controllers\Api\NotificationController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StoreMessageListener
{
    public function handle(MessageSentEvent $event): void
    {
        try {
            $message = $event->message;
            $session = $event->session;

            // 1. Update participant records
            ChatParticipant::getOrCreate($session->id, $session->user_one_id);
            ChatParticipant::getOrCreate($session->id, $session->user_two_id);

            ChatParticipant::where('session_id', $session->id)
                ->where('user_id', $message->sender_id)
                ->update(['last_read_at' => now()]);

            // 2. Store notification in database for offline users
            $this->storeNotificationInDatabase($message, $session);

            // 3. Send push notification (your existing method handles this)
            $notificationController = app(NotificationController::class);
            $notificationController->sendChatNotification($message);
        } catch (\Exception $e) {
            Log::error('Message processing failed', [
                'message_id' => $message->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function storeNotificationInDatabase(ChatMessage $message, ChatSession $session): void
    {
        $recipientId = $session->user_one_id === $message->sender_id
            ? $session->user_two_id
            : $session->user_one_id;

        UserNotification::create([
            'user_id' => $recipientId,
            'title' => 'New message from ' . $message->sender->name,
            'body' => Str::limit($message->message, 60),
            'type' => 'chat',
            'data' => json_encode([
                'session_id' => $session->id,
                'message_id' => $message->id,
                'sender_id' => $message->sender_id,
            ]),
            'is_read' => false,
        ]);
    }
}
