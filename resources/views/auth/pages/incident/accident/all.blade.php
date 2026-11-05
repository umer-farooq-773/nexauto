<x-layouts.auth page-title="{{ __('Accident Management') }}" sub-title="Manage Accident Records">

    <x-slot:actionButton>
        <div class="d-flex gap-2">
            <x-auth.add-accident-modal :vehicles="$data['vehicles']" :waybills="$data['waybills']" :has-allocated="$data['has_allocated'] ?? false" />
            <x-export-csv
                table="accidents"
                exportRoute="{{ route('export.csv.export') }}"
                columnsRoute="{{ route('export.csv.columns') }}"
            />
        </div>

    </x-slot:actionButton>

    <x-slot:quickLinks>
        <x-auth.quick-link-card-small icon="fa-car-crash" title="{{ $data['total_count'] }}" description="Total Records"
            route="accident.index" iconbg="bg-primary" icontext="text-white" />
        <x-auth.quick-link-card-small icon="fa-circle-check" title="{{ $data['resolved_count'] }}" description="Resolved"
            route="accident.index" iconbg="bg-success" icontext="text-white" />
        <x-auth.quick-link-card-small icon="fa-clock" title="{{ $data['inprogress_count'] }}" description="In Progress"
            route="accident.index" iconbg="bg-warning" icontext="text-dark" />
        <x-auth.quick-link-card-small icon="fa-circle-xmark" title="{{ $data['pending_count'] }}" description="Pending"
            route="accident.index" iconbg="bg-danger" icontext="text-white" />
    </x-slot:quickLinks>

    <div class="bg-white mt-3">
        <x-all-list title="{{ __('Accident Log Table') }}" :data="$data['all']">
            <x-auth.datatable :search="true">
                <thead>
                    <tr class="text-dark">
                        <th scope="col">Waybill</th>
                        <th scope="col">Driver Name</th>
                        <th scope="col">Asset</th>
                        <th scope="col">Accident Date</th>
                        <th scope="col">Location</th>
                        <th scope="col">Type</th>
                        <th scope="col">Damage Cost</th>
                        <th scope="col">Resolved Date</th>
                        <th scope="col">Severity</th>
                        <th scope="col">Status</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['all'] as $record)
                    <tr>
                        <td><a href="{{ route('accident.show', $record->id) }}" class="text-decoration-underline">{{ $record->waybill?->waybill_number ?? '-' }}</a></td>
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
                        <td>{{ $record->accident_date ? \Carbon\Carbon::parse($record->accident_date)->format('d M Y') : '-' }}</td>
                        <td>{{ $record->accident_location }}</td>
                        <td>{{ $record->accident_type ?? '-' }}</td>
                        <td>{{ $record->damage_cost ? number_format($record->damage_cost, 2) : '-' }}</td>
                        <td>{{ $record->resolved_date ? \Carbon\Carbon::parse($record->resolved_date)->format('d M Y') : '-' }}</td>
                        <td>{{ $record->driver_status ?? '-' }}</td>
                        <td><x-status-button
                                    status="{{ strtolower($record->status ?? 'awaiting') }}"
                                    {{-- :count="empty" --}}
                                    label="Status"
                                    :html="$record->status  ?? ''"
                                    table="accidents"
                                /></td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="btn btn-sm border dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('accident.show', $record->id) }}">
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
                                        <a class="dropdown-item" href="{{ route('accident.edit', $record->id) }}">
                                            <i class="fa-solid fa-pen-to-square me-2 text-success"></i> Edit
                                        </a>
                                    </li>
                                    <li>
                                        <form action="{{ route('accident.destroy', $record->id) }}" method="POST"
                                            onsubmit="return confirm('Are you sure?');">
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
