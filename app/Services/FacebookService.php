<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class FacebookService
{
    public function configured(): bool
    {
        return filled(config('newsflow.facebook.app_id')) && filled(config('newsflow.facebook.app_secret'));
    }

    private function version(): string
    {
        return config('newsflow.facebook.graph_version');
    }

    private function base(): string
    {
        return 'https://graph.facebook.com/' . $this->version();
    }

    // ফেসবুকের লগইন ও অনুমতির পাতার ঠিকানা
public function loginUrl(string $redirect, string $state): string
{
    $params = [
        'client_id' => config('newsflow.facebook.app_id'),
        'redirect_uri' => $redirect,
        'state' => $state,
        'response_type' => 'code',
    ];



    if (filled(config('newsflow.facebook.config_id'))) {
        $params['config_id'] = config('newsflow.facebook.config_id');
        $params['override_default_response_type'] = 'true'; 

    } else {
        $params['scope'] = implode(',', config('newsflow.facebook.scopes'));
    }

    return 'https://www.facebook.com/' . $this->version() . '/dialog/oauth?' . http_build_query($params);
}

    // ধাপ ১: ফেসবুক যে "কোড" দিয়ে ফেরত পাঠায়, সেটা দিয়ে অল্প সময়ের টোকেন নিই
    public function exchangeCode(string $code, string $redirect): string
    {
        $json = $this->parse(Http::timeout(20)->get($this->base() . '/oauth/access_token', [
            'client_id' => config('newsflow.facebook.app_id'),
            'client_secret' => config('newsflow.facebook.app_secret'),
            'redirect_uri' => $redirect,
            'code' => $code,
        ]));

        return $json['access_token'];
    }

    // ধাপ ২: অল্প সময়ের টোকেনকে দীর্ঘ মেয়াদের টোকেনে বদলাই
    public function longLived(string $token): string
    {
        $json = $this->parse(Http::timeout(20)->get($this->base() . '/oauth/access_token', [
            'grant_type' => 'fb_exchange_token',
            'client_id' => config('newsflow.facebook.app_id'),
            'client_secret' => config('newsflow.facebook.app_secret'),
            'fb_exchange_token' => $token,
        ]));

        return $json['access_token'];
    }

    // ধাপ ৩: ব্যবহারকারী যে পেজগুলো চালান, সেগুলোর তালিকা (প্রতিটি পেজের নিজস্ব টোকেনসহ)
    public function pages(string $userToken): array
    {
        $json = $this->parse(Http::timeout(20)->get($this->base() . '/me/accounts', [
            'fields' => 'id,name,access_token',
            'limit' => 100,
            'access_token' => $userToken,
        ]));

        return $json['data'] ?? [];
    }

    // পেজে ছবি পোস্ট। ফাইলটা সরাসরি আপলোড করি, তাই লোকালেও কাজ করে
    public function postPhoto(string $pageId, string $pageToken, string $file, string $caption): array
    {
        $response = Http::timeout(60)
            ->attach('source', file_get_contents($file), 'card.jpg')
            ->post($this->base() . '/' . $pageId . '/photos', [
                'message' => $caption,
                'published' => 'true',
                'access_token' => $pageToken,
            ]);

        return $this->parse($response);
    }

    // পোস্টের নিচে কমেন্ট
    public function comment(string $objectId, string $pageToken, string $message): string
    {
        $json = $this->parse(Http::timeout(20)->asForm()->post($this->base() . '/' . $objectId . '/comments', [
            'message' => $message,
            'access_token' => $pageToken,
        ]));

        return (string) ($json['id'] ?? '');
    }

    // উত্তর ঠিক থাকলে ডেটা ফেরত দেয়, নইলে বুঝিয়ে ত্রুটি ছোঁড়ে
    private function parse(Response $response): array
    {
        $json = $response->json() ?? [];

        if ($response->successful() && !isset($json['error'])) {
            return $json;
        }

        $error = $json['error'] ?? [];
        $code = (int) ($error['code'] ?? 0);
        $raw = $error['message'] ?? ('HTTP ' . $response->status());

        $auth = $code === 190;
        $retry = in_array($code, [1, 2, 4, 17, 32, 341, 613], true) || $response->status() >= 500;

        if ($auth) {
            $message = 'ফেসবুকের অনুমতির মেয়াদ শেষ বা বাতিল হয়েছে। সেটিংসে গিয়ে আবার যুক্ত করুন।';
        } elseif ($code >= 200 && $code < 300 || $code === 10) {
            $message = 'এই পেজে পোস্ট বা কমেন্টের অনুমতি নেই। (' . $raw . ')';
        } else {
            $message = 'ফেসবুকের ত্রুটি: ' . $raw;
        }

        throw new PublishException($message, $retry, $auth);
    }
}
