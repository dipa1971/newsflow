@extends('layout')
@use('App\Support\Bangla')
@section('title', 'আমার সাইট')

@section('content')
<div class="page-head">
    <div>
        <h1>আমার সাইট</h1>
        <p class="sub">আপনার যুক্ত করা সংবাদমাধ্যম</p>
    </div>
    <div class="actions">
        <a class="btn" href="/sites/create">+ নতুন সাইট</a>
    </div>
</div>

@if($sites->isEmpty())
    <div class="panel">
        <div class="empty">
            <h3>এখনও কোনো সাইট নেই</h3>
            <p>প্রথম সাইটটি যোগ করতে মাত্র তিনটি তথ্য লাগবে।</p>
            <a class="btn" href="/sites/create">সাইট যোগ করুন</a>
        </div>
    </div>
@else
    @foreach($sites as $site)
        @php $fb = $site->facebookConnection(); @endphp
        <a class="site-row" href="/sites/{{ $site->id }}">
            <div class="site-main">
                <strong>{{ $site->name }}</strong>
                <small>{{ $site->url }}</small>
            </div>
            <div class="site-chips">
                @if($fb && $fb->status === 'active')
                    <span class="chip ok">ফেসবুক যুক্ত</span>
                @elseif($fb)
                    <span class="chip warn">ফেসবুক আবার যুক্ত করুন</span>
                @else
                    <span class="chip">ফেসবুক যুক্ত নয়</span>
                @endif

                @if($site->telegramReady())
                    <span class="chip ok">টেলিগ্রাম যুক্ত</span>
                @endif

                <span class="chip {{ $site->isUsable() ? 'info' : 'warn' }}">
                    {{ $site->isUsable() ? 'চালু' : 'স্থগিত বা মেয়াদ শেষ' }}
                </span>
            </div>
            <div class="site-meta">
                ট্রায়াল: {{ Bangla::digits($site->trialDaysLeft()) }} দিন বাকি<br>
                পরীক্ষা: {{ Bangla::dateTime($site->last_checked_at) }}
            </div>
        </a>
    @endforeach
@endif
@endsection
