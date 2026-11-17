@props(['item'])
<img src="{{ $item->image_url }}"
     alt="{{ $item->name }}"
     loading="lazy"
     decoding="async"
     onerror="this.onerror=null;this.src='{{ asset('images/food-fallback.svg') }}';"
     {{ $attributes->merge(['class' => 'food-img']) }}>