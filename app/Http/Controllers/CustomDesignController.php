<?php

namespace App\Http\Controllers;

use App\Models\CustomDesign;
use App\Models\Site;
use App\Services\CardRenderer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CustomDesignController extends Controller
{
    private const LIMIT = 10;

    private function own(Site $site): Site
    {
        abort_unless($site->user_id === auth()->id(), 403);

        return $site;
    }

    private function mine(Site $site, CustomDesign $design): CustomDesign
    {
        abort_unless($design->site_id === $site->id, 404);

        return $design;
    }

    public function create(Site $site)
    {
        $this->own($site);

        return view('sites.designs.form', [
            'site' => $site,
            'design' => new CustomDesign(['name' => '', 'settings' => CustomDesign::defaults()]),
            'fonts' => CardRenderer::availableFonts(),
        ]);
    }

    public function store(Request $request, Site $site, CardRenderer $renderer)
    {
        $this->own($site);

        if ($site->customDesigns()->count() >= self::LIMIT) {
            return back()->withErrors(['name' => 'একটা সাইটে সর্বোচ্চ ' . self::LIMIT . 'টি নিজের ডিজাইন রাখা যায়। আগে একটা মুছুন।']);
        }

        $data = $request->validate(['name' => 'required|string|max:60']);

        $design = $site->customDesigns()->create([
            'name' => $data['name'],
            'settings' => CustomDesign::normalize($request->all()),
        ]);

        $this->makeThumb($renderer, $site, $design);

        if ($request->boolean('activate')) {
            $site->update(['design' => 'custom_' . $design->id]);
        }

        return redirect('/sites/' . $site->id . '/design')->with('ok', '“' . $design->name . '” ডিজাইন সংরক্ষণ হয়েছে।');
    }

    public function edit(Site $site, CustomDesign $design)
    {
        $this->own($site);
        $this->mine($site, $design);

        return view('sites.designs.form', [
            'site' => $site,
            'design' => $design,
            'fonts' => CardRenderer::availableFonts(),
        ]);
    }

    public function update(Request $request, Site $site, CustomDesign $design, CardRenderer $renderer)
    {
        $this->own($site);
        $this->mine($site, $design);

        $data = $request->validate(['name' => 'required|string|max:60']);

        $design->update([
            'name' => $data['name'],
            'settings' => CustomDesign::normalize($request->all()),
        ]);

        $this->makeThumb($renderer, $site, $design);

        if ($request->boolean('activate')) {
            $site->update(['design' => 'custom_' . $design->id]);
        }

        return redirect('/sites/' . $site->id . '/design')->with('ok', 'ডিজাইন হালনাগাদ হয়েছে।');
    }

    public function destroy(Site $site, CustomDesign $design)
    {
        $this->own($site);
        $this->mine($site, $design);

        if ($site->design === 'custom_' . $design->id) {
            $site->update(['design' => 'design1']);
        }

        Storage::disk('public')->delete('design-previews/custom-' . $design->id . '.jpg');
        $design->delete();

        return redirect('/sites/' . $site->id . '/design')->with('ok', 'ডিজাইন মুছে ফেলা হয়েছে।');
    }

    // Studio-র পাশের live preview: card-এর HTML ফেরত দেয় (ছবি নয়, তাই দ্রুত)
    public function preview(Request $request, Site $site, CardRenderer $renderer)
    {
        $this->own($site);

        $cfg = CustomDesign::normalize($request->query());

        $article = $site->articles()->whereNotNull('image_path')->latest()->first();
        $meta = ['category' => 'বাংলাদেশ'];

        if ($article && Storage::disk('public')->exists($article->image_path)) {
            $headline = $article->title;
            $photo = $article->image_path;
            $meta = ['category' => $article->category];
        } else {
            $headline = 'এখানে খবরের শিরোনাম থাকবে, লম্বা হলে নিজে থেকে ছোট হবে';
            $photo = $renderer->samplePhoto();
        }

        return response($renderer->html($site, $headline, $photo, $cfg, $meta));
    }

    // Design page-এ দেখানোর ছোট ছবি
    private function makeThumb(CardRenderer $renderer, Site $site, CustomDesign $design): void
    {
        try {
            $renderer->render(
                $site,
                'এখানে খবরের শিরোনাম থাকবে, বাকি অংশ আলাদা রঙে',
                $renderer->samplePhoto(),
                'design-previews/custom-' . $design->id . '.jpg',
                $design->cfg(),
                ['category' => 'বাংলাদেশ']
            );
        } catch (\Throwable $e) {
            report($e);   // ছবি না হলেও design সংরক্ষিত থাকে, ত্রুটি storage/logs/laravel.log-এ যায়
        }
    }
}
