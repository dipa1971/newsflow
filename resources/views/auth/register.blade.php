@extends('layout')

@section('content')
<div class="box" style="max-width:420px;margin:30px auto">
    <h2>নতুন অ্যাকাউন্ট</h2>
    <form method="POST" action="/register">
        @csrf
        <label>নাম</label>
        <input name="name" value="{{ old('name') }}" required>
        <label>ইমেইল</label>
        <input type="email" name="email" value="{{ old('email') }}" required>
        <label>পাসওয়ার্ড (কমপক্ষে ৮ অক্ষর)</label>
        <input type="password" name="password" required>
        <label>পাসওয়ার্ড আবার</label>
        <input type="password" name="password_confirmation" required>
        <button class="btn">নিবন্ধন করুন</button>
    </form>
    <p>আগে থেকেই অ্যাকাউন্ট আছে? <a href="/login">লগইন করুন</a></p>
</div>
@endsection
