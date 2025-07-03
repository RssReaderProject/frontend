@props(['status', 'failures' => 0, 'lastFailure' => null])

@php
    $statusConfig = [
        'active' => ['class' => 'bg-success', 'text' => 'Active', 'icon' => 'bi-check-circle'],
        'cooldown' => ['class' => 'bg-warning', 'text' => 'Cooldown', 'icon' => 'bi-clock'],
        'disabled' => ['class' => 'bg-danger', 'text' => 'Disabled', 'icon' => 'bi-x-circle'],
        'failures' => ['class' => 'bg-warning', 'text' => $failures . ' failures', 'icon' => 'bi-exclamation-triangle'],
    ];
    
    $config = $statusConfig[$status] ?? $statusConfig['active'];
@endphp

<span class="badge {{ $config['class'] }} d-inline-flex align-items-center gap-1">
    <i class="{{ $config['icon'] }}"></i>
    {{ $config['text'] }}
</span>

@if($lastFailure && $failures > 0)
    <br><small class="text-muted">{{ $lastFailure->diffForHumans() }}</small>
@endif 