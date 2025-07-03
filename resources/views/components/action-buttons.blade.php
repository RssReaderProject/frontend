@props(['viewUrl' => null, 'editUrl' => null, 'deleteUrl' => null, 'reEnableUrl' => null, 'showReEnable' => false])

<div class="btn-group" role="group">
    @if($viewUrl)
        <a href="{{ $viewUrl }}" class="btn btn-sm btn-outline-primary" title="View">
            <i class="bi bi-eye"></i>
        </a>
    @endif
    
    @if($editUrl)
        <a href="{{ $editUrl }}" class="btn btn-sm btn-outline-secondary" title="Edit">
            <i class="bi bi-pencil"></i>
        </a>
    @endif
    
    @if($showReEnable && $reEnableUrl)
        <form action="{{ $reEnableUrl }}" method="POST" class="d-inline">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-sm btn-outline-success" title="Re-enable">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
        </form>
    @endif
    
    @if($deleteUrl)
        <form action="{{ $deleteUrl }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger" 
                    onclick="return confirm('Are you sure you want to delete this item?')"
                    title="Delete">
                <i class="bi bi-trash"></i>
            </button>
        </form>
    @endif
</div> 