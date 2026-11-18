@extends('layout')
@section('title', 'ডিজাইন ও ক্যাপশন')

@section('content')
<div class="page-head">
    <div>
        <h1>ডিজাইন ও ক্যাপশন</h1>
        <p class="sub">কার্ড দেখতে কেমন হবে আর পোস্টের সঙ্গে কী লেখা যাবে।</p>
    </div>
</div>

<form method="POST" action="/sites/{{ $site->id }}/design" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <section class="panel">
        <div class="sec-head">
            <span class="num">১</span>
            <div>
                <h2>কার্ডের চেহারা</h2>
                <p class="sub">তৈরি ডিজাইন বাছুন, অথবা নিজের মতো করে বানান।</p>
            </div>
        </div>

        <div class="choices">
            @foreach(\App\Services\CardRenderer::DESIGNS as $key => $label)
                <label class="choice">
                    <input type="radio" name="design" value="{{ $key }}" @checked(old('design', $site->design ?: 'design1') === $key)>
                    <span class="choice-body">
                        @if(!empty($previews[$key]))
                            <img src="{{ $previews[$key] }}" alt="">
                        @else
                            <span class="ph">নমুনা নেই</span>
                        @endif
                        <span class="nm">{{ $label }}</span>
                    </span>
                </label>
            @endforeach

            @foreach($site->customDesigns as $c)
                <div class="choice-wrap">
                    <label class="choice">
                        <input type="radio" name="design" value="custom_{{ $c->id }}" @checked(old('design', $site->design) === 'custom_' . $c->id)>
                        <span class="choice-body">
                            @if($c->thumbUrl())
                                <img src="{{ $c->thumbUrl() }}" alt="">
                            @else
                                <span class="ph">নমুনা নেই</span>
                            @endif
                            <span class="nm">{{ $c->name }} <small>(নিজের)</small></span>
                        </span>
                    </label>
                    <div class="choice-tools">
                        <a href="/sites/{{ $site->id }}/designs/{{ $c->id }}/edit">বদলান</a>
                    </div>
                </div>
            @endforeach

            <a class="choice-new" href="/sites/{{ $site->id }}/designs/create">
                <span>+</span>
                নিজের ডিজাইন বানান
            </a>
        </div>
    </section>

    <section class="panel">
        <div class="sec-head">
            <span class="num">২</span>
            <div>
                <h2>লোগো ও গোল আইকন</h2>
                <p class="sub">গোল লোগোর ডিজাইনে আইকন বসে। আইকন না দিলে লোগো, তা-ও না থাকলে নামের প্রথম অক্ষর।</p>
            </div>
        </div>

        <div class="form-row">
            <div class="field">
                <label for="logo">লোগো (লম্বা বা চওড়া)</label>
                <input id="logo" type="file" name="logo" accept="image/png,image/jpeg">
                <div class="hint">PNG বা JPG, সর্বোচ্চ ২ MB। কার্ডের নিচে বা কোণে বসে।</div>
                @if($site->logo_path)
                    <img src="{{ asset('storage/'.$site->logo_path) }}" alt="" style="max-height:48px;margin-top:10px">
                @endif
            </div>
            <div class="field">
                <label for="icon">গোল আইকন (চৌকো, ৫১২×৫১২ হলে সবচেয়ে ভালো)</label>
                <input id="icon" type="file" name="icon" accept="image/png,image/jpeg">
                <div class="hint">গোল বাক্সের ভেতরে এটা বড় করে বসে।</div>
                @if($site->icon_path)
                    <img src="{{ asset('storage/'.$site->icon_path) }}" alt="" style="max-height:48px;margin-top:10px">
                @endif
            </div>
        </div>
    </section>

    <section class="panel">
        <div class="sec-head">
            <span class="num">৩</span>
            <div>
                <h2>কার্ডে কী কী তথ্য থাকবে</h2>
                <p class="sub">গোল লোগোর ডিজাইন ও নিজের ডিজাইনে কাজ করে।</p>
            </div>
        </div>

        <label class="check" style="margin-top:0">
            <input type="checkbox" name="card_show_category" value="1" @checked(old('card_show_category', $site->card_show_category ?? true))>
            খবরের ক্যাটাগরি দেখান (খবরে ক্যাটাগরি না থাকলে কিছু দেখাবে না)
        </label>
        <label class="check">
            <input type="checkbox" name="card_show_url" value="1" @checked(old('card_show_url', $site->card_show_url ?? true))>
            ওয়েবসাইটের ঠিকানা দেখান ({{ $site->domain() ?: 'ঠিকানা নেই' }})
        </label>

        <div class="field" style="margin:20px 0 0">
            <label for="card_photo_tag">ছবির কোণের লেখা (ঐচ্ছিক)</label>
            <input id="card_photo_tag" name="card_photo_tag" maxlength="40" value="{{ old('card_photo_tag', $site->card_photo_tag) }}" placeholder="যেমন: সংগৃহীত">
            <div class="hint">ফাঁকা রাখলে কিছু দেখাবে না। এই লেখা প্রতিটি কার্ডে একই থাকে, তাই এমন লেখা দিন যা সব ছবির জন্য সত্য।</div>
        </div>
    </section>

    <section class="panel">
        <div class="sec-head">
            <span class="num">৪</span>
            <div><h2>ক্যাপশন ও হ্যাশট্যাগ</h2></div>
        </div>

        <div class="radios">
            <label class="radio">
                <input type="radio" name="caption_mode" value="title_hashtags" @checked(old('caption_mode', $site->caption_mode ?: 'title_hashtags') === 'title_hashtags')>
                <span><strong>শিরোনাম ও হ্যাশট্যাগ</strong><small>সবচেয়ে সহজ। প্রস্তাবিত।</small></span>
            </label>
            <label class="radio">
                <input type="radio" name="caption_mode" value="custom" @checked(old('caption_mode', $site->caption_mode) === 'custom')>
                <span><strong>নিজের লেখা</strong><small>নিচের ছাঁচ অনুযায়ী ক্যাপশন তৈরি হবে।</small></span>
            </label>
            <label class="radio">
                <input type="radio" name="caption_mode" value="none" @checked(old('caption_mode', $site->caption_mode) === 'none')>
                <span><strong>ক্যাপশন ছাড়া</strong><small>শুধু কার্ড যাবে।</small></span>
            </label>
        </div>

        <div class="field">
            <label for="caption_template">নিজের লেখার ছাঁচ</label>
            <textarea id="caption_template" name="caption_template" placeholder="{title}&#10;&#10;{hashtag}">{{ old('caption_template', $site->caption_template) }}</textarea>
            <div class="hint">শুধু “নিজের লেখা” বাছলে কাজ করবে। ব্যবহার করা যায়: {title} {hashtag} {category} {excerpt} {site}</div>
        </div>

        <div class="field" style="margin-bottom:0">
            <label for="hashtags">হ্যাশট্যাগ</label>
            <input id="hashtags" name="hashtags" value="{{ old('hashtags', $site->hashtags) }}" placeholder="#সংবাদ #ময়মনসিংহ">
            <div class="hint">ফাঁক বা কমা দিয়ে আলাদা করুন।</div>
        </div>
    </section>

    <div class="savebar">
        <button class="btn">সংরক্ষণ করুন</button>
    </div>
</form>
@endsection
