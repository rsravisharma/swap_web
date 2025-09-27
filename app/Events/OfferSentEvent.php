<?php

namespace App\Events;

use App\Models\ChatOffer;
use App\Models\ChatSession;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OfferSentEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $offer;
    public $chat;

    public function __construct(ChatOffer $offer, ChatSession $chat)
    {
        $this->offer = $offer;
        $this->chat = $chat;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('private-chat.' . $this->chat->id)
        ];
    }

    public function broadcastAs(): string
    {
        return 'offer.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'offer_id' => $this->offer->id,
            'message_id' => $this->offer->message_id,
            'session_id' => $this->offer->session_id,
            'sender_id' => $this->offer->sender_id,
            'amount' => $this->offer->amount,
            'currency' => $this->offer->currency,
            'message' => $this->offer->message,
            'expires_at' => $this->offer->expires_at,
            'status' => $this->offer->status,
            'created_at' => $this->offer->created_at,
            'timestamp' => now()->toISOString()
        ];
    }
}
