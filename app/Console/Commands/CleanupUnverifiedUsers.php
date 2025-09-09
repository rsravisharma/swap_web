<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\EmailVerificationCode;

class CleanupUnverifiedUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:cleanup-unverified';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up unverified users older than 7 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deletedUsers = 0;
        
        User::where('email_verified_at', null)
            ->where('google_id', null)
            ->where('facebook_id', null)
            ->where('created_at', '<', now()->subDays(7))
            ->each(function ($user) use (&$deletedUsers) {
                // Clean up verification codes
                EmailVerificationCode::where('user_id', $user->id)->delete();
                $user->delete();
                $deletedUsers++;
            });

        $this->info("Cleaned up {$deletedUsers} unverified users.");
        
        return Command::SUCCESS;
    }
}
