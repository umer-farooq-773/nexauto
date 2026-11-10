<x-layouts.auth page-title="{{__('Driver Management')}}" sub-title="Manage Driver Data from this page">

    <x-slot:actionButton>

        <div class="d-flex gap-2">
            <x-auth.add-driver-model/>

            <x-export-csv
                table="driver_details"
                exportRoute="{{ route('export.csv.export') }}"
                columnsRoute="{{ route('export.csv.columns') }}"
            />

        </div>
    </x-slot:actionButton>

    <x-slot:quickLinks>
        <x-auth.quick-link-card-small icon="fa-truck" title="{{$data['all_driver_count']}}" description="Total Drivers" route="driver.index"
            iconbg="bg-primary" icontext="text-white" />

        <x-auth.quick-link-card-small icon="fa-route" title="{{$data['on_duty_count']}}" description="On Duty" route="driver.index"
            iconbg="bg-secondary" icontext="text-white" />

        <x-auth.quick-link-card-small icon="fa-user" title="{{$data['ratting_count']}}" description="Avg Rating" route="driver.index"
            iconbg="bg-success" icontext="text-white" />

        <x-auth.quick-link-card-small icon="fa-wrench" title="{{$data['off_duty_count']}}" description="Off Duty" route="driver.index"
            iconbg="bg-danger" icontext="text-white" />
    </x-slot:quickLinks>

    @if(session('driver_credentials'))
        <div class="alert alert-success alert-dismissible fade show mt-3 shadow-sm border-0 rounded-3" role="alert">
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="fa-solid fa-circle-check fs-5"></i>
                <h6 class="mb-0 fw-bold">{{ __('Driver Portal Credentials Generated!') }}</h6>
            </div>
            <p class="mb-1 text-muted">
                {{ __('A welcome notification was dispatched to the driver. You can also share these credentials directly:') }}
            </p>
            <div class="p-2 bg-light rounded-2 font-monospace small">
                <div><strong>Email:</strong> {{ session('driver_credentials.email') }}</div>
                <div><strong>Password:</strong> <span class="badge bg-dark">{{ session('driver_credentials.password') }}</span></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="bg-white mt-3">
        <x-all-list title="{{__('Driver Log Table')}}" :data="$data['all']">
            <x-auth.datatable :search="true">
                <thead >
                    <tr class="text-dark">
                        <th scope="col">Full Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Driver's License Number</th>
                        <th scope="col">License Category/Class</th>
                        <th scope="col">License Expiry Date</th>
                        <th scope="col">Asset</th>
                        <th scope="col">Status</th>
                        @canany(['view_driver', 'edit_driver', 'delete_driver'])
                        <th scope="col">Action</th>
                        @endcanany
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['all'] as $key => $driver)
                    <tr>
                        <td>
                            <div class="fw-semibold text-dark">{{ $driver?->full_name }}</div>
                            @if($driver?->user_id)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 10px;">Portal Active</span>
                            @endif
                        </td>
                        <td>
                            @if($driver?->email)
                                <span class="text-primary">{{ $driver->email }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $driver?->drivers_license_number }}</td>
                        <td>{{ $driver?->license_category_class }}</td>
                        <td>{{ $driver?->license_expiry_date }}</td>
                        <td>
                            @if($driver?->vehicles && $driver?->vehicles->count() > 0)
                                {{ $driver?->vehicles->pluck('fleet_no')->join(', ') }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            <x-status-button
                                    status="{{ strtolower($driver->status ?? 'awaiting') }}"
                                    {{-- :count="empty" --}}
                                    label="Status"
                                    :html="$driver->status  ?? ''"
                                    table="driver_details"
                                />
                        </td>
                        @canany(['view_driver', 'edit_driver', 'delete_driver'])
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="btn btn-sm border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-ellipsis-vertical"></i> </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow">
                                    <li>
                                        <a class="dropdown-item text-primary" href="{{ route('driver.portal', ['driver_id' => $driver->id]) }}" target="_blank">
                                            <i class="fa-solid fa-tablet-screen-button me-2"></i> {{ __('Open Driver ELD Screen') }}
                                        </a>
                                    </li>
                                    @if($driver?->email)
                                    <li>
                                        <a class="dropdown-item text-secondary" href="{{ route('driver.resend_credentials', $driver->id) }}" onclick="return confirm('Reset and send new login password to {{ $driver->email }}?');">
                                            <i class="fa-solid fa-key me-2"></i> {{ __('Resend Credentials') }}
                                        </a>
                                    </li>
                                    @endif
                                    @can('view_driver')
                                <li>
                                    <a class="dropdown-item" href="{{ route('driver.show', $driver?->id) }}">
                                    <i class="fa-solid fa-eye me-2 text-primary"></i> View
                                    </a>
                                </li>
                                @endcan
                                @can('edit_driver')
                                <li>
                                    <a class="dropdown-item" href="{{ route('driver.edit', $driver?->id) }}">
                                    <i class="fa-solid fa-pen-to-square me-2 text-success"></i> Edit
                                    </a>
                                </li>
                                @endcan
                                @can('delete_driver')
                                <li>
                                    <form action="{{ route('driver.destroy', $driver?->id) }}" method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this driver?');">
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
