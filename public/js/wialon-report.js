/**
 * wialon-report.js
 *
 * Handles native Wialon Report interface workflow:
 * Template -> Template Type -> Dynamic Object Selection (Vehicles vs Vehicle Groups) -> Interval -> Execute
 *
 * Unified "Sync Wialon" operation with full reconciliation and immediate state hydration.
 */
document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    const form = document.getElementById('report-filter-form');
    const fromInput = document.getElementById('from');
    const toInput = document.getElementById('to');
    const loadingEl = document.getElementById('report-loading');
    const emptyEl = document.getElementById('report-empty');
    const tablesContainer = document.getElementById('report-tables-container');
    const chartContainer = document.getElementById('report-chart-container');
    const chartCanvas = document.getElementById('report-chart');
    const generateBtn = document.getElementById('generate-btn');
    const clearBtn = document.getElementById('clear-btn');
    const syncBtn = document.getElementById('sync-wialon-btn') || document.getElementById('sync-templates-btn');
    const objectLabel = document.getElementById('object-label');

    let chartInstance = null;

    // Initialize Map
    if (typeof WialonReportMap !== 'undefined') {
        WialonReportMap.init('wialon-report-map', { zoom: 4 });
    }

    // ==========================================
    // 1. REACTIVE STATE
    // ==========================================
    const initial = window.initialWialonData || {};
    const state = {
        templates: Array.isArray(initial.templates) ? initial.templates : [],
        vehicles: Array.isArray(initial.vehicles) ? initial.vehicles : [],
        groups: Array.isArray(initial.groups) ? initial.groups : [],
        selectedTemplate: null,
        selectedObjectId: null,
    };

    // ==========================================
    // 2. SEARCHABLE COMBOBOX COMPONENT
    // ==========================================
    function createSearchableCombobox(config) {
        const input = document.getElementById(config.inputId);
        const clearBtn = document.getElementById(config.clearId);
        const dropdown = document.getElementById(config.dropdownId);
        const select = document.getElementById(config.selectId);
        const combobox = input ? input.closest('.custom-combobox') : null;

        let options = [];

        function setOptions(items) {
            options = Array.isArray(items) ? items : [];

            // Update native select options
            if (select) {
                const currentVal = select.value;
                select.innerHTML = `<option value="">${config.defaultOptionText || 'Select...'}</option>`;
                options.forEach(opt => {
                    const optEl = document.createElement('option');
                    optEl.value = opt.value;
                    optEl.textContent = opt.label;
                    select.appendChild(optEl);
                });
                select.value = currentVal;
            }

            // Sync input label if currently selected
            if (select && select.value) {
                const matched = options.find(o => String(o.value) === String(select.value));
                if (matched) {
                    input.value = matched.label;
                    toggleClear();
                } else {
                    clear();
                }
            }
        }

        function renderDropdown(query) {
            if (!dropdown) return;
            const q = (query || '').trim().toLowerCase();
            dropdown.innerHTML = '';

            const filtered = options.filter(opt =>
                String(opt.label).toLowerCase().includes(q)
            );

            if (!filtered.length) {
                dropdown.innerHTML = `<div class="dropdown-empty">${options.length === 0 ? 'No items available' : 'No results found'}</div>`;
                dropdown.classList.add('open');
                return;
            }

            filtered.forEach(opt => {
                const div = document.createElement('div');
                div.className = 'dropdown-item-custom';

                let text = opt.label;
                if (q) {
                    const regex = new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
                    text = text.replace(regex, '<mark>$1</mark>');
                }
                div.innerHTML = text;

                div.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    setValue(opt.value, opt.label, opt.raw);
                    dropdown.classList.remove('open');
                });

                dropdown.appendChild(div);
            });

            dropdown.classList.add('open');
        }

        function toggleClear() {
            if (clearBtn && input) {
                clearBtn.classList.toggle('show', input.value.length > 0);
            }
        }

        function closeDropdown() {
            if (dropdown) dropdown.classList.remove('open');
        }

        function setValue(val, label, raw) {
            if (select) select.value = val;
            if (input) input.value = label || '';
            toggleClear();
            if (typeof config.onSelect === 'function') {
                config.onSelect(val, { value: val, label: label, raw: raw });
            }
        }

        function clear() {
            if (select) select.value = '';
            if (input) input.value = '';
            toggleClear();
            closeDropdown();
            if (typeof config.onClear === 'function') {
                config.onClear();
            }
        }

        function setPlaceholder(text) {
            if (input) input.placeholder = text;
        }

        function getValue() {
            return select ? select.value : '';
        }

        function getLabel() {
            return input ? input.value : '';
        }

        if (input) {
            input.addEventListener('focus', () => renderDropdown(input.value));
            input.addEventListener('input', function () {
                if (select) select.value = '';
                toggleClear();
                renderDropdown(input.value);
            });
            input.addEventListener('blur', () => setTimeout(closeDropdown, 180));

            input.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    closeDropdown();
                    input.blur();
                }
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const item = dropdown?.querySelector('.dropdown-item-custom');
                    if (item) item.dispatchEvent(new Event('mousedown'));
                }
            });
        }

        if (clearBtn) {
            clearBtn.addEventListener('mousedown', function (e) {
                e.preventDefault();
                clear();
                if (input) input.focus();
                renderDropdown('');
            });
        }

        document.addEventListener('click', function (e) {
            if (combobox && !combobox.contains(e.target)) {
                closeDropdown();
            }
        });

        // Initialize from initial select options if available
        if (select) {
            const initialOpts = Array.from(select.options)
                .filter(opt => opt.value)
                .map(opt => ({
                    value: opt.value,
                    label: opt.textContent.trim(),
                    raw: null,
                }));
            if (initialOpts.length > 0) {
                options = initialOpts;
            }
        }

        return {
            setOptions,
            setValue,
            clear,
            setPlaceholder,
            getValue,
            getLabel,
            getOptions: () => options,
        };
    }

    // ==========================================
    // 3. COMBOBOX INSTANCES & DYNAMIC BINDING
    // ==========================================
    const templateCombobox = createSearchableCombobox({
        inputId: 'template-search',
        clearId: 'template-clear',
        dropdownId: 'template-dropdown',
        selectId: 'template_id',
        defaultOptionText: 'Select Template',
        onSelect: (templateId, item) => {
            handleTemplateSelection(templateId, item);
        },
        onClear: () => {
            handleTemplateClear();
        }
    });

    const objectCombobox = createSearchableCombobox({
        inputId: 'object-search',
        clearId: 'object-clear',
        dropdownId: 'object-dropdown',
        selectId: 'report_object_id',
        defaultOptionText: 'Select Object',
        onSelect: (objectId) => {
            state.selectedObjectId = objectId;
            const legacyUnitInput = document.getElementById('wialon_unit_id');
            if (legacyUnitInput) legacyUnitInput.value = objectId;
        },
        onClear: () => {
            state.selectedObjectId = null;
            const legacyUnitInput = document.getElementById('wialon_unit_id');
            if (legacyUnitInput) legacyUnitInput.value = '';
        }
    });

    // Populate initial template options
    function populateTemplateCombobox() {
        templateCombobox.setOptions(state.templates.map(t => ({
            value: t.id,
            label: t.name,
            raw: t,
        })));
    }

    /**
     * Handle Template Selection:
     * 1. Inspect template type (unit vs unit_group)
     * 2. Clear previously selected object immediately
     * 3. Switch Object dropdown to show appropriate entities
     */
    function handleTemplateSelection(templateId, item) {
        const template = item?.raw || state.templates.find(t => String(t.id) === String(templateId));
        state.selectedTemplate = template || null;

        // CRITICAL RULE: Clear previous object immediately upon template change
        objectCombobox.clear();
        state.selectedObjectId = null;
        const legacyUnitInput = document.getElementById('wialon_unit_id');
        if (legacyUnitInput) legacyUnitInput.value = '';

        if (!template) {
            handleTemplateClear();
            return;
        }

        const objType = template.objectType || template.templateType || 'unit';

        if (objType === 'unit_group') {
            if (objectLabel) objectLabel.textContent = 'Vehicle Group';
            objectCombobox.setPlaceholder('Search vehicle group...');
            objectCombobox.setOptions(state.groups.map(g => ({
                value: g.id,
                label: g.display_name || `${g.name} (${g.units_count} units)`,
                raw: g,
            })));
        } else {
            // Default: 'unit' / vehicle report
            if (objectLabel) objectLabel.textContent = 'Vehicle';
            objectCombobox.setPlaceholder('Search vehicle...');
            objectCombobox.setOptions(state.vehicles.map(v => ({
                value: v.id,
                label: v.display_name || (v.truck_no ? `${v.fleet_no} (${v.truck_no})` : v.fleet_no),
                raw: v,
            })));
        }
    }

    function handleTemplateClear() {
        state.selectedTemplate = null;
        state.selectedObjectId = null;
        objectCombobox.clear();
        const legacyUnitInput = document.getElementById('wialon_unit_id');
        if (legacyUnitInput) legacyUnitInput.value = '';

        if (objectLabel) objectLabel.textContent = 'Vehicle';
        objectCombobox.setPlaceholder('Select a template first...');
        // Default to vehicles list if no template selected
        objectCombobox.setOptions(state.vehicles.map(v => ({
            value: v.id,
            label: v.display_name || v.fleet_no,
            raw: v,
        })));
    }

    // Initialize templates and initial object list
    populateTemplateCombobox();
    handleTemplateClear();

    // ==========================================
    // 4. UNIFIED SYNC WIALON OPERATION
    // ==========================================
    async function syncWialon(isManual = false) {
        if (!form) return;
        const originalHtml = syncBtn ? syncBtn.innerHTML : '';

        if (syncBtn) {
            syncBtn.disabled = true;
            syncBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Syncing Wialon...';
        }

        try {
            const syncUrl = form.dataset.syncUrl || '/my-account/reports-analytics/sync';
            const response = await fetch(syncUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                },
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                if (isManual) {
                    showAlert(data.message || 'Wialon synchronization failed.', 'danger');
                } else {
                    console.warn('Wialon automatic synchronization failed:', data.message);
                }
                return;
            }

            // 1. Update in-memory state with freshly synchronized authoritative data
            state.templates = Array.isArray(data.data?.templates) ? data.data.templates : [];
            state.vehicles  = Array.isArray(data.data?.vehicles)  ? data.data.vehicles  : [];
            state.groups    = Array.isArray(data.data?.groups)    ? data.data.groups    : [];

            // 2. Re-populate template combobox
            const currentTemplateId = templateCombobox.getValue();
            populateTemplateCombobox();

            // 3. Re-evaluate selected template
            if (currentTemplateId) {
                const matchedTemplate = state.templates.find(t => String(t.id) === String(currentTemplateId));
                if (matchedTemplate) {
                    // Template still exists: restore selection and refresh object options
                    templateCombobox.setValue(matchedTemplate.id, matchedTemplate.name, matchedTemplate);
                    handleTemplateSelection(matchedTemplate.id, { raw: matchedTemplate });
                } else {
                    // Template was deleted in Wialon: clear template and object selection
                    templateCombobox.clear();
                    handleTemplateClear();
                }
            } else {
                handleTemplateClear();
            }

            // 4. Display informative success alert with breakdown
            const t = data.templates || { created: 0, updated: 0, deleted: 0 };
            const v = data.vehicles  || { created: 0, updated: 0, deleted: 0 };
            const g = data.groups    || { created: 0, updated: 0, deleted: 0 };

            if (isManual) {
                const summaryText = `Wialon synchronized successfully! Templates: (+${t.created}, ~${t.updated}, -${t.deleted}) | Vehicles: (+${v.created}, ~${v.updated}, -${v.deleted}) | Groups: (+${g.created}, ~${g.updated}, -${g.deleted})`;
                showAlert(summaryText, 'success');
            } else {
                console.log('Wialon auto-sync on load complete:', { templates: t, vehicles: v, groups: g });
            }

        } catch (err) {
            console.error('Wialon sync error:', err);
            if (isManual) {
                showAlert('Network error during synchronization. Please check your connection and try again.', 'danger');
            }
        } finally {
            if (syncBtn) {
                syncBtn.disabled = false;
                syncBtn.innerHTML = originalHtml || '<i class="bi bi-arrow-clockwise"></i> Sync Wialon';
            }
        }
    }

    if (syncBtn) {
        syncBtn.addEventListener('click', () => syncWialon(true));
    }

    // Trigger Wialon synchronization automatically on page load / reload
    syncWialon(false);

    // ==========================================
    // 5. QUICK DATE RANGE PRESETS
    // ==========================================
    document.querySelectorAll('.quick-range').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.quick-range').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const now = new Date();
            let from, to;

            switch (btn.dataset.range) {
                case 'today':
                    from = startOfDay(now);
                    to = now;
                    break;
                case 'yesterday':
                    const yesterday = new Date(now);
                    yesterday.setDate(now.getDate() - 1);
                    from = startOfDay(yesterday);
                    to = endOfDay(yesterday);
                    break;
                case 'week':
                    from = new Date(now);
                    from.setDate(now.getDate() - 7);
                    to = now;
                    break;
                case 'month':
                    from = new Date(now);
                    from.setMonth(now.getMonth() - 1);
                    to = now;
                    break;
            }

            if (fromInput) fromInput.value = toDatetimeLocal(from);
            if (toInput) toInput.value = toDatetimeLocal(to);
        });
    });

    // ==========================================
    // 6. REPORT GENERATION & EXECUTION
    // ==========================================
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const payload = getFormPayload();

        // Strict Validation before calling execution
        if (!payload.template_id) {
            showAlert('Please select a report template.', 'warning');
            return;
        }

        if (!payload.report_object_id && !payload.wialon_unit_id) {
            const objectTypeName = state.selectedTemplate?.objectType === 'unit_group' ? 'vehicle group' : 'vehicle';
            showAlert(`Please select a ${objectTypeName}.`, 'warning');
            return;
        }

        if (!payload.from || !payload.to) {
            showAlert('Please select a valid date/time range.', 'warning');
            return;
        }

        if (new Date(payload.from) > new Date(payload.to)) {
            showAlert('The "From" date must be earlier than or equal to the "To" date.', 'warning');
            return;
        }

        showLoading();
        disableGenerateBtn();

        try {
            const generateUrl = form.dataset.generateUrl || '/my-account/reports-analytics/generate';
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 120000); // 2 minute timeout

            const response = await fetch(generateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                },
                body: JSON.stringify(payload),
                signal: controller.signal,
            });

            clearTimeout(timeoutId);

            const data = await response.json();

            if (!response.ok || !data.success) {
                const errorMsg = data.message || getErrorMessageByStatus(response.status);
                showError(errorMsg, data.error_type);
                return;
            }

            if (data.warning) {
                showAlert(data.warning, 'warning');
            }

            if (data.message && data.result?.tables?.length === 0) {
                showAlert(data.message, 'info');
            }

            renderResult(data.result);

        } catch (err) {
            console.error('Report generation error:', err);

            if (err.name === 'AbortError') {
                showError('Request timed out. Please try a shorter time period or check your internet connection.', 'timeout');
            } else {
                showError('Network error. Please check your connection and try again.', 'network');
            }
        } finally {
            enableGenerateBtn();
        }
    });

    // ==========================================
    // 7. CLEAR BUTTON
    // ==========================================
    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            form.reset();
            document.querySelectorAll('.quick-range').forEach(b => b.classList.remove('active'));
            templateCombobox.clear();
            objectCombobox.clear();
            handleTemplateClear();
            showEmpty();

            if (typeof WialonReportMap !== 'undefined') {
                WialonReportMap.clearMarkers();
                const mapObj = WialonReportMap.getMap ? WialonReportMap.getMap() : null;
                if (mapObj) {
                    if (mapObj.getLayer('report-route-line')) mapObj.removeLayer('report-route-line');
                    if (mapObj.getSource('report-route')) mapObj.removeSource('report-route');
                }
            }
        });
    }

    // ==========================================
    // 8. EXPORT HANDLING
    // ==========================================
    document.querySelectorAll('.export-link').forEach((link) => {
        link.addEventListener('click', async (e) => {
            e.preventDefault();

            const payload = { ...getFormPayload(), format: link.dataset.format };

            if (!payload.template_id || (!payload.report_object_id && !payload.wialon_unit_id) || !payload.from || !payload.to) {
                showAlert('Please select template, object, and date range before exporting.', 'warning');
                return;
            }

            const originalText = link.textContent;
            link.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Exporting...';
            link.classList.add('disabled');

            try {
                const exportUrl = form.dataset.exportUrl || '/my-account/reports-analytics/export';
                const response = await fetch(exportUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(payload),
                });

                if (!response.ok) {
                    const data = await response.json().catch(() => ({}));
                    showAlert(data.message || 'Export failed. Please try again.', 'danger');
                    return;
                }

                const blob = await response.blob();
                const disposition = response.headers.get('Content-Disposition') || '';
                const match = disposition.match(/filename="(.+)"/);
                const fileName = match ? match[1] : `report.${link.dataset.format}`;

                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = fileName;
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);

                showAlert('Report exported successfully!', 'success');
            } catch (err) {
                console.error('Export error:', err);
                showAlert('Export failed. Please check your connection and try again.', 'danger');
            } finally {
                link.textContent = originalText;
                link.classList.remove('disabled');
            }
        });
    });

    // ==========================================
    // 9. RESULT RENDERING (Tables, Charts, Map)
    // ==========================================
    function renderResult(result) {
        hideLoading();

        if (result.summary) {
            displaySummary(result.summary);
        }

        // Tables
        tablesContainer.innerHTML = '';
        const tables = result.tables || [];

        if (tables.length === 0) {
            tablesContainer.innerHTML = '<div class="alert alert-info">No data found for the selected period.</div>';
            tablesContainer.classList.remove('d-none');
        } else {
            tables.forEach((table) => {
                tablesContainer.appendChild(buildTableElement(table));
            });
            tablesContainer.classList.remove('d-none');
        }

        // Chart
        if (result.charts && result.charts.length > 0) {
            renderChart(result.charts[0]);
            chartContainer.classList.remove('d-none');
        } else {
            chartContainer.classList.add('d-none');
        }

        // Map Route & Points
        const points = extractMapPoints(result.map);
        if (typeof WialonReportMap !== 'undefined') {
            if (points && points.length > 0) {
                if (typeof WialonReportMap.setRoute === 'function') {
                    WialonReportMap.setRoute(points);
                }

                const markers = [];
                markers.push({
                    lng: points[0].lng,
                    lat: points[0].lat,
                    label: 'Start: ' + (points[0].label || '')
                });
                markers.push({
                    lng: points[points.length - 1].lng,
                    lat: points[points.length - 1].lat,
                    label: 'End: ' + (points[points.length - 1].label || '')
                });
                if (typeof WialonReportMap.setPoints === 'function') {
                    WialonReportMap.setPoints(markers);
                }
            } else {
                if (typeof WialonReportMap.clearMarkers === 'function') {
                    WialonReportMap.clearMarkers();
                }
                const mapObj = typeof WialonReportMap.getMap === 'function' ? WialonReportMap.getMap() : null;
                if (mapObj) {
                    if (mapObj.getLayer('report-route-line')) mapObj.removeLayer('report-route-line');
                    if (mapObj.getSource('report-route')) mapObj.removeSource('report-route');
                }
            }
        }
    }

    function displaySummary(summary) {
        const summaryHtml = `
            <div class="alert alert-light border mb-3">
                <div class="row small">
                    <div class="col-md-6"><strong>Template:</strong> ${escapeHtml(summary.template_name)}</div>
                    <div class="col-md-6"><strong>Period:</strong> ${escapeHtml(summary.period_from)} to ${escapeHtml(summary.period_to)}</div>
                </div>
            </div>
        `;
        const existing = document.querySelector('.report-summary');
        if (existing) existing.remove();

        tablesContainer.insertAdjacentHTML('beforebegin', summaryHtml);
        tablesContainer.previousElementSibling.classList.add('report-summary');
    }

    function buildTableElement(table) {
        const wrapper = document.createElement('div');
        wrapper.className = 'mb-4 table-wrapper';

        const allRows = table.rows?.rows || table.rows || [];
        wrapper.dataset.allRows = JSON.stringify(allRows);
        wrapper.dataset.currentPage = '1';
        wrapper.dataset.rowsPerPage = '10';

        const titleRow = document.createElement('div');
        titleRow.className = 'd-flex justify-content-between align-items-center mb-3';

        const titleSection = document.createElement('div');
        titleSection.className = 'd-flex align-items-center gap-2';

        const title = document.createElement('h6');
        title.className = 'mb-0 fw-bold';
        title.textContent = table.label || 'Report Data';
        titleSection.appendChild(title);

        if (table.rowCount) {
            const badge = document.createElement('span');
            badge.className = 'badge bg-primary rounded-pill';
            badge.innerHTML = `<i class="bi bi-list-ul me-1"></i>${table.rowCount} row${table.rowCount !== 1 ? 's' : ''}`;
            titleSection.appendChild(badge);
        }

        titleRow.appendChild(titleSection);

        if (allRows.length > 5) {
            const searchBox = document.createElement('input');
            searchBox.type = 'text';
            searchBox.className = 'form-control form-control-sm table-search-box';
            searchBox.placeholder = 'Search in table...';
            searchBox.style.width = '220px';
            searchBox.addEventListener('input', (e) => {
                wrapper.dataset.searchTerm = e.target.value;
                wrapper.dataset.currentPage = '1';
                renderTablePage(wrapper, table.headers);
            });
            titleRow.appendChild(searchBox);
        }

        wrapper.appendChild(titleRow);

        const tableContainer = document.createElement('div');
        tableContainer.className = 'table-responsive table-container';

        const el = document.createElement('table');
        el.className = 'table table-sm table-striped table-hover table-bordered align-middle mb-0';

        if (table.headers && table.headers.length) {
            const thead = document.createElement('thead');
            thead.className = 'table-dark';
            const tr = document.createElement('tr');
            table.headers.forEach((h, index) => {
                const th = document.createElement('th');
                th.innerHTML = `
                    <div class="d-flex align-items-center justify-content-between">
                        <span>${h || ''}</span>
                        <i class="bi bi-arrow-down-up text-white-50 small ms-1" style="cursor: pointer;" onclick="sortTableWrapper(this, ${index})"></i>
                    </div>
                `;
                th.style.whiteSpace = 'nowrap';
                th.style.position = 'sticky';
                th.style.top = '0';
                th.style.zIndex = '10';
                tr.appendChild(th);
            });
            thead.appendChild(tr);
            el.appendChild(thead);
        }

        const tbody = document.createElement('tbody');
        tbody.className = 'table-body';
        el.appendChild(tbody);

        tableContainer.appendChild(el);
        wrapper.appendChild(tableContainer);

        // Pagination controls
        if (allRows.length > 10) {
            const paginationDiv = document.createElement('div');
            paginationDiv.className = 'table-pagination';
            paginationDiv.innerHTML = `
                <div class="pagination-info">
                    <span class="text-muted small">Showing <strong class="page-range-start">1</strong>-<strong class="page-range-end">10</strong> of <strong class="total-rows">${allRows.length}</strong></span>
                </div>
                <div class="pagination-controls">
                    <select class="rows-per-page">
                        <option value="10" selected>10 rows</option>
                        <option value="25">25 rows</option>
                        <option value="50">50 rows</option>
                        <option value="100">100 rows</option>
                        <option value="all">All rows</option>
                    </select>
                    <button class="btn-first" title="First page"><i class="bi bi-chevron-bar-left"></i></button>
                    <button class="btn-prev" title="Previous page"><i class="bi bi-chevron-left"></i></button>
                    <span class="page-info">Page <strong class="current-page">1</strong> of <strong class="total-pages">1</strong></span>
                    <button class="btn-next" title="Next page"><i class="bi bi-chevron-right"></i></button>
                    <button class="btn-last" title="Last page"><i class="bi bi-chevron-bar-right"></i></button>
                </div>
            `;

            const rowsPerPageSelect = paginationDiv.querySelector('.rows-per-page');
            rowsPerPageSelect.addEventListener('change', (e) => {
                wrapper.dataset.rowsPerPage = e.target.value;
                wrapper.dataset.currentPage = '1';
                renderTablePage(wrapper, table.headers);
            });

            paginationDiv.querySelector('.btn-first').addEventListener('click', () => {
                wrapper.dataset.currentPage = '1';
                renderTablePage(wrapper, table.headers);
            });

            paginationDiv.querySelector('.btn-prev').addEventListener('click', () => {
                const current = parseInt(wrapper.dataset.currentPage);
                if (current > 1) {
                    wrapper.dataset.currentPage = (current - 1).toString();
                    renderTablePage(wrapper, table.headers);
                }
            });

            paginationDiv.querySelector('.btn-next').addEventListener('click', () => {
                const filteredRows = getFilteredRows(wrapper);
                const rowsPerPage = wrapper.dataset.rowsPerPage === 'all' ? filteredRows.length : parseInt(wrapper.dataset.rowsPerPage);
                const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
                const current = parseInt(wrapper.dataset.currentPage);
                if (current < totalPages) {
                    wrapper.dataset.currentPage = (current + 1).toString();
                    renderTablePage(wrapper, table.headers);
                }
            });

            paginationDiv.querySelector('.btn-last').addEventListener('click', () => {
                const filteredRows = getFilteredRows(wrapper);
                const rowsPerPage = wrapper.dataset.rowsPerPage === 'all' ? filteredRows.length : parseInt(wrapper.dataset.rowsPerPage);
                const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
                wrapper.dataset.currentPage = totalPages.toString();
                renderTablePage(wrapper, table.headers);
            });

            wrapper.appendChild(paginationDiv);
        }

        renderTablePage(wrapper, table.headers);

        return wrapper;
    }

    function getFilteredRows(wrapper) {
        const allRows = JSON.parse(wrapper.dataset.allRows || '[]');
        const searchTerm = (wrapper.dataset.searchTerm || '').toLowerCase();

        if (!searchTerm) return allRows;

        return allRows.filter(row => {
            const cells = row.c || row;
            return (Array.isArray(cells) ? cells : []).some(cell => {
                const value = typeof cell === 'object' ? (cell?.t ?? '') : cell;
                return String(value).toLowerCase().includes(searchTerm);
            });
        });
    }

    function renderTablePage(wrapper, headers) {
        const tbody = wrapper.querySelector('.table-body');
        const filteredRows = getFilteredRows(wrapper);
        const currentPage = parseInt(wrapper.dataset.currentPage);
        const rowsPerPage = wrapper.dataset.rowsPerPage === 'all' ? filteredRows.length : parseInt(wrapper.dataset.rowsPerPage);

        const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
        const startIndex = (currentPage - 1) * rowsPerPage;
        const endIndex = Math.min(startIndex + rowsPerPage, filteredRows.length);
        const pageRows = filteredRows.slice(startIndex, endIndex);

        tbody.innerHTML = '';

        if (pageRows.length === 0) {
            const tr = document.createElement('tr');
            const td = document.createElement('td');
            td.colSpan = headers?.length || 1;
            td.className = 'text-center text-muted py-4';
            td.innerHTML = `<div>${wrapper.dataset.searchTerm ? 'No matching results found' : 'No data available'}</div>`;
            tr.appendChild(td);
            tbody.appendChild(tr);
        } else {
            pageRows.forEach((row) => {
                const tr = document.createElement('tr');
                tr.className = 'table-row';
                const cells = row.c || row;
                (Array.isArray(cells) ? cells : []).forEach((cell, cellIndex) => {
                    const td = document.createElement('td');
                    const cellValue = typeof cell === 'object' ? (cell?.t ?? '') : cell;
                    const formattedValue = formatCellValue(cellValue);

                    if (typeof cellValue === 'number') {
                        td.className = 'text-end fw-semibold';
                    }
                    if (cellIndex === 0 && cellValue) {
                        td.className = 'fw-bold text-primary';
                    }

                    td.textContent = formattedValue;
                    tr.appendChild(td);
                });
                tbody.appendChild(tr);
            });
        }

        const paginationDiv = wrapper.querySelector('.table-pagination');
        if (paginationDiv) {
            paginationDiv.querySelector('.page-range-start').textContent = filteredRows.length === 0 ? '0' : (startIndex + 1);
            paginationDiv.querySelector('.page-range-end').textContent = endIndex;
            paginationDiv.querySelector('.total-rows').textContent = filteredRows.length;
            paginationDiv.querySelector('.current-page').textContent = currentPage;
            paginationDiv.querySelector('.total-pages').textContent = totalPages || 1;

            paginationDiv.querySelector('.btn-first').disabled = currentPage === 1;
            paginationDiv.querySelector('.btn-prev').disabled = currentPage === 1;
            paginationDiv.querySelector('.btn-next').disabled = currentPage >= totalPages;
            paginationDiv.querySelector('.btn-last').disabled = currentPage >= totalPages;
        }
    }

    function formatCellValue(value) {
        if (value === null || value === undefined || value === '') {
            return '-';
        }
        if (typeof value === 'number' && value > 999) {
            return value.toLocaleString();
        }
        return String(value);
    }

    function renderChart(chart) {
        const series = chart.data?.series ?? [];

        if (series.length === 0) {
            chartContainer.classList.add('d-none');
            return;
        }

        const labels = series.map((point) => {
            const date = new Date(point.t * 1000);
            return date.toLocaleString(undefined, {
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        });
        const values = series.map((point) => point.y ?? point.v);

        if (chartInstance) {
            chartInstance.destroy();
        }

        if (typeof Chart !== 'undefined' && chartCanvas) {
            chartInstance = new Chart(chartCanvas, {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: chart.label,
                        data: values,
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        tension: 0.2,
                        fill: true,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: true, position: 'top' },
                        tooltip: { mode: 'index', intersect: false },
                    },
                    scales: {
                        y: { beginAtZero: false },
                    },
                },
            });
        }
    }

    function extractMapPoints(mapData) {
        if (Array.isArray(mapData)) {
            return mapData.map((p) => ({
                lng: p.lng ?? p.x,
                lat: p.lat ?? p.y,
                label: p.t ?? p.name ?? ''
            }));
        }
        const rawPoints = mapData?.points || mapData?.markers || [];
        return rawPoints.map((p) => ({ lng: p.x ?? p.lng, lat: p.y ?? p.lat, label: p.t ?? p.name }));
    }

    function getFormPayload() {
        const templateId = templateCombobox.getValue();
        const objectId = objectCombobox.getValue() || document.getElementById('report_object_id')?.value || document.getElementById('wialon_unit_id')?.value;

        return {
            template_id: templateId,
            report_object_id: objectId ? parseInt(objectId) : null,
            wialon_unit_id: objectId ? parseInt(objectId) : null,
            from: fromInput ? fromInput.value : '',
            to: toInput ? toInput.value : '',
        };
    }

    function showLoading() {
        if (loadingEl) loadingEl.classList.remove('d-none');
        if (emptyEl) emptyEl.classList.add('d-none');
        if (tablesContainer) tablesContainer.classList.add('d-none');
        if (chartContainer) chartContainer.classList.add('d-none');

        const existing = document.querySelector('.report-summary');
        if (existing) existing.remove();
    }

    function hideLoading() {
        if (loadingEl) loadingEl.classList.add('d-none');
    }

    function showEmpty() {
        if (emptyEl) {
            emptyEl.classList.remove('d-none');
            emptyEl.className = 'text-center py-5 text-muted';
            emptyEl.innerHTML = '<i class="bi bi-file-earmark-text fs-1 mb-2"></i><p>Select a template, an object, and a date range, then click Execute.</p>';
        }
        if (tablesContainer) tablesContainer.classList.add('d-none');
        if (chartContainer) chartContainer.classList.add('d-none');
    }

    function showError(message, errorType = 'general') {
        hideLoading();
        if (emptyEl) {
            emptyEl.classList.remove('d-none');
            emptyEl.className = 'alert alert-danger';

            let icon = 'bi-exclamation-triangle';
            let title = 'Error';

            if (errorType === 'timeout') {
                icon = 'bi-clock';
                title = 'Request Timed Out';
            } else if (errorType === 'network') {
                icon = 'bi-wifi-off';
                title = 'Network Error';
            }

            emptyEl.innerHTML = `
                <i class="${icon} fs-1 mb-2 d-block"></i>
                <h5>${title}</h5>
                <p class="mb-0">${escapeHtml(message)}</p>
            `;
        }
        if (tablesContainer) tablesContainer.classList.add('d-none');
        if (chartContainer) chartContainer.classList.add('d-none');
    }

    function showAlert(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.role = 'alert';
        alertDiv.innerHTML = `
            ${escapeHtml(message)}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        const cardBody = form ? form.closest('.card-body') : null;
        if (cardBody) {
            cardBody.insertBefore(alertDiv, cardBody.firstChild);
        }

        setTimeout(() => {
            if (alertDiv.parentElement) {
                alertDiv.classList.remove('show');
                setTimeout(() => alertDiv.remove(), 150);
            }
        }, 6000);
    }

    function getErrorMessageByStatus(status) {
        const messages = {
            400: 'Invalid request. Please check your inputs.',
            404: 'Resource not found. Please refresh the page.',
            422: 'Validation error. Please check your form inputs.',
            500: 'Server error. Please try again later.',
            502: 'Server temporarily unavailable. Please try again.',
            503: 'Service unavailable. Please try again later.',
            504: 'Request timed out. Please try a shorter time period.',
        };
        return messages[status] || 'An unexpected error occurred. Please try again.';
    }

    function disableGenerateBtn() {
        if (generateBtn) {
            generateBtn.disabled = true;
            generateBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Generating...';
        }
    }

    function enableGenerateBtn() {
        if (generateBtn) {
            generateBtn.disabled = false;
            generateBtn.textContent = 'Execute';
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function startOfDay(d) {
        const r = new Date(d);
        r.setHours(0, 0, 0, 0);
        return r;
    }

    function endOfDay(d) {
        const r = new Date(d);
        r.setHours(23, 59, 59, 999);
        return r;
    }

    function toDatetimeLocal(d) {
        const pad = (n) => String(n).padStart(2, '0');
        return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
    }

    window.sortTableWrapper = function (icon, columnIndex) {
        const wrapper = icon.closest('.table-wrapper');
        const allRows = JSON.parse(wrapper.dataset.allRows || '[]');
        const isAscending = icon.classList.contains('bi-arrow-down-up');

        allRows.sort((a, b) => {
            const aCells = a.c || a;
            const bCells = b.c || b;
            const aCell = (aCells[columnIndex] || '');
            const bCell = (bCells[columnIndex] || '');

            const aValue = typeof aCell === 'object' ? (aCell?.t ?? '') : aCell;
            const bValue = typeof bCell === 'object' ? (bCell?.t ?? '') : bCell;

            const aNum = parseFloat(String(aValue).replace(/,/g, ''));
            const bNum = parseFloat(String(bValue).replace(/,/g, ''));

            if (!isNaN(aNum) && !isNaN(bNum)) {
                return isAscending ? aNum - bNum : bNum - aNum;
            }

            const aStr = String(aValue);
            const bStr = String(bValue);
            return isAscending ? aStr.localeCompare(bStr) : bStr.localeCompare(aStr);
        });

        wrapper.dataset.allRows = JSON.stringify(allRows);

        const allIcons = wrapper.querySelectorAll('thead i.bi');
        allIcons.forEach(i => {
            i.className = 'bi bi-arrow-down-up text-white-50 small ms-1';
        });
        icon.className = `bi ${isAscending ? 'bi-sort-down' : 'bi-sort-up'} text-white small ms-1`;

        const headers = Array.from(wrapper.querySelectorAll('thead th')).map(th => th.textContent.trim());
        renderTablePage(wrapper, headers);
    };
});
