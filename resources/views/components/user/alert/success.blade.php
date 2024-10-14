<div class="alert success">
    <span class="closebtn">&times;</span>
    <strong>Standart sebede goşuldy</strong>
</div>

@if(session()->has('success-order'))
<div class="alert alert-success" style="display: block">
  <span class="closebtn">&times;</span>
  <strong>{{ __(session()->get('success-order')) }}</strong>
</div>
@endif
