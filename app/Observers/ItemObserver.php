<?php

namespace App\Observers;

use App\Models\Item;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ItemObserver
{
    /**
     * Handle the Item "created" event.
     */
    public function created(Item $item)
    {
        // ✅ Increment both total_listings and active_listings atomically
        DB::table('users')
            ->where('id', $item->user_id)
            ->update([
                'total_listings' => DB::raw('total_listings + 1'),
                'active_listings' => DB::raw('active_listings + 1'),
            ]);

        Log::info('📊 User stats updated after item creation', [
            'user_id' => $item->user_id,
            'item_id' => $item->id,
        ]);
    }

    /**
     * Handle the Item "updated" event.
     * 
     * ⚠️ IMPORTANT: Do NOT handle sales here!
     * Sales are handled in MeetupController@confirm() to avoid double counting.
     */
    public function updated(Item $item)
    {
        // ❌ REMOVED: Sales tracking (handled in MeetupController)
        // Only handle status changes that are NOT sales
        
        if ($item->isDirty('status')) {
            $oldStatus = $item->getOriginal('status');
            $newStatus = $item->status;

            Log::info('🔄 Item status changed', [
                'item_id' => $item->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);

            // Handle archive/unarchive ONLY
            if ($newStatus === 'archived' && $oldStatus === 'active') {
                // Item was archived
                DB::table('users')
                    ->where('id', $item->user_id)
                    ->where('active_listings', '>', 0)
                    ->decrement('active_listings');

                Log::info('📦 Item archived, active_listings decremented', [
                    'user_id' => $item->user_id,
                    'item_id' => $item->id,
                ]);
            } elseif ($newStatus === 'active' && $oldStatus === 'archived') {
                // Item was unarchived
                DB::table('users')
                    ->where('id', $item->user_id)
                    ->increment('active_listings');

                Log::info('🔓 Item unarchived, active_listings incremented', [
                    'user_id' => $item->user_id,
                    'item_id' => $item->id,
                ]);
            }
            // ❌ DO NOT handle 'sold' status here - it's handled in MeetupController!
        }
    }

    /**
     * Handle the Item "deleted" event.
     */
    public function deleted(Item $item)
    {
        // Decrement total_listings always
        // Decrement active_listings only if item was active
        DB::table('users')
            ->where('id', $item->user_id)
            ->update([
                'total_listings' => DB::raw('GREATEST(total_listings - 1, 0)'),
                'active_listings' => $item->status === 'active' 
                    ? DB::raw('GREATEST(active_listings - 1, 0)')
                    : DB::raw('active_listings'),
            ]);

        Log::info('🗑️ User stats updated after item deletion', [
            'user_id' => $item->user_id,
            'item_id' => $item->id,
            'item_status' => $item->status,
        ]);
    }
}
