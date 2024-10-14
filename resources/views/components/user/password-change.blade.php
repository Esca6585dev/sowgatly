<div class="section__profile__body">

    <ul class="breadcrumb">
        <li>
            <a href="{{ route('main-page', app()->getlocale() ) }}">
                {{ __('Main Page') }}
            </a>
        </li>
        <li>
          <a href="{{ route('profile', app()->getlocale() ) }}">
            {{ __('Profile') }}
          </a>
        </li>
        <li>
          {{ __('Password Change') }}
        </li>
    </ul>

    <h2>{{ __('Password Change') }}</h2>

    <div class="section__profile__form">

        <form class="section__container__profile__wrapper" action="{{ route('profile.password.edit', app()->getlocale() ) }}" method="post" >
            @csrf

            <span class="profile__edit" onclick="inputEnable()">
                <svg width="24px" height="24px" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                    <title>{{ __('Edit') }}</title>
                    <g id="Stockholm-icons-/-Design-/-Edit" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                        <rect id="bound" x="0" y="0" width="24" height="24"></rect>
                        <path d="M8,17.9148182 L8,5.96685884 C8,5.56391781 8.16211443,5.17792052 8.44982609,4.89581508 L10.965708,2.42895648 C11.5426798,1.86322723 12.4640974,1.85620921 13.0496196,2.41308426 L15.5337377,4.77566479 C15.8314604,5.0588212 16,5.45170806 16,5.86258077 L16,17.9148182 C16,18.7432453 15.3284271,19.4148182 14.5,19.4148182 L9.5,19.4148182 C8.67157288,19.4148182 8,18.7432453 8,17.9148182 Z" id="Path-11" fill="#000000" fill-rule="nonzero" transform="translate(12.000000, 10.707409) rotate(-135.000000) translate(-12.000000, -10.707409) "></path>
                        <rect id="Rectangle" fill="#000000" opacity="0.3" x="5" y="20" width="15" height="2" rx="1"></rect>
                    </g>
                </svg>
            </span>

            <div class="row">
                <div class="col d-flex">
                    <label class="hide-700" for="current_password">{{ __('Current Password') }}</label>

                    <input type="password" name="current_password" class="input @error('current_password') is-invalid @enderror" placeholder="{{ __('Current Password') }}" value="{{ old('current_password') }}" disabled>

                    @error('current_password')
                    <div class="invalid-feedback">
                        <div data-field="current_password" data-validator="notEmpty">
                            {{ $message }}
                        </div>
                    </div>
                    @enderror
                </div>
            </div>
            <div class="row">
                <div class="col d-flex">
                    <label class="hide-700" for="password">{{ __('New Password') }}</label>

                    <input type="password" name="password" class="input @error('password') is-invalid @enderror" placeholder="{{ __('New Password') }}" value="{{ old('password') }}" disabled>

                    @error('password')
                    <div class="invalid-feedback">
                        <div data-field="password" data-validator="notEmpty">
                            {{ $message }}
                        </div>
                    </div>
                    @enderror
                </div>
            </div>
            <div class="row">
                <div class="col d-flex">
                    <label class="hide-700" for="password_confirmation">{{ __('Confirm Password') }}</label>

                    <input type="password" name="password_confirmation" class="input @error('password_confirmation') is-invalid @enderror" placeholder="{{ __('Confirm Password') }}" value="{{ old('password_confirmation') }}" disabled>

                    @error('password_confirmation')
                    <div class="invalid-feedback">
                        <div data-field="password_confirmation" data-validator="notEmpty">
                            {{ $message }}
                        </div>
                    </div>
                    @enderror
                </div>
            </div>

            <div class="row hide" id="div__buttons">
                <div class="col d-flex justify-content-center align-items-center pt-10">
                    <button type="button" class="btn p-10-20 ma-10 w-100-p" onclick="inputDisable()">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn p-10-20 ma-10 w-100-p bg-goldenrod">{{ __('Password Change') }}</button>
                </div>
            </div>
        </form>

    </div>
</div>