<?php

namespace App\Console\Commands;

use App\Models\Site;
use App\Services\CardRenderer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestCards extends Command
{
    protected $signature = 'cards:test {design=design1 : design1 থেকে design5} {--limit=16 : কয়টি শিরোনাম}';

    protected $description = 'বাংলা শিরোনামের পরীক্ষা-কার্ড বানায় (storage/app/public/cards-test)';

    // কঠিন যুক্তাক্ষর ও লম্বা শিরোনাম। পরে এই তালিকা ৫০টিতে বাড়াতে হবে (PRD-র শর্ত)
    private array $headlines = [
        'গোলাপগঞ্জে সমবায় দিবস উপলক্ষে র‍্যালি ও আলোচনা সভা অনুষ্ঠিত',
        'ময়মনসিংহে বাস-সিএনজি সংঘর্ষে নিহত ৭, আহত অর্ধশতাধিক',
        'রাষ্ট্রীয় শোক দিবস পালনে সারাদেশে কর্মসূচি ঘোষণা',
        'শ্রদ্ধা ও ভালোবাসায় স্মরণ করা হলো মুক্তিযুদ্ধের শহীদদের',
        'বিদ্যুৎ-সংকট কাটাতে জরুরি বৈঠকে বসছেন প্রধান উপদেষ্টা',
        'প্রযুক্তি খাতে বিনিয়োগ বাড়াতে নতুন নীতিমালার খসড়া প্রকাশ',
        'উত্তরাঞ্চলে শৈত্যপ্রবাহ অব্যাহত, তাপমাত্রা নামল ৮ ডিগ্রিতে',
        'বন্যায় ক্ষতিগ্রস্ত কৃষকদের জন্য ক্ষতিপূরণ দেওয়ার দাবি',
        'ক্রিকেট বিশ্বকাপে টাইগারদের জয়ের নেপথ্যে অধিনায়কের চমকপ্রদ কৌশল',
        'স্বাস্থ্যসেবা নিশ্চিত করতে উপজেলা হাসপাতালে নতুন ৫০ চিকিৎসক',
        'দুর্ঘটনার আশঙ্কায় ঝুঁকিপূর্ণ সেতু দ্রুত সংস্কারের নির্দেশ',
        'অর্থনৈতিক সংস্কারে বিশ্বব্যাংকের সঙ্গে নতুন চুক্তি স্বাক্ষর',
        'বিশ্ববিদ্যালয়ে ভর্তি পরীক্ষার ফল প্রকাশ, উত্তীর্ণ ৩২ শতাংশ',
        'সীমান্তে গুলিতে বাংলাদেশি যুবক নিহতের অভিযোগে প্রতিবাদ',
        'ঢাকার বায়ুদূষণ আজ বিশ্বে দ্বিতীয় সর্বোচ্চ, স্বাস্থ্যঝুঁকিতে লাখো মানুষ',
        'মেঘনা নদীর ভাঙনে বিলীন হওয়ার পথে শতাধিক পরিবারের বসতভিটা, স্থায়ী বাঁধ নির্মাণের দাবিতে মানববন্ধন ও বিক্ষোভ কর্মসূচি পালন করেছে এলাকাবাসী',
    ];

    public function handle(CardRenderer $renderer): int
    {
        $design = $this->argument('design');

        if (!array_key_exists($design, CardRenderer::DESIGNS)) {
            $this->error('ডিজাইনের নাম ভুল। design1 থেকে design5-এর একটি দিন।');
            return self::FAILURE;
        }

        $disk = Storage::disk('public');
        $disk->makeDirectory('cards-test');

        // নমুনা ছবি GD দিয়ে বানাই (রঙের ধাপ)
        $photo = 'cards-test/sample-photo.jpg';
        $img = imagecreatetruecolor(1080, 1000);
        for ($y = 0; $y < 1000; $y++) {
            $c = imagecolorallocate($img, 40 + (int) ($y / 12), 90 + (int) ($y / 14), 140 + (int) ($y / 20));
            imageline($img, 0, $y, 1080, $y, $c);
        }
        imagejpeg($img, $disk->path($photo), 85);
        imagedestroy($img);

        $site = new Site(['name' => 'নমুনা নিউজ', 'design' => $design]);
        $list = array_slice($this->headlines, 0, (int) $this->option('limit'));

        foreach ($list as $i => $headline) {
            $out = 'cards-test/' . $design . '-' . ($i + 1) . '.jpg';

            try {
                $renderer->render($site, $headline, $photo, $out);
                $this->info('তৈরি: ' . $out);
            } catch (\Throwable $e) {
                $this->error('ব্যর্থ: ' . $e->getMessage());
                return self::FAILURE;
            }
        }

        $this->line('');
        $this->line('ব্রাউজারে দেখুন: ' . config('app.url') . '/storage/cards-test/' . $design . '-1.jpg');

        return self::SUCCESS;
    }
}
