<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\ActivityLog;
use App\Models\Article;
use App\Models\Card;
use App\Services\CardRenderer;
use App\Services\ImageFetcher;
use App\Models\Post;

class ProcessArticle implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;
    public int $timeout = 180;

    public function __construct(public int $articleId)
    {
    }

    public function handle(ImageFetcher $images, CardRenderer $renderer): void
    {
        $article = Article::with('site')->find($this->articleId);

        if (!$article || !$article->site) {
            return;
        }

        $site = $article->site;

        // ছবি ছাড়া খবরের কার্ড হয় না
        if (!$article->image_url) {
            $article->update(['status' => 'skipped', 'skip_reason' => 'ছবি নেই']);
            ActivityLog::record($site->id, 'warn', 'বাদ গেছে (ছবি নেই): ' . $article->title, $article->id);
            return;
        }

        try {
            $photo = $images->download($article->image_url, $site->id);
            $out = 'cards/' . $site->id . '/' . $article->id . '.jpg';

            $renderer->render($site, $article->title, $photo, $out);

            $article->update(['image_path' => $photo, 'status' => 'carded', 'skip_reason' => null]);

            $card = Card::updateOrCreate(
                ['article_id' => $article->id],
                ['site_id' => $site->id, 'design' => $site->design, 'path' => $out]
            );
            $card->touch();   // একই তথ্য হলেও সময় হালনাগাদ হয়, যাতে ব্রাউজার নতুন ছবি দেখায়

            ActivityLog::record($site->id, 'info', 'কার্ড তৈরি হয়েছে: ' . $article->title, $article->id);
                    // পোস্টের গন্তব্য ঠিক করি
        $destinations = [];

        if ($site->post_facebook) {
            $fb = $site->connections()->where('provider', 'facebook')->first();
            if ($fb && $fb->status === 'active') {
                $destinations[] = 'facebook';
            }
        }
        if ($site->post_telegram && $site->telegramReady()) {
            $destinations[] = 'telegram';
        }

        if (!$destinations) {
            ActivityLog::record($site->id, 'info', 'কার্ড তৈরি হয়েছে, কিন্তু পোস্টের কোনো গন্তব্য চালু নেই।', $article->id);
        }

        foreach ($destinations as $destination) {
            // একই কার্ড একই গন্তব্যে একবারই (ডাটাবেসেও unique সূচক আছে)
            $post = Post::firstOrCreate(
                ['card_id' => $card->id, 'destination' => $destination],
                ['site_id' => $site->id, 'status' => 'pending']
            );

            if ($post->wasRecentlyCreated) {
                PublishCard::dispatch($post->id);
            } else {
                ActivityLog::record($site->id, 'info',
                    'এই খবর আগেই ' . $post->destinationLabel() . '-এর জন্য নেওয়া হয়েছে, দ্বিতীয়বার পাঠানো হয়নি।', $article->id);
            }
        }
        } catch (\Throwable $e) {
            $article->update(['status' => 'failed', 'skip_reason' => mb_substr($e->getMessage(), 0, 250)]);
            ActivityLog::record($site->id, 'error', 'কার্ড তৈরি ব্যর্থ: ' . $e->getMessage(), $article->id);
        }
    }
}
