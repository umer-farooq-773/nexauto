<x-layouts.auth page-title="{{ __('Edit Illegal Haulage Record') }}" sub-title="Update illegal haulage details">
    <x-auth.add-illegal-haulage-form
        route-action="{{ route('illegal_haulage.update', $data->id) }}"
        :data="$data"
        :drivers="$drivers"
        :vehicles="$vehicles"
        :waybills="$waybills"
        :is-edit="true" />
</x-layouts.auth>
