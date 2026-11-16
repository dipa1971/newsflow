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
                @if($a->card->posts->isEmpty())
                    <span class="chip">কোথাও পাঠানো হয়নি</span>
                @endif
            @else
                <span class="chip {{ $a->statusClass() }}">{{ $a->statusLabel() }}</span>
            @endif
        </div>
        @if(!$a->card && $a->skip_reason)
            <small>{{ $a->skip_reason }}</small>
        @endif
    </div>
</div>
