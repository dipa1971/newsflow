<?php

namespace App\Console\Commands;

use App\Services\FeedReader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ProbeFeed extends Command
{
    protected $signature = 'feeds:probe {url : ফিডের পুরো ঠিকানা}';

    protected $description = 'একটা ফিডের ঠিকানা পরীক্ষা করে দেখায় ঠিক কী উত্তর আসছে';

    public function handle(FeedReader $reader): int
    {
        $url = (string) $this->argument('url');

        try {
            $response = Http::timeout(20)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; NewsFlowBot/1.0)'])
                ->get($url);
        } catch (\Throwable $e) {
            $this->error('ঠিকানায় পৌঁছানো যায়নি: ' . $e->getMessage());

            return self::FAILURE;
        }

        $body = $response->body();

        $this->line('HTTP অবস্থা: ' . $response->status());
        $this->line('Content-Type: ' . $response->header('Content-Type'));
        $this->line('আকার: ' . strlen($body) . ' বাইট');
        $this->line('--- শুরুর ৪০০ অক্ষর ---');
        $this->line(mb_substr($body, 0, 400));
        $this->line('--- শেষ ---');

        try {
            $items = $reader->fetch($url);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info(count($items) . 'টি খবর পাওয়া গেছে।');

        if ($items) {
            $this->line('প্রথম খবর: ' . $items[0]['title']);
            $this->line('ছবি: ' . ($items[0]['image'] ?: 'নেই'));
        }

        return self::SUCCESS;
    }
}
