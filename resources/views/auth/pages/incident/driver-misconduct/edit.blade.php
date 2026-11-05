<x-layouts.auth page-title="{{ __('Edit Driver Misconduct Record') }}" sub-title="Update driver misconduct details">
    <x-auth.add-driver-misconduct-form
        route-action="{{ route('driver_misconduct.update', $data->id) }}"
        :data="$data"
        :drivers="$drivers"
        :vehicles="$vehicles"
        :waybills="$waybills"
        :is-edit="true" />
</x-layouts.auth>
