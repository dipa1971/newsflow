@extends('layout')
@use('App\Support\Bangla')
@section('title', $site->name)

@section('content')
@php
    $steps = $site->checklist();
    $pending = collect($steps)->contains(fn ($s) => !$s['done'] && empty($s['optional']));
    $fb = $site->facebookConnection();
    $design = $site->designLabel();
    $base = '/sites/' . $site->id;

    $fbState = !$fb ? ['যুক্ত নয়', '']
        : ($fb->status !== 'active' ? ['আবার যুক্ত করুন', 'warn']
        : ($site->post_facebook ? ['চালু', 'ok'] : ['বন্ধ', 'warn']));

    $tgState = !$site->telegramReady() ? ['যুক্ত নয়', '']
        : ($site->post_telegram ? ['চালু', 'ok'] : ['বন্ধ', 'warn']);
@endphp

<div class="page-head">
    <div>
        <h1>{{ $site->name }}</h1>
        <p class="sub">{{ $site->url }}</p>
    </div>
    <div class="actions">
        <span class="chip {{ $site->isUsable() ? 'info' : 'warn' }}">
            {{ $site->isUsable() ? 'ট্রায়াল: ' . Bangla::digits($site->trialDaysLeft()) . ' দিন বাকি' : 'স্থগিত বা মেয়াদ শেষ' }}
        </span>
        <form class="inline" method="POST" action="{{ $base }}/check-now">
            @csrf
            <button class="btn btn-outline">এখনই ফিড পরীক্ষা</button>
        </form>
        <form class="inline" method="POST" action="{{ $base }}/test-latest">
            @csrf
            <button class="btn">সর্বশেষ খবর পাঠান</button>
        </form>
    </div>
</div>

@if($pending)
<div class="panel">
    <div class="panel-head"><h2>শুরুর ধাপ</h2></div>
    <ol class="steps">
        @foreach($steps as $i => $s)
            <li class="step {{ $s['done'] ? 'done' : '' }}">
                <span class="mark">{{ $s['done'] ? '✓' : Bangla::digits($i + 1) }}</span>
                <span class="t">{{ $s['title'] }}</span>
                @if(!$s['done'])
                    @if(!empty($s['test']))
                        <form class="inline" method="POST" action="{{ $base }}/test-latest">
                            @csrf
                            <button class="btn btn-sm">কার্ড পাঠান</button>
                        </form>
                    @else
                        <a class="btn btn-outline btn-sm" href="{{ $s['href'] }}">{{ $s['action'] }}</a>
                    @endif
                @endif
            </li>
        @endforeach
    </ol>
</div>
@endif

