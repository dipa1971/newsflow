<?php

namespace App\Console\Commands;

use App\Models\Site;
use App\Services\CardRenderer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MakeDesignPreviews extends Command
{
    protected $signature = 'cards:previews';

    protected $description = 'সেটিংস পাতার জন্য সব ডিজাইনের নমুনা ছবি বানায়';

    public function handle(CardRenderer $renderer): int
    {
        $disk = Storage::disk('public');
        $disk->makeDirectory('design-previews');

        $photo = 'design-previews/sample-photo.jpg';
        $img = imagecreatetruecolor(1080, 1000);
        for ($y = 0; $y < 1000; $y++) {
            $c = imagecolorallocate($img, 40 + (int) ($y / 12), 90 + (int) ($y / 14), 140 + (int) ($y / 20));
            imageline($img, 0, $y, 1080, $y, $c);
        }
        imagejpeg($img, $disk->path($photo), 85);
        imagedestroy($img);

        foreach (CardRenderer::DESIGNS as $key => $label) {
            $site = new Site(['name' => 'আপনার সাইট', 'design' => $key]);
            $renderer->render($site, 'এখানে খবরের শিরোনাম থাকবে', $photo, 'design-previews/' . $key . '.jpg');
            $this->info('তৈরি: ' . $label);
        }

        return self::SUCCESS;
    }
}
