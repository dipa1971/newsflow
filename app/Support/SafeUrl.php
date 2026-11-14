<?php

namespace App\Support;

class SafeUrl
{
    // ঠিকানাটি বাইরের (public) ওয়েবসাইটের কি না যাচাই করে। না হলে ত্রুটি ছোঁড়ে।
    public static function assertPublic(string $url): void
    {
        $parts = parse_url($url);
        $scheme = strtolower($parts['scheme'] ?? '');
        $host = $parts['host'] ?? '';

        if (!in_array($scheme, ['http', 'https'], true) || $host === '') {
            throw new \InvalidArgumentException('ঠিকানাটি সঠিক http বা https ঠিকানা নয়।');
        }

        // লোকাল টেস্টে .env দিয়ে খুলে রাখা যায়
        if (config('newsflow.allow_private_urls')) {
            return;
        }

        $ip = filter_var($host, FILTER_VALIDATE_IP) ? $host : gethostbyname($host);
        $isPublic = filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );

        if (!$isPublic) {
            throw new \InvalidArgumentException('ভেতরের নেটওয়ার্কের ঠিকানা অনুমোদিত নয়।');
        }
    }
}
