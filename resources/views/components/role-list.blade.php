<div class="form-floating mb-3">
    <select name="role" id="role" class="form-select select2-enable">
        <option value="">Select Account Type</option>
        @foreach($list as $item)
            <option value="{{ $item->id }}" {{ ($existingId ?? '') == $item->id ? 'selected' : '' }}>
                {{ $item->title ?? $item->name }}
            </option>
        @endforeach
    </select>
    <label for="role">Select Account Type</label>
</div>
