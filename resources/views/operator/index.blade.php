@extends('layout')

@section('content')
<h1>অপারেটর কনসোল</h1>

<div class="box">
    <h3>গ্রাহক ({{ $users->count() }})</h3>
    <table>
        <tr><th>নাম</th><th>ইমেইল</th><th>ভূমিকা</th><th>সাইট</th></tr>
        @foreach($users as $u)
            <tr>
                <td>{{ $u->name }}</td>
                <td>{{ $u->email }}</td>
                <td>{{ $u->role }}</td>
                <td>{{ $u->sites_count }}</td>
            </tr>
        @endforeach
    </table>
</div>

<div class="box">
    <h3>সব সাইট ({{ $sites->count() }})</h3>
    <table>
        <tr><th>সাইট</th><th>মালিক</th><th>অবস্থা</th><th>ট্রায়াল</th><th></th></tr>
        @foreach($sites as $s)
            <tr>
                <td>{{ $s->name }}<br><small>{{ $s->url }}</small></td>
                <td>{{ $s->user->name }}</td>
                <td>{{ $s->status === 'active' ? 'চালু' : 'স্থগিত' }}</td>
                <td>{{ $s->trialDaysLeft() }} দিন বাকি</td>
                <td>
                    <form method="POST" action="/operator/sites/{{ $s->id }}/toggle">
                        @csrf
                        <button class="btn">{{ $s->status === 'active' ? 'স্থগিত করুন' : 'চালু করুন' }}</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </table>
</div>
@endsection
