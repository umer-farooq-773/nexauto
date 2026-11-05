<?php

namespace App\Http\Controllers\Concerns;

use App\Constants\Constants;
use App\Models\PaymentHistory;
use App\Models\User;
use App\Models\Waybill;
use App\Notifications\ClientApprovedIncidentNotification;
use App\Notifications\PaymentProcessedNotification;
use App\Notifications\RecordCreatedNotification;
use App\Services\IncidentWaybillNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait HandlesIncidentWorkflow
{
    /**
     * Roles that are allowed to change incident status.
     * Client Directors, Admins, SuperAdmins, and Champs Management can all change status.
     */
    private function canChangeStatus(): bool
    {
        $user = auth()->user();
        return $user->hasRole([
            Constants::CLIENT_DIRECTORS,
            Constants::ADMIN,
            Constants::SUPERADMIN,
            Constants::CHAMPS_MANAGEMENT,
        ]);
    }

    /**
     * Check if the current user is allowed to change the incident status.
     * Client Directors, Admins, SuperAdmins, and Champs Management can change status
     * — but ONLY after client_approved = true.
     *
     * Returns a redirect response if blocked, or null if allowed.
     */
    protected function checkStatusChangePermission(Request $request, $existingRecord)
    {
        // If status field is not being changed, allow
        if (!$request->has('status')) {
            return null;
        }

        // If status value is same as existing, allow (no real change)
        if ((string) $request->input('status') === (string) $existingRecord->status) {
            return null;
        }

        // Must have client approval before any status change
        if (!($existingRecord->client_approved ?? false)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Client Director approval is required before changing the status of this incident.');
        }

        // Must be an authorised role
        if (!$this->canChangeStatus()) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'You do not have permission to change the status of this incident.');
        }

        // Store old status so fireIncidentStatusChangedEvent can use it
        $request->merge([
            '_incident_old_status' => (string) $existingRecord->status,
        ]);

        return null;
    }

    /**
     * Generic client-approval handler shared by all incident controllers.
     *
     * Usage in a controller:
     *   public function clientApprove(Request $request, $id)
     *   {
     *       return $this->handleClientApprove($request, $id, 'Accident', 'accident');
     *   }
     *
     * @param  Request  $request
     * @param  string   $id            The incident record ID
     * @param  string   $incidentLabel Human-readable label e.g. "Accident"
     * @param  string   $incidentType  Snake-case type  e.g. "accident"
     */
    protected function handleClientApprove(
        Request $request,
        string  $id,
        string  $incidentLabel,
        string  $incidentType
    ) {
        try {
            $user = Auth::user();

            if (!$user) {
                abort(403, 'Unauthorized.');
            }

            // Only Client Directors can approve
            if (!$user->hasRole(Constants::CLIENT_DIRECTORS)) {
                abort(403, 'Unauthorized. Only a Client Director can approve this record.');
            }

            $existing = $this->_repo->show($id);
            if (!$existing) {
                abort(404);
            }

            if ($existing->client_approved) {
                return redirect()->back()->with('info', 'This record has already been approved.');
            }

            $request->validate([
                'client_approved_comment' => ['required', 'string'],
            ]);

            $existing->update([
                'client_approved'         => true,
                'client_approved_at'      => now(),
                'client_approved_by'      => $user->id,
                'client_approved_comment' => $request->input('client_approved_comment'),
                ...($this->usesProceedPaymentStatus($incidentLabel) ? ['payment_status' => '1'] : []),
            ]);

            $showUrl   = route($this->_route . '.show', $existing->id);
            $vehicleNo = $existing->vehicle?->truck_no ?? 'N/A';
            $approvedBy = $user->full_name ?? $user->name ?? 'Client Director';

            // DB notification → Admin, SuperAdmin, Champs, Client Management
            $notification = new ClientApprovedIncidentNotification(
                $incidentLabel,
                $showUrl,
                $vehicleNo,
                $approvedBy
            );

            $recipients = User::role([
                Constants::ADMIN,
                Constants::SUPERADMIN,
                Constants::CHAMPS_MANAGEMENT,
                Constants::CLIENT_MANAGEMENT,
            ])->get()->unique('id');

            foreach ($recipients as $recipient) {
                try {
                    $recipient->notifyNow($notification, ['database']);
                } catch (\Throwable $e) {
                    Log::warning("ClientApprove DB notification failed [{$incidentType}]", [
                        'user_id' => $recipient->id,
                        'error'   => $e->getMessage(),
                    ]);
                }
            }

            return redirect()
                ->route($this->_route . '.show', $existing->id)
                ->with('success', "{$incidentLabel} record approved successfully. Status change is now unlocked.");
        } catch (\Throwable $th) {
            return $this->incidentFailureResponse(
                $th,
                $this->_route . '.index',
                ['module' => $incidentType, 'action' => 'clientApprove', 'record_id' => $id]
            );
        }
    }

    /**
     * Generic status-only update handler shared by all incident controllers.
     * Only Admin, SuperAdmin, Champs Management can call this, and only after
     * the Client Director has approved the record.
     */
    protected function handleStatusUpdate(
    \Illuminate\Http\Request $request,
    string $id,
    string $incidentLabel,
    string $incidentType,
    array $allowedStatuses = ['0', '1'],
    string $dateColumn = 'release_date',
    string $statusThatSetsDate = '1'
    ) {
        try {
            $existing = $this->_repo->show($id);
            if (!$existing) abort(404);

            if (!($existing->client_approved ?? false)) {
                return redirect()->back()
                    ->with('error', 'Client Director approval is required before changing the status of this incident.');
            }

            $paymentConfig = $this->paymentWorkflowConfig($incidentLabel);
            $requiredPaymentStatus = $this->usesProceedPaymentStatus($incidentLabel) ? '2' : '1';
            if ($paymentConfig && (string) ($existing->payment_status ?? '') !== $requiredPaymentStatus) {
                return redirect()->back()
                    ->with('error', 'Payment must be processed before changing the status of this record.');
            }

            $user = auth()->user();
            if (!$user->hasRole([Constants::ADMIN, Constants::SUPERADMIN, Constants::CHAMPS_MANAGEMENT])) {
                return redirect()->back()
                    ->with('error', 'You do not have permission to change the status of this incident.');
            }

            $request->validate([
                'status'  => ['required', 'in:' . implode(',', $allowedStatuses)],
                'remarks' => ['nullable', 'string'],
            ]);

            $updateData = ['status' => $request->input('status')];

            if ($request->filled('remarks')) {
                $updateData['remarks'] = $request->input('remarks');
            }

            if ($dateColumn) {
                if ($request->input('status') == $statusThatSetsDate) {
                    $updateData[$dateColumn] = now()->toDateString();
                } else {
                    $updateData[$dateColumn] = null;
                }
            }

            $oldStatus = (string) $existing->status;
            $existing->update($updateData);
            $record = $this->_repo->show($id);

            $showUrl = route($this->_route . '.show', $record);

            if ($oldStatus !== (string) $record->status) {
                $request->merge(['_incident_old_status' => $oldStatus]);
                $this->fireIncidentStatusChangedEvent($request, $record, $incidentType, $incidentLabel, $showUrl);
            }

            return redirect()->route($this->_route . '.show', $id)
                ->with('success', __('language.updated_successfully'));
        } catch (\Throwable $th) {
            return $this->incidentFailureResponse(
                $th,
                $this->_route . '.show',
                ['module' => $incidentType, 'action' => 'statusUpdate', 'record_id' => $id]
            );
        }
    }

    /**
     * Fire IncidentStatusChanged event if status was changed.
     * Call this AFTER the record has been saved.
     */
    protected function fireIncidentStatusChangedEvent(
        Request $request,
        $record,
        string  $incidentType,
        string  $incidentLabel,
        string  $showUrl
    ): void {
        if (!$request->has('_incident_old_status')) {
            return;
        }

        $oldStatus = $request->input('_incident_old_status');
        $newStatus = (string) $record->status;

        if ($oldStatus === $newStatus) {
            return;
        }

        // Resolve associated waybill
        $waybill = null;
        if (!empty($record->waybill_id)) {
            $waybill = Waybill::with(['vehicle', 'driver', 'route_trip'])
                ->find($record->waybill_id);
        }

        if (!$waybill && !empty($record->vehicle_id)) {
            $waybill = Waybill::with(['vehicle', 'driver', 'route_trip'])
                ->where('vehicle_id', $record->vehicle_id)
                ->latest()
                ->first();
        }

        $record->loadMissing('vehicle');

        event(new \App\Events\IncidentStatusChanged(
            incident:      $record,
            incidentType:  $incidentType,
            incidentLabel: $incidentLabel,
            oldStatus:     $oldStatus,
            newStatus:     $newStatus,
            showUrl:       $showUrl,
            waybill:       $waybill,
        ));
    }



    protected function handlePaymentProcess(
        Request $request,
        string  $id,
        string  $incidentLabel,
        ?array  $statusConfig = null
    ) {
        try {
            $paymentConfig = $this->paymentWorkflowConfig($incidentLabel);
            if (!$paymentConfig) {
                abort(404);
            }

            $record = $this->_repo->show($id);
            if (!$record) abort(404);

            if (!$record->client_approved) {
                return redirect()->back()->with('error', __('Client approval is required before processing payment.'));
            }

            if ($this->usesProceedPaymentStatus($incidentLabel)) {
                $paymentStatus = (string) ($record->payment_status ?? '0');

                if ($paymentStatus === '2') {
                    return redirect()->back()->with('info', __('Payment has already been processed.'));
                }

                if ($paymentStatus !== '1' && $paymentStatus !== '0') {
                    return redirect()->back()->with('error', __('Client approval is required before processing payment.'));
                }
            }

            $validationRules = [
                'paid_amount'       => ['required', 'numeric', 'min:0'],
                'payment_method'    => ['required', 'in:cash,bank_transfer,credit_card,cheque,pos_payment,atm_transfer,mobile_money'],
                'payment_reference' => ['nullable', 'required_if:payment_method,bank_transfer', 'required_if:payment_method,atm_transfer', 'string', 'max:255'],
                'payment_notes'     => 'nullable|string|max:1000',
                'main_price'        => ['nullable', 'numeric', 'min:0'],
                'expenses'          => ['nullable', 'array', 'min:1'],
                'expenses.*.payment_method' => ['nullable', 'in:cash,bank_transfer,credit_card,cheque,pos_payment,atm_transfer,mobile_money'],
                'expenses.*.reference_number' => ['nullable', 'string', 'max:255'],
                'expenses.*.authority' => ['nullable', 'string', 'max:255'],
                'expenses.*.paid_to' => ['nullable', 'string', 'max:255'],
                'expenses.*.price' => ['nullable', 'numeric', 'min:0.01'],
            ];

            if ($request->has('expenses')) {
                foreach ($request->input('expenses') as $key => $expense) {
                    if (isset($expense['payment_method']) && in_array($expense['payment_method'], ['bank_transfer', 'atm_transfer'])) {
                        $validationRules["expenses.{$key}.reference_number"] = ['required', 'string', 'max:255'];
                    }
                }
            }

            // Generic: only adds status/remarks validation when a config was
            // actually passed in. No module name is checked here at all.
            if ($statusConfig !== null) {
                $allowedStatuses = $statusConfig['allowed_statuses'] ?? [];

                $validationRules['status']  = ['required', 'in:' . implode(',', $allowedStatuses)];
                $validationRules['remarks'] = ['nullable', 'string'];
            }

            $validated = $request->validate($validationRules);

            $totalExpenses = 0;
            $mainPrice = floatval($request->input('main_price', 0));
            $totalExpenses += $mainPrice;

            if ($request->has('expenses')) {
                foreach ($request->input('expenses') as $expense) {
                    $totalExpenses += floatval($expense['price'] ?? 0);
                }
            }

            $paidAmount = floatval($request->input('paid_amount'));

            if ($totalExpenses > $paidAmount) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Total expenses (' . number_format($totalExpenses, 2) . ') exceed the paid amount (' . number_format($paidAmount, 2) . ').');
            }

            $costField = $paymentConfig['field'];
            $originalAmount = $costField ? $record->{$costField} : null;
            $user = Auth::user();

            DB::transaction(function () use ($record, $costField, $originalAmount, $paidAmount, $request, $user, $incidentLabel, $totalExpenses, $mainPrice, $statusConfig) {
                $updateData = [
                    'payment_status' => $this->usesProceedPaymentStatus($incidentLabel) ? '2' : '1',
                ];

                if ($costField && (float) $paidAmount !== (float) $originalAmount) {
                    $updateData[$costField] = $paidAmount;
                }

                // Generic: if a status config was supplied AND the request
                // actually sent a status, fold the date/status/remarks columns
                // into THIS SAME update call instead of a second ->update().
                // Which columns get touched is entirely driven by $statusConfig,
                // never hardcoded.
                if ($statusConfig !== null && $request->filled('status')) {
                    $status = (string) $request->input('status');
                    $dateColumn = $statusConfig['date_column'];
                    $statusThatSetsDate = $statusConfig['status_that_sets_date'];
                    $remarksColumn = $statusConfig['remarks_column'] ?? 'remarks';

                    $updateData['status'] = $status;
                    $updateData[$dateColumn] = $status === $statusThatSetsDate
                        ? now()->toDateString()
                        : null;

                    $remarks = $request->input('remarks');
                    if (!is_null($remarks) && $remarks !== '') {
                        $updateData[$remarksColumn] = $remarks;
                    }
                }

                $record->update($updateData);

                $expenseData = [];

                if ($mainPrice > 0) {
                    $expenseData[] = [
                        'type' => 'main',
                        'payment_method' => $request->input('payment_method'),
                        'reference_number' => $request->input('payment_reference'),
                        'authority' => $request->input('authority'),
                        'paid_to' => $request->input('paid_to'),
                        'price' => $mainPrice,
                    ];
                }

                if ($request->has('expenses')) {
                    foreach ($request->input('expenses') as $index => $expense) {
                        $expenseData[] = [
                            'type' => 'expense',
                            'payment_method' => $expense['payment_method'] ?? 'cash',
                            'reference_number' => $expense['reference_number'] ?? null,
                            'authority' => $expense['authority'] ?? null,
                            'paid_to' => $expense['paid_to'] ?? null,
                            'price' => floatval($expense['price'] ?? 0),
                        ];
                    }
                }

                PaymentHistory::create([
                    'payable_type'      => get_class($record),
                    'payable_id'        => $record->id,
                    'original_cost'     => $originalAmount,
                    'paid_amount'       => $paidAmount,
                    'payment_method'    => $request->input('payment_method'),
                    'reference_number'  => $request->input('payment_reference'),
                    'authority'         => $request->input('authority'),
                    'paid_to'           => $request->input('paid_to'),
                    'price'             => $mainPrice > 0 ? $mainPrice : null,
                    'notes'             => $request->input('payment_notes'),
                    'processed_by'      => $user?->id,
                    'processed_by_name' => $user?->full_name ?? $user?->name,
                    'expense_details'   => $expenseData,
                ]);
            });

            $this->notifyClientDirectorsPaymentProcessed($record, $incidentLabel);

            return redirect(route($this->_route . '.show', $record))
                ->with('success', __('Payment processed successfully. Total expenses: ' . number_format($totalExpenses, 2)));
        } catch (\Throwable $th) {
            return $this->incidentFailureResponse(
                $th,
                $this->_route . '.show',
                ['module' => $incidentLabel, 'action' => 'paymentProcess', 'record_id' => $id]
            );
        }
    }

    private function applyIncidentStatusUpdate(
        $record,
        string $status,
        ?string $remarks,
        string $dateColumn,
        string $statusThatSetsDate
    ): void {
        $updateData = ['status' => $status];

        if (!is_null($remarks) && $remarks !== '') {
            $updateData['remarks'] = $remarks;
        }

        $updateData[$dateColumn] = $status === $statusThatSetsDate
            ? now()->toDateString()
            : null;

        $record->update($updateData);
    }

    protected function paymentWorkflowConfig(string $incidentLabel): ?array
    {
        return match ($incidentLabel) {
            'Vehicle Breakdown' => ['field' => 'repair_cost', 'label' => 'Repair Cost'],
            'Vehicle Bogged Down' => ['field' => 'recovery_cost', 'label' => 'Recovery Cost'],
            'Illegal Detention' => ['field' => 'cost', 'label' => 'Cost'],
            'Impoundment' => ['field' => 'cost', 'label' => 'Cost'],
            'Maintenance', 'Vehicle Maintenance' => ['field' => 'cost', 'label' => 'Cost'],
            'Accident' => ['field' => 'damage_cost', 'label' => 'Damage Cost'],
            default => null,
        };
    }

    protected function usesProceedPaymentStatus(string $incidentLabel): bool
    {
        return in_array($incidentLabel, [
            'Vehicle Breakdown',
            'Vehicle Bogged Down',
            'Illegal Detention',
            'Accident',
            'Impoundment',
            'Maintenance',
            'Vehicle Maintenance',
        ], true);
    }

    protected function notifyClientDirectorsPaymentProcessed($record, string $incidentLabel): void
    {
        $showUrl = route($this->_route . '.show', $record->id);
        $vehicleNo = $record->vehicle?->truck_no ?? 'N/A';
        $notification = new PaymentProcessedNotification($incidentLabel, $showUrl, $vehicleNo);

        $recipients = User::role([Constants::CLIENT_DIRECTORS])->get()->unique('id');
        foreach ($recipients as $recipient) {
            try {
                $recipient->notifyNow($notification, ['database']);
            } catch (\Throwable $e) {
                Log::warning("Payment processed notification failed [{$incidentLabel}]", [
                    'user_id' => $recipient->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }
    }

    protected function notifyRecordCreated(string $label, string $targetUrl, ?string $vehicleNo = null): void
    {
        try {
            $notification = new RecordCreatedNotification($label, $targetUrl, $vehicleNo ?? 'N/A');

            $recipients = User::role([
                Constants::SUPERADMIN,
                Constants::CLIENT_DIRECTORS,
                Constants::ADMIN,
                Constants::CHAMPS_MANAGEMENT,
            ])->get()->unique('id');

            foreach ($recipients as $recipient) {
                // Database notification (bell icon)
                try {
                    $recipient->notifyNow($notification, ['database']);
                } catch (\Throwable $e) {
                    Log::warning("notifyRecordCreated DB failed [{$label}]", [
                        'user_id' => $recipient->id,
                        'error'   => $e->getMessage(),
                    ]);
                }

                // Mail notification — only if recipient has an email
                if (empty($recipient->email)) {
                    continue;
                }

                try {
                    $recipient->notifyNow($notification, ['mail']);
                } catch (\Throwable $e) {
                    Log::warning("notifyRecordCreated mail failed [{$label}]", [
                        'user_id' => $recipient->id,
                        'email'   => $recipient->email,
                        'error'   => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning("notifyRecordCreated failed [{$label}]: " . $e->getMessage());
        }
    }

    protected function syncIncidentDocument(Request $request, $record, string $directory): void
    {
        if (!$request->hasFile('document') || !$record) {
            return;
        }

        $uploaded = $record->uploadFile($request->file('document'), $directory);
        $existing = $record->files()->where('type', 'document')->latest()->first();

        if ($existing && !empty($existing->path)) {
            $record->deleteFile($existing->path);
            $existing->update([
                'name' => $uploaded['name'],
                'path' => $uploaded['path'],
            ]);

            return;
        }

        $record->files()->create([
            'type'   => 'document',
            'name'   => $uploaded['name'],
            'path'   => $uploaded['path'],
            'status' => 1,
        ]);
    }

    protected function touchIncidentWaybill(?string $waybillId): void
    {
        if (empty($waybillId)) {
            return;
        }

        Waybill::whereKey($waybillId)->update(['updated_at' => now()]);
    }

    protected function notifyIncidentWaybill(?string $waybillId, string $incidentType, string $incidentLabel, string $routeName, array $additionalData = []): void
    {
        $targetUrl = filter_var($routeName, FILTER_VALIDATE_URL) ? $routeName : route($routeName);

        if (empty($waybillId)) {
            $this->notifyRecordCreated($incidentLabel, $targetUrl);
            return;
        }

        $waybill = Waybill::with(['vehicle', 'driver.user'])->find($waybillId);
        if (!$waybill) {
            $this->notifyRecordCreated($incidentLabel, $targetUrl);
            return;
        }

        app(IncidentWaybillNotificationService::class)->notifyForIncident(
            $waybill,
            $incidentType,
            $incidentLabel,
            $routeName,
            $additionalData
        );
    }

    protected function incidentFailureResponse(\Throwable $th, ?string $fallbackRoute = null, array $context = [])
    {
        Log::error('Incident workflow failed', array_merge($context, [
            'message' => $th->getMessage(),
            'trace'   => $th->getTraceAsString(),
            'user_id' => auth()->id(),
        ]));

        if ($th instanceof NotFoundHttpException && $fallbackRoute) {
            return redirect()->route($fallbackRoute)->with('error', $th->getMessage());
        }

        $message = config('app.debug') ? $th->getMessage() : __('language.something_went_wrong');

        return redirect()->back()->withInput()->with('error', $message);
    }
}
