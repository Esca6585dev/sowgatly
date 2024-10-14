<!DOCTYPE html>
<html lang="{{ app()->getlocale() }}">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('img/tds-logo/tds-icon.gif') }}" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/swiper-bundle.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/font-awesome.min.css') }}" />
    <title>{{ __(Str::ucfirst(last(request()->segments()))) }} | {{ __('Main State Service «Turkmenstandartary»') }}</title>
  </head>
  <body>

    <x-user.alert.all/>
    
    <x-user.header />

    <x-user.profile.sidebar />

    <x-user.top-button />

    <x-user.footer />

    <x-user.script />

  </body>
</html>
