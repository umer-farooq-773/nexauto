<x-layouts.auth page-title="{{ __('Edit Accident Record') }}" sub-title="Update Accident Details">
    <x-auth.add-accident-form
        route-action="{{ route('accident.update', $data->id) }}"
        :data="$data"
        :vehicles="$vehicles"
        :waybills="$waybills"
        :is-edit="true" />
</x-layouts.auth>
