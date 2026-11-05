@props([
    'type' => 'text',
    'name' => '',
    'id' => $name,
    'required' => false,
    'place' => '',
    'val' => '',
    'extraclasses' => '',
    'label' => null,
    'readonly' => false,
    'disabled' => false,
])

<div class='required field form-floating'>
    @php
        $isRequired = filter_var($required, FILTER_VALIDATE_BOOLEAN);
    @endphp

    <input type='{{ $type }}' class='form-control {{ $extraclasses }} @error($name)
    is-invalid
    @enderror' id='{{ $id }}' name='{{ $name }}' placeholder='{{ $place }}' {{ $isRequired ? 'required' : '' }}
        {{ filter_var($readonly, FILTER_VALIDATE_BOOLEAN) ? 'readonly' : '' }}
        {{ filter_var($disabled, FILTER_VALIDATE_BOOLEAN) ? 'disabled' : '' }}
        value='{{ $val == '' ? old($name) : $val }}' autocomplete="off">

    @if ($label != null)
    <label for="{{ $id }}" class="form-label">
        {{ $label }}

        @if ($isRequired)
        <span style="color: red;">*</span>
        @endif
    </label>
    @endif

    @if ($errors->has($name))
    <span for="{{ $id }}" class="text-danger" style="font-size: 12px !important;">{{ $errors->first($name) }}</span>
    @endif
</div>
