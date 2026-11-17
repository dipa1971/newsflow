@extends('layout')
@section('title', $design->exists ? 'ডিজাইন বদলান' : 'নতুন ডিজাইন')

@section('content')
@php
    $cfg = $design->exists ? $design->cfg() : \App\Models\CustomDesign::defaults();
    $base = '/sites/' . $site->id;
    $activate = $design->exists ? ($site->design === 'custom_' . $design->id) : true;
@endphp

<div class="page-head">
    <div>
        <h1>{{ $design->exists ? 'ডিজাইন বদলান' : 'নিজের ডিজাইন বানান' }}</h1>
        <p class="sub">ডানে কার্ডের নমুনা, বদলালেই সঙ্গে সঙ্গে বদলে যাবে।</p>
    </div>
    <div class="actions">
        <a class="btn btn-outline" href="{{ $base }}/design">← ডিজাইন</a>
    </div>
</div>

<div class="studio">
    <form id="studio" method="POST" action="{{ $design->exists ? $base . '/designs/' . $design->id : $base . '/designs' }}">
        @csrf
        @if($design->exists)
            @method('PUT')
        @endif

        <section class="panel">
            <div class="field">
                <label for="name">ডিজাইনের নাম</label>
                <input id="name" name="name" value="{{ old('name', $design->name) }}" placeholder="যেমন: আমাদের নীল কার্ড" maxlength="60" required>
            </div>

            <div class="field">
                <label>কার্ডের গঠন</label>
                <div class="radios">
                    <label class="radio">
                        <input type="radio" name="layout" value="bottom" @checked($cfg['layout'] === 'bottom')>
                        <span><strong>ছবি উপরে, লেখা নিচে</strong><small>ক্লাসিক ধাঁচ</small></span>
                    </label>
                    <label class="radio">
                        <input type="radio" name="layout" value="overlay" @checked($cfg['layout'] === 'overlay')>
                        <span><strong>পুরো ছবির ওপরে লেখা</strong><small>ছবি বড় দেখায়</small></span>
                    </label>
                    <label class="radio">
                        <input type="radio" name="layout" value="top" @checked($cfg['layout'] === 'top')>
                        <span><strong>লেখা উপরে, ছবি নিচে</strong><small>শিরোনাম আগে চোখে পড়ে</small></span>
                    </label>
                </div>
            </div>

            <div class="field">
                <label>এক ক্লিকে রঙ বাছুন</label>
                <div class="presets">
                    @foreach(\App\Models\CustomDesign::PRESETS as $label => $p)
                        <button type="button" class="preset" data-preset="{{ json_encode($p) }}">
                            <span class="sw" style="background:{{ $p['panel_color'] }}"></span>{{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="colors">
                <div class="field">
                    <label for="panel_color">পটভূমির রঙ</label>
                    <input id="panel_color" type="color" name="panel_color" value="{{ $cfg['panel_color'] }}">
                </div>
                <div class="field">
                    <label for="text_color">শিরোনামের রঙ</label>
                    <input id="text_color" type="color" name="text_color" value="{{ $cfg['text_color'] }}">
                </div>
                <div class="field">
                    <label for="accent_color">দাগ বা রেখার রঙ</label>
                    <input id="accent_color" type="color" name="accent_color" value="{{ $cfg['accent_color'] }}">
                </div>
                <div class="field">
                    <label for="meta_color">নাম ও তারিখের রঙ</label>
                    <input id="meta_color" type="color" name="meta_color" value="{{ $cfg['meta_color'] }}">
                </div>
            </div>

            <div class="form-row">
                <div class="field">
                    <label for="font_size">শিরোনামের সর্বোচ্চ আকার: <b id="fs-val">{{ $cfg['font_size'] }}</b></label>
                    <input id="font_size" type="range" name="font_size" min="44" max="80" value="{{ $cfg['font_size'] }}">
                    <div class="hint">লম্বা শিরোনাম নিজে থেকে ছোট হয়ে বাক্সে বসে।</div>
                </div>
                <div class="field">
                    <label for="overlay">ছায়ার গাঢ়তা: <b id="ov-val">{{ $cfg['overlay'] }}</b>%</label>
                    <input id="overlay" type="range" name="overlay" min="40" max="95" value="{{ $cfg['overlay'] }}">
                    <div class="hint">শুধু “পুরো ছবির ওপরে লেখা” গঠনে কাজ করে।</div>
                </div>
            </div>

            <div class="field">
                <label>লেখা কোন দিকে</label>
                <div class="radios" style="grid-template-columns:repeat(3,1fr)">
                    <label class="radio"><input type="radio" name="align" value="left" @checked($cfg['align'] === 'left')><span><strong>বাঁ</strong></span></label>
                    <label class="radio"><input type="radio" name="align" value="center" @checked($cfg['align'] === 'center')><span><strong>মাঝে</strong></span></label>
                    <label class="radio"><input type="radio" name="align" value="right" @checked($cfg['align'] === 'right')><span><strong>ডান</strong></span></label>
                </div>
            </div>

            <label class="check"><input type="checkbox" name="accent_bar" value="1" @checked($cfg['accent_bar'])> রঙিন দাগ দেখান</label>
            <label class="check"><input type="checkbox" name="show_brand" value="1" @checked($cfg['show_brand'])> লোগো বা সাইটের নাম দেখান</label>
            <label class="check"><input type="checkbox" name="show_date" value="1" @checked($cfg['show_date'])> তারিখ দেখান</label>
            <label class="check" style="margin-top:20px"><input type="checkbox" name="activate" value="1" @checked($activate)> সংরক্ষণের পর এই ডিজাইন এখনই চালু করুন</label>
        </section>

        <div class="savebar">
            @if($design->exists)
                <button type="submit" form="del" class="btn btn-danger"
                        onclick="return confirm('এই ডিজাইন মুছে ফেলবেন?')">মুছুন</button>
            @endif
            <a class="btn btn-outline" href="{{ $base }}/design">বাতিল</a>
            <button class="btn">সংরক্ষণ করুন</button>
        </div>
    </form>

    <aside class="preview-box">
        <div class="frame">
            <iframe id="pv" data-src="{{ $base }}/designs/preview" title="কার্ডের নমুনা"></iframe>
        </div>
        <p class="hint">সাইটের সর্বশেষ আসল খবর থাকলে সেটাই দেখাবে, না থাকলে নমুনা লেখা।</p>
    </aside>
</div>

@if($design->exists)
    <form id="del" method="POST" action="{{ $base }}/designs/{{ $design->id }}">
        @csrf
        @method('DELETE')
    </form>
@endif

<script>
(function () {
    var form = document.getElementById('studio');
    var frame = document.getElementById('pv');
    var base = frame.getAttribute('data-src');
    var timer = null;

    function update() {
        var fd = new FormData(form);
        var p = new URLSearchParams();
        ['layout', 'panel_color', 'text_color', 'accent_color', 'meta_color', 'font_size', 'overlay', 'align']
            .forEach(function (k) { p.set(k, fd.get(k) || ''); });
        ['accent_bar', 'show_brand', 'show_date']
            .forEach(function (k) { if (fd.get(k)) { p.set(k, '1'); } });
        frame.src = base + '?' + p.toString();
    }

    form.addEventListener('input', function () {
        document.getElementById('fs-val').textContent = form.font_size.value;
        document.getElementById('ov-val').textContent = form.overlay.value;
        clearTimeout(timer);
        timer = setTimeout(update, 250);
    });

    document.querySelectorAll('.preset').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var p = JSON.parse(btn.dataset.preset);
            Object.keys(p).forEach(function (k) { form.elements[k].value = p[k]; });
            update();
        });
    });

    update();
})();
</script>
@endsection
