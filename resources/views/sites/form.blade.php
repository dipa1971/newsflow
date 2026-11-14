@extends('layout')
@section('title', 'সেটিংস: ' . $site->name)

@section('content')
@php $fb = $site->facebookConnection(); @endphp

<nav class="crumbs">
    <a href="/dashboard">আমার সাইট</a><span>/</span>
    <a href="/sites/{{ $site->id }}">{{ $site->name }}</a><span>/</span>
    <b>সেটিংস</b>
</nav>

<div class="page-head">
    <div>
        <h1>সেটিংস</h1>
        <p class="sub">{{ $site->name }}। সব এক পাতায়, শেষে একবার “সংরক্ষণ করুন” চাপলেই হবে।</p>
    </div>
    <div class="actions">
        <a class="btn btn-outline" href="/sites/{{ $site->id }}">← সাইটের পাতা</a>
    </div>
</div>

<form method="POST" action="/sites/{{ $site->id }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    {{-- ১. সাইটের তথ্য --}}
    <section class="panel" id="source">
        <div class="sec-head">
            <span class="num">১</span>
            <div><h2>সাইটের তথ্য</h2></div>
        </div>
        <div class="form-row">
            <div class="field">
                <label for="name">সাইটের নাম</label>
                <input id="name" name="name" value="{{ old('name', $site->name) }}" required>
            </div>
            <div class="field">
                <label for="district">জেলা</label>
                <input id="district" name="district" value="{{ old('district', $site->district) }}">
            </div>
        </div>
        <div class="field">
            <label for="url">ওয়েবসাইটের ঠিকানা</label>
            <input id="url" name="url" value="{{ old('url', $site->url) }}" required>
        </div>
        <div class="field" style="margin-bottom:0">
            <label for="feed_url">খবরের ফিডের ঠিকানা (RSS)</label>
            <input id="feed_url" name="feed_url" value="{{ old('feed_url', $site->feed_url) }}" placeholder="https://example.com/feed">
            <div class="hint">প্রতি ৫ মিনিটে এখান থেকে নতুন খবর খোঁজা হয়।</div>
        </div>
    </section>

    {{-- ২. কার্ডের চেহারা --}}
    <section class="panel">
        <div class="sec-head">
            <span class="num">২</span>
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

    {{-- ৩. ক্যাপশন --}}
    <section class="panel">
        <div class="sec-head">
            <span class="num">৩</span>
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
            <div class="hint">শুধু “নিজের লেখা” বাছলে কাজ করবে। এগুলো ব্যবহার করা যায়: {title} {hashtag} {category} {excerpt} {site}</div>
        </div>
        <div class="field" style="margin-bottom:0">
            <label for="hashtags">হ্যাশট্যাগ</label>
            <input id="hashtags" name="hashtags" value="{{ old('hashtags', $site->hashtags) }}" placeholder="#সংবাদ #ময়মনসিংহ">
            <div class="hint">ফাঁক বা কমা দিয়ে আলাদা করুন।</div>
        </div>
    </section>

    {{-- ৪. কোথায় পোস্ট হবে --}}
    <section class="panel">
        <div class="sec-head">
            <span class="num">৪</span>
            <div>
                <h2>কোথায় পোস্ট হবে</h2>
                <p class="sub">কোনো পরিবর্তন করে থাকলে আগে নিচের “সংরক্ষণ করুন” চেপে নিন, তারপর ফেসবুক যুক্ত করুন।</p>
            </div>
        </div>

        <div class="block" id="facebook">
            <div class="block-head">
                <h3>ফেসবুক পেজ</h3>
                @if($fb && $fb->status === 'active')
                    <span class="chip ok">যুক্ত: {{ $fb->name }}</span>
                @elseif($fb)
                    <span class="chip warn">সংযোগের মেয়াদ শেষ</span>
                @else
                    <span class="chip">যুক্ত নয়</span>
                @endif
            </div>

            @if($fb && $fb->status === 'active')
                <p class="muted">কার্ড এই পেজে পোস্ট হবে, আর প্রথম কমেন্টে খবরের লিংক যাবে।</p>
                <div class="actions">
                    <a class="btn btn-outline btn-sm" href="/sites/{{ $site->id }}/facebook/connect">অন্য পেজ বাছুন</a>
                    <button type="submit" form="fb-disconnect" class="btn btn-danger btn-sm"
                            onclick="return confirm('ফেসবুকের সংযোগ বিচ্ছিন্ন করবেন?')">সংযোগ বিচ্ছিন্ন করুন</button>
                </div>
            @else
                @if($fb)
                    <div class="alert alert-warn">ফেসবুকের অনুমতি শেষ বা বাতিল হয়েছে। আবার যুক্ত করুন।</div>
                @endif
                <p class="muted">আপনার ফেসবুকের পাসওয়ার্ড লাগবে না। ফেসবুক নিজেই জিজ্ঞেস করবে কোন পেজে অনুমতি দেবেন।</p>
                <a class="btn" href="/sites/{{ $site->id }}/facebook/connect">ফেসবুক দিয়ে যুক্ত হোন</a>
            @endif

            <label class="check">
                <input type="checkbox" name="post_facebook" value="1" @checked(old('post_facebook', $site->post_facebook))>
                ফেসবুকে পোস্ট করুন
            </label>
        </div>

        <div class="block" id="telegram">
            <div class="block-head">
                <h3>টেলিগ্রাম চ্যানেল <small>(ঐচ্ছিক)</small></h3>
                @if($site->telegramReady())
                    <span class="chip ok">তথ্য দেওয়া আছে</span>
                @else
                    <span class="chip">যুক্ত নয়</span>
                @endif
            </div>
            <div class="form-row">
                <div class="field">
                    <label for="telegram_token">বট টোকেন</label>
                    <input id="telegram_token" type="password" name="telegram_token" autocomplete="off"
                           placeholder="{{ filled($site->telegram_token) ? '••••••••• সংরক্ষিত আছে। বদলাতে নতুনটা লিখুন' : '123456789:AA...' }}">
                    <div class="hint">টেলিগ্রামে @BotFather থেকে পাওয়া কোড।</div>
                </div>
                <div class="field">
                    <label for="telegram_chat">চ্যানেলের নাম</label>
                    <input id="telegram_chat" name="telegram_chat" value="{{ old('telegram_chat', $site->telegram_chat) }}" placeholder="@mychannel">
                    <div class="hint">বটকে চ্যানেলের অ্যাডমিন করে রাখতে হবে।</div>
                </div>
            </div>
            <label class="check" style="margin-top:0">
                <input type="checkbox" name="post_telegram" value="1" @checked(old('post_telegram', $site->post_telegram))>
                টেলিগ্রামে পোস্ট করুন
            </label>
        </div>
    </section>

    <div class="savebar">
        <a class="btn btn-outline" href="/sites/{{ $site->id }}">বাতিল</a>
        <button class="btn">সংরক্ষণ করুন</button>
    </div>
</form>

{{-- ফর্মের বাইরের আলাদা ফর্ম (ফর্মের ভেতরে ফর্ম বসানো যায় না) --}}
<form id="fb-disconnect" method="POST" action="/sites/{{ $site->id }}/facebook/disconnect">@csrf</form>
@endsection
