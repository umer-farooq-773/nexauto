@push('auth_styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

<div class=''>
    @if ($label)
    <label for="{{ $id }}" class="form-label">{{ $label }}</label>
    @endif

    <select class="{{ $selectclass }} form-select @error($name) is-invalid @enderror" name="{{ $name }}[]" data-placeholder="{{ $placeholder }}" id="{{ $id }}" multiple>
        @if ($loopData)
            @foreach ($loopData as $key => $item)

                @php
                    if (is_object($item)) {
                        $value = $item->id;
                        $text = $item->name ?? $item->title ?? $value;
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
<script>
$(document).ready(function() {
    var selectElement = $('#{{ $id }}');
    var modalParent = selectElement.closest('.modal');
    
    // Destroy existing Select2 if it exists
    if (selectElement.data('select2')) {
        selectElement.select2('destroy');
    }
    
    selectElement.select2({
        dropdownParent: modalParent.length ? modalParent : $(document.body),
        placeholder: selectElement.data('placeholder') || 'Select options',
        allowClear: true,
        theme: "classic",
        width: '100%',
        closeOnSelect: false
    });
    
    // Reinitialize when modal is shown
    if (modalParent.length) {
        modalParent.on('shown.bs.modal', function() {
            selectElement.select2('destroy');
            selectElement.select2({
                dropdownParent: modalParent,
                placeholder: selectElement.data('placeholder') || 'Select options',
                allowClear: true,
                theme: "classic",
                width: '100%',
                closeOnSelect: false
            });
        });
    }
});
</script>

@endpush
