@extends('layout')
@use('App\Support\Bangla')
@section('title', 'লগ')

@section('content')
<div class="page-head">
    <div>
        <h1>লগ</h1>
        <p class="sub">সিস্টেম যা যা করেছে, সময় ধরে সাজানো</p>
    </div>
</div>

<div class="panel">
    @if($logs->isEmpty())
        <div class="empty"><h3>এখনও কিছু ঘটেনি</h3></div>
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

        <div class="pager">
            @if($logs->previousPageUrl())
                <a class="btn btn-outline btn-sm" href="{{ $logs->previousPageUrl() }}">← নতুনগুলো</a>
            @else
                <span></span>
            @endif
            @if($logs->hasMorePages())
                <a class="btn btn-outline btn-sm" href="{{ $logs->nextPageUrl() }}">পুরনোগুলো →</a>
            @endif
        </div>
    @endif
</div>
@endsection
