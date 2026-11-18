<?php

namespace App\Services;

use App\Models\CustomDesign;
use App\Models\Site;
use App\Support\Bangla;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class CardRenderer
{
    // ডিজাইনের কী => নাম
    public const DESIGNS = [
        'design1' => 'ক্লাসিক ব্যান্ড',
        'design2' => 'গাঢ় ওভারলে',
        'design3' => 'পাশে রেখা',
        'design4' => 'ফ্রেমে ছবি',
        'design5' => 'উপরে শিরোনাম',
        'design6' => 'গোল লোগো (হালকা)',
        'design7' => 'গোল লোগো (গাঢ়)',
    ];

    // এই দুটো আলাদা template ফাইল নয়, "নিজের ডিজাইন"-এর template-ই নির্দিষ্ট setting দিয়ে চলে
    public const PRESETS = [
        'design6' => [
            'layout' => 'circle', 'panel_color' => '#f1f3f6', 'text_color' => '#1f3d7a',
            'lead_color' => '#c4161c', 'accent_color' => '#1f3d7a', 'meta_color' => '#44505f',
            'font_size' => 62, 'align' => 'center', 'two_tone' => true, 'accent_bar' => false,
            'show_date' => true, 'show_brand' => true,
        ],
        'design7' => [
            'layout' => 'circle', 'panel_color' => '#8f1d14', 'text_color' => '#ffffff',
            'lead_color' => '#f2b01e', 'accent_color' => '#f2b01e', 'meta_color' => '#f3d0cc',
            'font_size' => 62, 'align' => 'center', 'two_tone' => true, 'accent_bar' => false,
            'show_date' => true, 'show_brand' => true,
        ],
    ];

    // Studio-র ফন্ট তালিকা: ডিফল্ট + যেগুলোর ফাইল public/fonts/ ফোল্ডারে আছে
    public static function availableFonts(): array
    {
        $list = ['default' => 'ডিফল্ট (সিস্টেম ফন্ট)'];

        foreach (config('newsflow.fonts', []) as $key => $font) {
            if (is_file(public_path('fonts/' . $font['file']))) {
                $list[$key] = $font['label'];
            }
        }

        return $list;
    }

    /**
     * Card-এর HTML বানায়।
     * $cfg দিলে সেই setting দিয়ে "নিজের design"-এর চেহারা হয় (preview-তে কাজে লাগে)।
     * $meta = ['category' => ...] খবরের তথ্য।
     */
    public function html(Site $site, string $headline, string $photoPath, ?array $cfg = null, array $meta = []): string
    {
        $disk = Storage::disk('public');
        $design = (string) $site->design;
        $view = 'cards.design1';

        if ($cfg !== null) {
            $view = 'cards.custom';
            $cfg = array_merge(CustomDesign::defaults(), $cfg);
        } elseif (str_starts_with($design, 'custom_')) {
            $custom = CustomDesign::where('site_id', $site->id)->find((int) substr($design, 7));
            if ($custom) {
                $view = 'cards.custom';
                $cfg = $custom->cfg();
            }
        } elseif (isset(self::PRESETS[$design])) {
            $view = 'cards.custom';
            $cfg = array_merge(CustomDesign::defaults(), self::PRESETS[$design]);
        } elseif (array_key_exists($design, self::DESIGNS)) {
            $view = 'cards.' . $design;
        }

        $logo = null;
        if ($site->logo_path && $disk->exists($site->logo_path)) {
            $logo = $this->dataUri($disk->path($site->logo_path));
        }

        // গোল বাক্সের ছবি: আইকন থাকলে আইকন, না থাকলে লোগো
        $icon = $logo;
        if ($site->icon_path && $disk->exists($site->icon_path)) {
            $icon = $this->dataUri($disk->path($site->icon_path));
        }

        $info = [
            'category' => ($site->card_show_category ?? true)
                ? (trim((string) ($meta['category'] ?? '')) ?: null) : null,
            'url' => ($site->card_show_url ?? true) ? ($site->domain() ?: null) : null,
            'tag' => trim((string) $site->card_photo_tag) ?: null,
            'initial' => mb_substr(trim((string) $site->name), 0, 1) ?: 'ন',
        ];

        $cfg = $cfg ?? CustomDesign::defaults();

        return view($view, [
            'headline' => $headline,
            'siteName' => $site->name,
            'dateText' => Bangla::date(now()),
            'photo' => $this->dataUri($disk->path($photoPath)),
            'logo' => $logo,
            'icon' => $icon,
            'info' => $info,
            'font' => $this->fontCss($cfg['font'] ?? null),
            'cfg' => $cfg,
        ])->render();
    }

    /**
     * কার্ড বানিয়ে storage/app/public/$outPath-এ রাখে।
     * $photoPath ও $outPath দুটোই "public" ডিস্কের ভেতরের পথ।
     */
    public function render(Site $site, string $headline, string $photoPath, string $outPath, ?array $cfg = null, array $meta = []): void
    {
        $width = config('newsflow.card_width');
        $height = config('newsflow.card_height');
        $disk = Storage::disk('public');

        $html = $this->html($site, $headline, $photoPath, $cfg, $meta);

        $tmpDir = storage_path('app/tmp');
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0775, true);
        }
        $name = Str::random(12);
        $htmlFile = $tmpDir . DIRECTORY_SEPARATOR . $name . '.html';
        $pngFile = $tmpDir . DIRECTORY_SEPARATOR . $name . '.png';
        file_put_contents($htmlFile, $html);

        $this->screenshot($htmlFile, $pngFile, $width, $height);

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

    // Preview ও থাম্বনেইলের জন্য নমুনা ছবি (না থাকলে বানায়)
    public function samplePhoto(): string
    {
        $disk = Storage::disk('public');
        $path = 'design-previews/sample-photo.jpg';

        if (!$disk->exists($path)) {
            $disk->makeDirectory('design-previews');
            $img = imagecreatetruecolor(1080, 1000);
            for ($y = 0; $y < 1000; $y++) {
                $c = imagecolorallocate($img, 40 + (int) ($y / 12), 90 + (int) ($y / 14), 140 + (int) ($y / 20));
                imageline($img, 0, $y, 1080, $y, $c);
            }
            imagejpeg($img, $disk->path($path), 85);
            imagedestroy($img);
        }

        return $path;
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
            '--window-size=' . $width . ',' . ($height + 200),
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

    private function dataUri(string $file): string
    {
        $mime = mime_content_type($file) ?: 'image/jpeg';

        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file));
    }

    // নির্বাচিত ফন্ট। ফাইল না পেলে bangla.ttf, তা-ও না থাকলে সিস্টেম ফন্ট
    private function fontCss(?string $key): string
    {
        $file = null;

        if ($key && $key !== 'default') {
            $name = config('newsflow.fonts.' . $key . '.file');
            if ($name) {
                $file = public_path('fonts/' . $name);
            }
        }

        if (!$file || !is_file($file)) {
            $file = public_path('fonts/bangla.ttf');
        }

        if (!is_file($file)) {
            return '';
        }

        $format = str_ends_with($file, '.otf') ? 'opentype' : 'truetype';

        return "@font-face{font-family:'CardBangla';src:url(data:font/ttf;base64,"
            . base64_encode(file_get_contents($file))
            . ") format('" . $format . "');font-weight:100 900;}";
    }
}
