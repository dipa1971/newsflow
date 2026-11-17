@if($cfg['show_brand'] || $cfg['show_date'])
<div class="foot">
    @if($cfg['show_brand'])
        @if($logo)<img class="logo" src="{{ $logo }}" alt="">@else<span class="site">{{ $siteName }}</span>@endif
    @endif
    @if($cfg['show_date'])<span class="date">{{ $dateText }}</span>@endif
</div>
@endif
