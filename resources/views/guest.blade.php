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
<div class="guest">
    <div class="guest-box">
        <div class="guest-brand"><span class="logo-mark"><i></i><i></i><i></i><i></i></span>NewsFlow</div>
        @if($errors->any())
            <div class="alert alert-bad">
                @foreach($errors->all() as $e)
                    <div>{{ $e }}</div>
                @endforeach
            </div>
        @endif
        @yield('content')
    </div>
</div>
</body>
</html>