<div class="stats">
    <div class="stat">
        <b>{{ Bangla::digits($stats['cards_today']) }}</b><span>আজ তৈরি কার্ড</span>
    </div>
    <div class="stat">
        <b>{{ Bangla::digits($stats['cards_total']) }}</b><span>মোট কার্ড</span>
    </div>
    <div class="stat green">
        <b>{{ Bangla::digits($stats['fb_sent'] + $stats['tg_sent']) }}</b><span>পাঠানো পোস্ট</span>
    </div>
    <div class="stat {{ $stats['failed'] > 0 ? 'red' : '' }}">
        <b>{{ Bangla::digits($stats['failed']) }}</b><span>ব্যর্থ পোস্ট</span>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <h2>অটোমেশনের অবস্থা</h2>
        <small>সিস্টেম আপনার হয়ে যা যা করছে</small>
    </div>

    <div class="feat">
        <div class="ft">
            <strong>খবর ধরা</strong>
            <small>প্রতি ৫ মিনিটে ফিড দেখা হয়। সর্বশেষ পরীক্ষা: {{ Bangla::dateTime($site->last_checked_at) }}</small>
            @if($site->last_error)<small style="color:var(--red)">ত্রুটি: {{ $site->last_error }}</small>@endif
        </div>
        <span class="chip {{ !$site->feed_url ? 'warn' : ($site->last_error ? 'bad' : 'ok') }}">
            {{ !$site->feed_url ? 'ফিড নেই' : ($site->last_error ? 'ত্রুটি' : 'চালু') }}
        </span>
        <a class="btn btn-outline btn-sm" href="{{ $base }}/settings">ফিড</a>
    </div>

    <div class="feat">
        <div class="ft">
            <strong>কার্ড বানানো</strong>
            <small>ডিজাইন: {{ $design }}। এ পর্যন্ত {{ Bangla::digits($stats['cards_total']) }}টি কার্ড</small>
        </div>
        <span class="chip ok">চালু</span>
        <a class="btn btn-outline btn-sm" href="{{ $base }}/design">ডিজাইন</a>
    </div>

    <div class="feat">
        <div class="ft">
            <strong>ফেসবুক পেজে পোস্ট</strong>
            <small>
                @if($fb && $fb->status === 'active')
                    পেজ: {{ $fb->name }}। {{ Bangla::digits($stats['fb_sent']) }}টি পোস্ট, প্রথম কমেন্টে লিংকসহ
                @else
                    এখনও কোনো পেজ যুক্ত নেই
                @endif
            </small>
        </div>
        <span class="chip {{ $fbState[1] }}">{{ $fbState[0] }}</span>
        <a class="btn btn-outline btn-sm" href="{{ $base }}/connections">{{ $fb ? 'পরিচালনা' : 'যুক্ত করুন' }}</a>
    </div>

    <div class="feat">
        <div class="ft">
            <strong>টেলিগ্রাম চ্যানেলে পোস্ট</strong>
            <small>
                @if($site->telegramReady())
                    চ্যানেল: {{ $site->telegram_chat }}। {{ Bangla::digits($stats['tg_sent']) }}টি পোস্ট
                @else
                    এখনও কোনো চ্যানেল যুক্ত নেই (ঐচ্ছিক)
                @endif
            </small>
        </div>
        <span class="chip {{ $tgState[1] }}">{{ $tgState[0] }}</span>
        <a class="btn btn-outline btn-sm" href="{{ $base }}/connections">{{ $site->telegramReady() ? 'পরিচালনা' : 'যুক্ত করুন' }}</a>
    </div>

    <div class="feat">
        <div class="ft">
            <strong>ডুপ্লিকেট সুরক্ষা</strong>
            <small>একই খবর একই জায়গায় কখনো দুইবার যায় না, আর প্রথমবার পুরনো সব খবর বাদ থাকে</small>
        </div>
        <span class="chip ok">চালু</span>
    </div>

    <div class="feat">
        <div class="ft">
            <strong>ব্যর্থ হলে আবার চেষ্টা</strong>
            <small>
                সাময়িক সমস্যায় ১, ৫ ও ১৫ মিনিট পরে নিজে থেকে আবার পাঠায়
                @if($stats['retrying'] > 0) ({{ Bangla::digits($stats['retrying']) }}টি অপেক্ষায়) @endif
            </small>
        </div>
        <span class="chip ok">চালু</span>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <h2>সর্বশেষ কার্ড</h2>
        <a class="more" href="{{ $base }}/cards">সব দেখুন →</a>
    </div>
    @if($articles->isEmpty())
        <div class="empty">
            <h3>এখনও কোনো কার্ড তৈরি হয়নি</h3>
            <p>ফিড ঠিক করে “সর্বশেষ খবর পাঠান” চাপলেই প্রথম কার্ড তৈরি হবে।</p>
        </div>
    @else
        <div class="cards">
            @foreach($articles as $a)
                @include('sites.partials.thumb', ['a' => $a])
            @endforeach
        </div>
    @endif
</div>

<div class="panel">
    <div class="panel-head">
        <h2>সর্বশেষ ঘটনা</h2>
        <a class="more" href="{{ $base }}/log">সব লগ →</a>
    </div>
    @if($logs->isEmpty())
        <p class="muted">এখনও কিছু ঘটেনি।</p>
    @else
        <div class="table-wrap">
            <table class="table">
                <tbody>
                @foreach($logs as $log)
                    <tr>
                        <td class="w-time">{{ Bangla::dateTime($log->created_at) }}</td>
                        <td><span class="chip {{ $log->levelClass() }}">{{ $log->levelLabel() }}</span> {{ $log->message }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
