<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'NewsFlow')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
</head>
<body>
@php
    $id = $navSite?->id;
    $b = $navSite ? '/sites/' . $id : null;
    $menu = $navSite ? [
        ['সারসংক্ষেপ', $b, ['sites/' . $id]],
        ['খবর ও কার্ড', $b . '/cards', ['sites/' . $id . '/cards']],
        ['সংযোগ', $b . '/connections', ['sites/' . $id . '/connections', 'sites/' . $id . '/facebook/*']],
        ['ডিজাইন', $b . '/design', ['sites/' . $id . '/design']],
        ['লগ', $b . '/log', ['sites/' . $id . '/log']],
        ['সেটিংস', $b . '/settings', ['sites/' . $id . '/settings']],
    ] : [];
@endphp

<header class="topbar">
    <div class="topbar-in">
        <a href="/dashboard" class="logo">
            <span class="logo-mark"><i></i><i></i><i></i><i></i></span>NewsFlow
        </a>

        @if($navSite)
            <details class="switch">
                <summary><span>{{ $navSite->name }}</span></summary>
                <div class="switch-pop">
                    @foreach($navSites as $s)
                        <a href="/sites/{{ $s->id }}" class="{{ $s->id === $navSite->id ? 'cur' : '' }}">{{ $s->name }}</a>
                    @endforeach
                    <hr>
                    <a href="/dashboard?all=1">সব সাইট দেখুন</a>
                    <a href="/sites/create">+ নতুন সাইট যোগ করুন</a>
                </div>
            </details>

            <nav class="menu">
                @foreach($menu as $item)
                    <a href="{{ $item[1] }}" class="{{ request()->is(...$item[2]) ? 'active' : '' }}">
                        {{ $item[0] }}
                        @if($item[0] === 'খবর ও কার্ড' && $navFailed > 0)
                            <span class="nbadge">{{ \App\Support\Bangla::digits($navFailed) }}</span>
                        @endif
                    </a>
                @endforeach
            </nav>
        @else
            <nav class="menu">
                <a href="/sites/create" class="active">প্রথম সাইট যোগ করুন</a>
            </nav>
        @endif

        <div class="right">
            @if(auth()->user()->isOperator())
                <a class="oplink" href="/operator">অপারেটর</a>
            @endif
            <details class="who">
                <summary>
                    <span class="avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                    <span>{{ auth()->user()->name }}</span>
                </summary>
                <div class="who-pop">
                    <small>{{ auth()->user()->email }}</small>
                    <form method="POST" action="/logout">
                        @csrf
                        <button class="btn btn-outline btn-sm btn-block">লগআউট</button>
                    </form>
                </div>
            </details>
        </div>
    </div>
</header>

<main class="content">
    @if(session('ok'))
        <div class="alert alert-ok">{{ session('ok') }}</div>
    @endif
    @if(session('warn'))
        <div class="alert alert-warn">{{ session('warn') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-bad">
            @foreach($errors->all() as $e)
                <div>{{ $e }}</div>
            @endforeach
        </div>
    @endif

    @yield('content')
</main>
</body>
</html>
