@extends('cards.layout')

@section('styles')
.photo { left: 0; top: 0; width: 1080px; height: 840px; }
.bar { position: absolute; left: 0; top: 840px; width: 1080px; height: 14px; background: #b3261e; }
.panel { position: absolute; left: 0; top: 854px; width: 1080px; height: 496px; background: #fff; padding: 44px 64px 0; }
.hl { height: 290px; color: #111; }
.foot { position: absolute; left: 64px; right: 64px; bottom: 34px; display: flex; justify-content: space-between; align-items: center; color: #555; }
.site { color: #b3261e; }
@endsection

@section('card')
<div class="photo" style="background-image:url('{{ $photo }}')"></div>
<div class="bar"></div>
<div class="panel">
    <div class="hl" id="hl" data-max="62">{{ $headline }}</div>
    <div class="foot">
        @if($logo)<img class="logo" src="{{ $logo }}" alt="">@else<span class="site">{{ $siteName }}</span>@endif
        <span class="date">{{ $dateText }}</span>
    </div>
</div>
@endsection
