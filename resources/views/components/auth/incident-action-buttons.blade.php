{{--
    Reusable incident action buttons component.

    Props:
      $data            — the incident model instance
      $approveRoute    — named route for client approval  e.g. 'accident.client_approve'
      $modalTarget     — Bootstrap modal ID               e.g. '#statusChangeModal'
      $approveLabel    — (optional) label for approve btn, default 'Approve'
      $approveModalId  — (optional) modal ID for approval modal, default 'clientApproveModal'
--}}

@php
    use App\Constants\Constants;
    $user = auth()->user();
    $isClientDir = $user && method_exists($user, 'hasRole') && $user->hasRole(Constants::CLIENT_DIRECTORS);
    $canChangeStatus =
        $user &&
        method_exists($user, 'hasRole') &&
        $user->hasRole([Constants::ADMIN, Constants::SUPERADMIN, Constants::CHAMPS_MANAGEMENT]);
    $isPaymentProcessor = $user && method_exists($user, 'hasRole') && $user->hasRole([
    Constants::ADMIN,
    Constants::SUPERADMIN,
    // Constants::CHAMPS_MANAGEMENT,
]);
    $clientApproved = (bool) ($data->client_approved ?? false);
    $usesProceedPaymentStatus = (bool) ($usesProceedPaymentStatus ?? false);
    $paymentStatus = (string) ($data->payment_status ?? '0');
    // For proceed-payment-status modules: show payment button when status is '1' (approved by client director)
    // Also allow it when status is '0' but client has approved (handles legacy records created before the '1' auto-set)
    $paymentReady = $usesProceedPaymentStatus
        ? $paymentStatus === '1' || ($paymentStatus === '0' && $clientApproved)
        : $paymentStatus !== '1';
    $paymentProcessed = $usesProceedPaymentStatus ? $paymentStatus === '2' : $paymentStatus === '1';
    $approveLabel = $approveLabel ?? ($usesProceedPaymentStatus ? 'Payment Approve' : 'Approve');
    $approvedLabel = $usesProceedPaymentStatus ? 'Payment Approved' : 'Approved';
    $approveModalId = $approveModalId ?? 'clientApproveModal';
    $paymentRoute = $paymentRoute ?? null;
    $requiresPayment = (bool) $paymentRoute;
    $paymentLabel = $paymentLabel ?? 'Process Payment';
    $paymentModalId = $paymentModalId ?? 'paymentModal';
    $paymentCostLabel = $paymentCostLabel ?? 'Cost';
    $paymentCostAmount = $paymentCostAmount ?? 0;
@endphp

{{-- Client Director: show approval button only if not yet approved --}}
@if ($isClientDir && !$clientApproved)
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#{{ $approveModalId }}">
        <i class="fas fa-check-circle me-1"></i> {{ $approveLabel }}
    </button>

    <!-- Client Approval Modal -->
    <div class="modal fade" id="{{ $approveModalId }}" tabindex="-1" aria-labelledby="{{ $approveModalId }}Label"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content {{ isset($data->test_result) && $data->test_result === 'positive' ? 'border-danger' : '' }}" 
                 style="{{ isset($data->test_result) && $data->test_result === 'positive' ? 'border-width: 3px !important;' : '' }}">
                <div class="modal-header {{ isset($data->test_result) && $data->test_result === 'positive' ? 'bg-danger text-white' : '' }}">
                    <h5 class="modal-title" id="{{ $approveModalId }}Label">
                        @if(isset($data->test_result) && $data->test_result === 'positive')
                            <i class="fas fa-exclamation-triangle me-2"></i>
                        @endif
                        {{ $approveLabel }}
                        @if(isset($data->test_result) && $data->test_result === 'positive')
                            <span class="badge bg-white text-danger ms-2">POSITIVE TEST</span>
                        @endif
                    </h5>
                    <button type="button" class="btn-close {{ isset($data->test_result) && $data->test_result === 'positive' ? 'btn-close-white' : '' }}" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route($approveRoute, $data->id) }}">
                    @csrf
                    <div class="modal-body">
                        {{-- Critical Alert for Positive Breathaliser Test --}}
                        @if(isset($data->test_result) && $data->test_result === 'positive')
                        <div class="alert alert-danger border-danger shadow-sm mb-3" style="background: #f8d7da; border: 2px solid #dc3545 !important; border-radius: 8px; padding: 16px;">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-exclamation-triangle me-3" style="color: #dc3545; font-size: 24px; margin-top: 2px;"></i>
                                <div style="flex: 1;">
                                    <h6 class="mb-2" style="color: #721c24; font-weight: 700; font-size: 16px;">
                                        <i class="fas fa-ban me-2"></i>CRITICAL: Driver Stand-Down Required
                                    </h6>
                                    <p class="mb-0" style="color: #721c24; font-size: 14px; line-height: 1.6; font-weight: 500;">
                                        This failure means driver must not be allowed to drive the vehicle until otherwise instructed.
                                    </p>
                                    <p class="mb-0 mt-2" style="color: #721c24; font-size: 13px; font-weight: 600;">
                                        Please acknowledge this critical status in your approval notes.
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <div class="mb-3">
                            <label class="form-label">Notes <span class="text-danger">*</span></label>
                            <textarea name="client_approved_comment" class="form-control" rows="4" placeholder="Enter approval notes..."
                                required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check-circle me-1"></i> {{ $approveLabel }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

