<div class="card border-0 shadow-sm rounded-4 section-card mt-4">
    <div class="card-body p-4">

        <div class="section-header mb-4">
            <div class="section-header-left">
                <div class="icon-tile amber">
                    <i class="ri-tools-line"></i>
                </div>
                <div>
                    <h6 class="section-title mb-0">{{ __('Asset Breakdowns') }}</h6>
                    <small class="text-muted">{{ __('Vehicle breakdown records') }}</small>
                </div>
            </div>
            <span class="chip chip-amber">{{ $records->count() }}</span>
        </div>

        <div class="table-responsive">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Location') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Repair Cost') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-end">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $record)
                    <tr>
                        <td>{{ $record->breakdown_date?->format('d M Y') ?? '-' }}</td>
                        <td>{{ $record->breakdown_location ?? '-' }}</td>
                        <td>{{ $record->breakdown_type ?? '-' }}</td>
                        <td>{{ $record->repair_cost ? number_format($record->repair_cost, 2) : '-' }}</td>
                        <td>
                            @php
                                $statusMap = ['0' => ['Pending','danger'], '1' => ['Resolved','success'], '2' => ['In Progress','warning']];
                                [$label, $color] = $statusMap[$record->status] ?? ['Unknown','secondary'];
                                $chipClass = match($color) {
                                    'danger'    => 'chip-red',
                                    'success'   => 'chip-green',
                                    'warning'   => 'chip-amber',
                                    'info'      => 'chip-cyan',
                                    default     => 'chip-slate',
                                };
                            @endphp
                            <span class="chip {{ $chipClass }}">{{ __($label) }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('vehicle_breakdown.show',$record->id) }}" class="btn btn-sm btn-gradient-blue rounded-pill px-3">
                                <i class="ri-eye-line me-1"></i>{{ __('View') }}
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .icon-tile {
        width: 46px; height: 46px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.35rem; flex-shrink: 0;
    }
    .icon-tile.amber { background: #fef3c7; color: #d97706; }
    .section-header {
        display: flex; align-items: center; justify-content: space-between;
        gap: 1rem; flex-wrap: wrap;
    }
    .section-header-left { display: flex; align-items: center; gap: 0.85rem; }
    .section-title { font-weight: 700; font-size: 1.05rem; color: #1e293b; }
    .chip {
        display: inline-flex; align-items: center;
        padding: 5px 12px; border-radius: 50px;
        font-size: 0.82rem; font-weight: 600;
    }
    .chip-red   { background: #fee2e2; color: #b91c1c; }
    .chip-green { background: #dcfce7; color: #15803d; }
    .chip-amber { background: #fef3c7; color: #b45309; }
    .chip-cyan  { background: #cffafe; color: #0e7490; }
    .chip-slate { background: #e2e8f0; color: #334155; }
    .section-card {
        background: #ffffff; border: 1px solid #e2e8f0 !important;
        transition: box-shadow 0.2s ease;
    }
    .section-card:hover { box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05) !important; }
    .modern-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .modern-table thead th {
        background: #f8fafc; color: #64748b; font-size: 0.72rem;
        text-transform: uppercase; letter-spacing: 0.6px;
        font-weight: 700; padding: 12px 14px; border: none;
        border-bottom: 2px solid #e2e8f0; white-space: nowrap;
    }
    .modern-table tbody td {
        padding: 12px 14px; border-bottom: 1px solid #f1f5f9;
        vertical-align: middle; font-size: 0.9rem; color: #1e293b;
    }
    .modern-table tbody tr:hover { background: #f8fafc; }
    .modern-table tbody tr:last-child td { border-bottom: none; }
    .btn-gradient-blue {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        border: none; color: #fff; font-weight: 600;
        transition: all 0.25s ease;
    }
    .btn-gradient-blue:hover {
        color: #fff; transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(59, 130, 246, 0.35);
    }
</style>
