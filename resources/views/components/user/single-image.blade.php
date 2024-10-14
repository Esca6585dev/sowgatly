<main class="main__section__single">
    <div class="main__section__single__wrapper">
        <div class="breadcrumb">
            <a href="index.html" class="breadcrumb__latest">Watch</a>
            <svg aria-label="breadcrumb arrow" class="breadcrumb__icon" height="8" role="img" viewBox="0 0 24 24"
                width="8">
                <path
                    d="M6.65.66c-.87.88-.87 2.3 0 3.18L14.71 12l-8.06 8.16c-.87.88-.87 2.3 0 3.18a2.2 2.2 0 0 0 3.14 0L21 12 9.8.66a2.2 2.2 0 0 0-3.15 0">
                </path>
            </svg>
            <a href="single-page.html" class="breadcrumb__current">Beauty</a>
        </div>

        <div class="main__section__single__body__wrapper">
            <div class="main__section__single__body__container">

                <div class="main__section__single__body__left">
                    @if($id < 163)
                    <img class="bg__image__single" loading="lazy"
                        data-src="{{ asset("img/images/hande-ercel ($id).jpg") }}"
                        alt="{{ asset("img/images/hande-ercel ($id).jpg") }}" title="Open">
                    @else
                    <video class="bg__image__single" controls muted autoplay loop loading="lazy"
                        src="{{ asset("video/video ($id).mp4") }}" alt="{{ asset("video/video ($id).mp4") }}"
                        title="Open" onclick="redirectToUrl({{ $id }}, '{{ app()->getlocale() }}')">
                    @endif
                </div>

                <div class="main__section__single__body__right">

                    <div class="main__section__single__body__left__inside">

                        <div class="main__section__single__body__left__inside__button">
                            <a class="icon__button" onclick="openDropdownButton(1)">
                                <svg aria-hidden="true" height="20" role="img" viewBox="0 0 24 24" width="20"
                                    title="Share">
                                    <path
                                        d="M10 7.66 8.81 8.84a2 2 0 0 1-2.84-2.82l6-6.02L18 6.01a2 2 0 0 1-2.82 2.83l-1.2-1.19v6.18a2 2 0 0 1-4 0zM19 16a2 2 0 0 1 4 0v6a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-6a2 2 0 0 1 4 0v4h14z">
                                    </path>
                                </svg>

                                <span class="icon__button__title noselect">Share</span>
                            </a>

                            <ul class="icon__button__dropdown noselect hide" id="icon__button__dropdown__1">
                                <li class="icon__button__dropdown__li">Download image</li>
                                <li class="icon__button__dropdown__li">Hide Pin</li>
                            </ul>
                        </div>

                        <div class="main__section__single__body__left__inside__button">
                            <a class="icon__button" onclick="openDropdownButton(2)">
                                <svg aria-hidden="true" height="20" role="img" viewBox="0 0 24 24" width="20">
                                    <path
                                        d="M12 9a3 3 0 1 0 0 6 3 3 0 0 0 0-6M3 9a3 3 0 1 0 0 6 3 3 0 0 0 0-6m18 0a3 3 0 1 0 0 6 3 3 0 0 0 0-6">
                                    </path>
                                </svg>

                                <span class="icon__button__title noselect">Options</span>

                            </a>

                            <ul class="icon__button__dropdown noselect hide" id="icon__button__dropdown__2">
                                <li class="icon__button__dropdown__li">Download image</li>
                                <li class="icon__button__dropdown__li">Hide Pin</li>
                                <li class="icon__button__dropdown__li">Report Pin</li>
                                <li class="icon__button__dropdown__li">Get Pin embed code</li>
                            </ul>
                        </div>



                    </div>

                    <div class="main__section__single__body__right__inside__button">
                        <button class="bg__grey__button">Visit</button>
                        <button class="bg__red__button">Save</button>
                    </div>


                </div>
            </div>
        </div>

    </div>
</main>