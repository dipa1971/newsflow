<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\ActivityLog;
use App\Models\Connection;
use App\Models\Post;
use App\Services\CaptionBuilder;
use App\Services\FacebookService;
use App\Services\PublishException;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Storage;

class PublishCard implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;
    public int $timeout = 120;

    public function __construct(public int $postId)
    {
    }

    public function handle(FacebookService $facebook, TelegramService $telegram, CaptionBuilder $captions): void
    {
        $post = Post::with(['card.article', 'site'])->find($this->postId);

        if (!$post || !$post->card || !$post->site) {
            return;
        }

        // আগেই পাঠানো হয়ে গেলে আর কখনো না (দ্বিতীয়বার পোস্ট আটকানোর মূল জায়গা)
        if ($post->status === 'sent') {
            return;
        }

        $post->update(['status' => 'sending', 'attempts' => $post->attempts + 1]);

        try {
            if ($post->destination === 'facebook') {
                $this->sendFacebook($post, $facebook, $captions);
            } else {
                $this->sendTelegram($post, $telegram, $captions);
            }

            $post->update(['status' => 'sent', 'sent_at' => now(), 'error' => null]);

            ActivityLog::record(
                $post->site_id,
                'info',
                $post->destinationLabel() . '-এ পাঠানো হয়েছে: ' . $post->card->article->title,
                $post->card->article_id
            );
        } catch (PublishException $e) {
            $this->failed($post, $e->getMessage(), $e->retryable, $e->auth);
        } catch (\Throwable $e) {
            // নেটওয়ার্ক ইত্যাদি অজানা সমস্যা: আবার চেষ্টা করে দেখি
            $this->failed($post, $e->getMessage(), true, false);
        }
    }

    private function sendFacebook(Post $post, FacebookService $facebook, CaptionBuilder $captions): void
    {
        $site = $post->site;
        $article = $post->card->article;
        $connection = Connection::where('site_id', $site->id)->where('provider', 'facebook')->first();

        if (!$connection || $connection->status !== 'active') {
            throw new PublishException('ফেসবুক যুক্ত নেই। সেটিংসে গিয়ে যুক্ত করুন।', false, true);
        }

        // ধাপ ১: ছবি পোস্ট। remote_id থাকলে আগেই হয়ে গেছে, আবার করি না
        if (!$post->remote_id) {
            $file = Storage::disk('public')->path($post->card->path);
            $result = $facebook->postPhoto(
                $connection->external_id,
                $connection->token,
                $file,
                $captions->forFacebook($site, $article)
            );

            $post->update(['remote_id' => (string) ($result['post_id'] ?? $result['id'] ?? '')]);
        }

        // ধাপ ২: প্রথম কমেন্টে খবরের লিংক
        if (!$post->comment_id) {
            $commentId = $facebook->comment(
                $post->remote_id,
                $connection->token,
                $captions->commentFor($article)
            );

            $post->update(['comment_id' => $commentId]);
        }
    }

    private function sendTelegram(Post $post, TelegramService $telegram, CaptionBuilder $captions): void
    {
        $site = $post->site;

        if (!$site->telegramReady()) {
            throw new PublishException('টেলিগ্রামের বট টোকেন বা চ্যানেল দেওয়া নেই।', false, true);
        }

        $file = Storage::disk('public')->path($post->card->path);
        $messageId = $telegram->sendPhoto(
            $site->telegram_token,
            $site->telegram_chat,
            $file,
            $captions->forTelegram($site, $post->card->article)
        );

        $post->update(['remote_id' => (string) $messageId]);
    }

    private function failed(Post $post, string $message, bool $retryable, bool $auth): void
    {
        // লগে বা পাতায় যেন টোকেন ফাঁস না হয়
        $message = preg_replace('/access_token=[^&\s]+/', 'access_token=***', $message);
        $message = preg_replace('/bot\d+:[A-Za-z0-9_-]+/', 'bot***', $message);
        $message = mb_substr($message, 0, 400);

        $articleId = $post->card->article_id;
        $label = $post->destinationLabel();

        // টোকেন বাতিল হলে সংযোগকে "মেয়াদ শেষ" চিহ্নিত করি
        if ($auth && $post->destination === 'facebook') {
            Connection::where('site_id', $post->site_id)->where('provider', 'facebook')->update(['status' => 'expired']);
        }

        // সাময়িক সমস্যা: ১, ৫, ১৫ মিনিট পরে আবার চেষ্টা
        $delays = config('newsflow.retry_delays');
        if ($retryable && $post->attempts <= count($delays)) {
            $delay = $delays[$post->attempts - 1];
            $post->update(['status' => 'retrying', 'error' => $message]);
            self::dispatch($post->id)->delay(now()->addSeconds($delay));

            ActivityLog::record($post->site_id, 'warn',
                $label . '-এ পাঠানো যায়নি, ' . intdiv($delay, 60) . ' মিনিট পরে আবার চেষ্টা হবে: ' . $message, $articleId);

            return;
        }

        // ফেসবুকে ছবি পোস্ট হয়ে গেছে, শুধু কমেন্ট যায়নি: ছবি আবার পাঠাব না, "পাঠানো" ধরি
        if ($post->remote_id) {
            $post->update([
                'status' => 'sent',
                'sent_at' => now(),
                'error' => 'পোস্ট হয়েছে, কিন্তু কমেন্টে লিংক বসেনি: ' . $message,
            ]);

            ActivityLog::record($post->site_id, 'warn',
                $label . '-এ পোস্ট হয়েছে, কিন্তু কমেন্টে লিংক বসেনি: ' . $message, $articleId);

            return;
        }

        $post->update(['status' => 'failed', 'error' => $message]);

        ActivityLog::record($post->site_id, 'error', $label . '-এ পাঠানো ব্যর্থ: ' . $message, $articleId);
    }
}
