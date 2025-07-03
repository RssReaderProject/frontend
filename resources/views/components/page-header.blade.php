@props(['title', 'subtitle' => null])

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-dark">{{ $title }}</h1>
        @if($subtitle)
            <p class="text-muted mb-0">{{ $subtitle }}</p>
        @endif
    </div>
    @if($slot->isNotEmpty())
        <div class="d-flex gap-2">
            {{ $slot }}
        </div>
    @endif
</div> 