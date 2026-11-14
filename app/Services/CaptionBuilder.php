<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Site;

class CaptionBuilder
{
    // ফেসবুকের ক্যাপশন: খবরের লিংক এখানে নেই, লিংক যায় প্রথম কমেন্টে
    public function forFacebook(Site $site, Article $article): string
    {
        return match ($site->caption_mode) {
            'none' => '',
            'custom' => $this->fill($site->caption_template ?: '{title}', $site, $article),
            default => trim($article->title . "\n\n" . $this->hashtags($site)),
        };
    }

    // টেলিগ্রামের ক্যাপশন: নিচে লিংক থাকে (সর্বোচ্চ ১০২৪ অক্ষর)
    public function forTelegram(Site $site, Article $article): string
    {
        $link = (string) $article->link;
        $room = max(0, 1024 - mb_strlen($link) - 2);
        $text = mb_substr($this->forFacebook($site, $article), 0, $room);

        return trim($text . "\n\n" . $link);
    }

    // "বিস্তারিত" লিংকসহ কমেন্টের লেখা
    public function commentFor(Article $article): string
    {
        return 'বিস্তারিত পড়ুন: ' . $article->link;
    }

    public function hashtags(Site $site): string
    {
        $parts = preg_split('/[\s,]+/u', (string) $site->hashtags, -1, PREG_SPLIT_NO_EMPTY);
        $tags = array_map(fn ($t) => '#' . ltrim($t, '#'), $parts);

        return implode(' ', array_unique($tags));
    }

    private function fill(string $template, Site $site, Article $article): string
    {
        $text = strtr($template, [
            '{title}' => $article->title,
            '{hashtag}' => $this->hashtags($site),
            '{category}' => (string) $article->category,
            '{excerpt}' => (string) $article->excerpt,
            '{site}' => $site->name,
        ]);

        return trim(preg_replace("/\n{3,}/", "\n\n", $text));
    }
}
