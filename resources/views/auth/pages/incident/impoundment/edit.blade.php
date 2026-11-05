<x-layouts.auth page-title="{{ __('Edit Impoundment Record') }}" sub-title="Update Impoundment Details">
    <x-auth.add-impoundment-form
        route-action="{{ route('impoundment.update', $data->id) }}"
        :data="$data"
        :vehicles="$vehicles"
        :waybills="$waybills"
        :is-edit="true" />
</x-layouts.auth>
