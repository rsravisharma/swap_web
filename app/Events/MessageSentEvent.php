<?php

namespace App\Events;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSentEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $session;

    public function __construct(ChatMessage $message, ChatSession $session)
    {
        $this->message = $message;
        $this->session = $session;
        
        // 🔥 DEBUG: Log when event is created
        \Log::info('MessageSentEvent created', [
            'message_id' => $message->id,
            'session_id' => $session->id,
            'channel' => 'private-chat.' . $session->id
        ]);
    }

    public function broadcastOn(): array
    {
        $channelName = 'private-chat.' . $this->session->id;
        \Log::info('Broadcasting on channel', ['channel' => $channelName]);
        
        return [
            new PrivateChannel($channelName)
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        $data = [
            'id' => $this->message->id,
            'session_id' => $this->message->session_id,
            'sender_id' => $this->message->sender_id,
            'message' => $this->message->message,
            'message_type' => $this->message->message_type,
            'metadata' => $this->message->metadata,
            'status' => $this->message->status,
            'created_at' => $this->message->created_at,
            'sender' => [
                'id' => $this->message->sender->id,
                'name' => $this->message->sender->name,
                'profile_image' => $this->message->sender->profile_image,
            ]
        ];
        
        \Log::info('Broadcasting data', ['data' => $data]);
        return $data;
    }
}
