@props(['name', 'label', 'options', 'selected' => null, 'placeholder' => null, 'required' => false])

<div class="mb-3">
    <label for="{{ $name }}" class="form-label fw-medium">
        {{ $label }}
        @if($required)
            <span class="text-danger">*</span>
        @endif
    </label>
    <select name="{{ $name }}" 
            id="{{ $name }}" 
            class="form-select @error($name) is-invalid @enderror"
            @if($required) required @endif
            {{ $attributes }}>
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach($options as $value => $label)
            <option value="{{ $value }}" {{ ($selected ?? old($name)) == $value ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @endforeach
    </select>
    @error($name)
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror
</div> 