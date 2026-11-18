@php
    $parts = !empty($cfg['two_tone']) ? preg_split('/(?<=,)\s*/u', $headline, 2) : [$headline];
@endphp
@if(count($parts) === 2 && trim($parts[1]) !== '')<span class="lead">{{ $parts[0] }}</span> {{ $parts[1] }}@else{{ $headline }}@endif
