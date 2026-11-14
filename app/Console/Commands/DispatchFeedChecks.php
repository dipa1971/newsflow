<?php

namespace App\Console\Commands;

use App\Jobs\CheckSiteFeed;
use App\Models\Site;
use Illuminate\Console\Command;

class DispatchFeedChecks extends Command
{
    protected $signature = 'feeds:dispatch';

    protected $description = 'সব চালু সাইটের ফিড পরীক্ষার কাজ কিউতে দেয়';

    public function handle(): int
    {
        $sites = Site::where('status', 'active')
            ->whereNotNull('feed_url')
            ->where('trial_ends_at', '>', now())
            ->get();

        foreach ($sites as $site) {
            CheckSiteFeed::dispatch($site->id);
        }

        $this->info($sites->count() . 'টি সাইটের কাজ কিউতে দেওয়া হয়েছে।');

        return self::SUCCESS;
    }
}
