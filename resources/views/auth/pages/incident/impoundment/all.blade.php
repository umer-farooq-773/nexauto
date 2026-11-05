<x-layouts.auth page-title="{{ __('Impoundment Management') }}" sub-title="Manage Vehicle Impoundment Records">

    <x-slot:actionButton>
        <div class="d-flex gap-2">
            <x-auth.add-impoundment-modal :vehicles="$data['vehicles']" :waybills="$data['waybills']" :has-allocated="$data['has_allocated'] ?? false" />

            <x-export-csv table="impoundments" exportRoute="{{ route('export.csv.export') }}"
                columnsRoute="{{ route('export.csv.columns') }}" />

        </div>

    </x-slot:actionButton>

    <x-slot:quickLinks>
        <x-auth.quick-link-card-small icon="fa-truck-ramp-box" title="{{ $data['total_count'] }}"
            description="Total Records" route="impoundment.index" iconbg="bg-primary" icontext="text-white" />

        <x-auth.quick-link-card-small icon="fa-lock" title="{{ $data['impounded_count'] }}" description="Impounded"
            route="impoundment.index" iconbg="bg-danger" icontext="text-white" />

        <x-auth.quick-link-card-small icon="fa-lock-open" title="{{ $data['released_count'] }}" description="Released"
            route="impoundment.index" iconbg="bg-success" icontext="text-white" />
    </x-slot:quickLinks>

    <div class="bg-white mt-3">
        <x-all-list title="{{ __('Impoundment Log Table') }}" :data="$data['all']">
            <x-auth.datatable :search="true">
                <thead>
                    <tr class="text-dark">
                        <th scope="col">WayBill Number</th>
                        <th scope="col">Asset</th>
                        <th scope="col">Impound Date</th>
                        <th scope="col">Location</th>
                        <th scope="col">Authority</th>
                        <th scope="col">Driver Name</th>
                        <th scope="col">Release Date</th>
                        <th scope="col">Status</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['all'] as $record)
                        <tr>
                            <td><a
                                    href="{{ route('waybill.show', $record->waybill?->id) }}">{{ $record->waybill?->waybill_number ?? '-' }}</a>
                            </td>

                            <td>{{ $record->vehicle?->fleet_no ?? '-' }} - {{ $record->vehicle?->truck_no ?? '' }}</td>
                            <td>{{ $record->impound_date ? \Carbon\Carbon::parse($record->impound_date)->format('d M Y') : '-' }}</td>
                            <td>{{ $record->impound_location }}</td>
                            <td>{{ $record->impound_authority ?? '-' }}</td>
                            <td>
                                @if ($record->waybill?->driver)
                                    {{ $record->waybill->driver->full_name }}
                                @elseif ($record->vehicle?->drivers && $record->vehicle->drivers->count() > 0)
                                    {{ $record->vehicle->drivers->pluck('full_name')->join(', ') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="release_date">
                                {{ $record->release_date ? \Carbon\Carbon::parse($record->release_date)->format('Y-m-d') : '-' }}
                            </td>
                            <td>
                                <div class="dropdown status-dropdown">
                                    <span class="dropdown-toggle" role="button" data-bs-toggle="dropdown"
                                        aria-expanded="false" data-id="{{ $record->id }}"
                                        data-status="{{ $record->status }}"
                                        style="display:inline-flex; align-items:center; gap:7px; padding:6px 14px; font-size:12.5px; font-weight:600; letter-spacing:0.2px; border-radius:999px; white-space:nowrap; color:{{ $record->status == '1' ? '#0f6848' : '#721c24' }}; background:{{ $record->status == '1' ? '#d4edda' : '#f8d7da' }}; border:1px solid {{ $record->status == '1' ? '#28a745' : '#dc3545' }}; cursor:pointer; transition:transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease; box-shadow:0 2px 6px rgba(20,20,30,0.25);"
                                        onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 16px rgba(0,0,0,0.2)'; this.style.background='{{ $record->status == '1' ? '#b8e6c0' : '#f5c6cb' }}';"
                                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 6px rgba(20,20,30,0.25)'; this.style.background='{{ $record->status == '1' ? '#d4edda' : '#f8d7da' }}';">
                                        <i class="fa-solid {{ $record->status == '1' ? 'fa-lock-open' : 'fa-lock' }}"
                                            style="font-size:12px; color:{{ $record->status == '1' ? '#0f6848' : '#721c24' }};"></i>
                                        <span
                                            style="font-size:12.5px; font-weight:600;">{{ $record->status == '1' ? 'Released' : 'Impounded' }}</span>
                                        <i class="fa-solid fa-chevron-down ms-1"
                                            style="font-size:9px; opacity:0.7; color:{{ $record->status == '1' ? '#0f6848' : '#721c24' }};"></i>
                                    </span>
                                    <ul class="dropdown-menu dropdown-menu-sm shadow-sm border-0 py-1"
                                        style="border-radius:12px; padding:8px; min-width:auto; border:none; box-shadow:0 8px 24px rgba(0,0,0,0.15); background:#fff;">
                                        <li>
                                            <a class="dropdown-item status-option d-flex align-items-center gap-2 {{ $record->status == '0' ? 'active' : '' }}"
                                                href="#" data-value="0" data-id="{{ $record->id }}"
                                                style="border-radius:8px; padding:4px 8px; text-decoration:none; display:flex; align-items:center; gap:8px; {{ $record->status == '0' ? 'background:#f8f9fa;' : '' }} cursor:pointer; transition:background 0.15s;"
                                                onmouseover="this.style.background='#f8f9fa';"
                                                onmouseout="this.style.background='{{ $record->status == '0' ? '#f8f9fa' : 'transparent' }}';">
                                                <span
                                                    style="display:inline-flex; align-items:center; gap:7px; padding:4px 12px; font-size:11px; font-weight:600; border-radius:999px; white-space:nowrap; color:#721c24; background:#f8d7da; border:1px solid #dc3545; pointer-events:none; box-shadow:0 2px 4px rgba(0,0,0,0.08);">
                                                    <i class="fa-solid fa-lock" style="font-size:11px;"></i> Impounded
                                                </span>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item status-option d-flex align-items-center gap-2 {{ $record->status == '1' ? 'active' : '' }}"
                                                href="#" data-value="1" data-id="{{ $record->id }}"
                                                style="border-radius:8px; padding:4px 8px; text-decoration:none; display:flex; align-items:center; gap:8px; {{ $record->status == '1' ? 'background:#f8f9fa;' : '' }} cursor:pointer; transition:background 0.15s;"
                                                onmouseover="this.style.background='#f8f9fa';"
                                                onmouseout="this.style.background='{{ $record->status == '1' ? '#f8f9fa' : 'transparent' }}';">
                                                <span
                                                    style="display:inline-flex; align-items:center; gap:7px; padding:4px 12px; font-size:11px; font-weight:600; border-radius:999px; white-space:nowrap; color:#0f6848; background:#d4edda; border:1px solid #28a745; pointer-events:none; box-shadow:0 2px 4px rgba(0,0,0,0.08);">
                                                    <i class="fa-solid fa-lock-open" style="font-size:11px;"></i>
                                                    Released
                                                </span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>

                                <style>
                                    /* Minimal CSS for dropdown functionality */
                                    .dropdown-menu {
                                        position: absolute;
                                        z-index: 1000;
                                        display: none;
                                        float: left;
                                        min-width: 10rem;
                                        padding: 0.5rem 0;
                                        margin: 0.125rem 0 0;
                                        font-size: 1rem;
                                        color: #212529;
                                        text-align: left;
                                        list-style: none;
                                        background-color: #fff;
                                        background-clip: padding-box;
                                        border: 1px solid rgba(0, 0, 0, .15);
                                        border-radius: 0.25rem;
                                    }

                                    .dropdown-menu.show {
                                        display: block;
                                    }

                                    .dropdown-item {
                                        display: block;
                                        width: 100%;
                                        padding: 0.25rem 1.5rem;
                                        clear: both;
                                        font-weight: 400;
                                        color: #212529;
                                        text-align: inherit;
                                        text-decoration: none;
                                        white-space: nowrap;
                                        background-color: transparent;
                                        border: 0;
                                    }

                                    .dropdown-item:hover {
                                        background-color: #f8f9fa;
                                    }

                                    .dropdown-item.active {
                                        background-color: #f8f9fa;
                                    }

                                    .dropdown-toggle::after {
                                        display: none !important;
                                    }
                                </style>
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
                                                href="{{ route('impoundment.show', $record->id) }}">
                                                <i class="fa-solid fa-eye me-2 text-primary"></i> View
                                            </a>
                                        </li>
                                        {{-- file  --}}

                                        @if ($record->document_url)
                                            <li>
                                                <a class="dropdown-item" href="{{ $record->document_url }}"
                                                    target="_blank">
                                                    <i class="fa-solid fa-file-pdf me-2 text-danger"></i> View File
                                                </a>
                                            </li>
                                        @endif
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('impoundment.edit', $record->id) }}">
                                                <i class="fa-solid fa-pen-to-square me-2 text-success"></i> Edit
                                            </a>
                                        </li>
                                        <li>
                                            <form action="{{ route('impoundment.destroy', $record->id) }}"
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
    @push('auth_scripts')
        <script>
            $(document).ready(function() {

                $(document).on('click', '.status-option', function(e) {
                    e.preventDefault();

                    var $option = $(this);
                    var newValue = $option.data('value');
                    var id = $option.data('id');
                    var $dropdown = $option.closest('.status-dropdown');
                    var $chip = $dropdown.find('.dropdown-toggle');

                    // Bail if same status selected
                    if ($chip.data('status') == newValue) return;

                    var url = "{{ route('impoundment.status_update', ['impoundment' => '__ID__']) }}".replace(
                        '__ID__', id);

                    // Store previous status for rollback
                    var previousValue = $chip.data('status');

                    // Optimistic UI update
                    var isReleased = newValue == '1';

                    // Update chip colors and icons with inline styles
                    if (isReleased) {
                        $chip.css({
                            'color': '#0f6848',
                            'background': '#d4edda',
                            'border': '1px solid #28a745'
                        });
                        $chip.find('i:first').attr('class', 'fa-solid fa-lock-open').css('color', '#0f6848');
                        $chip.find('span:first').text('Released');
                        $chip.find('.fa-chevron-down').css('color', '#0f6848');
                    } else {
                        $chip.css({
                            'color': '#721c24',
                            'background': '#f8d7da',
                            'border': '1px solid #dc3545'
                        });
                        $chip.find('i:first').attr('class', 'fa-solid fa-lock').css('color', '#721c24');
                        $chip.find('span:first').text('Impounded');
                        $chip.find('.fa-chevron-down').css('color', '#721c24');
                    }

                    // Update data status
                    $chip.data('status', newValue);

                    // Update dropdown arrow visibility
                    var $dropdownMenu = $dropdown.find('.dropdown-menu');
                    $dropdownMenu.find('.status-option').removeClass('active');
                    $option.addClass('active');

                    // Update the dropdown items to show active state
                    $dropdownMenu.find('.status-option').each(function() {
                        var $opt = $(this);
                        if ($opt.data('value') == newValue) {
                            $opt.css('background', '#f8f9fa');
                        } else {
                            $opt.css('background', 'transparent');
                        }
                    });

                    // Close dropdown
                    $dropdownMenu.removeClass('show');

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            status: newValue,
                            _token: '{{ csrf_token() }}',
                            _method: 'POST'
                        },
                        success: function(response) {
                            if (response.success) {
                                var releaseDate = response.data?.release_date ?
                                    new Date(response.data.release_date).toISOString().split('T')[
                                    0] :
                                    '-';
                                $chip.closest('tr').find('.release_date').text(releaseDate);

                                // Show success toast
                                if (typeof toastr !== 'undefined') {
                                    toastr.success('Status updated successfully!');
                                } else {
                                    alert('Status updated successfully!');
                                }
                            }
                        },
                        error: function(xhr) {
                            // Revert on failure - restore previous status
                            var wasReleased = previousValue == '1';

                            if (wasReleased) {
                                $chip.css({
                                    'color': '#0f6848',
                                    'background': '#d4edda',
                                    'border': '1px solid #28a745'
                                });
                                $chip.find('i:first').attr('class', 'fa-solid fa-lock-open').css(
                                    'color', '#0f6848');
                                $chip.find('span:first').text('Released');
                                $chip.find('.fa-chevron-down').css('color', '#0f6848');
                            } else {
                                $chip.css({
                                    'color': '#721c24',
                                    'background': '#f8d7da',
                                    'border': '1px solid #dc3545'
                                });
                                $chip.find('i:first').attr('class', 'fa-solid fa-lock').css('color',
                                    '#721c24');
                                $chip.find('span:first').text('Impounded');
                                $chip.find('.fa-chevron-down').css('color', '#721c24');
                            }

                            // Restore data status
                            $chip.data('status', previousValue);

                            // Restore active state in dropdown
                            $dropdown.find('.dropdown-menu .status-option').each(function() {
                                var $opt = $(this);
                                if ($opt.data('value') == previousValue) {
                                    $opt.css('background', '#f8f9fa');
                                } else {
                                    $opt.css('background', 'transparent');
                                }
                            });

                            // Show error message
                            var msg = xhr.status === 403 ?
                                "You don't have permission to update status!" :
                                xhr.responseJSON?.message || 'Error updating status!';

                            if (typeof toastr !== 'undefined') {
                                toastr.error(msg);
                            } else {
                                alert(msg);
                            }
                        }
                    });
                });

                // Handle dropdown toggle with inline styles
                $(document).on('click', '.dropdown-toggle', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    var $dropdown = $(this).closest('.status-dropdown');
                    var $menu = $dropdown.find('.dropdown-menu');

                    // Toggle dropdown
                    $menu.toggleClass('show');
                });

                // Close dropdown when clicking outside
                $(document).on('click', function(e) {
                    if (!$(e.target).closest('.status-dropdown').length) {
                        $('.dropdown-menu').removeClass('show');
                    }
                });

            });
        </script>
    @endpush
</x-layouts.auth>
