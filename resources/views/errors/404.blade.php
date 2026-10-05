<!doctype html>
<html lang="{{ app()->getLocale() }}" data-theme="light">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 · Sowgatly</title>
    <script>(function(){try{var t=localStorage.getItem('sg-theme')||(matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light');document.documentElement.dataset.theme=t;}catch(e){}})();</script>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/logo/favicon-32x32.png') }}">
    <link rel="stylesheet" href="{{ asset('admin/admin.css') }}">
</head>
<body>
<main style="min-height:100vh;display:grid;place-items:center;padding:24px;text-align:center">
    <div class="stack" style="align-items:center;max-width:420px">
        <img src="{{ asset('img/logo/logo-rounded.svg') }}" alt="Sowgatly" width="72" height="72">
        <h1 style="font-size:64px;letter-spacing:-.04em;line-height:1">404</h1>
        <p class="muted">{{ __('This page does not exist or was moved.') }}</p>
        <a class="btn btn-primary" href="{{ url('/') }}">{{ __('Go to the home page') }}</a>
    </div>
</main>
</body>
</html>
