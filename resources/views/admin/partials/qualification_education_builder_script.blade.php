<script>
document.addEventListener('DOMContentLoaded', function () {
    const hiddenRequirement = document.getElementById('qualification_education');
    const hiddenConfig = document.getElementById('qualification_education_config');
    const educationSelect = document.getElementById('minimum_education_code');
    const previewWrap = document.getElementById('education_preview_wrap');
    const previewText = document.getElementById('education_preview_text');

    const detailWrap = document.getElementById('education_detail_wrap');
    const detailGroupLabel = document.getElementById('education_detail_group_label');
    const detailAnyInput = document.getElementById('education_detail_any');
    const detailAnyLabel = document.getElementById('education_detail_any_label');
    const detailSpecificInput = document.getElementById('education_detail_specific');
    const detailSpecificLabel = document.getElementById('education_detail_specific_label');

    const specificWrap = document.getElementById('education_specific_picker_wrap');
    const specificLabel = document.getElementById('education_specific_picker_label');
    const specificRows = document.getElementById('education_specific_rows');
    const addSpecificButton = document.getElementById('education_add_specific_btn');
    const specificRowTemplate = document.getElementById('education_specific_row_template');

    if (
        !hiddenRequirement ||
        !hiddenConfig ||
        !educationSelect ||
        !previewWrap ||
        !previewText ||
        !detailWrap ||
        !detailGroupLabel ||
        !detailAnyInput ||
        !detailAnyLabel ||
        !detailSpecificInput ||
        !detailSpecificLabel ||
        !specificWrap ||
        !specificLabel ||
        !specificRows ||
        !addSpecificButton ||
        !specificRowTemplate
    ) {
        return;
    }

    const normalize = (value) => String(value || '').trim().toLowerCase();
    const normalizeSpaces = (value) => String(value || '').replace(/\s+/g, ' ').trim();
    const hasValue = (value) => String(value || '').trim() !== '';
    const escapeAttr = (value) => String(value || '').replace(/"/g, '&quot;');
    const escapeHtml = (value) => String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');

    function expandProgramAbbreviation(label) {
        const value = normalizeSpaces(label);
        if (!value) {
            return '';
        }

        const bsMatch = value.match(/^b\.?\s*s\.?\s+(.+)$/i);
        if (bsMatch) {
            return `Bachelor of Science in ${normalizeSpaces(bsMatch[1])}`;
        }

        const baMatch = value.match(/^b\.?\s*a\.?\s+(.+)$/i);
        if (baMatch) {
            return `Bachelor of Arts in ${normalizeSpaces(baMatch[1])}`;
        }

        const msMatch = value.match(/^m\.?\s*s\.?\s+(.+)$/i);
        if (msMatch) {
            return `Master of Science in ${normalizeSpaces(msMatch[1])}`;
        }

        const maMatch = value.match(/^m\.?\s*a\.?\s+(.+)$/i);
        if (maMatch) {
            return `Master of Arts in ${normalizeSpaces(maMatch[1])}`;
        }

        const phdMatch = value.match(/^p\.?\s*h\.?\s*d\.?\s+(.+)$/i);
        if (phdMatch) {
            return `Doctor of Philosophy in ${normalizeSpaces(phdMatch[1])}`;
        }

        return value;
    }

    function requirementProgramLabel(label) {
        let value = normalizeSpaces(expandProgramAbbreviation(label));
        if (!value) {
            return '';
        }

        const leadingPatterns = [
            /^completion of 2 years of studies in college in\s+/i,
            /^bachelor(?:'s)? degree in\s+/i,
            /^masteral degree in\s+/i,
            /^doctorate degree in\s+/i,
            /^bachelor of science in\s+/i,
            /^bachelor of arts in\s+/i,
            /^bachelor of\s+/i,
            /^master of science in\s+/i,
            /^master of arts in\s+/i,
            /^master in\s+/i,
            /^master of\s+/i,
            /^doctor of philosophy in\s+/i,
            /^doctor of\s+/i,
        ];

        for (const pattern of leadingPatterns) {
            if (pattern.test(value)) {
                value = normalizeSpaces(value.replace(pattern, ''));
                break;
            }
        }

        return value;
    }

    function bachelorSpecificProgramLabel(label) {
        let value = normalizeSpaces(expandProgramAbbreviation(label));
        if (!value) {
            return '';
        }

        value = normalizeSpaces(value.replace(/^bachelor(?:'s)? degree\s+in\s+/i, ''));
        return value;
    }

    function comparableProgramLabel(label) {
        const comparable = requirementProgramLabel(label);
        if (comparable) {
            return normalize(comparable);
        }

        return normalize(expandProgramAbbreviation(label));
    }

    const collegeCoursesListUrl = @json(route('admin.courses.list'));

    const defaultCollegeCourseOptions = [
        { code: 'LLB_JD', label: 'Bachelor of Laws / Juris Doctor' },
        { code: 'BS_ACCOUNTANCY', label: 'BS Accountancy' },
        { code: 'BS_INFORMATION_TECHNOLOGY', label: 'BS Information Technology' },
        { code: 'BS_COMPUTER_SCIENCE', label: 'BS Computer Science' },
        { code: 'BS_INFORMATION_SYSTEMS', label: 'BS Information Systems' },
        { code: 'B_PUBLIC_ADMIN', label: 'Bachelor of Public Administration' },
        { code: 'BS_PSYCHOLOGY', label: 'BS Psychology' },
    ];

    const defaultMasteralOptions = [
        { code: 'MASTER_PUBLIC_ADMIN', label: 'Master of Public Administration' },
        { code: 'MASTER_IT', label: 'Master in Information Technology' },
        { code: 'MBA', label: 'Master in Business Administration' },
        { code: 'MASTER_EDUCATION', label: 'Master of Arts in Education' },
        { code: 'MASTER_PSYCHOLOGY', label: 'Master of Arts in Psychology' },
    ];

    const defaultDoctorateOptions = [
        { code: 'PHD_PUBLIC_ADMIN', label: 'Doctor of Philosophy in Public Administration' },
        { code: 'PHD_IT', label: 'Doctor of Philosophy in Information Technology' },
        { code: 'EDD', label: 'Doctor of Education' },
        { code: 'PHD_PSYCHOLOGY', label: 'Doctor of Philosophy in Psychology' },
        { code: 'SJD', label: 'Doctor of Juridical Science' },
    ];

    function normalizedProgramOptions(options) {
        return (Array.isArray(options) ? options : []).map((item) => ({
            ...item,
            label: expandProgramAbbreviation(item?.label || ''),
        }));
    }

    let collegeCourseOptions = normalizedProgramOptions(defaultCollegeCourseOptions);
    let masteralOptions = normalizedProgramOptions(defaultMasteralOptions);
    let doctorateOptions = normalizedProgramOptions(defaultDoctorateOptions);
    let rowCounter = 0;

    const educationMeta = {
        HIGH_SCHOOL_GRAD: {
            label: 'Junior High School Graduate',
            requirementTextAny: 'Junior High School Graduate',
            previewAny: 'Applicants must have at least a Junior High School Graduate.',
            detail: null,
        },
        SENIOR_HIGH_SCHOOL_GRAD: {
            label: 'Senior High School Graduate',
            requirementTextAny: 'Senior High School Graduate',
            previewAny: 'Applicants must have at least a Senior High School Graduate.',
            detail: null,
        },
        COLLEGE_2Y: {
            label: 'Completion of 2 Years in College',
            requirementTextAny: 'Completion of 2 years of studies in college',
            previewAny: 'Applicants must have at least a Completion of 2 Years in College.',
            detail: {
                groupLabel: 'Course requirement',
                anyLabel: 'Any college course',
                specificLabel: 'Specific college course',
                specificFieldLabel: 'Required course',
                optionsProvider: () => collegeCourseOptions,
                requirementTextSpecific: (program) => `Completion of 2 years of studies in college in ${requirementProgramLabel(program)}`,
                requirementTextSpecificMultiple: (programs) => `Completion of 2 years of studies in college in ${programs.map((program) => requirementProgramLabel(program)).join(', ')}`,
                previewSpecific: (program) => `Applicants must have at least a Completion of 2 Years in College in ${requirementProgramLabel(program)}.`,
                previewSpecificMultiple: (programs) => `Applicants may hold any of the following courses: ${programs.map((program) => requirementProgramLabel(program)).join(', ')}.`,
            },
        },
        BACHELOR: {
            label: 'Bachelors Degree',
            requirementTextAny: "Bachelor's Degree (any field)",
            previewAny: 'Applicants must have at least a Bachelors Degree.',
            detail: {
                groupLabel: 'Degree requirement',
                anyLabel: 'Any bachelors degree',
                specificLabel: 'Specific bachelors degree',
                specificFieldLabel: 'Degree',
                optionsProvider: () => collegeCourseOptions,
                requirementTextSpecific: (program) => `${bachelorSpecificProgramLabel(program)}`,
                requirementTextSpecificMultiple: (programs) => `${programs.map((program) => bachelorSpecificProgramLabel(program)).join(', ')}`,
                previewSpecific: (program) => `Applicants must hold the degree ${bachelorSpecificProgramLabel(program)}.`,
                previewSpecificMultiple: (programs) => `Applicants may hold any of the following degrees: ${programs.map((program) => bachelorSpecificProgramLabel(program)).join(', ')}.`,
            },
        },
        MASTERAL: {
            label: 'Masteral Degree',
            requirementTextAny: 'Masteral Degree',
            previewAny: 'Applicants must have at least a Masteral Degree.',
            detail: {
                groupLabel: 'Degree requirement',
                anyLabel: 'Any masteral degree',
                specificLabel: 'Specific masteral degree',
                specificFieldLabel: 'Degree',
                optionsProvider: () => masteralOptions,
                requirementTextSpecific: (program) => `Masteral Degree in ${requirementProgramLabel(program)}`,
                requirementTextSpecificMultiple: (programs) => `Masteral Degree in ${programs.map((program) => requirementProgramLabel(program)).join(', ')}`,
                previewSpecific: (program) => `Applicants must hold a master's degree in ${requirementProgramLabel(program)}.`,
                previewSpecificMultiple: (programs) => `Applicants may hold any master's degree in the following fields: ${programs.map((program) => requirementProgramLabel(program)).join(', ')}.`,
            },
        },
        DOCTORATE: {
            label: 'Doctorate Degree',
            requirementTextAny: 'Doctorate Degree',
            previewAny: 'Applicants must have at least a Doctorate Degree.',
            detail: {
                groupLabel: 'Degree requirement',
                anyLabel: 'Any doctorate degree',
                specificLabel: 'Specific doctorate degree',
                specificFieldLabel: 'Degree',
                optionsProvider: () => doctorateOptions,
                requirementTextSpecific: (program) => `Doctorate Degree in ${requirementProgramLabel(program)}`,
                requirementTextSpecificMultiple: (programs) => `Doctorate Degree in ${programs.map((program) => requirementProgramLabel(program)).join(', ')}`,
                previewSpecific: (program) => `Applicants must hold a doctorate degree in ${requirementProgramLabel(program)}.`,
                previewSpecificMultiple: (programs) => `Applicants may hold a doctorate degree in the following fields: ${programs.map((program) => requirementProgramLabel(program)).join(', ')}.`,
            },
        },
    };

    function currentEducationCode() {
        return String(educationSelect.value || '').trim();
    }

    function currentMeta() {
        return educationMeta[currentEducationCode()] || null;
    }

    function selectedDetailMode() {
        if (detailAnyInput.checked) return 'ANY';
        if (detailSpecificInput.checked) return 'SPECIFIC';
        return '';
    }

    function setDetailMode(mode) {
        detailAnyInput.checked = mode === 'ANY';
        detailSpecificInput.checked = mode === 'SPECIFIC';
    }

    function detailOptions(detail) {
        if (!detail) return [];
        if (typeof detail.optionsProvider === 'function') {
            const items = detail.optionsProvider();
            return Array.isArray(items) ? items : [];
        }
        return Array.isArray(detail.options) ? detail.options : [];
    }

    function currentDetailOptions() {
        const meta = currentMeta();
        if (!meta || !meta.detail) return [];
        return detailOptions(meta.detail);
    }

    function optionByCode(code, options) {
        const n = normalize(code);
        if (!n) return null;
        return options.find((item) => normalize(item.code) === n) || null;
    }

    function optionByLabel(label, options) {
        const n = normalize(label);
        if (!n) return null;

        const comparable = comparableProgramLabel(label);
        return options.find((item) => {
            const itemLabel = normalize(item.label);
            const itemComparable = comparableProgramLabel(item.label);

            return itemLabel === n
                || itemComparable === n
                || itemLabel === comparable
                || itemComparable === comparable;
        }) || null;
    }

    function optionCodeFromLabel(label, options) {
        const found = optionByLabel(label, options);
        return found ? found.code : '';
    }

    function getSpecificRows() {
        return Array.from(specificRows.querySelectorAll('.education-specific-row'));
    }

    function rowInput(row) {
        return row?.querySelector('[data-role="specific-input"]') || null;
    }

    function rowMenu(row) {
        return row?.querySelector('[data-role="specific-menu"]') || null;
    }

    function rowOptionsWrap(row) {
        return row?.querySelector('[data-role="specific-options"]') || null;
    }

    function rowRemoveButton(row) {
        return row?.querySelector('[data-role="remove-specific-row"]') || null;
    }

    function closeSpecificMenu(row) {
        const input = rowInput(row);
        const menu = rowMenu(row);
        if (!input || !menu) return;
        menu.classList.add('hidden');
        input.setAttribute('aria-expanded', 'false');
    }

    function closeAllSpecificMenus() {
        getSpecificRows().forEach((row) => closeSpecificMenu(row));
    }

    function filterSpecificOptions(query, options) {
        const q = normalize(query);
        if (!q) return options;
        return options.filter((item) => normalize(item.label).includes(q));
    }

    function renderSpecificOptionsList(row) {
        const input = rowInput(row);
        const optionsWrap = rowOptionsWrap(row);
        if (!input || !optionsWrap) return;

        const options = currentDetailOptions();
        const filtered = filterSpecificOptions(input.value, options);
        const selectedCode = String(input.dataset.selectedCode || '').trim();

        if (filtered.length === 0) {
            optionsWrap.innerHTML = '<div class="px-3 py-2 text-sm text-slate-500">No matches found.</div>';
            return;
        }

        optionsWrap.innerHTML = filtered
            .map((item) => {
                const selectedClass = selectedCode === item.code ? ' bg-slate-100 font-medium' : '';
                return `<button type="button" class="block w-full px-3 py-2 text-left text-sm text-slate-700 transition hover:bg-slate-100 focus:bg-slate-100${selectedClass}" data-code="${escapeAttr(item.code)}" data-label="${escapeAttr(item.label)}">${escapeHtml(item.label)}</button>`;
            })
            .join('');
    }

    function openSpecificMenu(row) {
        const input = rowInput(row);
        const menu = rowMenu(row);
        if (!input || !menu) return;

        if (specificWrap.classList.contains('hidden') || input.disabled) {
            closeSpecificMenu(row);
            return;
        }

        closeAllSpecificMenus();
        renderSpecificOptionsList(row);
        menu.classList.remove('hidden');
        input.setAttribute('aria-expanded', 'true');
    }

    function updateSpecificLabelFor() {
        const firstRow = getSpecificRows()[0] || null;
        const input = firstRow ? rowInput(firstRow) : null;
        if (input && input.id) {
            specificLabel.setAttribute('for', input.id);
        } else {
            specificLabel.removeAttribute('for');
        }
    }

    function ensureRowActionsState() {
        const rows = getSpecificRows();
        const showRemove = rows.length > 1;
        rows.forEach((row) => {
            const removeButton = rowRemoveButton(row);
            if (!removeButton) return;
            removeButton.classList.toggle('hidden', !showRemove);
            removeButton.disabled = rowInput(row)?.disabled ?? false;
        });
        updateSpecificLabelFor();
    }

    function buildSpecificRow(initialValue = '') {
        const fragment = specificRowTemplate.content.cloneNode(true);
        const row = fragment.querySelector('.education-specific-row');
        const input = rowInput(row);
        const optionsWrap = rowOptionsWrap(row);
        const removeButton = rowRemoveButton(row);

        rowCounter += 1;
        input.id = `education_specific_picker_input_${rowCounter}`;

        const initialLabel = String(initialValue || '').trim();
        input.value = initialLabel;
        input.dataset.selectedCode = optionCodeFromLabel(initialLabel, currentDetailOptions()) || '';
        input.setAttribute('aria-expanded', 'false');

        input.addEventListener('click', function () {
            if (selectedDetailMode() !== 'SPECIFIC') return;
            openSpecificMenu(row);
        });

        input.addEventListener('input', function () {
            input.dataset.selectedCode = optionCodeFromLabel(input.value, currentDetailOptions()) || '';
            syncState();
            openSpecificMenu(row);
        });

        input.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeSpecificMenu(row);
                return;
            }

            if (event.key === 'Enter') {
                const menu = rowMenu(row);
                if (menu && !menu.classList.contains('hidden')) {
                    const firstOption = optionsWrap.querySelector('button[data-code]');
                    if (firstOption) {
                        event.preventDefault();
                        firstOption.click();
                    }
                }
            }
        });

        optionsWrap.addEventListener('click', function (event) {
            const target = event.target instanceof HTMLElement ? event.target.closest('button[data-code]') : null;
            if (!target) return;

            const code = String(target.getAttribute('data-code') || '').trim();
            const label = String(target.getAttribute('data-label') || '').trim();
            if (!code || !label) return;

            input.dataset.selectedCode = code;
            input.value = label;
            closeSpecificMenu(row);
            syncState();
        });

        removeButton.addEventListener('click', function () {
            row.remove();
            ensureSpecificRowCount(1);
            syncState();
        });

        return row;
    }

    function ensureSpecificRowCount(minCount = 1) {
        let rows = getSpecificRows();
        while (rows.length < minCount) {
            specificRows.appendChild(buildSpecificRow(''));
            rows = getSpecificRows();
        }
        ensureRowActionsState();
    }

    function setSpecificRows(values) {
        specificRows.innerHTML = '';
        const list = Array.isArray(values) ? values : [];
        if (list.length === 0) {
            specificRows.appendChild(buildSpecificRow(''));
        } else {
            list.forEach((value) => {
                specificRows.appendChild(buildSpecificRow(value));
            });
        }
        ensureRowActionsState();
    }

    function setPreview(text) {
        previewText.textContent = text;
        previewWrap.classList.toggle('hidden', !hasValue(text));
    }

    function renderDetailControls() {
        const meta = currentMeta();
        const detail = meta?.detail || null;
        const mode = selectedDetailMode();

        const showDetail = Boolean(detail);
        detailWrap.classList.toggle('hidden', !showDetail);
        detailAnyInput.disabled = !showDetail;
        detailSpecificInput.disabled = !showDetail;

        if (!showDetail) {
            setDetailMode('');
            setSpecificRows([]);
            specificWrap.classList.add('hidden');
            addSpecificButton.classList.add('hidden');
            getSpecificRows().forEach((row) => {
                const input = rowInput(row);
                if (input) input.disabled = true;
            });
            closeAllSpecificMenus();
            return;
        }

        detailGroupLabel.textContent = detail.groupLabel;
        detailAnyLabel.textContent = detail.anyLabel;
        detailSpecificLabel.textContent = detail.specificLabel;

        const showSpecific = mode === 'SPECIFIC';
        specificWrap.classList.toggle('hidden', !showSpecific);
        addSpecificButton.classList.toggle('hidden', !showSpecific);
        specificLabel.textContent = detail.specificFieldLabel;

        ensureSpecificRowCount(1);

        getSpecificRows().forEach((row) => {
            const input = rowInput(row);
            if (!input) return;

            input.disabled = !showSpecific;
            if (!showSpecific) {
                closeSpecificMenu(row);
                return;
            }

            const options = detailOptions(detail);
            const selectedCode = String(input.dataset.selectedCode || '').trim();
            if (selectedCode && !optionByCode(selectedCode, options)) {
                input.dataset.selectedCode = '';
            }

            const exact = optionByLabel(input.value, options);
            if (exact) {
                input.dataset.selectedCode = exact.code;
                input.value = exact.label;
            } else if (!hasValue(input.value)) {
                input.dataset.selectedCode = '';
            }

            renderSpecificOptionsList(row);
        });

        ensureRowActionsState();
    }

    function evaluateState() {
        const code = currentEducationCode();
        const meta = currentMeta();
        if (!meta) {
            return { valid: false, message: 'Education requirement is required.' };
        }

        const detail = meta.detail;
        if (!detail) {
            return {
                valid: true,
                requirementText: meta.requirementTextAny,
                preview: meta.previewAny,
                config: {
                    minimum_education_code: code,
                    requirement_mode: null,
                    required_program_codes: [],
                    required_program_labels: [],
                    required_program_code: null,
                    required_program_label: null,
                },
            };
        }

        const mode = selectedDetailMode();
        if (!mode) {
            return { valid: false, message: `Select ${detail.groupLabel.toLowerCase()}.` };
        }

        if (mode === 'ANY') {
            return {
                valid: true,
                requirementText: meta.requirementTextAny,
                preview: meta.previewAny,
                config: {
                    minimum_education_code: code,
                    requirement_mode: 'ANY',
                    required_program_codes: [],
                    required_program_labels: [],
                    required_program_code: null,
                    required_program_label: null,
                },
            };
        }

        const options = detailOptions(detail);
        const selectedItems = [];
        const selectedCodes = new Set();
        let hasInvalidTypedValue = false;

        getSpecificRows().forEach((row) => {
            const input = rowInput(row);
            if (!input) return;

            const typedLabel = String(input.value || '').trim();
            if (!typedLabel) {
                return;
            }

            let selected = optionByCode(String(input.dataset.selectedCode || '').trim(), options);
            if (!selected) {
                selected = optionByLabel(typedLabel, options);
            }

            if (!selected) {
                hasInvalidTypedValue = true;
                return;
            }

            input.dataset.selectedCode = selected.code;
            input.value = selected.label;

            if (selectedCodes.has(selected.code)) {
                return;
            }

            selectedCodes.add(selected.code);
            selectedItems.push(selected);
        });

        if (selectedItems.length === 0 || hasInvalidTypedValue) {
            return { valid: false, message: `Select a valid ${detail.specificFieldLabel.toLowerCase()}.` };
        }

        const labels = selectedItems.map((item) => item.label);
        const codes = selectedItems.map((item) => item.code);
        const isMultiple = labels.length > 1;

        return {
            valid: true,
            requirementText: isMultiple
                ? detail.requirementTextSpecificMultiple(labels)
                : detail.requirementTextSpecific(labels[0]),
            preview: isMultiple
                ? detail.previewSpecificMultiple(labels)
                : detail.previewSpecific(labels[0]),
            config: {
                minimum_education_code: code,
                requirement_mode: 'SPECIFIC',
                required_program_codes: codes,
                required_program_labels: labels,
                required_program_code: codes[0] || null,
                required_program_label: labels[0] || null,
            },
        };
    }

    function syncState() {
        renderDetailControls();
        const state = evaluateState();

        if (!state.valid) {
            hiddenRequirement.value = '';
            hiddenConfig.value = '';
            setPreview('');
        } else {
            hiddenRequirement.value = state.requirementText || '';
            hiddenConfig.value = JSON.stringify(state.config || {});
            setPreview(state.preview || '');
        }

        if (typeof checkAllFieldsFilled === 'function') {
            checkAllFieldsFilled();
        }
    }

    function parseSpecificList(value) {
        const compact = String(value || '').trim().replace(/[.;]+$/, '');
        if (!compact) {
            return [];
        }

        const normalizedSeparators = compact.replace(/\s+(?:or|and)\s+/gi, ',');
        const pieces = normalizedSeparators.split(/[,;]+/);
        const seen = new Set();

        return pieces
            .map((item) => String(item || '').trim().replace(/^in\s+/i, ''))
            .filter((item) => {
                if (!item) return false;
                const key = normalize(item);
                if (seen.has(key)) return false;
                seen.add(key);
                return true;
            });
    }

    function parseExisting(rawText) {
        const text = String(rawText || '').trim();
        const n = normalize(text);
        if (!n) {
            return { code: '', mode: '', specificList: [] };
        }

        if (n.includes('senior high') || n.includes('grade 12') || n.includes('shs')) {
            return { code: 'SENIOR_HIGH_SCHOOL_GRAD', mode: '', specificList: [] };
        }

        if (n.includes('junior graduate') || n.includes('junior high') || n.includes('high school') || n.includes('secondary')) {
            return { code: 'HIGH_SCHOOL_GRAD', mode: '', specificList: [] };
        }

        if (n.includes('completion of 2 years') && n.includes('college')) {
            const m = text.match(/college\s+in\s+(.+)$/i);
            return {
                code: 'COLLEGE_2Y',
                mode: m ? 'SPECIFIC' : 'ANY',
                specificList: m ? parseSpecificList(m[1]) : [],
            };
        }

        if (n.includes('doctorate') || n.includes('doctoral') || n.includes('phd') || n.includes('ph.d')) {
            const m = text.match(/doctorate degree\s+in\s+(.+)$/i);
            return {
                code: 'DOCTORATE',
                mode: m ? 'SPECIFIC' : 'ANY',
                specificList: m ? parseSpecificList(m[1]) : [],
            };
        }

        if (n.includes('master')) {
            const m = text.match(/masteral degree\s+in\s+(.+)$/i);
            return {
                code: 'MASTERAL',
                mode: m ? 'SPECIFIC' : 'ANY',
                specificList: m ? parseSpecificList(m[1]) : [],
            };
        }

        if (n.includes('bachelor') || n.includes('college graduate') || n.includes('college degree') || n.includes('law')) {
            const m = text.match(/bachelor(?:'s)? degree\s+in\s+(.+)$/i);
            const looksLikeAnyBachelor = n.includes('(any field)')
                || /bachelor(?:'s)? degree$/i.test(n)
                || n.includes('college graduate')
                || n.includes('college degree');

            if (m) {
                return {
                    code: 'BACHELOR',
                    mode: 'SPECIFIC',
                    specificList: parseSpecificList(m[1]),
                };
            }

            return {
                code: 'BACHELOR',
                mode: looksLikeAnyBachelor ? 'ANY' : 'SPECIFIC',
                specificList: looksLikeAnyBachelor ? [] : parseSpecificList(text),
            };
        }

        return { code: '', mode: '', specificList: [] };
    }

    function setFromRaw(rawValue, treatAsInitial) {
        const parsed = parseExisting(rawValue);
        educationSelect.value = parsed.code || '';
        setDetailMode(parsed.mode || '');
        setSpecificRows(parsed.specificList || []);
        syncState();

        if (treatAsInitial) {
            educationSelect.defaultValue = educationSelect.value;
            hiddenRequirement.defaultValue = hiddenRequirement.value;
            hiddenConfig.defaultValue = hiddenConfig.value;
        }
    }

    educationSelect.addEventListener('change', function () {
        setDetailMode('');
        setSpecificRows([]);
        closeAllSpecificMenus();
        syncState();
    });

    detailAnyInput.addEventListener('change', function () {
        if (!detailAnyInput.checked) return;
        setSpecificRows([]);
        closeAllSpecificMenus();
        syncState();
    });

    detailSpecificInput.addEventListener('change', function () {
        if (!detailSpecificInput.checked) return;
        ensureSpecificRowCount(1);
        syncState();
    });

    addSpecificButton.addEventListener('click', function () {
        if (selectedDetailMode() !== 'SPECIFIC') return;
        specificRows.appendChild(buildSpecificRow(''));
        ensureRowActionsState();
        syncState();
    });

    document.addEventListener('click', function (event) {
        if (!(event.target instanceof Node)) {
            return;
        }

        if (!specificWrap.contains(event.target)) {
            closeAllSpecificMenus();
        }
    });

    window.setEducationRequirementFromRaw = function (rawValue, treatAsInitial) {
        setFromRaw(rawValue, Boolean(treatAsInitial));
    };

    window.hasEducationRequirementValue = function () {
        return hasValue(hiddenRequirement.value);
    };

    window.validateEducationRequirementConfig = function () {
        const state = evaluateState();
        return {
            valid: Boolean(state.valid),
            message: state.message || '',
        };
    };

    async function loadProgramOptions() {
        try {
            const response = await fetch(collegeCoursesListUrl, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            const rows = Array.isArray(payload?.data) ? payload.data : [];
            const mapped = rows
                .map((row) => {
                    const code = String(row?.code || '').trim();
                    const label = expandProgramAbbreviation(row?.name || '');
                    const level = String(row?.level || 'COLLEGE').trim().toUpperCase();
                    if (!code || !label) {
                        return null;
                    }
                    return { code, label, level };
                })
                .filter(Boolean);

            if (mapped.length === 0) {
                return;
            }

            const byLevel = {
                COLLEGE: [],
                MASTERAL: [],
                DOCTORATE: [],
            };

            mapped.forEach((item) => {
                const level = item.level;
                if (level === 'MASTERAL') {
                    byLevel.MASTERAL.push({ code: item.code, label: item.label });
                    return;
                }
                if (level === 'DOCTORATE') {
                    byLevel.DOCTORATE.push({ code: item.code, label: item.label });
                    return;
                }
                byLevel.COLLEGE.push({ code: item.code, label: item.label });
            });

            if (byLevel.COLLEGE.length > 0) {
                collegeCourseOptions = byLevel.COLLEGE;
            }
            if (byLevel.MASTERAL.length > 0) {
                masteralOptions = byLevel.MASTERAL;
            }
            if (byLevel.DOCTORATE.length > 0) {
                doctorateOptions = byLevel.DOCTORATE;
            }

            syncState();

        } catch (error) {
            // Keep fallback options when API is unavailable.
        }
    }

    setFromRaw(hiddenRequirement.value, true);
    loadProgramOptions();
});
</script>
