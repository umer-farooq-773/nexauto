@props(['vehicle' => null, 'triggerId' => null, 'triggerText' => null, 'modalId' => null])

@php
    $triggerId = $triggerId ?? 'vehicle_detail_' . ($vehicle?->id ?? 'null');
    $triggerText = $triggerText ?? ($vehicle?->fleet_no ?? '-');
    $modalId = $modalId ?? 'vehicleModal_' . ($vehicle?->id ?? 'null');

    $statusBadge = 'secondary';
    $statusText = $vehicle->status ?? '-';
    if ($vehicle) {
        if ($vehicle->status == '1' || strtolower($vehicle->status) == 'active') {
            $statusBadge = 'active'; $statusText = 'Active';
        } elseif ($vehicle->status == '0' || strtolower($vehicle->status) == 'inactive') {
            $statusBadge = 'inactive'; $statusText = 'Inactive';
        } elseif (strtolower($vehicle->status) == 'impounded') {
            $statusBadge = 'impounded'; $statusText = 'Impounded';
        } elseif (strtolower($vehicle->status) == 'released') {
            $statusBadge = 'released'; $statusText = 'Released';
        }
    }

    // NOTE: adjust these relation/attribute names to match your actual model.
    $makeModelName = $vehicle?->vehicle_make_model?->name ?? $vehicle?->vehicleMakeModel?->name ?? '-';
    $vehicleTypeName = $vehicle?->vehicle_type?->name ?? '-';
    $vehicleTypeStatus = $vehicle?->vehicle_type?->status ?? null;
    $createdByName = $vehicle?->creator?->name ?? $vehicle?->created_by ?? '-';
@endphp

<!-- Trigger Link -->
<td>
    <a href="#"
       class="vd-trigger"
       id="{{ $triggerId }}"
       onclick="event.preventDefault(); openVehicleModal('{{ $modalId }}')">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
            <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/>
            <path d="M14 9h4l4 4v4a1 1 0 0 1-1 1h-2"/>
            <circle cx="7" cy="18" r="2"/>
            <circle cx="17" cy="18" r="2"/>
        </svg>
        {{ $triggerText }}
    </a>
</td>

