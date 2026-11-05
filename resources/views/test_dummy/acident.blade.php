<x-layouts.auth page-title="{{__('Check Asset Details')}}" sub-title="">

    <x-slot:actionButton>
        <button class="btn secondary-color rounded-pill px-4 mt-md-0 mt-3" data-bs-toggle="modal"
            data-bs-target="#addVehicleModal">
            + Add New Asset
        </button>

        <x-auth.modal modal-id="addVehicleModal" modal-title="Create new asset">
            <div class="modal-body p-4">
                <x-auth.form form-id="addVehicleForm" form-action="{{route('vehicle.store')}}">
                    <div class="col-md-6">
                        <label class="small fw-bold mb-1">Asset Make</label>
                        <select class="form-select border-dark shadow-sm">
                            <option>Volkswagen</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="small fw-bold mb-1">Asset Model</label>
                        <select class="form-select border-dark shadow-sm">
                            <option>Voyage</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="small fw-bold mb-1">Trip - Originate</label>
                        <select class="form-select border-dark shadow-sm">
                            <option>Enugu</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="small fw-bold mb-1">Trip - Destination</label>
                        <select class="form-select border-dark shadow-sm">
                            <option>Festac Town</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="small fw-bold mb-1">Registration</label>
                        <div class="input-group">
                            <input type="text" class="form-control border-dark shadow-sm" value="123456" />
                            <span class="input-group-text bg-white border-dark"><i class="fas fa-caret-down"></i></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="small fw-bold mb-1">&nbsp;</label>
                        <input type="text" class="form-control border-dark shadow-sm" />
                    </div>

                    <div class="col-md-6">
                        <input type="text" class="form-control border-dark shadow-sm mt-2" />
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control border-dark shadow-sm mt-2" />
                    </div>

                    <div class="col-12 text-center mt-4">
                        <x-auth.input-button btn-class="" btn-type="submit" btn-value="Save Entry" />
                    </div>
                </x-auth.form>
            </div>
        </x-auth.modal>
    </x-slot:actionButton>

    <div class="row g-3 mb-4">
        <x-auth.quick-link-card-small icon="fa-truck" title="124" description="Total Asset" route="vehicle.index"
            iconbg="bg-primary" icontext="text-white" />

        <x-auth.quick-link-card-small icon="fa-road" title="24" description="Active Asset" route="vehicle.index"
            iconbg="bg-purple" icontext="text-white" />

        <x-auth.quick-link-card-small icon="fa-user" title="89" description="Active Drivers" route="vehicle.index"
            iconbg="bg-success" icontext="text-white" />

        <x-auth.quick-link-card-small icon="fa-wrench" title="8" description="In Maintenance" route="vehicle.index"
            iconbg="bg-danger" icontext="text-white" />
    </div>

    <div class="bg-white p-4 rounded-4 shadow-sm border">
        <h6 class="text-muted mb-3">Select Asset</h6>
        <select class="form-select mb-4 fw-bold border-2">
            <option>VH-789 - Volkswagen Voyage</option>
        </select>

        <div class="container-fluid bg-white p-0">
            <div class="d-flex detail-row">
                <div class="detail-label">Asset ID:</div>
                <div>VH-789</div>
            </div>
            <div class="d-flex detail-row">
                <div class="detail-label">Make/Model:</div>
                <div>Volkswagen Voyage</div>
            </div>
            <div class="d-flex detail-row">
                <div class="detail-label">Registration:</div>
                <div>KA-01-AB-1234</div>
            </div>
            <div class="d-flex detail-row">
                <div class="detail-label">Asset Type:</div>
                <div>Truck</div>
            </div>
            <div class="d-flex detail-row">
                <div class="detail-label">Current Status:</div>
                <div><span class="status-badge">Active</span></div>
            </div>
            <div class="d-flex detail-row">
                <div class="detail-label">Assigned Driver:</div>
                <div>John Doe (DRV-001)</div>
            </div>
            <div class="d-flex detail-row">
                <div class="detail-label">Last Service Date:</div>
                <div>15 Jan 2024</div>
            </div>
            <div class="d-flex detail-row">
                <div class="detail-label">Next Service Due:</div>
                <div>15 Mar 2024</div>
            </div>
            <div class="d-flex detail-row">
                <div class="detail-label">Current Location:</div>
                <div>Enugu Depot</div>
            </div>
            <div class="d-flex detail-row">
                <div class="detail-label">Fuel Level:</div>
                <div>85%</div>
            </div>
            <div class="d-flex detail-row">
                <div class="detail-label">Mileage:</div>
                <div>85,250 km</div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-4 p-4 shadow-sm mt-3">
        <h5 class="fw-bold mb-3">Asset Log Table</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Driver</th>
                        <th>Asset Make</th>
                        <th>Asset Model</th>
                        <th>Origin</th>
                        <th>Destination</th>
                        <th>Registration</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>2025-12-22</td>
                        <td>Zaid Ali</td>
                        <td>Volkswagen</td>
                        <td>Voyage</td>
                        <td>Enugu</td>
                        <td>Festac Town</td>
                        <td>123456</td>
                        <td><span class="badge bg-success">Active</span></td>
                        <td>
                            <button class="btn btn-sm btn-light border">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.auth>
