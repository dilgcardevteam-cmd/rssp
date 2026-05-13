@extends('layout.pds_layout')
@section('title','Work Experience')
@section('content')
@php
    $simple = in_array(request()->input('simple'), [1, '1', true, 'true'], true);
@endphp
<style>
    .pds-flow-page {
        position: relative;
        color: #163053;
        scroll-behavior: smooth;
    }

    .pds-flow-page::before {
        content: '';
        position: fixed;
        inset: 0;
        z-index: -1;
        background:
            radial-gradient(circle at top left, rgba(13, 91, 215, 0.14), transparent 28%),
            radial-gradient(circle at top right, rgba(0, 44, 118, 0.08), transparent 24%),
            linear-gradient(180deg, #f7faff 0%, #edf3fb 100%);
    }

    .pds-flow-banner {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        margin-bottom: 1rem;
        padding: 1rem 1.1rem;
        border: 1px solid rgba(164, 188, 227, 0.45);
        border-radius: 1.25rem;
        background: linear-gradient(135deg, #001a45 0%, #002c76 58%, #0b4ea8 100%);
        color: #fff;
        box-shadow: 0 18px 40px rgba(14, 36, 82, 0.18);
    }

    .pds-flow-banner p {
        margin: 0;
        color: rgba(255, 255, 255, 0.82);
    }

    .pds-flow-banner-title {
        display: inline-flex;
        align-items: center;
        gap: 0.7rem;
    }

    .pds-flow-banner-title .material-icons {
        font-size: clamp(1.6rem, 1.35rem + 0.55vw, 2rem);
        color: rgba(255, 255, 255, 0.92);
    }

    .pds-flow-banner-title strong {
        display: inline-block;
        font-size: clamp(1.45rem, 1.2rem + 0.7vw, 1.95rem);
        line-height: 1.15;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .pds-flow-banner-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
    }

    .pds-flow-banner-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.42rem 0.75rem;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, 0.18);
        background: rgba(255, 255, 255, 0.12);
        font-size: 0.78rem;
        line-height: 1.1;
    }

    .pds-flow-section {
        position: relative;
        overflow: hidden;
        scroll-margin-top: 6.5rem;
        border: 1px solid rgba(153, 176, 214, 0.32);
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(247, 250, 255, 0.96) 100%);
        box-shadow: 0 16px 40px rgba(15, 36, 79, 0.08), 0 2px 8px rgba(15, 36, 79, 0.04);
    }

    .pds-flow-section::before {
        content: '';
        position: absolute;
        inset: 0 0 auto;
        height: 4px;
        background: linear-gradient(90deg, #002c76 0%, #2563eb 56%, #7fb2ff 100%);
    }

    .pds-section-title {
        display: flex;
        align-items: center;
        gap: 0.9rem;
    }

    .pds-section-icon {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        width: 2.9rem;
        height: 2.9rem;
        margin-right: 0 !important;
        border-radius: 0.95rem;
        background: linear-gradient(135deg, #e6efff 0%, #f7faff 100%);
        color: #002c76;
        box-shadow: inset 0 0 0 1px rgba(115, 151, 210, 0.22);
    }

    .pds-primary-action,
    .pds-submit-button,
    .pds-back-button {
        border-radius: 0.95rem !important;
        box-shadow: 0 12px 24px rgba(0, 44, 118, 0.14);
    }

    a.pds-flow-banner-chip {
        text-decoration: none;
        color: inherit;
        transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
    }

    a.pds-flow-banner-chip:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-1px);
        box-shadow: 0 10px 18px rgba(8, 26, 67, 0.14);
    }

    .pds-empty-state {
        border: 1px dashed #c8d7ef;
        background: linear-gradient(180deg, #f8fbff 0%, #f1f6ff 100%) !important;
    }

    .pds-table-shell {
        border-color: #d7e3f5 !important;
        box-shadow: 0 10px 24px rgba(15, 36, 79, 0.05);
    }

    .pds-submit-bar {
        position: sticky;
        bottom: 1rem;
        z-index: 20;
        padding: 1rem;
        border: 1px solid rgba(162, 183, 218, 0.4);
        border-radius: 1.15rem;
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(12px);
        box-shadow: 0 18px 40px rgba(15, 36, 79, 0.12);
    }

    .pds-submit-button {
        background: linear-gradient(135deg, #0d5bd7 0%, #002c76 100%) !important;
    }

    .pds-warning-footer {
        border: 1px solid rgba(231, 188, 110, 0.4);
        border-radius: 1rem;
        background: linear-gradient(180deg, rgba(255, 248, 231, 0.95) 0%, rgba(255, 252, 245, 0.98) 100%);
        color: #70511b;
        box-shadow: 0 12px 28px rgba(122, 84, 19, 0.08);
    }

    .pds-preview-fab {
        position: fixed;
        right: 1.25rem;
        bottom: 1.25rem;
        z-index: 70;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
        min-height: 3.5rem;
        padding: 0.9rem 1.2rem;
        border: 1px solid rgba(189, 213, 255, 0.35);
        border-radius: 999px;
        background: linear-gradient(135deg, #002c76 0%, #0d5bd7 100%);
        color: #fff;
        font-size: 0.9rem;
        font-weight: 700;
        line-height: 1;
        box-shadow: 0 18px 36px rgba(7, 26, 67, 0.28);
        backdrop-filter: blur(12px);
        transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease, background 0.2s ease;
        touch-action: none;
        cursor: grab;
        user-select: none;
    }

    .pds-preview-fab:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 22px 42px rgba(7, 26, 67, 0.34);
    }

    .pds-preview-fab.is-dragging,
    .pds-preview-fab:active {
        cursor: grabbing;
    }

    .pds-preview-fab:disabled {
        cursor: not-allowed;
        opacity: 0.72;
        background: linear-gradient(135deg, #8a97ad 0%, #a7b2c6 100%);
        box-shadow: 0 10px 22px rgba(15, 36, 79, 0.12);
        transform: none;
    }

    @media (max-width: 640px) {
        .pds-preview-fab {
            left: 1rem;
            right: 1rem;
            bottom: calc(6.25rem + env(safe-area-inset-bottom, 0px));
            width: auto;
        }
    }
</style>
<!-- Main Content -->
<main class="pds-flow-page {{ $simple ? 'w-full max-w-none' : 'max-w-7xl mx-auto' }} -mt-6 sm:-mt-8 px-4 sm:px-6 lg:px-8 pt-0 pb-8">
        <form id="myForm" class="space-y-8" method="POST" action='/pds/submit_c2/display_c3'>
            @csrf
            <div class="pds-flow-banner">
                <div class="pds-flow-banner-title">
                    <span class="material-icons">workspace_premium</span>
                    <strong>Eligibility and Work Experience</strong>
                </div>
                <div class="pds-flow-banner-meta">
                    <a href="#eligibility-section" class="pds-flow-banner-chip">
                        <span class="material-icons text-sm">badge</span>
                        Eligibility records
                    </a>
                    <a href="#work-experience-section" class="pds-flow-banner-chip">
                        <span class="material-icons text-sm">work_history</span>
                        Employment history
                    </a>
                </div>
            </div>

            <!-- Civil Service Eligibility Section -->
            <section id="eligibility-section" class="pds-flow-section bg-white rounded-2xl shadow-xl p-4 sm:p-8 mb-8 animate-slide-in">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6">
                    <div class="pds-section-title mb-3 sm:mb-0">
                        <span class="material-icons pds-section-icon text-blue-600 text-2xl sm:text-3xl">verified</span>
                        <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Eligibility</h2>
                    </div>
                    <button type="button" id="add-civil-service-btn" class="pds-primary-action flex items-center justify-center w-full sm:w-auto sm:ml-auto px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 text-sm sm:text-base">
                        <span class="material-icons mr-2 text-sm sm:text-base">add_circle</span>
                        Add Eligibility
                    </button>
                </div>

                <!-- Empty State -->
                <div id="civil-service-empty" class="pds-empty-state hidden text-center py-8 sm:py-12 bg-gray-50 rounded-lg">
                    <span class="material-icons text-4xl sm:text-6xl text-gray-300 mb-4">badge</span>
                    <p class="text-gray-500 mb-4 text-sm sm:text-base">No civil service eligibility entries yet.</p>
                    <p class="text-xs sm:text-sm text-gray-400">Click "Add Eligibility" to get started.</p>
                </div>

                <!-- Civil Service Table -->
                <div class="pds-table-shell overflow-x-auto rounded-lg shadow-sm border border-gray-200">
                    <table id="civil-service-table" class="modern-table civil-table w-full min-w-[1080px]">
                        <thead>
                            <tr>
                                <th rowspan="2" class="rounded-tl-lg text-xs sm:text-sm p-2 sm:p-3">27. CES/CSEE/CAREER SERVICE/RA 1080 (BOARD/ BAR)/UNDER SPECIAL LAWS/CATEGORY II/ IV ELIGIBILITY and ELIGIBILITIES FOR UNIFORMED PERSONNEL</th>
                                <th rowspan="2" class="text-xs sm:text-sm p-2 sm:p-3">RATING<br>(If Applicable)</th>
                                <th rowspan="2" class="text-xs sm:text-sm p-2 sm:p-3">DATE OF EXAMINATION / CONFERMENT</th>
                                <th rowspan="2" class="text-xs sm:text-sm p-2 sm:p-3">PLACE OF EXAMINATION / CONFERMENT</th>
                                <th class="text-xs sm:text-sm p-2 sm:p-3" colspan="2">LICENSE (IF APPLICABLE)</th>
                                <th rowspan="2" class="rounded-tr-lg text-center text-xs sm:text-sm p-2 sm:p-3">ACTIONS</th>
                            </tr>
                            <tr class="license-subhead border-l-gray-200 border-t border-b">
                                <th class="text-xs sm:text-sm p-1.5 sm:p-2">NUMBER</th>
                                <th class="text-xs sm:text-sm p-1.5 sm:p-2">VALID UNTIL</th>
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
            <section id="work-experience-section" class="pds-flow-section bg-white rounded-2xl shadow-xl p-4 sm:p-8 mb-8 animate-slide-in">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6">
                    <div class="pds-section-title mb-3 sm:mb-0">
                        <span class="material-icons pds-section-icon text-blue-600 text-2xl sm:text-3xl">work_history</span>
                        <h2 class="text-xl sm:text-2xl font-bold text-gray-900">V. WORK EXPERIENCE</h2>
                    </div>
                    <button type="button" id="add-work-exp-btn" class="pds-primary-action flex items-center justify-center w-full sm:w-auto sm:ml-auto px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 text-sm sm:text-base">
                        <span class="material-icons mr-2 text-sm sm:text-base">add_circle</span>
                        Add Work Experience
                    </button>
                </div>

                <!-- Empty State -->
                <div id="work-exp-empty" class="pds-empty-state hidden text-center py-8 sm:py-12 bg-gray-50 rounded-lg">
                    <span class="material-icons text-4xl sm:text-6xl text-gray-300 mb-4">work_off</span>
                    <p class="text-gray-500 mb-4 text-sm sm:text-base">No work experience entries yet.</p>
                    <p class="text-xs sm:text-sm text-gray-400">Click "Add Work Experience" to get started.</p>
                </div>

                <!-- Work Experience Table -->
                <div class="pds-table-shell overflow-x-auto rounded-lg shadow-sm border border-gray-200">
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
            <div class="pds-submit-bar flex flex-col sm:flex-row justify-between items-center mt-8 gap-4">
                <button type="button" onclick="window.location.href='{{ route('display_c1', ['simple' => 1]) }}'" class="pds-back-button use-loader w-full sm:w-auto px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors duration-200 flex items-center justify-center">
                    <span class="material-icons mr-2">arrow_back</span>
                    Previous
                </button>
                <button id="save-work-exp" type="submit" class="pds-submit-button w-full sm:w-auto px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors duration-200 flex items-center justify-center">
                    Save
                    <span class="material-icons ml-2">arrow_forward</span>
                </button>
            </div>
        </form>  <!-- end form database entry -->
        <button
            type="button"
            id="pdsPreviewBtn"
            class="pds-preview-fab"
            aria-controls="pdsPreviewOverlay"
            aria-haspopup="dialog"
        >
            <span class="material-icons !text-base">visibility</span>
            Preview PDS
        </button>
        <footer class="pds-warning-footer mt-8 sm:mt-12 text-center text-xs sm:text-sm text-gray-600 px-4 py-4">
            <p class="mb-2">
                <strong>WARNING:</strong> Any misrepresentation made in the Personal Data Sheet and the Work Experience Sheet shall cause the filing of administrative/criminal case/s against the person concerned.
            </p>
            <p>CS FORM 212 (Revised 2025), Page 2 of 4.</p>
        </footer>
    </main> 
    <div id="pdsPreviewOverlay" class="hidden fixed inset-0 z-[100] bg-black bg-opacity-50 p-4 sm:p-8 flex items-center justify-center">
        <div class="bg-white w-full max-w-6xl h-[90vh] overflow-hidden rounded-xl shadow-2xl flex flex-col">
            <div class="flex items-center justify-between px-4 sm:px-6 py-3 border-b shrink-0">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900">Personal Data Sheet Preview</h3>
                <button id="pdsPreviewClose" class="p-2 rounded hover:bg-gray-100">
                    <span class="material-icons">close</span>
                </button>
            </div>
            <div class="p-4 sm:p-6 flex-1 min-h-0">
                <div class="mb-3 text-xs text-gray-500">Preview is rendered from the PDF template and auto-filled from your saved PDS data.</div>
                <div class="w-full h-[calc(100%-1.75rem)] border border-gray-200 rounded-lg overflow-hidden bg-gray-50">
                    <iframe
                        id="pdsPdfPreviewFrame"
                        title="PDS PDF Preview"
                        src="about:blank"
                        data-preview-src="{{ route('pds.preview', ['embedded' => 1]) }}"
                        scrolling="no"
                        class="w-full h-full"
                    ></iframe>
                </div>
            </div>
        </div>
    </div>
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
            text-align: center;
            text-transform: uppercase;
        }

        .civil-table thead .license-subhead th {
            background: linear-gradient(135deg, #1c3faa, #1f74e1);
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        #civil-service-table thead {
            background: linear-gradient(135deg, #071a46 0%, #0f2f7a 42%, #1b56c5 100%);
        }

        #civil-service-table thead th,
        #civil-service-table thead .license-subhead th {
            background: transparent !important;
            border-right: 1px solid rgba(255, 255, 255, 0.18);
            border-bottom: 1px solid rgba(255, 255, 255, 0.18);
        }

        #civil-service-table thead th:first-child {
            border-left: 1px solid rgba(255, 255, 255, 0.18);
        }

        #civil-service-table thead tr:first-child th {
            border-top: 1px solid rgba(255, 255, 255, 0.18);
        }

        #civil-service-table thead .license-subhead th {
            border-top: 1px solid rgba(255, 255, 255, 0.18);
        }

        #work-exp-table thead th {
            text-align: center;
        }

        #work-exp-table thead {
            background: linear-gradient(135deg, #071a46 0%, #0f2f7a 42%, #1b56c5 100%);
        }

        #work-exp-table thead th {
            background: transparent !important;
            border-top: 1px solid rgba(255, 255, 255, 0.18);
            border-right: 1px solid rgba(255, 255, 255, 0.18);
            border-bottom: 1px solid rgba(255, 255, 255, 0.18);
        }

        #work-exp-table thead th:first-child {
            border-left: 1px solid rgba(255, 255, 255, 0.18);
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
        const CIVIL_SERVICE_ELIGIBILITY_LEVEL_ALL_VALUE = '__ALL_LEVELS__';

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

        function normalizeCivilServiceEligibilityLevel(value) {
            return String(value || '').trim().toLowerCase();
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

        function getSelectableCivilServiceEligibilityLevels() {
            const seenLevels = new Set();
            const orderedLevels = [];

            getSelectableCivilServiceEligibilityOptions().forEach((option) => {
                const rawLevel = String(option?.level || '').trim();
                const normalizedLevel = normalizeCivilServiceEligibilityLevel(rawLevel);
                if (!normalizedLevel || seenLevels.has(normalizedLevel)) {
                    return;
                }

                seenLevels.add(normalizedLevel);
                orderedLevels.push(rawLevel);
            });

            return orderedLevels.sort((a, b) => {
                const first = normalizeCivilServiceEligibilityLevel(a);
                const second = normalizeCivilServiceEligibilityLevel(b);

                if (first === second) {
                    return 0;
                }

                if (first.includes('first level')) {
                    return -1;
                }

                if (second.includes('first level')) {
                    return 1;
                }

                return a.localeCompare(b);
            });
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

        function renderCivilServiceEligibilityLevelOptions(selectedLevel) {
            const selectableLevels = getSelectableCivilServiceEligibilityLevels();
            const normalizedSelected = normalizeCivilServiceEligibilityLevel(selectedLevel);
            const hasLevelMatch = selectableLevels.some(
                (level) => normalizeCivilServiceEligibilityLevel(level) === normalizedSelected
            );
            const selectedFilterValue = hasLevelMatch ? String(selectedLevel || '').trim() : CIVIL_SERVICE_ELIGIBILITY_LEVEL_ALL_VALUE;

            return `
                <option value="${CIVIL_SERVICE_ELIGIBILITY_LEVEL_ALL_VALUE}" ${selectedFilterValue === CIVIL_SERVICE_ELIGIBILITY_LEVEL_ALL_VALUE ? 'selected' : ''}>All Levels</option>
                ${selectableLevels.map((level) => `
                    <option value="${escapeCivilServiceOptionHtml(level)}" ${selectedFilterValue === level ? 'selected' : ''}>${escapeCivilServiceOptionHtml(level)}</option>
                `).join('')}
            `;
        }

        function renderCivilServiceEligibilityOptions(selectedValue, selectedLevel = CIVIL_SERVICE_ELIGIBILITY_LEVEL_ALL_VALUE) {
            const normalizedSelectedLevel = normalizeCivilServiceEligibilityLevel(selectedLevel);
            const selectableOptions = getSelectableCivilServiceEligibilityOptions().filter((option) => {
                if (normalizedSelectedLevel === '' || selectedLevel === CIVIL_SERVICE_ELIGIBILITY_LEVEL_ALL_VALUE) {
                    return true;
                }

                return normalizeCivilServiceEligibilityLevel(option.level) === normalizedSelectedLevel;
            });
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
            let finalSubmitRequested = false;

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

            form.addEventListener('submit', async function (event) {
                if (finalSubmitRequested) {
                    form.dataset.finalSubmit = '1';
                    return;
                }

                const workRows = workExpTable.querySelectorAll('tbody tr');
                let firstInvalidInput = null;

                workRows.forEach((row) => {
                    const isValid = validateWorkExperienceDatePair(row, false);
                    if (!isValid && !firstInvalidInput) {
                        firstInvalidInput = row.querySelector('input[name="work_exp_from[]"], input[name="work_exp_to[]"]');
                    }
                });

                if (firstInvalidInput) {
                    event.preventDefault();
                    firstInvalidInput.reportValidity();
                    firstInvalidInput.focus();
                    return;
                }

                event.preventDefault();

                if (typeof window.__pdsAutosaveNow === 'function') {
                    try {
                        await window.__pdsAutosaveNow({ force: true, maxWaitMs: 2500 });
                    } catch (error) {
                        // Ignore autosave flush failures; explicit submit should still proceed.
                    }
                }

                finalSubmitRequested = true;
                form.dataset.finalSubmit = '1';
                form.requestSubmit();
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
                    const restoreDate = toInput.dataset.lastDate || toInput.value || '';
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
                            <option value="Contract of Service" ${(!is_new && work_exp_status == 'Contract of Service') ? 'selected' : ''}>Contract of Service</option>
                            <option value="Job Order" ${(!is_new && work_exp_status == 'Job Order') ? 'selected' : ''}>Job Order</option>
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
                            <div class="grid gap-2 sm:grid-cols-[11rem_minmax(0,1fr)]">
                                <select class="form-input" data-cs-career-level-select>
                                    ${renderCivilServiceEligibilityLevelOptions()}
                                </select>
                                <select class="form-input" data-cs-career-select required>
                                    ${renderCivilServiceEligibilityOptions((!is_new) ? cs_eligibility_career : '')}
                                </select>
                            </div>
                            <input type="text" class="form-input hidden" data-cs-career-custom placeholder="Specify eligibility not on list">
                            <input type="hidden" name="cs_eligibility_career[]" data-cs-career-value value="${(!is_new) ? escapeCivilServiceOptionHtml(cs_eligibility_career) : ''}">
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
                const levelSelectEl = row.querySelector('[data-cs-career-level-select]');
                const selectEl = row.querySelector('[data-cs-career-select]');
                const customInputEl = row.querySelector('[data-cs-career-custom]');
                const hiddenInputEl = row.querySelector('[data-cs-career-value]');

                if (!levelSelectEl || !selectEl || !customInputEl || !hiddenInputEl) {
                    return;
                }

                const normalizedInitial = normalizeCivilServiceEligibilityName(initialCareerValue);
                const matchedPreset = getSelectableCivilServiceEligibilityOptions().find(
                    (item) => normalizeCivilServiceEligibilityName(item.name) === normalizedInitial
                );
                const initialPresetBlocked = isDisallowedElementaryOnlyPreset(initialCareerValue);

                if (initialPresetBlocked) {
                    levelSelectEl.value = CIVIL_SERVICE_ELIGIBILITY_LEVEL_ALL_VALUE;
                    selectEl.value = '';
                    hiddenInputEl.value = '';
                    customInputEl.value = '';
                    customInputEl.classList.add('hidden');
                    customInputEl.required = false;
                } else if (matchedPreset) {
                    levelSelectEl.value = matchedPreset.level;
                    selectEl.innerHTML = renderCivilServiceEligibilityOptions(matchedPreset.name, matchedPreset.level);
                    selectEl.value = matchedPreset.name;
                    hiddenInputEl.value = matchedPreset.name;
                    customInputEl.value = '';
                    customInputEl.classList.add('hidden');
                    customInputEl.required = false;
                } else if (String(initialCareerValue || '').trim() !== '') {
                    levelSelectEl.value = CIVIL_SERVICE_ELIGIBILITY_LEVEL_ALL_VALUE;
                    selectEl.innerHTML = renderCivilServiceEligibilityOptions(initialCareerValue, CIVIL_SERVICE_ELIGIBILITY_LEVEL_ALL_VALUE);
                    selectEl.value = CIVIL_SERVICE_ELIGIBILITY_OTHERS_VALUE;
                    customInputEl.value = String(initialCareerValue || '').trim();
                    customInputEl.classList.remove('hidden');
                    customInputEl.required = true;
                    hiddenInputEl.value = customInputEl.value;
                } else {
                    levelSelectEl.value = CIVIL_SERVICE_ELIGIBILITY_LEVEL_ALL_VALUE;
                    selectEl.value = '';
                    customInputEl.value = '';
                    customInputEl.classList.add('hidden');
                    customInputEl.required = false;
                    hiddenInputEl.value = '';
                }

                const syncLevelFilteredOptions = () => {
                    const currentCareerValue = String(hiddenInputEl.value || '').trim();
                    const selectedLevel = String(levelSelectEl.value || '').trim() || CIVIL_SERVICE_ELIGIBILITY_LEVEL_ALL_VALUE;
                    const isOthersSelected = String(selectEl.value || '').trim() === CIVIL_SERVICE_ELIGIBILITY_OTHERS_VALUE;
                    selectEl.innerHTML = renderCivilServiceEligibilityOptions(
                        isOthersSelected ? currentCareerValue : selectEl.value,
                        selectedLevel
                    );

                    if (isOthersSelected) {
                        selectEl.value = CIVIL_SERVICE_ELIGIBILITY_OTHERS_VALUE;
                        customInputEl.classList.remove('hidden');
                        customInputEl.required = true;
                        hiddenInputEl.value = currentCareerValue;
                        return;
                    }

                    const hasMatchingOption = Array.from(selectEl.options).some(
                        (option) => String(option.value || '').trim() === currentCareerValue
                    );

                    if (hasMatchingOption && currentCareerValue !== '') {
                        selectEl.value = currentCareerValue;
                        hiddenInputEl.value = currentCareerValue;
                        return;
                    }

                    selectEl.value = '';
                    hiddenInputEl.value = '';
                    customInputEl.value = '';
                    customInputEl.classList.add('hidden');
                    customInputEl.required = false;
                };

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

                levelSelectEl.addEventListener('change', syncLevelFilteredOptions);
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
            function initPdsPreview() {
                const openBtn = document.getElementById('pdsPreviewBtn');
                const overlay = document.getElementById('pdsPreviewOverlay');
                const closeBtn = document.getElementById('pdsPreviewClose');
                const frame = document.getElementById('pdsPdfPreviewFrame');
                if (!openBtn || !overlay || !closeBtn || !frame) return;

                const closeOverlay = () => overlay.classList.add('hidden');

                openBtn.addEventListener('click', async () => {
                    const lastDragAt = Number(openBtn.dataset.lastDragAt || '0');
                    if (Date.now() - lastDragAt < 250) {
                        return;
                    }

                    if (openBtn.disabled) return;

                    const originalText = openBtn.innerHTML;
                    openBtn.innerHTML = '<span class="material-icons text-sm animate-spin">autorenew</span>Preparing...';
                    openBtn.disabled = true;

                    try {
                        if (typeof window.__pdsAutosaveNow === 'function') {
                            await window.__pdsAutosaveNow({ force: true, maxWaitMs: 5000 });
                        }

                        const previewSrc = frame.dataset.previewSrc || @json(route('pds.preview'));
                        const separator = previewSrc.includes('?') ? '&' : '?';
                        frame.src = previewSrc + separator + 'ts=' + Date.now();
                        overlay.classList.remove('hidden');
                    } finally {
                        openBtn.innerHTML = originalText;
                        openBtn.disabled = false;
                    }
                });

                closeBtn.addEventListener('click', closeOverlay);
                overlay.addEventListener('click', (event) => {
                    if (event.target === overlay) {
                        closeOverlay();
                    }
                });
                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && !overlay.classList.contains('hidden')) {
                        closeOverlay();
                    }
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initPdsPreview, { once: true });
            } else {
                initPdsPreview();
            }
        })();
    </script>
    <script>
        (function () {
            function initDraggablePdsPreviewButton() {
                const button = document.getElementById('pdsPreviewBtn');
                if (!button) return;

                let isPointerDown = false;
                let isDragging = false;
                let pointerId = null;
                let startX = 0;
                let startY = 0;
                let originLeft = 0;
                let originTop = 0;
                let suppressClick = false;

                const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

                const getViewportBounds = () => {
                    const rect = button.getBoundingClientRect();
                    return {
                        minLeft: 8,
                        minTop: 8,
                        maxLeft: Math.max(8, window.innerWidth - rect.width - 8),
                        maxTop: Math.max(8, window.innerHeight - rect.height - 8),
                    };
                };

                const applyPosition = (left, top) => {
                    button.style.left = `${left}px`;
                    button.style.top = `${top}px`;
                    button.style.right = 'auto';
                    button.style.bottom = 'auto';
                };

                const syncToViewport = () => {
                    const rect = button.getBoundingClientRect();
                    const bounds = getViewportBounds();
                    applyPosition(
                        clamp(rect.left, bounds.minLeft, bounds.maxLeft),
                        clamp(rect.top, bounds.minTop, bounds.maxTop)
                    );
                };

                button.addEventListener('pointerdown', (event) => {
                    if (event.button !== 0) return;

                    const rect = button.getBoundingClientRect();
                    isPointerDown = true;
                    isDragging = false;
                    pointerId = event.pointerId;
                    startX = event.clientX;
                    startY = event.clientY;
                    originLeft = rect.left;
                    originTop = rect.top;

                    applyPosition(originLeft, originTop);
                    button.setPointerCapture(pointerId);
                });

                button.addEventListener('pointermove', (event) => {
                    if (!isPointerDown || event.pointerId !== pointerId) return;

                    const deltaX = event.clientX - startX;
                    const deltaY = event.clientY - startY;

                    if (!isDragging && (Math.abs(deltaX) > 4 || Math.abs(deltaY) > 4)) {
                        isDragging = true;
                        button.classList.add('is-dragging');
                    }

                    if (!isDragging) return;

                    event.preventDefault();
                    const bounds = getViewportBounds();
                    applyPosition(
                        clamp(originLeft + deltaX, bounds.minLeft, bounds.maxLeft),
                        clamp(originTop + deltaY, bounds.minTop, bounds.maxTop)
                    );
                });

                button.addEventListener('pointerup', (event) => {
                    if (event.pointerId !== pointerId) return;

                    if (isDragging) {
                        event.preventDefault();
                        event.stopPropagation();
                        suppressClick = true;
                        button.dataset.lastDragAt = String(Date.now());
                    }

                    button.classList.remove('is-dragging');
                    isPointerDown = false;
                    isDragging = false;
                    pointerId = null;
                });

                button.addEventListener('pointercancel', () => {
                    button.classList.remove('is-dragging');
                    isPointerDown = false;
                    isDragging = false;
                    pointerId = null;
                });

                button.addEventListener('click', (event) => {
                    if (suppressClick) {
                        event.preventDefault();
                        event.stopPropagation();
                        suppressClick = false;
                    }
                }, true);

                window.addEventListener('resize', syncToViewport);
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initDraggablePdsPreviewButton, { once: true });
            } else {
                initDraggablePdsPreviewButton();
            }
        })();
    </script>
    <script>
        (function () {
            function initC2DraftSupport() {
                const form = document.getElementById('myForm');
                if (!form) return;

                const LOCAL_DRAFT_KEY = 'dilg-car:pds:c2:local-draft:v1';
                const errorMessages = @json($errors->all());
                const sessionError = @json(session('error'));
                let isRestoringDraft = false;

                const notify = (message, type = 'error', duration = 5000) => {
                    if (!message) return;
                    if (typeof window.showNotification === 'function') {
                        window.showNotification(message, type);
                        return;
                    }
                    if (typeof window.showAppToast === 'function') {
                        window.showAppToast(message, type, duration);
                    }
                };

                const shouldShowDraftRestoreToast = () => {
                    const navigation = performance.getEntriesByType?.('navigation')?.[0];
                    return navigation?.type === 'reload' || performance.navigation?.type === 1;
                };

                const formFields = () => Array.from(form.querySelectorAll('[name]'));
                const getNamedFields = (name) => formFields().filter((field) => field.name === name);

                function collectDraftData() {
                    const data = {};

                    formFields().forEach((field) => {
                        if (!field.name || ['file', 'submit', 'button', 'reset'].includes(field.type)) {
                            return;
                        }

                        if (field.type === 'radio') {
                            if (!Object.prototype.hasOwnProperty.call(data, field.name)) {
                                data[field.name] = '';
                            }
                            if (field.checked) {
                                data[field.name] = field.value;
                            }
                            return;
                        }

                        if (field.type === 'checkbox') {
                            if (!Array.isArray(data[field.name])) {
                                data[field.name] = [];
                            }
                            if (field.checked) {
                                data[field.name].push(field.value);
                            }
                            return;
                        }

                        data[field.name] = field.value;
                    });

                    return data;
                }

                function persistLocalDraft() {
                    try {
                        window.localStorage.setItem(LOCAL_DRAFT_KEY, JSON.stringify({
                            savedAt: new Date().toISOString(),
                            data: collectDraftData(),
                        }));
                    } catch (error) {
                        // Ignore storage write failures.
                    }
                }

                function readLocalDraft() {
                    try {
                        const raw = window.localStorage.getItem(LOCAL_DRAFT_KEY);
                        return raw ? JSON.parse(raw) : null;
                    } catch (error) {
                        return null;
                    }
                }

                function draftDataDiffers(localDraftData) {
                    if (!localDraftData || typeof localDraftData !== 'object') {
                        return false;
                    }

                    const currentData = collectDraftData();
                    const fieldNames = new Set([...Object.keys(currentData), ...Object.keys(localDraftData)]);

                    for (const fieldName of fieldNames) {
                        const currentValue = currentData[fieldName];
                        const localValue = localDraftData[fieldName];

                        if (Array.isArray(currentValue) || Array.isArray(localValue)) {
                            const currentArray = Array.isArray(currentValue) ? currentValue : [];
                            const localArray = Array.isArray(localValue) ? localValue : [];

                            if (currentArray.length !== localArray.length) {
                                return true;
                            }

                            if (currentArray.some((value, index) => value !== localArray[index])) {
                                return true;
                            }

                            continue;
                        }

                        if (String(currentValue ?? '') !== String(localValue ?? '')) {
                            return true;
                        }
                    }

                    return false;
                }

                function restoreLocalDraftIfNeeded() {
                    const localDraft = readLocalDraft();
                    if (!localDraft?.data || !draftDataDiffers(localDraft.data)) {
                        return false;
                    }

                    const restoredFields = new Set();
                    isRestoringDraft = true;

                    Object.entries(localDraft.data).forEach(([name, value]) => {
                        const fields = getNamedFields(name);
                        if (!fields.length) return;

                        const firstField = fields[0];

                        if (firstField.type === 'radio') {
                            const checkedField = fields.find((field) => field.value === value) ?? null;
                            fields.forEach((field) => {
                                field.checked = field.value === value;
                            });
                            if (checkedField) restoredFields.add(checkedField);
                            return;
                        }

                        if (firstField.type === 'checkbox') {
                            const values = Array.isArray(value) ? value : [value];
                            fields.forEach((field) => {
                                field.checked = values.includes(field.value);
                            });
                            restoredFields.add(firstField);
                            return;
                        }

                        firstField.value = String(value ?? '');
                        restoredFields.add(firstField);
                    });

                    restoredFields.forEach((field) => {
                        field.dispatchEvent(new Event('change', { bubbles: true }));
                        if (!['radio', 'checkbox', 'select-one', 'select-multiple'].includes(field.type)) {
                            field.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    });

                    isRestoringDraft = false;
                    if (shouldShowDraftRestoreToast()) {
                        notify('A saved C2 draft was restored from this browser.', 'warning', 4000);
                    }
                    return true;
                }

                const persistIfNeeded = () => {
                    if (isRestoringDraft) return;
                    persistLocalDraft();
                };

                form.addEventListener('input', persistIfNeeded);
                form.addEventListener('change', persistIfNeeded);
                form.addEventListener('click', (event) => {
                    if (event.target.closest('button[type="button"], input[type="checkbox"], input[type="radio"]')) {
                        persistIfNeeded();
                    }
                });
                form.addEventListener('submit', persistLocalDraft);
                window.addEventListener('pagehide', persistLocalDraft);
                window.addEventListener('beforeunload', persistLocalDraft);

                restoreLocalDraftIfNeeded();

                const firstError = sessionError || (errorMessages.length ? errorMessages[0] : '');
                if (firstError) {
                    const suffix = errorMessages.length > 1 ? ` (+${errorMessages.length - 1} more)` : '';
                    notify(`${firstError}${suffix}`, 'error', 6000);
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initC2DraftSupport, { once: true });
            } else {
                initC2DraftSupport();
            }
        })();
    </script>
    <script>
        (function () {
            function initAutosave() {
            const form = document.getElementById('myForm');
            if (!form) return;

            const autosaveUrl = @json(route('pds.autosave', ['section' => 'c2']));
            const AUTOSAVE_INTERVAL_MS = 30000;
            const AUTOSAVE_DEBOUNCE_MS = 600;
            let isDirty = false;
            let isSubmitting = false;
            let inFlight = false;
            let queued = false;
            let autosaveTimer = null;

            const scheduleAutosave = () => {
                if (isSubmitting) return;
                window.clearTimeout(autosaveTimer);
                autosaveTimer = window.setTimeout(() => {
                    saveDraft(false);
                }, AUTOSAVE_DEBOUNCE_MS);
            };

            const markDirty = () => {
                isDirty = true;
                scheduleAutosave();
            };
            form.addEventListener('input', markDirty);
            form.addEventListener('change', markDirty);
            form.addEventListener('click', (event) => {
                if (event.target.closest('#add-work-exp-btn, #add-civil-service-btn, .remove-row')) {
                    markDirty();
                }
            });
            form.dataset.finalSubmit = form.dataset.finalSubmit || '0';
            form.addEventListener('submit', () => {
                window.clearTimeout(autosaveTimer);
                if (form.dataset.finalSubmit === '1') {
                    isSubmitting = true;
                }
            });

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
                    window.clearTimeout(autosaveTimer);
                    saveDraft(true);
                }
            });

            window.addEventListener('pagehide', () => {
                window.clearTimeout(autosaveTimer);
                if (!isDirty || isSubmitting || !navigator.sendBeacon) return;
                const formData = new FormData(form);
                navigator.sendBeacon(autosaveUrl, formData);
            });

            window.addEventListener('beforeunload', () => {
                window.clearTimeout(autosaveTimer);
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
