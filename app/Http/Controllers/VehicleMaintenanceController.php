<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesIncidentWorkflow;
use App\Dto\VehicleMaintenanceDto;
use App\Interfaces\VehicleMaintenanceInterface;
use App\Interfaces\VehicleInterface;
use App\Http\Requests\VehicleMaintenanceRequest;
use App\Models\File;
use App\Models\PaymentHistory;
use App\Models\User;
use App\Models\VehicleMaintenance;
use App\Models\VehicleMaintenanceItem;
use App\Models\VehicleMaintenanceType;
use App\Services\VehicleMaintenanceItemService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Str;
class VehicleMaintenanceController extends BaseController
{
    use HandlesIncidentWorkflow;

    public function __construct(VehicleMaintenanceInterface $repo)
    {
        $this->setRepo($repo, 'auth.pages.maintenance', 'maintenance');
    }


    // ... other methods

    /**
     * Display all files for a maintenance record
     */
    public function maintenanceFiles(VehicleMaintenance $maintenance)
    {
        $files = $maintenance->files()
            ->where('type', 'maintenance_file')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Make sure this returns the files view, not the all view
        return view('auth.pages.maintenance.files', compact('maintenance', 'files'));
    }

    /**
     * Store a new file for the maintenance record
     */
    public function storeMaintenanceFiles(Request $request, VehicleMaintenance $maintenance)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'file' => 'required|file|max:10240',
        ]);

        if ($request->hasFile('file')) {
            $uploadedFile = $request->file('file');
            $originalName = $uploadedFile->getClientOriginalName();

            $fileName = Str::random(4) . '_' . time() . '_' . $originalName;

            $path = $uploadedFile->storeAs(
                'maintenance/' . $maintenance->id,
                $fileName,
                'public'
            );

            File::create([
                'id' => Str::uuid(),
                'name' => $request->input('name', $originalName),
                'path' => $path,
                'file_type' => VehicleMaintenance::class,
                'file_id' => $maintenance->id,
                'type' => 'maintenance_file',
                'status' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect()
                ->route('maintenance.file.index', $maintenance->id)
                ->with('success', 'File uploaded successfully!');
        }

        return redirect()
            ->back()
            ->with('error', 'No file selected for upload.');
    }

    /**
     * Replace an existing file
     */
    public function replace(Request $request, VehicleMaintenance $maintenance, File $file)
    {
        if ($file->file_id !== $maintenance->id || $file->file_type !== VehicleMaintenance::class) {
            return redirect()
                ->back()
                ->with('error', 'Invalid file record.');
        }

        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        if (Storage::disk('public')->exists($file->path)) {
            Storage::disk('public')->delete($file->path);
        }

        if ($request->hasFile('file')) {
            $uploadedFile = $request->file('file');
            $originalName = $uploadedFile->getClientOriginalName();

            $fileName = Str::random(4) . '_' . time() . '_' . $originalName;

            $path = $uploadedFile->storeAs(
                'maintenance/' . $maintenance->id,
                $fileName,
                'public'
            );

            $file->update([
                'name' => $request->input('name', $originalName),
                'path' => $path,
                'updated_at' => now(),
            ]);

            return redirect()
                ->route('maintenance.file.index', $maintenance->id)
                ->with('success', 'File replaced successfully!');
        }

        return redirect()
            ->back()
            ->with('error', 'No file selected for replacement.');
    }

    /**
     * Delete a file
     */
    public function deleteFile(VehicleMaintenance $maintenance, File $file)
    {
        if ($file->file_id !== $maintenance->id || $file->file_type !== VehicleMaintenance::class) {
            return redirect()
                ->back()
                ->with('error', 'Invalid file record.');
        }

        if (Storage::disk('public')->exists($file->path)) {
            Storage::disk('public')->delete($file->path);
        }

        $file->delete();

        return redirect()
            ->route('maintenance.file.index', $maintenance->id)
            ->with('success', 'File deleted successfully!');
    }


    public function index()
    {
        $data['all'] = $this->_repo->index();

        $all = $this->_repo->all();
        $data['total_count']     = count($all);
        $data['completed_count'] = count($all->where('status', '1'));
        $data['pending_count']   = count($all->where('status', '0'));
        $data['inprogress_count'] = count($all->where('status', '2'));

        $data['vehicles'] = app()->make(VehicleInterface::class)->all();
        $data['maintenanceTypes'] = VehicleMaintenanceType::where('status', true)->orderBy('name')->get();
        return view($this->_directory . '.all', compact('data'));
    }

    public function history(Request $request)
    {
        $query = VehicleMaintenanceItem::query()
            ->with([
                'maintenance.vehicle.drivers',
                'maintenance.vehicle.vehicle_make_model',
                'maintenance.maintenanceTypes',
            ]);

        // Filters
        if ($request->filled('vehicle_id') && $request->vehicle_id !== 'all') {
            $query->whereHas('maintenance', function ($q) use ($request) {
                $q->where('vehicle_id', $request->vehicle_id);
            });
        }

        if ($request->filled('maintenance_type_id') && $request->maintenance_type_id !== 'all') {
            $query->whereHas('maintenance.maintenanceTypes', function ($q) use ($request) {
                $q->where('vehicle_maintenance_types.id', $request->maintenance_type_id);
            });
        }

        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        if ($request->filled('action') && $request->action !== 'all') {
            $query->where('action', $request->action);
        }

        if ($request->filled('date_from')) {
            $query->where(function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->date_from)
                  ->orWhereHas('maintenance', function ($mq) use ($request) {
                      $mq->whereDate('maintenance_date', '>=', $request->date_from);
                  });
            });
        }

        if ($request->filled('date_to')) {
            $query->where(function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->date_to)
                  ->orWhereHas('maintenance', function ($mq) use ($request) {
                      $mq->whereDate('maintenance_date', '<=', $request->date_to);
                  });
            });
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('size', 'like', "%{$search}%")
                    ->orWhere('old_serial_no', 'like', "%{$search}%")
                    ->orWhere('new_serial_no', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('axle', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('maintenance.vehicle', function ($vq) use ($search) {
                        $vq->where('truck_no', 'like', "%{$search}%")
                            ->orWhere('fleet_no', 'like', "%{$search}%");
                    })
                    ->orWhereHas('maintenance', function ($mq) use ($search) {
                        $mq->where('reference_no', 'like', "%{$search}%")
                            ->orWhere('service_provider', 'like', "%{$search}%");
                    });
            });
        }

        $items = $query->orderBy('created_at', 'desc')->get();

        // Overall stats
        $stats = [
            'total_items'                => VehicleMaintenanceItem::count(),
            'total_maintenance_records'  => VehicleMaintenance::count(),
            'total_cost'                 => VehicleMaintenanceItem::sum('cost') ?? 0,
            'tyre_count'                 => VehicleMaintenanceItem::where('category', 'tyre')->count(),
            'battery_count'              => VehicleMaintenanceItem::where('category', 'battery')->count(),
            'telematics_count'           => VehicleMaintenanceItem::where('category', 'telematics')->count(),
            'brake_count'                => VehicleMaintenanceItem::where('category', 'brake')->count(),
            'engine_count'               => VehicleMaintenanceItem::where('category', 'engine')->count(),
            'general_count'              => VehicleMaintenanceItem::whereIn('category', ['general', 'other'])->count(),
        ];

        $vehicles = app()->make(VehicleInterface::class)->all();
        $maintenanceTypes = VehicleMaintenanceType::where('status', true)->orderBy('name')->get();

        return view($this->_directory . '.history', compact('items', 'stats', 'vehicles', 'maintenanceTypes'));
    }

    /**
     * Dedicated View History Page for a single maintenance history item.
     */
    public function historyShow($id)
    {
        $item = VehicleMaintenanceItem::with([
            'maintenance.vehicle.drivers',
            'maintenance.vehicle.vehicle_make_model',
            'maintenance.vehicle.activeComponents',
            'maintenance.vehicle.componentHistory',
            'maintenance.maintenanceTypes',
            'maintenance.maintenanceFiles',
            'maintenance.items',
        ])->findOrFail($id);

        $vehicles = app()->make(VehicleInterface::class)->all();
        $maintenanceTypes = VehicleMaintenanceType::where('status', true)->orderBy('name')->get();

        return view($this->_directory . '.history-show', compact('item', 'vehicles', 'maintenanceTypes'));
    }

    public function show($id)
    {
        $data = $this->_repo->show($id);
        $data->load([
            'maintenanceTypes',
            'maintenanceFiles',
            'items',
            'vehicle.activeComponents',
            'vehicle.componentHistory',
            'vehicle.vehicle_make_model',
            'vehicle.drivers'
        ]);

        $approved_by = null;
        if ($data->client_approved_by) {
            $approved_by = User::where('id', $data->client_approved_by)->select('first_name', 'last_name')->first();
        }

        $payment_paid_by = null;
        if ($data->created_by) {
            $payment_paid_by = User::where('id', $data->created_by)->select('first_name', 'last_name')->first();
        }

        $payments = PaymentHistory::where('payable_type', get_class($data))->where('payable_id', $data->id)->orderBy('created_at', 'desc')->get();

        if (!$data) abort(404);
        return view($this->_directory . '.show', compact('data','approved_by','payment_paid_by','payments'));
    }

    public function edit($id)
    {
        $data = $this->_repo->show($id);
        if (!$data) abort(404);

        if ((string)($data->payment_status ?? '') === '2') {
            return redirect()->route($this->_route . '.show', $data->id)
                ->with('error', __('This maintenance record is locked because payment has already been processed.'));
        }

        $data->load(['maintenanceFiles', 'maintenanceTypes', 'items']);
        $vehicles = app()->make(VehicleInterface::class)->all();
        $maintenanceTypes = \App\Models\VehicleMaintenanceType::where('status', true)->orderBy('name')->get();
        return view($this->_directory . '.edit', compact('data', 'vehicles', 'maintenanceTypes'));
    }

    public function store(VehicleMaintenanceRequest $request, VehicleMaintenanceItemService $itemService)
    {
        try {
            $dto = VehicleMaintenanceDto::fromRequest(
                array_merge(
                    $request->validated(),
                    ['maintenance_files' => $request->file('maintenance_files') ?? []]
                )
            );

            $record = $this->_repo->store($dto);

            // Sync maintenance items if provided in create form
            if ($request->filled('items') || !empty($request->input('items'))) {
                $itemService->syncMaintenanceItems(
                    $record,
                    $request->input('items', []),
                    $request->file('item_photos', [])
                );
            }

            $vehicleNo = $record->vehicle?->truck_no ?? 'N/A';

            // Notification is isolated so a failure never blocks the success response
            try {
                $this->notifyRecordCreated(
                    'Vehicle Maintenance',
                    route($this->_route . '.show', $record),
                    $vehicleNo
                );
            } catch (\Throwable $notifyEx) {
                Log::warning('Maintenance create notification failed: ' . $notifyEx->getMessage(), [
                    'record_id' => $record->id,
                ]);
            }

            return redirect()->route($this->_route . '.show', $record->id)->with('success', __('language.successfully_created'));
        } catch (\Throwable $th) {
            Log::error('Maintenance store error: ' . $th->getMessage(), ['exception' => $th]);
            return redirect()->route($this->_route . '.index')->with('error', __('language.something_went_wrong'));
        }
    }

    public function update(VehicleMaintenanceRequest $request, $id, VehicleMaintenanceItemService $itemService)
    {
        try {
            $existing = $this->_repo->show($id);
            if (!$existing) abort(404);

            if ((string)($existing->payment_status ?? '') === '2') {
                return redirect()->route($this->_route . '.show', $existing->id)
                    ->with('error', __('This maintenance record is locked because payment has already been processed.'));
            }

            if ($blocked = $this->checkStatusChangePermission($request, $existing)) {
                return $blocked;
            }

            $dto = VehicleMaintenanceDto::fromRequest(
                array_merge(
                    $request->validated(),
                    ['maintenance_files' => $request->file('maintenance_files') ?? []]
                )
            );

            $this->_repo->update($id, $dto);

            // Sync maintenance items from edit form
            $itemService->syncMaintenanceItems(
                $existing,
                $request->input('items', []),
                $request->file('item_photos', []),
                $request->input('deleted_item_ids', [])
            );

            // If the record was previously approved, reset approval so Client Director must re-approve and payment button is hidden
            $wasApproved = (bool) ($existing->client_approved ?? false);
            if ($wasApproved) {
                $itemService->resetApprovalAndNotify($existing);
                return redirect()->route($this->_route . '.show', $existing->id)
                    ->with('success', __('Maintenance record updated successfully. Re-submitted for Client Director approval.'));
            }

            return redirect()->route($this->_route . '.show', $existing->id)->with('success', __('language.updated_successfully'));
        } catch (\Throwable $th) {
            Log::error('Maintenance update error: ' . $th->getMessage(), ['exception' => $th]);
            if ($th instanceof NotFoundHttpException) {
                return redirect()->route($this->_route . '.index')->with('error', $th->getMessage());
            }
            return redirect()->route($this->_route . '.index')->with('error', __('language.something_went_wrong'));
        }
    }


    public function statusUpdate(\Illuminate\Http\Request $request, $id)
    {
        return $this->handleStatusUpdate($request, $id, 'Vehicle Maintenance', 'vehicle_maintenance', ['0', '1', '2'], " ", "client_approved_at");
    }

    public function clientApprove(\Illuminate\Http\Request $request, $id)
    {
        return $this->handleClientApprove($request, $id, 'Vehicle Maintenance', 'vehicle_maintenance');
    }

    public function paymentProcess(\Illuminate\Http\Request $request, $id)
    {
        return $this->handlePaymentProcess($request, $id, 'Vehicle Maintenance', ['allowed_statuses'      => ['0', '1', '2'],'date_column' => 'resolved_date','status_that_sets_date' => '1',]);
        return $this->handlePaymentProcess($request, $id, 'Vehicle Maintenance');
    }
}
