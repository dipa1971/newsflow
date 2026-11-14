@extends('layout')
@use('App\Support\Bangla')
@section('title', $site->name)

@section('content')
@php
    $steps = $site->checklist();
    $pending = collect($steps)->contains(fn ($s) => !$s['done'] && empty($s['optional']));
@endphp

<nav class="crumbs">
    <a href="/dashboard">আমার সাইট</a><span>/</span>
    <b>{{ $site->name }}</b>
</nav>
    <div>
        <h1>{{ $site->name }}</h1>
        <p class="sub">{{ $site->url }}</p>
    </div>
    <div class="actions">
        <span class="chip {{ $site->isUsable() ? 'info' : 'warn' }}">
            {{ $site->isUsable() ? 'ট্রায়াল: ' . Bangla::digits($site->trialDaysLeft()) . ' দিন বাকি' : 'স্থগিত বা মেয়াদ শেষ' }}
        </span>
        <a class="btn" href="/sites/{{ $site->id }}/edit">সেটিংস</a>
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
                        <form class="inline" method="POST" action="/sites/{{ $site->id }}/test-latest">
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

<div class="panel">
    <div class="panel-head">
        <div>
            <h2>খবর ও কার্ড</h2>
            <small>সর্বশেষ ফিড পরীক্ষা: {{ Bangla::dateTime($site->last_checked_at) }}</small>
        </div>
        <div class="actions">
            <form class="inline" method="POST" action="/sites/{{ $site->id }}/check-now">
                @csrf
                <button class="btn btn-outline btn-sm">এখনই ফিড পরীক্ষা</button>
            </form>
            <form class="inline" method="POST" action="/sites/{{ $site->id }}/test-latest">
                @csrf
                <button class="btn btn-sm">সর্বশেষ খবর পাঠান</button>
            </form>
        </div>
    </div>

    @if($site->last_error)
        <div class="alert alert-bad">সর্বশেষ ত্রুটি: {{ $site->last_error }}</div>
    @endif

    @if($articles->isEmpty())
        <div class="empty">
            <h3>এখনও কোনো খবর পড়া হয়নি</h3>
            <p>ফিডের ঠিকানা দিয়ে “এখনই ফিড পরীক্ষা” চাপুন।</p>
        </div>
    @else
        <div class="cards">
            @foreach($articles as $a)
                <div class="thumb">
                    @if($a->card)
                        <a href="{{ $a->card->url() }}" target="_blank"><img src="{{ $a->card->url() }}" alt=""></a>
                    @else
                        <div class="ph">{{ $a->statusLabel() }}</div>
                    @endif
                    <div class="cap">
                        {{ \Illuminate\Support\Str::limit($a->title, 50) }}
                        <div class="chips">
                            @if($a->card)
                                @foreach($a->card->posts as $p)
                                    <span class="chip {{ $p->statusClass() }}" @if($p->error) title="{{ $p->error }}" @endif>
                                        {{ $p->destinationLabel() }}: {{ $p->statusLabel() }}
                                    </span>
                                    @if($p->status === 'failed')
                                        <form class="inline" method="POST" action="/posts/{{ $p->id }}/retry">
                                            @csrf
                                            <button class="btn btn-outline btn-sm">আবার পাঠান</button>
                                        </form>
                                    @endif
                                @endforeach
                            @else
                                <span class="chip {{ $a->statusClass() }}">{{ $a->statusLabel() }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<div class="panel">
    <details class="fold" style="border-top:0;margin-top:0;padding-top:0" open>
        <summary>অ্যাক্টিভিটি লগ</summary>
        @if($logs->isEmpty())
            <p class="muted">এখনও কিছু ঘটেনি।</p>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th class="w-time">সময়</th><th>বার্তা</th></tr></thead>
                    <tbody>
                    @foreach($logs as $log)
                        <tr>
                            <td>{{ Bangla::dateTime($log->created_at) }}</td>
                            <td><span class="chip {{ $log->levelClass() }}">{{ $log->levelLabel() }}</span> {{ $log->message }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </details>

    <details class="fold">
        <summary>কানেক্টরের তথ্য (পরে লাগবে)</summary>
        <dl class="kv">
            <dt>সাইট আইডি</dt><dd>{{ $site->site_key }}</dd>
            <dt>গোপন চাবি</dt><dd>{{ $site->maskedSecret() }}</dd>
        </dl>
        <form method="POST" action="/sites/{{ $site->id }}/regenerate-secret" style="margin-top:14px"
              onsubmit="return confirm('নতুন চাবি বানালে পুরনোটা কাজ করবে না। নিশ্চিত?')">
            @csrf
            <button class="btn btn-outline btn-sm">নতুন চাবি বানান</button>
        </form>
    </details>
</div>
@endsection
