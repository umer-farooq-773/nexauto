<x-layouts.auth page-title="Trip & Route Management" sub-title="Manage Trip & Route Data from this page">

    <x-slot:actionButton>
        <x-auth.add-route-trip-modal />
    </x-slot:actionButton>

    <x-slot:quickLinks>
        <x-auth.quick-link-card-small icon="fa-route" title="{{ $data['total_count'] }}" description="Total Routes"
            route="route_trip.index" iconbg="bg-primary" icontext="text-white" />

        <x-auth.quick-link-card-small icon="fa-circle-check" title="{{ $data['active_count'] }}" description="Active"
            route="route_trip.index" iconbg="bg-success" icontext="text-white" />

        <x-auth.quick-link-card-small icon="fa-circle-xmark" title="{{ $data['inactive_count'] }}" description="Inactive"
            route="route_trip.index" iconbg="bg-danger" icontext="text-white" />
    </x-slot:quickLinks>

    <div class="bg-white mt-3">
        <x-all-list title="Trip And Route Log Table" :data="$data['all']">
            <x-auth.datatable :search="true">
                <thead>
                    <tr class="text-dark">
                        <th scope="col">Trip Name</th>
                        <th scope="col">Origin</th>
                        <th scope="col">Destination</th>
                        <th scope="col">Distance (KM)</th>
                        <th scope="col">Est. Duration</th>
                        <th scope="col">Status</th>
                        @canany(['view_route_trip', 'edit_route_trip', 'delete_route_trip'])
                        <th scope="col">Action</th>
                        @endcanany
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['all'] as $route)
                    <tr>
                        <td>{{ $route->trip_name }}</td>
                        <td>{{ $route->origin }}</td>
                        <td>{{ $route->destination }}</td>
                        <td>{{ $route->distance_km ?? '-' }}</td>
                        <td>{{ $route->estimated_duration ?? '-' }}</td>
                        <td>{!! $route->status_badge !!}</td>
                        @canany(['view_route_trip', 'edit_route_trip', 'delete_route_trip'])
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="btn btn-sm border dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    @can('view_route_trip')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('route_trip.show', $route->id) }}">
                                            <i class="fa-solid fa-eye me-2 text-primary"></i> View
                                        </a>
                                    </li>
                                    @endcan
                                    @can('edit_route_trip')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('route_trip.edit', $route->id) }}">
                                            <i class="fa-solid fa-pen-to-square me-2 text-success"></i> Edit
                                        </a>
                                    </li>
                                    @endcan
                                    @can('delete_route_trip')
                                    <li>
                                        <form action="{{ route('route_trip.destroy', $route->id) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this route?');">
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
