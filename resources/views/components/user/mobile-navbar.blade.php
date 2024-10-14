<!-- begin::mobile__menu__bottom -->
<navbar class="mobile__menu__bottom" id="mobile__menu__bottom">
    <ul class="mobile__menu__bottom__icon__ul">
        <li class="mobile__menu__bottom__icon__li">
            <a href="#Home" class="mobile__icon">
                <svg aria-label="Home" class="svg__icon" height="24" role="img" viewBox="0 0 24 24" width="24">
                    <path d="M12 0 1 10v14h8v-7a3 3 0 1 1 6 0v7h8V10z"></path>
                </svg>
            </a>
        </li>
        <li class="mobile__menu__bottom__icon__li">
            <a href="#Search" class="mobile__icon">
                <svg aria-label="Search" class="svg__icon" height="24" role="img" viewBox="0 0 24 24" width="24">
                    <path
                        d="M10 16a6 6 0 1 1 .01-12.01A6 6 0 0 1 10 16m13.12 2.88-4.26-4.26a10 10 0 1 0-4.24 4.24l4.26 4.26a3 3 0 1 0 4.24-4.24">
                    </path>
                </svg>
            </a>
        </li>
        <li class="mobile__menu__bottom__icon__li mobile__menu__bottom__icon__li__center">
            <a href="#Post" class="mobile__icon mobile__icon__center">
                <svg aria-label="Post" class="svg__icon svg__icon__add" xmlns="http://www.w3.org/2000/svg" x="0px"
                    y="0px" width="40" height="40" viewBox="0 0 50 50">
                    <path
                        d="M25,2C12.317,2,2,12.317,2,25s10.317,23,23,23s23-10.317,23-23S37.683,2,25,2z M37,26H26v11h-2V26H13v-2h11V13h2v11h11V26z">
                    </path>
                </svg>
            </a>
        </li>
        <li class="mobile__menu__bottom__icon__li">
            <a href="#Inbox" class="mobile__icon">
                <svg aria-label="News notifications and messages" class="svg__icon" height="24" role="img"
                    viewBox="0 0 24 24" width="24">
                    <path
                        d="M18 12.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m-6 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m-6-3a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3M12 0a11 11 0 0 0-8.52 17.95l-1.46 5.43a.5.5 0 0 0 .73.55l5.08-2.75A10.98 10.98 0 0 0 23 11 11 11 0 0 0 12 0">
                    </path>
                </svg>
            </a>
        </li>
        <li class="mobile__menu__bottom__icon__li">
            @if(Auth::check())
            <a href="#Saved" class="mobile__icon">
              <img class="svg__icon__profile" src="{{ asset('img/logo/brand_logo.png') }}" alt="{{ asset('img/logo/brand_logo.png') }}" width="24">
            </a>
            @else
            <a href="#Saved" class="mobile__icon">
                <svg aria-label="Мой профиль" class="svg__icon" height="24" role="img" viewBox="0 0 24 24" width="24">
                    <path d="M17.5 5.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0M2 22a10 10 0 1 1 20 0v2H2z"></path>
                </svg>
            </a>
            @endif
        </li>
    </ul>
</navbar>
<!-- end::mobile__menu__bottom -->