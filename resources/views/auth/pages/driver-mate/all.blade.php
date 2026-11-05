<x-layouts.auth page-title="{{ __('Driver Mate Management') }}" sub-title="Manage Driver Mate Data from this page">

    <x-slot:actionButton>

        <div class="d-flex gap-2">
            <x-auth.add-driver-mate-model />

            <x-export-csv
                table="driver_mates"
                exportRoute="{{ route('export.csv.export') }}"
                columnsRoute="{{ route('export.csv.columns') }}"
            />
        </div>

    </x-slot:actionButton>

    <x-slot:quickLinks>
        <x-auth.quick-link-card-small icon="fa-users" title="{{ $data['all_mate_count'] }}" description="Total Mates" route="driver_mate.index"
            iconbg="bg-primary" icontext="text-white" />

        <x-auth.quick-link-card-small icon="fa-circle-check" title="{{ $data['active_count'] }}" description="Active" route="driver_mate.index"
            iconbg="bg-success" icontext="text-white" />

        <x-auth.quick-link-card-small icon="fa-circle-xmark" title="{{ $data['inactive_count'] }}" description="Inactive" route="driver_mate.index"
            iconbg="bg-danger" icontext="text-white" />
    </x-slot:quickLinks>

    <div class="bg-white mt-3">
        <x-all-list title="{{ __('Driver Mate Log Table') }}" :data="$data['all']">
            <x-auth.datatable :search="true">
                <thead>
                    <tr class="text-dark">
                        <th scope="col">Full Name</th>
                        <th scope="col">Phone Number</th>
                        <th scope="col">National ID / Passport</th>
                        <th scope="col">Date Employed</th>
                        <th scope="col">Status</th>
                        @canany(['view_driver_mate', 'edit_driver_mate', 'delete_driver_mate'])
                        <th scope="col">Action</th>
                        @endcanany
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['all'] as $mate)
                    <tr>
                        <td>{{ $mate?->full_name }}</td>
                        <td>{{ $mate?->phone_number }}</td>
                        <td>{{ $mate?->national_id_passport_number }}</td>
                        <td>{{ $mate?->date_employed ?? '-' }}</td>
                        <td>
                            <x-status-button
                                    status="{{ strtolower($mate->status ?? 'awaiting') }}"
                                    {{-- :count="empty" --}}
                                    label="Status"
                                    :html="$mate->status  ?? ''"
                                    table="driver_mates"
                                />
                        </td>
                        @canany(['view_driver_mate', 'edit_driver_mate', 'delete_driver_mate'])
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="btn btn-sm border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    @can('view_driver_mate')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('driver_mate.show', $mate?->id) }}">
                                            <i class="fa-solid fa-eye me-2 text-primary"></i> View
                                        </a>
                                    </li>
                                    @endcan
                                    @can('edit_driver_mate')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('driver_mate.edit', $mate?->id) }}">
                                            <i class="fa-solid fa-pen-to-square me-2 text-success"></i> Edit
                                        </a>
                                    </li>
                                    @endcan
                                    @can('delete_driver_mate')
                                    <li>
                                        <form action="{{ route('driver_mate.destroy', $mate?->id) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this driver mate?');">
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
