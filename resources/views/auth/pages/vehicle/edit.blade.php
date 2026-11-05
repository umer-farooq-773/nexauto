<x-layouts.auth page-title="{{__('Asset Edit')}}" sub-title="Edit Asset Details">
    <x-auth.vehicle-form route-action="{{route('vehicle.update', $data->id)}}" :data="$data" :is-edit="true"/>
</x-layouts.auth>
