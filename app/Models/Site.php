<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    protected $fillable = [
        'user_id', 'name', 'url', 'feed_url', 'district', 'logo_path',
        'hashtags', 'site_key', 'secret', 'status', 'trial_ends_at',
    ];

    protected $casts = [
        'secret' => 'encrypted',
        'trial_ends_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
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
}
