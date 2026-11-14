<?php

namespace App\Http\Controllers;

use App\Jobs\CheckSiteFeed;
use App\Jobs\ProcessArticle;
use App\Models\Article;
use App\Models\Site;
use App\Services\CardRenderer;
use App\Services\FeedReader;
use App\Services\PublishException;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SiteController extends Controller
{
    private function own(Site $site): Site
    {
        abort_unless($site->user_id === auth()->id(), 403);

        return $site;
    }

    // নতুন সাইট বানানোর সময় শুধু তিনটি ঘর
    private function createRules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'url' => ['required', 'url', 'max:200', Rule::unique('sites', 'url')],
            'feed_url' => 'nullable|url|max:300',
        ];
    }

    // সেটিংস পাতার সব ঘর
    private function updateRules(int $siteId): array
    {
        return [
            'name' => 'required|string|max:100',
            'url' => ['required', 'url', 'max:200', Rule::unique('sites', 'url')->ignore($siteId)],
            'feed_url' => 'nullable|url|max:300',
            'district' => 'nullable|string|max:50',
            'design' => ['required', Rule::in(array_keys(CardRenderer::DESIGNS))],
            'logo' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'caption_mode' => ['required', Rule::in(['title_hashtags', 'custom', 'none'])],
            'caption_template' => 'nullable|string|max:500',
            'hashtags' => 'nullable|string|max:200',
            'telegram_token' => 'nullable|string|max:200',
            'telegram_chat' => ['nullable', 'string', 'max:100', 'regex:/^(@[A-Za-z0-9_]{4,}|-?\d+)$/'],
        ];
    }

    public function index()
    {
        return redirect('/dashboard');
    }

    public function create()
    {
        return view('sites.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->createRules());

        $data['user_id'] = auth()->id();
        $data['site_key'] = Str::random(24);
        $data['secret'] = Str::random(40);
        $data['trial_ends_at'] = now()->addDays(7);

        $site = Site::create($data);

        return redirect('/sites/' . $site->id)
            ->with('ok', 'সাইট যোগ হয়েছে। ৭ দিনের ট্রায়াল শুরু হলো। নিচের ধাপগুলো শেষ করুন।');
    }

    public function show(Site $site)
    {
        $this->own($site)->load('connections');

        $articles = $site->articles()->with('card.posts')->latest()->take(12)->get();
        $logs = $site->logs()->latest()->take(15)->get();

        return view('sites.show', compact('site', 'articles', 'logs'));
    }

    public function edit(Site $site)
    {
        $this->own($site)->load('connections');

        // কার্ডের ডিজাইনের নমুনা ছবি থাকলে দেখাই (php artisan cards:previews দিয়ে বানানো)
        $previews = [];
        foreach (array_keys(CardRenderer::DESIGNS) as $key) {
            if (Storage::disk('public')->exists('design-previews/' . $key . '.jpg')) {
                $previews[$key] = asset('storage/design-previews/' . $key . '.jpg');
            }
        }

        return view('sites.form', compact('site', 'previews'));
    }

    public function update(Request $request, Site $site, TelegramService $telegram)
    {
        $this->own($site);
        $data = $request->validate($this->updateRules($site->id));

        if ($request->hasFile('logo')) {
            if ($site->logo_path) {
                Storage::disk('public')->delete($site->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }
        unset($data['logo']);

        // টিক-বাক্স: টিক না দিলে কিছুই আসে না, তাই আলাদা করে পড়ি
        $data['post_facebook'] = $request->boolean('post_facebook');
        $data['post_telegram'] = $request->boolean('post_telegram');

        // টোকেনের ঘর ফাঁকা থাকলে আগের টোকেন রেখে দিই
        if (blank($data['telegram_token'] ?? null)) {
            unset($data['telegram_token']);
        }

        $site->update($data);

        // টেলিগ্রাম চালু থাকলে সঙ্গে সঙ্গে যাচাই করে জানাই
        $warn = null;
        if ($site->post_telegram) {
            if (!$site->telegramReady()) {
                $warn = 'টেলিগ্রাম চালু করেছেন, কিন্তু বট টোকেন বা চ্যানেলের নাম দেননি।';
            } else {
                try {
                    $telegram->check($site->telegram_token, $site->telegram_chat);
                } catch (PublishException $e) {
                    $warn = $e->getMessage() . ' (বটটি চ্যানেলের অ্যাডমিন কি না দেখুন।)';
                } catch (\Throwable $e) {
                    $warn = 'টেলিগ্রামে পৌঁছানো যায়নি। ইন্টারনেট বা SSL সেটিং দেখুন।';
                }
            }
        }

        $redirect = redirect('/sites/' . $site->id)->with('ok', 'সংরক্ষণ হয়েছে।');

        return $warn ? $redirect->with('warn', $warn) : $redirect;
    }

    public function destroy(Site $site)
    {
        $this->own($site);

        if ($site->logo_path) {
            Storage::disk('public')->delete($site->logo_path);
        }
        $site->delete();

        return redirect('/dashboard')->with('ok', 'সাইট মুছে ফেলা হয়েছে।');
    }

    public function regenerateSecret(Site $site)
    {
        $this->own($site);
        $site->update(['secret' => Str::random(40)]);

        return back()->with('ok', 'নতুন গোপন চাবি তৈরি হয়েছে। পুরনোটা আর কাজ করবে না।');
    }

    public function checkNow(Site $site)
    {
        $this->own($site);
        CheckSiteFeed::dispatch($site->id);

        return back()->with('ok', 'ফিড পরীক্ষার কাজ কিউতে দেওয়া হয়েছে। কয়েক সেকেন্ড পর পাতা রিফ্রেশ করুন।');
    }

    // ফিডের সবচেয়ে নতুন খবর নিয়ে কার্ড বানিয়ে চালু গন্তব্যগুলোতে পাঠায়
    public function testLatest(Site $site, FeedReader $reader)
    {
        $this->own($site);

        if (!$site->feed_url) {
            return back()->withErrors(['feed_url' => 'আগে সাইটে ফিডের ঠিকানা দিন।']);
        }

        try {
            $items = $reader->fetch($site->feed_url);
        } catch (\Throwable $e) {
            return back()->withErrors(['feed_url' => $e->getMessage()]);
        }

        if (!$items) {
            return back()->withErrors(['feed_url' => 'ফিডে কোনো খবর নেই।']);
        }

        $item = $items[0];

        $article = Article::updateOrCreate(
            ['site_id' => $site->id, 'guid_hash' => Article::hashFor($item['guid'])],
            Article::attributesFromItem($item, 'pending') + ['skip_reason' => null]
        );

        ProcessArticle::dispatch($article->id);

        return back()->with('ok', 'সর্বশেষ খবরের কাজ কিউতে দেওয়া হয়েছে। কার্ড বানিয়ে চালু গন্তব্যে পাঠানো হবে।');
    }
}
