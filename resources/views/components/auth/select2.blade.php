@push('auth_styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

<div class=''>
    @if ($label != null)
    <label for="{{ $id }}" class="form-label">{{ $label }}</label>
    @endif

    <select class="{{$selectclass}} form-select @error($name)
    is-invalid
    @enderror" name="{{ $name }}" data-placeholder="{{ $placeholder }}" id="{{ $id }}" {{ $attributes }}>
        <option></option>

        @if ($loopData)
            @foreach ($loopData as $key => $item)

                @php
                    if (is_object($item)) {
                        $value = $item->id;
                        $text = $item->name;
                    } elseif (is_array($loopData) && !is_numeric($key)) {
                        $value = $key;
                        $text = $item;
                    } else {
                        $value = $item;
                        $text = $item;
                    }
                @endphp

                @php
                    $isSelected = is_array($existingId)
                        ? in_array($value, $existingId)
                        : $value == $existingId;
                @endphp
                <option value="{{ $value }}" {{ $isSelected ? 'selected' : '' }}>
                    {{ $text }}
                </option>

            @endforeach
        @endif
    </select>

    @if ($errors->has($name))
    <span for="{{ $id }}" class="text-danger">{{ $errors->first($name) }}</span>
    @endif
</div>

@push('auth_scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    $('#{{ $id }}').select2({
        dropdownParent: $('#{{ $id }}').parent(), // ensures dropdown stays inside floating label
        placeholder: $('#{{ $id }}').data('placeholder'),
        allowClear: true,
        theme: "classic",
        width: '100%',
        minimumResultsForSearch: Infinity // optional: hides search
    });

    // Optional: show all options fully on click
    $('#{{ $id }}').on('select2:open', function() {
        $('.select2-dropdown').css('max-height', 'none');
    });
});
</script>

@endpush
