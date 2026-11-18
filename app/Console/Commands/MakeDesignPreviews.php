<?php

namespace App\Console\Commands;

use App\Models\Site;
use App\Services\CardRenderer;
use Illuminate\Console\Command;

class MakeDesignPreviews extends Command
{
    protected $signature = 'cards:previews';

    protected $description = 'Design পাতার জন্য সব ডিজাইনের নমুনা ছবি বানায়';

    public function handle(CardRenderer $renderer): int
    {
        $photo = $renderer->samplePhoto();

        foreach (CardRenderer::DESIGNS as $key => $label) {
            $site = new Site([
                'name' => 'আপনার সাইট',
                'url' => 'https://yoursite.com',
                'design' => $key,
            ]);

            $renderer->render(
                $site,
                'এখানে খবরের শিরোনাম থাকবে, বাকি অংশ আলাদা রঙে',
                $photo,
                'design-previews/' . $key . '.jpg',
                null,
                ['category' => 'বাংলাদেশ']
            );

            $this->info('তৈরি: ' . $label);
        }

        return self::SUCCESS;
    }
}
