<x-layouts.auth page-title="{{ __('Awaiting Bills') }}" sub-title="Waybills pending allocation">

    <x-slot:actionButton>
        <x-auth.add-waybill-modal
            :vehicles="$data['vehicles']"
            :drivers="$data['drivers']"
            :route-trips="$data['route_trips']" />
    </x-slot:actionButton>

    <div class="row g-3 mb-4">
        <x-auth.quick-link-card-small icon="fa-hourglass-half" title="{{ $data['awaiting_count'] }}"
            description="Awaiting Bills" route="waybill.awaiting" iconbg="bg-warning" icontext="text-dark" />

        <x-auth.quick-link-card-small icon="fa-file-invoice-dollar" title="{{ __('All Waybills') }}"
            description="View All" route="waybill.index" iconbg="bg-primary" icontext="text-white" />
    </div>

    <div class="bg-white mt-3">
        <x-all-list title="{{ __('Awaiting Bill Log Table') }}" :data="$data['all']">
            <x-auth.datatable :search="true">
                <thead>
                    <tr class="text-dark">
                        <th scope="col">Waybill No</th>
                        <th scope="col">Vehicle</th>
                        <th scope="col">Driver</th>
                        <th scope="col">Route</th>
                        <th scope="col">Dispatch Date</th>
                        <th scope="col">Delivery Date</th>
                        <th scope="col">Status</th>
                        @canany(['view_waybill', 'edit_waybill', 'delete_waybill'])
                        <th scope="col">Action</th>
                        @endcanany
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['all'] as $waybill)
                    <tr>
                        <td>{{ $waybill->waybill_number }}</td>
                        <td>{{ $waybill->vehicle?->fleet_no ?? '-' }}</td>
                        <td>
                            @php
                                $listedDrivers = $waybill->driverPlans->filter(fn ($plan) => $plan->driver)->map(fn ($plan) => $plan->driver->full_name)->values();
                                if ($listedDrivers->isEmpty() && $waybill->driver) $listedDrivers->push($waybill->driver->full_name);
                            @endphp
                            {{ $listedDrivers->first() ?? '-' }}
                            @if ($listedDrivers->count() > 1)
                                <span class="badge bg-light text-primary border" title="{{ $listedDrivers->slice(1)->implode(', ') }}" data-bs-toggle="tooltip">+{{ $listedDrivers->count() - 1 }}</span>
                                <span class="visually-hidden">{{ $listedDrivers->slice(1)->implode(', ') }}</span>
                            @endif
                        </td>
                        <td>{{ $waybill->route_trip?->trip_name ?? '-' }}</td>
                        <td>{{ $waybill->dispatch_date?->format('d M Y') ?? '-' }}</td>
                        <td>{{ $waybill->delivery_date?->format('d M Y') ?? '-' }}</td>
                        <td>{!! $waybill->status_badge !!}</td>
                        @canany(['view_waybill', 'edit_waybill', 'delete_waybill'])
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="btn btn-sm border dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    @can('view_waybill')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('waybill.show', $waybill->id) }}">
                                            <i class="fa-solid fa-eye me-2 text-primary"></i> View
                                        </a>
                                    </li>
                                    @endcan
                                    @can('edit_waybill')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('waybill.edit', $waybill->id) }}">
                                            <i class="fa-solid fa-pen-to-square me-2 text-success"></i> Edit
                                        </a>
                                    </li>
                                    @endcan
                                    @can('delete_waybill')
                                    <li>
                                        <form action="{{ route('waybill.destroy', $waybill->id) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this waybill?');">
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
