<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = [
        'site_id', 'guid_hash', 'title', 'link', 'excerpt', 'category',
        'image_url', 'image_path', 'published_at', 'status', 'skip_reason',
    ];

    protected $casts = ['published_at' => 'datetime'];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function card()
    {
        return $this->hasOne(Card::class);
    }

    // GUID বা লিংক থেকে ৬৪ অক্ষরের ছাপ
    public static function hashFor(string $guid): string
    {
        return hash('sha256', $guid);
    }

    // ফিড থেকে পাওয়া একটা খবরকে ডাটাবেসের কলামে সাজানো
    public static function attributesFromItem(array $item, string $status): array
    {
        return [
            'title' => mb_substr($item['title'], 0, 500),
            'link' => $item['link'],
            'excerpt' => $item['excerpt'],
            'category' => $item['category'],
            'image_url' => $item['image'],
            'published_at' => $item['published_at'],
            'status' => $status,
        ];
    }

    public function statusLabel(): string
    {
        return [
            'baseline' => 'পুরনো',
            'pending' => 'অপেক্ষায়',
            'carded' => 'কার্ড হয়েছে',
            'skipped' => 'বাদ',
            'failed' => 'ব্যর্থ',
        ][$this->status] ?? $this->status;
    }

    public function statusClass(): string
    {
        return [
            'baseline' => '',
            'pending' => 'warn',
            'carded' => 'ok',
            'skipped' => 'warn',
            'failed' => 'bad',
        ][$this->status] ?? '';
    }
}
