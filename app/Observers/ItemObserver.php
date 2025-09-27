<?php

namespace App\Observers;

use App\Models\Item;
use App\Models\User;

class ItemObserver
{
    public function created(Item $item)
    {
        // Increment total_listings when item is created
        User::where('id', $item->user_id)->increment('total_listings');
        User::where('id', $item->user_id)->increment('active_listings');
    }

    public function updated(Item $item)
    {
        if ($item->isDirty('status')) {
            $user = $item->user;
            
            // Handle status changes
            if ($item->status === 'sold' && $item->getOriginal('status') !== 'sold') {
                // Item just got sold
                $user->increment('items_sold');
                $user->decrement('active_listings');
                
                // Add earnings if price is set
                if ($item->price) {
                    $user->increment('total_earnings', $item->price);
                }
            } elseif ($item->getOriginal('status') === 'sold' && $item->status !== 'sold') {
                // Item was unsold
                $user->decrement('items_sold');
                $user->increment('active_listings');
                
                if ($item->price) {
                    $user->decrement('total_earnings', $item->price);
                }
            }
        }
    }

    public function deleted(Item $item)
    {
        $user = $item->user;
        $user->decrement('total_listings');
        
        if ($item->status === 'active') {
            $user->decrement('active_listings');
        }
    }
}
