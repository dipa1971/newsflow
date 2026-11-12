@extends('layout')

@section('content')
<h1>{{ $site->exists ? 'সাইট বদলান' : 'নতুন সাইট' }}</h1>

<form class="box" method="POST"
      action="{{ $site->exists ? '/sites/'.$site->id : '/sites' }}"
      enctype="multipart/form-data">
    @csrf
    @if($site->exists)
        @method('PUT')
    @endif

    <label>সাইটের নাম</label>
    <input name="name" value="{{ old('name', $site->name) }}" required>

    <label>ওয়েব ঠিকানা (https:// সহ)</label>
    <input name="url" value="{{ old('url', $site->url) }}" required>

    <label>RSS ফিডের ঠিকানা (থাকলে)</label>
    <input name="feed_url" value="{{ old('feed_url', $site->feed_url) }}">

    <label>জেলা</label>
    <input name="district" value="{{ old('district', $site->district) }}">

    <label>হ্যাশট্যাগ</label>
    <input name="hashtags" value="{{ old('hashtags', $site->hashtags) }}">

    <label>লোগো (PNG বা JPG, সর্বোচ্চ ২ MB)</label>
    <input type="file" name="logo" accept="image/*">

    <button class="btn">সেভ করুন</button>
</form>
@endsection
