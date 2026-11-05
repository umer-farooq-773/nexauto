<x-layouts.auth :page-title="__('Incident & Intervention')"
    sub-title="Emergency reporting and field intervention management">

    <div class="row g-4 mb-5">
        <x-auth.quick-link-card icon="fa-info-circle" :title="__('Impoundment')"
            description="Detailed Asset information and history logs" route="impoundment" iconbg="bg-blue-light"
            icontext="" />

        <x-auth.quick-link-card icon="fa-user-tie" :title="__('tracking Asset Breakdown')"
            description="Driver profiles, licenses and route assignments" route="vehicle_breakdown"
            iconbg="bg-green-light" icontext="" />

        <x-auth.quick-link-card icon="fa-route" :title="__('Illegal Detention')"
            description="Plan and track delivery routes and history" route="illegal_detention" iconbg="bg-yellow-light"
            icontext="" />

        <x-auth.quick-link-card icon="fa-file-invoice-dollar" :title="__('Accident')"
            description="Shipment documentation and billing records" route="acident" iconbg="bg-orange-light"
            icontext="" />

        <x-auth.quick-link-card icon="fa-clock-rotate-left" :title="__('Bogged Down')"
            description="Maintenance logs and service schedules" route="vehicle_bogged_down" iconbg="bg-blue-light"
            icontext="" />

        <x-auth.quick-link-card icon="fa-clock-rotate-left" :title="__('Incident History')"
            description="Maintenance logs and service schedules" route="inccident" iconbg="bg-blue-light" icontext="" />
    </div>
</x-layouts.auth>
