<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\UserFollow;

class SyncUserStats extends Command
{
    protected $signature = 'users:sync-stats {--user_id=}';
    protected $description = 'Sync user statistics with actual data';

    public function handle()
    {
        $userId = $this->option('user_id');
        
        if ($userId) {
            $users = User::where('id', $userId)->get();
        } else {
            $users = User::all();
        }

        $this->info("Syncing stats for {$users->count()} users...");

        foreach ($users as $user) {
            $this->syncUserStats($user);
        }

        $this->info('Stats sync completed!');
    }

    private function syncUserStats(User $user)
    {
        $totalListings = Item::where('user_id', $user->id)->count();
        $activeListings = Item::where('user_id', $user->id)
                             ->where('status', 'active')->count();
        $itemsSold = Item::where('user_id', $user->id)
                        ->where('status', 'sold')->count();
        $itemsBought = Purchase::where('user_id', $user->id)->count();
        $followers = UserFollow::where('followed_id', $user->id)->count();
        $following = UserFollow::where('follower_id', $user->id)->count();

        $user->update([
            'total_listings' => $totalListings,
            'active_listings' => $activeListings,
            'items_sold' => $itemsSold,
            'items_bought' => $itemsBought,
            'followers_count' => $followers,
            'following_count' => $following,
            'stats_last_updated' => now(),
        ]);

        $this->line("✅ Synced stats for user {$user->id}");
    }
}
