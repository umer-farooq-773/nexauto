<x-layouts.auth page-title="{{ __('Edit Breathaliser Test Record') }}" sub-title="Update breathaliser test details">
    <x-auth.add-breathaliser-test-form
        route-action="{{ route('breathaliser_test.update', $data->id) }}"
        :data="$data"
        :drivers="$drivers"
        :vehicles="$vehicles"
        :waybills="$waybills"
        :is-edit="true" />
</x-layouts.auth>
