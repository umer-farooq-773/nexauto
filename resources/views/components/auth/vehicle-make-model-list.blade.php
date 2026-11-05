<div class="form-floating mb-3">
    <select id="make_model" name="make_model" class="form-select select2" data-placeholder="Select Make/Model">
        <option value="">Select Make/Model</option>
        @foreach($list as $item)
            <option value="{{ $item->id }}"
                {{ (isset($existingId) && $existingId == $item->id) ? 'selected' : '' }}>
                {{ $item->name }}
            </option>
        @endforeach
    </select>
    <label for="make_model">Make/Model</label>
</div>
