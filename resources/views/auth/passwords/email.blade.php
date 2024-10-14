<!DOCTYPE html>
<html lang="{{ app()->getlocale() }}">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="shortcut icon" href="{{ asset('img/tds-logo/tds-icon.gif') }}" />

    <!-- ===== CSS ===== -->
    <link rel="stylesheet" href="{{ asset('css/auth-style.css') }}">
         
    <title>{{ __('Reset Password') }} | {{ __('Main State Service «Turkmenstandartary»') }}</title>
</head>
<body>
    <div class="container">
        <div class="forms" style="height: auto;">
            <div class="form login">
                <div class="form-top">
                    <span class="title">{{ __('Reset Password') }}</span>
                    <a href="{{ route('main-page', app()->getlocale()) }}">
                        <img class="logo" src="{{ asset('img/tds-logo/tds-logo.webp') }}" alt="img/tds-logo/tds-logo.webp">
                    </a>
                </div>

                <form method="POST" action="{{ route('password.email', app()->getlocale() ) }}">
                    @csrf

                    <div class="input-field">
                        <input type="text" placeholder="{{ __('Email Address') }}" class="@error('email') is-invalid @enderror" name="email" autocomplete="on" required value="{{ old('email') }}">
                        <i class="uil uil-envelope icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path opacity="0.50" d="M1 6C1 4.34315 2.34315 3 4 3H20C21.6569 3 23 4.34315 23 6V18C23 19.6569 21.6569 21 20 21H4C2.34315 21 1 19.6569 1 18V6Z" fill="#daa520"/>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M5.23177 7.35984C5.58534 6.93556 6.2159 6.87824 6.64018 7.2318L11.3598 11.1648C11.7307 11.4739 12.2693 11.4739 12.6402 11.1648L17.3598 7.2318C17.7841 6.87824 18.4147 6.93556 18.7682 7.35984C19.1218 7.78412 19.0645 8.41468 18.6402 8.76825L13.9205 12.7013C12.808 13.6284 11.192 13.6284 10.0794 12.7013L5.35981 8.76825C4.93553 8.41468 4.87821 7.78412 5.23177 7.35984Z" fill="#daa520" />
                            </svg>
                        </i>
                    </div>
                    @error('email')
                        <div class="invalid-feedback">
                            <div>{{ $message }}</div>
                        </div>
                    @enderror

                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="input-field button">
                        <input type="submit" value="{{ __('Send Password Reset Link') }}">
                    </div>
                </form>
            </div>

        </div>
    </div>

    <script src="{{ asset('js/auth-script.js') }}"></script>
</body>
</html>