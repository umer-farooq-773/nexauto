<?php

namespace App\Services;

use App\Constants\Constants;
use App\Models\User;
use App\Models\VehicleComponent;
use App\Models\VehicleMaintenance;
use App\Models\VehicleMaintenanceItem;
use App\Notifications\RecordCreatedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VehicleMaintenanceItemService
{
    /**
     * Process array of items from request during maintenance store or update.
     */
    public function syncMaintenanceItems(
        VehicleMaintenance $maintenance,
        array $itemsData = [],
        array $photos = [],
        array $deletedItemIds = []
    ): void {
        // 1. Delete removed items
        if (!empty($deletedItemIds)) {
            foreach ($deletedItemIds as $delId) {
                if (!empty($delId)) {
                    $item = VehicleMaintenanceItem::where('vehicle_maintenance_id', $maintenance->id)->find($delId);
                    if ($item) {
                        $this->deleteItem($maintenance, $item);
                    }
                }
            }
        }

        // 2. Process each item from form
        if (!empty($itemsData)) {
            foreach ($itemsData as $index => $data) {
                if (empty($data['category'])) {
                    continue;
                }

                $photoFile = $photos[$index] ?? null;
                $existingId = $data['id'] ?? null;
                $existingItem = null;

                if (!empty($existingId)) {
                    $existingItem = VehicleMaintenanceItem::where('vehicle_maintenance_id', $maintenance->id)->find($existingId);
                }

                $this->saveItem($maintenance, $data, $photoFile, $existingItem);
            }
        }

        // 3. Recalculate total cost if items have cost
        $this->recalculateMaintenanceCost($maintenance);
    }

    /**
     * Save or update a single maintenance item.
     */
    public function saveItem(
        VehicleMaintenance $maintenance,
        array $data,
        $photoFile = null,
        ?VehicleMaintenanceItem $existingItem = null
    ): VehicleMaintenanceItem {
        $category = strtolower($data['category'] ?? 'general');
        $action = strtolower($data['action'] ?? 'replace');
        $location = $data['location'] ?? null;
        $axle = $data['axle'] ?? null;
        $position = $data['position'] ?? null;

        // Auto-format location from axle & position if not provided
        if (empty($location)) {
            if (!empty($axle) && !empty($position)) {
                $location = $axle . ' – ' . $position;
            } elseif (!empty($axle)) {
                $location = $axle;
            } elseif (!empty($position)) {
                $location = $position;
            }
        }

        $cost = !empty($data['cost']) ? floatval($data['cost']) : null;
        $quantity = !empty($data['quantity']) ? intval($data['quantity']) : 1;
        $unitCost = !empty($data['unit_cost']) ? floatval($data['unit_cost']) : null;
        if (empty($cost) && !empty($unitCost)) {
            $cost = $unitCost * $quantity;
        }

        $photoPath = $existingItem?->photo_path;
        if ($photoFile instanceof UploadedFile) {
            if ($photoPath && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }
            $filename = 'item_' . Str::random(6) . '_' . time() . '.' . $photoFile->getClientOriginalExtension();
            $photoPath = $photoFile->storeAs('maintenance/' . $maintenance->id . '/items', $filename, 'public');
        }

        $itemName = $data['item_name'] ?? null;
        if (empty($itemName)) {
            $itemName = match ($category) {
                'tyre' => 'Tyre',
                'battery' => 'Battery',
                'telematics' => 'GPS / Telematics Unit',
                'brake' => 'Brake Component',
                'engine' => 'Engine Part',
                default => 'General Component',
            };
        }

        $itemAttributes = [
            'vehicle_maintenance_id' => $maintenance->id,
            'category'               => $category,
            'action'                 => $action,
            'location'               => $location,
            'axle'                   => $axle,
            'position'               => $position,
            'item_name'              => $itemName,
            'brand'                  => $data['brand'] ?? null,
            'model'                  => $data['model'] ?? null,
            'size'                   => $data['size'] ?? null,
            'old_serial_no'          => $data['old_serial_no'] ?? null,
            'new_serial_no'          => $data['new_serial_no'] ?? null,
            'tread_depth_mm'         => !empty($data['tread_depth_mm']) ? floatval($data['tread_depth_mm']) : null,
            'reason'                 => $data['reason'] ?? null,
            'quantity'               => $quantity,
            'unit_cost'              => $unitCost,
            'cost'                   => $cost,
            'notes'                  => $data['notes'] ?? null,
            'photo_path'             => $photoPath,
        ];

        if ($existingItem) {
            $existingItem->update($itemAttributes);
            $item = $existingItem;
        } else {
            $item = VehicleMaintenanceItem::create($itemAttributes);
        }

        // Sync with Vehicle Component Register (for theft & asset tracking)
        if ($maintenance->vehicle_id && (!empty($item->new_serial_no) || !empty($item->old_serial_no) || in_array($item->action, ['replace', 'install']))) {
            try {
                $this->syncComponentRegister($maintenance, $item);
            } catch (\Throwable $syncError) {
                Log::error('Component register sync failed: ' . $syncError->getMessage(), [
                    'item_id' => $item->id,
                ]);
            }
        }

        return $item;
    }

    /**
     * Delete an item and its associated files & component tracking records.
     */
    public function deleteItem(VehicleMaintenance $maintenance, VehicleMaintenanceItem $item): void
    {
        if ($item->photo_path && Storage::disk('public')->exists($item->photo_path)) {
            Storage::disk('public')->delete($item->photo_path);
        }

        VehicleComponent::where('vehicle_maintenance_item_id', $item->id)->delete();
        $item->delete();
        $this->recalculateMaintenanceCost($maintenance);
    }

    /**
     * Sync with Vehicle Component Register (Theft/Control Audit Trail).
     */
    public function syncComponentRegister(VehicleMaintenance $maintenance, VehicleMaintenanceItem $item): void
    {
        $vehicleId = $maintenance->vehicle_id;
        $mDate = $maintenance->maintenance_date
            ? ($maintenance->maintenance_date instanceof \DateTime ? $maintenance->maintenance_date->format('Y-m-d') : $maintenance->maintenance_date)
            : date('Y-m-d');
        $odometer = $maintenance->odometer;

        // 1. If old serial number exists, update or record removal
        if (!empty($item->old_serial_no)) {
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
            VehicleComponent::where('vehicle_maintenance_item_id', $item->id)
                ->where('status', 'removed')
                ->delete();
        }

        // 2. If new serial number exists, register/update as active installed component
        if (!empty($item->new_serial_no)) {
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
            VehicleComponent::where('vehicle_maintenance_item_id', $item->id)
                ->where('status', 'active')
                ->delete();
        }
    }

    /**
     * Recalculate total cost on the parent maintenance record if items have costs.
     */
    public function recalculateMaintenanceCost(VehicleMaintenance $maintenance): void
    {
        $itemsTotal = $maintenance->items()->sum('cost');
        if ($itemsTotal > 0 && ($maintenance->cost === null || (float) $maintenance->cost == 0)) {
            $maintenance->update(['cost' => $itemsTotal]);
        }
    }

    /**
     * If maintenance record was previously client-approved, reset approval and payment status,
     * and send re-approval notification to Client Directors.
     */
    public function resetApprovalAndNotify(VehicleMaintenance $maintenance): void
    {
        if ($maintenance->client_approved || $maintenance->payment_status === '1') {
            $maintenance->update([
                'client_approved'         => false,
                'client_approved_at'      => null,
                'client_approved_by'      => null,
                'client_approved_comment' => null,
                'payment_status'          => '0',
            ]);

            $showUrl = route('maintenance.show', $maintenance->id);
            $vehicleNo = $maintenance->vehicle?->truck_no ?? 'N/A';

            try {
                $notification = new RecordCreatedNotification(
                    'Vehicle Maintenance (Updated - Approval Required)',
                    $showUrl,
                    $vehicleNo
                );

                $recipients = User::role([
                    Constants::CLIENT_DIRECTORS,
                    Constants::SUPERADMIN,
                ])->get()->unique('id');

                foreach ($recipients as $recipient) {
                    try {
                        $recipient->notifyNow($notification, ['database']);
                        if (!empty($recipient->email)) {
                            $recipient->notifyNow($notification, ['mail']);
                        }
                    } catch (\Throwable $e) {
                        Log::warning("Re-approval notification failed [Vehicle Maintenance]: " . $e->getMessage(), [
                            'user_id' => $recipient->id,
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("resetApprovalAndNotify notification failed: " . $e->getMessage());
            }
        }
    }
}
