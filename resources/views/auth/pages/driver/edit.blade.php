<x-layouts.auth page-title="{{__('Driver Edit')}}" sub-title="Edit Driver Details">
    <x-auth.add-driver-form route-action="{{route('driver.update', $data->id)}}" :data="$data" :is-edit="true"/>
</x-layouts.auth>