{{-- Approved badge for Client Director --}}
@if ($isClientDir && $clientApproved)
    <span class="btn btn-success disabled">
        <i class="fas fa-check-double me-1"></i> {{ $approvedLabel }}
    </span>
@endif

{{-- Payment Processing button: visible to Admin/SuperAdmin/Champs only after client approval --}}
@if ($isPaymentProcessor && $clientApproved && $paymentReady && $paymentRoute)
    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#{{ $paymentModalId }}">
        <i class="fas fa-credit-card me-1"></i> {{ $paymentLabel }}
    </button>
@endif

@if ($isPaymentProcessor && $requiresPayment && $paymentProcessed)
    <span class="btn btn-info disabled">
        <i class="fas fa-check-circle me-1"></i> Payment Processed
    </span>
@endif

{{-- Status Change button: visible to Admin/SuperAdmin/Champs after approval; paid incidents also require payment. --}}
{{-- Client Directors do NOT see the Status Change button --}}

@if (
    request()->routeIs([
        'illegal_haulage.show',
        'illegal_shortage.show',
        'breathaliser_test.show',
        // 'accident.show',
        'route_diversion.show',
        'driver_misconduct.show',
    ])
)

    @if ($canChangeStatus)
        @if ($clientApproved && (!$requiresPayment || $paymentProcessed))
            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="{{ $modalTarget }}">
                <i class="fas fa-sync-alt me-1"></i> Status Change
            </button>
        @elseif($clientApproved && $requiresPayment)
            <span class="btn btn-secondary disabled" title="Waiting for payment processing">
                <i class="fas fa-hourglass-half me-1"></i> Awaiting Payment
            </span>
        @else
            <span class="btn btn-secondary disabled" title="Waiting for Client Director approval">
                <i class="fas fa-hourglass-half me-1"></i> Awaiting Approval
            </span>
        @endif
    @endif

@endif

