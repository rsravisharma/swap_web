<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageDeletedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $messageId;
    public $chatId;
    public $deletedBy;

    public function __construct(string $messageId, string $chatId, int $deletedBy)
    {
        $this->messageId = $messageId;
        $this->chatId = $chatId;
        $this->deletedBy = $deletedBy;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('private-chat.' . $this->chatId)
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.deleted';
    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->messageId,
            'chat_id' => $this->chatId,
            'deleted_by' => $this->deletedBy,
            'timestamp' => now()->toISOString()
        ];
    }
}
