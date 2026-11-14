@extends('guest')
@section('title', 'লগইন')

@section('content')
<div class="panel">
    <h1>লগইন</h1>
    <form method="POST" action="/login">
        @csrf
        <div class="field">
            <label for="email">ইমেইল</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
        </div>
        <div class="field">
            <label for="password">পাসওয়ার্ড</label>
            <input id="password" type="password" name="password" required>
        </div>
        <div class="field">
            <label class="check"><input type="checkbox" name="remember" value="1"> আমাকে মনে রাখুন</label>
        </div>
        <button class="btn btn-block">ঢুকুন</button>
    </form>
</div>
<p class="guest-foot">অ্যাকাউন্ট নেই? <a href="/register">নিবন্ধন করুন</a></p>
@endsection
