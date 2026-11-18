@extends('cards.layout')

@php
    $layout = $cfg['layout'];
    $panel = $cfg['panel_color'];
    $o = $cfg['overlay'] / 100;
    $shadeTop = \App\Models\CustomDesign::rgba($panel, $o);
    $shadeMid = \App\Models\CustomDesign::rgba($panel, $o * 0.75);
    $shadeEnd = \App\Models\CustomDesign::rgba($panel, 0);
    $barLeft = ['left' => 64, 'center' => 480, 'right' => 896][$cfg['align']];
    $initialColor = \App\Models\CustomDesign::darker($cfg['panel_color'], $cfg['text_color']);

    $metaParts = [];
    if (!empty($info['category'])) { $metaParts[] = $info['category']; }
    if ($cfg['show_date']) { $metaParts[] = $dateText; }
    $metaLine = implode(' | ', $metaParts);
@endphp

@section('styles')
.photo { background-color: #cfd6e2; }
.hl { color: {{ $cfg['text_color'] }}; text-align: {{ $cfg['align'] }}; }
.lead { color: {{ $cfg['lead_color'] }}; }
.site, .date { color: {{ $cfg['meta_color'] }}; }
.bar { position: absolute; background: {{ $cfg['accent_color'] }}; }
.foot { position: absolute; left: 64px; right: 64px; display: flex; justify-content: space-between; align-items: center; }
.date { margin-left: auto; }
.ptag { position: absolute; background: rgba(0, 0, 0, .5); color: #fff; padding: 6px 18px; border-radius: 6px; font-size: 24px; line-height: 1.5; }
@if($layout === 'bottom')
.photo { left: 0; top: 0; width: 1080px; height: 830px; }
.panel { position: absolute; left: 0; top: 830px; width: 1080px; height: 520px; background: {{ $panel }}; padding: 58px 64px 0; }
.bar { left: 0; top: 830px; width: 1080px; height: 14px; }
.hl { height: 290px; }
.foot { bottom: 34px; }
.ptag { left: 56px; top: 770px; }
@elseif($layout === 'top')
.panel { position: absolute; left: 0; top: 0; width: 1080px; height: 540px; background: {{ $panel }}; padding: 60px 64px 0; }
.photo { left: 0; top: 540px; width: 1080px; height: 810px; }
.bar { left: 0; top: 540px; width: 1080px; height: 14px; }
.hl { height: 350px; }
.foot { bottom: 30px; }
.ptag { left: 56px; bottom: 40px; }
@elseif($layout === 'circle')
.photo { left: 0; top: 0; width: 1080px; height: 860px; }
.panel { position: absolute; left: 0; top: 860px; width: 1080px; height: 490px; background: {{ $panel }}; }
.bar { left: 0; top: 860px; width: 1080px; height: 12px; }
.badge { position: absolute; left: 440px; top: 760px; width: 200px; height: 200px; border-radius: 50%; background: #fff; border: 10px solid {{ $panel }}; box-sizing: border-box; display: flex; align-items: center; justify-content: center; overflow: hidden; }
.badge img { width: 140px; height: 140px; object-fit: contain; }
.badge b { font-size: 100px; line-height: 1; font-weight: 700; color: {{ $initialColor }}; }
.hl { position: absolute; left: 64px; right: 64px; top: 985px; height: 262px; }
.cfoot { position: absolute; left: 0; bottom: 0; width: 1080px; height: 84px; background: rgba(0, 0, 0, .10); display: flex; align-items: center; justify-content: space-between; padding: 0 56px; font-size: 26px; color: {{ $cfg['meta_color'] }}; }
.ptag { left: 56px; top: 790px; }
@else
.photo { left: 0; top: 0; width: 1080px; height: 1350px; }
.shade { position: absolute; left: 0; bottom: 0; width: 1080px; height: 860px; background: linear-gradient(to top, {{ $shadeTop }} 0%, {{ $shadeMid }} 50%, {{ $shadeEnd }} 100%); }
.bar { left: {{ $barLeft }}px; bottom: 456px; width: 120px; height: 10px; }
.hl { position: absolute; left: 64px; right: 64px; bottom: 110px; height: 330px; text-shadow: 0 2px 10px rgba(0, 0, 0, .35); }
.foot { bottom: 40px; }
.ptag { left: 56px; top: 48px; }
@endif
@endsection

@section('card')
<div class="photo" style="background-image:url('{{ $photo }}')"></div>
@if($layout === 'overlay')<div class="shade"></div>@endif
@if(!empty($info['tag']))<div class="ptag">{{ $info['tag'] }}</div>@endif

@if($layout === 'circle')
    <div class="panel"></div>
    @if($cfg['accent_bar'])<div class="bar"></div>@endif
    <div class="badge">
        @if($icon)<img src="{{ $icon }}" alt="">@else<b>{{ $info['initial'] }}</b>@endif
    </div>
    <div class="hl" id="hl" data-max="{{ $cfg['font_size'] }}">@include('cards.partials.hl')</div>
    <div class="cfoot">
        <span>{{ $metaLine }}</span>
        <span>{{ $info['url'] }}</span>
    </div>
@elseif($layout === 'overlay')
    @if($cfg['accent_bar'])<div class="bar"></div>@endif
    <div class="hl" id="hl" data-max="{{ $cfg['font_size'] }}">@include('cards.partials.hl')</div>
    @include('cards.partials.foot')
@else
    <div class="panel">
        <div class="hl" id="hl" data-max="{{ $cfg['font_size'] }}">@include('cards.partials.hl')</div>
        @include('cards.partials.foot')
    </div>
    @if($cfg['accent_bar'])<div class="bar"></div>@endif
@endif
@endsection
