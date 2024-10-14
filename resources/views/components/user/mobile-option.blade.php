@if(Auth::check())
<x-user.mobile-option-auth />
@else
<x-user.mobile-option-noauth />
@endif