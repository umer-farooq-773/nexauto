<div class="form-floating mb-3">
    <select id="vehicle_type" name="vehicle_type" class="form-select select2" data-placeholder="Select Asset Type">
        <option value="">Select Asset Type</option>
        @foreach($list as $item)
            <option value="{{ $item->id }}"
                {{ (isset($existingId) && (strtolower((string)$existingId) == strtolower((string)$item->id) || strtolower((string)$existingId) == strtolower((string)$item->name))) ? 'selected' : '' }}>
                {{ $item->name }}
            </option>
        @endforeach
    </select>
    <label for="vehicle_type">Asset Type</label>
</div>
