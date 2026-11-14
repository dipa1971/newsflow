@extends('cards.layout')

@section('styles')
.head { position: absolute; left: 0; top: 0; width: 1080px; height: 520px; background: #0f3d3e; padding: 56px 64px 0; }
.hl { height: 360px; color: #fff; }
.photo { left: 0; top: 520px; width: 1080px; height: 830px; }
.stamp { position: absolute; left: 0; bottom: 0; background: #fff; padding: 18px 34px; display: flex; align-items: center; gap: 24px; color: #333; }
.site { color: #0f3d3e; }
@endsection

@section('card')
<div class="head">
    <div class="hl" id="hl" data-max="62">{{ $headline }}</div>
</div>
<div class="photo" style="background-image:url('{{ $photo }}')"></div>
<div class="stamp">
    @if($logo)<img class="logo" src="{{ $logo }}" alt="">@else<span class="site">{{ $siteName }}</span>@endif
    <span class="date">{{ $dateText }}</span>
</div>
@endsection
