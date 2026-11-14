@extends('guest')
@section('title', 'নিবন্ধন')

@section('content')
<div class="panel">
    <h1>নতুন অ্যাকাউন্ট</h1>
    <form method="POST" action="/register">
        @csrf
        <div class="field">
            <label for="name">নাম</label>
            <input id="name" name="name" value="{{ old('name') }}" required autofocus>
        </div>
        <div class="field">
            <label for="email">ইমেইল</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required>
        </div>
        <div class="field">
            <label for="password">পাসওয়ার্ড</label>
            <input id="password" type="password" name="password" required>
            <div class="hint">কমপক্ষে ৮ অক্ষর।</div>
        </div>
        <div class="field">
            <label for="password_confirmation">পাসওয়ার্ড আবার</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required>
        </div>
        <button class="btn btn-block">নিবন্ধন করুন</button>
    </form>
</div>
<p class="guest-foot">আগে থেকেই অ্যাকাউন্ট আছে? <a href="/login">লগইন করুন</a></p>
@endsection
