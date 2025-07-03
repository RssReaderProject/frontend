@props(['name', 'label', 'type' => 'text', 'placeholder' => null, 'required' => false, 'value' => null])

<div class="mb-3">
    <label for="{{ $name }}" class="form-label fw-medium">
        {{ $label }}
        @if($required)
            <span class="text-danger">*</span>
        @endif
    </label>
    <input type="{{ $type }}" 
           class="form-control @error($name) is-invalid @enderror"
           name="{{ $name }}" 
           id="{{ $name }}" 
           value="{{ $value ?? old($name) }}"
           placeholder="{{ $placeholder }}"
           @if($required) required @endif
           {{ $attributes }}>
    @error($name)
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror
</div> 