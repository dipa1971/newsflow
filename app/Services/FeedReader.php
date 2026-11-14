<?php

namespace App\Services;

use App\Support\SafeUrl;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;

class FeedReader
{
    /** ফিড থেকে খবরের তালিকা ফেরত দেয়। সমস্যা হলে ত্রুটি ছোঁড়ে। */
    public function fetch(string $feedUrl): array
    {
        SafeUrl::assertPublic($feedUrl);

        $response = Http::timeout(15)
            ->withHeaders(['User-Agent' => 'NewsFlowBot/1.0'])
            ->get($feedUrl);

        if (!$response->successful()) {
            throw new \RuntimeException('ফিড খোলা যায়নি (HTTP ' . $response->status() . ')।');
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($response->body(), SimpleXMLElement::class, LIBXML_NOCDATA | LIBXML_NONET);

        if ($xml === false) {
            throw new \RuntimeException('ফিডটি সঠিক XML নয়।');
        }

        $items = [];

        if (isset($xml->channel->item)) {            // RSS
            foreach ($xml->channel->item as $item) {
                $items[] = $this->fromRss($item, $feedUrl);
            }
        } elseif (isset($xml->entry)) {              // Atom
            foreach ($xml->entry as $entry) {
                $items[] = $this->fromAtom($entry, $feedUrl);
            }
        }

        // শিরোনাম বা লিংক ছাড়া খবর বাদ
        return array_values(array_filter($items, fn ($i) => $i['title'] !== '' && $i['link'] !== ''));
    }

    private function fromRss(SimpleXMLElement $item, string $base): array
    {
        $link = trim((string) $item->link);
        $guid = trim((string) $item->guid);
        $html = (string) $item->description;
        $content = (string) $item->children('http://purl.org/rss/1.0/modules/content/')->encoded;
        $category = trim((string) ($item->category[0] ?? ''));

        return [
            'guid' => $guid !== '' ? $guid : $link,
            'title' => $this->clean((string) $item->title),
            'link' => $link,
            'excerpt' => mb_substr($this->clean($html !== '' ? $html : $content), 0, 300),
            'category' => $category !== '' ? $category : null,
            'image' => $this->findImage($item, $html . $content, $base),
            'published_at' => $this->parseDate((string) $item->pubDate),
        ];
    }

    private function fromAtom(SimpleXMLElement $entry, string $base): array
    {
        $link = '';
        foreach ($entry->link as $l) {
            $rel = (string) $l['rel'];
            if ($rel === '' || $rel === 'alternate') {
                $link = trim((string) $l['href']);
                break;
            }
        }

        $html = (string) $entry->summary;
        if ($html === '') {
            $html = (string) $entry->content;
        }
        $date = (string) $entry->published;
        if ($date === '') {
            $date = (string) $entry->updated;
        }
        $id = trim((string) $entry->id);
        $category = (string) ($entry->category['term'] ?? '');

        return [
            'guid' => $id !== '' ? $id : $link,
            'title' => $this->clean((string) $entry->title),
            'link' => $link,
            'excerpt' => mb_substr($this->clean($html), 0, 300),
            'category' => $category !== '' ? $category : null,
            'image' => $this->findImage($entry, $html, $base),
            'published_at' => $this->parseDate($date),
        ];
    }

    // ছবি খোঁজার তিনটি জায়গা: enclosure, media:content/thumbnail, লেখার ভেতরের <img>
    private function findImage(SimpleXMLElement $node, string $html, string $base): ?string
    {
        foreach ($node->enclosure as $enc) {
            $type = (string) $enc['type'];
            $url = (string) $enc['url'];
            if ($url !== '' && ($type === '' || str_starts_with($type, 'image/'))) {
                return $this->absolute($url, $base);
            }
        }

        $media = $node->children('http://search.yahoo.com/mrss/');
        foreach (['content', 'thumbnail'] as $tag) {
            if (isset($media->$tag)) {
                $url = (string) $media->$tag->attributes()['url'];
                if ($url !== '') {
                    return $this->absolute($url, $base);
                }
            }
        }

        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', html_entity_decode($html), $m)) {
            return $this->absolute($m[1], $base);
        }

        return null;
    }

    // আপেক্ষিক ঠিকানাকে (/storage/a.jpg) পূর্ণ ঠিকানায় বদলানো
    private function absolute(string $url, string $base): string
    {
        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }

        $p = parse_url($base);
        $scheme = $p['scheme'] ?? 'https';

        if (str_starts_with($url, '//')) {
            return $scheme . ':' . $url;
        }

        $origin = $scheme . '://' . ($p['host'] ?? '') . (isset($p['port']) ? ':' . $p['port'] : '');

        return $origin . '/' . ltrim($url, '/');
    }

    // HTML চিহ্ন ও বাড়তি ফাঁক সরিয়ে পরিষ্কার লেখা
    private function clean(string $text): string
    {
        $text = strip_tags(html_entity_decode($text));

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    private function parseDate(string $text): ?Carbon
    {
        if (trim($text) === '') {
            return null;
        }
        try {
            return Carbon::parse($text);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
