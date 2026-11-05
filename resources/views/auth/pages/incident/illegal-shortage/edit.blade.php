<x-layouts.auth page-title="{{ __('Edit Illegal Shortage Record') }}" sub-title="Update illegal shortage details">
    <x-auth.add-illegal-shortage-form
        route-action="{{ route('illegal_shortage.update', $data->id) }}"
        :data="$data"
        :drivers="$drivers"
        :vehicles="$vehicles"
        :waybills="$waybills"
        :is-edit="true" />
</x-layouts.auth>
