@extends('layout')
@use('App\Support\Bangla')
@section('title', 'সেটিংস')

@section('content')
<div class="page-head">
    <div>
        <h1>সেটিংস</h1>
        <p class="sub">সাইটের মূল তথ্য ও কানেক্টরের চাবি।</p>
    </div>
</div>

<form class="panel" method="POST" action="/sites/{{ $site->id }}/settings">
    @csrf
    @method('PUT')

    <div class="panel-head"><h2>সাইটের তথ্য</h2></div>

    <div class="form-row">
        <div class="field">
            <label for="name">সাইটের নাম</label>
            <input id="name" name="name" value="{{ old('name', $site->name) }}" required>
            <div class="hint">কার্ডে এই নাম ছাপা হয় (লোগো না থাকলে)।</div>
        </div>
        <div class="field">
            <label for="district">জেলা</label>
            <input id="district" name="district" value="{{ old('district', $site->district) }}">
            <div class="hint">আবহাওয়া ও নামাজের সময়ের জন্য লাগবে।</div>
        </div>
    </div>

    <div class="field">
        <label for="url">ওয়েবসাইটের ঠিকানা</label>
        <input id="url" name="url" value="{{ old('url', $site->url) }}" required>
    </div>

    <div class="field">
        <label for="feed_url">খবরের ফিডের ঠিকানা (RSS)</label>
        <input id="feed_url" name="feed_url" value="{{ old('feed_url', $site->feed_url) }}" placeholder="https://example.com/feed">
        <div class="hint">প্রতি ৫ মিনিটে এখান থেকে নতুন খবর খোঁজা হয়।</div>
    </div>

    <div class="form-actions">
        <button class="btn">সংরক্ষণ করুন</button>
    </div>
</form>

<div class="panel">
    <div class="panel-head"><h2>কানেক্টরের তথ্য</h2><small>পরের ধাপে লাগবে</small></div>
    <dl class="kv">
        @if (session('new_secret'))
        <div class="hint" style="margin-top:16px;padding:12px 14px;border:1px solid #e0b34a;border-radius:8px;background:#fff8e6">
            <b>নতুন গোপন চাবি (এই একবারই দেখানো হচ্ছে, এখনই কপি করুন):</b><br>
            <code style="word-break:break-all;user-select:all">{{ session('new_secret') }}</code>
        </div>
    @endif
        <dt>প্ল্যাটফর্ম URL</dt><dd>{{ rtrim(config('app.url'), '/') }}</dd>
        <dt>সাইট আইডি</dt><dd>{{ $site->site_key }}</dd>
        <dt>গোপন চাবি</dt><dd>{{ $site->maskedSecret() }}</dd>
        <dt>ট্রায়াল</dt><dd>{{ Bangla::digits($site->trialDaysLeft()) }} দিন বাকি</dd>
    </dl>
    <form method="POST" action="/sites/{{ $site->id }}/regenerate-secret" style="margin-top:16px"
          onsubmit="return confirm('নতুন চাবি বানালে পুরনোটা কাজ করবে না। নিশ্চিত?')">
        @csrf
        <button class="btn btn-outline btn-sm">নতুন চাবি বানান</button>
    </form>
</div>

<div class="panel danger-zone">
    <div class="panel-head"><h2>সাইট মুছে ফেলা</h2></div>
    <p class="muted">সাইট মুছলে এর সব খবর, কার্ড ও পোস্টের রেকর্ড মুছে যাবে। এটা আর ফেরানো যায় না।</p>
    <form method="POST" action="/sites/{{ $site->id }}"
          onsubmit="return confirm('সত্যিই এই সাইট মুছে ফেলবেন? সব তথ্য চলে যাবে।')">
        @csrf
        @method('DELETE')
        <button class="btn btn-danger">সাইট মুছুন</button>
    </form>
</div>
@endsection
