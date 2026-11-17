<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    protected $fillable = [
        'user_id', 'name', 'url', 'feed_url', 'district', 'logo_path',
        'hashtags', 'design', 'site_key', 'secret', 'status', 'trial_ends_at',
        'last_checked_at', 'baseline_done_at', 'last_error',
        'caption_mode', 'caption_template', 'post_facebook', 'post_telegram',
        'telegram_token', 'telegram_chat',
    ];

    protected $casts = [
        'secret' => 'encrypted',
        'telegram_token' => 'encrypted',
        'trial_ends_at' => 'datetime',
        'last_checked_at' => 'datetime',
        'baseline_done_at' => 'datetime',
        'post_facebook' => 'boolean',
        'post_telegram' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    public function logs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function connections()
    {
        return $this->hasMany(Connection::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function facebookConnection(): ?Connection
    {
        return $this->connections->firstWhere('provider', 'facebook');
    }

    public function telegramReady(): bool
    {
        return filled($this->telegram_token) && filled($this->telegram_chat);
    }

    public function maskedSecret(): string
    {
        return substr($this->secret, 0, 4) . str_repeat('•', 12);
    }

    public function trialDaysLeft(): int
    {
        if (!$this->trial_ends_at) {
            return 0;
        }
        return max(0, (int) ceil(now()->diffInDays($this->trial_ends_at, false)));
    }

    public function isUsable(): bool
    {
        return $this->status === 'active'
            && $this->trial_ends_at !== null
            && $this->trial_ends_at->isFuture();
    }

    // শুরুর ধাপের চেকলিস্ট (সাইটের পাতায় দেখানো হয়)
        // শুরুর ধাপের চেকলিস্ট (সারসংক্ষেপ পাতায় দেখানো হয়)
    public function checklist(): array
    {
        $fb = $this->facebookConnection();
        $base = '/sites/' . $this->id;

        return [
            [
                'title' => 'ফিডের ঠিকানা দিন',
                'done' => filled($this->feed_url),
                'href' => $base . '/settings',
                'action' => 'ঠিকানা দিন',
            ],
            [
                'title' => 'ফেসবুক পেজ যুক্ত করুন',
                'done' => $fb && $fb->status === 'active',
                'href' => $base . '/connections',
                'action' => 'যুক্ত করুন',
            ],
            [
                'title' => 'টেলিগ্রাম চ্যানেল যুক্ত করুন (ঐচ্ছিক)',
                'done' => $this->telegramReady(),
                'href' => $base . '/connections',
                'action' => 'যুক্ত করুন',
                'optional' => true,
            ],
            [
                'title' => 'প্রথম কার্ড পাঠিয়ে দেখুন',
                'done' => $this->posts()->where('status', 'sent')->exists(),
                'test' => true,
            ],
        ];
    }

        public function customDesigns()
    {
        return $this->hasMany(CustomDesign::class);
    }

    // Design-এর নাম দেখানোর জন্য (built-in বা নিজের)
    public function designLabel(): string
    {
        if (str_starts_with((string) $this->design, 'custom_')) {
            $c = $this->customDesigns->firstWhere('id', (int) substr($this->design, 7));

            return $c ? $c->name . ' (নিজের)' : 'ডিজাইন মুছে গেছে';
        }

        return \App\Services\CardRenderer::DESIGNS[$this->design] ?? (string) $this->design;
    }
}
