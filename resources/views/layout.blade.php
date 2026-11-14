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
<header class="topbar">
    <div class="topbar-in">
        <a href="/dashboard" class="logo">
            <span class="logo-mark"><i></i><i></i><i></i><i></i></span>NewsFlow
        </a>
        <nav class="menu">
            <a href="/dashboard" class="{{ request()->is('dashboard', 'sites*') ? 'active' : '' }}">আমার সাইট</a>
            @if(auth()->user()->isOperator())
                <a href="/operator" class="{{ request()->is('operator*') ? 'active' : '' }}">অপারেটর</a>
            @endif
        </nav>
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
