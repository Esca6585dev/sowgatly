<header class="header" id="header">
    <div class="header__wrapper">
        <div class="header__left">
            <a href="#header__logo" class="header__logo">
                <img class="logo" src="{{ asset('img/logo/modahouse-logo-small.png') }}"
                    alt="{{ asset('img/logo/modahouse-logo-small.png') }}" width="150px">
            </a>

            <ul class="header__menu__left">
                <li class="header__menu__dropdown" onclick="openClose()">
                    <span id="dropdownText">House</span>
                    <i id="chevronDown" class="fa fa-chevron-down"></i>
                </li>

                <div id="dropdownMenu" class="hide">
                    <li onclick="selectDropdown('House')">
                        <span>House</span>

                        <span class="hide" id="iconHouse">
                            <svg aria-label="Selected item" height="12" role="img" viewBox="0 0 24 24" width="12">
                                <path
                                    d="M9 22 .73 13.75a2.5 2.5 0 1 1 3.54-3.53L9 14.94l10.73-10.7a2.5 2.5 0 0 1 3.54 3.52z">
                                </path>
                            </svg>
                        </span>
                    </li>
                    <li onclick="selectDropdown('Store')">
                        <span>Store</span>

                        <span class="hide" id="iconStore">
                            <svg aria-label="Selected item" height="12" role="img" viewBox="0 0 24 24" width="12">
                                <path
                                    d="M9 22 .73 13.75a2.5 2.5 0 1 1 3.54-3.53L9 14.94l10.73-10.7a2.5 2.5 0 0 1 3.54 3.52z">
                                </path>
                            </svg>
                        </span>
                    </li>
                </div>
            </ul>
        </div>

        <div class="header__center top__searchbox">
            <div class="header__center__searchbox">
                <i class="fa fa-search"></i>

                <input type="text" class="header__search__input" placeholder="Search">

                <button class="close__btn">
                    <svg aria-hidden="true" aria-label="" height="20" role="img" viewBox="0 0 24 24" width="20">
                        <path
                            d="M15.18 16.95 12 13.77l-3.18 3.18a1.25 1.25 0 0 1-1.77-1.77L10.23 12 7.05 8.82a1.25 1.25 0 0 1 1.77-1.77L12 10.23l3.18-3.18a1.25 1.25 0 1 1 1.77 1.77L13.77 12l3.18 3.18a1.25 1.25 0 0 1-1.77 1.77M24 12a12 12 0 1 0-24 0 12 12 0 0 0 24 0">
                        </path>
                    </svg>
                </button>
            </div>
        </div>

        <div class="header__right">

            @if(Auth::check())
            <ul class="header__menu__right hide2 auth">
                <li class="header__menu__right__li__icon">
                    <svg aria-hidden="true" aria-label="" class="BNH gUZ U9O kVc" height="24" role="img"
                        viewBox="0 0 24 24" width="24" fill="#767676">
                        <path
                            d="M19 7v6.17A10 10 0 0 1 22 19H2a10 10 0 0 1 3-5.83V7a7 7 0 1 1 14 0m-4 14a3 3 0 1 1-6 0z">
                        </path>
                    </svg>
                </li>
                <li class="header__menu__right__li__icon">
                    <svg aria-hidden="true" aria-label="" class="Hn_ BNH gUZ U9O kVc" height="24" role="img"
                        viewBox="0 0 24 24" width="24" fill="#767676">
                        <path
                            d="M18 12.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m-6 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m-6-3a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3M12 0a11 11 0 0 0-8.52 17.95l-1.46 5.43a.5.5 0 0 0 .73.55l5.08-2.75A10.98 10.98 0 0 0 23 11 11 11 0 0 0 12 0">
                        </path>
                    </svg>
                </li>
                <li class="header__menu__right__li__icon"><img src="{{ asset('img/logo/brand_logo.png') }}"
                        alt="{{ asset('img/logo/brand_logo.png') }}" class="header__menu__right__li__icon__img"
                        width="50px"></li>
                <li class="header__menu__right__li__chevron__down" onclick="openCloseProfile()">
                    <i class="fa fa-chevron-down" id="chevronDownProfile"></i>
                </li>

                <div id="dropdownMenuProfile" class="hide">
                    <p class="profile__text">Currently in</p>
                    <li class="profile__li">
                        <div class="profile__box">
                            <div class="profile__box__lt">
                                <img src="{{ asset('img/logo/brand_logo.png') }}"
                                    alt="{{ asset('img/logo/brand_logo.png') }}" class="profile__box__logo">
                            </div>

                            <div class="profile__box__rt">
                                <span>Esca Meredoff</span>
                                <p>Personal</p>
                                <p>esca656585@gmail.com</p>
                            </div>
                        </div>
                    </li>
                    <p class="profile__text mt-10">Your accounts</p>
                    <li class="profile__li">Add account</li>
                    <li class="profile__li">Convert to business</li>
                    <p class="profile__text mt-10">More options</p>
                    <li class="profile__li">Settings</li>
                    <li class="profile__li">Home feed tuner</li>
                    <li class="profile__li">Install the Windows app</li>
                    <li class="profile__li">Reports and Violations Centre</li>
                    <li class="profile__li">Your privacy rights</li>
                    <li class="profile__li">Help Centre</li>
                    <li class="profile__li">Opens a new tab</li>
                    <li class="profile__li">Terms of Service</li>
                    <li class="profile__li">Opens a new tab</li>
                    <li class="profile__li">Privacy Policy</li>
                    <li class="profile__li">Opens a new tab</li>
                    <li class="profile__li">Be a beta tester</li>
                    <li class="profile__li">Opens a new tab</li>
                    <li class="profile__li">Log out</li>
                    <li class="profile__li hide">What is ModaHouse ?</li>
                    <li class="profile__li hide">View terms and privacy policy</li>
                    <li class="profile__li hide">Don't show login prompts</li>
                </div>

            </ul>
            @else
            <ul class="header__menu__right">
                <li class="header__menu__right__li">Log in</li>
                <li class="header__menu__right__li header__menu__red__active">Sign up</li>
            </ul>
            @endif




        </div>
    </div>
</header>