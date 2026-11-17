@props(['label', 'value', 'icon' => 'bi-circle', 'cols' => 'col-12 col-md-4'])
<div class="{{ $cols }}">
    <div class="stat-card">
        <div class="stat-icon"><i class="bi {{ $icon }}"></i></div>
        <div>
            <div class="stat-value">{{ $value }}</div>
            <div class="stat-label">{{ $label }}</div>
        </div>
    </div>
</div>