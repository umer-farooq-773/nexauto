<x-layouts.auth page-title="{{ __('Edit ' . $module['record_title']) }}" sub-title="Update {{ $module['module_title'] }} Details">
    <x-auth.add-vehicle-breakdown-form
        route-action="{{ route($module['route_prefix'] . '.update', $data->id) }}"
        :data="$data"
        :vehicles="$vehicles"
        :waybills="$waybills"
        :is-edit="true"
        :module="$module" />
</x-layouts.auth>
