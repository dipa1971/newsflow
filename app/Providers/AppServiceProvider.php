<?php

namespace App\Providers;

use App\Models\Site;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // উপরের মেনুর জন্য: এখন কোন সাইটে আছি, আর ব্যর্থ পোস্ট কয়টি
        View::composer('layout', function ($view) {
            $user = auth()->user();

            if (!$user) {
                return;
            }

            $sites = $user->sites()->orderBy('name')->get(['id', 'name']);
            $current = null;

            // ঠিকানায় সাইট থাকলে (যেমন /sites/৩/cards) সেটাই বর্তমান সাইট
            $routeSite = request()->route('site');
            if ($routeSite instanceof Site && $routeSite->user_id === $user->id) {
                $current = $sites->firstWhere('id', $routeSite->id);
                session(['current_site' => $routeSite->id]);
            }

            // নইলে শেষবার যে সাইটে ছিলেন, তা; তা-ও না থাকলে প্রথম সাইট
            if (!$current) {
                $current = $sites->firstWhere('id', session('current_site')) ?? $sites->first();
            }

            $failed = $current ? $current->posts()->where('status', 'failed')->count() : 0;

            $view->with('navSites', $sites)->with('navSite', $current)->with('navFailed', $failed);
        });
    }
}
