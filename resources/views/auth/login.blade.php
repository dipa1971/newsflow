@extends('layout')

@section('content')
<div class="box" style="max-width:420px;margin:30px auto">
    <h2>লগইন</h2>
    <form method="POST" action="/login">
        @csrf
        <label>ইমেইল</label>
        <input type="email" name="email" value="{{ old('email') }}" required autofocus>
        <label>পাসওয়ার্ড</label>
        <input type="password" name="password" required>
        <label style="display:block;margin-bottom:12px">
            <input type="checkbox" name="remember" value="1" style="width:auto"> আমাকে মনে রাখুন
        </label>
        <button class="btn">ঢুকুন</button>
    </form>
    <p>অ্যাকাউন্ট নেই? <a href="/register">নিবন্ধন করুন</a></p>
</div>
@endsection
