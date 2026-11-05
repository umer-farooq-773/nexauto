@props([
    'name' => 'location',
    'id' => null,
    'placeholder' => 'Search for location...',
    'value' => '',
    'lat' => '',
    'lng' => '',
    'required' => false,
    'extraClasses' => '',
    'helpText' => null,
])

@php
    // Use provided ID or create a stable one from the name
    $fieldId = $id ?? str_replace(['[', ']', '.'], '_', $name);
    $latFieldId = $fieldId . '_lat';
    $lngFieldId = $fieldId . '_lng';
    $suggestionsId = $fieldId . '_suggestions';
@endphp

<div class="mb-3 {{ $extraClasses }}">
    <div class="position-relative">
        <input
            type="text"
            class="form-control geoapify-location-input"
            style="padding: 12px;"
            id="{{ $fieldId }}"
            name="{{ $name }}"
            value="{{ $value }}"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            data-lat-field="{{ $latFieldId }}"
            data-lng-field="{{ $lngFieldId }}"
            data-suggestions="{{ $suggestionsId }}"
            {{ $required ? 'required' : '' }}
        >

        <!-- Hidden fields to store lat/lng -->
        <input type="hidden" id="{{ $latFieldId }}" name="{{ $name }}_lat" value="{{ $lat }}">
        <input type="hidden" id="{{ $lngFieldId }}" name="{{ $name }}_lng" value="{{ $lng }}">

        <!-- Dropdown for suggestions -->
        <div id="{{ $suggestionsId }}" class="geoapify-suggestions" style="display: none;"></div>
    </div>

    @if($helpText)
        <small class="form-text text-muted">{{ $helpText }}</small>
    @endif

    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

@once
<style>
.geoapify-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-top: none;
    border-radius: 0 0 4px 4px;
    max-height: 300px;
    overflow-y: auto;
    z-index: 2060;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.geoapify-suggestion-item {
    padding: 10px 15px;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
    transition: background-color 0.2s;
}

.geoapify-suggestion-item:hover {
    background-color: #f8f9fa;
}

.geoapify-suggestion-item:last-child {
    border-bottom: none;
}

.geoapify-suggestion-main {
    font-weight: 500;
    color: #333;
}

.geoapify-suggestion-secondary {
    font-size: 0.85em;
    color: #666;
    margin-top: 2px;
}

.geoapify-location-input:focus + .geoapify-suggestions {
    border-color: #86b7fe;
}
</style>

<script>
(function() {
    const GEOAPIFY_API_KEY = '{{ config('services.geoapify.api_key') }}';
    const DEBOUNCE_DELAY = 300;

    function initGeoapifyLocation(input) {
        if (!input || input.dataset.geoapifyInit) return;
        input.dataset.geoapifyInit = 'true';

        const latFieldId = input.dataset.latField;
        const lngFieldId = input.dataset.lngField;
        const suggestionsId = input.dataset.suggestions;

        const latInput = document.getElementById(latFieldId);
        const lngInput = document.getElementById(lngFieldId);
        const suggestionsDiv = document.getElementById(suggestionsId);

        if (!latInput || !lngInput || !suggestionsDiv) {
            console.error('Geoapify: Missing required elements for', input.id);
            return;
        }

        let debounceTimer = null;

        input.addEventListener('input', function() {
            const query = this.value.trim();

            // Clear coordinates when user modifies the input text
            latInput.value = '';
            lngInput.value = '';

            clearTimeout(debounceTimer);

            if (query.length < 2) {
                suggestionsDiv.style.display = 'none';
                return;
            }

            debounceTimer = setTimeout(() => {
                fetchSuggestions(query, suggestionsDiv, input, latInput, lngInput);
            }, DEBOUNCE_DELAY);
        });

        // Close suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !suggestionsDiv.contains(e.target)) {
                suggestionsDiv.style.display = 'none';
            }
        });

        // Show suggestions when clicking on input if it has text
        input.addEventListener('click', function() {
            if (this.value.trim().length >= 2 && suggestionsDiv.children.length > 0) {
                suggestionsDiv.style.display = 'block';
            }
        });
    }

    function fetchSuggestions(query, suggestionsDiv, input, latInput, lngInput) {
        const url = `https://api.geoapify.com/v1/geocode/autocomplete?text=${encodeURIComponent(query)}&apiKey=${GEOAPIFY_API_KEY}&limit=5`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.features && data.features.length > 0) {
                    displaySuggestions(data.features, suggestionsDiv, input, latInput, lngInput);
                } else {
                    suggestionsDiv.innerHTML = '<div class="geoapify-suggestion-item">No results found</div>';
                    suggestionsDiv.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Geoapify autocomplete error:', error);
                suggestionsDiv.style.display = 'none';
            });
    }

    function displaySuggestions(features, suggestionsDiv, input, latInput, lngInput) {
        suggestionsDiv.innerHTML = '';

        features.forEach(feature => {
            const item = document.createElement('div');
            item.className = 'geoapify-suggestion-item';

            const mainText = document.createElement('div');
            mainText.className = 'geoapify-suggestion-main';
            mainText.textContent = feature.properties.name || feature.properties.formatted;

            const secondaryText = document.createElement('div');
            secondaryText.className = 'geoapify-suggestion-secondary';
            secondaryText.textContent = feature.properties.formatted;

            item.appendChild(mainText);
            item.appendChild(secondaryText);

            item.addEventListener('click', function() {
                input.value = feature.properties.formatted;
                latInput.value = feature.properties.lat;
                lngInput.value = feature.properties.lon;
                suggestionsDiv.style.display = 'none';

                // Trigger change event for form validation
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });

            suggestionsDiv.appendChild(item);
        });

        suggestionsDiv.style.display = 'block';
    }

    // Initialize all Geoapify location inputs
    function initAllGeoapifyInputs() {
        document.querySelectorAll('.geoapify-location-input').forEach(input => {
            initGeoapifyLocation(input);
        });
    }

    // Initialize on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAllGeoapifyInputs);
    } else {
        initAllGeoapifyInputs();
    }

    // Re-initialize when Bootstrap modals are shown (for edit forms in modals)
    document.addEventListener('shown.bs.modal', function() {
        setTimeout(initAllGeoapifyInputs, 100);
    });
})();
</script>
@endonce
