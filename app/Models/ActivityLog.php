<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['site_id', 'article_id', 'level', 'message'];

    // যেকোনো জায়গা থেকে এক লাইনে লগ লেখার সহজ পথ
    public static function record(?int $siteId, string $level, string $message, ?int $articleId = null): void
    {
        static::create([
            'site_id' => $siteId,
            'article_id' => $articleId,
            'level' => $level,
            'message' => $message,
        ]);
    }

    public function levelLabel(): string
    {
        return ['info' => 'তথ্য', 'warn' => 'সতর্ক', 'error' => 'ত্রুটি'][$this->level] ?? $this->level;
    }

    public function levelClass(): string
    {
        return ['info' => 'ok', 'warn' => 'warn', 'error' => 'bad'][$this->level] ?? '';
    }
}
