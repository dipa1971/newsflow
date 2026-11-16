<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\Site;
use App\Services\FacebookService;
use App\Services\PublishException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class FacebookController extends Controller
{
    private function own(Site $site): Site
    {
        abort_unless($site->user_id === auth()->id(), 403);

        return $site;
    }

    private function back(Site $site): string
    {
        return '/sites/' . $site->id . '/connections';
    }

    // ১) ফেসবুকের লগইন পাতায় পাঠাই
    public function connect(Site $site, FacebookService $facebook)
    {
        $this->own($site);

        if (!$facebook->configured()) {
            return redirect($this->back($site))
                ->withErrors(['facebook' => 'ফেসবুক অ্যাপের তথ্য (.env-এ FB_APP_ID ও FB_APP_SECRET) এখনও দেওয়া হয়নি।']);
        }

        // state = গোপন একবারের কোড। ফেরার সময় মিলিয়ে দেখি, যাতে জাল অনুরোধ আটকায়
        $state = Str::random(40);
        session(['fb_state' => $state, 'fb_site' => $site->id]);

        return redirect()->away($facebook->loginUrl(url('/facebook/callback'), $state));
    }

    // ২) ফেসবুক এখানে ফেরত পাঠায়
    public function callback(Request $request, FacebookService $facebook)
    {
        $site = Site::find(session('fb_site'));
        abort_unless($site && $site->user_id === auth()->id(), 403);

        if ($request->has('error')) {
            return redirect($this->back($site))
                ->withErrors(['facebook' => 'আপনি ফেসবুকে অনুমতি দেননি, তাই সংযোগ হয়নি।']);
        }

        abort_unless(
            hash_equals((string) session('fb_state'), (string) $request->query('state')),
            403
        );
        session()->forget('fb_state');

        try {
            $short = $facebook->exchangeCode((string) $request->query('code'), url('/facebook/callback'));
            $long = $facebook->longLived($short);
            $pages = $facebook->pages($long);
        } catch (PublishException $e) {
            return redirect($this->back($site))->withErrors(['facebook' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return redirect($this->back($site))
                ->withErrors(['facebook' => 'ফেসবুকের সঙ্গে যোগাযোগ করা যায়নি। ইন্টারনেট ও SSL সেটিং দেখুন।']);
        }

        if (!$pages) {
            return redirect($this->back($site))->withErrors([
                'facebook' => 'কোনো পেজ পাওয়া যায়নি। আপনি যে অ্যাকাউন্টে ঢুকেছেন সেটি পেজের অ্যাডমিন কি না এবং সব অনুমতি দিয়েছেন কি না দেখুন।',
            ]);
        }

        // পেজের টোকেনগুলো সেশনে এনক্রিপ্ট করে রাখি (শুধু বাছাইয়ের জন্য)
        session(['fb_pages' => Crypt::encryptString(json_encode($pages))]);

        if (count($pages) === 1) {
            return $this->saveConnection($site, $pages[0]);
        }

        return redirect('/sites/' . $site->id . '/facebook/pages');
    }

    // ৩) একাধিক পেজ থাকলে বাছাইয়ের পাতা
    public function pages(Site $site)
    {
        $this->own($site);
        $pages = $this->sessionPages();

        if (!$pages) {
            return redirect($this->back($site));
        }

        // টোকেন পাতায় পাঠাই না, শুধু আইডি ও নাম
        $list = array_map(fn ($p) => ['id' => $p['id'], 'name' => $p['name']], $pages);

        return view('sites.facebook-pages', ['site' => $site, 'pages' => $list]);
    }

    public function choose(Request $request, Site $site)
    {
        $this->own($site);
        $data = $request->validate(['page_id' => 'required|string']);

        $page = collect($this->sessionPages())->firstWhere('id', $data['page_id']);
        abort_unless($page, 404);

        return $this->saveConnection($site, $page);
    }

    public function disconnect(Site $site)
    {
        $this->own($site);
        $site->connections()->where('provider', 'facebook')->delete();

        return redirect($this->back($site))->with('ok', 'ফেসবুকের সংযোগ বিচ্ছিন্ন করা হয়েছে।');
    }

    private function sessionPages(): array
    {
        $encrypted = session('fb_pages');

        if (!$encrypted) {
            return [];
        }

        try {
            return json_decode(Crypt::decryptString($encrypted), true) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function saveConnection(Site $site, array $page)
    {
        Connection::updateOrCreate(
            ['site_id' => $site->id, 'provider' => 'facebook'],
            [
                'external_id' => $page['id'],
                'name' => $page['name'],
                'token' => $page['access_token'],
                'status' => 'active',
            ]
        );

        session()->forget('fb_pages');

        return redirect($this->back($site))->with('ok', '“' . $page['name'] . '” পেজ যুক্ত হয়েছে।');
    }
}