{{-- Dynamic Payment Modal --}}
@if ($paymentRoute)
    <div class="modal fade" id="{{ $paymentModalId }}" tabindex="-1" aria-labelledby="{{ $paymentModalId }}Label"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="{{ $paymentModalId }}Label">{{ __('Process Payment') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route($paymentRoute, $data->id) }}" id="dynamicPaymentForm">
                    @csrf
                    <div class="modal-body">
                        {{-- Main Payment Section --}}
                        <div class="payment-main-section">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __($paymentCostLabel) }} <span
                                                class="text-danger">*</span></label>
                                        <input type="number" name="paid_amount" class="form-control paid-amount"
                                            min="0" step="0.01"
                                            value="{{ old('paid_amount', $paymentCostAmount) }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            {{ __('Payment Method') }}
                                            <span class="text-danger">*</span>
                                        </label>

                                        <select name="payment_method" class="form-select payment-method" required>
                                            <option value="">{{ __('Select Payment Method') }}</option>
                                            <option value="pos_payment">{{ __('POS Payment') }}</option>
                                            <option value="bank_transfer">{{ __('Bank Transfer') }}</option>
                                            <option value="atm_transfer">{{ __('ATM Transfer') }}</option>
                                            <option value="mobile_money">{{ __('Mobile Money') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3" id="mainReferenceWrapper">
                                        <label class="form-label">{{ __('Reference Number') }}</label>
                                        <input type="text" name="payment_reference"
                                            class="form-control payment-reference"
                                            placeholder="{{ __('Enter reference number') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('Authority') }}</label>
                                        <input type="text" name="authority" class="form-control"
                                            placeholder="{{ __('Authority') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('Paid To') }}</label>
                                        <input type="text" name="paid_to" class="form-control"
                                            placeholder="{{ __('Paid To') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('Main Price') }}</label>
                                        <input type="number" name="main_price" class="form-control main-price"
                                            placeholder="{{ __('Enter Price') }}" step="0.01" min="0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Balance Display --}}
                        <div class="alert alert-info mb-3">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <strong>{{ __('Paid Amount:') }}</strong>
                                    <span class="paid-amount-display" id="paidAmountDisplay">0.00</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>{{ __('Total Expenses:') }}</strong>
                                    <span class="total-expenses" id="totalExpenses">0.00</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>{{ __('Remaining Balance:') }}</strong>
                                    <span class="remaining-balance" id="remainingBalance">0.00</span>
                                </div>
                                <div class="col-md-3 text-end">
                                    <span class="badge bg-secondary" id="expenseCount">Expenses: 0</span>
                                </div>
                            </div>
                        </div>

                        {{-- Dynamic Expense Blocks Container --}}
                        <div id="expenseBlocksContainer">
                            {{-- No initial expense block - will be added dynamically when user clicks "Add More" --}}
                        </div>

                        {{-- Add More Button --}}
                        <div class="text-center mb-3">
                            <button type="button" class="btn btn-primary" id="addMoreExpense">
                                <i class="fas fa-plus-circle"></i> {{ __('+ Add More Expense') }}
                            </button>
                        </div>

                        {{-- Payment Notes --}}
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ __('Status') }} <span
                                            class="text-danger">*</span></label>

                                        @if($table == 'impoundment')
                                        <select name="status" class="form-select" required>
                                            <option value="">Select Status</option>
                                            <option value="0" {{ $data->status == '0' ? 'selected' : '' }}>Impounded</option>
                                            <option value="1" {{ $data->status == '1' ? 'selected' : '' }}>Released</option>
                                        </select>

                                    @elseif($table == 'vehicle-breakdown')
                                        <select name="status" class="form-select" required>
                                            <option value="">Select Status</option>
                                            <option value="0" {{ $data->status == '0' ? 'selected' : '' }}>Pending</option>
                                            <option value="1" {{ $data->status == '1' ? 'selected' : '' }}>Resolved</option>
                                            <option value="2" {{ $data->status == '2' ? 'selected' : '' }}>In Progress</option>
                                        </select>

                                    @elseif($table == 'illegal_detentions')
                                        <select name="status" class="form-select" required>
                                            <option value="">Select Status</option>
                                            <option value="0" {{ $data->status == '0' ? 'selected' : '' }}>Detained</option>
                                            <option value="1" {{ $data->status == '1' ? 'selected' : '' }}>Released</option>
                                        </select>

                                    @elseif($table == 'illegal_haulages')
                                        <select name="status" class="form-select" required>
                                            <option value="">Select Status</option>
                                            <option value="0" {{ $data->status == '0' ? 'selected' : '' }}>Open</option>
                                            <option value="1" {{ $data->status == '1' ? 'selected' : '' }}>Under Review</option>
                                            <option value="2" {{ $data->status == '2' ? 'selected' : '' }}>Resolved</option>
                                        </select>

                                    @elseif($table == 'accidents')
                                        <select name="status" class="form-select" required>
                                            <option value="">Select Status</option>
                                            <option value="0" {{ $data->status == '0' ? 'selected' : '' }}>Pending</option>
                                            <option value="1" {{ $data->status == '1' ? 'selected' : '' }}>Resolved</option>
                                            <option value="2" {{ $data->status == '2' ? 'selected' : '' }}>Progress</option>
                                        </select>

                                    @elseif($table == 'vehicle_bogged_downs')
                                        <select name="status" class="form-select" required>
                                            <option value="">Select Status</option>
                                            <option value="0" {{ $data->status == '0' ? 'selected' : '' }}>Stuck/Pending</option>
                                            <option value="1" {{ $data->status == '1' ? 'selected' : '' }}>Recovered</option>
                                            <option value="2" {{ $data->status == '2' ? 'selected' : '' }}>Recovery In Progress</option>
                                        </select>

                                        @elseif($table == 'vehicle_maintenances')
                                        <select name="status" class="form-select" required>
                                            <option value="">Select Status</option>
                                            <option value="0" {{ $data->status == '0' ? 'selected' : '' }}>Pending</option>
                                            <option value="2" {{ $data->status == '2' ? 'selected' : '' }}>In Progress</option>
                                            <option value="1" {{ $data->status == '1' ? 'selected' : '' }}>Completed</option>
                                        </select>

                                    @endif
                                </div>

                            </div>
                            <div class="col-6">
                                <div>
                                    <label class="form-label fw-semibold">{{ __('Status Remarks') }}</label>
                                    <textarea name="remarks" class="form-control" rows="3" placeholder="{{ __('Optional remarks...') }}">{{ $remarks ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Payment Notes with Character Counter --}}
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Notes (Optional)') }}</label>
                                    <div class="position-relative">
                                        <textarea name="payment_notes"
                                            class="form-control payment-notes"
                                            rows="3"
                                            maxlength="1000"
                                            placeholder="{{ __('Enter payment notes...') }}"
                                            aria-describedby="notesCounter"
                                        ></textarea>
                                        <div class="text-end mt-1">
                                            <small id="notesCounter" class="text-muted">
                                                <span id="charCount">0</span> / 1000 characters
                                            </small>
                                        </div>
                                    </div>
                                    <div class="invalid-feedback" id="notesFeedback">
                                        Payment notes must not exceed 1000 characters.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="me-auto">
                            <small class="text-muted" id="validationMessage"></small>
                        </div>
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-success" id="submitPayment">
                            <i class="fas fa-check-circle me-1"></i> {{ __('Proceed Payment') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- JavaScript for Dynamic Functionality --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Only initialize if the payment modal exists
            const modal = document.getElementById('{{ $paymentModalId }}');
            if (!modal) return;

            let blockCounter = 0;
            let expenseCount = 0;
            let isUpdating = false;

            // ============================================
            // PAYMENT NOTES CHARACTER COUNTER
            // ============================================
            const notesTextarea = modal.querySelector('.payment-notes');
            const charCountSpan = document.getElementById('charCount');
            const notesFeedback = document.getElementById('notesFeedback');
            const MAX_CHARS = 1000;

            if (notesTextarea && charCountSpan) {
                // Initial count
                updateCharCount(notesTextarea, charCountSpan, notesFeedback, MAX_CHARS);

                // Real-time update
                notesTextarea.addEventListener('input', function() {
                    updateCharCount(this, charCountSpan, notesFeedback, MAX_CHARS);

                    // Also check form validity
                    validateNotesField(this, notesFeedback, MAX_CHARS);
                });

                // Check on blur
                notesTextarea.addEventListener('blur', function() {
                    validateNotesField(this, notesFeedback, MAX_CHARS);
                });
            }

            // Function to update character count
            function updateCharCount(textarea, counter, feedback, max) {
                const currentLength = textarea.value.length;
                counter.textContent = currentLength;

                // Change color based on remaining characters
                const remaining = max - currentLength;
                if (remaining <= 0) {
                    counter.style.color = '#dc3545'; // Red
                } else if (remaining <= 100) {
                    counter.style.color = '#ffc107'; // Yellow warning
                } else {
                    counter.style.color = '#6c757d'; // Default gray
                }
            }

            // Function to validate notes field
            function validateNotesField(textarea, feedback, max) {
                const currentLength = textarea.value.length;

                if (currentLength > max) {
                    textarea.classList.add('is-invalid');
                    if (feedback) {
                        feedback.style.display = 'block';
                    }
                    return false;
                } else {
                    textarea.classList.remove('is-invalid');
                    if (feedback) {
                        feedback.style.display = 'none';
                    }
                    return true;
                }
            }

            // ============================================
            // REFERENCE NUMBER TOGGLE (MAIN PAYMENT)
            // ============================================
            const mainMethod = modal.querySelector('.payment-method');
            const mainReferenceWrapper = document.getElementById('mainReferenceWrapper');
            const mainReference = modal.querySelector('.payment-reference');

            if (mainMethod && mainReferenceWrapper) {
                toggleReferenceField(mainMethod, mainReferenceWrapper);

                mainMethod.addEventListener('change', function() {
                    toggleReferenceField(this, mainReferenceWrapper);
                });
            }

            function toggleReferenceField(methodSelect, wrapperElement) {
                const method = methodSelect.value;
                const requiresReference = method === 'bank_transfer' || method === 'atm_transfer';

                const label = wrapperElement.querySelector('label');
                const input = wrapperElement.querySelector('input');

                if (requiresReference) {
                    wrapperElement.style.display = '';
                    if (input) {
                        input.setAttribute('required', 'required');
                        input.disabled = false;
                    }
                    if (label && !label.querySelector('.text-danger')) {
                        label.innerHTML = label.textContent + ' <span class="text-danger">*</span>';
                    }
                } else {
                    wrapperElement.style.display = 'none';
                    if (input) {
                        input.removeAttribute('required');
                        input.disabled = true;
                        input.value = '';
                    }
                    if (label) {
                        const star = label.querySelector('.text-danger');
                        if (star) star.remove();
                    }
                }
            }

            // ============================================
            // EXPENSE BLOCKS
            // ============================================
            function setupExpenseBlockListeners(block) {
                const methodSelect = block.querySelector('.expense-payment-method');
                const referenceWrapper = block.querySelector('.expense-reference-wrapper');

                if (methodSelect && referenceWrapper) {
                    toggleReferenceField(methodSelect, referenceWrapper);

                    methodSelect.addEventListener('change', function() {
                        toggleReferenceField(this, referenceWrapper);
                    });
                }

                const priceInputs = block.querySelectorAll('.expense-price');
                priceInputs.forEach(function(input) {
                    input.addEventListener('input', function() {
                        if (!isUpdating) {
                            calculateBalances();
                        }
                    });
                });

                if (methodSelect) {
                    methodSelect.addEventListener('change', function() {
                        calculateBalances();
                    });
                }
            }

            function createExpenseBlock(blockId) {
                const block = document.createElement('div');
                block.className = 'expense-block card mb-3';
                block.setAttribute('data-block-id', blockId);
                block.style.animation = 'slideDown 0.4s ease';

                block.innerHTML = `
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Expense #${blockId}</h6>
                        <button type="button" class="btn btn-sm btn-danger remove-block">
                            <i class="fas fa-trash"></i> Remove
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">
                                        Payment Method
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select
                                        name="expenses[${blockId}][payment_method]"
                                        class="form-select expense-payment-method"
                                        required
                                    >
                                        <option value="">Select Payment Method</option>
                                        <option value="pos_payment">POS Payment</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="atm_transfer">ATM Transfer</option>
                                        <option value="mobile_money">Mobile Money</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3 expense-reference-wrapper">
                                    <label class="form-label">Reference Number</label>
                                    <input type="text" name="expenses[${blockId}][reference_number]"
                                        class="form-control expense-reference" placeholder="Enter reference">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Authority</label>
                                    <input type="text" name="expenses[${blockId}][authority]" class="form-control" placeholder="Authority">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Paid To</label>
                                    <input type="text" name="expenses[${blockId}][paid_to]" class="form-control" placeholder="Paid To">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Price <span class="text-danger">*</span></label>
                                    <input type="number" name="expenses[${blockId}][price]" class="form-control expense-price"
                                        placeholder="Enter amount" step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                return block;
            }

            // ============================================
            // BALANCE CALCULATIONS
            // ============================================
            function calculateBalances() {
                const paidAmount = parseFloat(modal.querySelector('.paid-amount')?.value) || 0;
                let totalExpenses = 0;

                const paidDisplay = document.getElementById('paidAmountDisplay');
                if (paidDisplay) paidDisplay.textContent = paidAmount.toFixed(2);

                const expensePrices = modal.querySelectorAll('.main-price, .expense-price');
                expensePrices.forEach(function(input) {
                    const price = parseFloat(input.value) || 0;
                    totalExpenses += price;
                });

                const remainingBalance = paidAmount - totalExpenses;

                const totalExpensesEl = document.getElementById('totalExpenses');
                const remainingBalanceEl = document.getElementById('remainingBalance');
                const validationMsg = document.getElementById('validationMessage');
                const submitBtn = document.getElementById('submitPayment');
                const expenseCountEl = document.getElementById('expenseCount');
                const addMoreBtn = document.getElementById('addMoreExpense');
                const blocks = modal.querySelectorAll('.expense-block');

                if (totalExpensesEl) totalExpensesEl.textContent = totalExpenses.toFixed(2);
                if (remainingBalanceEl) {
                    remainingBalanceEl.textContent = remainingBalance.toFixed(2);

                    if (remainingBalance < 0) {
                        remainingBalanceEl.style.color = 'red';
                        remainingBalanceEl.className = 'remaining-balance text-danger';
                    } else if (remainingBalance === 0) {
                        remainingBalanceEl.style.color = 'green';
                        remainingBalanceEl.className = 'remaining-balance text-success';
                    } else {
                        remainingBalanceEl.style.color = '#0d6efd';
                        remainingBalanceEl.className = 'remaining-balance';
                    }
                }

                if (expenseCountEl) {
                    expenseCountEl.textContent = 'Expenses: ' + blocks.length;
                }

                if (addMoreBtn) {
                    if (remainingBalance <= 0 || paidAmount <= 0) {
                        addMoreBtn.disabled = true;
                        addMoreBtn.title = paidAmount <= 0 ? 'Please enter paid amount first' :
                            'Cannot add more expenses - balance is fully utilized';
                        addMoreBtn.classList.add('opacity-50');
                    } else {
                        addMoreBtn.disabled = false;
                        addMoreBtn.title = '';
                        addMoreBtn.classList.remove('opacity-50');
                    }
                }

                // Check notes validation
                let notesValid = true;
                if (notesTextarea) {
                    const notesLength = notesTextarea.value.length;
                    notesValid = notesLength <= MAX_CHARS;
                    if (notesTextarea) {
                        validateNotesField(notesTextarea, notesFeedback, MAX_CHARS);
                    }
                }

                if (submitBtn) {
                    if (remainingBalance < 0 || !notesValid) {
                        submitBtn.disabled = true;
                        submitBtn.title = !notesValid ? 'Notes exceed 1000 characters' :
                            'Cannot submit - expenses exceed the paid amount';
                    } else {
                        submitBtn.disabled = false;
                        submitBtn.title = '';
                    }
                }

                if (validationMsg) {
                    let message = '';
                    if (!notesValid) {
                        message = '<span class="text-danger">⚠️ Payment notes exceed 1000 characters!</span>';
                    } else if (remainingBalance < 0) {
                        message = '<span class="text-danger">⚠️ Expenses exceed paid amount by ' +
                            Math.abs(remainingBalance).toFixed(2) + '!</span>';
                    } else if (remainingBalance === 0) {
                        message = '<span class="text-success">✓ Balance fully utilized. Cannot add more expenses.</span>';
                    } else {
                        message = '<span class="text-info">ℹ️ Remaining balance: ' +
                            remainingBalance.toFixed(2) + '</span>';
                    }
                    validationMsg.innerHTML = message;
                }

                // Validate individual expense prices
                expensePrices.forEach(function(input) {
                    const price = parseFloat(input.value) || 0;
                    const currentRemaining = paidAmount - totalExpenses + price;

                    if (price > paidAmount) {
                        input.classList.add('is-invalid');
                        let feedback = input.parentElement.querySelector('.invalid-feedback');
                        if (!feedback) {
                            feedback = document.createElement('div');
                            feedback.className = 'invalid-feedback';
                            feedback.textContent = 'Price cannot exceed total paid amount!';
                            input.parentElement.appendChild(feedback);
                        }
                    } else if (price > currentRemaining && price > 0) {
                        input.classList.add('is-invalid');
                        let feedback = input.parentElement.querySelector('.invalid-feedback');
                        if (!feedback) {
                            feedback = document.createElement('div');
                            feedback.className = 'invalid-feedback';
                            feedback.textContent = 'Price exceeds remaining balance!';
                            input.parentElement.appendChild(feedback);
                        }
                    } else {
                        input.classList.remove('is-invalid');
                        const feedback = input.parentElement.querySelector('.invalid-feedback');
                        if (feedback) feedback.remove();
                    }
                });
            }

            // ============================================
            // ADD MORE EXPENSE
            // ============================================
            const addButton = document.getElementById('addMoreExpense');
            if (addButton) {
                addButton.addEventListener('click', function() {
                    const paidAmount = parseFloat(modal.querySelector('.paid-amount')?.value) || 0;
                    let totalExpenses = 0;
                    modal.querySelectorAll('.main-price, .expense-price').forEach(function(input) {
                        totalExpenses += parseFloat(input.value) || 0;
                    });
                    const remainingBalance = paidAmount - totalExpenses;

                    if (paidAmount <= 0) {
                        showNotification('Please enter the paid amount first!', 'warning');
                        return;
                    }

                    if (remainingBalance <= 0) {
                        showNotification('Cannot add more expenses. Balance is fully utilized!', 'warning');
                        return;
                    }

                    blockCounter++;
                    expenseCount++;

                    const container = document.getElementById('expenseBlocksContainer');
                    const newBlock = createExpenseBlock(blockCounter);
                    container.appendChild(newBlock);

                    setupExpenseBlockListeners(newBlock);

                    const removeBtn = newBlock.querySelector('.remove-block');
                    if (removeBtn) {
                        removeBtn.addEventListener('click', function() {
                            const block = this.closest('.expense-block');
                            const container = document.getElementById('expenseBlocksContainer');

                            if (container.querySelectorAll('.expense-block').length < 1) {
                                showNotification('Cannot remove the last expense block.', 'warning');
                                return;
                            }

                            block.style.animation = 'slideUp 0.3s ease';
                            setTimeout(function() {
                                block.remove();
                                calculateBalances();
                                showNotification('Expense block removed.', 'info');
                            }, 300);
                        });
                    }

                    calculateBalances();
                    showNotification('Expense block added successfully!', 'success');
                });
            }

            // ============================================
            // INPUT LISTENERS FOR BALANCE CALCULATION
            // ============================================
            modal.querySelectorAll('.paid-amount, .main-price').forEach(function(input) {
                input.addEventListener('input', function() {
                    if (!isUpdating) {
                        calculateBalances();
                    }
                });
            });

            // ============================================
            // FORM VALIDATION
            // ============================================
            const form = document.getElementById('dynamicPaymentForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Check notes length
                    if (notesTextarea) {
                        const notesLength = notesTextarea.value.length;
                        if (notesLength > MAX_CHARS) {
                            e.preventDefault();
                            showNotification('Payment notes must not exceed 1000 characters.', 'danger');
                            notesTextarea.focus();
                            return false;
                        }
                    }

                    const remainingBalance = parseFloat(document.getElementById('remainingBalance')
                        ?.textContent) || 0;

                    if (remainingBalance < 0) {
                        e.preventDefault();
                        showNotification('Cannot submit: Balance is fully utilized or exceeded!', 'danger');
                        return false;
                    }

                    let isValid = true;
                    const blocks = modal.querySelectorAll('.expense-block');

                    blocks.forEach(function(block) {
                        const method = block.querySelector('.expense-payment-method');
                        const price = block.querySelector('.expense-price');
                        const refWrapper = block.querySelector('.expense-reference-wrapper');
                        const ref = refWrapper ? refWrapper.querySelector('input') : null;

                        if (!method || !method.value) {
                            isValid = false;
                            if (method) method.classList.add('is-invalid');
                        }

                        if (!price || !price.value || parseFloat(price.value) <= 0) {
                            isValid = false;
                            if (price) price.classList.add('is-invalid');
                        }

                        if (method && method.value &&
                            (method.value === 'bank_transfer' || method.value === 'atm_transfer') &&
                            refWrapper && refWrapper.style.display !== 'none') {
                            if (!ref || !ref.value) {
                                isValid = false;
                                if (ref) ref.classList.add('is-invalid');
                            }
                        }
                    });

                    if (!isValid) {
                        e.preventDefault();
                        showNotification('Please fill all required fields in expense blocks.', 'warning');
                        return false;
                    }
                });
            }

            // ============================================
            // NOTIFICATIONS
            // ============================================
            function showNotification(message, type = 'info') {
                const modalBody = modal.querySelector('.modal-body');
                if (!modalBody) return;

                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-' + type + ' alert-dismissible fade show';
                alertDiv.role = 'alert';
                alertDiv.innerHTML = message +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';

                modalBody.prepend(alertDiv);

                setTimeout(function() {
                    alertDiv.style.transition = 'opacity 0.3s ease';
                    alertDiv.style.opacity = '0';
                    setTimeout(function() {
                        alertDiv.remove();
                    }, 300);
                }, 3000);
            }

            // ============================================
            // MODAL RESET
            // ============================================
            modal.addEventListener('hidden.bs.modal', function() {
                const form = document.getElementById('dynamicPaymentForm');
                if (form) form.reset();

                const validationMsg = document.getElementById('validationMessage');
                if (validationMsg) validationMsg.innerHTML = '';

                const submitBtn = document.getElementById('submitPayment');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.title = '';
                }

                const addMoreBtn = document.getElementById('addMoreExpense');
                if (addMoreBtn) {
                    addMoreBtn.disabled = false;
                    addMoreBtn.title = '';
                    addMoreBtn.classList.remove('opacity-50');
                }

                const container = document.getElementById('expenseBlocksContainer');
                if (container) {
                    container.innerHTML = '';
                }

                // Reset character counter
                if (notesTextarea) {
                    notesTextarea.value = '';
                    updateCharCount(notesTextarea, charCountSpan, notesFeedback, MAX_CHARS);
                    validateNotesField(notesTextarea, notesFeedback, MAX_CHARS);
                }

                blockCounter = 0;
                expenseCount = 0;
                calculateBalances();
            });

            // ============================================
            // INITIALIZATION
            // ============================================
            setTimeout(calculateBalances, 100);

            // ============================================
            // CSS STYLES
            // ============================================
            const style = document.createElement('style');
            style.textContent = `
                @keyframes slideDown {
                    from {
                        opacity: 0;
                        transform: translateY(-20px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
                @keyframes slideUp {
                    from {
                        opacity: 1;
                        transform: translateY(0);
                    }
                    to {
                        opacity: 0;
                        transform: translateY(-20px);
                    }
                }
                .expense-block {
                    transition: all 0.3s ease;
                    border-left: 4px solid #0d6efd;
                }
                .expense-block .card-header {
                    background-color: #f8f9fa;
                }
                .expense-block .remove-block {
                    transition: all 0.2s ease;
                }
                .expense-block .remove-block:hover {
                    transform: scale(1.1);
                }
                #addMoreExpense {
                    transition: all 0.3s ease;
                }
                #addMoreExpense:hover:not(:disabled) {
                    transform: scale(1.05);
                    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                }
                #addMoreExpense:disabled {
                    cursor: not-allowed;
                    opacity: 0.5;
                }
                .remaining-balance {
                    font-size: 1.2em;
                    font-weight: bold;
                    transition: color 0.3s ease;
                }
                .total-expenses {
                    font-size: 1.2em;
                    font-weight: bold;
                    color: #0d6efd;
                }
                .paid-amount-display {
                    font-size: 1.2em;
                    font-weight: bold;
                    color: #198754;
                }
                .is-invalid {
                    border-color: #dc3545 !important;
                }
                .opacity-50 {
                    opacity: 0.5;
                }
                .payment-notes.is-invalid {
                    border-color: #dc3545;
                }
                #charCount {
                    font-weight: bold;
                    transition: color 0.3s ease;
                }
                .invalid-feedback {
                    display: none;
                }
                .invalid-feedback.show {
                    display: block;
                }
            `;
            document.head.appendChild(style);
        });
    </script>
@endif
