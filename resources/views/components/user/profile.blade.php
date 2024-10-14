<div class="section__profile__body">

    <x-user.profile.breadcrumb route="Profile" />

    <div class="d-flex justify-content-between align-items-center">
        <h2>{{ __('Profile') }}</h2>

        <div>
            <a href="{{ route('profile.password.change', app()->getlocale() ) }}" class="btn">{{ __('Password Change') }}</a>
        </div>
    </div>

    <div class="section__profile__form">

        <form class="section__container__profile__wrapper" action="#" @disabled(true)>
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
                    <label class="hide-700" for="first_name">{{ __('First Name') }}</label>

                    <input type="text" name="first_name" class="input" placeholder="{{ __('First Name') }}" value="{{ Auth::user()->first_name }}" {{ Auth::user()->first_name ? 'disabled' : '' }} >
                </div>
            </div>
            <div class="row">
                <div class="col d-flex">
                    <label class="hide-700" for="last_name">{{ __('Last Name') }}</label>

                    <input type="text" name="last_name" class="input" placeholder="{{ __('Last Name') }}" value="{{ Auth::user()->last_name }}" {{ Auth::user()->last_name ? 'disabled' : '' }} >
                </div>
            </div>
            <div class="row">
                <div class="col d-flex">
                    <label class="hide-700" for="email">{{ __('Email Address') }}</label>

                    <input type="text" name="email" class="input__email" placeholder="{{ __('Email Address') }}" value="{{ Auth::user()->email }}" {{ Auth::user()->email ? 'disabled' : '' }} >
                </div>
            </div>
            @if(Auth::user()->roles->pluck('name')->first() != 'raýat')
            <div class="row">
                <div class="col d-flex">
                    <label class="hide-700" for="company_name">{{ __('Company name') }}</label>

                    <input type="text" name="company_name" class="input" placeholder="{{ __('Company name') }}" value="{{ Auth::user()->company_name }}" {{ Auth::user()->company_name ? 'disabled' : '' }} >
                </div>
            </div>
            @endif
            <div class="row">
                <div class="col d-flex">
                    <label class="hide-700" for="last_name">{{ __('Address') }}</label>

                    <textarea id="subject" name="address" class="input" placeholder="{{ __('Enter your address') }}" style="height:100px" {{ Auth::user()->address ? 'disabled' : '' }} >{{ Auth::user()->address }}</textarea>
                </div>
            </div>
            <div class="row">
                <div class="col d-flex">
                    <label class="hide-700" for="last_name">{{ __('Phone') }}</label>

                    <div class="form__profile__phone__number">
                        <span class="phone__number__code">+993</span>
                        <input type="text" name="phone_number" id="phone_number" class="input" placeholder="{{ __('Enter your phone-number') }}" value="{{ old('phone_number') ?? Auth::user()->phone_number }}" {{ Auth::user()->phone_number ? 'disabled' : '' }}>
                        
                        @if(Auth::user()->phone_number == null)
                        <div class="invalid-feedback">
                            <div data-field="phone_number" data-validator="notEmpty">
                                {{ __('Please enter your phone number!') }}
                            </div>
                        </div>
                        @endif

                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col d-flex">
                    <label class="hide-700" for="roles">{{ __('Roles') }}</label>
                    
                    <select name="roles" id="role" class="show__btn" {{ Auth::user()->roles->pluck('name')->first() ? 'disabled' : '' }} >
                        @foreach($roles as $role)
                            <option value="{{ $role }}" {{ Auth::user()->roles->pluck('name')->first() == $role ? 'selected=selected' : '' }} >{{ $role }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row hide" id="div__buttons">
                <div class="col d-flex justify-content-center align-items-center pt-10">
                    <button type="button" class="btn p-10-20 ma-10 w-100-p" onclick="inputDisable()">{{ __('Cancel') }}</button>
                    <button type="button" class="btn p-10-20 ma-10 w-100-p bg-goldenrod" onclick="saveData()" >{{ __('Save') }}</button>
                </div>
            </div>
        </form>

    </div>
</div>
