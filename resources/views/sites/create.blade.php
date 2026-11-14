@extends('layout')
@section('title', 'নতুন সাইট')

@section('content')

<nav class="crumbs">
    <a href="/dashboard">আমার সাইট</a><span>/</span>
    <b>নতুন সাইট</b>
</nav>
<div class="page-head">
    <div>
        <h1>নতুন সাইট যোগ করুন</h1>
        <p class="sub">শুরুতে মাত্র তিনটি তথ্য। বাকি সব পরে সেটিংসে ঠিক করবেন।</p>
    </div>
</div>

<form class="panel" method="POST" action="/sites" style="max-width:640px">
    @csrf

    <div class="field">
        <label for="name">সাইটের নাম</label>
        <input id="name" name="name" value="{{ old('name') }}" required autofocus>
        <div class="hint">কার্ডে এই নাম ছাপা হবে।</div>
    </div>

    <div class="field">
        <label for="url">ওয়েবসাইটের ঠিকানা</label>
        <input id="url" name="url" value="{{ old('url') }}" placeholder="https://example.com" required>
    </div>

    <div class="field">
        <label for="feed_url">খবরের ফিডের ঠিকানা (RSS)</label>
        <input id="feed_url" name="feed_url" value="{{ old('feed_url') }}" placeholder="https://example.com/feed">
        <div class="hint">সাধারণত সাইটের ঠিকানার শেষে /feed বসালেই হয়। পরে দিলেও চলবে।</div>
    </div>

    <div class="form-actions">
        <button class="btn">সাইট যোগ করুন</button>
        <a class="btn btn-outline" href="/dashboard">বাতিল</a>
    </div>
</form>
@endsection
