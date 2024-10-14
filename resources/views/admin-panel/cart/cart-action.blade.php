<span class="dtr-data d-flex">
    <a href="{{ route(Request::segment(3) . '.show', [ app()->getlocale(), $cart->id ] ) }}"
        class="btn btn-sm btn-clean btn-icon mr-2" title="{{ __('View') }}">
        <span class="svg-icon svg-icon-xl svg-icon-primary">
            <svg width="24px" height="24px" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg"
                xmlns:xlink="http://www.w3.org/1999/xlink">
                <g id="Stockholm-icons-/-General-/-Visible" stroke="none" stroke-width="1" fill="none"
                    fill-rule="evenodd">
                    <rect id="bound" x="0" y="0" width="24" height="24"></rect>
                    <path
                        d="M3,12 C3,12 5.45454545,6 12,6 C16.9090909,6 21,12 21,12 C21,12 16.9090909,18 12,18 C5.45454545,18 3,12 3,12 Z"
                        id="Shape" fill="#000000" fill-rule="nonzero" opacity="0.4"></path>
                    <path
                        d="M12,15 C10.3431458,15 9,13.6568542 9,12 C9,10.3431458 10.3431458,9 12,9 C13.6568542,9 15,10.3431458 15,12 C15,13.6568542 13.6568542,15 12,15 Z"
                        id="Path" fill="#000000" opacity="1"></path>
                </g>
            </svg>
        </span>
    </a>

    <form action="{{ route(Request::segment(3) . '.destroy', [ app()->getlocale(), $cart->id ] ) }}"
        method="post">
        @method('delete')
        @csrf

        <button type="button" class="btn btn-sm btn-clean btn-icon" title="{{ __('Delete') }}" data-toggle="modal"
            data-target="#exampleModalSizeSm-{{$cart->id}}">
            <span class="svg-icon svg-icon-md svg-icon-danger">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                    height="24px" viewBox="0 0 24 24" version="1.1">
                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                        <rect x="0" y="0" width="24" height="24"></rect>
                        <path
                            d="M6,8 L6,20.5 C6,21.3284271 6.67157288,22 7.5,22 L16.5,22 C17.3284271,22 18,21.3284271 18,20.5 L18,8 L6,8 Z"
                            fill="#000000" fill-rule="nonzero">
                        </path>
                        <path
                            d="M14,4.5 L14,4 C14,3.44771525 13.5522847,3 13,3 L11,3 C10.4477153,3 10,3.44771525 10,4 L10,4.5 L5.5,4.5 C5.22385763,4.5 5,4.72385763 5,5 L5,5.5 C5,5.77614237 
                            5.22385763,6 5.5,6 L18.5,6 C18.7761424,6 19,5.77614237 19,5.5 L19,5 C19,4.72385763 18.7761424,4.5 18.5,4.5 L14,4.5 Z"
                            fill="#000000" opacity="0.3">
                        </path>
                    </g>
                </svg>
            </span>
        </button>

        <div class="example-preview">
            <!--begin::Modal-->
            <div class="modal fade" id="exampleModalSizeSm-{{$cart->id}}" tabindex="-1" aria-labelledby="exampleModalSizeSm-{{$cart->id}}"
                style="display: none;" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">{{ __('Warning') }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <i aria-hidden="true" class="ki ki-close"></i>
                            </button>
                        </div>
                        <div class="modal-body">{{ __('Are you sure you want to delete this resource?') }}</div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary font-weight-bold"
                                data-dismiss="modal">{{ __('Close') }}</button>

                            <button type="submit"
                                class="btn btn-light-danger font-weight-bold">{{ __('Delete') }}</button>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Modal-->
        </div>

    </form>
</span>
