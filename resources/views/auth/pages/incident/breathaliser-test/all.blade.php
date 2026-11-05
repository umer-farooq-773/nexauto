<x-layouts.auth page-title="{{ __('Breathaliser Test Management') }}" sub-title="Track and manage breathaliser test records">

    <x-slot:actionButton>

        <div class="d-flex gap-2">
            <x-auth.add-breathaliser-test-modal :drivers="$data['drivers']" :vehicles="$data['vehicles']" :waybills="$data['waybills']" :has-allocated="$data['has_allocated'] ?? false" />

            <x-export-csv
                table="breathaliser_tests"
                exportRoute="{{ route('export.csv.export') }}"
                columnsRoute="{{ route('export.csv.columns') }}"
            />
        </div>

    </x-slot:actionButton>

    <x-slot:quickLinks>
        <x-auth.quick-link-card-small icon="fa-wind" title="{{ $data['total_count'] }}" description="Total Records"
            route="breathaliser_test.index" iconbg="bg-primary" icontext="text-white" />
        <x-auth.quick-link-card-small icon="fa-triangle-exclamation" title="{{ $data['open_count'] }}" description="Open"
            route="breathaliser_test.index" iconbg="bg-danger" icontext="text-white" />
        <x-auth.quick-link-card-small icon="fa-circle-exclamation" title="{{ $data['positive_count'] }}" description="Positive Tests"
            route="breathaliser_test.index" iconbg="bg-warning" icontext="text-dark" />
        <x-auth.quick-link-card-small icon="fa-circle-check" title="{{ $data['closed_count'] }}" description="Closed"
            route="breathaliser_test.index" iconbg="bg-success" icontext="text-white" />
    </x-slot:quickLinks>

    <div class="bg-white mt-3">
        <x-all-list title="{{ __('Breathaliser Test Log Table') }}" :data="$data['all']">
            <x-auth.datatable :search="true">
                <thead>
                    <tr class="text-dark">
                        <th scope="col">Waybill</th>
                        <th scope="col">Driver</th>
                        <th scope="col">Asset</th>
                        <th scope="col">Test Date</th>
                        <th scope="col">BAC Level</th>
                        <th scope="col">Result</th>
                        <th scope="col">Attachment</th>
                        <th scope="col">Status</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['all'] as $record)
                    <tr>
                        <td><a href="{{ route('breathaliser_test.show', $record->id) }}" class="text-decoration-underline">{{ $record->waybill?->waybill_number ?? '-' }}</a></td>
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
                        {{-- <td>{{ $record->waybill?->waybill_number ?? '-' }}</td> --}}
                        <td>{{ $record->test_date ? Carbon\Carbon::parse($record->test_date)->format('Y-m-d') : '-' }}</td>
                        <td>{{ $record->bac_level ?? '-' }}</td>
                        <td>
                            <x-status-button
                                status="{{ strtolower($record->test_result ?? 'negative') }}"
                                label="Result"
                                table="breathaliser_tests_result"
                            />
                        </td>
                        <td>
                            @if ($record->document_url)
                                <a href="{{ $record->document_url }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill">
                                    View File
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <x-status-button
                                status="{{ (string)($record->status ?? '0') }}"
                                label="Status"
                                table="breathaliser_tests"
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
                                        <a class="dropdown-item" href="{{ route('breathaliser_test.show', $record->id) }}">
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
                                        <a class="dropdown-item" href="{{ route('breathaliser_test.edit', $record->id) }}">
                                            <i class="fa-solid fa-pen-to-square me-2 text-success"></i> Edit
                                        </a>
                                    </li>
                                    <li>
                                        <form action="{{ route('breathaliser_test.destroy', $record->id) }}" method="POST"
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
