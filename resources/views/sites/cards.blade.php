@extends('layout')
@use('App\Support\Bangla')
@section('title', 'খবর ও কার্ড')

@section('content')
@php
    $base = '/sites/' . $site->id;
    $tabs = [
        'all' => ['সব', $counts->except('baseline')->sum()],
        'carded' => ['কার্ড হয়েছে', $counts['carded'] ?? 0],
        'failed' => ['ব্যর্থ', $counts['failed'] ?? 0],
        'skipped' => ['বাদ', $counts['skipped'] ?? 0],
        'baseline' => ['পুরনো খবর', $counts['baseline'] ?? 0],
    ];
@endphp

<div class="page-head">
    <div>
        <h1>খবর ও কার্ড</h1>
        <p class="sub">ফিড থেকে পড়া খবর, তৈরি কার্ড ও পোস্টের অবস্থা</p>
    </div>
    <div class="actions">
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

<div class="filters">
    @foreach($tabs as $key => $tab)
        <a href="{{ $base }}/cards?status={{ $key }}" class="{{ $status === $key ? 'on' : '' }}">
            {{ $tab[0] }} ({{ Bangla::digits($tab[1]) }})
        </a>
    @endforeach
</div>

<div class="panel">
    @if($articles->isEmpty())
        <div class="empty">
            <h3>এখানে কিছু নেই</h3>
            <p>ফিডের ঠিকানা দিয়ে “এখনই ফিড পরীক্ষা” চাপুন, অথবা অন্য ফিল্টার দেখুন।</p>
        </div>
    @else
        <div class="cards">
            @foreach($articles as $a)
                @include('sites.partials.thumb', ['a' => $a])
            @endforeach
        </div>

        <div class="pager">
            @if($articles->previousPageUrl())
                <a class="btn btn-outline btn-sm" href="{{ $articles->previousPageUrl() }}">← আগের</a>
            @else
                <span></span>
            @endif
            @if($articles->hasMorePages())
                <a class="btn btn-outline btn-sm" href="{{ $articles->nextPageUrl() }}">পরের →</a>
            @endif
        </div>
    @endif
</div>
@endsection
