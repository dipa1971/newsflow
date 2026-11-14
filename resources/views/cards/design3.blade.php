@extends('cards.layout')

@section('styles')
.photo { left: 0; top: 0; width: 1080px; height: 1350px; }
.box { position: absolute; left: 0; bottom: 0; width: 1080px; height: 520px; background: #0f1b2d; border-left: 18px solid #e8b422; padding: 48px 60px 0 52px; }
.hl { height: 330px; color: #fff; }
.foot { position: absolute; left: 52px; right: 60px; bottom: 34px; display: flex; justify-content: space-between; align-items: center; color: #c9d1dc; }
.site { color: #e8b422; }
@endsection

@section('card')
<div class="photo" style="background-image:url('{{ $photo }}')"></div>
<div class="box">
    <div class="hl" id="hl" data-max="62">{{ $headline }}</div>
    <div class="foot">
        @if($logo)<img class="logo" src="{{ $logo }}" alt="">@else<span class="site">{{ $siteName }}</span>@endif
        <span class="date">{{ $dateText }}</span>
    </div>
</div>
@endsection
