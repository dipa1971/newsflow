<?php

namespace App\Http\Controllers;

use App\Jobs\PublishCard;
use App\Models\Post;

class PostController extends Controller
{
    // ব্যর্থ পোস্ট আবার পাঠানোর বোতাম
    public function retry(Post $post)
    {
        abort_unless($post->site->user_id === auth()->id(), 403);

        if ($post->status === 'sent') {
            return back()->with('ok', 'এটা আগেই পাঠানো হয়ে গেছে।');
        }

        $post->update(['status' => 'pending', 'attempts' => 0, 'error' => null]);
        PublishCard::dispatch($post->id);

        return back()->with('ok', 'আবার পাঠানোর কাজ কিউতে দেওয়া হয়েছে।');
    }
}
