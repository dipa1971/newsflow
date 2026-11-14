<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class TelegramService
{
    private function url(string $token, string $method): string
    {
        return 'https://api.telegram.org/bot' . $token . '/' . $method;
    }

    // টোকেন ও চ্যানেল ঠিক আছে কি না যাচাই করে, বটের নাম ফেরত দেয়
    public function check(string $token, string $chat): string
    {
        $me = $this->parse(Http::timeout(20)->post($this->url($token, 'getMe')));
        $this->parse(Http::timeout(20)->post($this->url($token, 'getChat'), ['chat_id' => $chat]));

        return $me['username'] ?? '';
    }

    public function sendPhoto(string $token, string $chat, string $file, string $caption): int
    {
        $response = Http::timeout(60)
            ->attach('photo', file_get_contents($file), 'card.jpg')
            ->post($this->url($token, 'sendPhoto'), [
                'chat_id' => $chat,
                'caption' => $caption,
            ]);

        return (int) ($this->parse($response)['message_id'] ?? 0);
    }

    private function parse(Response $response): array
    {
        $json = $response->json() ?? [];

        if (($json['ok'] ?? false) === true) {
            return is_array($json['result']) ? $json['result'] : [];
        }

        $code = (int) ($json['error_code'] ?? $response->status());
        $description = $json['description'] ?? 'অজানা ত্রুটি';

        throw new PublishException(
            'টেলিগ্রামের ত্রুটি: ' . $description,
            $code === 429 || $code >= 500,
            in_array($code, [401, 403], true)
        );
    }
}
