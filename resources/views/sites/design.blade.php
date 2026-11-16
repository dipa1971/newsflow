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
            <div><h2>কার্ডের চেহারা</h2></div>
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
        </div>

        <div class="field" style="margin:20px 0 0">
            <label for="logo">লোগো</label>
            <input id="logo" type="file" name="logo" accept="image/png,image/jpeg">
            <div class="hint">PNG বা JPG, সর্বোচ্চ ২ MB। না দিলে সাইটের নাম লেখা হবে।</div>
            @if($site->logo_path)
                <img src="{{ asset('storage/'.$site->logo_path) }}" alt="" style="max-height:40px;margin-top:10px">
            @endif
        </div>
    </section>

    <section class="panel">
        <div class="sec-head">
            <span class="num">২</span>
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
