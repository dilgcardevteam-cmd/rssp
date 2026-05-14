@extends('layout.pds_layout')
@section('title', 'Learning and Development')
@php
    $simple = in_array(request()->input('simple'), [1, '1', true, 'true'], true);
@endphp
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
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
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

    /* Floating label styles */
    .floating-label {
        transition: all 0.2s ease-out;
    }

    .floating-label-input:focus+.floating-label,
    .floating-label-input:not(:placeholder-shown)+.floating-label {
        transform: translateY(-1.25rem) scale(0.85);
        color: #3b82f6;
        background-color: white;
        padding: 0 0.25rem;
    }

    /* Glass morphism effect */
    .glass-effect {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.18);
    }

    /* Card hover effects */
    .card-hover {
        transition: all 0.3s ease;
    }

    .card-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    /* Remove animation */
    @keyframes slideOut {
        to {
            opacity: 0;
            transform: translateX(-20px);
        }
    }

    .removing {
        animation: slideOut 0.3s ease-out forwards;
    }

    label {
        text-transform: uppercase;
    }

    .entry-collapse-toggle {
        width: 100%;
        text-align: left;
    }

    .entry-collapse-toggle:focus-visible {
        outline: 2px solid #2563eb;
        outline-offset: 2px;
        border-radius: 0.75rem;
    }

    .collapse-icon {
        transition: transform 0.2s ease;
    }

    .entry-card.is-collapsed .collapse-icon {
        transform: rotate(180deg);
    }

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
        align-items: flex-start;
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

    .pds-empty-state {
        border: 1px dashed #c8d7ef;
        background: linear-gradient(180deg, #f8fbff 0%, #f1f6ff 100%) !important;
    }

    .pds-subsection {
        border: 1px solid #d8e4f8;
        border-radius: 1rem;
        background: linear-gradient(180deg, rgba(248, 251, 255, 0.94) 0%, rgba(255, 255, 255, 0.98) 100%);
        padding: 1rem;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.72);
    }

    .pds-primary-action,
    .pds-submit-button,
    .pds-back-button {
        border-radius: 0.95rem !important;
        box-shadow: 0 12px 24px rgba(0, 44, 118, 0.14);
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
@section('content')
    <!-- Main Content -->
    <main class="pds-flow-page {{ $simple ? 'w-full max-w-none' : 'max-w-7xl mx-auto' }} -mt-6 sm:-mt-8 px-4 sm:px-6 lg:px-8 pt-0 pb-8">
        @php
    $c3RouteParams = ['go_to' => 'display_c4'];
    if (request()->query('simple')) {
        $c3RouteParams['simple'] = 1;
    }
@endphp
<form id="learning-form" class="space-y-8" action="{{ route('submit_c3', $c3RouteParams) }}" method="POST">
            @csrf
            @if(request()->boolean('simple'))
                <input type="hidden" name="simple" value="1">
            @endif
            <div class="pds-flow-banner">
                <div class="pds-flow-banner-title">
                    <span class="material-icons">volunteer_activism</span>
                    <strong>VOLUNTARY WORK, TRAININGS AND OTHER INFORMATION</strong>
                </div>
                <div class="pds-flow-banner-meta">
                    <a href="#voluntary-work-section" class="pds-flow-banner-chip">
                        <span class="material-icons text-sm">volunteer_activism</span>
                        Voluntary work
                    </a>
                    <a href="#learning-development-section" class="pds-flow-banner-chip">
                        <span class="material-icons text-sm">school</span>
                        Learning and development
                    </a>
                    <a href="#other-information-section" class="pds-flow-banner-chip">
                        <span class="material-icons text-sm">info</span>
                        Other information
                    </a>
                </div>
            </div>

            <!-- Voluntary Work Section -->
            <section id="voluntary-work-section" class="pds-flow-section bg-white rounded-2xl shadow-xl p-4 sm:p-8 animate-slide-in">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
                    <div class="pds-section-title">
                        <span
                            class="material-icons pds-section-icon text-blue-600 text-2xl sm:text-3xl mt-1 sm:mt-0">volunteer_activism</span>
                        <h2 class="text-lg sm:text-2xl font-bold text-gray-900 leading-tight">VI. VOLUNTARY WORK OR
                            INVOLVEMENT IN CIVIC /<br class="hidden sm:block">NON-GOVERNMENT / PEOPLE / VOLUNTARY ORGANIZATION/S
                        </h2>
                    </div>
                    <button type="button" id="add-voluntary-btn"
                        class="pds-primary-action hidden w-full sm:w-auto flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 text-sm sm:text-base">
                            <span class="material-icons mr-2 text-sm sm:text-base">add_circle</span>
                        Add Voluntary Work
                    </button>
                </div>

                <p class="text-gray-600 mb-6 text-xs sm:text-sm">
                    29. Include involvement in civic/non-government/people/voluntary organizations.
                </p>

                <input type="hidden" name="voluntary_work_count" value="0">
                <!-- Voluntary Work Entries Container -->
                <div id="voluntary-container" class="space-y-4">
                    <!-- Entries will be added dynamically -->
                </div>

                <!-- Empty State -->
                <div id="voluntary-empty" class="pds-empty-state text-center py-8 sm:py-12 bg-gray-50 rounded-lg">
                    <span class="material-icons text-4xl sm:text-6xl text-gray-300 mb-4">volunteer_activism</span>
                    <p class="text-gray-500 mb-4 text-sm sm:text-base">No voluntary work entries yet.</p>
                    <button type="button"
                        class="add-voluntary-trigger px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 text-sm sm:text-base">
                        <span class="material-icons mr-2 text-sm sm:text-base">add</span>
                        Add Your First Voluntary Work
                    </button>
                </div>
            </section> <!-- END Voluntary Work Section -->

            <!-- Learning and Development Section -->
            <section id="learning-development-section" class="pds-flow-section bg-white rounded-2xl shadow-xl p-4 sm:p-8 animate-slide-in">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
                    <div class="pds-section-title">
                        <span class="material-icons pds-section-icon text-blue-600 text-2xl sm:text-3xl mt-1 sm:mt-0">school</span>
                        <h2 class="text-lg sm:text-2xl font-bold text-gray-900 leading-tight">VII. LEARNING AND DEVELOPMENT
                            (L&D) INTERVENTIONS /<br class="hidden sm:block">TRAINING PROGRAMS ATTENDED</h2>
                    </div>
                    <button type="button" id="add-learning-btn"
                        class="pds-primary-action hidden w-full sm:w-auto flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 text-sm sm:text-base">
                        <span class="material-icons mr-2 text-sm sm:text-base">add_circle</span>
                        Add Training
                    </button>
                </div>

                <p class="text-gray-600 mb-6 text-xs sm:text-sm">
                    30. List down all learning and development interventions you have attended. Start from the most recent.
                </p>

                <input type="hidden" name="learning_entry_count" value="0">
                <!-- Learning Entries Container -->
                <div id="learning-container" class="space-y-4">
                    <!-- Entries will be added dynamically -->
                </div>

                <!-- Empty State -->
                <div id="learning-empty" class="pds-empty-state text-center py-8 sm:py-12 bg-gray-50 rounded-lg">
                    <span class="material-icons text-4xl sm:text-6xl text-gray-300 mb-4">school</span>
                    <p class="text-gray-500 mb-4 text-sm sm:text-base">No learning and development entries yet.</p>
                    <button type="button"
                        class="add-learning-trigger px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 text-sm sm:text-base">
                        <span class="material-icons mr-2 text-sm sm:text-base">add</span>
                        Add Your First Training
                    </button>
                </div> <!-- End Empty State -->

            </section> <!-- End Learning and Development Section-->

            <!-- Other Information Section -->
            <section id="other-information-section" class="pds-flow-section bg-white rounded-2xl shadow-xl p-4 sm:p-8 animate-slide-in">
                <div class="flex items-center justify-between mb-6">
                    <div class="pds-section-title">
                        <span class="material-icons pds-section-icon text-blue-600 text-2xl sm:text-3xl">info</span>
                        <h2 class="text-lg sm:text-2xl font-bold text-gray-900">VIII. OTHER INFORMATION</h2>
                    </div>
                </div>

                <div class="space-y-6 sm:space-y-8">
                    <!-- Special Skills and Hobbies -->
                    <div class="pds-subsection">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-4 flex items-center">
                            <span class="material-icons text-sm mr-2 text-blue-500">sports_esports</span>
                            31. SPECIAL SKILLS AND HOBBIES
                        </h3>
                        <div id="skills-container" class="space-y-3">
                            <div class="flex flex-col sm:flex-row gap-3">
                                <input type="text" name="skills[]" placeholder="Enter special skill or hobby"
                                    class="flex-1 px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm sm:text-base">
                                <button type="button"
                                    class="add-skill px-4 py-2 sm:py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors duration-200 flex items-center justify-center">
                                    <span class="material-icons text-sm sm:text-base">add</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Non-Academic Distinctions -->
                    <div class="pds-subsection">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-4 flex items-center">
                            <span class="material-icons text-sm mr-2 text-blue-500">emoji_events</span>
                            32. NON-ACADEMIC DISTINCTIONS / RECOGNITION
                        </h3>
                        <div id="distinctions-container" class="space-y-3">
                            <div class="flex flex-col sm:flex-row gap-3">
                                <input type="text" name="distinctions[]"
                                    placeholder="Enter non-academic distinction or recognition (Write in full)"
                                    class="flex-1 px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm sm:text-base">
                                <button type="button"
                                    class="add-distinction px-4 py-2 sm:py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors duration-200 flex items-center justify-center">
                                    <span class="material-icons text-sm sm:text-base">add</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Membership in Organizations -->
                    <div class="pds-subsection">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-4 flex items-center">
                            <span class="material-icons text-sm mr-2 text-blue-500">groups</span>
                            33. MEMBERSHIP IN ASSOCIATION/ORGANIZATION (Write in full)
                        </h3>
                        <div id="organizations-container" class="space-y-3">
                            <div class="flex flex-col sm:flex-row gap-3">
                                <input type="text" name="organizations[]"
                                    placeholder="Enter organization name (Write in full)"
                                    class="flex-1 px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm sm:text-base">
                                <button type="button"
                                    class="add-organization px-4 py-2 sm:py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors duration-200 flex items-center justify-center">
                                    <span class="material-icons text-sm sm:text-base">add</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section> <!-- END Other Information Section -->

            <!-- Navigation -->
            <div class="pds-submit-bar flex flex-col sm:flex-row justify-between items-center mt-8 gap-4">
                <button type="button" onclick="window.location.href='{{ route('display_c2', ['simple' => 1]) }}'"
                    class="pds-back-button use-loader w-full sm:w-auto px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors duration-200 flex items-center justify-center">
                    <span class="material-icons mr-2">arrow_back</span>
                    Previous
                </button>
                <button type="submit"
                    class="pds-submit-button w-full sm:w-auto px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors duration-200 flex items-center justify-center">
                    Save
                    <span class="material-icons ml-2">arrow_forward</span>
                </button>
            </div> <!-- End Navigation -->
        </form>

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

    </main>
    <footer class="pds-warning-footer mt-8 sm:mt-12 text-center text-xs sm:text-sm text-gray-600 px-4 py-4">
        <p class="mb-2">
            <strong>WARNING:</strong> Any misrepresentation made in the Personal Data Sheet and the Work Experience Sheet
            shall cause the filing of administrative/criminal case/s against the person concerned.
        </p>
        <p>CS FORM 212 (Revised 2025), Page 3 of 4.</p>
    </footer>
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

<script>
    let entryCount_learning = 0; // TODO add in voluntary
    let entryCount_voluntary = 0; // TODO add in voluntary
    document.addEventListener('DOMContentLoaded', function () {

        // Initialize containers
        const form = document.getElementById('learning-form');
        const learningContainer = document.getElementById('learning-container');
        const voluntaryContainer = document.getElementById('voluntary-container');
        const learningEmpty = document.getElementById('learning-empty');
        const voluntaryEmpty = document.getElementById('voluntary-empty');

        const bindClick = (selector, callback) => {
            const element = document.querySelector(selector);
            if (!element) return;
            element.addEventListener('click', callback);
        };

        // Add Learning Event Listeners
        bindClick('#add-learning-btn', addLearningEntry);
        bindClick('.add-learning-trigger', addLearningEntry);

        // Add Voluntary Work Event Listeners
        bindClick('#add-voluntary-btn', addVoluntaryEntry);
        bindClick('.add-voluntary-trigger', addVoluntaryEntry);

        // Add Other Information Event Listeners
        bindClick('.add-skill', function () {
            addSkillEntry(''); // Add a new empty input
        });

        bindClick('.add-distinction', function () {
            addDistinctionEntry(''); // Add a new empty input
        });

        bindClick('.add-organization', function () {
            addOrganizationEntry(''); // Add a new empty input
        });

        // ==================================================================================================================================
        // ADD DATA FOR OTHER INFORMATION FIELD from the passed data in PDSController
        let data_otherInfo = {!! json_encode($data_otherInfo) !!};
        if (!data_otherInfo || typeof data_otherInfo !== 'object') {
            data_otherInfo = {};
        }
        // -----------------------------------------------------------------------------------------------------------------------------------
        // ADD SKILL FUNCTION (Add Other Information)
        if (Array.isArray(data_otherInfo.skill) && data_otherInfo.skill.length > 0) { // Load entries from session if available
            data_otherInfo.skill.forEach(entry => {
                if (entry) { // skips null, undefined, 0, "", false
                    addSkillEntry(entry);
                }
            });
        }
        function addSkillEntry(value) {
            addField('skills-container', 'skills[]', 'Enter special skill or hobby', value);
        } // END ADD SKILL

        // -----------------------------------------------------------------------------------------------------------------------------------
        // ADD DISTICTION FUNCTION (Add Other Information)
        if (Array.isArray(data_otherInfo.distinction) && data_otherInfo.distinction.length > 0) { // Load entries from session if available
            data_otherInfo.distinction.forEach(entry => {
                if (entry) { // skips null, undefined, 0, "", false
                    addDistinctionEntry(entry);
                }
            });
        }

        function addDistinctionEntry(value) {
            addField('distinctions-container', 'distinctions[]', 'Enter non-academic distinction or recognition', value);
        } // END ADD DISTICTION
        // -----------------------------------------------------------------------------------------------------------------------------------

        // ADD ORGANIZATION FUNCTION (Add Other Information)
        if (Array.isArray(data_otherInfo.organization) && data_otherInfo.organization.length > 0) { // Load entries from session if available
            data_otherInfo.organization.forEach(entry => {
                if (entry) { // skips null, undefined, 0, "", false
                    addOrganizationEntry(entry);
                }
            });
        }

        function addOrganizationEntry(value) {
            addField('organizations-container', 'organizations[]', 'Enter organization name', value);
        } // END ADD ORGANIZATION
        // END ADD DATA FOR OTHER INFORMATION
        // ==================================================================================================================================

        // Remove entry functionality
        document.addEventListener('click', function (e) {
            const removeEntryButton = e.target.closest('.remove-entry');
            if (removeEntryButton) {
                const entry = removeEntryButton.closest('.entry-card');
                if (!entry) return;
                entry.remove();
                updateEntryCount();
                reindexEntries_learning();
                reindexEntries_voluntary();
                checkEmptyStates();
                return;
            }

            const removeFieldButton = e.target.closest('.remove-field');
            if (removeFieldButton) {
                const field = removeFieldButton.closest('.other-info-row');
                if (!field) return;
                field.remove();
            }
        });

        // TODO: add too in voluntary works [CHECKED]
        function updateEntryCount() {
            entryCount_learning = learningContainer.querySelectorAll('.entry-card').length;
            entryCount_voluntary = voluntaryContainer.querySelectorAll('.entry-card').length;
            updateHiddenEntryCount_learning();
            updateHiddenEntryCount_voluntary();
        }

        // TODO: add too in voluntary works [CHECKED]
        function updateHiddenEntryCount_learning() {
            const input = document.querySelector('input[name="learning_entry_count"]');
            if (input) input.value = entryCount_learning;
        }
        function updateHiddenEntryCount_voluntary() {
            const input = document.querySelector('input[name="voluntary_work_count"]');
            if (input) input.value = entryCount_voluntary;
        }

        // TODO: add too in voluntary works [CHECKED]
        function reindexEntries_learning() {
            //const entries = document.querySelectorAll('.entry-card'); NEW TODO
            const entries = learningContainer.querySelectorAll('.entry-card');
            entries.forEach((entry, index) => {
                const newIndex = index + 1;

                // Update headings
                const title = entry.querySelector('.training-title'); // TODO: add in voluntary[CHECKED]
                if (title) title.textContent = `Training ${newIndex}`;

                // Rename all inputs
                entry.querySelectorAll('input, select').forEach(input => {
                    const name = input.getAttribute('name');
                    if (name && name.startsWith('learning_')) {
                        const newName = name.replace(/\d+$/, newIndex);
                        input.setAttribute('name', newName);
                    }
                });
            });

            updateHiddenEntryCount_learning();
        }
        function reindexEntries_voluntary() {
            //const entries = document.querySelectorAll('.entry-card'); NEW TODO
            const entries = voluntaryContainer.querySelectorAll('.entry-card');
            // DELETE UPPER CODE
            entries.forEach((entry, index) => {
                const newIndex = index + 1;

                // Update headings
                const title = entry.querySelector('.voluntary-title');
                if (title) title.textContent = `Voluntary Work ${newIndex}`;

                // Rename all inputs
                entry.querySelectorAll('input, select').forEach(input => {
                    const name = input.getAttribute('name');
                    if (name && name.startsWith('voluntary_')) {
                        const newName = name.replace(/\d+$/, newIndex);
                        input.setAttribute('name', newName);
                    }
                });
            });

            updateHiddenEntryCount_voluntary();
        }

        function parseDateInput(value) {
            if (!value) return null;
            const parsed = new Date(`${value}T00:00:00`);
            return Number.isNaN(parsed.getTime()) ? null : parsed;
        }

        function formatDateInputValue(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function bindDateRangeValidation(entryEl, prefix) {
            if (!entryEl) return;
            if (entryEl.dataset.dateValidationBound === '1') return;

            const fromInput = entryEl.querySelector(`input[name^="${prefix}_from_"]`);
            const toInput = entryEl.querySelector(`input[name^="${prefix}_to_"]`);
            if (!fromInput || !toInput) return;

            const setErrorState = (input, message = '') => {
                input.setCustomValidity(message);
                input.classList.toggle('border-red-500', message !== '');
                input.classList.toggle('focus:border-red-500', message !== '');
            };

            const validate = (showMessage = false) => {
                const fromVal = (fromInput.value || '').trim();
                const toVal = (toInput.value || '').trim();
                const fromDate = parseDateInput(fromVal);

                if (fromDate) {
                    // No minimum date restriction (same day allowed)
                    toInput.removeAttribute('min');
                } else {
                    toInput.removeAttribute('min');
                }

                setErrorState(fromInput, '');
                setErrorState(toInput, '');

                if (!fromVal || !toVal) {
                    return true;
                }

                const toDate = parseDateInput(toVal);
                if (!fromDate || !toDate) {
                    return true;
                }

                // TO date cannot be before FROM date (same day is allowed)
                if (toDate.getTime() < fromDate.getTime()) {
                    setErrorState(fromInput, 'The "From" date must not be later than the "To" date.');
                    setErrorState(toInput, 'The "To" date must not be earlier than the "From" date.');
                    if (showMessage) {
                        toInput.reportValidity();
                    }
                    return false;
                }

                return true;
            };

            const validateOnBlur = () => validate(true);
            const validateSilently = () => validate(false);

            fromInput.addEventListener('blur', validateOnBlur);
            toInput.addEventListener('blur', validateOnBlur);
            fromInput.addEventListener('change', validateSilently);
            toInput.addEventListener('change', validateSilently);

            entryEl.__validateDateRange = validate;
            entryEl.dataset.dateValidationBound = '1';
            validate(false);
        }

        function getLearningEntryFields(entry) {
            return {
                title: entry.querySelector('input[name^="learning_title_"]'),
                type: entry.querySelector('select[name^="learning_type_"]'),
                from: entry.querySelector('input[name^="learning_from_"]'),
                to: entry.querySelector('input[name^="learning_to_"]'),
                hours: entry.querySelector('input[name^="learning_hours_"]'),
                conducted: entry.querySelector('input[name^="learning_conducted_"]'),
            };
        }

        function buildLearningSummary(entry) {
            const fields = getLearningEntryFields(entry);
            const summaryParts = [];

            const title = (fields.title?.value || '').trim();
            const type = (fields.type?.value || '').trim();
            const from = (fields.from?.value || '').trim();
            const to = (fields.to?.value || '').trim();
            const hours = (fields.hours?.value || '').trim();
            const conducted = (fields.conducted?.value || '').trim();

            if (title) summaryParts.push(title);
            if (type) summaryParts.push(type);
            if (from || to) summaryParts.push([from || 'No start date', to || 'No end date'].join(' to '));
            if (hours) summaryParts.push(`${hours} hour${hours === '1' ? '' : 's'}`);
            if (conducted) summaryParts.push(conducted);

            return summaryParts.join(' | ') || 'Fill out this training entry.';
        }

        function isLearningEntryComplete(entry) {
            const fields = getLearningEntryFields(entry);
            return Object.values(fields).every((field) => field && String(field.value || '').trim() !== '');
        }

        function setLearningEntryCollapsed(entry, collapsed) {
            const body = entry.querySelector('.entry-body');
            const summary = entry.querySelector('.learning-summary');
            const toggle = entry.querySelector('.entry-collapse-toggle');
            const icon = entry.querySelector('.collapse-icon');
            if (!body || !summary || !toggle || !icon) return;

            summary.textContent = buildLearningSummary(entry);
            entry.classList.toggle('is-collapsed', collapsed);
            body.classList.toggle('hidden', collapsed);
            toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            icon.textContent = collapsed ? 'expand_more' : 'expand_less';
        }

        function bindLearningEntryInteractions(entry, collapseIfComplete = false) {
            if (!entry || entry.dataset.learningBound === '1') return;

            const toggle = entry.querySelector('.entry-collapse-toggle');
            const body = entry.querySelector('.entry-body');
            if (!toggle || !body) return;

            const refresh = (shouldCollapse = false) => {
                const collapse = shouldCollapse && isLearningEntryComplete(entry);
                setLearningEntryCollapsed(entry, collapse);
            };

            toggle.addEventListener('click', function () {
                const collapsed = entry.classList.contains('is-collapsed');
                setLearningEntryCollapsed(entry, !collapsed);
            });

            entry.querySelectorAll('input, select').forEach((field) => {
                field.addEventListener('input', () => setLearningEntryCollapsed(entry, false));
                field.addEventListener('change', () => refresh(false));
            });

            body.addEventListener('focusout', function (event) {
                if (body.contains(event.relatedTarget)) return;
                refresh(true);
            });

            entry.dataset.learningBound = '1';
            refresh(collapseIfComplete);
        }

        const learningData = @json($data_learning);
        function addLearningEntry(data = null) {
            const rowCount = learningContainer.children.length + 1;
            //const entryCount = learningContainer.querySelectorAll('.entry-card').length;
            // the <input..$rowCount is for the C3Controller for monitoring the rowCount :: FOR DATABASE
            const entryHtml = `
<div class="entry-card bg-gray-50 rounded-lg p-4 sm:p-6 card-hover animate-fade-in">
    <div class="flex items-start gap-3 mb-4">
        <button type="button" class="entry-collapse-toggle flex-1">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h4 class="training-title text-base sm:text-lg font-medium text-gray-700">Training ${entryCount_learning + 1}</h4>
                    <p class="learning-summary mt-1 text-xs sm:text-sm text-gray-500">Fill out this training entry.</p>
                </div>
                <span class="collapse-icon material-icons text-gray-400 text-lg sm:text-xl">expand_less</span>
            </div>
        </button>
        <button type="button" class="remove-entry text-red-500 hover:text-red-700 transition-colors duration-200 p-1">
            <span class="material-icons text-lg sm:text-xl">close</span>
        </button>
    </div>
    <div class="entry-body grid grid-cols-1 gap-4">
        <!-- Training Title - Full width -->
        <div class="relative">
            <input type="text" name="learning_title_${entryCount_learning + 1}" value="${data?.learning_title ?? ''}"
            placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
            <label class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-xs">Title of Learning and Development Interventions/Training Programs (Write in full)</label>
        </div>
        
        <!-- Type of L&D - Full width on mobile -->
        <div class="relative">
            <select name="learning_type_${entryCount_learning + 1}" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm sm:text-base">
                <option value="">Select Type</option>
                <option value="Managerial" ${data?.learning_type === 'Managerial' ? 'selected' : ''}>Managerial</option>
                <option value="Supervisory" ${data?.learning_type === 'Supervisory' ? 'selected' : ''}>Supervisory</option>
                <option value="Technical" ${data?.learning_type === 'Technical' ? 'selected' : ''}>Technical</option>
                <option value="Others" ${data?.learning_type === 'Others' ? 'selected' : ''}>Others</option>
            </select>
            <label class="absolute -top-2 left-2 sm:left-3 bg-gray-50 px-1 text-xs sm:text-sm text-gray-600">Type of L&D</label>
        </div>
        
        <!-- Date Range - Side by side on mobile -->
        <div class="grid grid-cols-2 gap-3 sm:gap-4">
            <div class="relative">
                <input type="date" name="learning_from_${entryCount_learning + 1}" value="${data?.learning_from ?? ''}"
                class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm sm:text-base">
                <label class="absolute -top-2 left-2 sm:left-3 bg-gray-50 px-1 text-xs sm:text-sm text-gray-600">From</label>
            </div>
            <div class="relative">
                <input type="date" name="learning_to_${entryCount_learning + 1}" value="${data?.learning_to ?? ''}"
                class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm sm:text-base">
                <label class="absolute -top-2 left-2 sm:left-3 bg-gray-50 px-1 text-xs sm:text-sm text-gray-600">To</label>
            </div>
        </div>
        
        <!-- Number of Hours - Full width -->
        <div class="relative">
            <input type="number" name="learning_hours_${entryCount_learning + 1}" value="${data?.learning_hours ?? ''}" min="1" max="32767" step="1"
            placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
            <label class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-xs sm:text-sm">Number of Hours</label>
        </div>
        
        <!-- Conducted/Sponsored By - Full width -->
        <div class="relative">
            <input type="text" name="learning_conducted_${entryCount_learning + 1}" value="${data?.learning_conducted ?? ''}"
            placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
            <label class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-xs sm:text-sm">Conducted/Sponsored By (Write in full)</label>
        </div>
    </div>
</div>
                `;
            learningContainer.insertAdjacentHTML('beforeend', entryHtml);
            const newEntry = learningContainer.lastElementChild;
            bindDateRangeValidation(newEntry, 'learning');
            bindLearningEntryInteractions(newEntry, !!data);
            updateEntryCount();
            checkEmptyStates();
        }
        // Load entries from session if available
        if (Array.isArray(learningData) && learningData.length > 0) {
            learningData.forEach(entry => addLearningEntry(entry));
        }

        const voluntaryData = @json($data_voluntary);
        function addVoluntaryEntry(data = null) {
            const rowCount = voluntaryContainer.children.length + 1;
            //const entryCount = voluntaryContainer.querySelectorAll('.entry-card').length;
            // the <input..$rowCount is for the C3Controller for monitoring the rowCount :: FOR DATABASE
            const entryHtml = `

                   <div class="entry-card bg-gray-50 rounded-lg p-4 sm:p-6 card-hover animate-fade-in">
    <div class="flex justify-between items-start mb-4">
        <h4 class="voluntary-title text-base sm:text-lg font-medium text-gray-700">Voluntary Work ${entryCount_voluntary + 1}</h4>
        <button type="button" class="remove-entry text-red-500 hover:text-red-700 transition-colors duration-200 p-1">
            <span class="material-icons text-lg sm:text-xl">close</span>
        </button>
    </div>
    <div class="grid grid-cols-1 gap-4">
        <!-- Organization Name - Full width on mobile -->
        <div class="relative">
            <input type="text" name="voluntary_org_${entryCount_voluntary + 1}" value="${data?.voluntary_org ?? ''}"
            placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
            <label class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-xs sm:text-sm">Name & Address of Organization (Write in full)</label>
        </div>
        
        <!-- Date Range - Side by side on mobile -->
        <div class="grid grid-cols-2 gap-3 sm:gap-4">
            <div class="relative">
                <input type="date" name="voluntary_from_${entryCount_voluntary + 1}" value="${data?.voluntary_from ?? ''}"
                class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm sm:text-base">
                <label class="absolute -top-2 left-2 sm:left-3 bg-gray-50 px-1 text-xs sm:text-sm text-gray-600">From</label>
            </div>
            <div class="relative">
                <input type="date" name="voluntary_to_${entryCount_voluntary + 1}" value="${data?.voluntary_to ?? ''}"
                class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm sm:text-base">
                <label class="absolute -top-2 left-2 sm:left-3 bg-gray-50 px-1 text-xs sm:text-sm text-gray-600">To</label>
            </div>
        </div>
        
        <!-- Number of Hours - Full width on mobile -->
        <div class="relative">
            <input type="number" name="voluntary_hours_${entryCount_voluntary + 1}" value="${data?.voluntary_hours ?? ''}" min="1" max="32767" step="1"
            placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
            <label class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-xs sm:text-sm">Number of Hours</label>
        </div>
        
        <!-- Position/Nature of Work - Full width on mobile -->
        <div class="relative">
            <input type="text" name="voluntary_position_${entryCount_voluntary + 1}" value="${data?.voluntary_position ?? ''}"
            placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
            <label class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-xs sm:text-sm">Position/Nature of Work</label>
        </div>
    </div>
</div>
                `;
            voluntaryContainer.insertAdjacentHTML('beforeend', entryHtml);
            const newEntry = voluntaryContainer.lastElementChild;
            bindDateRangeValidation(newEntry, 'voluntary');
            updateEntryCount();
            checkEmptyStates();
        }
        // Load entries from session if available
        if (Array.isArray(voluntaryData) && voluntaryData.length > 0) {
            voluntaryData.forEach(entry => addVoluntaryEntry(entry));
        }

        learningContainer.querySelectorAll('.entry-card').forEach(entry => bindDateRangeValidation(entry, 'learning'));
        learningContainer.querySelectorAll('.entry-card').forEach(entry => bindLearningEntryInteractions(entry, true));
        voluntaryContainer.querySelectorAll('.entry-card').forEach(entry => bindDateRangeValidation(entry, 'voluntary'));

        if (form) {
            form.addEventListener('submit', function (event) {
                const allEntries = [
                    ...learningContainer.querySelectorAll('.entry-card'),
                    ...voluntaryContainer.querySelectorAll('.entry-card'),
                ];
                let firstInvalidInput = null;

                allEntries.forEach((entry) => {
                    const validateFn = entry.__validateDateRange;
                    const isValid = typeof validateFn === 'function' ? validateFn(false) : true;
                    if (!isValid && !firstInvalidInput) {
                        if (entry.querySelector('input[name^="learning_"], select[name^="learning_"]')) {
                            setLearningEntryCollapsed(entry, false);
                        }
                        firstInvalidInput = entry.querySelector(
                            'input[name^="learning_from_"], input[name^="learning_to_"], input[name^="voluntary_from_"], input[name^="voluntary_to_"]'
                        );
                    }
                });

                if (!firstInvalidInput) {
                    return;
                }

                event.preventDefault();
                firstInvalidInput.reportValidity();
                firstInvalidInput.focus();
            });
        }



        function addField(containerId, fieldName, placeholder, value = '') {
            const container = document.getElementById(containerId);
            const fieldHtml = `
                    <div class="other-info-row flex flex-col sm:flex-row gap-3 animate-fade-in">
    <input type="text" name="${fieldName}" placeholder="${placeholder}" required value="${value}" class="flex-1 px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm sm:text-base">
    <button type="button" class="remove-field px-4 py-2 sm:py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors duration-200 flex items-center justify-center">
        <span class="material-icons text-sm sm:text-base">remove</span>
    </button>
</div>
                `;
            container.insertAdjacentHTML('beforeend', fieldHtml);
        }

        function checkEmptyStates() {
            const addLearningBtn = document.getElementById('add-learning-btn');
            const addVoluntaryBtn = document.getElementById('add-voluntary-btn');

            // Check learning entries
            if (learningContainer.querySelectorAll('.entry-card').length === 0) {
                learningEmpty.style.display = 'block';
                if (addLearningBtn) addLearningBtn.classList.add('hidden');
            } else {
                learningEmpty.style.display = 'none';
                if (addLearningBtn) addLearningBtn.classList.remove('hidden');
            }

            // Check voluntary entries
            if (voluntaryContainer.querySelectorAll('.entry-card').length === 0) {
                voluntaryEmpty.style.display = 'block';
                if (addVoluntaryBtn) addVoluntaryBtn.classList.add('hidden');
            } else {
                voluntaryEmpty.style.display = 'none';
                if (addVoluntaryBtn) addVoluntaryBtn.classList.remove('hidden');
            }
        }
        checkEmptyStates(); // Initial check
    });

    function submit(location) {
        const form = document.querySelector('#learning-form');
        const simpleParam = new URLSearchParams(window.location.search).get('simple');
        const simpleQuery = simpleParam ? `?simple=${simpleParam}` : '';
        form.action = `/pds/submit_c3/${location}${simpleQuery}`;
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

            const DRAG_PADDING = 16;
            const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

            const getViewportBounds = () => {
                const rect = button.getBoundingClientRect();
                const layoutWidth = document.documentElement.clientWidth || window.innerWidth;
                const layoutHeight = document.documentElement.clientHeight || window.innerHeight;
                const viewport = window.visualViewport;
                const viewportLeft = viewport?.offsetLeft ?? 0;
                const viewportTop = viewport?.offsetTop ?? 0;
                const viewportWidth = Math.min(layoutWidth, viewport?.width ?? layoutWidth);
                const viewportHeight = Math.min(layoutHeight, viewport?.height ?? layoutHeight);
                return {
                    minLeft: viewportLeft + DRAG_PADDING,
                    minTop: viewportTop + DRAG_PADDING,
                    maxLeft: Math.max(viewportLeft + DRAG_PADDING, viewportLeft + viewportWidth - rect.width - DRAG_PADDING),
                    maxTop: Math.max(viewportTop + DRAG_PADDING, viewportTop + viewportHeight - rect.height - DRAG_PADDING),
                };
            };

            const applyPosition = (left, top) => {
                button.style.left = `${left}px`;
                button.style.top = `${top}px`;
                button.style.right = 'auto';
                button.style.bottom = 'auto';
            };

            const resetToDefaultPosition = () => {
                button.style.removeProperty('left');
                button.style.removeProperty('top');
                button.style.removeProperty('right');
                button.style.removeProperty('bottom');
                button.style.removeProperty('inset');
            };

            const pinToViewportCorner = () => {
                const bounds = getViewportBounds();
                applyPosition(bounds.maxLeft, bounds.maxTop);
            };

            const syncToViewport = () => {
                const rect = button.getBoundingClientRect();
                const bounds = getViewportBounds();
                applyPosition(
                    clamp(rect.left, bounds.minLeft, bounds.maxLeft),
                    clamp(rect.top, bounds.minTop, bounds.maxTop)
                );
            };

            const scheduleViewportSync = () => {
                window.requestAnimationFrame(() => {
                    window.requestAnimationFrame(() => {
                        pinToViewportCorner();
                        syncToViewport();
                    });
                });
            };

            const initializePosition = () => {
                resetToDefaultPosition();
                scheduleViewportSync();
                window.setTimeout(() => {
                    scheduleViewportSync();
                }, 120);
                window.setTimeout(() => {
                    scheduleViewportSync();
                }, 420);
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
            window.addEventListener('scroll', syncToViewport, { passive: true });
            window.addEventListener('load', initializePosition);
            window.visualViewport?.addEventListener('resize', syncToViewport);
            window.visualViewport?.addEventListener('scroll', syncToViewport);
            window.addEventListener('pageshow', initializePosition);
            initializePosition();
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
        function initC3DraftSupport() {
            const form = document.getElementById('learning-form');
            if (!form) return;

            const LOCAL_DRAFT_KEY = 'dilg-car:pds:c3:local-draft:v1';
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
                    notify('A saved C3 draft was restored from this browser.', 'warning', 4000);
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
            document.addEventListener('DOMContentLoaded', initC3DraftSupport, { once: true });
        } else {
            initC3DraftSupport();
        }
    })();
</script>

@include('partials.loader')
<script>
    (function () {
        function initAutosave() {
        const form = document.getElementById('learning-form');
        if (!form) return;

        const autosaveUrl = @json(route('pds.autosave', ['section' => 'c3']));
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
            if (event.target.closest('#add-learning-btn, #add-voluntary-btn, .remove-entry, .add-skill, .add-distinction, .add-organization, .remove-field')) {
                markDirty();
            }
        });
        form.addEventListener('submit', () => {
            isSubmitting = true;
            window.clearTimeout(autosaveTimer);
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
