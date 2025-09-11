<?php

namespace App\Console\Commands;

use App\Models\TrendingSearch;
use Illuminate\Console\Command;

class CleanupTrendingSearches extends Command
{
    protected $signature = 'trending:cleanup {--days=30}';
    protected $description = 'Clean up old trending search records';

    public function handle()
    {
        $days = $this->option('days');
        $deleted = TrendingSearch::cleanupOldRecords($days);
        
        $this->info("Deleted {$deleted} old trending search records older than {$days} days.");
    }
}
