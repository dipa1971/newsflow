@extends('layout')
@section('title', 'সংযোগ')

@section('content')
@php $fb = $site->facebookConnection(); @endphp

<div class="page-head">
    <div>
        <h1>সংযোগ</h1>
        <p class="sub">কার্ড কোথায় কোথায় পোস্ট হবে, এখান থেকে ঠিক করুন।</p>
    </div>
</div>

<form method="POST" action="/sites/{{ $site->id }}/connections">
    @csrf
    @method('PUT')

    <section class="panel" id="facebook">
        <div class="panel-head">
            <h2>ফেসবুক পেজ</h2>
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
    </section>

    <section class="panel" id="telegram">
        <div class="panel-head">
            <h2>টেলিগ্রাম চ্যানেল <small>(ঐচ্ছিক)</small></h2>
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
    </section>

    <div class="savebar">
        <button class="btn">সংরক্ষণ করুন</button>
    </div>
</form>

{{-- ফর্মের ভেতরে ফর্ম বসানো যায় না, তাই বাইরে আলাদা --}}
<form id="fb-disconnect" method="POST" action="/sites/{{ $site->id }}/facebook/disconnect">@csrf</form>
@endsection
