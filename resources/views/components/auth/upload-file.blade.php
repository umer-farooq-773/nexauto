@php
    $uid      = $uid ?? $name ?? 'file';
    $fieldName = $name ?? 'file';
    // max size in KB (default 2048 KB = 2 MB), can be overridden via component attribute
    $maxKb    = $maxKb ?? 2048;
@endphp

<div>
    @if($label ?? '')
        <label class="small fw-bold mb-1">{{ $label }}
            @if($required ?? false)<span class="text-danger">*</span>@endif
        </label>
    @endif

    <div class="border rounded p-2 text-center position-relative @error($fieldName) border-danger @enderror"
         style="min-height:120px;">

        <img id="img_{{ $uid }}"
             src="{{ ($image ?? '') != '' ? $image : asset('assets/img/user-img.png') }}"
             onerror="this.onerror=null; this.src='{{ asset('assets/img/user-img.png') }}';"
             class="border shadow-sm mb-1"
             style="width:120px; height:120px; object-fit:cover; border-radius:50%">

        <label for="file_{{ $uid }}"
               class="d-block btn btn-sm btn-outline-primary mt-1"
               style="cursor:pointer;">
            <i class="fa fa-upload me-1"></i>{{ __('Choose File') }}
        </label>

        {{-- Hidden size-error message shown by JS before submit --}}
        <span id="size_err_{{ $uid }}" class="text-danger d-block mt-1" style="font-size:12px;display:none!important;"></span>

        <input type="file" id="file_{{ $uid }}" name="{{ $fieldName }}"
               accept="image/*" style="display:none;"
               data-max-kb="{{ $maxKb }}"
               onchange="previewUpload('{{ $uid }}', this, {{ $maxKb }})">
    </div>

    {{-- Laravel server-side validation error --}}
    @error($fieldName)
        <span class="text-danger d-block mt-1" style="font-size:12px;">{{ $message }}</span>
    @enderror

    <small class="text-muted">{{ __('Accepted: JPG, JPEG, PNG · Max: ') }}{{ round($maxKb / 1024, 1) }} MB</small>
</div>

<script>
function previewUpload(uid, input, maxKb) {
    const file = input.files[0];
    const errEl = document.getElementById('size_err_' + uid);
    errEl.style.display = 'none';
    errEl.textContent   = '';

    if (!file) return;

    // Client-side size guard
    const fileSizeKb = file.size / 1024;
    if (fileSizeKb > maxKb) {
        const maxMb = (maxKb / 1024).toFixed(1);
        errEl.textContent   = `The image must not be greater than ${maxKb} KB (${maxMb} MB).`;
        errEl.style.display = 'block';
        input.value = '';   // clear the input so the oversized file is not submitted
        // Reset preview back to original
        document.getElementById('img_' + uid).src = document.getElementById('img_' + uid).dataset.original
            || document.getElementById('img_' + uid).src;
        return;
    }

    const reader = new FileReader();
    reader.onload = e => {
        const img = document.getElementById('img_' + uid);
        img.dataset.original = img.dataset.original || img.src; // remember original
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}
</script>
