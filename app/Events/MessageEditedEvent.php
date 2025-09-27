<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageEditedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $messageId;
    public $chatId;
    public $newText;
    public $editedBy;

    public function __construct(string $messageId, string $chatId, string $newText, int $editedBy)
    {
        $this->messageId = $messageId;
        $this->chatId = $chatId;
        $this->newText = $newText;
        $this->editedBy = $editedBy;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('private-chat.' . $this->chatId)
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.edited';
    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->messageId,
            'chat_id' => $this->chatId,
            'new_text' => $this->newText,
            'edited_by' => $this->editedBy,
            'timestamp' => now()->toISOString()
        ];
    }
}
