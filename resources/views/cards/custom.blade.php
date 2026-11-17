@extends('cards.layout')

@php
    $layout = $cfg['layout'];
    $panel = $cfg['panel_color'];
    $o = $cfg['overlay'] / 100;
    $shadeTop = \App\Models\CustomDesign::rgba($panel, $o);
    $shadeMid = \App\Models\CustomDesign::rgba($panel, $o * 0.75);
    $shadeEnd = \App\Models\CustomDesign::rgba($panel, 0);
    $barLeft = ['left' => 64, 'center' => 480, 'right' => 896][$cfg['align']];
@endphp

@section('styles')
.photo { background-color: #cfd6e2; }
.hl { color: {{ $cfg['text_color'] }}; text-align: {{ $cfg['align'] }}; }
.site, .date { color: {{ $cfg['meta_color'] }}; }
.bar { position: absolute; background: {{ $cfg['accent_color'] }}; }
.foot { position: absolute; left: 64px; right: 64px; display: flex; justify-content: space-between; align-items: center; }
.date { margin-left: auto; }
@if($layout === 'bottom')
.photo { left: 0; top: 0; width: 1080px; height: 830px; }
.panel { position: absolute; left: 0; top: 830px; width: 1080px; height: 520px; background: {{ $panel }}; padding: 58px 64px 0; }
.bar { left: 0; top: 830px; width: 1080px; height: 14px; }
.hl { height: 290px; }
.foot { bottom: 34px; }
@elseif($layout === 'top')
.panel { position: absolute; left: 0; top: 0; width: 1080px; height: 540px; background: {{ $panel }}; padding: 60px 64px 0; }
.photo { left: 0; top: 540px; width: 1080px; height: 810px; }
.bar { left: 0; top: 540px; width: 1080px; height: 14px; }
.hl { height: 350px; }
.foot { bottom: 30px; }
@else
.photo { left: 0; top: 0; width: 1080px; height: 1350px; }
.shade { position: absolute; left: 0; bottom: 0; width: 1080px; height: 860px; background: linear-gradient(to top, {{ $shadeTop }} 0%, {{ $shadeMid }} 50%, {{ $shadeEnd }} 100%); }
.bar { left: {{ $barLeft }}px; bottom: 456px; width: 120px; height: 10px; }
.hl { position: absolute; left: 64px; right: 64px; bottom: 110px; height: 330px; text-shadow: 0 2px 10px rgba(0, 0, 0, .35); }
.foot { bottom: 40px; }
@endif
@endsection

@section('card')
<div class="photo" style="background-image:url('{{ $photo }}')"></div>
@if($layout === 'overlay')
    <div class="shade"></div>
    @if($cfg['accent_bar'])<div class="bar"></div>@endif
    <div class="hl" id="hl" data-max="{{ $cfg['font_size'] }}">{{ $headline }}</div>
    @include('cards.partials.foot')
@else
    <div class="panel">
        <div class="hl" id="hl" data-max="{{ $cfg['font_size'] }}">{{ $headline }}</div>
        @include('cards.partials.foot')
    </div>
    @if($cfg['accent_bar'])<div class="bar"></div>@endif
@endif
@endsection
