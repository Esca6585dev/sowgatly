@props(['icon' => 'inbox', 'text' => null])
<div class="empty"><x-admin.icon :name="$icon" /><div>{{ $text ?? __('Nothing here yet') }}</div>{{ $slot }}</div>
