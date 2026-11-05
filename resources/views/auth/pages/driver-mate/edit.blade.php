<x-layouts.auth page-title="{{ __('Driver Mate Edit') }}" sub-title="Edit Driver Mate Details">
    <x-auth.add-driver-mate-form route-action="{{ route('driver_mate.update', $data->id) }}" :data="$data" :is-edit="true" />
</x-layouts.auth>
