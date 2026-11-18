<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessArticle;
use App\Models\ActivityLog;
use App\Models\Article;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConnectorController extends Controller
{
    public function ping(Request $request): JsonResponse
    {
        $site = $request->attributes->get('site');

        return response()->json([
            'ok' => true,
            'site' => $site->name,
            'usable' => $site->isUsable(),
            'trial_days_left' => $site->trialDaysLeft(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $site = $request->attributes->get('site');

        if (!$site->isUsable()) {
            return response()->json(['ok' => false, 'message' => 'সাইট চালু নেই বা ট্রায়ালের মেয়াদ শেষ।'], 402);
        }

        $v = Validator::make($request->json()->all(), [
            'url' => ['required', 'string', 'max:2000', 'url', 'starts_with:http://,https://'],
            'guid' => 'nullable|string|max:2000',
            'title' => 'required|string|max:500',
            'excerpt' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:100',
            'image_url' => ['nullable', 'string', 'max:2000', 'url', 'starts_with:http://,https://'],
            'published_at' => 'nullable|date',
        ]);

        if ($v->fails()) {
            return response()->json(['ok' => false, 'message' => 'তথ্য ঠিক নেই: ' . $v->errors()->first()], 422);
        }

        $d = $v->validated();

        if (config('newsflow.connector_same_host')) {
            $own = $site->domain();
            $host = preg_replace('/^www\./i', '', strtolower((string) parse_url($d['url'], PHP_URL_HOST)));

            if ($own === '' || ($host !== $own && !str_ends_with($host, '.' . $own))) {
                return response()->json(['ok' => false, 'message' => 'খবরের লিংক সাইটের ডোমেইনের নয়।'], 422);
            }
        }

        $guid = filled($d['guid'] ?? null) ? $d['guid'] : $d['url'];
        $hash = Article::hashFor($guid);

        if (Article::where('site_id', $site->id)->where('guid_hash', $hash)->exists()) {
            return response()->json(['ok' => true, 'message' => 'আগেই নেওয়া হয়েছে'], 409);
        }

        try {
            $article = Article::create([
                'site_id' => $site->id,
                'guid_hash' => $hash,
                'title' => mb_substr($d['title'], 0, 500),
                'link' => $d['url'],
                'excerpt' => isset($d['excerpt']) ? mb_substr($d['excerpt'], 0, 300) : null,
                'category' => $d['category'] ?? null,
                'image_url' => $d['image_url'] ?? null,
                'published_at' => $d['published_at'] ?? null,
                'status' => 'pending',
            ]);
        } catch (UniqueConstraintViolationException $e) {
            return response()->json(['ok' => true, 'message' => 'আগেই নেওয়া হয়েছে'], 409);
        }

        $site->update(['last_checked_at' => now(), 'last_error' => null]);
        ActivityLog::record($site->id, 'info', 'কানেক্টর থেকে নতুন খবর এসেছে: ' . $article->title, $article->id);

        ProcessArticle::dispatch($article->id);

        return response()->json(['ok' => true, 'id' => $article->id, 'message' => 'কার্ড তৈরির কাজে দেওয়া হয়েছে'], 201);
    }
}
