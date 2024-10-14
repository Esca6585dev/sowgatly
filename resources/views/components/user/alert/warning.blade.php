<div class="alert warning">
    <span class="closebtn">&times;</span>
    <strong>Arza ugratmak üçin agza bolmaly!</strong>
</div>

@if(session()->has('warning'))
<div class="alert alert-warning" style="display: block">
    <span class="closebtn">&times;</span>
    <strong>{{ __(session()->get('warning')) }}</strong>
</div>
@endif
