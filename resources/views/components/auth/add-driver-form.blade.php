<x-auth.form form-id="DriverForm" form-action="{{$route}}" enctype="true">
    @if($isEdit)
        @method('PUT')
    @endif

    @if($errors->any())
        <div class="col-12">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    <div class="col-md-6">
        <x-auth.input-field type="text" name="first_name" id="first_name" required="true" place="{{ __('John') }}" val="{{$result?->first_name}}" extraclasses="" label="{{ __('First Name') }}" />
    </div>

    <div class="col-md-6">
        <x-auth.input-field type="text" name="last_name" id="last_name" required="true" place="{{ __('Doe') }}" val="{{$result?->last_name}}" extraclasses="" label="{{ __('Last Name') }}" />
    </div>

    <div class="col-md-6">
        <x-auth.input-field type="tel" name="phone_number" id="phone_number" required="true" place="{{ __('+92 300 1234567') }}" val="{{$result?->phone_number}}" extraclasses="" label="{{ __('Phone Number') }}" />
    </div>

    <div class="col-md-6">
        <x-auth.input-field type="date" name="date_of_birth" id="date_of_birth" required="false" place="" val="{{$result?->date_of_birth}}" extraclasses="" label="{{ __('Date of Birth') }}" />
    </div>

    <div class="col-md-6">
        <x-auth.input-field type="text" name="national_id_passport_number" id="national_id_passport_number" required="true" place="{{ __('42101-1234567-1') }}" val="{{$result?->national_id_passport_number}}" extraclasses="" label="{{ __('NIN (National Identity Number)') }}" />
    </div>

    <div class="col-md-6">
        <x-auth.input-field type="text" name="bvn_number" id="bvn_number" required="true" place="{{ __('42101-1234567-1') }}" val="{{$result?->bvn_number}}" extraclasses="" label="{{ __('BVN (Bank Verification Number)') }}" />
    </div>

    <div class="col-md-6">
        <x-auth.input-field type="date" name="date_employed" id="date_employed" required="false" place="" val="{{$result?->date_employed}}" extraclasses="" label="{{ __('Date Employed') }}" />
    </div>

    <div class="col-md-6">
        <x-auth.input-field type="text" name="drivers_license_number" id="drivers_license_number" required="true" place="{{ __('DL-987654321') }}" val="{{$result?->drivers_license_number}}" extraclasses="" label="{{ __('Driver License Number') }}" />
    </div>

    <div class="col-md-6 form-floating">
        <x-auth.license-category-list :existing-val="$result?->license_category_class" />
    </div>

    <div class="col-md-6">
        <x-auth.input-field type="date" name="license_expiry_date" id="license_expiry_date" required="false" place="" val="{{$result?->license_expiry_date}}" extraclasses="" label="{{ __('License Expiry Date') }}" />
    </div>

    <div class="form-floating mb-3 col-md-6">
        <select name="status" id="status" class="form-select  shadow-sm">
            <option value="" selected disabled>Select Status</option>
            <option value="1" {{ $result?->status == 1 ? 'selected' : '' }}>{{ __('language.vehicle_status_1') }}</option>
            <option value="0" {{ $result?->status == 0 ? 'selected' : '' }}>{{ __('language.vehicle_status_0') }}</option>
        </select>
        <label for="status">Status</label>
    </div>

    <div class="col-md-6">
        <x-auth.input-field type="text" name="employer_number" id="employer_number" required="false" place="{{ __('1223-32') }}" val="{{$result?->employer_number}}" extraclasses="" label="{{ __('Employer Number') }}" />
    </div>

    <div class="col-md-6">
        <x-auth.input-field type="text" name="emergency_contact_name" id="emergency_contact_name" required="false" place="{{ __('9809-2988821') }}" val="{{$result?->emergency_contact_name}}" extraclasses="" label="{{ __('Guuaranter') }}" />
    </div>

    <div class="col-md-6">
        <x-auth.input-field type="tel" name="emergency_contact_phone_number" id="emergency_contact_phone_number" required="false" place="{{ __('+92 321 7654321') }}" val="{{$result?->emergency_contact_phone_number}}" extraclasses="" label="{{ __('Emergency Contact Phone Number') }}" />
    </div>
    <div class="col-md-12">
        <x-auth.text-area name="address" id="address" required="true" place="{{ __('Plot 45, Street 10, Karachi') }}" val="{{$result?->address}}" extraclasses="form-control auto-grow-textarea" label="{{ __('Address') }}" />
    </div>
    <div class="col-md-4 mb-2">
        <x-auth.upload-file name="driver_profile" uid="driver_profile" label="{{ __('Driver Photograph') }}" image="{{ $result?->fileUrl('image') ?? asset('assets/img/user-img.png') }}" />
    </div>

    <div class="col-md-4 mb-2">
        <x-auth.upload-file name="company_id_card" uid="company_id_card" label="{{ __('Company ID Card') }}" image="{{ $result?->fileUrl('company_id_card') ?? asset('assets/img/com-id-img.png') }}" />
    </div>

    <div class="col-md-4 mb-2">
        <x-auth.upload-file name="license_card" uid="license_card" label="{{ __('License Card') }}" image="{{ $result?->fileUrl('license_card') ?? asset('assets/img/license-img.jfif') }}" />
    </div>




    <div class="col-12 mx-auto text-center mt-4">
        <x-auth.input-button btn-class="btn btn-primary px-5 py-2 rounded-pill fw-bold shadow" btn-type="submit" btn-value="{{ __('Submit') }}" />
    </div>
</x-auth.form>
