@php
    $metaParts = [];
    if (!empty($info['category'])) { $metaParts[] = $info['category']; }
    if ($cfg['show_date']) { $metaParts[] = $dateText; }
    $metaLine = implode(' | ', $metaParts);
@endphp
@if($cfg['show_brand'] || $metaLine !== '')
<div class="foot">
    @if($cfg['show_brand'])
        @if($logo)<img class="logo" src="{{ $logo }}" alt="">@else<span class="site">{{ $siteName }}</span>@endif
    @endif
    @if($metaLine !== '')<span class="date">{{ $metaLine }}</span>@endif
</div>
@endif
