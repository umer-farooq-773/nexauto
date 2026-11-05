<x-layouts.auth page-title="{{ __('Edit Bogged Down Record') }}" sub-title="Update vehicle bogged down details">
    <x-auth.add-vehicle-bogged-down-form
        route-action="{{ route('vehicle_bogged_down.update', $data->id) }}"
        :data="$data"
        :vehicles="$vehicles"
        :waybills="$waybills"
        :is-edit="true" />
</x-layouts.auth>
