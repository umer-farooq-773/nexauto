<x-layouts.auth page-title="{{ __('Edit Illegal Detention Record') }}" sub-title="Update Detention Details">
    <x-auth.add-illegal-detention-form
        route-action="{{ route('illegal_detention.update', $data->id) }}"
        :data="$data"
        :vehicles="$vehicles"
        :waybills="$waybills"
        :is-edit="true" />
</x-layouts.auth>