<!-- Modal -->
<div class="modal fade vd-modal" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content vd-content">

            <!-- Header -->
            <div class="vd-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="vd-header-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/>
                            <path d="M14 9h4l4 4v4a1 1 0 0 1-1 1h-2"/>
                            <circle cx="7" cy="18" r="2"/>
                            <circle cx="17" cy="18" r="2"/>
                        </svg>
                    </div>
                    <div>
                        <h5 class="vd-title">Vehicle Details</h5>
                        <p class="vd-subtitle">Fleet&nbsp;#&nbsp;<strong>{{ $vehicle?->fleet_no ?? 'N/A' }}</strong></p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Body -->
            <div class="vd-body">
                @if($vehicle)
                    <div class="row g-3">
                        <!-- Left Column -->
                        <div class="col-lg-6 d-flex flex-column gap-3">

                            <div class="vd-card vd-accent-blue">
                                <div class="vd-card-head">
                                    <span class="vd-icon-badge vd-badge-blue">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    </span>
                                    <h6 class="vd-card-title">Manager Information</h6>
                                </div>
                                <div class="vd-field">
                                    <span class="vd-label">Full Name</span>
                                    <span class="vd-value">{{ trim(($vehicle->manager_first_name ?? '') . ' ' . ($vehicle->manager_last_name ?? '')) ?: '-' }}</span>
                                </div>
                                <div class="vd-field">
                                    <span class="vd-label">Email Address</span>
                                    <span class="vd-value">{{ $vehicle->manager_email ?? '-' }}</span>
                                </div>
                            </div>

                            <div class="vd-card vd-accent-green">
                                <div class="vd-card-head">
                                    <span class="vd-icon-badge vd-badge-green">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    </span>
                                    <h6 class="vd-card-title">Owner Information</h6>
                                </div>
                                <div class="vd-field">
                                    <span class="vd-label">Full Name</span>
                                    <span class="vd-value">{{ trim(($vehicle->owner_first_name ?? '') . ' ' . ($vehicle->owner_last_name ?? '')) ?: '-' }}</span>
                                </div>
                            </div>

                            @if($vehicle?->drivers && $vehicle?->drivers->count() > 0)
                                @foreach($vehicle->drivers as $driver)
                                <div class="vd-card vd-accent-purple">
                                    <div class="vd-card-head">
                                        <span class="vd-icon-badge vd-badge-purple">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                        </span>
                                        <h6 class="vd-card-title">Driver Information</h6>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="vd-field">
                                                <span class="vd-label">Full Name</span>
                                                <span class="vd-value">{{ trim(($driver->first_name ?? '') . ' ' . ($driver->last_name ?? '')) ?: '-' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="vd-field">
                                                <span class="vd-label">Phone Number</span>
                                                <span class="vd-value">{{ $driver->phone_number ?? '-' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="vd-field">
                                                <span class="vd-label">License Number</span>
                                                <span class="vd-value">{{ $driver->drivers_license_number ?? '-' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="vd-field">
                                                <span class="vd-label">License Class</span>
                                                <span class="vd-value">{{ $driver->license_category_class ?? '-' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="vd-field">
                                                <span class="vd-label">License Expiry</span>
                                                <span class="vd-value">{{ $driver->license_expiry_date ?? '-' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="vd-field">
                                                <span class="vd-label">Date Employed</span>
                                                <span class="vd-value">{{ $driver->date_employed ?? '-' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="vd-field">
                                                <span class="vd-label">Emergency Contact</span>
                                                <span class="vd-value">{{ $driver->emergency_contact_name ?? '-' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="vd-field">
                                                <span class="vd-label">Emergency Phone</span>
                                                <span class="vd-value">{{ $driver->emergency_contact_phone_number ?? '-' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="vd-field">
                                                <span class="vd-label">National ID/Passport</span>
                                                <span class="vd-value">{{ $driver->national_id_passport_number ?? '-' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="vd-field">
                                                <span class="vd-label">BVN Number</span>
                                                <span class="vd-value">{{ $driver->bvn_number ?? '-' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="vd-field">
                                                <span class="vd-label">Employer Number</span>
                                                <span class="vd-value">{{ $driver->employer_number ?? '-' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="vd-field">
                                                <span class="vd-label">Address</span>
                                                <span class="vd-value">{{ $driver->address ?? '-' }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    @if($driver?->mates && $driver->mates->count() > 0)
                                        @foreach($driver->mates as $mate)
                                        <div class="vd-submate">
                                            <div class="vd-submate-head">
                                                <span class="vd-icon-badge vd-badge-indigo vd-icon-badge-sm">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                                </span>
                                                <span class="vd-submate-title">Driver Mate</span>
                                                @if(!empty($mate->status))
                                                <span class="vd-mini-badge {{ strtolower($mate->status) == 'active' || $mate->status == '1' ? 'vd-mini-active' : 'vd-mini-inactive' }}">
                                                    {{ strtolower($mate->status) == 'active' || $mate->status == '1' ? 'Active' : 'Inactive' }}
                                                </span>
                                                @endif
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-6">
                                                    <div class="vd-field">
                                                        <span class="vd-label">Full Name</span>
                                                        <span class="vd-value">{{ trim(($mate->first_name ?? '') . ' ' . ($mate->last_name ?? '')) ?: '-' }}</span>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="vd-field">
                                                        <span class="vd-label">Phone Number</span>
                                                        <span class="vd-value">{{ $mate->phone_number ?? '-' }}</span>
                                                    </div>
                                                </div>
                                                @if(!empty($mate->date_of_birth))
                                                <div class="col-6">
                                                    <div class="vd-field">
                                                        <span class="vd-label">Date of Birth</span>
                                                        <span class="vd-value">{{ $mate->date_of_birth }}</span>
                                                    </div>
                                                </div>
                                                @endif
                                                @if(!empty($mate->date_employed))
                                                <div class="col-6">
                                                    <div class="vd-field">
                                                        <span class="vd-label">Date Employed</span>
                                                        <span class="vd-value">{{ $mate->date_employed }}</span>
                                                    </div>
                                                </div>
                                                @endif
                                                @if(!empty($mate->national_id_passport_number))
                                                <div class="col-6">
                                                    <div class="vd-field">
                                                        <span class="vd-label">National ID/Passport</span>
                                                        <span class="vd-value">{{ $mate->national_id_passport_number }}</span>
                                                    </div>
                                                </div>
                                                @endif
                                                @if(!empty($mate->emergency_contact_name))
                                                <div class="col-6">
                                                    <div class="vd-field">
                                                        <span class="vd-label">Emergency Contact</span>
                                                        <span class="vd-value">{{ $mate->emergency_contact_name }}</span>
                                                    </div>
                                                </div>
                                                @endif
                                                @if(!empty($mate->emergency_contact_phone_number))
                                                <div class="col-6">
                                                    <div class="vd-field">
                                                        <span class="vd-label">Emergency Phone</span>
                                                        <span class="vd-value">{{ $mate->emergency_contact_phone_number }}</span>
                                                    </div>
                                                </div>
                                                @endif
                                                @if(!empty($mate->address))
                                                <div class="col-12">
                                                    <div class="vd-field">
                                                        <span class="vd-label">Address</span>
                                                        <span class="vd-value">{{ $mate->address }}</span>
                                                    </div>
                                                </div>
                                                @endif
                                                @if(!empty($mate->pivot?->assigned_at))
                                                <div class="col-12">
                                                    <div class="vd-field">
                                                        <span class="vd-label">Assigned At</span>
                                                        <span class="vd-value">{{ $mate->pivot->assigned_at }}</span>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        @endforeach
                                    @endif
                                </div>
                                @endforeach
                            @endif

                            <div class="vd-card vd-accent-cyan">
                                <div class="vd-card-head">
                                    <span class="vd-icon-badge vd-badge-cyan">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M14 9h4l4 4v4a1 1 0 0 1-1 1h-2"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/></svg>
                                    </span>
                                    <h6 class="vd-card-title">Vehicle Information</h6>
                                </div>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="vd-field">
                                            <span class="vd-label">Truck #</span>
                                            <span class="vd-value">{{ $vehicle->truck_no ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="vd-field">
                                            <span class="vd-label">Vehicle Type</span>
                                            <span class="vd-value">{{ $vehicleTypeName }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="vd-field">
                                            <span class="vd-label">Make &amp; Model</span>
                                            <span class="vd-value">{{ $makeModelName }}</span>
                                        </div>
                                    </div>
                                    @if(!is_null($vehicleTypeStatus))
                                    <div class="col-6">
                                        <div class="vd-field">
                                            <span class="vd-label">Type Status</span>
                                            <span class="vd-value">
                                                <span class="vd-mini-badge {{ strtolower($vehicleTypeStatus) == 'active' || $vehicleTypeStatus == '1' ? 'vd-mini-active' : 'vd-mini-inactive' }}">
                                                    {{ strtolower($vehicleTypeStatus) == 'active' || $vehicleTypeStatus == '1' ? 'Active' : 'Inactive' }}
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                    @endif
                                    <div class="col-12">
                                        <div class="vd-field">
                                            <span class="vd-label">Vehicle ID</span>
                                            <span class="vd-value vd-mono text-truncate d-block">{{ $vehicle->id ?? '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-lg-6 d-flex flex-column gap-3">

                            <div class="vd-card vd-accent-amber">
                                <div class="vd-card-head">
                                    <span class="vd-icon-badge vd-badge-amber">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                                    </span>
                                    <h6 class="vd-card-title">Important Dates</h6>
                                </div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="vd-date-box">
                                            <span class="vd-label">Last Service</span>
                                            <span class="vd-value">{{ $vehicle->last_service_date ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="vd-date-box">
                                            <span class="vd-label">Next Service Due</span>
                                            <span class="vd-value">{{ $vehicle->next_service_due_date ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="vd-date-box">
                                            <span class="vd-label">Insurance Expiry</span>
                                            <span class="vd-value">{{ $vehicle->insurance_expiry_date ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="vd-date-box">
                                            <span class="vd-label">License Expiry</span>
                                            <span class="vd-value">{{ $vehicle->vehicle_license_expiry_date ?? '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="vd-card vd-accent-red">
                                <div class="vd-card-head">
                                    <span class="vd-icon-badge vd-badge-red">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                    </span>
                                    <h6 class="vd-card-title">Location &amp; Status</h6>
                                </div>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="vd-field">
                                            <span class="vd-label">Base Site</span>
                                            <span class="vd-value">{{ $vehicle->base_site ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <span class="vd-label d-block mb-1">Current Status</span>
                                        <span class="vd-status vd-status-{{ $statusBadge }}">
                                            <span class="vd-status-dot"></span>{{ $statusText }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="vd-card vd-accent-slate">
                                <div class="vd-card-head">
                                    <span class="vd-icon-badge vd-badge-slate">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                    </span>
                                    <h6 class="vd-card-title">Record Info</h6>
                                </div>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="vd-field">
                                            <span class="vd-label">Created By</span>
                                            <span class="vd-value">{{ $createdByName }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="vd-field">
                                            <span class="vd-label">Created At</span>
                                            <span class="vd-value">{{ $vehicle->created_at?->format('Y-m-d H:i') ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="vd-field">
                                            <span class="vd-label">Last Updated</span>
                                            <span class="vd-value">{{ $vehicle->updated_at?->format('Y-m-d H:i') ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="vd-field">
                                            <span class="vd-label">Driver(s) Assigned</span>
                                            <span class="vd-value">{{ $vehicle?->drivers?->count() ?? 0 }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="vd-empty">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M14 9h4l4 4v4a1 1 0 0 1-1 1h-2"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/></svg>
                        <p>No vehicle information available</p>
                    </div>
                @endif
            </div>

            <!-- Footer -->
            <div class="vd-footer">
                <span class="vd-footer-id">Vehicle ID: {{ $vehicle->id ?? '-' }}</span>
                <button type="button" class="btn vd-btn-close" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.vd-trigger{
    display:inline-flex;align-items:center;color:#2a5298;font-weight:600;
    text-decoration:none;transition:color .15s;
}
.vd-trigger:hover{color:#1e3c72;text-decoration:underline;}

.vd-modal .vd-content{border:0;border-radius:16px;overflow:hidden;box-shadow:0 20px 50px rgba(0,0,0,.2);}

.vd-header{
    display:flex;align-items:center;justify-content:space-between;
    padding:22px 28px;background:linear-gradient(135deg,#1e3c72 0%,#2a5298 100%);
}
.vd-header-icon{
    width:48px;height:48px;border-radius:50%;background:rgba(255,255,255,.15);
    display:flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0;
}
.vd-title{color:#fff;font-weight:700;font-size:1.15rem;margin:0;}
.vd-subtitle{color:rgba(255,255,255,.75);font-size:.85rem;margin:2px 0 0;}
.vd-subtitle strong{color:#fff;}

.vd-body{background:#f4f6f9;padding:24px;}

.vd-card{
    background:#fff;border-radius:12px;padding:18px 20px;
    box-shadow:0 1px 3px rgba(0,0,0,.06);border-left:3px solid transparent;
    transition:box-shadow .15s, transform .15s;
}
.vd-card:hover{box-shadow:0 6px 16px rgba(0,0,0,.08);transform:translateY(-1px);}
.vd-accent-blue{border-left-color:#2a5298;}
.vd-accent-green{border-left-color:#1e8e5a;}
.vd-accent-cyan{border-left-color:#0891b2;}
.vd-accent-amber{border-left-color:#d97706;}
.vd-accent-red{border-left-color:#dc2626;}
.vd-accent-slate{border-left-color:#64748b;}
.vd-accent-purple{border-left-color:#7c3aed;}

.vd-card-head{display:flex;align-items:center;gap:10px;margin-bottom:14px;padding-bottom:12px;border-bottom:1px solid #eef1f5;}
.vd-icon-badge{
    width:32px;height:32px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.vd-badge-blue{background:#e8edfa;color:#2a5298;}
.vd-badge-green{background:#e5f5ec;color:#1e8e5a;}
.vd-badge-cyan{background:#e0f4f8;color:#0891b2;}
.vd-badge-amber{background:#fdf1de;color:#d97706;}
.vd-badge-red{background:#fbe6e6;color:#dc2626;}
.vd-badge-slate{background:#eef1f5;color:#64748b;}
.vd-badge-purple{background:#f1e9fd;color:#7c3aed;}
.vd-badge-indigo{background:#e7e9fc;color:#4338ca;}
.vd-card-title{font-weight:700;font-size:.92rem;color:#1f2937;margin:0;}

.vd-submate{
    background:#f8f9fd;border:1px dashed #d8dcf0;border-radius:10px;
    padding:14px 16px;margin-top:14px;
}
.vd-submate-head{display:flex;align-items:center;gap:8px;margin-bottom:12px;}
.vd-submate-title{font-weight:700;font-size:.8rem;color:#4338ca;text-transform:uppercase;letter-spacing:.04em;}
.vd-icon-badge-sm{width:26px;height:26px;border-radius:7px;}

.vd-field{margin-bottom:12px;}
.vd-field:last-child{margin-bottom:0;}
.vd-label{
    display:block;font-size:.68rem;font-weight:700;letter-spacing:.06em;
    text-transform:uppercase;color:#9aa3af;margin-bottom:3px;
}
.vd-value{font-size:.94rem;font-weight:600;color:#1f2937;}
.vd-mono{font-family:'SFMono-Regular',Consolas,monospace;font-size:.82rem;color:#4b5563;}

.vd-date-box{background:#f8fafc;border:1px solid #eef1f5;border-radius:9px;padding:10px 12px;height:100%;}

.vd-status{
    display:inline-flex;align-items:center;gap:6px;padding:6px 14px;
    border-radius:999px;font-weight:700;font-size:.85rem;
}
.vd-status-dot{width:7px;height:7px;border-radius:50%;background:currentColor;}
.vd-status-active{background:#e5f5ec;color:#1e8e5a;}
.vd-status-inactive{background:#fbe6e6;color:#dc2626;}
.vd-status-impounded{background:#fdf1de;color:#d97706;}
.vd-status-released{background:#e0f4f8;color:#0891b2;}
.vd-status-secondary{background:#eef1f5;color:#6b7280;}

.vd-mini-badge{display:inline-block;padding:2px 10px;border-radius:999px;font-size:.75rem;font-weight:700;}
.vd-mini-active{background:#e5f5ec;color:#1e8e5a;}
.vd-mini-inactive{background:#fbe6e6;color:#dc2626;}

.vd-empty{text-align:center;padding:60px 0;color:#9aa3af;}
.vd-empty svg{margin-bottom:12px;}

.vd-footer{
    display:flex;align-items:center;justify-content:space-between;
    padding:14px 24px;background:#fff;border-top:1px solid #eef1f5;
}
.vd-footer-id{font-size:.8rem;color:#9aa3af;}
.vd-btn-close{
    background:#2a5298;color:#fff;font-weight:600;border:0;border-radius:8px;
    padding:8px 20px;font-size:.88rem;transition:background .15s;
}
.vd-btn-close:hover{background:#1e3c72;color:#fff;}
</style>

<script>
    function openVehicleModal(modalId) {
        const modalElement = document.getElementById(modalId);
        if (modalElement) {
            const existingModal = bootstrap.Modal.getInstance(modalElement);
            if (existingModal) existingModal.dispose();
            new bootstrap.Modal(modalElement, { backdrop: 'static', keyboard: true }).show();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.vd-modal').forEach(function(modalEl) {
            new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: true });
        });
    });
</script>
