@props(['title', 'description', 'action' => null, 'actionText' => null, 'actionUrl' => null, 'icon' => 'bi-inbox'])

<div class="card shadow-sm border-0">
    <div class="card-body text-center py-5">
        <div class="mb-3">
            <i class="{{ $icon }} text-muted" style="font-size: 3rem;"></i>
        </div>
        <h5 class="card-title text-muted mb-2">{{ $title }}</h5>
        <p class="card-text text-muted mb-4">{{ $description }}</p>
        @if($action)
            {{ $action }}
        @elseif($actionText && $actionUrl)
            <a href="{{ $actionUrl }}" class="btn btn-primary">
                {{ $actionText }}
            </a>
        @endif
    </div>
</div> 