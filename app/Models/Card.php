<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $fillable = ['article_id', 'site_id', 'design', 'path'];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
        public function posts()
    {
        return $this->hasMany(Post::class);
    }

    // ?v=... দেওয়ায় নতুন কার্ড বানালে ব্রাউজার পুরনো ছবি দেখায় না
    public function url(): string
    {
        return asset('storage/' . $this->path) . '?v=' . $this->updated_at->timestamp;
    }
}
