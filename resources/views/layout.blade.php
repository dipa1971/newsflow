<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'NewsFlow')</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Noto Sans Bengali', Arial, sans-serif; margin: 0; background: #f5f6f8; color: #222; }
        header { background: #1f3864; color: #fff; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center; }
        header a, header span { color: #fff; text-decoration: none; margin-left: 14px; }
        .brand { font-weight: 700; font-size: 20px; margin-left: 0; }
        main { max-width: 900px; margin: 20px auto; padding: 0 16px; }
        .box { background: #fff; padding: 18px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,.1); margin-bottom: 16px; }
        input, select { width: 100%; padding: 8px; margin: 4px 0 12px; box-sizing: border-box; font-family: inherit; }
        .btn, button { background: #1f3864; color: #fff; border: 0; padding: 9px 16px; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-block; font-family: inherit; font-size: 14px; }
        .danger { background: #b3261e; }
        .link { background: none; padding: 0; margin-left: 14px; text-decoration: underline; }
        .ok { background: #e6f6ea; padding: 10px; border-radius: 6px; margin-bottom: 12px; }
        .err { background: #fde8e8; padding: 10px; border-radius: 6px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 8px; border-bottom: 1px solid #ddd; text-align: left; }
    </style>
</head>
<body>
<header>
    <a href="/dashboard" class="brand">NewsFlow</a>
    <nav>
        @auth
            @if(auth()->user()->isOperator())
                <a href="/operator">অপারেটর</a>
            @endif
            <span>{{ auth()->user()->name }}</span>
            <form method="POST" action="/logout" style="display:inline">
                @csrf
                <button class="link">লগআউট</button>
            </form>
        @endauth
    </nav>
</header>
<main>
    @if(session('ok'))
        <div class="ok">{{ session('ok') }}</div>
    @endif
    @if($errors->any())
        <div class="err">
            @foreach($errors->all() as $e)
                <div>{{ $e }}</div>
            @endforeach
        </div>
    @endif
    @yield('content')
</main>
</body>
</html>
