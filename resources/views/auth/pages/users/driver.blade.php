<x-layouts.auth page-title="{{__('Check Driver Details')}}" sub-title="">

    <x-slot:actionButton>
        <button class="btn secondary-color rounded-pill px-4 mt-md-0 mt-3" data-bs-toggle="modal"
            data-bs-target="#addDrivereModal">
            + Add New Driver
        </button>

        <x-auth.modal modal-id="addDrivereModal" modal-title="Create new Driver">
            <div class="modal-body p-4">
                <x-auth.form form-id="addDriverForm" form-action="{{route('users.store')}}">
                    <div class="col-12 mb-2">
                        <h6 class="fw-bold  border-bottom pb-2">
                            Personal Information
                        </h6>
                    </div>

                    <div class="col-md-6">
                        <label class="small fw-bold mb-1">Full Name</label>
                        <input type="text" class="form-control border-dark shadow-sm"
                            placeholder="Enter driver's name" />
                    </div>

                    <div class="col-md-6">
                        <label class="small fw-bold mb-1">Contact Number</label>
                        <input type="tel" class="form-control border-dark shadow-sm" placeholder="+92 XXX XXXXXXX" />
                    </div>

                    <div class="col-md-6">
                        <label class="small fw-bold mb-1">Date of Birth</label>
                        <input type="date" class="form-control border-dark shadow-sm" />
                    </div>

                    <div class="col-md-6">
                        <label class="small fw-bold mb-1">Blood Group</label>
                        <select class="form-select border-dark shadow-sm">
                            <option selected disabled>Select Group</option>
                            <option>A+</option>
                            <option>B+</option>
                            <option>O+</option>
                            <option>AB+</option>
                        </select>
                    </div>

                    <div class="col-12 mt-4 mb-2">
                        <h6 class="fw-bold border-bottom pb-2">
                            Professional Details
                        </h6>
                    </div>

                    <div class="col-md-6">
                        <label class="small fw-bold mb-1">License Number</label>
                        <input type="text" class="form-control border-dark shadow-sm" placeholder="e.g. PK-12345" />
                    </div>

                    <div class="col-md-6">
                        <label class="small fw-bold mb-1">License Expiry</label>
                        <input type="date" class="form-control border-dark shadow-sm" />
                    </div>

                    <div class="col-md-6">
                        <label class="small fw-bold mb-1">Experience (Years)</label>
                        <input type="number" class="form-control border-dark shadow-sm" placeholder="e.g. 5" />
                    </div>

                    <div class="col-md-6">
                        <label class="small fw-bold mb-1">Assign Initial Vehicle (Optional)</label>
                        <select class="form-select border-dark shadow-sm">
                            <option selected>None</option>
                            <option>VH-789 - Volkswagen Voyage</option>
                            <option>VH-456 - Hino Truck</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="small fw-bold mb-1">Residential Address</label>
                        <textarea class="form-control border-dark shadow-sm" rows="2"
                            placeholder="Full address..."></textarea>
                    </div>

                    <div class="col-12 text-center mt-4">
                        <x-auth.input-button btn-class="" btn-type="submit" btn-value="Save Entry" />
                    </div>
                </x-auth.form>
            </div>
        </x-auth.modal>
    </x-slot:actionButton>

    <div class="row g-3 mb-4">
        <x-auth.quick-link-card-small icon="fa-users" title="124" description="Total Drivers" route=""
            iconbg="bg-success" icontext="text-white" />

        <x-auth.quick-link-card-small icon="fa-car" title="24" description="On Duty" route="" iconbg="bg-primary"
            icontext="text-white" />

        <x-auth.quick-link-card-small icon="fa-star" title="89" description="Avg Rating" route="" iconbg="bg-warning"
            icontext="text-dark" />

        <x-auth.quick-link-card-small icon="fa-clock" title="8" description="Off Duty" route="" iconbg="bg-danger"
            icontext="text-white" />
    </div>

    <div class="bg-white p-4 rounded-4 shadow-sm border mb-4">
        <h6 class="text-muted mb-3">Select Driver</h6>
        <select class="form-select mb-4 fw-bold border-2">
            @foreach ($data['all'] as $driver)
            <option>{{$driver->driverid}} - {{$driver->full_name}}</option>
            @endforeach
        </select>

        <div class="row">
            <div class="col-md-2 text-center mb-3">
                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto shadow-sm"
                    style="
                          width: 120px;
                          height: 120px;
                          border: 3px solid var(--navy);
                        ">
                    <i class="fas fa-user-tie fa-4x text-muted"></i>
                </div>
                <span class="badge bg-success mt-2">Verified</span>
            </div>

            <div class="col-md-10">
                <div class="container-fluid p-0">
                    <div class="d-flex detail-row">
                        <div class="detail-label">Driver ID:</div>
                        <div>DRV-001</div>
                    </div>
                    <div class="d-flex detail-row">
                        <div class="detail-label">Full Name:</div>
                        <div>John Doe</div>
                    </div>
                    <div class="d-flex detail-row">
                        <div class="detail-label">License Number:</div>
                        <div>DL-987654321</div>
                    </div>
                    <div class="d-flex detail-row">
                        <div class="detail-label">Contact:</div>
                        <div>+92 300 1234567</div>
                    </div>
                    <div class="d-flex detail-row">
                        <div class="detail-label">Experience:</div>
                        <div>5 Years</div>
                    </div>
                    <div class="d-flex detail-row">
                        <div class="detail-label">Assigned Vehicle:</div>
                        <div>VH-789 (Volkswagen Voyage)</div>
                    </div>
                    <div class="d-flex detail-row">
                        <div class="detail-label">Blood Group:</div>
                        <div>B+ Positive</div>
                    </div>
                    <div class="d-flex detail-row">
                        <div class="detail-label">Home Address:</div>
                        <div>Plot 45, Street 10, Karachi</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-4 p-4 shadow-sm">
        <h5 class="fw-bold mb-3">Recent Trips & Performance</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Trip ID</th>
                        <th>Vehicle</th>
                        <th>Route</th>
                        <th>Status</th>
                        <th>Rating</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>2025-12-20</td>
                        <td>#TRP-502</td>
                        <td>VH-789</td>
                        <td>Karachi → Hyderabad</td>
                        <td><span class="badge bg-success">Completed</span></td>
                        <td><i class="fas fa-star text-warning"></i> 5.0</td>
                    </tr>
                    <tr>
                        <td>2025-12-18</td>
                        <td>#TRP-488</td>
                        <td>VH-789</td>
                        <td>Karachi → Sukkur</td>
                        <td><span class="badge bg-success">Completed</span></td>
                        <td><i class="fas fa-star text-warning"></i> 4.5</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.auth>