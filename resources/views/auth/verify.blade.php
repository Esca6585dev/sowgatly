@extends('layouts.app')

@section('title')
	{{ __('Verify Your Email Address') }} | {{ __('Main State Service «Turkmenstandartary»') }}
@endsection

@section('content')
	<!--begin::Body-->
	<body id="kt_body" class="bg-body">
		<!--begin::Main-->
		<div class="d-flex flex-column flex-root">
			<!--begin::Authentication - Two-stes -->
			<div class="d-flex flex-column flex-column-fluid bgi-position-y-bottom position-x-center bgi-no-repeat bgi-size-contain bgi-attachment-fixed" style="background-image: url(assets/media/illustrations/development-hd.png)">
				<!--begin::Content-->
				<div class="d-flex flex-center flex-column flex-column-fluid p-10 pb-lg-20">
					<!--begin::Logo-->
					<a href="{{ route('main-page', app()->getlocale() ) }}" class="mb-12">
						<img alt="img/tds-logo/tds-logo.webp" src="{{ asset('img/tds-logo/tds-logo.webp') }}" class="h-75px" />
					</a>
					<!--end::Logo-->
					<!--begin::Wrapper-->
					<div class="w-lg-600px bg-body rounded shadow-sm p-10 p-lg-15 mx-auto">
						<!--begin::Form-->
						<form action="" method="get" class="form w-100 mb-10" >
							<!--begin::Icon-->
							<div class="text-center mb-10">
								<img alt="Logo" class="mh-125px" src="{{ asset('metronic-template/v8/assets/media/svg/misc/open-mail.svg') }}" />
							</div>
							<!--end::Icon-->
							<!--begin::Heading-->
							<div class="text-center mb-10">
								<!--begin::Mobile no-->
								<div class="fw-bolder text-primary fs-3"></div>
								<!--end::Mobile no-->
								<div style="border-bottom: 1px solid #eeeeee; margin: 10px 0;"></div>
								<!--begin::Title-->
								<h1 class="text-dark mb-3">{{ __('A verification code has been sent to your email address') }}</h1>
								<!--end::Title-->
							</div>
							<!--end::Heading-->
							<!--begin::Section-->
							<div class="mb-10 px-md-10">
								<!--begin::Label-->
								<div class="fw-bolder text-center text-muted fs-4 mb-5 ms-1">{{ __('Before proceeding, please check your email for a verification link.') }}</div>
								<!--end::Label-->
							</div>
							<!--end::Section-->
						</form>
						<!--end::Form-->

						<!--begin::Notice-->
						<div class="text-center fw-bold fs-5">
							<span class="text-muted me-1">{{ __('If you did not receive the email') }}</span>
                            <form method="POST" action="{{ route('verification.resend') }}">
                                @csrf
                                <button type="submit" class="btn btn-primary link-primary fw-bolder fs-5 me-1 mt-5">{{ __('Resend') }}</button>
                            </form>
						</div>
						<!--end::Notice-->


						<!--begin::Profile-->
						<div class="text-center fw-bold fs-5 mt-10">
							<a href="{{ route('profile', app()->getlocale() ) }}">{{ __('Profile') }}</a>
						</div>
						<!--end::Profile-->

					</div>
					<!--end::Wrapper-->
				</div>
				<!--end::Content-->
			</div>
			<!--end::Authentication - Two-stes-->
		</div>
		<!--end::Main-->

        <!--begin::Alert-->
        @if(session()->has('resent'))
			<div class="alert success" id="alert__success">
				<span class="closebtn">&times;</span>
				<strong>{{ __('A fresh verification link has been sent to your email address.') }}</strong>
			</div>
        @endif
        <!--end::Alert-->

	</body>
	<!--end::Body-->

<style>
	#kt_body {
		background-image: url('/img/tds-wallpaper-4.jpg') !important;
		background-repeat: repeat;
		background-position: right center;
		height: 100vh;
	}

	.alert {
		transition: opacity 0.6s;
		position: fixed;
		top: 10px;
		right: 10px;
		width: 350px;
		z-index: 9999;
		opacity: 1;
		border-radius: 0px;
		color: #fff;
		padding: 20px;
		border-radius: 5px;
		background-color: #0095e8;
	}

	.closebtn {
		margin-left: 15px;
		color: white;
		font-weight: bold;
		font-size: 22px;
		line-height: 20px;
		cursor: pointer;
		transition: 0.3s;
		float: right;
	}

	.closebtn:hover {
		color: black;
	}

	@media only screen and (max-width: 900px) {
		.success {
			width: 250px;
		}
	}

</style>

<script>
	var close = document.getElementsByClassName("closebtn");

	for (var i = 0; i < close.length; i++) {
		close[i].onclick = function(){
			var div = this.parentElement;
			div.style.opacity = "0";
			setTimeout(function(){ div.style.display = "none"; }, 600);
		}
	}

	alertFade();

	function alertFade(){
		setTimeout(
            function(){
                document.getElementById("alert__success").style.display='none';
            }, 3000
        );
	}

</script>

@endsection
