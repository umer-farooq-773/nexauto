<x-auth.form form-id="RouteTripForm" form-action="{{ $route }}" enctype="false">
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="col-md-6">
        <x-auth.input-field type="text" name="trip_name" id="trip_name" required="true"
            place="{{ __('Karachi to Lahore Express') }}" val="{{ $result?->trip_name }}"
            extraclasses="" label="{{ __('Trip Name') }}" />
    </div>

    <div class="col-md-6">
        <x-auth.input-field type="text" name="origin" id="origin" required="true"
            place="{{ __('Karachi') }}" val="{{ $result?->origin }}"
            extraclasses="" label="{{ __('Origin') }}" />
    </div>

    <div class="col-md-6">
        <x-auth.input-field type="text" name="destination" id="destination" required="true"
            place="{{ __('Lahore') }}" val="{{ $result?->destination }}"
            extraclasses="" label="{{ __('Destination') }}" />
    </div>

    <div class="col-md-6">
        <x-auth.input-field type="number" name="distance_km" id="distance_km" required="false"
            place="{{ __('1200') }}" val="{{ $result?->distance_km }}"
            extraclasses="" label="{{ __('Distance (KM)') }}" />
    </div>

    <div class="col-md-6">
        <x-auth.input-field type="text" name="estimated_duration" id="estimated_duration" required="false"
            place="{{ __('18 Hours') }}" val="{{ $result?->estimated_duration }}"
            extraclasses="" label="{{ __('Estimated Duration') }}" />
    </div>

    <div class="col-md-6 form-floating mb-3">
        <select name="status" id="status" class="form-select shadow-sm">
            <option value="1" {{ ($result?->status ?? 1) == 1 ? 'selected' : '' }}>Active</option>
            <option value="0" {{ ($result?->status ?? 1) == 0 ? 'selected' : '' }}>Inactive</option>
        </select>
        <label for="status">{{ __('Status') }}</label>
    </div>

    <div class="col-md-12">
        <x-auth.text-area name="description" id="description" required="false"
            place="{{ __('Route description...') }}" val="{{ $result?->description }}"
            extraclasses="" label="{{ __('Description') }}" />
    </div>

    <div class="col-4 mx-auto text-center mt-4">
        <x-auth.input-button btn-class="btn btn-primary px-5 py-2 rounded-pill fw-bold shadow"
            btn-type="submit" btn-value="{{ __('Submit') }}" />
    </div>
</x-auth.form>
