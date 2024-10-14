<!--begin::Aside Menu-->
<div class="aside-menu-wrapper flex-column-fluid" id="kt_aside_menu_wrapper">
    <!--begin::Menu Container-->
    <div id="kt_aside_menu" class="aside-menu my-4" data-menu-vertical="1" data-menu-scroll="1"
        data-menu-dropdown-timeout="500">
        <!--begin::Menu Nav-->
        <ul class="menu-nav">
            <!--begin::Dashboard-->
            <li class="menu-item {{ Request::is('*/admin/dashboard') ? 'menu-item-active' : '' }}" aria-haspopup="true">
                <a href="{{ route('admin.dashboard', app()->getlocale() ) }}" class="menu-link">
                    <span class="svg-icon menu-icon">
                        <!--begin::Svg Icon | path:assets/media/svg/icons/Design/Layers.svg-->
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                            height="24px" viewBox="0 0 24 24" version="1.1">
                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <polygon points="0 0 24 0 24 24 0 24" />
                                <path
                                    d="M12.9336061,16.072447 L19.36,10.9564761 L19.5181585,10.8312381 C20.1676248,10.3169571 20.2772143,9.3735535 19.7629333,8.72408713 C19.6917232,8.63415859 19.6104327,8.55269514 19.5206557,8.48129411 L12.9336854,3.24257445 C12.3871201,2.80788259 11.6128799,2.80788259 11.0663146,3.24257445 L4.47482784,8.48488609 C3.82645598,9.00054628 3.71887192,9.94418071 4.23453211,10.5925526 C4.30500305,10.6811601 4.38527899,10.7615046 4.47382636,10.8320511 L4.63,10.9564761 L11.0659024,16.0730648 C11.6126744,16.5077525 12.3871218,16.5074963 12.9336061,16.072447 Z"
                                    fill="#000000" fill-rule="nonzero" />
                                <path
                                    d="M11.0563554,18.6706981 L5.33593024,14.122919 C4.94553994,13.8125559 4.37746707,13.8774308 4.06710397,14.2678211 C4.06471678,14.2708238 4.06234874,14.2738418 4.06,14.2768747 L4.06,14.2768747 C3.75257288,14.6738539 3.82516916,15.244888 4.22214834,15.5523151 C4.22358765,15.5534297 4.2250303,15.55454 4.22647627,15.555646 L11.0872776,20.8031356 C11.6250734,21.2144692 12.371757,21.2145375 12.909628,20.8033023 L19.7677785,15.559828 C20.1693192,15.2528257 20.2459576,14.6784381 19.9389553,14.2768974 C19.9376429,14.2751809 19.9363245,14.2734691 19.935,14.2717619 L19.935,14.2717619 C19.6266937,13.8743807 19.0546209,13.8021712 18.6572397,14.1104775 C18.654352,14.112718 18.6514778,14.1149757 18.6486172,14.1172508 L12.9235044,18.6705218 C12.377022,19.1051477 11.6029199,19.1052208 11.0563554,18.6706981 Z"
                                    fill="#000000" opacity="0.3" />
                            </g>
                        </svg>
                        <!--end::Svg Icon-->
                    </span>
                    <span class="menu-text">{{ __('Dashboard') }}</span>
                </a>
            </li>
            <!--end::Dashboard-->
            <!--begin::Categories-->
            <li class="menu-item menu-item-submenu {{ Request::is('*/admin/*/category*') ? 'menu-item-open' : '' }} "
                aria-haspopup="true" data-menu-toggle="hover">
                <a href="javascript:;" class="menu-link menu-toggle">
                    <span class="svg-icon menu-icon">
                        <!--begin::Svg Icon | path:assets/media/svg/icons/Layout/Layout-4-blocks.svg-->
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                            height="24px" viewBox="0 0 24 24" version="1.1">
                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <rect x="0" y="0" width="24" height="24"></rect>
                                <rect fill="#000000" x="4" y="4" width="7" height="7" rx="1.5"></rect>
                                <path
                                    d="M5.5,13 L9.5,13 C10.3284271,13 11,13.6715729 11,14.5 L11,18.5 C11,19.3284271 10.3284271,20 9.5,20 L5.5,20 C4.67157288,20 4,19.3284271 4,18.5 L4,14.5 C4,13.6715729 4.67157288,13 5.5,13 Z M14.5,4 L18.5,4 C19.3284271,4 20,4.67157288 20,5.5 L20,9.5 C20,10.3284271 19.3284271,11 18.5,11 L14.5,11 C13.6715729,11 13,10.3284271 13,9.5 L13,5.5 C13,4.67157288 13.6715729,4 14.5,4 Z M14.5,13 L18.5,13 C19.3284271,13 20,13.6715729 20,14.5 L20,18.5 C20,19.3284271 19.3284271,20 18.5,20 L14.5,20 C13.6715729,20 13,19.3284271 13,18.5 L13,14.5 C13,13.6715729 13.6715729,13 14.5,13 Z"
                                    fill="#000000" opacity="0.3"></path>
                            </g>
                        </svg>
                        <!--end::Svg Icon-->
                    </span>
                    <span class="menu-text">{{ __('Categories') }}</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="menu-submenu">
                    <i class="menu-arrow"></i>
                    <ul class="menu-subnav">
                        <li class="menu-item {{ Request::is('*/admin/all/category*') ? 'menu-item-active' : '' }}"
                            aria-haspopup="true">
                            <a href="{{ route('category.index', [ app()->getlocale(), 'all' ] ) }}" class="menu-link">
                                <i class="menu-bullet menu-bullet-dot">
                                    <span></span>
                                </i>
                                <span class="menu-text">{{ __('All Categories') }}</span>
                            </a>
                        </li>
                        <li class="menu-item {{ Request::is('*/admin/parent/category*') ? 'menu-item-active' : '' }}"
                            aria-haspopup="true">
                            <a href="{{ route('category.index', [ app()->getlocale(), 'parent' ] ) }}"
                                class="menu-link">
                                <i class="menu-bullet menu-bullet-dot">
                                    <span></span>
                                </i>
                                <span class="menu-text">{{ __('Parent Categories') }}</span>
                            </a>
                        </li>
                        <li class="menu-item {{ Request::is('*/admin/sub/category*') ? 'menu-item-active' : '' }}"
                            aria-haspopup="true">
                            <a href="{{ route('category.index', [ app()->getlocale(), 'sub' ] ) }}" class="menu-link">
                                <i class="menu-bullet menu-bullet-dot">
                                    <span></span>
                                </i>
                                <span class="menu-text">{{ __('Sub Categories') }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <!--end::Categories-->
            <!--begin::Attributes-->
            <li class="menu-item {{ Request::is('*/admin/attribute*') ? 'menu-item-active' : '' }}"
                aria-haspopup="true">
                <a href="{{ route('attribute.index', app()->getlocale() ) }}" class="menu-link">
                    <span class="svg-icon menu-icon">
                        <svg width="24px" height="24px" viewBox="0 0 24 24" version="1.1"
                            xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <!-- Generator: Sketch 50.2 (55047) - http://www.bohemiancoding.com/sketch -->
                            <title>Stockholm-icons / Layout / Layout-arrange</title>
                            <desc>Created with Sketch.</desc>
                            <defs></defs>
                            <g id="Stockholm-icons-/-Layout-/-Layout-arrange" stroke="none" stroke-width="1" fill="none"
                                fill-rule="evenodd">
                                <rect id="bound" x="0" y="0" width="24" height="24"></rect>
                                <path
                                    d="M5.5,4 L9.5,4 C10.3284271,4 11,4.67157288 11,5.5 L11,6.5 C11,7.32842712 10.3284271,8 9.5,8 L5.5,8 C4.67157288,8 4,7.32842712 4,6.5 L4,5.5 C4,4.67157288 4.67157288,4 5.5,4 Z M14.5,16 L18.5,16 C19.3284271,16 20,16.6715729 20,17.5 L20,18.5 C20,19.3284271 19.3284271,20 18.5,20 L14.5,20 C13.6715729,20 13,19.3284271 13,18.5 L13,17.5 C13,16.6715729 13.6715729,16 14.5,16 Z"
                                    id="Combined-Shape" fill="#000000"></path>
                                <path
                                    d="M5.5,10 L9.5,10 C10.3284271,10 11,10.6715729 11,11.5 L11,18.5 C11,19.3284271 10.3284271,20 9.5,20 L5.5,20 C4.67157288,20 4,19.3284271 4,18.5 L4,11.5 C4,10.6715729 4.67157288,10 5.5,10 Z M14.5,4 L18.5,4 C19.3284271,4 20,4.67157288 20,5.5 L20,12.5 C20,13.3284271 19.3284271,14 18.5,14 L14.5,14 C13.6715729,14 13,13.3284271 13,12.5 L13,5.5 C13,4.67157288 13.6715729,4 14.5,4 Z"
                                    id="Combined-Shape" fill="#000000" opacity="0.3"></path>
                            </g>
                        </svg>
                    </span>
                    <span class="menu-text">{{ __('Attributes') }}</span>
                </a>
            </li>
            <!--end::Attributes-->
            <!--begin::User-->
            <li class="menu-item {{ Request::is('*/admin/user*') ? 'menu-item-active' : '' }}" aria-haspopup="true">
                <a href="{{ route('user.index', app()->getlocale() ) }}" class="menu-link">
                    <span class="svg-icon menu-icon">
                        <svg width="24px" height="24px" viewBox="0 0 24 24" version="1.1"
                            xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <g id="Stockholm-icons-/-General-/-User" stroke="none" stroke-width="1" fill="none"
                                fill-rule="evenodd">
                                <polygon id="Shape" points="0 0 24 0 24 24 0 24"></polygon>
                                <path
                                    d="M12,11 C9.790861,11 8,9.209139 8,7 C8,4.790861 9.790861,3 12,3 C14.209139,3 16,4.790861 16,7 C16,9.209139 14.209139,11 12,11 Z"
                                    id="Mask" fill="#000000" fill-rule="nonzero" opacity="0.3"></path>
                                <path
                                    d="M3.00065168,20.1992055 C3.38825852,15.4265159 7.26191235,13 11.9833413,13 C16.7712164,13 20.7048837,15.2931929 20.9979143,20.2 C21.0095879,20.3954741 20.9979143,21 20.2466999,21 C16.541124,21 11.0347247,21 3.72750223,21 C3.47671215,21 2.97953825,20.45918 3.00065168,20.1992055 Z"
                                    id="Mask-Copy" fill="#000000" fill-rule="nonzero"></path>
                            </g>
                        </svg>
                    </span>
                    <span class="menu-text">{{ __('Users') }}</span>
                </a>
            </li>
            <!--end::User-->
            <!--begin::Address-->
            <li class="menu-item {{ Request::is('*/admin/address*') ? 'menu-item-active' : '' }}" aria-haspopup="true">
                <a href="{{ route('address.index', app()->getlocale() ) }}" class="menu-link">
                    <span class="svg-icon menu-icon">
                        <svg width="24px" height="24px" viewBox="0 0 24 24" version="1.1"
                            xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <!-- Generator: Sketch 50.2 (55047) - http://www.bohemiancoding.com/sketch -->
                            <title>Stockholm-icons / Communication / Adress-book2</title>
                            <desc>Created with Sketch.</desc>
                            <defs></defs>
                            <g id="Stockholm-icons-/-Communication-/-Adress-book2" stroke="none" stroke-width="1"
                                fill="none" fill-rule="evenodd">
                                <rect id="bound" x="0" y="0" width="24" height="24"></rect>
                                <path
                                    d="M18,2 L20,2 C21.6568542,2 23,3.34314575 23,5 L23,19 C23,20.6568542 21.6568542,22 20,22 L18,22 L18,2 Z"
                                    id="Rectangle-161-Copy" fill="#000000" opacity="0.3"></path>
                                <path
                                    d="M5,2 L17,2 C18.6568542,2 20,3.34314575 20,5 L20,19 C20,20.6568542 18.6568542,22 17,22 L5,22 C4.44771525,22 4,21.5522847 4,21 L4,3 C4,2.44771525 4.44771525,2 5,2 Z M12,11 C13.1045695,11 14,10.1045695 14,9 C14,7.8954305 13.1045695,7 12,7 C10.8954305,7 10,7.8954305 10,9 C10,10.1045695 10.8954305,11 12,11 Z M7.00036205,16.4995035 C6.98863236,16.6619875 7.26484009,17 7.4041679,17 C11.463736,17 14.5228466,17 16.5815,17 C16.9988413,17 17.0053266,16.6221713 16.9988413,16.5 C16.8360465,13.4332455 14.6506758,12 11.9907452,12 C9.36772908,12 7.21569918,13.5165724 7.00036205,16.4995035 Z"
                                    id="Combined-Shape" fill="#000000"></path>
                            </g>
                        </svg>
                    </span>
                    <span class="menu-text">{{ __('Address') }}</span>
                </a>
            </li>
            <!--end::Address-->
            <!--begin::Regions-->
            <li class="menu-item {{ Request::is('*/admin/region*') ? 'menu-item-active' : '' }}" aria-haspopup="true">
                <a href="{{ route('region.index', app()->getlocale() ) }}" class="menu-link">
                    <span class="svg-icon menu-icon">
                        <svg width="24px" height="24px" viewBox="0 0 24 24" version="1.1"
                            xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <g id="Stockholm-icons-/-Home-/-Globe" stroke="none" stroke-width="1" fill="none"
                                fill-rule="evenodd">
                                <rect id="bound" x="0" y="0" width="24" height="24"></rect>
                                <path
                                    d="M13,18.9450712 L13,20 L14,20 C15.1045695,20 16,20.8954305 16,22 L8,22 C8,20.8954305 8.8954305,20 10,20 L11,20 L11,18.9448245 C9.02872877,18.7261967 7.20827378,17.866394 5.79372555,16.5182701 L4.73856106,17.6741866 C4.36621808,18.0820826 3.73370941,18.110904 3.32581341,17.7385611 C2.9179174,17.3662181 2.88909597,16.7337094 3.26143894,16.3258134 L5.04940685,14.367122 C5.46150313,13.9156769 6.17860937,13.9363085 6.56406875,14.4106998 C7.88623094,16.037907 9.86320756,17 12,17 C15.8659932,17 19,13.8659932 19,10 C19,7.73468744 17.9175842,5.65198725 16.1214335,4.34123851 C15.6753081,4.01567657 15.5775721,3.39010038 15.903134,2.94397499 C16.228696,2.49784959 16.8542722,2.4001136 17.3003976,2.72567554 C19.6071362,4.40902808 21,7.08906798 21,10 C21,14.6325537 17.4999505,18.4476269 13,18.9450712 Z"
                                    id="Combined-Shape" fill="#000000" fill-rule="nonzero"></path>
                                <circle id="Oval" fill="#000000" opacity="0.3" cx="12" cy="10" r="6"></circle>
                            </g>
                        </svg>
                    </span>
                    <span class="menu-text">{{ __('Regions') }}</span>
                </a>
            </li>
            <!--end::Regions-->
            <!--begin::Shops-->
            <li class="menu-item {{ Request::is('*/admin/shop*') ? 'menu-item-active' : '' }}" aria-haspopup="true">
                <a href="{{ route('shop.index', app()->getlocale() ) }}" class="menu-link">
                    <span class="svg-icon menu-icon">
                        <svg width="24px" height="24px" viewBox="0 0 24 24" version="1.1"
                            xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <!-- Generator: Sketch 50.2 (55047) - http://www.bohemiancoding.com/sketch -->
                            <title>Stockholm-icons / Shopping / Cart2</title>
                            <desc>Created with Sketch.</desc>
                            <defs></defs>
                            <g id="Stockholm-icons-/-Shopping-/-Cart2" stroke="none" stroke-width="1" fill="none"
                                fill-rule="evenodd">
                                <rect id="bound" x="0" y="0" width="24" height="24"></rect>
                                <path
                                    d="M12,4.56204994 L7.76822128,9.6401844 C7.4146572,10.0644613 6.7840925,10.1217854 6.3598156,9.76822128 C5.9355387,9.4146572 5.87821464,8.7840925 6.23177872,8.3598156 L11.2317787,2.3598156 C11.6315738,1.88006147 12.3684262,1.88006147 12.7682213,2.3598156 L17.7682213,8.3598156 C18.1217854,8.7840925 18.0644613,9.4146572 17.6401844,9.76822128 C17.2159075,10.1217854 16.5853428,10.0644613 16.2317787,9.6401844 L12,4.56204994 Z"
                                    id="Path-30" fill="#000000" fill-rule="nonzero" opacity="0.3"></path>
                                <path
                                    d="M3.28077641,9 L20.7192236,9 C21.2715083,9 21.7192236,9.44771525 21.7192236,10 C21.7192236,10.0817618 21.7091962,10.163215 21.6893661,10.2425356 L19.5680983,18.7276069 C19.234223,20.0631079 18.0342737,21 16.6576708,21 L7.34232922,21 C5.96572629,21 4.76577697,20.0631079 4.43190172,18.7276069 L2.31063391,10.2425356 C2.17668518,9.70674072 2.50244587,9.16380623 3.03824078,9.0298575 C3.11756139,9.01002735 3.1990146,9 3.28077641,9 Z M12,12 C11.4477153,12 11,12.4477153 11,13 L11,17 C11,17.5522847 11.4477153,18 12,18 C12.5522847,18 13,17.5522847 13,17 L13,13 C13,12.4477153 12.5522847,12 12,12 Z M6.96472382,12.1362967 C6.43125772,12.2792385 6.11467523,12.8275755 6.25761704,13.3610416 L7.29289322,17.2247449 C7.43583503,17.758211 7.98417199,18.0747935 8.51763809,17.9318517 C9.05110419,17.7889098 9.36768668,17.2405729 9.22474487,16.7071068 L8.18946869,12.8434035 C8.04652688,12.3099374 7.49818992,11.9933549 6.96472382,12.1362967 Z M17.0352762,12.1362967 C16.5018101,11.9933549 15.9534731,12.3099374 15.8105313,12.8434035 L14.7752551,16.7071068 C14.6323133,17.2405729 14.9488958,17.7889098 15.4823619,17.9318517 C16.015828,18.0747935 16.564165,17.758211 16.7071068,17.2247449 L17.742383,13.3610416 C17.8853248,12.8275755 17.5687423,12.2792385 17.0352762,12.1362967 Z"
                                    id="Combined-Shape" fill="#000000"></path>
                            </g>
                        </svg>
                    </span>
                    <span class="menu-text">{{ __('Shops') }}</span>
                </a>
            </li>
            <!--end::Shops-->
            <!--begin::Products-->
            <li class="menu-item {{ Request::is('*/admin/product*') ? 'menu-item-active' : '' }}" aria-haspopup="true">
                <a href="{{ route('product.index', app()->getlocale() ) }}" class="menu-link">
                    <span class="svg-icon menu-icon">
                        <svg width="24px" height="24px" viewBox="0 0 24 24" version="1.1"
                            xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <!-- Generator: Sketch 50.2 (55047) - http://www.bohemiancoding.com/sketch -->
                            <title>Stockholm-icons / Home / Flower3</title>
                            <desc>Created with Sketch.</desc>
                            <defs></defs>
                            <g id="Stockholm-icons-/-Home-/-Flower3" stroke="none" stroke-width="1" fill="none"
                                fill-rule="evenodd">
                                <polygon id="bound" points="0 0 24 0 24 24 0 24"></polygon>
                                <path
                                    d="M1.4152146,4.84010415 C11.1782334,10.3362599 14.7076452,16.4493804 12.0034499,23.1794656 C5.02500006,22.0396582 1.4955883,15.9265377 1.4152146,4.84010415 Z"
                                    id="Path-36-Copy-2" fill="#000000" opacity="0.3"></path>
                                <path
                                    d="M22.5950046,4.84010415 C12.8319858,10.3362599 9.30257403,16.4493804 12.0067693,23.1794656 C18.9852192,22.0396582 22.5146309,15.9265377 22.5950046,4.84010415 Z"
                                    id="Path-36-Copy-3" fill="#000000" opacity="0.3"></path>
                                <path
                                    d="M12.0002081,2 C6.29326368,11.6413199 6.29326368,18.7001435 12.0002081,23.1764706 C17.4738192,18.7001435 17.4738192,11.6413199 12.0002081,2 Z"
                                    id="Path-36" fill="#000000" opacity="0.3"></path>
                            </g>
                        </svg>
                    </span>
                    <span class="menu-text">{{ __('Products') }}</span>
                </a>
            </li>
            <!--end::Products-->
            <!--begin::Message-->
            <li class="menu-item {{ Request::is('*/admin/message*') ? 'menu-item-active' : '' }}" aria-haspopup="true">
                <a href="{{ route('message.index', app()->getlocale() ) }}" class="menu-link">
                    <span class="svg-icon menu-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path opacity="0.25"
                                d="M1 6C1 4.34315 2.34315 3 4 3H20C21.6569 3 23 4.34315 23 6V18C23 19.6569 21.6569 21 20 21H4C2.34315 21 1 19.6569 1 18V6Z"
                                fill="#FFFFFF" />
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M5.23177 7.35984C5.58534 6.93556 6.2159 6.87824 6.64018 7.2318L11.3598 11.1648C11.7307 11.4739 12.2693 11.4739 12.6402 11.1648L17.3598 7.2318C17.7841 6.87824 18.4147 6.93556 18.7682 7.35984C19.1218 7.78412 19.0645 8.41468 18.6402 8.76825L13.9205 12.7013C12.808 13.6284 11.192 13.6284 10.0794 12.7013L5.35981 8.76825C4.93553 8.41468 4.87821 7.78412 5.23177 7.35984Z"
                                fill="#121319" />
                        </svg>
                    </span>
                    <span class="menu-text">{{ __('Messages') }}</span>
                </a>
            </li>
            <!--end::Message-->
            <!--begin::Roles-->
            <li class="menu-item {{ Request::is('*/admin/role*') ? 'menu-item-active' : '' }}" aria-haspopup="true">
                <a href="{{ route('role.index', app()->getlocale() ) }}" class="menu-link">
                    <span class="svg-icon menu-icon">
                        <svg width="24px" height="24px" viewBox="0 0 24 24" version="1.1"
                            xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <!-- Generator: Sketch 50.2 (55047) - http://www.bohemiancoding.com/sketch -->
                            <title>Stockholm-icons / General / Settings-2</title>
                            <desc>Created with Sketch.</desc>
                            <defs></defs>
                            <g id="Stockholm-icons-/-General-/-Settings-2" stroke="none" stroke-width="1" fill="none"
                                fill-rule="evenodd">
                                <rect id="bound" x="0" y="0" width="24" height="24"></rect>
                                <path
                                    d="M5,8.6862915 L5,5 L8.6862915,5 L11.5857864,2.10050506 L14.4852814,5 L19,5 L19,9.51471863 L21.4852814,12 L19,14.4852814 L19,19 L14.4852814,19 L11.5857864,21.8994949 L8.6862915,19 L5,19 L5,15.3137085 L1.6862915,12 L5,8.6862915 Z M12,15 C13.6568542,15 15,13.6568542 15,12 C15,10.3431458 13.6568542,9 12,9 C10.3431458,9 9,10.3431458 9,12 C9,13.6568542 10.3431458,15 12,15 Z"
                                    id="Combined-Shape" fill="#000000"></path>
                            </g>
                        </svg>
                    </span>
                    <span class="menu-text">{{ __('Roles') }}</span>
                </a>
            </li>
            <!--end::Roles-->
            <!--begin::Permissions-->
            <li class="menu-item {{ Request::is('*/admin/permission*') ? 'menu-item-active' : '' }}"
                aria-haspopup="true">
                <a href="{{ route('permission.index', app()->getlocale() ) }}" class="menu-link">
                    <span class="svg-icon menu-icon">
                        <svg width="24px" height="24px" viewBox="0 0 24 24" version="1.1"
                            xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <g id="Stockholm-icons-/-Home-/-Key" stroke="none" stroke-width="1" fill="none"
                                fill-rule="evenodd">
                                <rect id="bound" x="0" y="0" width="24" height="24"></rect>
                                <polygon id="Path-59" fill="#000000" opacity="0.3"
                                    transform="translate(8.885842, 16.114158) rotate(-315.000000) translate(-8.885842, -16.114158) "
                                    points="6.89784488 10.6187476 6.76452164 19.4882481 8.88584198 21.6095684 11.0071623 19.4882481 9.59294876 18.0740345 10.9659914 16.7009919 9.55177787 15.2867783 11.0071623 13.8313939 10.8837471 10.6187476">
                                </polygon>
                                <path
                                    d="M15.9852814,14.9852814 C12.6715729,14.9852814 9.98528137,12.2989899 9.98528137,8.98528137 C9.98528137,5.67157288 12.6715729,2.98528137 15.9852814,2.98528137 C19.2989899,2.98528137 21.9852814,5.67157288 21.9852814,8.98528137 C21.9852814,12.2989899 19.2989899,14.9852814 15.9852814,14.9852814 Z M16.1776695,9.07106781 C17.0060967,9.07106781 17.6776695,8.39949494 17.6776695,7.57106781 C17.6776695,6.74264069 17.0060967,6.07106781 16.1776695,6.07106781 C15.3492424,6.07106781 14.6776695,6.74264069 14.6776695,7.57106781 C14.6776695,8.39949494 15.3492424,9.07106781 16.1776695,9.07106781 Z"
                                    id="Combined-Shape" fill="#000000"
                                    transform="translate(15.985281, 8.985281) rotate(-315.000000) translate(-15.985281, -8.985281) ">
                                </path>
                            </g>
                        </svg>
                    </span>
                    <span class="menu-text">{{ __('Permissions') }}</span>
                </a>
            </li>
            <!--end::Permissions-->
            <!--begin::Admin-->
            <li class="menu-item {{ Request::is('*/admin/admin*') ? 'menu-item-active' : '' }}" aria-haspopup="true">
                <a href="{{ route('admin.index', app()->getlocale() ) }}" class="menu-link">
                    <span class="svg-icon menu-icon">
                        <svg width="24px" height="24px" viewBox="0 0 24 24" version="1.1"
                            xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <!-- Generator: Sketch 50.2 (55047) - http://www.bohemiancoding.com/sketch -->
                            <title>Stockholm-icons / Communication / Shield-user</title>
                            <desc>Created with Sketch.</desc>
                            <defs></defs>
                            <g id="Stockholm-icons-/-Communication-/-Shield-user" stroke="none" stroke-width="1"
                                fill="none" fill-rule="evenodd">
                                <rect id="bound" x="0" y="0" width="24" height="24"></rect>
                                <path
                                    d="M4,4 L11.6314229,2.5691082 C11.8750185,2.52343403 12.1249815,2.52343403 12.3685771,2.5691082 L20,4 L20,13.2830094 C20,16.2173861 18.4883464,18.9447835 16,20.5 L12.5299989,22.6687507 C12.2057287,22.8714196 11.7942713,22.8714196 11.4700011,22.6687507 L8,20.5 C5.51165358,18.9447835 4,16.2173861 4,13.2830094 L4,4 Z"
                                    id="Path-50" fill="#000000" opacity="0.3"></path>
                                <path
                                    d="M12,11 C10.8954305,11 10,10.1045695 10,9 C10,7.8954305 10.8954305,7 12,7 C13.1045695,7 14,7.8954305 14,9 C14,10.1045695 13.1045695,11 12,11 Z"
                                    id="Mask" fill="#000000" opacity="0.3"></path>
                                <path
                                    d="M7.00036205,16.4995035 C7.21569918,13.5165724 9.36772908,12 11.9907452,12 C14.6506758,12 16.8360465,13.4332455 16.9988413,16.5 C17.0053266,16.6221713 16.9988413,17 16.5815,17 C14.5228466,17 11.463736,17 7.4041679,17 C7.26484009,17 6.98863236,16.6619875 7.00036205,16.4995035 Z"
                                    id="Mask-Copy" fill="#000000" opacity="0.3"></path>
                            </g>
                        </svg>
                    </span>
                    <span class="menu-text">{{ __('Admins') }}</span>
                </a>
            </li>
            <!--end::Admin-->
            <!--begin::Cart-->
            <li class="menu-item {{ Request::is('*/admin/cart*') ? 'menu-item-active' : '' }}" aria-haspopup="true">
                <a href="{{ route('cart.index', app()->getlocale() ) }}" class="menu-link">
                    <span class="svg-icon menu-icon">
                        <svg width="24px" height="24px" viewBox="0 0 24 24" version="1.1"
                            xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <g id="Stockholm-icons-/-Shopping-/-Cart1" stroke="none" stroke-width="1" fill="none"
                                fill-rule="evenodd">
                                <rect id="bound" x="0" y="0" width="24" height="24"></rect>
                                <path
                                    d="M18.1446364,11.84388 L17.4471627,16.0287218 C17.4463569,16.0335568 17.4455155,16.0383857 17.4446387,16.0432083 C17.345843,16.5865846 16.8252597,16.9469884 16.2818833,16.8481927 L4.91303792,14.7811299 C4.53842737,14.7130189 4.23500006,14.4380834 4.13039941,14.0719812 L2.30560137,7.68518803 C2.28007524,7.59584656 2.26712532,7.50338343 2.26712532,7.4104669 C2.26712532,6.85818215 2.71484057,6.4104669 3.26712532,6.4104669 L16.9929851,6.4104669 L17.606173,3.78251876 C17.7307772,3.24850086 18.2068633,2.87071314 18.7552257,2.87071314 L20.8200821,2.87071314 C21.4717328,2.87071314 22,3.39898039 22,4.05063106 C22,4.70228173 21.4717328,5.23054898 20.8200821,5.23054898 L19.6915238,5.23054898 L18.1446364,11.84388 Z"
                                    id="Combined-Shape" fill="#000000" opacity="0.3"></path>
                                <path
                                    d="M6.5,21 C5.67157288,21 5,20.3284271 5,19.5 C5,18.6715729 5.67157288,18 6.5,18 C7.32842712,18 8,18.6715729 8,19.5 C8,20.3284271 7.32842712,21 6.5,21 Z M15.5,21 C14.6715729,21 14,20.3284271 14,19.5 C14,18.6715729 14.6715729,18 15.5,18 C16.3284271,18 17,18.6715729 17,19.5 C17,20.3284271 16.3284271,21 15.5,21 Z"
                                    id="Combined-Shape" fill="#000000"></path>
                            </g>
                        </svg>
                    </span>
                    <span class="menu-text">{{ __('Cart') }}</span>
                </a>
            </li>
            <!--end::Cart-->
        </ul>
        <!--end::Menu Nav-->
    </div>
    <!--end::Menu Container-->
</div>
<!--end::Aside Menu-->
