@extends('layout.pds_layout')
@section('title', 'Learning and Development')
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
</style>
@section('content')
    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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

            <!-- Voluntary Work Section -->
            <section class="bg-white rounded-2xl shadow-xl p-4 sm:p-8 animate-slide-in">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
                    <div class="flex items-start sm:items-center">
                        <span
                            class="material-icons text-blue-600 mr-3 text-2xl sm:text-3xl mt-1 sm:mt-0">volunteer_activism</span>
                        <h2 class="text-lg sm:text-2xl font-bold text-gray-900 leading-tight">VI. VOLUNTARY WORK OR
                            INVOLVEMENT IN CIVIC /<br class="hidden sm:block">NON-GOVERNMENT / PEOPLE / VOLUNTARY ORGANIZATION/S
                        </h2>
                    </div>
                    <button type="button" id="add-voluntary-btn"
                        class="hidden w-full sm:w-auto flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 text-sm sm:text-base">
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
                <div id="voluntary-empty" class="text-center py-8 sm:py-12 bg-gray-50 rounded-lg">
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
            <section class="bg-white rounded-2xl shadow-xl p-4 sm:p-8 animate-slide-in">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
                    <div class="flex items-start sm:items-center">
                        <span class="material-icons text-blue-600 mr-3 text-2xl sm:text-3xl mt-1 sm:mt-0">school</span>
                        <h2 class="text-lg sm:text-2xl font-bold text-gray-900 leading-tight">VII. LEARNING AND DEVELOPMENT
                            (L&D) INTERVENTIONS /<br class="hidden sm:block">TRAINING PROGRAMS ATTENDED</h2>
                    </div>
                    <button type="button" id="add-learning-btn"
                        class="hidden w-full sm:w-auto flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 text-sm sm:text-base">
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
                <div id="learning-empty" class="text-center py-8 sm:py-12 bg-gray-50 rounded-lg">
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
            <section class="bg-white rounded-2xl shadow-xl p-4 sm:p-8 animate-slide-in">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <span class="material-icons text-blue-600 mr-3 text-2xl sm:text-3xl">info</span>
                        <h2 class="text-lg sm:text-2xl font-bold text-gray-900">VIII. OTHER INFORMATION</h2>
                    </div>
                </div>

                <div class="space-y-6 sm:space-y-8">
                    <!-- Special Skills and Hobbies -->
                    <div>
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
                    <div>
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
                    <div>
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
            <div class="flex flex-col sm:flex-row justify-between items-center mt-8 gap-4">
                <button type="button" onclick="window.location.href='{{ route('display_c2', ['simple' => 1]) }}'"
                    class="use-loader w-full sm:w-auto px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors duration-200 flex items-center justify-center">
                    <span class="material-icons mr-2">arrow_back</span>
                    Previous
                </button>
                <button type="submit"
                    class="w-full sm:w-auto px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors duration-200 flex items-center justify-center">
                    Save
                    <span class="material-icons ml-2">arrow_forward</span>
                </button>
            </div> <!-- End Navigation -->
        </form>

    </main>
    <footer class="mt-8 sm:mt-12 text-center text-xs sm:text-sm text-gray-600 px-4">
        <p class="mb-2">
            <strong>WARNING:</strong> Any misrepresentation made in the Personal Data Sheet and the Work Experience Sheet
            shall cause the filing of administrative/criminal case/s against the person concerned.
        </p>
        <p>CS FORM 212 (Revised 2025), Page 3 of 4.</p>
    </footer>
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

        const learningData = @json($data_learning);
        function addLearningEntry(data = null) {
            const rowCount = learningContainer.children.length + 1;
            //const entryCount = learningContainer.querySelectorAll('.entry-card').length;
            // the <input..$rowCount is for the C3Controller for monitoring the rowCount :: FOR DATABASE
            const entryHtml = `
<div class="entry-card bg-gray-50 rounded-lg p-4 sm:p-6 card-hover animate-fade-in">
    <div class="flex justify-between items-start mb-4">
        <h4 class="training-title text-base sm:text-lg font-medium text-gray-700">Training ${entryCount_learning + 1}</h4>
        <button type="button" class="remove-entry text-red-500 hover:text-red-700 transition-colors duration-200 p-1">
            <span class="material-icons text-lg sm:text-xl">close</span>
        </button>
    </div>
    <div class="grid grid-cols-1 gap-4">
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

@include('partials.loader')
<script>
    (function () {
        function initAutosave() {
        const form = document.getElementById('learning-form');
        if (!form) return;

        const autosaveUrl = @json(route('pds.autosave', ['section' => 'c3']));
        const AUTOSAVE_INTERVAL_MS = 30000;
        let isDirty = false;
        let isSubmitting = false;
        let inFlight = false;
        let queued = false;

        const markDirty = () => { isDirty = true; };
        form.addEventListener('input', markDirty);
        form.addEventListener('change', markDirty);
        form.addEventListener('click', (event) => {
            if (event.target.closest('#add-learning-btn, #add-voluntary-btn, .remove-entry, .add-skill, .add-distinction, .add-organization, .remove-field')) {
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
