<x-layouts.auth page-title="{{ __('Edit Waybill') }}" sub-title="Update Waybill Details">
    <div style="position: relative; z-index: 994;">
    <x-auth.add-waybill-form
        route-action="{{ route('waybill.update', $data->id) }}"
        :result="$data"
        :is-edit="true"
        :vehicles="$vehicles"
        :drivers="$drivers"
        :route-trips="$route_trips" />
</div>

</x-layouts.auth>
