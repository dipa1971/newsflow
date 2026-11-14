@extends('cards.layout')

@section('styles')
.photo { left: 0; top: 0; width: 1080px; height: 1350px; }
.shade { position: absolute; left: 0; bottom: 0; width: 1080px; height: 800px; background: linear-gradient(to top, rgba(0,0,0,.92) 0%, rgba(0,0,0,.72) 50%, rgba(0,0,0,0) 100%); }
.top { position: absolute; left: 56px; right: 56px; top: 48px; display: flex; justify-content: space-between; align-items: center; color: #fff; text-shadow: 0 2px 8px rgba(0,0,0,.6); }
.accent { position: absolute; left: 64px; bottom: 424px; width: 120px; height: 10px; background: #e8b422; }
.hl { position: absolute; left: 64px; right: 64px; bottom: 84px; height: 320px; color: #fff; }
.date { position: absolute; left: 64px; bottom: 34px; color: #d6d6d6; }
@endsection

@section('card')
<div class="photo" style="background-image:url('{{ $photo }}')"></div>
<div class="shade"></div>
<div class="top">
    @if($logo)<img class="logo" src="{{ $logo }}" alt="">@else<span class="site">{{ $siteName }}</span>@endif
</div>
<div class="accent"></div>
<div class="hl" id="hl" data-max="64">{{ $headline }}</div>
<div class="date">{{ $dateText }}</div>
@endsection
