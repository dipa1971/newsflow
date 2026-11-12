@extends('layout')

@section('content')
<h1>{{ $site->name }}</h1>

<div class="box">
    <p><b>ওয়েব ঠিকানা:</b> {{ $site->url }}</p>
    <p><b>ফিড:</b> {{ $site->feed_url ?: 'দেওয়া হয়নি' }}</p>
    <p><b>জেলা:</b> {{ $site->district ?: '—' }}</p>
    <p><b>হ্যাশট্যাগ:</b> {{ $site->hashtags ?: '—' }}</p>
    <p><b>অবস্থা:</b> {{ $site->status === 'active' ? 'চালু' : 'স্থগিত' }}</p>
    <p><b>ট্রায়াল:</b> {{ $site->trialDaysLeft() }} দিন বাকি</p>
    @if($site->logo_path)
        <p><img src="{{ asset('storage/'.$site->logo_path) }}" style="max-height:60px" alt="লোগো"></p>
    @endif
</div>

<div class="box">
    <p><b>সাইট আইডি:</b> {{ $site->site_key }}</p>
    <p><b>গোপন চাবি:</b> {{ $site->maskedSecret() }}</p>
    <form method="POST" action="/sites/{{ $site->id }}/regenerate-secret"
          onsubmit="return confirm('নতুন চাবি বানালে পুরনোটা কাজ করবে না। নিশ্চিত?')">
        @csrf
        <button class="btn">নতুন চাবি বানান</button>
    </form>
</div>

<a class="btn" href="/sites/{{ $site->id }}/edit">বদলান</a>
<form method="POST" action="/sites/{{ $site->id }}" style="display:inline"
      onsubmit="return confirm('সত্যিই মুছবেন?')">
    @csrf
    @method('DELETE')
    <button class="btn danger">মুছুন</button>
</form>
@endsection
