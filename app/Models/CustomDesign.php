<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CustomDesign extends Model
{
    protected $fillable = ['site_id', 'name', 'settings'];

    protected $casts = ['settings' => 'array'];

    // এক ক্লিকের রঙের সেট (Studio-র preset বোতামে ব্যবহার হয়)
    public const PRESETS = [
        'নীল' => ['panel_color' => '#1f3d7a', 'text_color' => '#ffffff', 'accent_color' => '#f2b01e', 'meta_color' => '#c9d6ee', 'lead_color' => '#f2b01e'],
        'সবুজ' => ['panel_color' => '#0f4d3a', 'text_color' => '#ffffff', 'accent_color' => '#f2b01e', 'meta_color' => '#cfe6dc', 'lead_color' => '#f2b01e'],
        'লাল' => ['panel_color' => '#8f1d14', 'text_color' => '#ffffff', 'accent_color' => '#f2b01e', 'meta_color' => '#f3d0cc', 'lead_color' => '#f2b01e'],
        'হলুদ' => ['panel_color' => '#f2b01e', 'text_color' => '#1a2433', 'accent_color' => '#1f3d7a', 'meta_color' => '#4a3a10', 'lead_color' => '#8f1d14'],
        'সাদা' => ['panel_color' => '#ffffff', 'text_color' => '#111827', 'accent_color' => '#1f4fa3', 'meta_color' => '#5b6778', 'lead_color' => '#b42318'],
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    // নতুন design শুরু হয় এই মান দিয়ে
    public static function defaults(): array
    {
        return [
            'layout' => 'bottom',
            'panel_color' => '#1f3d7a',
            'text_color' => '#ffffff',
            'accent_color' => '#f2b01e',
            'meta_color' => '#c9d6ee',
            'lead_color' => '#b42318',
            'font' => 'default',
            'font_size' => 62,
            'overlay' => 80,
            'align' => 'left',
            'accent_bar' => true,
            'show_date' => true,
            'show_brand' => true,
            'two_tone' => false,
        ];
    }

    // User যা-ই পাঠাক, শুধু নিরাপদ মান রেখে বাকি ফেলে দিই।
    // রং CSS-এর ভেতরে বসে, তাই কঠোরভাবে #RRGGBB ছাড়া কিছু নিই না।
    public static function normalize(array $in): array
    {
        $d = self::defaults();

        $color = function (string $key) use ($in, $d) {
            $v = $in[$key] ?? null;

            return is_string($v) && preg_match('/^#[0-9a-fA-F]{6}$/', $v) ? strtolower($v) : $d[$key];
        };
        $flag = fn (string $key) => filter_var($in[$key] ?? false, FILTER_VALIDATE_BOOLEAN);
        $num = fn (string $key, int $min, int $max) => max($min, min($max, (int) ($in[$key] ?? $d[$key])));
        $fonts = array_merge(['default'], array_keys(config('newsflow.fonts', [])));

        return [
            'layout' => in_array($in['layout'] ?? null, ['bottom', 'overlay', 'top', 'circle'], true) ? $in['layout'] : $d['layout'],
            'panel_color' => $color('panel_color'),
            'text_color' => $color('text_color'),
            'accent_color' => $color('accent_color'),
            'meta_color' => $color('meta_color'),
            'lead_color' => $color('lead_color'),
            'font' => in_array($in['font'] ?? null, $fonts, true) ? $in['font'] : 'default',
            'font_size' => $num('font_size', 44, 80),
            'overlay' => $num('overlay', 40, 95),
            'align' => in_array($in['align'] ?? null, ['left', 'center', 'right'], true) ? $in['align'] : $d['align'],
            'accent_bar' => $flag('accent_bar'),
            'show_date' => $flag('show_date'),
            'show_brand' => $flag('show_brand'),
            'two_tone' => $flag('two_tone'),
        ];
    }

    // #1f3d7a আর ০.৮ থেকে rgba(31,61,122,0.80)
    public static function rgba(string $hex, float $alpha): string
    {
        $hex = ltrim($hex, '#');

        return sprintf(
            'rgba(%d,%d,%d,%.2f)',
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
            $alpha
        );
    }

    // রঙ কতটা উজ্জ্বল (০ = কালো, ১ = সাদা)
    public static function luminance(string $hex): float
    {
        $hex = ltrim($hex, '#');

        return (0.2126 * hexdec(substr($hex, 0, 2))
            + 0.7152 * hexdec(substr($hex, 2, 2))
            + 0.0722 * hexdec(substr($hex, 4, 2))) / 255;
    }

    // দুটো রঙের মধ্যে গাঢ়টা (সাদা গোল বাক্সে অক্ষর পড়ার জন্য)
    public static function darker(string $a, string $b): string
    {
        return self::luminance($a) <= self::luminance($b) ? $a : $b;
    }

    // সংরক্ষিত setting + ফাঁকা জায়গায় default
    public function cfg(): array
    {
        return array_merge(self::defaults(), $this->settings ?? []);
    }

    public function thumbUrl(): ?string
    {
        $path = 'design-previews/custom-' . $this->id . '.jpg';

        if (!Storage::disk('public')->exists($path)) {
            return null;
        }

        return asset('storage/' . $path) . '?v=' . $this->updated_at->timestamp;
    }
}
