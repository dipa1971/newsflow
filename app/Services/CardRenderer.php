<?php

namespace App\Services;

use App\Models\Site;
use App\Support\Bangla;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class CardRenderer
{
    // ডিজাইনের কী => নাম। resources/views/cards/ ফোল্ডারের ফাইলের নামের সঙ্গে মিলতে হবে
    public const DESIGNS = [
        'design1' => 'ক্লাসিক ব্যান্ড',
        'design2' => 'গাঢ় ওভারলে',
        'design3' => 'পাশে রেখা',
        'design4' => 'ফ্রেমে ছবি',
        'design5' => 'উপরে শিরোনাম',
    ];

    /**
     * কার্ড বানিয়ে storage/app/public/$outPath-এ রাখে।
     * $photoPath ও $outPath দুটোই "public" ডিস্কের ভেতরের পথ।
     */
    public function render(Site $site, string $headline, string $photoPath, string $outPath): void
    {
        $width = config('newsflow.card_width');
        $height = config('newsflow.card_height');

        $design = array_key_exists((string) $site->design, self::DESIGNS) ? $site->design : 'design1';
        $disk = Storage::disk('public');

        // ১) ডিজাইনের HTML বানাই
        $html = view('cards.' . $design, [
            'headline' => $headline,
            'siteName' => $site->name,
            'dateText' => Bangla::date(now()),
            'photo' => $this->dataUri($disk->path($photoPath)),
            'logo' => $site->logo_path ? $this->dataUri($disk->path($site->logo_path)) : null,
            'font' => $this->fontCss(),
        ])->render();

        // ২) অস্থায়ী ফাইলে রাখি
        $tmpDir = storage_path('app/tmp');
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0775, true);
        }
        $name = Str::random(12);
        $htmlFile = $tmpDir . DIRECTORY_SEPARATOR . $name . '.html';
        $pngFile = $tmpDir . DIRECTORY_SEPARATOR . $name . '.png';
        file_put_contents($htmlFile, $html);

        // ৩) Chrome দিয়ে ছবি তুলি
        $this->screenshot($htmlFile, $pngFile, $width, $height);

        // ৪) উপরের ১০৮০×১৩৫০ অংশ কেটে JPEG করে রাখি
        $source = imagecreatefrompng($pngFile);
        $crop = imagecrop($source, ['x' => 0, 'y' => 0, 'width' => $width, 'height' => $height]);

        if (!$crop || imagesx($crop) !== $width || imagesy($crop) !== $height) {
            throw new \RuntimeException('কার্ডের মাপ ঠিক হয়নি (স্ক্রিনশট ছোট এসেছে)।');
        }

        $disk->makeDirectory(dirname($outPath));
        imagejpeg($crop, $disk->path($outPath), 90);

        imagedestroy($source);
        imagedestroy($crop);
        @unlink($htmlFile);
        @unlink($pngFile);
    }

    private function screenshot(string $htmlFile, string $pngFile, int $width, int $height): void
    {
        $chrome = (string) config('newsflow.chrome_path');

        if ($chrome === '' || !is_file($chrome)) {
            throw new \RuntimeException('Chrome পাওয়া যায়নি। .env ফাইলে CHROME_PATH ঠিক করুন।');
        }

        $args = [
            $chrome,
            '--headless',
            '--disable-gpu',
            '--hide-scrollbars',
            '--force-device-scale-factor=1',
            // উচ্চতা বেশি রাখি, কারণ Chrome কখনো কখনো কিছুটা কম উচ্চতার ছবি দেয়। পরে কেটে নিই
            '--window-size=' . $width . ',' . ($height + 200),
            // আলাদা প্রোফাইল, যাতে চালু থাকা আপনার Chrome-এর সঙ্গে গোলমাল না হয়
            '--user-data-dir=' . storage_path('app/chrome-profile'),
            '--virtual-time-budget=4000',
            '--screenshot=' . $pngFile,
            'file:///' . str_replace('\\', '/', $htmlFile),
        ];

        if (config('newsflow.chrome_no_sandbox')) {
            $args[] = '--no-sandbox';
        }

        $process = new Process($args);
        $process->setTimeout(60);
        $process->run();

        if (!is_file($pngFile)) {
            throw new \RuntimeException('কার্ডের ছবি তৈরি হয়নি। ' . trim($process->getErrorOutput()));
        }
    }

    // ছবিকে HTML-এর ভেতরেই বসিয়ে দিই, যাতে ফাইলের পথ নিয়ে ঝামেলা না হয়
    private function dataUri(string $file): string
    {
        $mime = mime_content_type($file) ?: 'image/jpeg';

        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file));
    }

    private function fontCss(): string
    {
        $file = public_path('fonts/bangla.ttf');

        if (!is_file($file)) {
            return '';
        }

        return "@font-face{font-family:'CardBangla';src:url(data:font/ttf;base64,"
            . base64_encode(file_get_contents($file))
            . ") format('truetype');font-weight:100 900;}";
    }
}
