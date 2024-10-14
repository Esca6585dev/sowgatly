<div class="main__section__image__option__mobile hide" id="image__option__mobile">
    <div class="main__section__image__option__top__mobile">
        <button class="main__section__image__option__top__btn__mobile" onclick="toggleOption(1)" id="close__1">
            <svg aria-hidden="true" class="close__button" height="18" width="18" role="img" viewBox="0 0 24 24">
                <path
                    d="m15.18 12 7.16-7.16a2.25 2.25 0 1 0-3.18-3.18L12 8.82 4.84 1.66a2.25 2.25 0 1 0-3.18 3.18L8.82 12l-7.16 7.16a2.25 2.25 0 1 0 3.18 3.18L12 15.18l7.16 7.16a2.24 2.24 0 0 0 3.18 0c.88-.88.88-2.3 0-3.18z">
                </path>
            </svg>
        </button>
    </div>

    <div class="main__section__image__option__social__media">

        <button class="main__section__image__option__social__media__button">
            <img class="main__section__image__option__social__media__button__icon"
                src="{{ asset('img/social-media/pinterest.svg') }}">
            <p class="main__section__image__option__social__media__button__name">Pinterest</p>
        </button>

        <button class="main__section__image__option__social__media__button">
            <img class="main__section__image__option__social__media__button__icon"
                src="{{ asset('img/social-media/facebook-3.svg') }}">
            <p class="main__section__image__option__social__media__button__name">Facebook</p>
        </button>

        <button class="main__section__image__option__social__media__button">
            <img class="main__section__image__option__social__media__button__icon"
                src="{{ asset('img/social-media/github-1.svg') }}">
            <p class="main__section__image__option__social__media__button__name">Github</p>
        </button>

        <button class="main__section__image__option__social__media__button">
            <img class="main__section__image__option__social__media__button__icon"
                src="{{ asset('img/social-media/google-icon.svg') }}">
            <p class="main__section__image__option__social__media__button__name">Google</p>
        </button>

        <button class="main__section__image__option__social__media__button">
            <img class="main__section__image__option__social__media__button__icon"
                src="{{ asset('img/social-media/instagram-2-1.svg') }}">
            <p class="main__section__image__option__social__media__button__name">Instagram</p>
        </button>

        <button class="main__section__image__option__social__media__button">
            <img class="main__section__image__option__social__media__button__icon"
                src="{{ asset('img/icon/document-copy-svgrepo-com.svg') }}">
            <p class="main__section__image__option__social__media__button__name">Copy</p>
        </button>

        <button class="main__section__image__option__social__media__button">
            <img class="main__section__image__option__social__media__button__icon"
                src="{{ asset('img/icon/Share.svg') }}">
            <p class="main__section__image__option__social__media__button__name">Share</p>
        </button>

    </div>

    <hr class="hr__line">

    <div class="main__section__image__option__menu__mobile">
        <ul class="main__section__image__option__menu__ul__mobile">
            <li class="main__section__image__option__menu__li__mobile">
                <img src="{{ asset('img/icon/Download.svg') }}" alt="{{ asset('img/icon/Download.svg') }}"
                    class="main__section__image__option__menu__li__icon__mobile">

                <p class="main__section__image__option__menu__li__name__mobile">
                    Download image
                </p>
            </li>
            <li class="main__section__image__option__menu__li__mobile">
                <img src="{{ asset('img/icon/Share.svg') }}" alt="{{ asset('img/icon/Share.svg') }}"
                    class="main__section__image__option__menu__li__icon__mobile">

                <p class="main__section__image__option__menu__li__name__mobile">
                    Repost
                </p>
            </li>
        </ul>
    </div>
</div>