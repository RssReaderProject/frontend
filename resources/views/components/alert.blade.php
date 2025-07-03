@props(['type' => 'info', 'dismissible' => true])

@php
    $alertConfig = [
        'success' => [
            'class' => 'alert-success',
            'icon' => 'bi-check-circle',
            'title' => 'Success'
        ],
        'error' => [
            'class' => 'alert-danger',
            'icon' => 'bi-exclamation-triangle',
            'title' => 'Error'
        ],
        'warning' => [
            'class' => 'alert-warning',
            'icon' => 'bi-exclamation-triangle',
            'title' => 'Warning'
        ],
        'info' => [
            'class' => 'alert-info',
            'icon' => 'bi-info-circle',
            'title' => 'Information'
        ]
    ];
    
    $config = $alertConfig[$type] ?? $alertConfig['info'];
@endphp

@if(session($type))
    <div class="alert {{ $config['class'] }} {{ $dismissible ? 'alert-dismissible fade show' : '' }} shadow-sm" role="alert">
        <div class="d-flex align-items-center">
            <i class="{{ $config['icon'] }} me-2"></i>
            <div class="flex-grow-1">
                {{ session($type) }}
            </div>
        </div>
        @if($dismissible)
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        @endif
    </div>
@endif 