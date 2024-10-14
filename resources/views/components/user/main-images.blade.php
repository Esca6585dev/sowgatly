<main class="main__section__image">
    <div class="main__section__image__wrapper">

        <h3 class="h3__text">Browse popular ideas</h3>

        <div class="main__section__image__container__grid">



            @for($id=1; $id<=162; $id++) <div class="main__section__image__column">
                <div class="image__item">
                    <a href="{{ route('single-page', [app()->getlocale(), $id]) }}">
                        <img class="bg__image__3" loading="lazy"
                            data-src="{{ asset("img/images/hande-ercel ($id).jpg") }}"
                            alt="{{ asset("img/images/hande-ercel ($id).jpg") }}" title="Open">
                    </a>

                    <x-user.option :id="$id" />

                </div>
                <h3 class="main__section__image__box__text__heading hide2" id="name{{ $id }}">Eda yildiz {{ $id }}</h3>
                <p class="main__section__image__box__text__body hide2" id="desc{{ $id }}" title="Hande Erçel | Quotes $id Lorem ipsum dolor sit amet consectetur, adipisicing elit. Reprehenderit porro molestias voluptatum omnis veniam nesciunt accusantium ad, natus eligendi cum ipsum corporis similique voluptatem, magni nostrum illum expedita nobis voluptates?">{{ Str::limit("Hande Erçel | Quotes $id Lorem ipsum dolor sit amet consectetur, adipisicing elit. Reprehenderit porro molestias voluptatum omnis veniam nesciunt accusantium ad, natus eligendi cum ipsum corporis similique voluptatem, magni nostrum illum expedita nobis voluptates?", 50) }}</p>
                <div class="main__section__image__box__profile">
                    <div class="main__section__image__box__profile__lt">
                        <img class="main__section__image__box__profile__img"
                            src="{{ asset('img/logo/brand_logo.png') }}" alt="{{ asset('img/logo/brand_logo.png') }}"
                            id="profileIcon{{ $id }}">
                        <p class="main__section__image__box__profile__text" id="profileName{{ $id }}">Hande Ercel {{ $id }}</p>
                    </div>
                    <div class="main__section__image__box__profile__rt">
                        <img class="more__btn" onclick="toggleOption({{ $id }})"
                            src="{{ asset('img/icon/three-dots-svgrepo-com.svg') }}" type="image/svg+xml">
                    </div>
                </div>
                </a>
        </div>
        @endfor

        @for($id=163; $id<=204; $id++) <div class="main__section__image__column">
            <div class="image__item">

                <a href="{{ route('single-page', [app()->getlocale(), $id]) }}">
                    <video class="bg__image__3" controls muted loop loading="lazy">
                        <source src="{{ asset("video/video ($id).mp4") }}" type="video/mp4">
                    </video>
                </a>

                <x-user.option :id="$id" />

            </div>
            <h3 class="main__section__image__box__text__heading hide2" id="name{{ $id }}">Hande Eda yildiz {{ $id }}</h3>
            <p class="main__section__image__box__text__body hide2" id="desc{{ $id }}">Hande Erçel | Video {{ $id }}</p>
            <div class="main__section__image__box__profile">
                <div class="main__section__image__box__profile__lt">
                    <img class="main__section__image__box__profile__img" src="{{ asset('img/logo/brand_logo.png') }}"
                        alt="{{ asset('img/logo/brand_logo.png') }}" id="profileIcon{{ $id }}">
                    <p class="main__section__image__box__profile__text" id="profileName{{ $id }}">Hande Ercel {{ $id }}</p>
                </div>
                <div class="main__section__image__box__profile__rt">
                    <img class="more__btn" onclick="toggleOption({{ $id }})"
                        src="{{ asset('img/icon/three-dots-svgrepo-com.svg') }}" type="image/svg+xml">
                </div>
            </div>
            </a>
    </div>
    @endfor

    </div>
</main>
