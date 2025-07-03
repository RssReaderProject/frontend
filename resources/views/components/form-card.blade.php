@props(['title' => null, 'subtitle' => null])

<div class="card shadow-sm border-0">
    @if($title || $subtitle)
        <div class="card-header bg-light border-0">
            @if($title)
                <h5 class="mb-1 fw-semibold">{{ $title }}</h5>
            @endif
            @if($subtitle)
                <p class="text-muted mb-0 small">{{ $subtitle }}</p>
            @endif
        </div>
    @endif
    <div class="card-body">
        {{ $slot }}
    </div>
</div> 