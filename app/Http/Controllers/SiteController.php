<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SiteController extends Controller
{
    // এই সাইট কি লগইন করা ব্যক্তির? না হলে ৪০৩ (নিষিদ্ধ)
    private function own(Site $site): Site
    {
        abort_unless($site->user_id === auth()->id(), 403);

        return $site;
    }

    private function rules(?int $ignoreId = null): array
    {
        return [
            'name' => 'required|string|max:100',
            'url' => ['required', 'url', 'max:200', Rule::unique('sites', 'url')->ignore($ignoreId)],
            'feed_url' => 'nullable|url|max:300',
            'district' => 'nullable|string|max:50',
            'hashtags' => 'nullable|string|max:200',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ];
    }

    public function index()
    {
        return redirect('/dashboard');
    }

    public function create()
    {
        return view('sites.form', ['site' => new Site()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }
        unset($data['logo']);

        $data['user_id'] = auth()->id();
        $data['site_key'] = Str::random(24);
        $data['secret'] = Str::random(40);
        $data['trial_ends_at'] = now()->addDays(7);

        $site = Site::create($data);

        return redirect('/sites/' . $site->id)
            ->with('ok', 'সাইট যোগ হয়েছে। ৭ দিনের ট্রায়াল শুরু হলো।');
    }

    public function show(Site $site)
    {
        return view('sites.show', ['site' => $this->own($site)]);
    }

    public function edit(Site $site)
    {
        return view('sites.form', ['site' => $this->own($site)]);
    }

    public function update(Request $request, Site $site)
    {
        $this->own($site);
        $data = $request->validate($this->rules($site->id));

        if ($request->hasFile('logo')) {
            if ($site->logo_path) {
                Storage::disk('public')->delete($site->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }
        unset($data['logo']);

        $site->update($data);

        return redirect('/sites/' . $site->id)->with('ok', 'সাইট হালনাগাদ হয়েছে।');
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
}
