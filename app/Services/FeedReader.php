<?php

namespace App\Services;

use App\Support\SafeUrl;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;

class FeedReader
{
    /** ফিড থেকে খবরের তালিকা ফেরত দেয়। সমস্যা হলে কারণসহ ত্রুটি ছোঁড়ে। */
    public function fetch(string $feedUrl): array
    {
        SafeUrl::assertPublic($feedUrl);

        $response = Http::timeout(20)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; NewsFlowBot/1.0)',
                'Accept' => 'application/rss+xml, application/atom+xml, application/xml;q=0.9, text/xml;q=0.9, */*;q=0.5',
            ])
            ->get($feedUrl);

        $body = $response->body();
        $type = (string) $response->header('Content-Type');

        if (!$response->successful()) {
            throw new \RuntimeException(
                $this->describe('ফিড খোলা যায়নি (HTTP ' . $response->status() . ')।', $body, $type)
            );
        }

        $xml = $this->parseXml($body);

        if ($xml === null) {
            throw new \RuntimeException($this->describe('ফিডটি সঠিক XML নয়।', $body, $type));
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

    /**
     * লেখাটাকে XML হিসেবে পড়ার চেষ্টা। আগে আবর্জনা পরিষ্কার করি, না হলে '&' সারিয়ে আবার চেষ্টা।
     * না পারলে null ফেরত দেয়।
     */
    private function parseXml(string $body): ?SimpleXMLElement
    {
        // UTF-8 BOM সরাই
        $body = preg_replace('/^\xEF\xBB\xBF/', '', $body);

        // XML শুরুর আগের আবর্জনা (ফাঁকা লাইন, PHP warning) ছেঁটে ফেলি
        if (!preg_match('/<\?xml\b|<rss\b|<feed\b|<rdf:RDF\b/i', $body, $m, PREG_OFFSET_CAPTURE)) {
            return null;
        }
        $body = substr($body, $m[0][1]);

        // শেষ বন্ধ-ট্যাগের পরের আবর্জনাও ছেঁটে ফেলি
        if (preg_match_all('/<\/(rss|feed|rdf:RDF)>/i', $body, $mm, PREG_OFFSET_CAPTURE) && !empty($mm[0])) {
            $last = end($mm[0]);
            $body = substr($body, 0, $last[1] + strlen($last[0]));
        }

        // XML-এ অচলে এমন নিয়ন্ত্রণ-চিহ্ন বাদ
        $body = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $body);

        // দ্বিতীয় চেষ্টায় ভাঙা '&' (যেমন: Tom & Jerry) সারাই
        $fixed = preg_replace('/&(?!(?:amp|lt|gt|quot|apos|#[0-9]+|#x[0-9a-fA-F]+);)/', '&amp;', $body);

        libxml_use_internal_errors(true);

        foreach ([$body, $fixed] as $candidate) {
            libxml_clear_errors();
            $xml = simplexml_load_string($candidate, SimpleXMLElement::class, LIBXML_NOCDATA | LIBXML_NONET);

            if ($xml !== false) {
                libxml_clear_errors();

                return $xml;
            }
        }

        libxml_clear_errors();

        return null;
    }

    // ত্রুটির বার্তার সঙ্গে "আসলে কী এসেছিল" জুড়ে দিই, যাতে অনুমান করতে না হয়
    private function describe(string $message, string $body, string $type): string
    {
        $lower = strtolower($body);
        $hint = null;

        if (str_contains($lower, 'requires javascript') || str_contains($lower, 'aes.js') || str_contains($lower, '__test')) {
            $hint = 'সাইটের হোস্টিংয়ের বট-সুরক্ষা (JavaScript পরীক্ষা) আমাদের server-কে আটকাচ্ছে। এই সাইটের জন্য ব্রিজ (connector) লাগবে।';
        } elseif (str_contains($lower, 'just a moment') || str_contains($lower, 'cf-chl') || str_contains($lower, 'attention required')) {
            $hint = 'Cloudflare-এর যাচাইয়ের পাতা এসেছে।';
        } elseif (preg_match('/\b(Deprecated|Warning|Notice|Fatal error|Parse error)\b\s*:/', $body)) {
            $hint = 'PHP-র error বা warning লেখা ছাপা হয়ে ফিডের ভেতরে ঢুকছে। ওই সাইটের PHP-তে display_errors বন্ধ করুন।';
        } elseif (str_contains($lower, '<html') || str_contains($lower, '<!doctype html')) {
            $hint = 'ঠিকানা থেকে ওয়েবপাতা (HTML) এসেছে, ফিড নয়। ফিডের ঠিকানা ঠিক আছে কি না দেখুন।';
        } elseif (trim($body) === '') {
            $hint = 'ঠিকানা থেকে খালি উত্তর এসেছে।';
        }

        $text = trim(preg_replace('/\s+/u', ' ', strip_tags($body)));
        $text = mb_convert_encoding(mb_substr($text, 0, 140), 'UTF-8', 'UTF-8');

        return $message
            . ' [Content-Type: ' . ($type !== '' ? $type : 'নেই') . ']'
            . ($hint ? ' সম্ভাব্য কারণ: ' . $hint : '')
            . ($text !== '' ? ' শুরুর লেখা: “' . $text . '”' : '');
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
