<x-layouts.auth page-title="{{ __('Edit Route Diversion Record') }}" sub-title="Update route diversion details">
    <x-auth.add-route-diversion-form
        route-action="{{ route('route_diversion.update', $data->id) }}"
        :data="$data"
        :vehicles="$vehicles"
        :waybills="$waybills"
        :is-edit="true" />
</x-layouts.auth>
