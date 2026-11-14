<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'site_id', 'card_id', 'destination', 'status', 'attempts',
        'remote_id', 'comment_id', 'error', 'sent_at',
    ];

    protected $casts = ['sent_at' => 'datetime'];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    public function destinationLabel(): string
    {
        return ['facebook' => 'ফেসবুক', 'telegram' => 'টেলিগ্রাম'][$this->destination] ?? $this->destination;
    }

    public function statusLabel(): string
    {
        return [
            'pending' => 'অপেক্ষায়',
            'sending' => 'পাঠানো হচ্ছে',
            'retrying' => 'আবার চেষ্টা হবে',
            'sent' => 'পাঠানো হয়েছে',
            'failed' => 'ব্যর্থ',
        ][$this->status] ?? $this->status;
    }

    public function statusClass(): string
    {
        return [
            'pending' => 'info',
            'sending' => 'info',
            'retrying' => 'warn',
            'sent' => 'ok',
            'failed' => 'bad',
        ][$this->status] ?? '';
    }
}
