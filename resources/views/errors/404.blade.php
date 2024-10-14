@extends('layouts.app')

@section('title')
{{ __('404') }} | {{ __('Main State Service «Turkmenstandartary»') }}
@endsection

@section('content')
<!--begin::Body-->

<body id="kt_body" class="bg-body" style="background-image: url('/public/img/bg-image-1.jpg');">
    <!--begin::Main-->
    <div class="d-flex flex-column flex-root">
        <!--begin::Authentication - Error 404 -->
        <div class="d-flex flex-column flex-column-fluid bgi-position-y-bottom position-x-center bgi-no-repeat bgi-size-contain bgi-attachment-fixed"
            style="background-image: url({{ asset('metronic-template/v8/assets/media/illustrations/development-hd.png') }})">
            <!--begin::Content-->
            <div class="d-flex flex-column flex-column-fluid text-center p-10 py-lg-20">
                <!--begin::Logo-->
                <a href="{{ url('/') }}" class="mb-10 pt-lg-20">
                    <svg width="113" height="32" viewBox="0 0 113 32" xmlns="http://www.w3.org/2000/svg" fill="#fa4d1e"
                        data-v-d92e36ae="">
                        <path
                            d="M30.5097 7.72414c-1.5882 0-2.6801.60188-3.375 1.70533-.6949 1.10343-.6949 1.60503-.6949 3.31033v.9028h-1.2904v2.4076h1.2904v9.0282h2.7795v-9.0282h2.8787v-2.4076h-2.8787v-.9028c0-1.8056.0992-2.1066 1.0919-2.5078.1985-.1003.3971-.1003.5956-.1003.2978 0 .5956.1003.7941.2006l.3971.2006V8.22571l-.0993-.10032c-.397-.30094-.8934-.40125-1.489-.40125zm3.2758-.20063h2.7795V25.0784h-2.7795V7.52351zM44.8041 13.442c-3.2758 0-5.8567 2.6082-5.8567 5.9185s2.5809 5.9185 5.8567 5.9185 5.8567-2.6082 5.8567-5.9185-2.5809-5.9185-5.8567-5.9185zm0 9.5298c-1.7868 0-3.1765-1.5047-3.1765-3.6113 0-2.1066 1.3897-3.6113 3.1765-3.6113s3.1765 1.5047 3.1765 3.6113c0 2.1066-1.3897 3.6113-3.1765 3.6113zm32.7578-2.2069l-1.9853-6.0188-.397-1.1035h-1.9854l-.2978 1.1035-1.9853 6.0188-2.3824-7.022.0993-.1003h-2.8787l.0992.2007-2.3824 6.9216-1.9853-6.0188-.3971-1.1035h-1.9853l-.4963 1.1035-1.9853 6.0188-2.3824-7.1223h-2.7795l4.2685 11.4358h1.6875l2.4817-6.6207 2.4816 6.6207h1.6875l2.8788-7.6239 2.8787 7.6239h1.6875l2.4817-6.6207 2.4816 6.6207h1.9853l4.3678-11.4358h-2.7795l-2.4817 7.1223zm11.6142-7.3229c-3.2758 0-5.8567 2.6082-5.8567 5.9185s2.5809 5.9185 5.8567 5.9185 5.8567-2.6082 5.8567-5.9185-2.5809-5.9185-5.8567-5.9185zm0 9.5298c-1.7868 0-3.1765-1.5047-3.1765-3.6113 0-2.1066 1.3897-3.6113 3.1765-3.6113s3.1765 1.5047 3.1765 3.6113c0 2.1066-1.3897 3.6113-3.1765 3.6113zm20.9449-9.3292l-2.382 7.1223-1.985-6.0188-.398-1.1035h-1.985l-.298 1.1035-1.985 6.0188-2.3823-7.1223h-2.7795l4.3678 11.4358h1.687l2.482-6.6207 2.482 6.6207h1.687L113 13.6426h-2.879zm-99.4645 7.3229l-.0993-6.2194c2.8787-.4013 4.9633-2.1066 6.0553-5.11601.9926-2.60815 1.1912-6.52037.8934-8.62696-1.6876.40126-4.2685 1.80565-5.0626 2.50784C11.8477 2.40752 10.458.80251 9.4653 0c-.99267.90282-2.38239 2.40752-2.97799 3.51097-.79413-.70219-3.37505-2.10658-5.06258-2.50784-.2978 2.10659-.09927 6.01881.8934 8.62696 1.09193 3.00941 3.17652 4.71471 6.05524 5.11601v6.3197s0-.1003-.09927-.1003C6.78511 18.558 3.90639 15.9498.13427 14.9467-.65986 21.5674 2.02033 29.3919 9.4653 32c7.445-2.6081 10.1252-10.5329 9.331-17.1536-3.7721 1.1034-6.5515 3.7116-8.1398 6.1191zm-8.43764-2.8088c2.87872 1.7054 6.15451 4.9154 6.05524 10.9342-3.67285-2.4075-5.65817-6.0188-6.05524-10.9342zm7.24644-5.6175c-4.66552 0-5.75745-4.21318-5.95598-8.42635 1.29046.7022 2.48166 1.70533 3.47432 2.80878.69486-1.5047 1.29046-2.60815 2.48166-3.81191 1.1912 1.20376 1.7868 2.30721 2.4817 3.81191.9926-1.00313 2.1838-2.10658 3.4743-2.70846-.1986 4.11285-1.2905 8.32603-5.956 8.32603zm1.1912 16.5517c0-6.0188 3.2758-9.3291 6.0552-10.9342-.2978 4.9154-2.2831 8.5267-6.0552 10.9342z">
                        </path>
                    </svg>
                </a>
                <!--end::Logo-->
                <!--begin::Wrapper-->
                <div>
                    <!--begin::Logo-->
                    <h1 class="fw-bolder fs-4x text-gray-800 mb-5">404</h1>
                    <h1 class="fw-bolder fs-4x text-gray-800 mb-5">{{ __('Page Not Found') }}</h1>
                    <h1 class="fw-bolder fs-4x text-gray-800 mb-5">Page Not Found</h1>
                    <h1 class="fw-bolder fs-4x text-gray-800 mb-5">Страница не найдена</h1>
                    <!--end::Logo-->
                    <!--begin::Message-->
                    <div class="fw-bold fs-3 text-muted mb-15">Siziň gözleýän sahypaňyz tapylmady!</div>
                    <!--end::Message-->
                    <!--begin::Action-->
                    <div class="text-center">
                        <a href="{{ url('/') }}" class="btn btn-lg btn-primary fw-bolder">{{ __('Go to homepage') }}</a>
                    </div>
                    <!--end::Action-->
                </div>
                <!--end::Wrapper-->
            </div>
            <!--end::Content-->
            <!--begin::Footer-->
            <div class="d-flex flex-center flex-column-auto p-16">
                <!--begin::Links-->
                <div class="d-flex align-items-center fw-bold fs-6">
                    <select class="form-select" id="changeLanguage">
                        @foreach (Config::get('languages') as $lang => $language)
                        <option value="{{ $lang }}" {{ Request::segment(1) == $lang ? 'selected' : '' }}>
                            {{ $language['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <!--end::Links-->
            </div>
            <div style="padding-top: 277px"></div>
            <!--end::Footer-->
        </div>
        <!--end::Authentication - Error 404-->
    </div>
    <!--end::Main-->
</body>
<!--end::Body-->

@endsection
