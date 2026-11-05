<x-layouts.auth page-title="{{ __($module['module_title'] . ' Management') }}" sub-title="Manage {{ $module['module_title'] }} Records">

    <x-slot:actionButton>
        <div class="d-flex gap-2">
            <x-auth.add-vehicle-breakdown-modal
                :vehicles="$data['vehicles']"
                :waybills="$data['waybills']"
                :has-allocated="$data['has_allocated'] ?? false"
                :route-prefix="$module['route_prefix']"
                :module-title="$module['module_title']"
                :module="$module" />

            <x-export-csv
                table="vehicle_breakdowns"
                exportRoute="{{ route('export.csv.export') }}"
                columnsRoute="{{ route('export.csv.columns') }}"
            />

        </div>
    </x-slot:actionButton>

    <x-slot:quickLinks>
        <x-auth.quick-link-card-small icon="fa-car-burst" title="{{ $data['total_count'] }}" description="Total Records"
            :route="$module['route_prefix'] . '.index'" iconbg="bg-primary" icontext="text-white" />

        <x-auth.quick-link-card-small icon="fa-circle-check" title="{{ $data['resolved_count'] }}" description="Resolved"
            :route="$module['route_prefix'] . '.index'" iconbg="bg-success" icontext="text-white" />

        <x-auth.quick-link-card-small icon="fa-clock" title="{{ $data['inprogress_count'] }}" description="In Progress"
            :route="$module['route_prefix'] . '.index'" iconbg="bg-warning" icontext="text-dark" />

        <x-auth.quick-link-card-small icon="fa-circle-xmark" title="{{ $data['pending_count'] }}" description="Pending"
            :route="$module['route_prefix'] . '.index'" iconbg="bg-danger" icontext="text-white" />
    </x-slot:quickLinks>

    <div class="bg-white mt-3">
        <x-all-list title="{{ __($module['module_title'] . ' Log Table') }}" :data="$data['all']">
            <x-auth.datatable :search="true">
                <thead>
                    <tr class="text-dark">
                        <th scope="col">WayBill</th>
                        <th scope="col">Driver Name</th>
                        <th scope="col">Asset</th>
                        <th scope="col">{{ $module['date_label'] }}</th>
                        <th scope="col">{{ $module['location_label'] }}</th>
                        <th scope="col">{{ $module['type_label'] }}</th>
                        <th scope="col">{{ $module['cost_label'] }}</th>
                        <th scope="col">{{ $module['resolved_date_label'] }}</th>
                        <th scope="col">Status</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['all'] as $record)
                    <tr>
                        <td><a href="{{ route('vehicle_breakdown.show', $record->id) }}" class="text-decoration-underline">{{ $record->waybill?->waybill_number ?? '-' }}</a></td>
                        <td>
                            @if ($record->waybill?->driver)
                                {{ $record->waybill->driver->full_name }}
                            @elseif ($record->vehicle?->drivers && $record->vehicle->drivers->count() > 0)
                                {{ $record->vehicle->drivers->pluck('full_name')->join(', ') }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $record->vehicle?->fleet_no ?? '-' }} - {{ $record->vehicle?->truck_no ?? '' }}</td>
                        <td>{{ $record->breakdown_date ? \Carbon\Carbon::parse($record->breakdown_date)->format('d M Y') : '-' }}</td>
                        <td>{{ $record->breakdown_location }}</td>
                        <td>{{ $record->breakdown_type ?? '-' }}</td>
                        <td>{{ $record->repair_cost ? number_format($record->repair_cost, 2) : '-' }}</td>
                        <td>{{ $record->resolved_date ? \Carbon\Carbon::parse($record->resolved_date)->format('d M Y') : '-' }}</td>
                        <td><x-status-button
                                    status="{{ strtolower($record->status ?? 'awaiting') }}"
                                    {{-- :count="empty" --}}
                                    label="Status"
                                    :html="$record->status  ?? ''"
                                    table="vehicle_breakdowns"
                                /></td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="btn btn-sm border dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="{{ route($module['route_prefix'] . '.show', $record->id) }}">
                                            <i class="fa-solid fa-eye me-2 text-primary"></i> View
                                        </a>
                                    </li>
                                    @if($record->document_url)
                                    <li>
                                        <a class="dropdown-item" href="{{ $record->document_url }}" target="_blank">
                                            <i class="fa-solid fa-file-pdf me-2 text-danger"></i> View File
                                        </a>
                                    </li>
                                    @endif
                                    <li>
                                        <a class="dropdown-item" href="{{ route($module['route_prefix'] . '.edit', $record->id) }}">
                                            <i class="fa-solid fa-pen-to-square me-2 text-success"></i> Edit
                                        </a>
                                    </li>
                                    <li>
                                        <form action="{{ route($module['route_prefix'] . '.destroy', $record->id) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this record?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fa-solid fa-trash me-2"></i> Delete
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </x-auth.datatable>
        </x-all-list>
    </div>

</x-layouts.auth>
