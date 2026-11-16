@props(['status'])
@php
    $color = match ($status->value) {
        'pending' => 'warning',
        'accepted' => 'success',
        'rejected' => 'danger',
        'expired' => 'secondary',
        'cancelled' => 'dark',
    };
@endphp
<span class="badge text-bg-{{ $color }}">{{ $status->label() }}</span>