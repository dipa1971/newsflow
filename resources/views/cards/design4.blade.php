@extends('cards.layout')

@section('styles')
.card { background: #f1eee8; }
.frame { position: absolute; left: 48px; top: 48px; width: 984px; height: 760px; border: 12px solid #fff; box-shadow: 0 6px 24px rgba(0,0,0,.16); }
.frame .photo { left: 0; top: 0; width: 100%; height: 100%; }
.hl { position: absolute; left: 72px; right: 72px; top: 850px; height: 300px; color: #1c1c1c; }
.rule { position: absolute; left: 72px; top: 1172px; width: 140px; height: 6px; background: #1f3864; }
.foot { position: absolute; left: 72px; right: 72px; bottom: 40px; display: flex; justify-content: space-between; align-items: center; color: #555; }
.site { color: #1f3864; }
@endsection

@section('card')
<div class="frame">
    <div class="photo" style="background-image:url('{{ $photo }}')"></div>
</div>
<div class="hl" id="hl" data-max="58">{{ $headline }}</div>
<div class="rule"></div>
<div class="foot">
    @if($logo)<img class="logo" src="{{ $logo }}" alt="">@else<span class="site">{{ $siteName }}</span>@endif
    <span class="date">{{ $dateText }}</span>
</div>
@endsection
