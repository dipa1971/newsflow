<?php

namespace App\Services;

use App\Support\SafeUrl;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ImageFetcher
{
    /** ছবি নামিয়ে storage/public-এ রাখে এবং সেখানকার পথ ফেরত দেয় */
    public function download(string $url, int $siteId): string
    {
        SafeUrl::assertPublic($url);

        $response = Http::timeout(20)
            ->withHeaders(['User-Agent' => 'NewsFlowBot/1.0'])
            ->get($url);

        if (!$response->successful()) {
            throw new \RuntimeException('ছবি নামানো যায়নি (HTTP ' . $response->status() . ')।');
        }

        $body = $response->body();

        if (strlen($body) > 8 * 1024 * 1024) {
            throw new \RuntimeException('ছবিটি ৮ MB-এর চেয়ে বড়।');
        }

        // ফাইলের নাম বা ধরনের ওপর ভরসা না করে ছবির ভেতর দেখে ধরন চিনি
        $info = @getimagesizefromstring($body);
        $ext = match ($info['mime'] ?? '') {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => null,
        };

        if (!$ext) {
            throw new \RuntimeException('ছবির ধরন সমর্থিত নয় (শুধু JPG, PNG ও WEBP)।');
        }

        $path = 'articles/' . $siteId . '/' . sha1($url) . '.' . $ext;
        Storage::disk('public')->put($path, $body);

        return $path;
    }
}
