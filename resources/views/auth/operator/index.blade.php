@extends('layout')
@use('App\Support\Bangla')
@section('title', 'অপারেটর কনসোল')

@section('content')
<div class="page-head">
    <div>
        <h1>অপারেটর কনসোল</h1>
        <p class="sub">সব গ্রাহক ও সাইট এক জায়গায়</p>
    </div>
</div>

<div class="stats">
    <div class="stat">
        <b>{{ Bangla::digits($users->count()) }}</b><span>মোট গ্রাহক</span>
    </div>
    <div class="stat">
        <b>{{ Bangla::digits($sites->count()) }}</b><span>মোট সাইট</span>
    </div>
    <div class="stat green">
        <b>{{ Bangla::digits($sites->where('status', 'active')->count()) }}</b><span>চালু সাইট</span>
    </div>
    <div class="stat yellow">
        <b>{{ Bangla::digits($sites->filter(fn ($s) => $s->status === 'active' && $s->trialDaysLeft() <= 2)->count()) }}</b>
        <span>ট্রায়াল শেষ হচ্ছে (২ দিনের মধ্যে)</span>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h2>সব সাইট</h2></div>
    @if($sites->isEmpty())
        <div class="empty"><h3>এখনও কোনো সাইট নেই</h3></div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>সাইট</th>
                        <th>মালিক</th>
                        <th>অবস্থা</th>
                        <th>ট্রায়াল</th>
                        <th>সর্বশেষ ফিড পরীক্ষা</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($sites as $s)
                    <tr>
                        <td><strong>{{ $s->name }}</strong><br><small>{{ $s->url }}</small></td>
                        <td>{{ $s->user->name }}</td>
                        <td>
                            <span class="chip {{ $s->status === 'active' ? 'ok' : 'warn' }}">
                                {{ $s->status === 'active' ? 'চালু' : 'স্থগিত' }}
                            </span>
                        </td>
                        <td>
                            <span class="chip {{ $s->trialDaysLeft() <= 2 ? 'warn' : '' }}">
                                {{ Bangla::digits($s->trialDaysLeft()) }} দিন বাকি
                            </span>
                        </td>
                        <td>{{ Bangla::dateTime($s->last_checked_at) }}</td>
                        <td class="right">
                            <form class="inline" method="POST" action="/operator/sites/{{ $s->id }}/toggle"
                                  onsubmit="return confirm('{{ $s->status === 'active' ? 'সাইটটি স্থগিত করবেন? কার্ড তৈরি ও পোস্ট বন্ধ হবে।' : 'সাইটটি আবার চালু করবেন?' }}')">
                                @csrf
                                @if($s->status === 'active')
                                    <button class="btn btn-danger btn-sm">স্থগিত করুন</button>
                                @else
                                    <button class="btn btn-outline btn-sm">চালু করুন</button>
                                @endif
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="panel">
    <div class="panel-head"><h2>গ্রাহক</h2></div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>নাম</th><th>ইমেইল</th><th>ভূমিকা</th><th>সাইট</th><th>যোগ দিয়েছেন</th></tr>
            </thead>
            <tbody>
            @foreach($users as $u)
                <tr>
                    <td>{{ $u->name }}</td>
                    <td>{{ $u->email }}</td>
                    <td>
                        <span class="chip {{ $u->role === 'operator' ? 'info' : '' }}">
                            {{ $u->role === 'operator' ? 'অপারেটর' : 'গ্রাহক' }}
                        </span>
                    </td>
                    <td>{{ Bangla::digits($u->sites_count) }}</td>
                    <td>{{ Bangla::dateTime($u->created_at) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
