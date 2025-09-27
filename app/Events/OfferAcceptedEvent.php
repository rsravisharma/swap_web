<?php

namespace App\Events;

use App\Models\ChatOffer;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OfferAcceptedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $offer;
    public $acceptedBy;

    public function __construct(ChatOffer $offer, int $acceptedBy)
    {
        $this->offer = $offer;
        $this->acceptedBy = $acceptedBy;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('private-chat.' . $this->offer->session_id)
        ];
    }

    public function broadcastAs(): string
    {
        return 'offer.accepted';
    }

    public function broadcastWith(): array
    {
        return [
            'offer_id' => $this->offer->id,
            'message_id' => $this->offer->message_id,
            'session_id' => $this->offer->session_id,
            'sender_id' => $this->offer->sender_id,
            'accepted_by' => $this->acceptedBy,
            'amount' => $this->offer->amount,
            'currency' => $this->offer->currency,
            'status' => 'accepted',
            'accepted_at' => now()->toISOString(),
            'timestamp' => now()->toISOString()
        ];
    }
}
