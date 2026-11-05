<x-layouts.auth page-title="{{__('Assets Enumeration')}}" sub-title="">

    <x-slot:actionButton>

        <div class="d-flex gap-2">
            <x-auth.add-vehicle-model/>

            <x-export-csv
                table="vehicles"
                exportRoute="{{ route('export.csv.export') }}"
                columnsRoute="{{ route('export.csv.columns') }}"
            />

        </div>

    </x-slot:actionButton>

    <x-slot:quickLinks>
        <x-auth.quick-link-card-small icon="fa-truck" title="{{$data['all_vehicle_count']}}" description="Total Assets" route="vehicle.index"
            iconbg="bg-primary" icontext="text-white" />

        <x-auth.quick-link-card-small icon="fa-road" title="{{$data['active_vehicle_count']}}" description="Active Assets" route="vehicle.index"
            iconbg="bg-info" icontext="text-white" />

        <x-auth.quick-link-card-small icon="fa-user" title="{{$data['active_driver_count']}}" description="Active Drivers" route="driver.index"
            iconbg="bg-success" icontext="text-white" />

        <x-auth.quick-link-card-small icon="fa-wrench" title="{{$data['in_maintenance_count']}}" description="In Maintenance" route="vehicle.index"
            iconbg="bg-danger" icontext="text-white" />
    </x-slot:quickLinks>

    <div class="bg-white">
        <x-all-list title="{{__('Asset Log Table')}}" :data="$data['all']">
            <x-auth.datatable :search="true">
                <thead >
                    <tr class="text-dark">
                        <th scope="col">Truck No</th>
                        {{-- <th scope="col">Fleet No</th> --}}
                        <th scope="col">Asset Owner Name</th>
                        <th scope="col">Asset Manager Name</th>
                        <th scope="col">Make/Model</th>
                        <th scope="col">Status</th>
                        @canany(['view_vehicle', 'edit_vehicle', 'delete_vehicle'])
                        <th scope="col">Action</th>
                        @endcanany
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['all'] as $key => $vehicle)
                    <tr>
                        <td>{{ $vehicle?->truck_no ?? '-' }} - {{ $vehicle?->fleet_no ?? '-' }}</td>

                        {{-- <td>{{ $vehicle?->fleet_no }}</td> --}}
                        <td>{{ $vehicle?->vehicle_owner_name }}</td>
                        <td>{{ $vehicle?->vehicle_manager_name }}</td>
                        <td>{{ $vehicle?->vehicle_make_model?->name }}</td>
                        {{-- <td>{{ $vehicle?->vehicle_status }}</td> --}}
                        <td>
                            <x-status-button
                                    status="{{ strtolower($vehicle->status ?? 'awaiting') }}"
                                    {{-- :count="empty" --}}
                                    label="Status"
                                    :html="$vehicle->status  ?? ''"
                                    table="vehicles"
                                />
                        </td>
                        @canany(['view_vehicle', 'edit_vehicle', 'delete_vehicle'])
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="btn btn-sm border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-ellipsis-vertical"></i> </button>
                                <ul class="dropdown-menu">
                                    @can('view_vehicle')
                                <li>
                                    <a class="dropdown-item" href="{{ route('vehicle.show', $vehicle?->id) }}">
                                    <i class="fa-solid fa-eye me-2 text-primary"></i> View
                                    </a>
                                </li>
                                @endcan
                                @can('edit_vehicle')
                                <li>
                                    <a class="dropdown-item" href="{{ route('vehicle.edit', $vehicle?->id) }}">
                                    <i class="fa-solid fa-pen-to-square me-2 text-success"></i> Edit
                                    </a>
                                </li>
                                @endcan
                                @can('delete_vehicle')
                                <li>
                                    <form action="{{ route('vehicle.destroy', $vehicle?->id) }}" method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this vehicle?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fa-solid fa-trash me-2"></i> Delete
                                        </button>
                                    </form>
                                </li>
                                @endcan
                                </ul>
                            </div>
                        </td>
                        @endcanany
                    </tr>
                    @endforeach
                </tbody>
            </x-auth.datatable>
        </x-all-list>
    </div>
</x-layouts.auth>
