<?php

namespace App\Http\Controllers;

use App\Models\VehicleComponent;
use App\Models\VehicleMaintenance;
use App\Models\VehicleMaintenanceItem;
use App\Services\VehicleMaintenanceItemService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VehicleMaintenanceItemController extends Controller
{
    public function store(Request $request, VehicleMaintenance $maintenance, VehicleMaintenanceItemService $itemService)
    {
        if ((string)($maintenance->payment_status ?? '') === '2') {
            return redirect()->back()
                ->with('error', __('Cannot add maintenance items. This record is locked because payment has already been processed.'));
        }

        // Debug logging for live environment
        Log::info('=== MAINTENANCE ITEM STORE DEBUG ===');
        Log::info('Category:', ['category' => $request->input('category')]);
        Log::info('Raw request data:', $request->except(['_token', 'photo']));
        
        $this->normalizeCategoryInputs($request);
        
        Log::info('After normalization:', $request->except(['_token', 'photo']));

        try {
            $validated = $request->validate([
                'category'        => ['required', 'string', 'in:tyre,battery,telematics,brake,engine,general,other'],
                'action'          => ['required', 'string'],
                'location'        => ['nullable', 'string', 'max:255'],
                'axle'            => ['nullable', 'string', 'max:255'],
                'position'        => ['nullable', 'string', 'max:255'],
                'item_name'       => ['nullable', 'string', 'max:255'],
                'brand'           => ['nullable', 'string', 'max:255'],
                'model'           => ['nullable', 'string', 'max:255'],
                'size'            => ['nullable', 'string', 'max:255'],
                'old_serial_no'   => ['nullable', 'string', 'max:255'],
                'new_serial_no'   => ['nullable', 'string', 'max:255'],
                'tread_depth_mm'  => ['nullable', 'numeric', 'min:0', 'max:1000'],
                'reason'          => ['nullable', 'string', 'max:255'],
                'quantity'        => ['nullable', 'integer', 'min:1'],
                'unit_cost'       => ['nullable', 'numeric', 'min:0'],
                'cost'            => ['nullable', 'numeric', 'min:0'],
                'notes'           => ['nullable', 'string'],
                'photo'           => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx', 'max:10240'],
            ]);
            
            Log::info('Validation passed', ['validated' => $validated]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation FAILED', ['errors' => $e->errors()]);
            throw $e;
        }

        try {
            DB::beginTransaction();

            $photoPath = null;
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $filename = 'item_' . Str::random(6) . '_' . time() . '.' . $file->getClientOriginalExtension();
                $photoPath = $file->storeAs('maintenance/' . $maintenance->id . '/items', $filename, 'public');
            }

            // Calculate formatted location if axle + position provided
            $location = $validated['location'] ?? null;
            if (empty($location)) {
                if (!empty($validated['axle']) && !empty($validated['position'])) {
                    $location = $validated['axle'] . ' – ' . $validated['position'];
                } elseif (!empty($validated['axle'])) {
                    $location = $validated['axle'];
                } elseif (!empty($validated['position'])) {
                    $location = $validated['position'];
                }
            }

            $cost = $validated['cost'] ?? null;
            if (empty($cost) && !empty($validated['unit_cost'])) {
                $cost = $validated['unit_cost'] * ($validated['quantity'] ?? 1);
            }

            Log::info('About to create item', [
                'maintenance_id' => $maintenance->id,
                'category' => $validated['category'],
                'location' => $location,
                'cost' => $cost
            ]);

            $item = VehicleMaintenanceItem::create([
                'vehicle_maintenance_id' => $maintenance->id,
                'category'               => $validated['category'],
                'action'                 => $validated['action'],
                'location'               => $location,
                'axle'                   => $validated['axle'] ?? null,
                'position'               => $validated['position'] ?? null,
                'item_name'              => $validated['item_name'] ?? ucfirst($validated['category']),
                'brand'                  => $validated['brand'] ?? null,
                'model'                  => $validated['model'] ?? null,
                'size'                   => $validated['size'] ?? null,
                'old_serial_no'          => $validated['old_serial_no'] ?? null,
                'new_serial_no'          => $validated['new_serial_no'] ?? null,
                'tread_depth_mm'         => $validated['tread_depth_mm'] ?? null,
                'reason'                 => $validated['reason'] ?? null,
                'quantity'               => $validated['quantity'] ?? 1,
                'unit_cost'              => $validated['unit_cost'] ?? null,
                'cost'                   => $cost,
                'notes'                  => $validated['notes'] ?? null,
                'photo_path'             => $photoPath,
            ]);

            Log::info('Item created successfully', ['item_id' => $item->id, 'item' => $item->toArray()]);

            // Sync with Vehicle Component Register (for theft & asset tracking)
            if ($maintenance->vehicle_id && (!empty($item->new_serial_no) || in_array($item->action, ['replace', 'install']))) {
                try {
                    Log::info('Syncing component register');
                    $this->syncComponentRegister($maintenance, $item);
                    Log::info('Component register synced successfully');
                } catch (\Exception $syncError) {
                    Log::error('Component register sync failed but continuing', [
                        'error' => $syncError->getMessage(),
                        'trace' => $syncError->getTraceAsString()
                    ]);
                    // Don't throw - allow the item to be created even if sync fails
                }
            }

            // Update maintenance total cost if item cost provided
            try {
                Log::info('Recalculating maintenance cost');
                $this->recalculateMaintenanceCost($maintenance);
                Log::info('Maintenance cost recalculated');
            } catch (\Exception $costError) {
                Log::error('Cost recalculation failed but continuing', [
                    'error' => $costError->getMessage()
                ]);
                // Don't throw - allow the item to be created even if cost calc fails
            }

            Log::info('Committing transaction');
            DB::commit();

            Log::info('=== MAINTENANCE ITEM STORED SUCCESSFULLY ===');

            // If the record was approved, reset approval so Client Director must re-approve
            $itemService->resetApprovalAndNotify($maintenance);

            if ($request->input('redirect_to') === 'history_show') {
                return redirect()
                    ->route('maintenance.history.show', $item->id)
                    ->with('success', __('Maintenance item added successfully! Re-submitted for approval if applicable.'));
            }

            if ($request->input('redirect_to') === 'history') {
                return redirect()
                    ->route('maintenance.history')
                    ->with('success', __('Maintenance item added successfully! Re-submitted for approval if applicable.'));
            }

            return redirect()
                ->route('maintenance.show', $maintenance->id)
                ->with('success', __('Maintenance item added successfully! Re-submitted for approval if applicable.'));

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Maintenance item store error: ' . $th->getMessage(), ['exception' => $th]);
            return redirect()
                ->back()
                ->with('error', __('Failed to add maintenance item: ') . $th->getMessage())
                ->withInput();
        }
    }

    /**
     * Update an existing maintenance item.
     */
    public function update(Request $request, VehicleMaintenance $maintenance, VehicleMaintenanceItem $item, VehicleMaintenanceItemService $itemService)
    {
        if ((string)($maintenance->payment_status ?? '') === '2') {
            return redirect()->back()
                ->with('error', __('Cannot update maintenance items. This record is locked because payment has already been processed.'));
        }

        $this->normalizeCategoryInputs($request);

        $validated = $request->validate([
            'category'        => ['required', 'string', 'in:tyre,battery,telematics,brake,engine,general,other'],
            'action'          => ['required', 'string'],
            'location'        => ['nullable', 'string', 'max:255'],
            'axle'            => ['nullable', 'string', 'max:255'],
            'position'        => ['nullable', 'string', 'max:255'],
            'item_name'       => ['nullable', 'string', 'max:255'],
            'brand'           => ['nullable', 'string', 'max:255'],
            'model'           => ['nullable', 'string', 'max:255'],
            'size'            => ['nullable', 'string', 'max:255'],
            'old_serial_no'   => ['nullable', 'string', 'max:255'],
            'new_serial_no'   => ['nullable', 'string', 'max:255'],
            'tread_depth_mm'  => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'reason'          => ['nullable', 'string', 'max:255'],
            'quantity'        => ['nullable', 'integer', 'min:1'],
            'unit_cost'       => ['nullable', 'numeric', 'min:0'],
            'cost'            => ['nullable', 'numeric', 'min:0'],
            'notes'           => ['nullable', 'string'],
            'photo'           => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx', 'max:10240'],
        ]);

        try {
            DB::beginTransaction();

            if ($request->hasFile('photo')) {
                if ($item->photo_path && Storage::disk('public')->exists($item->photo_path)) {
                    Storage::disk('public')->delete($item->photo_path);
                }
                $file = $request->file('photo');
                $filename = 'item_' . Str::random(6) . '_' . time() . '.' . $file->getClientOriginalExtension();
                $item->photo_path = $file->storeAs('maintenance/' . $maintenance->id . '/items', $filename, 'public');
            }

            $location = $validated['location'] ?? null;
            if (empty($location)) {
                if (!empty($validated['axle']) && !empty($validated['position'])) {
                    $location = $validated['axle'] . ' – ' . $validated['position'];
                } elseif (!empty($validated['axle'])) {
                    $location = $validated['axle'];
                } elseif (!empty($validated['position'])) {
                    $location = $validated['position'];
                }
            }

            $cost = $validated['cost'] ?? null;
            if (empty($cost) && !empty($validated['unit_cost'])) {
                $cost = $validated['unit_cost'] * ($validated['quantity'] ?? 1);
            }

            $item->update([
                'category'       => $validated['category'],
                'action'         => $validated['action'],
                'location'       => $location,
                'axle'           => $validated['axle'] ?? null,
                'position'       => $validated['position'] ?? null,
                'item_name'      => $validated['item_name'] ?? ucfirst($validated['category']),
                'brand'          => $validated['brand'] ?? null,
                'model'          => $validated['model'] ?? null,
                'size'           => $validated['size'] ?? null,
                'old_serial_no'  => $validated['old_serial_no'] ?? null,
                'new_serial_no'  => $validated['new_serial_no'] ?? null,
                'tread_depth_mm' => $validated['tread_depth_mm'] ?? null,
                'reason'         => $validated['reason'] ?? null,
                'quantity'       => $validated['quantity'] ?? 1,
                'unit_cost'      => $validated['unit_cost'] ?? null,
                'cost'           => $cost,
                'notes'          => $validated['notes'] ?? null,
            ]);

            if ($maintenance->vehicle_id) {
                $this->syncComponentRegister($maintenance, $item);
            }

            $this->recalculateMaintenanceCost($maintenance);

            DB::commit();

            // If the record was approved, reset approval so Client Director must re-approve
            $itemService->resetApprovalAndNotify($maintenance);

            if ($request->input('redirect_to') === 'history_show') {
                return redirect()
                    ->route('maintenance.history.show', $item->id)
                    ->with('success', __('Maintenance item updated successfully! Re-submitted for approval if applicable.'));
            }

            if ($request->input('redirect_to') === 'history') {
                return redirect()
                    ->route('maintenance.history')
                    ->with('success', __('Maintenance item updated successfully!'));
            }

            return redirect()
                ->route('maintenance.show', $maintenance->id)
                ->with('success', __('Maintenance item updated successfully!'));

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Maintenance item update error: ' . $th->getMessage(), ['exception' => $th]);
            return redirect()
                ->back()
                ->with('error', __('Failed to update maintenance item: ') . $th->getMessage());
        }
    }

    /**
     * Delete a maintenance item.
     */
    public function destroy(VehicleMaintenance $maintenance, VehicleMaintenanceItem $item, VehicleMaintenanceItemService $itemService)
    {
        if ((string)($maintenance->payment_status ?? '') === '2') {
            return redirect()->back()
                ->with('error', __('Cannot delete maintenance items. This record is locked because payment has already been processed.'));
        }

        try {
            DB::beginTransaction();

            if ($item->photo_path && Storage::disk('public')->exists($item->photo_path)) {
                Storage::disk('public')->delete($item->photo_path);
            }

            // Remove associated vehicle component record if created
            VehicleComponent::where('vehicle_maintenance_item_id', $item->id)->delete();

            $item->delete();

            $this->recalculateMaintenanceCost($maintenance);

            DB::commit();

            // If the record was approved, reset approval so Client Director must re-approve
            $itemService->resetApprovalAndNotify($maintenance);

            if (request()->input('redirect_to') === 'history') {
                return redirect()
                    ->route('maintenance.history')
                    ->with('success', __('Maintenance item removed successfully!'));
            }

            return redirect()
                ->route('maintenance.show', $maintenance->id)
                ->with('success', __('Maintenance item removed successfully!'));

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Maintenance item delete error: ' . $th->getMessage(), ['exception' => $th]);
            return redirect()
                ->back()
                ->with('error', __('Failed to delete maintenance item.'));
        }
    }

    /**
     * Syncs new and replaced components with the Vehicle Component Register (Theft/Control Audit Trail).
     */
    protected function syncComponentRegister(VehicleMaintenance $maintenance, VehicleMaintenanceItem $item)
    {
        $vehicleId = $maintenance->vehicle_id;
        $mDate = $maintenance->maintenance_date ? $maintenance->maintenance_date->format('Y-m-d') : date('Y-m-d');
        $odometer = $maintenance->odometer;

        // 1. If old serial number exists, record or update the removed component in historical audit log
        if (!empty($item->old_serial_no)) {
            // Deactivate any existing active component with this serial or at this location
            VehicleComponent::where('vehicle_id', $vehicleId)
                ->where(function ($q) use ($item) {
                    $q->where('serial_no', $item->old_serial_no)
                      ->orWhere('location', $item->location);
                })
                ->where('status', 'active')
                ->where('vehicle_maintenance_item_id', '!=', $item->id)
                ->update([
                    'status'         => 'replaced',
                    'removed_date'   => $mDate,
                    'removed_reason' => 'Replaced by ' . ($item->new_serial_no ?: 'new part') . ' during ' . ($maintenance->formatted_reference_no),
                ]);

            VehicleComponent::updateOrCreate(
                [
                    'vehicle_maintenance_item_id' => $item->id,
                    'status'                      => 'removed',
                ],
                [
                    'vehicle_id'                  => $vehicleId,
                    'category'                    => $item->category,
                    'component_name'              => $item->item_name ?: ucfirst($item->category),
                    'location'                    => $item->location ?: 'N/A',
                    'serial_no'                   => $item->old_serial_no,
                    'brand'                       => $item->brand,
                    'model'                       => $item->model,
                    'size'                        => $item->size,
                    'removed_date'                => $mDate,
                    'removed_reason'              => 'Removed during ' . ($maintenance->formatted_reference_no) . ' (' . ($item->reason ?: 'Replaced') . ')',
                    'vehicle_maintenance_id'      => $maintenance->id,
                ]
            );
        } else {
            // If old serial was removed during edit, delete old removed record
            VehicleComponent::where('vehicle_maintenance_item_id', $item->id)
                ->where('status', 'removed')
                ->delete();
        }

        // 2. If new serial number exists, register/update as active installed component
        if (!empty($item->new_serial_no)) {
            // Deactivate any prior active component at this exact location
            if (!empty($item->location)) {
                VehicleComponent::where('vehicle_id', $vehicleId)
                    ->where('category', $item->category)
                    ->where('location', $item->location)
                    ->where('status', 'active')
                    ->where('vehicle_maintenance_item_id', '!=', $item->id)
                    ->update([
                        'status'         => 'replaced',
                        'removed_date'   => $mDate,
                        'removed_reason' => 'Replaced by ' . $item->new_serial_no . ' (' . ($maintenance->formatted_reference_no) . ')',
                    ]);
            }

            VehicleComponent::updateOrCreate(
                [
                    'vehicle_maintenance_item_id' => $item->id,
                    'status'                      => 'active',
                ],
                [
                    'vehicle_id'                  => $vehicleId,
                    'category'                    => $item->category,
                    'component_name'              => $item->item_name ?: ucfirst($item->category),
                    'location'                    => $item->location ?: 'N/A',
                    'serial_no'                   => $item->new_serial_no,
                    'brand'                       => $item->brand,
                    'model'                       => $item->model,
                    'size'                        => $item->size,
                    'installed_date'              => $mDate,
                    'installed_odometer'          => $odometer,
                    'vehicle_maintenance_id'      => $maintenance->id,
                ]
            );
        } else {
            // If new serial was cleared during edit, delete active record
            VehicleComponent::where('vehicle_maintenance_item_id', $item->id)
                ->where('status', 'active')
                ->delete();
        }
    }

    /**
     * Recalculate total cost on the parent maintenance record if items have costs.
     */
    protected function recalculateMaintenanceCost(VehicleMaintenance $maintenance)
    {
        $itemsTotal = $maintenance->items()->sum('cost');
        if ($itemsTotal > 0 && ($maintenance->cost === null || $maintenance->cost == 0)) {
            $maintenance->update(['cost' => $itemsTotal]);
        }
    }

    /**
     * Normalize category-specific inputs from the form into standard fields.
     */
    protected function normalizeCategoryInputs(Request $request): void
    {
        $category = $request->input('category', 'tyre');

        // Remove all category-specific suffixed fields that don't belong to selected category
        // This prevents conflicts when disabled fields are submitted with empty values
        if ($category === 'tyre') {
            // Remove non-tyre category fields
            $request->request->remove('action_battery');
            $request->request->remove('location_battery');
            $request->request->remove('item_name_battery');
            $request->request->remove('old_serial_no_battery');
            $request->request->remove('new_serial_no_battery');
            $request->request->remove('brand_battery');
            $request->request->remove('size_battery');
            $request->request->remove('reason_battery');
            
            $request->request->remove('action_telematics');
            $request->request->remove('location_telematics');
            $request->request->remove('item_name_telematics');
            $request->request->remove('old_serial_no_telematics');
            $request->request->remove('new_serial_no_telematics');
            $request->request->remove('brand_telematics');
            $request->request->remove('model_telematics');
            $request->request->remove('reason_telematics');
            
            $request->request->remove('action_general');
            $request->request->remove('location_general');
            $request->request->remove('item_name_general');
            $request->request->remove('old_serial_no_general');
            $request->request->remove('new_serial_no_general');
            $request->request->remove('brand_general');
            $request->request->remove('reason_general');
            
            // Explicitly set tyre fields with fallbacks
            $request->merge([
                'action'        => $request->input('action', 'replace'),
                'axle'          => $request->input('axle', 'Rear Axle 1'),
                'position'      => $request->input('position', 'Left Outer'),
                'size'          => $request->input('size'),
                'brand'         => $request->input('brand'),
                'model'         => $request->input('model'),
                'old_serial_no' => $request->input('old_serial_no'),
                'new_serial_no' => $request->input('new_serial_no'),
                'tread_depth_mm'=> $request->input('tread_depth_mm'),
                'reason'        => $request->input('reason'),
                'item_name'     => 'Tyre', // Default item name for tyre category
            ]);
        } elseif ($category === 'battery') {
            $request->merge([
                'action'        => $request->input('action_battery', $request->input('action', 'replace')),
                'location'      => $request->input('location_battery', $request->input('location', 'Main Battery 1')),
                'item_name'     => $request->input('item_name_battery', $request->input('item_name', 'Battery')),
                'old_serial_no' => $request->input('old_serial_no_battery', $request->input('old_serial_no')),
                'new_serial_no' => $request->input('new_serial_no_battery', $request->input('new_serial_no')),
                'brand'         => $request->input('brand_battery', $request->input('brand')),
                'size'          => $request->input('size_battery', $request->input('size')),
                'reason'        => $request->input('reason_battery', $request->input('reason')),
            ]);
        } elseif ($category === 'telematics') {
            $request->merge([
                'item_name'     => $request->input('item_name_telematics', $request->input('item_name', 'GPS Tracker')),
                'action'        => $request->input('action_telematics', $request->input('action', 'replace')),
                'location'      => $request->input('location_telematics', $request->input('location', 'Cab / Dashboard')),
                'old_serial_no' => $request->input('old_serial_no_telematics', $request->input('old_serial_no')),
                'new_serial_no' => $request->input('new_serial_no_telematics', $request->input('new_serial_no')),
                'brand'         => $request->input('brand_telematics', $request->input('brand')),
                'model'         => $request->input('model_telematics', $request->input('model')),
                'reason'        => $request->input('reason_telematics', $request->input('reason')),
            ]);
        } elseif (in_array($category, ['brake', 'engine', 'general', 'other'])) {
            $request->merge([
                'item_name'     => $request->input('item_name_general', $request->input('item_name', ucfirst($category))),
                'action'        => $request->input('action_general', $request->input('action', 'replace')),
                'location'      => $request->input('location_general', $request->input('location')),
                'old_serial_no' => $request->input('old_serial_no_general', $request->input('old_serial_no')),
                'new_serial_no' => $request->input('new_serial_no_general', $request->input('new_serial_no')),
                'brand'         => $request->input('brand_general', $request->input('brand')),
                'reason'        => $request->input('reason_general', $request->input('reason')),
            ]);
        }
    }
}
