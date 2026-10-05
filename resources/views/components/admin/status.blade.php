{{-- Order / application / shop status pill with a consistent colour per value. --}}
@props(['value'])
@php
$tones = [
  'pending' => 'warn', 'processing' => 'violet', 'delivering' => 'info', 'completed' => 'ok', 'cancelled' => 'bad',
  'new' => 'warn', 'contacted' => 'violet', 'approved' => 'ok', 'rejected' => 'bad',
  'unpaid' => null, 'paid' => 'ok', 'refunded' => 'warn',
  'active' => 'ok', 'inactive' => null, '1' => 'ok', '0' => null,
];
$labels = ['1' => __('Active'), '0' => __('Inactive')];
$key = is_bool($value) ? ($value ? '1' : '0') : (string) $value;
@endphp
<x-admin.pill :tone="$tones[$key] ?? null" dot>{{ $labels[$key] ?? __(ucfirst($key)) }}</x-admin.pill>
