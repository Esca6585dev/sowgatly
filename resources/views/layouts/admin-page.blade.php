@php
    $locale = app()->getLocale();
    $admin = auth('admin')->user();
    $adminName = $admin ? trim(($admin->first_name ?? '') . ' ' . ($admin->last_name ?? '')) ?: $admin->username : 'Admin';
    $v = fn ($file) => asset($file) . '?v=' . @filemtime(public_path($file));
    $segments = request()->segments();
    $langUrl = function ($lang) use ($segments) {
        $s = $segments; if ($s) { $s[0] = $lang; }
        $q = request()->getQueryString();
        return url(implode('/', $s)) . ($q ? '?' . $q : '');
    };
    $languages = ['tm' => 'Türkmençe', 'ru' => 'Русский', 'en' => 'English'];
@endphp
<!doctype html>
<html lang="{{ $locale }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex">
    <title>@hasSection('page-title')@yield('page-title') · @endif{{ __('Admin panel') }} · Sowgatly</title>
    <script>(function(){var R=document.documentElement;try{var t=localStorage.getItem('sg-theme');if(!t)t=matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light';R.dataset.theme=t;if(localStorage.getItem('sg-collapsed')==='1')R.dataset.collapsed='';}catch(e){}})();</script>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/logo/favicon-32x32.png') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('img/logo/logo-rounded.svg') }}">
    <link rel="stylesheet" href="{{ $v('admin/admin.css') }}">
    @stack('styles')
</head>
<body>
<div class="app">
    <div class="scrim" data-drawer></div>
    @include('layouts.sidebar', ['adminName' => $adminName, 'admin' => $admin])

    <main class="main">
        <header class="top">
            <button class="icon-btn burger" type="button" data-drawer aria-label="{{ __('Menu') }}"><x-admin.icon name="menu" /></button>
            <nav class="crumbs" aria-label="breadcrumb">
                <a href="{{ route('admin.dashboard', $locale) }}" title="{{ __('Dashboard') }}"><x-admin.icon name="home" class="i-sm" /></a>
                @hasSection('breadcrumb')<span class="sep">/</span>@yield('breadcrumb')@endif
            </nav>
            <div class="top-actions">
                <form class="top-search" method="get" action="{{ route('order.index', $locale) }}" role="search">
                    <x-admin.icon name="search" class="i-sm" />
                    <input type="search" name="search" placeholder="{{ __('Find an order by number or product') }}…" aria-label="{{ __('Search') }}">
                </form>
                <details class="menu">
                    <summary class="icon-btn" title="{{ __('Language') }}" style="list-style:none"><x-admin.icon name="globe" /></summary>
                    <div class="menu-pop">
                        @foreach($languages as $code => $name)
                        <a href="{{ $langUrl($code) }}" class="{{ $code === $locale ? 'on' : '' }}">{{ strtoupper($code) }} · {{ $name }}</a>
                        @endforeach
                    </div>
                </details>
                <button class="icon-btn" type="button" data-theme-toggle title="{{ __('Light / dark theme') }}">
                    <x-admin.icon name="moon" class="theme-light" /><x-admin.icon name="sun" class="theme-dark" />
                </button>
                <a class="icon-btn" href="{{ route('order.index', [$locale, 'status' => 'pending']) }}" title="{{ __('New orders') }}">
                    <x-admin.icon name="bell" />@if(($sidebarCounts['orders'] ?? 0) > 0)<span class="dot"></span>@endif
                </a>
            </div>
        </header>

        @yield('content')
    </main>
</div>

@include('layouts.alert')

<dialog class="modal" id="confirm-dialog">
    <form method="dialog">
        <div class="modal-b">
            <div class="modal-icon"><x-admin.icon name="trash" /></div>
            <h3>{{ __('Warning') }}</h3>
            <p class="muted" data-confirm-text>{{ __('Are you sure you want to delete this resource?') }}</p>
        </div>
        <div class="modal-f">
            <button class="btn" value="cancel">{{ __('Cancel') }}</button>
            <button class="btn btn-danger" value="ok">{{ __('Delete') }}</button>
        </div>
    </form>
</dialog>

<script src="{{ $v('admin/admin.js') }}"></script>
@stack('scripts')
</body>
</html>
