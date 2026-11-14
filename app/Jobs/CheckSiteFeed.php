<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\ActivityLog;
use App\Models\Article;
use App\Models\Site;
use App\Services\FeedReader;
use App\Support\Bangla;
use Illuminate\Database\UniqueConstraintViolationException;

class CheckSiteFeed implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;
public int $timeout = 120;

public function __construct(public int $siteId)
{
}

public function handle(FeedReader $reader): void
{
    $site = Site::find($this->siteId);

    if (!$site || !$site->feed_url) {
        return;
    }

    if (!$site->isUsable()) {
        ActivityLog::record($site->id, 'warn', 'সাইট চালু নেই বা ট্রায়ালের মেয়াদ শেষ, তাই ফিড পড়া হয়নি।');
        return;
    }

    // ১) ফিড পড়ি
    try {
        $items = $reader->fetch($site->feed_url);
    } catch (\Throwable $e) {
        $site->update(['last_checked_at' => now(), 'last_error' => $e->getMessage()]);
        ActivityLog::record($site->id, 'error', 'ফিড পড়া যায়নি: ' . $e->getMessage());
        return;
    }

    $firstRead = $site->baseline_done_at === null;
    $limit = (int) config('newsflow.max_new_per_check');
    $newCount = 0;
    $baselineCount = 0;

    // ২) প্রতিটি খবর দেখি
    foreach ($items as $item) {
        $hash = Article::hashFor($item['guid']);

        // আগে দেখা খবর হলে বাদ (ডুপ্লিকেট সুরক্ষা)
        if (Article::where('site_id', $site->id)->where('guid_hash', $hash)->exists()) {
            continue;
        }

        $status = 'pending';
        $reason = null;

        if ($firstRead) {
            $status = 'baseline';               // প্রথমবার: সব পুরনো ধরি
        } elseif ($newCount >= $limit) {
            $status = 'skipped';
            $reason = 'একসাথে অনেক খবর এসেছে';
        }

        try {
            $article = Article::create(
                ['site_id' => $site->id, 'guid_hash' => $hash, 'skip_reason' => $reason]
                + Article::attributesFromItem($item, $status)
            );
        } catch (UniqueConstraintViolationException $e) {
            continue;                           // অন্য একটি কাজ আগেই বসিয়ে ফেলেছে
        }

        if ($status === 'pending') {
            $newCount++;
            ProcessArticle::dispatch($article->id);
        } elseif ($status === 'baseline') {
            $baselineCount++;
        }
    }

    // ৩) সাইটের অবস্থা হালনাগাদ
    $site->update([
        'last_checked_at' => now(),
        'last_error' => null,
        'baseline_done_at' => $site->baseline_done_at ?? now(),
    ]);

    // ৪) লগ (কিছু ঘটলে তবেই, নইলে প্রতি ৫ মিনিটে লগ ভরে যাবে)
    if ($firstRead) {
        ActivityLog::record($site->id, 'info',
            'প্রথমবার ফিড পড়া হয়েছে। ' . Bangla::digits($baselineCount) . 'টি বর্তমান খবর পুরনো ধরা হলো, এগুলোর কার্ড হবে না।');
    } elseif ($newCount > 0) {
        ActivityLog::record($site->id, 'info',
            Bangla::digits($newCount) . 'টি নতুন খবর পাওয়া গেছে, কার্ড তৈরি হচ্ছে।');
    }
}


}
