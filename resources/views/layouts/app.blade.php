<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
	<!--begin::Head-->
	<head>
        <title>@yield('title') | {{ config('app.name') }}</title>
		
        <meta name="description" content="{{ config('app.name') }} @yield('title')" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta charset="utf-8" />
		<meta property="og:locale" content="{{ app()->getLocale() }}" />
		<meta property="og:title" content="{{ config('app.name') }} @yield('title')" />
		<meta property="og:site_name" content="{{ config('app.name') }}" />
		
		<link rel="shortcut icon" href="{{ asset('img/tds-logo/tds-icon.gif') }}" />
		<!--begin::Fonts-->
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
		<!--end::Fonts-->
		<!--begin::Global Stylesheets Bundle(used by all pages)-->
		<link href="{{ asset('metronic-template/v7/assets/css/alert.css') }}" rel="stylesheet" type="text/css" />
		<link href="{{ asset('metronic-template/v8/assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
		<!--end::Global Stylesheets Bundle-->

	</head>
	<!--end::Head-->
	<!--begin::Body-->

	<body id="kt_body" class="bg-body">
		<!--begin::Main-->
		<div id="app">
			<main>
				@yield('content')
			</main>
		</div>
	</body>
	<!--end::Body-->
    
    
    <!--end::Main-->
    <!--begin::Javascript-->
    <!--begin::Global Javascript Bundle(used by all pages)-->
    <script src="{{ asset('metronic-template/v8/assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('metronic-template/v8/assets/js/scripts.bundle.js') }}"></script>
    <!--end::Global Javascript Bundle-->
    
	<!--begin::jQuery-->
	<script src="{{ asset('/metronic-template/v7/assets/js/ajax/jquery-3.6.0.min.js') }}"></script>
	<script src="{{ asset('/metronic-template/v7/assets/js/ajax/changeLanguage.js') }}"></script>
	<script src="{{ asset('/js/script.js') }}"></script>
    <!--begin::jQuery-->

</html>