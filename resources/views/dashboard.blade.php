@extends('layout')

@section('content')
<h1>আমার সাইট</h1>
<p><a class="btn" href="/sites/create">+ নতুন সাইট যোগ করুন</a></p>

@forelse($sites as $site)
    <div class="box">
        <h3><a href="/sites/{{ $site->id }}">{{ $site->name }}</a></h3>
        <p>{{ $site->url }}</p>
        <p>
            অবস্থা: {{ $site->status === 'active' ? 'চালু' : 'স্থগিত' }}
            · ট্রায়াল: {{ $site->trialDaysLeft() }} দিন বাকি
        </p>
    </div>
@empty
    <p>এখনও কোনো সাইট যোগ করেননি।</p>
@endforelse
@endsection
