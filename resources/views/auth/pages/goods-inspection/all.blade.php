<x-layouts.auth page-title="{{ __('On Board Goods Inspection') }}" sub-title="Manage Goods Inspection Records">

    <x-slot:actionButton>

        <div class="d-flex gap-2">
            <x-auth.add-goods-inspection-modal :vehicles="$data['vehicles']" :waybills="$data['waybills']" />

            <x-export-csv
                table="goods_inspections"
                exportRoute="{{ route('export.csv.export') }}"
                columnsRoute="{{ route('export.csv.columns') }}"
            />
        </div>


    </x-slot:actionButton>

    <x-slot:quickLinks>
        <x-auth.quick-link-card-small icon="fa-inbox" title="{{ $data['total_count'] }}" description="Total Inspections"
            route="goods_inspection.index" iconbg="bg-primary" icontext="text-white" />

        <x-auth.quick-link-card-small icon="fa-check-circle" title="{{ $data['cleared_count'] }}"
            description="Cleared / Safe" route="goods_inspection.index" iconbg="bg-success" icontext="text-white" />

        <x-auth.quick-link-card-small icon="fa-exclamation-triangle" title="{{ $data['issues_count'] }}"
            description="Issues Found" route="goods_inspection.index" iconbg="bg-danger" icontext="text-white" />

        <x-auth.quick-link-card-small icon="fa-hourglass-half" title="{{ $data['pending_count'] }}"
            description="In Progress" route="goods_inspection.index" iconbg="bg-warning" icontext="text-white" />
    </x-slot:quickLinks>

    <div class="bg-white mt-3">
        <x-all-list title="{{ __('Goods Inspection Table') }}" :data="$data['all']">
            <x-auth.datatable :search="true">
                <thead>
                    <tr class="text-dark">
                        <th scope="col">Driver Name</th>
                        <th scope="col">Fleet No</th>
                        <th scope="col">Inspection Date</th>
                        <th scope="col">Inspection Type</th>
                        <th scope="col">Inspector</th>
                        <th scope="col">Goods Status</th>
                        <th scope="col">Inspection Status</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['all'] as $record)
                        <tr>
                            <td>
                            @if ($record->waybill?->driver)
                                {{ $record->waybill->driver->full_name }}
                            @elseif ($record->vehicle?->drivers && $record->vehicle->drivers->count() > 0)
                                {{ $record->vehicle->drivers->pluck('full_name')->join(', ') }}
                            @else
                                -
                            @endif
                        </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $record->fleet_no ?? '-' }} {{ $record->truck_no ?? '' }}</span>
                            </td>
                            <td>{{ $record->inspection_date->format('d/m/Y') }}</td>
                            <td>
                                <x-status-button
                                    status="{{ strtolower($record->inspection_type ?? '') }}"
                                    label="Type"
                                    table="goods_inspections_type"
                                />
                            </td>
                            <td>{{ $record->inspector_name }}</td>
                            <td>
                                <x-status-button
                                    status="{{ strtolower($record->goods_status ?? 'cleared') }}"
                                    label="Goods Status"
                                    table="goods_inspections_goods"
                                />
                            </td>
                            <td>
                                <x-status-button
                                    status="{{ (string)($record->status ?? '0') }}"
                                    label="Inspection Status"
                                    table="goods_inspections"
                                />
                            </td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm border dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('goods_inspection.show', $record->id) }}">
                                                <i class="fa-solid fa-eye me-2 text-primary"></i> View
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('goods_inspection.edit', $record->id) }}">
                                                <i class="fa-solid fa-pen-to-square me-2 text-success"></i> Edit
                                            </a>
                                        </li>
                                        <li>
                                            <form action="{{ route('goods_inspection.destroy', $record->id) }}"
                                                method="POST"
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
