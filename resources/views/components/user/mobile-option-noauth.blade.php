<!-- begin::mobile option -->
<div class="main__section__image__option__mobile hide" id="image__option__mobile">
    <div class="main__section__image__option__top__mobile">
        <button class="main__section__image__option__top__btn__mobile" onclick="toggleOption(1)" id="close__1">
            <svg aria-hidden="true" class="close__button" width="24" height="24" role="img" viewBox="0 0 24 24">
                <path
                    d="m15.18 12 7.16-7.16a2.25 2.25 0 1 0-3.18-3.18L12 8.82 4.84 1.66a2.25 2.25 0 1 0-3.18 3.18L8.82 12l-7.16 7.16a2.25 2.25 0 1 0 3.18 3.18L12 15.18l7.16 7.16a2.24 2.24 0 0 0 3.18 0c.88-.88.88-2.3 0-3.18z">
                </path>
            </svg>
        </button>
    </div>

    <div class="main__section__image__option__text__section">
        <p class="main__section__image__option__text__name" id="optionName"></p>
        <p class="main__section__image__option__text__desc">
            <p class="main__section__image__option__text__description description__hide"
                id="image__option__text__description"></p>
            <button class="main__section__image__option__button__more" onclick="descShowHide()"
                id="image__option__button__more" data-text-type="More">More</button>
        </p>
        <div class="main__section__image__option__profile">
            <img class="main__section__image__option__profile__img" src="{{ asset('img/logo/modahouse-logo-favicon.ico') }}"
                alt="{{ asset('img/logo/modahouse-logo-favicon.ico') }}" id="optionProfileIcon">
            <div class="main__section__image__option__profile__text">
                <p class="main__section__image__option__profile__text__account" id="optionProfileName"></p>
                <p class="main__section__image__option__profile__text__category">DIY Gifts</p>
            </div>
        </div>
    </div>


    <hr class="hr__line">

    <div class="main__section__image__option__menu__mobile">
        <ul class="main__section__image__option__menu__ul__mobile">
            <li class="main__section__image__option__menu__li__mobile">
                <img src="{{ asset('img/icon/Download.svg') }}" alt="{{ asset('img/icon/Download.svg') }}"
                    class="main__section__image__option__menu__li__icon__mobile">

                <p class="main__section__image__option__menu__li__name__mobile">
                    {{ __('Download') }}
                </p>
            </li>
            <li class="main__section__image__option__menu__li__mobile">
                <img src="{{ asset('img/icon/Share.svg') }}" alt="{{ asset('img/icon/Share.svg') }}"
                    class="main__section__image__option__menu__li__icon__mobile">

                <p class="main__section__image__option__menu__li__name__mobile">
                    {{ __('Repost') }}
                </p>
            </li>
        </ul>
    </div>
</div>
<!-- end::mobile option -->
