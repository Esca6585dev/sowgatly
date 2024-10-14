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

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="input-field">
                        <input type="text" placeholder="{{ __('Email Address') }}" class="@error('email') is-invalid @enderror" name="email" autocomplete="on" required value="{{ $email ?? old('email') }}">
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

                    
                    <div class="input-field">
                        <input type="password" class="password @error('password') is-invalid @enderror" placeholder="{{ __('Password') }}" name="password" autocomplete="on" required value="{{ old('password') }}" >
                        <i class="uil uil-lock icon">
                            <svg width="24px" height="24px" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                <defs>
                                    <polygon id="path-1" points="0 0 24 0 24 24 0 24"></polygon>
                                </defs>
                                <g id="Stockholm-icons-/-General-/-Lock" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <mask id="mask-2" fill="white">
                                        <use xlink:href="#path-1"></use>
                                    </mask>
                                    <g id="bound"></g>
                                    <path opacity="0.50" d="M7,10 L7,8 C7,5.23857625 9.23857625,3 12,3 C14.7614237,3 17,5.23857625 17,8 L17,10 L18,10 C19.1045695,10 20,10.8954305 20,12 L20,18 C20,19.1045695 19.1045695,20 18,20 L6,20 C4.8954305,20 4,19.1045695 4,18 L4,12 C4,10.8954305 4.8954305,10 6,10 L7,10 Z M12,5 C10.3431458,5 9,6.34314575 9,8 L9,10 L15,10 L15,8 C15,6.34314575 13.6568542,5 12,5 Z" id="Mask" fill="#daa520"></path>
                                </g>
                            </svg>
                        </i>
                        <i class="uil uil-eye-slash showHidePw">
                            <svg width="24px" height="24px" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                <g id="Stockholm-icons-/-General-/-Visible" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <rect id="bound" x="0" y="0" width="24" height="24"></rect>
                                    <path d="M3,12 C3,12 5.45454545,6 12,6 C16.9090909,6 21,12 21,12 C21,12 16.9090909,18 12,18 C5.45454545,18 3,12 3,12 Z" id="Shape" fill="#daa520" fill-rule="nonzero" opacity="0.7"></path>
                                    <path d="M12,15 C10.3431458,15 9,13.6568542 9,12 C9,10.3431458 10.3431458,9 12,9 C13.6568542,9 15,10.3431458 15,12 C15,13.6568542 13.6568542,15 12,15 Z" id="Path" fill="#000000" opacity="0.3"></path>
                                </g>
                            </svg>
                        </i>
                    </div>
                    @error('password')
                        <div class="invalid-feedback">
                            <div>{{ $message }}</div>
                        </div>
                    @enderror
                    <div class="input-field">
                        <input type="password" class="password @error('password') is-invalid @enderror" placeholder="{{ __('Confirm Password') }}" name="password_confirmation" autocomplete="on" required >
                        <i class="uil uil-lock icon ">
                            <svg width="24px" height="24px" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                <defs>
                                    <polygon id="path-1" points="0 0 24 0 24 24 0 24"></polygon>
                                </defs>
                                <g id="Stockholm-icons-/-General-/-Lock" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <mask id="mask-2" fill="white">
                                        <use xlink:href="#path-1"></use>
                                    </mask>
                                    <g id="bound"></g>
                                    <path opacity="0.50" d="M7,10 L7,8 C7,5.23857625 9.23857625,3 12,3 C14.7614237,3 17,5.23857625 17,8 L17,10 L18,10 C19.1045695,10 20,10.8954305 20,12 L20,18 C20,19.1045695 19.1045695,20 18,20 L6,20 C4.8954305,20 4,19.1045695 4,18 L4,12 C4,10.8954305 4.8954305,10 6,10 L7,10 Z M12,5 C10.3431458,5 9,6.34314575 9,8 L9,10 L15,10 L15,8 C15,6.34314575 13.6568542,5 12,5 Z" id="Mask" fill="#daa520"></path>
                                </g>
                            </svg>
                        </i>
                        <i class="uil uil-eye-slash showHidePw">
                            <svg width="24px" height="24px" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                <g id="Stockholm-icons-/-General-/-Visible" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <rect id="bound" x="0" y="0" width="24" height="24"></rect>
                                    <path d="M3,12 C3,12 5.45454545,6 12,6 C16.9090909,6 21,12 21,12 C21,12 16.9090909,18 12,18 C5.45454545,18 3,12 3,12 Z" id="Shape" fill="#daa520" fill-rule="nonzero" opacity="0.7"></path>
                                    <path d="M12,15 C10.3431458,15 9,13.6568542 9,12 C9,10.3431458 10.3431458,9 12,9 C13.6568542,9 15,10.3431458 15,12 C15,13.6568542 13.6568542,15 12,15 Z" id="Path" fill="#000000" opacity="0.3"></path>
                                </g>
                            </svg>
                        </i>
                    </div>
                    @error('password')
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
                        <input type="submit" value="{{ __('Reset Password') }}">
                    </div>
                </form>
            </div>

        </div>
    </div>

    <script src="{{ asset('js/auth-script.js') }}"></script>
</body>
</html>