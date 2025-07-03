@props(['title' => 'Filters', 'clearUrl' => null])

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-light border-0">
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-funnel me-2"></i>{{ $title }}
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ request()->url() }}" class="row g-3">
            {{ $slot }}
            
            <div class="col-12 d-flex gap-2 justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search me-1"></i> Apply Filters
                </button>
                @if($clearUrl)
                    <a href="{{ $clearUrl }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i> Clear
                    </a>
                @endif
            </div>
        </form>
    </div>
</div> 