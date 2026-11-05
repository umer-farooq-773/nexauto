<x-auth.form form-id="DriverMateForm" form-action="{{ $route }}" enctype="true">
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row g-3">
        <div class="col-md-6">
            <x-auth.input-field type="text" name="first_name" id="first_name" required="true" place="John" val="{{ $result?->first_name }}" extraclasses="" label="First Name" />
        </div>

        <div class="col-md-6">
            <x-auth.input-field type="text" name="last_name" id="last_name" required="true" place="Doe" val="{{ $result?->last_name }}" extraclasses="" label="Last Name" />
        </div>

        <div class="col-md-6">
            <x-auth.input-field type="tel" name="phone_number" id="phone_number" required="true" place="+92 300 1234567" val="{{ $result?->phone_number }}" extraclasses="" label="Phone Number" />
        </div>

        <div class="col-md-6">
            <x-auth.input-field type="date" name="date_of_birth" id="date_of_birth" required="false" place="" val="{{ $result?->date_of_birth }}" extraclasses="" label="Date of Birth" />
        </div>

        <div class="col-md-6">
            <x-auth.input-field type="text" name="national_id_passport_number" id="national_id_passport_number" required="true" place="42101-1234567-1" val="{{ $result?->national_id_passport_number }}" extraclasses="" label="ID / Passport" />
        </div>

        <div class="col-md-6">
            <x-auth.input-field type="date" name="date_employed" id="date_employed" required="false" place="" val="{{ $result?->date_employed }}" extraclasses="" label="Date Employed" />
        </div>

        <div class="col-md-12">
            @php
                $selectedDriverId = old('driver_id', $result?->drivers?->first()?->id);
            @endphp
            <div class="mb-3">
                <label class="form-label">Assign Driver</label>
                <select name="driver_id" class="form-select" required>
                    <option value="">Select Driver</option>
                    @foreach($drivers as $driver)
                        <option value="{{ $driver->id }}" {{ $selectedDriverId == $driver->id ? 'selected' : '' }}>
                            {{ $driver->full_name }} - {{ $driver->phone_number }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-md-6">
            <x-auth.input-field type="text" name="emergency_contact_name" id="emergency_contact_name" required="false" place="Jane Doe" val="{{ $result?->emergency_contact_name }}" extraclasses="" label="Emergency Contact" />
        </div>

        <div class="col-md-6">
            <x-auth.input-field type="tel" name="emergency_contact_phone_number" id="emergency_contact_phone_number" required="false" place="+92 321 7654321" val="{{ $result?->emergency_contact_phone_number }}" extraclasses="" label="Emergency Phone" />
        </div>

        <div class="col-md-6">
            <label class="form-label">Mate Photograph</label>
            <x-auth.upload-file image="{{ $result?->image() ?? asset('assets/img/user-img.png') }}" name="mate_profile" uid="mate_profile" />
        </div>

        <div class="col-md-6">
            <label class="form-label">Company ID Card</label>
            <x-auth.upload-file image="{{ $result?->fileUrl('company_id_card') ?? asset('assets/img/com-id-img.png') }}" name="company_id_card" uid="company_id_card" />
        </div>

        <div class="col-md-6">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="1" {{ ($result?->status ?? 1) == 1 ? 'selected' : '' }}>Active</option>
                <option value="0" {{ ($result?->status ?? 1) == 0 ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <div class="col-md-12">
            <x-auth.text-area name="address" id="address" required="true" place="Plot 45, Street 10, Karachi" val="{{ $result?->address }}" extraclasses="" label="Address" />
        </div>

        <div class="col-12 text-center mt-3">
            <button type="submit" class="btn btn-primary px-5 py-2">Submit</button>
        </div>
    </div>
</x-auth.form>
