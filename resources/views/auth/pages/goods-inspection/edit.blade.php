<x-layouts.auth page-title="{{ __('Edit Goods Inspection') }}" sub-title="">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5>{{ __('Update Goods Inspection Record') }}</h5>
        <a href="{{ route('goods_inspection.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-2"></i> {{ __('Back') }}
        </a>
    </div>

    <div class="bg-white p-4 rounded-3 border">
        <x-auth.add-goods-inspection-form 
            :route-action="route('goods_inspection.update', $data->id)" 
            :data="$data" 
            :vehicles="$vehicles" 
            :waybills="$waybills"
            :is-edit="true" 
            :incident-evaluation="$incidentEvaluation ?? null"
        />
    </div>


</x-layouts.auth>
