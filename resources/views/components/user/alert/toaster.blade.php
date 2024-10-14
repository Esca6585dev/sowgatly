<div class="toast" role="alert" aria-live="assertive" aria-atomic="true" id="showToast">
    <div class="toast-body">
      <div class="toast-text">{{ __('Are you sure you want to run this action?') }}</div>

      <div class="toast-btn">
        <a class="color-grey" data-bs-dismiss="toast" onclick="closeToast()">{{ __('No') }}</a>
        <a class="color-gold" onclick="logout()">{{ __('Yes') }}</a>
      </div>
    </div>
</div>
