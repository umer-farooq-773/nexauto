<x-layouts.auth page-title="{{ __('Trip & Route Edit') }}" sub-title="Edit Trip & Route Details">
    <x-auth.add-route-trip-form route-action="{{ route('route_trip.update', $data->id) }}" :data="$data" :is-edit="true" />
</x-layouts.auth>
