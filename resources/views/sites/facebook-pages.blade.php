@extends('layout')
@section('title', 'পেজ বাছুন')

@section('content')
<nav class="crumbs">
    <a href="/sites/{{ $site->id }}/connections">সংযোগ</a><span>/</span>
    <b>পেজ বাছুন</b>
</nav>
<div class="page-head">
    <div>
        <h1>কোন পেজে কার্ড যাবে?</h1>
        <p class="sub">আপনি যে পেজগুলো পরিচালনা করেন, তার তালিকা। একটি বেছে নিন।</p>
    </div>
</div>

<form class="panel" method="POST" action="/sites/{{ $site->id }}/facebook/choose" style="max-width:640px">
    @csrf
    <div class="radios">
        @foreach($pages as $p)
            <label class="radio">
                <input type="radio" name="page_id" value="{{ $p['id'] }}" required>
                <span><strong>{{ $p['name'] }}</strong><small>পেজ আইডি: {{ $p['id'] }}</small></span>
            </label>
        @endforeach
    </div>
    <div class="form-actions">
        <button class="btn">এই পেজ ব্যবহার করুন</button>
        <a class="btn btn-outline" href="/sites/{{ $site->id }}/connections">বাতিল</a>
    </div>
</form>
@endsection
