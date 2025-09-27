<?php

namespace App\Events;

use App\Models\ChatOffer;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OfferRejectedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $offer;
    public $rejectedBy;

    public function __construct(ChatOffer $offer, int $rejectedBy)
    {
        $this->offer = $offer;
        $this->rejectedBy = $rejectedBy;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('private-chat.' . $this->offer->session_id)
        ];
    }

    public function broadcastAs(): string
    {
        return 'offer.rejected';
    }

    public function broadcastWith(): array
    {
        return [
            'offer_id' => $this->offer->id,
            'message_id' => $this->offer->message_id,
            'session_id' => $this->offer->session_id,
            'sender_id' => $this->offer->sender_id,
            'rejected_by' => $this->rejectedBy,
            'amount' => $this->offer->amount,
            'currency' => $this->offer->currency,
            'status' => 'rejected',
            'rejected_at' => now()->toISOString(),
            'timestamp' => now()->toISOString()
        ];
    }
}
