<div class="form-floating mb-3">
    <select name="license_category_class" id="license_category_class" class="form-select">
        <option value="">Select Category</option>
        @foreach($list as $item)
            <option value="{{ $item->name }}" {{ ($existingVal ?? '') == $item->name ? 'selected' : '' }}>
                {{ $item->name }}
            </option>
        @endforeach
    </select>
    <label for="license_category_class">License Category</label>
</div>
