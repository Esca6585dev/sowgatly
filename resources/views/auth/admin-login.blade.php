@php $v = fn ($file) => asset($file) . '?v=' . @filemtime(public_path($file)); $l = app()->getLocale(); @endphp
<!doctype html>
<html lang="{{ $l }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ __('Sign in') }} · {{ __('Admin panel') }} · Sowgatly</title>
    <script>(function(){var R=document.documentElement;try{var t=localStorage.getItem('sg-theme');if(!t)t=matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light';R.dataset.theme=t;}catch(e){}})();</script>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/logo/favicon-32x32.png') }}">
    <link rel="stylesheet" href="{{ $v('admin/admin.css') }}">
</head>
<body>
<div class="auth">
    <aside class="auth-art">
        <div class="brand" style="padding:0">
            <img src="{{ asset('img/logo/logo-rounded.svg') }}" alt="" style="background:#fff;border-radius:12px">
            <div><b>sowgatly</b><small style="color:rgba(255,255,255,.8)">{{ __('Admin panel') }}</small></div>
        </div>
        <div>
            <h2>{{ __('Orders, shops and customers in one place.') }}</h2>
            <p>{{ __('Track every bouquet from the shop to the door.') }}</p>
        </div>
        <img class="mark" src="{{ asset('img/logo/logo-mark-white.svg') }}" alt="">
        <span class="small" style="opacity:.75">© {{ date('Y') }} Sowgatly</span>
    </aside>

    <main class="auth-form">
        <form class="auth-card" method="post" action="{{ route('admin.login.submit', $l) }}">
            @csrf
            <div>
                <h1>{{ __('Sign in') }}</h1>
                <p class="muted">{{ __('Use your administrator account.') }}</p>
            </div>
            @if($errors->any())
                <div class="alert bad"><x-admin.icon name="alert" class="i-sm" /><span>{{ $errors->first() }}</span></div>
            @endif
            <div class="field">
                <label for="username">{{ __('Username') }}</label>
                <input class="input" id="username" name="username" value="{{ old('username') }}" autocomplete="username" required autofocus>
            </div>
            <div class="field">
                <label for="password">{{ __('Password') }}</label>
                <input class="input" id="password" type="password" name="password" autocomplete="current-password" required>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;gap:10px">
                <label class="check"><input type="checkbox" name="remember" value="1" @checked(old('remember'))><span>{{ __('Remember me') }}</span></label>
                <div style="display:flex;gap:6px">
                    @foreach(['tm', 'ru', 'en'] as $code)
                    <a class="chip {{ $code === $l ? 'on' : '' }}" href="{{ route('admin.login', $code) }}">{{ strtoupper($code) }}</a>
                    @endforeach
                </div>
            </div>
            <button class="btn btn-primary btn-block" type="submit">{{ __('Sign in') }}</button>
            <button class="btn btn-ghost btn-block" type="button" data-theme-toggle>
                <x-admin.icon name="moon" class="i-sm theme-light" /><x-admin.icon name="sun" class="i-sm theme-dark" />{{ __('Light / dark theme') }}
            </button>
        </form>
    </main>
</div>
<script src="{{ $v('admin/admin.js') }}"></script>
</body>
</html>
