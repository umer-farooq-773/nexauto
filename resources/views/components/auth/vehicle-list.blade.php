<div class="form-floating mb-3 select2-parent">
    <x-auth.select2 name="vehicle" id="vehicle" class="searchable-select" :data="$list" label="" placeholder="Select Vehicle" :existing-id="$existingId ?? null" />
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Modal detection
        var modalParent = $('#vehicle').closest('.modal');

        $('#vehicle').select2({
            placeholder: "Select Asset",
            allowClear: true,
            width: '100%',
            dropdownParent: modalParent.length ? modalParent : $(document.body)
        });

        // Agar select hone par koi specific action perform karna ho
        $('#vehicle').on('select2:select', function (e) {
            var data = e.params.data;
            console.log("Selected Vehicle ID: " + data.id);
        });
    });

    </script>
<style>

    /* 2. Select2 box ki height Bootstrap floating input (58px) jitni karein */
    .select2-container--default .select2-selection--single {
        height: 58px !important;
        padding-top: 17px !important; /* Label ke liye jagah */

        border-radius: 0.375rem !important;
        background-color: #fff !important;
    }

    /* 4. Select2 ke andar ke arrow (dropdown icon) ko center mein karein */
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 58px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #838383 !important;
        line-height: 28px;
    }

    .select2-container{
        font-weight: 400 !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: rgb(117, 117, 117);
    }

</style>
@endpush
