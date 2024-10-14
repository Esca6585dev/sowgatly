@if(Auth::check())
<x-user.option-auth :id="$id" />
@else
<x-user.option-noauth :id="$id" />
@endif