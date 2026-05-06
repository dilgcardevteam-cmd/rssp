@extends('layout.pds_layout')
@section('title','Work Experience')
@section('content')
<!-- Main Content -->
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form id="myForm" method="POST" action='/pds/submit_c2/display_c3'>
            @csrf

            <!-- Civil Service Eligibility Section -->
            <section class="bg-white rounded-2xl shadow-xl p-4 sm:p-8 mb-8 animate-slide-in">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6">
                    <div class="flex items-center mb-3 sm:mb-0">
                        <span class="material-icons text-blue-600 mr-3 text-2xl sm:text-3xl">verified</span>
                        <h2 class="text-xl sm:text-2xl font-bold text-gray-900">IV. CIVIL SERVICE ELIGIBILITY</h2>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 mb-6">
                    <button type="button" id="add-civil-service-btn" class="flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 text-sm sm:text-base">
                        <span class="material-icons mr-2 text-sm sm:text-base">add_circle</span>
                        Add Eligibility
                    </button>
                    <button id="clear-work-exp-btn" class="flex items-center justify-center px-4 py-2 bg-white-500 text-white text-sm sm:text-base cursor-not-allowed opacity-50" disabled>
                        <span class="material-icons mr-2 text-sm sm:text-base">delete</span>
                        Clear Eligibility
                    </button>
                </div>

                <!-- Empty State -->
                <div id="civil-service-empty" class="hidden text-center py-8 sm:py-12 bg-gray-50 rounded-lg">
                    <span class="material-icons text-4xl sm:text-6xl text-gray-300 mb-4">badge</span>
                    <p class="text-gray-500 mb-4 text-sm sm:text-base">No civil service eligibility entries yet.</p>
                    <p class="text-xs sm:text-sm text-gray-400">Click "Add Eligibility" to get started.</p>
                </div>

                <!-- Civil Service Table -->
                <div class="overflow-x-auto rounded-lg shadow-sm border border-gray-200">
                    <table id="civil-service-table" class="modern-table civil-table w-full min-w-[1080px]">
                        <thead>
                            <tr>
                                <th class="rounded-tl-lg text-xs sm:text-sm p-2 sm:p-3">27. CES/CSEE/CAREER SERVICE/RA 1080 (BOARD/ BAR)/UNDER SPECIAL LAWS/CATEGORY II/ IV ELIGIBILITY and ELIGIBILITIES FOR UNIFORMED PERSONNEL</th>
                                <th class="text-xs sm:text-sm p-2 sm:p-3">RATING<br>(If Applicable)</th>
                                <th class="text-xs sm:text-sm p-2 sm:p-3">DATE OF EXAMINATION / CONFERMENT</th>
                                <th class="text-xs sm:text-sm p-2 sm:p-3">PLACE OF EXAMINATION / CONFERMENT</th>
                                <th class="text-xs sm:text-sm p-2 sm:p-3" colspan="2">LICENSE (IF APPLICABLE)</th>
                                <th class="rounded-tr-lg text-center text-xs sm:text-sm p-2 sm:p-3">ACTIONS</th>
                            </tr>
                            <tr class="license-subhead border-l-gray-200 border-t border-b">
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th class="text-xs sm:text-sm p-1.5 sm:p-2">NUMBER</th>
                                <th class="text-xs sm:text-sm p-1.5 sm:p-2">VALID UNTIL</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Rows will be added dynamically -->
                        </tbody>
                    </table>
                </div>

                <p class="text-xs sm:text-sm text-gray-500 mt-4 italic">
                    * Click the 'Add' button to include additional eligibility.
                </p>
            </section>

            <!-- Work Experience Section -->
            <section class="bg-white rounded-2xl shadow-xl p-4 sm:p-8 mb-8 animate-slide-in">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6">
                    <div class="flex items-center mb-3 sm:mb-0">
                        <span class="material-icons text-blue-600 mr-3 text-2xl sm:text-3xl">work_history</span>
                        <h2 class="text-xl sm:text-2xl font-bold text-gray-900">V. WORK EXPERIENCE</h2>
                    </div>
                </div>

                <p class="text-gray-600 mb-6 text-xs sm:text-sm">
                    Include private employment. Start from your recent work. Description of duties should be indicated in the attached Work Experience Sheet.
                </p>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 mb-6">
                    <button type="button" id="add-work-exp-btn" class="flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 text-sm sm:text-base">
                        <span class="material-icons mr-2 text-sm sm:text-base">add_circle</span>
                        Add Work Experience
                    </button>

                    <button class="flex items-center justify-center px-4 py-2 bg-white-500 text-white text-sm sm:text-base cursor-not-allowed opacity-50" disabled>
                        <span class="material-icons mr-2 text-sm sm:text-base">delete</span>
                        Clear Work Experience
                    </button>
                </div>

                <!-- Empty State -->
                <div id="work-exp-empty" class="hidden text-center py-8 sm:py-12 bg-gray-50 rounded-lg">
                    <span class="material-icons text-4xl sm:text-6xl text-gray-300 mb-4">work_off</span>
                    <p class="text-gray-500 mb-4 text-sm sm:text-base">No work experience entries yet.</p>
                    <p class="text-xs sm:text-sm text-gray-400">Click "Add Work Experience" to get started.</p>
                </div>

                <!-- Work Experience Table -->
                <div class="overflow-x-auto rounded-lg shadow-sm border border-gray-200">
                    <table id="work-exp-table" class="modern-table w-full min-w-[1000px]">
                        <thead>
                            <tr>
                                <th class="text-xs sm:text-sm p-2 sm:p-3">28. INCLUSIVE DATES<br>(dd/mm/yyyy) FROM</th>
                                <th class="text-xs sm:text-sm p-2 sm:p-3">INCLUSIVE DATES<br>(dd/mm/yyyy) TO</th>
                                <th class="text-xs sm:text-sm p-2 sm:p-3">POSITION TITLE<br>(Write in full/Do not abbreviate)</th>
                                <th class="text-xs sm:text-sm p-2 sm:p-3">DEPARTMENT / AGENCY / OFFICE / COMPANY<br>(Write in full/Do not abbreviate)</th>
                                <th class="text-xs sm:text-sm p-2 sm:p-3">STATUS OF APPOINTMENT</th>
                                <th class="text-xs sm:text-sm p-2 sm:p-3">GOV'T SERVICE<br>(Y/ N)</th>
                                <th class="rounded-tr-lg text-center text-xs sm:text-sm p-2 sm:p-3">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody id="work-exp-body">
                            <!-- Rows will be added dynamically -->
                        </tbody>
                    </table>
                </div>
                <p class="text-xs sm:text-sm text-gray-500 mt-4 italic">
                    * Click the 'Add' button to include additional experience.
                </p>
            </section>

            <!-- Navigation -->
            <div class="flex flex-col sm:flex-row justify-between items-center mt-8 gap-4">
                <button type="button" onclick="window.location.href='{{ route('display_c1', ['simple' => 1]) }}'" class="use-loader w-full sm:w-auto px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors duration-200 flex items-center justify-center">
                    <span class="material-icons mr-2">arrow_back</span>
                    Previous
                </button>
                <button id="save-work-exp" type="submit" class="w-full sm:w-auto px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors duration-200 flex items-center justify-center">
                    Save
                    <span class="material-icons ml-2">arrow_forward</span>
                </button>
            </div>
        </form>  <!-- end form database entry -->
        <footer class="mt-8 sm:mt-12 text-center text-xs sm:text-sm text-gray-600 px-4">
            <p class="mb-2">
                <strong>WARNING:</strong> Any misrepresentation made in the Personal Data Sheet and the Work Experience Sheet shall cause the filing of administrative/criminal case/s against the person concerned.
            </p>
            <p>CS FORM 212 (Revised 2025), Page 2 of 4.</p>
        </footer>
    </main> 
    @endsection
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        /* Custom animations */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-slide-in {
            animation: slideIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .animate-fade-in {
            animation: fadeIn 0.3s ease-out;
        }

        /* Custom focus styles */
        .custom-focus:focus {
            outline: none;
            ring: 2px;
            ring-offset: 2px;
            ring-blue-500;
            border-color: #3b82f6;
        }

        /* Glass morphism effect */
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        /* Table styles */
        .modern-table {
            border-collapse: separate;
            border-spacing: 0;
        }

        .modern-table thead th {
            background: linear-gradient(135deg, #1c3faa, #1f74e1);
            color: white;
            font-weight: 700;
            padding: 0.85rem 0.9rem;
            text-align: left;
            font-size: 0.85rem;
            letter-spacing: 0.01em;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .civil-table thead th {
            text-transform: uppercase;
        }

        .civil-table thead .license-subhead th {
            background: linear-gradient(135deg, #1c3faa, #1f74e1);
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .modern-table tbody tr {
            transition: all 0.2s ease;
        }

        .modern-table tbody tr:hover {
            background-color: #f3f6ff;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .modern-table tbody td {
            padding: 0.7rem 0.9rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .modern-table input,
        .modern-table select {
            width: 100%;
            padding: 0.55rem 0.65rem;
            border: 1px solid #d1d5db;
            border-radius: 0.35rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            background-color: #fff;
        }

        .modern-table input:focus,
        .modern-table select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Remove row animation */
        @keyframes slideOut {
            to {
                opacity: 0;
                transform: translateX(-20px);
            }
        }

        .removing {
            animation: slideOut 0.3s ease-out forwards;
        }
    </style>

    <script>
        const DEFAULT_CIVIL_SERVICE_ELIGIBILITY_OPTIONS = [
            { name: 'Bar/Board Eligibility', legalBasis: 'RA 1080', level: 'Second Level' },
            { name: 'CSC Professional Eligibility', legalBasis: 'CSR 2017/PD 807', level: 'Second Level' },
            { name: 'Honor Graduate Eligibility', legalBasis: 'PD 907', level: 'Second Level' },
            { name: 'Foreign School Honor Graduate Eligibility', legalBasis: 'CSC Res. 1302714', level: 'Second Level' },
            { name: 'Scientific and Technological Specialist Eligibility', legalBasis: 'PD 997', level: 'Second Level' },
            { name: 'Electronic Data Processing Specialist Eligibility', legalBasis: 'CSC Res. 90-083', level: 'Second Level' },
            { name: 'Subprofessional (Sub-Prof) Eligibility', legalBasis: 'CSR 2017/PD 807', level: 'First Level' },
            { name: 'Skills Eligibility-Category II', legalBasis: 'CSC MC 11, s.1996', level: 'First Level' },
            { name: 'Barangay Official Eligibility', legalBasis: 'RA 7160', level: 'First Level' },
            { name: 'Sanggunian Member Eligibility', legalBasis: 'RA 10156', level: 'First Level' },
            { name: 'Barangay Health Worker Eligibility', legalBasis: 'RA 7883', level: 'First Level' },
            { name: 'Barangay Nutrition Scholar Eligibility', legalBasis: 'PD 1569', level: 'First Level' },
        ];
        let CIVIL_SERVICE_ELIGIBILITY_OPTIONS = [...DEFAULT_CIVIL_SERVICE_ELIGIBILITY_OPTIONS];

        const CIVIL_SERVICE_ELIGIBILITY_OTHERS_VALUE = '__OTHERS__';

        // Education level flags passed from controller
        const HAS_COLLEGE_DEGREE = @json($has_college_degree ?? false);
        const IS_HIGH_SCHOOL_ONLY = @json($is_high_school_only ?? false);
        const IS_ELEMENTARY_ONLY = @json($is_elementary_only ?? false);

        async function loadCivilServiceEligibilityOptions() {
            try {
                const response = await fetch(@json(route('pds.eligibilities.list')), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    throw new Error('Failed to load eligibility presets');
                }

                const payload = await response.json();
                const fetchedOptions = Array.isArray(payload?.data) ? payload.data : [];

                const normalized = fetchedOptions
                    .map((item) => ({
                        name: String(item?.name || '').trim(),
                        legalBasis: String(item?.legal_basis || '').trim(),
                        level: String(item?.level || '').trim(),
                    }))
                    .filter((item) => item.name !== '');

                if (normalized.length > 0) {
                    CIVIL_SERVICE_ELIGIBILITY_OPTIONS = normalized;
                    return;
                }
            } catch (error) {
                // Fallback to defaults if endpoint is unavailable.
            }

            CIVIL_SERVICE_ELIGIBILITY_OPTIONS = [...DEFAULT_CIVIL_SERVICE_ELIGIBILITY_OPTIONS];
        }

        function normalizeCivilServiceEligibilityName(value) {
            return String(value || '').trim().toLowerCase();
        }

        function isCivilServiceSecondLevelOption(option) {
            const normalizedLevel = String(option?.level || '').trim().toLowerCase();
            return normalizedLevel.includes('second level');
        }

        function isCivilServiceCscProfessionalOption(option) {
            const normalizedName = normalizeCivilServiceEligibilityName(option?.name || '');
            return normalizedName.includes('csc professional') || normalizedName.includes('career service professional');
        }

        function getSelectableCivilServiceEligibilityOptions() {
            // College degree holders can see ALL eligibilities (First and Second Level)
            if (HAS_COLLEGE_DEGREE) {
                return CIVIL_SERVICE_ELIGIBILITY_OPTIONS;
            }

            // High school only: First Level + CSC Professional only
            if (IS_HIGH_SCHOOL_ONLY) {
                return CIVIL_SERVICE_ELIGIBILITY_OPTIONS.filter((option) => {
                    if (!isCivilServiceSecondLevelOption(option)) {
                        return true; // First Level is allowed
                    }
                    // Only CSC Professional is allowed from Second Level
                    return isCivilServiceCscProfessionalOption(option);
                });
            }

            // Elementary only: First Level only (no Second Level at all)
            if (IS_ELEMENTARY_ONLY) {
                return CIVIL_SERVICE_ELIGIBILITY_OPTIONS.filter((option) => {
                    return !isCivilServiceSecondLevelOption(option);
                });
            }

            // Default: show all (fallback)
            return CIVIL_SERVICE_ELIGIBILITY_OPTIONS;
        }

        function findCivilServicePresetByName(name) {
            const normalizedTarget = normalizeCivilServiceEligibilityName(name);
            if (!normalizedTarget) {
                return null;
            }

            return CIVIL_SERVICE_ELIGIBILITY_OPTIONS.find(
                (option) => normalizeCivilServiceEligibilityName(option.name) === normalizedTarget
            ) || null;
        }

        function isDisallowedElementaryOnlyPreset(name) {
            // College degree holders have no restrictions
            if (HAS_COLLEGE_DEGREE) {
                return false;
            }

            const preset = findCivilServicePresetByName(name);
            if (!preset) {
                return false;
            }

            // High school only: disallow Second Level except CSC Professional
            if (IS_HIGH_SCHOOL_ONLY) {
                return isCivilServiceSecondLevelOption(preset) && !isCivilServiceCscProfessionalOption(preset);
            }

            // Elementary only: disallow ALL Second Level
            if (IS_ELEMENTARY_ONLY) {
                return isCivilServiceSecondLevelOption(preset);
            }

            return false;
        }

        function escapeCivilServiceOptionHtml(value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function renderCivilServiceEligibilityOptions(selectedValue) {
            const selectableOptions = getSelectableCivilServiceEligibilityOptions();
            const normalizedSelected = normalizeCivilServiceEligibilityName(selectedValue);
            const hasPresetMatch = selectableOptions.some(
                (option) => normalizeCivilServiceEligibilityName(option.name) === normalizedSelected
            );
            const selectedSelectValue = hasPresetMatch ? String(selectedValue || '').trim() : CIVIL_SERVICE_ELIGIBILITY_OTHERS_VALUE;

            return `
                <option value="" ${!selectedValue ? 'selected' : ''}>Select eligibility</option>
                ${selectableOptions.map((option) => {
                    const isSelected = selectedSelectValue === option.name;
                    return `
                        <option value="${escapeCivilServiceOptionHtml(option.name)}" ${isSelected ? 'selected' : ''}>
                            ${escapeCivilServiceOptionHtml(option.name)} (${escapeCivilServiceOptionHtml(option.legalBasis)} | ${escapeCivilServiceOptionHtml(option.level)})
                        </option>
                    `;
                }).join('')}
                <option value="${CIVIL_SERVICE_ELIGIBILITY_OTHERS_VALUE}" ${selectedSelectValue === CIVIL_SERVICE_ELIGIBILITY_OTHERS_VALUE ? 'selected' : ''}>Others (Specify)</option>
            `;
        }

        document.addEventListener('DOMContentLoaded', async function() {
            // Initialize tables
            const form = document.getElementById('myForm');
            const workExpTable = document.getElementById('work-exp-table');
            const civilServiceTable = document.getElementById('civil-service-table');
            const workExpEmpty = document.getElementById('work-exp-empty');
            const civilServiceEmpty = document.getElementById('civil-service-empty');

            await loadCivilServiceEligibilityOptions();

            // Check initial state
            updateEmptyState();

            // Display all of user's work experiences retrieved from database (TODO: Change to session instead..)

            var all_user_work_exps = {{ Js::from($all_user_work_exps) }}

            for (let i in all_user_work_exps) {
                addWorkExperienceRow(
                    false,
                    all_user_work_exps[i]['id'],
                    all_user_work_exps[i]['work_exp_from'],
                    all_user_work_exps[i]['work_exp_to'],
                    all_user_work_exps[i]['work_exp_position'],
                    all_user_work_exps[i]['work_exp_department'],
                    all_user_work_exps[i]['work_exp_status'],
                    all_user_work_exps[i]['work_exp_govt_service']
                )
            }

            // Display all of user's civil service eligibility retrieved from database (TODO: Change to session instead..)
            var all_user_civil_service_eligibility = {{ Js::from($all_user_civil_service_eligibility) }}

            for (let i in all_user_civil_service_eligibility) {
                addCivilServiceRow(
                    false,
                    all_user_civil_service_eligibility[i]['id'],
                    all_user_civil_service_eligibility[i]['cs_eligibility_career'],
                    all_user_civil_service_eligibility[i]['cs_eligibility_rating'],
                    all_user_civil_service_eligibility[i]['cs_eligibility_date'],
                    all_user_civil_service_eligibility[i]['cs_eligibility_place'],
                    all_user_civil_service_eligibility[i]['cs_eligibility_license'],
                    all_user_civil_service_eligibility[i]['cs_eligibility_validity']
                )
            }

            // Work Experience Add Button
            document.getElementById('add-work-exp-btn').addEventListener('click', function (event) {
                event.preventDefault();
                addWorkExperienceRow();
            });
            //document.getElementById('floating-add-work').addEventListener('click', addWorkExperienceRow);

            // Civil Service Eligibility Add Button
            document.getElementById('add-civil-service-btn').addEventListener('click', function (event) {
                event.preventDefault();
                addCivilServiceRow();
            });
            //document.getElementById('floating-add-civil').addEventListener('click', addCivilServiceRow);

            // Clear buttons
            //document.getElementById('clear-work-exp-btn').addEventListener('click', clearWorkExperience);
            //document.getElementById('clear-civil-service-btn').addEventListener('click', clearCivilService);

            // Add initial rows if empty
            if (workExpTable.querySelector('tbody').children.length === 0) {
                addWorkExperienceRow();
            }
            if (civilServiceTable.querySelector('tbody').children.length === 0) {
                addCivilServiceRow();
            }

            // Remove row functionality using event delegation
            document.addEventListener('click', function(e) {
                if (e.target && e.target.closest('.remove-row')) {
                    const row = e.target.closest('tr');
                    const table = row.closest('table');

                    let target_table = table.id;
                    let target_input = "";
                    let target_id = "";

                    if (target_table === 'work-exp-table') {
                        target_input = row.querySelector('input[name="work_exp_id[]"]');
                        target_id = target_input?.value;
                        console.log(target_table + " " + target_id);
                    }
                    else if (target_table === 'civil-service-table') {
                        target_input = row.querySelector('input[name="cs_eligibility_id[]"]');
                        target_id = target_input?.value;
                        console.log(target_table + " " + target_id);
                    }
                    else {
                        return showAppToast('Delete Failed');
                    }
                    if (target_id) {
                        fetch(`/c2/d/${target_table}/${target_id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                            }
                        })
                        .then(response => {
                            if (response.ok) {
                                // Add fade-out animation
                                row.classList.add('removing');
                                setTimeout(() => {
                                    row.remove();
                                    updateEmptyState();
                                }, 200);
                            }
                            else {
                                showAppToast('Delete Failed');
                            }
                        });
                    }
                    else {
                        // Add fade-out animation
                        row.classList.add('removing');
                        setTimeout(() => {
                            row.remove();
                            updateEmptyState();
                        }, 200);
                    }
                }
            });

            form.addEventListener('submit', function (event) {
                const workRows = workExpTable.querySelectorAll('tbody tr');
                let firstInvalidInput = null;

                workRows.forEach((row) => {
                    const isValid = validateWorkExperienceDatePair(row, false);
                    if (!isValid && !firstInvalidInput) {
                        firstInvalidInput = row.querySelector('input[name="work_exp_from[]"], input[name="work_exp_to[]"]');
                    }
                });

                if (!firstInvalidInput) {
                    return;
                }

                event.preventDefault();
                firstInvalidInput.reportValidity();
                firstInvalidInput.focus();
            });

            // Functions
            function parseDateInput(value) {
                if (!value) return null;
                const parsed = new Date(`${value}T00:00:00`);
                return Number.isNaN(parsed.getTime()) ? null : parsed;
            }

            function setWorkDateErrorState(input, message = '') {
                if (!input) return;
                input.setCustomValidity(message);
                input.classList.toggle('border-red-500', message !== '');
                input.classList.toggle('focus:border-red-500', message !== '');
            }

            function validateWorkExperienceDatePair(row, showMessage) {
                const fromInput = row.querySelector('input[name="work_exp_from[]"]');
                const toInput = row.querySelector('input[name="work_exp_to[]"]');
                const presentToggle = row.querySelector('[data-present-toggle]');
                if (!fromInput || !toInput) {
                    return true;
                }

                setWorkDateErrorState(fromInput, '');
                setWorkDateErrorState(toInput, '');

                if (presentToggle && presentToggle.checked) {
                    toInput.setCustomValidity('');
                    return true;
                }

                if (!fromInput.value || !toInput.value) {
                    return true;
                }

                const fromDate = parseDateInput(fromInput.value);
                const toDate = parseDateInput(toInput.value);
                if (!fromDate || !toDate) {
                    return true;
                }

                // Same-day attendance is allowed; only reject inverted date ranges.
                if (fromDate.getTime() > toDate.getTime()) {
                    setWorkDateErrorState(fromInput, 'FROM date must be the same day or earlier than TO date.');
                    setWorkDateErrorState(toInput, 'TO date must be the same day or later than FROM date.');
                    if (showMessage) {
                        toInput.reportValidity();
                    }
                    return false;
                }

                return true;
            }

            function attachWorkExperienceDateValidation(row) {
                const fromInput = row.querySelector('input[name="work_exp_from[]"]');
                const toInput = row.querySelector('input[name="work_exp_to[]"]');
                const presentToggle = row.querySelector('[data-present-toggle]');
                if (!fromInput || !toInput) {
                    return;
                }

                const validateOnBlur = () => validateWorkExperienceDatePair(row, true);
                const validateSilently = () => validateWorkExperienceDatePair(row, false);

                fromInput.addEventListener('blur', validateOnBlur);
                toInput.addEventListener('blur', validateOnBlur);
                fromInput.addEventListener('change', validateSilently);
                toInput.addEventListener('change', validateSilently);
                presentToggle?.addEventListener('change', () => {
                    applyPresentState(row, presentToggle.checked);
                    validateSilently();
                });
            }

            function applyPresentState(row, isPresent) {
                const toInput = row.querySelector('input[name="work_exp_to[]"]');
                const presentToggle = row.querySelector('[data-present-toggle]');
                if (!toInput || !presentToggle) return;

                if (isPresent) {
                    // Keep last typed date so we can restore if unchecked.
                    const previousDate = toInput.type === 'date' ? toInput.value : toInput.dataset.lastDate || '';
                    toInput.dataset.lastDate = previousDate;
                    toInput.type = 'text';
                    toInput.value = 'PRESENT';
                    toInput.readOnly = true;
                    toInput.required = false;
                    setWorkDateErrorState(toInput, '');
                } else {
                    const restoreDate = toInput.dataset.lastDate || '';
                    toInput.type = 'date';
                    toInput.readOnly = false;
                    toInput.required = true;
                    toInput.value = restoreDate;
                }
            }

            function addWorkExperienceRow(
                is_new = true,
                work_exp_id = null,
                work_exp_from = null,
                work_exp_to = null,
                work_exp_position = null,
                work_exp_department = null,
                work_exp_status = null,
                work_exp_govt_service = null,
                shouldScroll = true
            ) {
                const tbody = workExpTable.querySelector('tbody');
                const rowCount = tbody.children.length;
                const newRow = document.createElement('tr');
                newRow.className = 'animate-fade-in';
                const isPresentValue = (!is_new && typeof work_exp_to === 'string' && work_exp_to.toLowerCase() === 'present');


                // the <input..$rowCount is for the C2Controller for monitoring the rowCount :: FOR DATABASE
                newRow.innerHTML = `
                    <input type="hidden" name="work_exp_count" value="${rowCount + 1}">

                    <input type="hidden" name="work_exp_id[]" value="${(!is_new && work_exp_id !== null && work_exp_id !== undefined && String(work_exp_id).toLowerCase() !== 'null') ? work_exp_id : ''}">
                    <!-- <td class="font-medium text-center">${rowCount + 1}</td> -->
                    <td>
                        <input type="date" name="work_exp_from[]" class="form-input" value="${(!is_new) ? work_exp_from : ''}" />
                    </td>
                    <td>
                        <div class="flex items-center gap-2">
                            <input type="date" name="work_exp_to[]" class="form-input" value="${(!is_new && (String(work_exp_to).toLowerCase() !== 'present')) ? work_exp_to : ''}" data-work-to>
                            <label class="inline-flex items-center gap-1 text-xs text-gray-700">
                                <input type="checkbox" class="present-toggle h-4 w-4" data-present-toggle>
                                <span>Present</span>
                            </label>
                        </div>
                    </td>
                    <td>
                        <input type="text" name="work_exp_position[]"  placeholder="Position Title" class="form-input" value="${(!is_new) ? work_exp_position : ''}"/>
                    </td>
                    <td>
                        <input type="text" name="work_exp_department[]" placeholder="Department/Agency" class="form-input" value="${(!is_new) ? work_exp_department : ''}"/>
                    </td>
                    <td>
                        <select name="work_exp_status[]" class="form-input" >
                            <option value="" ${(is_new) ? 'selected' : ''}>Select</option>
                            <option value="Permanent" ${(!is_new && work_exp_status == 'Permanent') ? 'selected' : ''}>Permanent</option>
                            <option value="Temporary" ${(!is_new && work_exp_status == 'Temporary') ? 'selected' : ''}>Temporary</option>
                            <option value="Casual" ${(!is_new && work_exp_status == 'Casual') ? 'selected' : ''}>Casual</option>
                            <option value="Contractual" ${(!is_new && work_exp_status == 'Contractual') ? 'selected' : ''}>Contractual</option>
                        </select>
                    </td>
                    <td>
                        <select name="work_exp_govt_service[]" class="form-input">
                            <option value="" ${(is_new) ? 'selected' : ''}>Y/N</option>
                            <option value="Y" ${(!is_new && work_exp_govt_service == 'Y') ? 'selected' : ''}>Yes</option>
                            <option value="N" ${(!is_new && work_exp_govt_service == 'N') ? 'selected' : ''}>No</option>
                        </select>
                    </td>
                    <td class="text-center">
                        <button type="button" class="remove-row text-red-500 hover:text-red-700 transition-colors duration-200">
                            <span class="material-icons">delete</span>
                        </button>
                    </td>
                `;

                tbody.appendChild(newRow);
                const presentToggle = newRow.querySelector('[data-present-toggle]');
                if (presentToggle) {
                    presentToggle.checked = isPresentValue;
                    applyPresentState(newRow, isPresentValue);
                }
                attachWorkExperienceDateValidation(newRow);
                updateEmptyState();

                // Scroll to the new row
                if (shouldScroll) {
                    setTimeout(() => {
                        newRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }, 10);
                }
            }

            function addCivilServiceRow(
                is_new = true,
                cs_eligibility_id = null,
                cs_eligibility_career = null,
                cs_eligibility_rating = null,
                cs_eligibility_date = null,
                cs_eligibility_place = null,
                cs_eligibility_license = null,
                cs_eligibility_validity = null,
                shouldScroll = true
            ) {
                const tbody = civilServiceTable.querySelector('tbody');
                const rowCount = tbody.children.length;
                const newRow = document.createElement('tr');
                newRow.className = 'animate-fade-in';

                // the <input..$rowCount is for the C2Controller for monitoring the rowCount :: FOR DATABASE
                newRow.innerHTML = `
                    <input type="hidden" name="civil_service_count" value="${rowCount + 1}">

                    <input type="hidden" name="cs_eligibility_id[]" value="${(!is_new && cs_eligibility_id !== null && cs_eligibility_id !== undefined && String(cs_eligibility_id).toLowerCase() !== 'null') ? cs_eligibility_id : ''}">
                    <td>
                        <div class="space-y-2">
                            <select class="form-input" data-cs-career-select required>
                                ${renderCivilServiceEligibilityOptions((!is_new) ? cs_eligibility_career : '')}
                            </select>
                            <input type="text" class="form-input hidden" data-cs-career-custom placeholder="Specify eligibility not on list">
                            <input type="hidden" name="cs_eligibility_career[]" data-cs-career-value value="${(!is_new) ? escapeCivilServiceOptionHtml(cs_eligibility_career) : ''}">
                            <p class="text-[11px] leading-4 text-gray-500">Select from the list. Choose Others if not listed.</p>
                        </div>
                    </td>
                    <td>
                        <input type="text" name="cs_eligibility_rating[]" placeholder="Rating %" class="form-input" value="${(!is_new) ? cs_eligibility_rating : ''}"/>
                    </td>
                    <td>
                        <input type="date" name="cs_eligibility_date[]" class="form-input" required value="${(!is_new) ? cs_eligibility_date : ''}"/>
                    </td>
                    <td>
                        <input type="text" name="cs_eligibility_place[]" placeholder="Place of Examination / Conferment" class="form-input" required value="${(!is_new) ? cs_eligibility_place : ''}"/>
                    </td>
                    <td>
                        <input type="text" name="cs_eligibility_license[]" placeholder="License No. (if applicable)" class="form-input" value="${(!is_new) ? cs_eligibility_license : ''}"/>
                    </td>
                    <td>
                        <input type="date" name="cs_eligibility_validity[]" class="form-input" value="${(!is_new) ? cs_eligibility_validity : ''}"/>
                    </td>
                    <td class="text-center">
                        <button type="button" class="remove-row text-red-500 hover:text-red-700 transition-colors duration-200">
                            <span class="material-icons">delete</span>
                        </button>
                    </td>
                `;

                tbody.appendChild(newRow);
                initializeCivilServiceCareerInput(newRow, (!is_new) ? cs_eligibility_career : '');
                updateEmptyState();

                // Scroll to the new row
                if (shouldScroll) {
                    setTimeout(() => {
                        newRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }, 10);
                }
            }

            function initializeCivilServiceCareerInput(row, initialCareerValue = '') {
                const selectEl = row.querySelector('[data-cs-career-select]');
                const customInputEl = row.querySelector('[data-cs-career-custom]');
                const hiddenInputEl = row.querySelector('[data-cs-career-value]');

                if (!selectEl || !customInputEl || !hiddenInputEl) {
                    return;
                }

                const normalizedInitial = normalizeCivilServiceEligibilityName(initialCareerValue);
                const matchedPreset = getSelectableCivilServiceEligibilityOptions().find(
                    (item) => normalizeCivilServiceEligibilityName(item.name) === normalizedInitial
                );
                const initialPresetBlocked = isDisallowedElementaryOnlyPreset(initialCareerValue);

                if (initialPresetBlocked) {
                    selectEl.value = '';
                    hiddenInputEl.value = '';
                    customInputEl.value = '';
                    customInputEl.classList.add('hidden');
                    customInputEl.required = false;
                } else if (matchedPreset) {
                    selectEl.value = matchedPreset.name;
                    hiddenInputEl.value = matchedPreset.name;
                    customInputEl.value = '';
                    customInputEl.classList.add('hidden');
                    customInputEl.required = false;
                } else if (String(initialCareerValue || '').trim() !== '') {
                    selectEl.value = CIVIL_SERVICE_ELIGIBILITY_OTHERS_VALUE;
                    customInputEl.value = String(initialCareerValue || '').trim();
                    customInputEl.classList.remove('hidden');
                    customInputEl.required = true;
                    hiddenInputEl.value = customInputEl.value;
                } else {
                    selectEl.value = '';
                    customInputEl.value = '';
                    customInputEl.classList.add('hidden');
                    customInputEl.required = false;
                    hiddenInputEl.value = '';
                }

                const syncHiddenValue = () => {
                    const selectedValue = String(selectEl.value || '').trim();
                    if (selectedValue === CIVIL_SERVICE_ELIGIBILITY_OTHERS_VALUE) {
                        customInputEl.classList.remove('hidden');
                        customInputEl.required = true;
                        hiddenInputEl.value = String(customInputEl.value || '').trim();
                        return;
                    }

                    customInputEl.classList.add('hidden');
                    customInputEl.required = false;
                    customInputEl.value = '';
                    hiddenInputEl.value = selectedValue;
                };

                selectEl.addEventListener('change', syncHiddenValue);
                customInputEl.addEventListener('input', function () {
                    if (String(selectEl.value || '').trim() === CIVIL_SERVICE_ELIGIBILITY_OTHERS_VALUE) {
                        hiddenInputEl.value = String(customInputEl.value || '').trim();
                    }
                });
            }

            function clearWorkExperience() {
                if (confirm('Are you sure you want to clear all work experience entries?')) {
                    const tbody = workExpTable.querySelector('tbody');
                    tbody.innerHTML = '';
                    updateEmptyState();
                    addWorkExperienceRow(); // Add one empty row
                }
            }

            function clearCivilService() {
                if (confirm('Are you sure you want to clear all civil service eligibility entries?')) {
                    const tbody = civilServiceTable.querySelector('tbody');
                    tbody.innerHTML = '';
                    updateEmptyState();
                    addCivilServiceRow(); // Add one empty row
                }
            }

            function updateWorkExperienceNumbers() {
                const rows = workExpTable.querySelectorAll('tbody tr');
                rows.forEach((row, index) => {
                    row.cells[0].textContent = index + 1;
                    // Update the name attributes to maintain sequential numbering
                    const inputs = row.querySelectorAll('input, select');
                    inputs.forEach(input => {
                        const name = input.name.replace(/\d+$/, index + 1);
                        input.name = name;
                    });
                });
            }

            function updateCivilServiceEligibilityNumbers() {
                const rows = civilServiceTable.querySelectorAll('tbody tr');
                rows.forEach((row, index) => {
                    row.cells[0].textContent = index + 1;
                    // Update the name attributes to maintain sequential numbering
                    const inputs = row.querySelectorAll('input, select');
                    inputs.forEach(input => {
                        const name = input.name.replace(/\d+$/, index + 1);
                        input.name = name;
                    });
                });
            }

            function updateEmptyState() {
                // Work Experience
                const workExpRows = workExpTable.querySelector('tbody').children.length;
                if (workExpRows === 0) {
                    workExpTable.parentElement.classList.add('hidden');
                    workExpEmpty.classList.remove('hidden');
                } else {
                    workExpTable.parentElement.classList.remove('hidden');
                    workExpEmpty.classList.add('hidden');
                }

                // Civil Service
                const civilServiceRows = civilServiceTable.querySelector('tbody').children.length;
                if (civilServiceRows === 0) {
                    civilServiceTable.parentElement.classList.add('hidden');
                    civilServiceEmpty.classList.remove('hidden');
                } else {
                    civilServiceTable.parentElement.classList.remove('hidden');
                    civilServiceEmpty.classList.add('hidden');
                }
            }
        });

    function submit(location){
        const form = document.querySelector('#myForm');
        const simpleParam = new URLSearchParams(window.location.search).get('simple');
        const simpleQuery = simpleParam ? `?simple=${simpleParam}` : '';
        form.action = `/pds/submit_c2/${location}${simpleQuery}`;
        form.requestSubmit();
    }
    </script>
    <script>
        (function () {
            function initAutosave() {
            const form = document.getElementById('myForm');
            if (!form) return;

            const autosaveUrl = @json(route('pds.autosave', ['section' => 'c2']));
            const AUTOSAVE_INTERVAL_MS = 30000;
            let isDirty = false;
            let isSubmitting = false;
            let inFlight = false;
            let queued = false;

            const markDirty = () => { isDirty = true; };
            form.addEventListener('input', markDirty);
            form.addEventListener('change', markDirty);
            form.addEventListener('click', (event) => {
                if (event.target.closest('#add-work-exp-btn, #add-civil-service-btn, .remove-row')) {
                    markDirty();
                }
            });
            form.addEventListener('submit', () => { isSubmitting = true; });

            async function saveDraft(force = false) {
                if (isSubmitting) return;
                if (!force && !isDirty) return;
                if (inFlight) {
                    queued = true;
                    return;
                }

                inFlight = true;
                try {
                    const formData = new FormData(form);
                    const response = await fetch(autosaveUrl, {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin'
                    });
                    if (response.ok) {
                        isDirty = false;
                    }
                } catch (error) {
                    // Ignore autosave failures silently; normal submit remains available.
                } finally {
                    inFlight = false;
                    if (queued) {
                        queued = false;
                        saveDraft(true);
                    }
                }
            }

            const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

            async function flushDraftNow(options = {}) {
                const force = options.force === true;
                const parsedMaxWaitMs = Number(options.maxWaitMs);
                const maxWaitMs = Number.isFinite(parsedMaxWaitMs) && parsedMaxWaitMs > 0
                    ? parsedMaxWaitMs
                    : 1200;
                const startedAt = Date.now();

                while (inFlight && (Date.now() - startedAt) < maxWaitMs) {
                    if (force) {
                        queued = true;
                    }
                    await sleep(80);
                }

                if (inFlight) {
                    return false;
                }

                await saveDraft(force);

                while ((inFlight || queued) && (Date.now() - startedAt) < maxWaitMs) {
                    if (!inFlight && queued) {
                        queued = false;
                        await saveDraft(force);
                        continue;
                    }
                    await sleep(80);
                }

                return !(inFlight || queued);
            }

            window.__pdsAutosaveNow = flushDraftNow;

            setInterval(() => saveDraft(false), AUTOSAVE_INTERVAL_MS);

            document.addEventListener('visibilitychange', () => {
                if (document.hidden && isDirty) {
                    saveDraft(true);
                }
            });

            window.addEventListener('pagehide', () => {
                if (!isDirty || isSubmitting || !navigator.sendBeacon) return;
                const formData = new FormData(form);
                navigator.sendBeacon(autosaveUrl, formData);
            });

            window.addEventListener('beforeunload', () => {
                if (!isDirty || isSubmitting || !navigator.sendBeacon) return;
                const formData = new FormData(form);
                navigator.sendBeacon(autosaveUrl, formData);
            });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initAutosave, { once: true });
            } else {
                initAutosave();
            }
        })();
    </script>
