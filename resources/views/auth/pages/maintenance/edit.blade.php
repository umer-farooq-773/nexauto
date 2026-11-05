<x-layouts.auth page-title="{{ __('Edit Maintenance Record') }}" sub-title="Update Maintenance Details">
    <x-auth.add-maintenance-form
        route-action="{{ route('maintenance.update', $data->id) }}"
        :data="$data"
        :vehicles="$vehicles"
        :maintenance-types="$maintenanceTypes"
        :is-edit="true" />
</x-layouts.auth>
