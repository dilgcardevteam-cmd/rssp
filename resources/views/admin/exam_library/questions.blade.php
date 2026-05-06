@extends('layout.admin')
@section('title', 'Manage Questions')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        .font-montserrat {
            font-family: 'Montserrat', sans-serif;
        }
    </style>
@endpush

@section('content')

    <main class="w-full min-h-screen flex flex-col space-y-6 p-6 font-montserrat">
        <!-- Header -->
        <section class="flex items-center justify-between border-b border-[#0D2B70] pb-4">
            <div class="flex items-center gap-4">
                <button aria-label="Back" onclick="window.location.href='{{ route('admin.exam_library') }}'" class="group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#0D2B70] hover:opacity-80 transition"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <div>
                    <h1 class="text-4xl font-bold text-[#0D2B70]">{{ $series->series_name }}</h1>
                    @if($series->description)
                        <p class="text-gray-600 mt-2">{{ $series->description }}</p>
                    @endif
                </div>
            </div>
            <button onclick="openCreateQuestionModal()"
                class="bg-[#002C76] hover:bg-blue-900 text-white font-bold py-2 px-6 rounded-lg inline-flex items-center gap-2 transition-all duration-200 hover:scale-105">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Question
            </button>
        </section>

        <!-- Success/Error Messages -->
        <div id="alert-container" class="hidden">
            <div id="alert-message"
                class="px-4 py-3 rounded-lg shadow text-sm font-semibold flex items-center justify-between" role="alert">
                <span id="alert-text"></span>
                <button onclick="closeAlert()" class="font-bold text-lg hover:opacity-70">&times;</button>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="flex gap-4 items-center flex-wrap">
            <div class="relative flex-1 max-w-md">
                <input id="searchInput" type="search" placeholder="Search questions..."
                    class="w-full pl-10 pr-4 py-2 rounded-lg border border-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70]" />
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="w-5 h-5 text-[#7D93B3] absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                </svg>
            </div>

            <select id="typeFilter"
                class="h-10 cursor-pointer px-4 rounded-md border border-[#0D2B70] text-[#0D2B70] font-semibold bg-white focus:outline-none focus:ring-2 focus:ring-[#0D2B70]">
                <option value="">All Types</option>
                <option value="multiple_choice">Multiple Choice</option>
                <option value="essay">Essay</option>
            </select>
        </div>

        <!-- Questions List -->
        <div id="questions-container" class="space-y-6">
            <!-- Questions will be loaded here -->
        </div>

        <!-- Question Modal -->
        <div id="questionModal"
            class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
            <div class="bg-white rounded-xl shadow-2xl p-8 max-w-4xl w-full mx-4 my-8 max-h-[90vh] overflow-y-auto">
                <h2 id="modalTitle" class="text-2xl font-bold text-[#0D2B70] mb-6">Add Question</h2>

                <form id="questionForm" onsubmit="saveQuestion(event)">
                    <input type="hidden" id="questionId" value="">

                    <!-- Question Text -->
                    <div class="mb-4">
                        <label for="questionText" class="block text-sm font-semibold text-gray-700 mb-2">Question <span class="text-red-500">*</span></label>
                        <input type="text" id="questionText" required
                            class="w-full px-4 py-2 border border-blue-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0D2B70]"
                            placeholder="Enter your question here...">
                    </div>

                    <!-- Question Type -->
                    <div class="mb-4">
                        <label for="questionType" class="block text-sm font-semibold text-gray-700 mb-2">Question Type
                        <span class="text-red-500">*</span></label>
                        <select id="questionType" required onchange="handleTypeChange()"
                            class="w-full h-10 cursor-pointer px-4 rounded-md border border-[#0D2B70] text-[#0D2B70] font-semibold bg-white focus:outline-none focus:ring-2 focus:ring-[#0D2B70]">
                            <option value="multiple_choice">Multiple Choice</option>
                            <option value="essay">Essay</option>
                        </select>
                    </div>

                    <!-- Choices (for multiple choice and true/false) -->
                    <div id="choicesContainer" class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Choices</label>
                        <div id="choicesList" class="space-y-2">
                            <!-- Choices will be added here -->
                        </div>
                        <div class="flex items-center gap-3 mt-2" id="addChoiceContainer">
                            <div class="w-5 h-5 rounded-full border-2 border-gray-300 flex-shrink-0"></div>
                            <button type="button" onclick="addChoice()" id="addChoiceBtn"
                                class="text-sm text-gray-500 hover:text-[#0D2B70] font-medium hover:underline">
                                Add option
                            </button>
                        </div>
                        <p class="italic text-sm text-red-500 mt-2" id="choiceTip">
                            Tick the option to declare as answer.
                        </p>
                        <p class="italic text-sm text-red-500 mt-2 hidden" id="choicesError"></p>
                    </div>

                    <!-- Essay Answer Guide -->
                    <div id="essayGuideContainer" class="mb-4 hidden">
                        <label for="essayGuide" class="block text-sm font-semibold text-gray-700 mb-2">Answer Guide
                            (Optional)</label>
                        <textarea id="essayGuide" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0D2B70]"
                            placeholder="Answer here."></textarea>
                    </div>

                    <!-- Essay Max Score -->
                    <div id="essayMaxContainer" class="mb-4 hidden">
                        <label for="essayMaxScore" class="block text-sm font-semibold text-gray-700 mb-2">Max Score <span class="text-red-500">*</span></label>
                        <input type="number" id="essayMaxScore" min="0" placeholder="e.g., 50"
                            class="w-40 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0D2B70]">
                        <p class="text-xs text-gray-500 mt-1">Highest allowed score for this essay question.</p>
                    </div>



                    <div class="flex gap-4 justify-end">
                        <button type="button" onclick="closeQuestionModal()"
                            class="border-2 border-gray-300 hover:bg-gray-50 text-gray-700 font-bold py-2 px-6 rounded-lg transition">
                            Cancel
                        </button>
                        <button type="submit" id="saveQuestionBtn" disabled
                            class="bg-[#002C76] hover:bg-blue-900 text-white font-bold py-2 px-6 rounded-lg transition-all duration-200 hover:scale-105 opacity-60 pointer-events-none">
                            <i class="fa-solid fa-floppy-disk mr-2"></i>
                            Save Question
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @include('partials.loader')
    </main>

    <script>
        const seriesId = {{ $series->id }};
        let allQuestions = [];
        let choiceCount = 0;
        let selectedCorrectAnswer = -1;

        // Load questions on page load
        document.addEventListener('DOMContentLoaded', () => {
            loadQuestions();
            handleTypeChange(); // Initialize form based on default type
            // wire question text validation
            const qText = document.getElementById('questionText');
            if (qText) qText.addEventListener('input', validateForm);
            attachChoiceListeners();
            validateForm();
        });

        // Search and filter
        document.getElementById('searchInput').addEventListener('input', filterQuestions);
        document.getElementById('typeFilter').addEventListener('change', filterQuestions);

        async function loadQuestions() {
            try {
                console.log('Loading questions for series:', seriesId);
                const response = await fetch(`/admin/exam-library/series/${seriesId}/questions?ajax=1`);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                console.log('Questions loaded:', data);
                allQuestions = data;
                renderQuestions(allQuestions);
            } catch (error) {
                console.error('Error loading questions:', error);
                showAlert('Failed to load questions. Please refresh the page.', 'error');
            }
        }

        function filterQuestions() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const type = document.getElementById('typeFilter').value;

            const filtered = allQuestions.filter(q => {
                const matchesSearch = q.question.toLowerCase().includes(search);
                const matchesType = !type || q.question_type === type;
                return matchesSearch && matchesType;
            });

            renderQuestions(filtered);
        }

        function renderQuestions(questions) {
            const container = document.getElementById('questions-container');

            if (questions.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-gray-500 text-xl font-semibold">There are no questions yet.</p>
                        <button type="button" onclick="openCreateQuestionModal()"
                            class="mt-4 bg-[#002C76] hover:bg-blue-900 text-white font-bold py-2 px-6 rounded-lg inline-flex items-center gap-2 transition-all duration-200 hover:scale-105">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Your First Question
                        </button>
                    </div>
                `;
                return;
            }

            container.innerHTML = questions.map((q, index) => `
                <div class="p-6 bg-white rounded-lg shadow border border-gray-200 w-full relative">
                    <!-- Question Header -->
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-1">
                            <p class="text-lg font-semibold text-gray-800 mb-2">${q.question}</p>
                            <div class="flex gap-2 flex-wrap">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold ${getTypeColor(q.question_type)}">
                                    ${formatType(q.question_type)}
                                </span>
                                ${q.exam_usages_count > 0 ? `<span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">Used in ${q.exam_usages_count} exam(s)</span>` : ''}
                            </div>
                        </div>
                    </div>

                    ${q.choices && q.choices.length > 0 ? `
                        <div class="mt-4 space-y-2">
                            ${q.choices.map((choice, idx) => `
                                <div class="flex items-center gap-3">
                                    <div class="w-5 h-5 rounded-full border-2 flex-shrink-0 flex items-center justify-center ${q.correct_answer === choice ? 'border-[#0D2B70] bg-[#0D2B70]' : 'border-gray-400'}">
                                        ${q.correct_answer === choice ? '<div class="w-2 h-2 rounded-full bg-white"></div>' : ''}
                                    </div>
                                    <span class="text-gray-700">${choice}</span>
                                </div>
                            `).join('')}
                        </div>
                        <p class="italic text-sm text-[#0D2B70] mt-2">
                            Correct answer: ${q.correct_answer}
                        </p>
                    ` : ''}

                    ${q.correct_answer && !q.choices ? `
                        <div class="mt-4 pl-4 border-l-2 border-gray-200">
                            <p class="text-sm text-green-600 font-semibold">âœ“ Answer: ${q.correct_answer}</p>
                        </div>
                    ` : ''}

                    <!-- Action Buttons -->
                    <div class="flex justify-end gap-2 mt-4">
                        <button onclick="editQuestion(${q.id})" title="Edit this question"
                            class="text-white font-bold p-3 rounded-lg transition-all duration-200 hover:scale-105 hover:shadow-md relative group">
                            <i class="fa-solid fa-pen-to-square text-blue-600 text-xl"></i>
                            <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1 bg-gray-800 text-white text-xs rounded-md whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                                Edit Question
                            </span>
                        </button>

                        <button onclick="duplicateQuestion(${q.id})" title="Duplicate this question"
                            class="text-white font-bold p-3 rounded-lg transition-all duration-200 hover:scale-105 hover:shadow-md relative group">
                            <i class="fa-solid fa-copy text-[#0D2B70] text-xl"></i>
                            <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1 bg-gray-800 text-white text-xs rounded-md whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                                Duplicate Question
                            </span>
                        </button>

                        <button onclick="deleteQuestion(${q.id})" title="Remove this question"
                            class="text-white font-bold p-3 rounded-lg transition-all duration-200 hover:scale-105 hover:shadow-md relative group">
                            <i class="fa-solid fa-trash text-red-700 text-xl"></i>
                            <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1 bg-gray-800 text-white text-xs rounded-md whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                                Remove Question
                            </span>
                        </button>
                    </div>
                </div>
            `).join('');
        }

        function getTypeColor(type) {
            const colors = {
                'multiple_choice': 'bg-blue-100 text-blue-800',
                'essay': 'bg-yellow-100 text-yellow-800',
            };
            return colors[type] || 'bg-gray-100 text-gray-800';
        }



        function formatType(type) {
            return type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
        }

        function openCreateQuestionModal() {
            document.getElementById('modalTitle').textContent = 'Add Question';
            document.getElementById('questionForm').reset();
            document.getElementById('questionId').value = '';
            choiceCount = 0;
            selectedCorrectAnswer = -1;
            document.getElementById('choicesList').innerHTML = '';
            handleTypeChange();
            document.getElementById('questionModal').classList.remove('hidden');
        }

        function closeQuestionModal() {
            document.getElementById('questionModal').classList.add('hidden');
        }

        function handleTypeChange() {
            const type = document.getElementById('questionType').value;
            const choicesContainer = document.getElementById('choicesContainer');
            const essayGuideContainer = document.getElementById('essayGuideContainer');
            const essayMaxContainer = document.getElementById('essayMaxContainer');
            const addChoiceBtn = document.getElementById('addChoiceContainer');
            const choiceTip = document.getElementById('choiceTip');

            if (type === 'multiple_choice') {
                choicesContainer.classList.remove('hidden');
                essayGuideContainer.classList.add('hidden');
                essayMaxContainer.classList.add('hidden');
                addChoiceBtn.classList.remove('hidden');
                choiceTip.classList.remove('hidden');
                if (choiceCount === 0) {
                    for (let i = 0; i < 4; i++) addChoice();
                }
                // ensure listeners and validation after creating default choices
                attachChoiceListeners();
                validateForm();
            } else if (type === 'essay') {
                choicesContainer.classList.add('hidden');
                essayGuideContainer.classList.remove('hidden');
                essayMaxContainer.classList.remove('hidden');
                // Attach input listener to essay max score to re-validate on change
                const essayMaxEl = document.getElementById('essayMaxScore');
                if (essayMaxEl && !essayMaxEl._hasListener) {
                    essayMaxEl.addEventListener('input', validateForm);
                    essayMaxEl._hasListener = true;
                }
                validateForm();
            }
        }

        function createChoiceElement(value = '', index = choiceCount, readonly = false) {
            return `
                                                    <div class="flex items-center gap-3 group">
                                                        <div onclick="selectCorrectAnswer(${index})" 
                                                            class="w-5 h-5 rounded-full border-2 flex-shrink-0 cursor-pointer transition-all flex items-center justify-center choice-radio"
                                                            data-index="${index}"
                                                            style="border-color: ${selectedCorrectAnswer === index ? '#0D2B70' : '#9CA3AF'}; background-color: ${selectedCorrectAnswer === index ? '#0D2B70' : 'transparent'};">
                                                            <div class="w-2 h-2 rounded-full bg-white" style="display: ${selectedCorrectAnswer === index ? 'block' : 'none'};"></div>
                                                        </div>
                                                        <input type="text" class="choice-input flex-1 border-b border-transparent hover:border-gray-300 focus:border-[#0D2B70] focus:outline-none py-1 px-2 transition-colors" 
                                                            placeholder="Option ${index + 1}" value="${value}" ${readonly ? 'readonly' : ''} data-index="${index}">
                                                        ${!readonly ? `
                                                            <button type="button" onclick="removeChoice(${index})" 
                                                                class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-red-500 transition-opacity p-1">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                                </svg>
                                                            </button>
                                                        ` : ''}
                                                    </div>
                                                `;
        }

        function addChoice() {
            const choicesList = document.getElementById('choicesList');
            const div = document.createElement('div');
            div.innerHTML = createChoiceElement('', choiceCount);
            choicesList.appendChild(div.firstElementChild);
            choiceCount++;
            refreshChoiceIndices();
            attachChoiceListeners();
            validateForm();
        }

        function removeChoice(index) {
            const choicesList = document.getElementById('choicesList');
            const choices = choicesList.querySelectorAll('.group');
            choices.forEach(choice => {
                const input = choice.querySelector('.choice-input');
                if (input && parseInt(input.dataset.index) === index) {
                    choice.remove();
                    // after removal, refresh indices and validate
                    refreshChoiceIndices();
                    attachChoiceListeners();
                    validateForm();
                }
            });
        }

        function selectCorrectAnswer(index) {
            selectedCorrectAnswer = index;
            // Update all radio buttons
            document.querySelectorAll('.choice-radio').forEach(radio => {
                const radioIndex = parseInt(radio.dataset.index);
                const isSelected = radioIndex === index;
                radio.style.borderColor = isSelected ? '#0D2B70' : '#9CA3AF';
                radio.style.backgroundColor = isSelected ? '#0D2B70' : 'transparent';
                const dot = radio.querySelector('div');
                if (dot) dot.style.display = isSelected ? 'block' : 'none';
            });
            validateForm();
        }

        function refreshChoiceIndices() {
            const choicesList = document.getElementById('choicesList');
            const groups = Array.from(choicesList.querySelectorAll('.group'));
            let foundSelected = false;
            groups.forEach((group, i) => {
                const radio = group.querySelector('.choice-radio');
                const input = group.querySelector('.choice-input');
                if (radio) {
                    radio.dataset.index = i;
                    radio.setAttribute('onclick', `selectCorrectAnswer(${i})`);
                }
                if (input) {
                    input.dataset.index = i;
                    input.placeholder = `Option ${i + 1}`;
                }
                // detect visually selected radio
                if (radio) {
                    const dot = radio.querySelector('div');
                    const isSelected = dot && (dot.style.display === 'block');
                    if (isSelected) {
                        selectedCorrectAnswer = i;
                        foundSelected = true;
                    }
                }
            });
            if (!foundSelected) selectedCorrectAnswer = -1;
            // adjust choiceCount
            choiceCount = groups.length;
        }

        function getChoicesArray() {
            return Array.from(document.querySelectorAll('.choice-input'))
                .map(input => input.value.trim())
                .filter(v => v !== '');
        }

        function hasDuplicateChoices(arr) {
            const seen = new Set();
            for (const v of arr) {
                const key = v.toLowerCase();
                if (seen.has(key)) return true;
                seen.add(key);
            }
            return false;
        }

        function validateForm() {
            const type = document.getElementById('questionType').value;
            const questionText = document.getElementById('questionText').value.trim();
            const saveBtn = document.getElementById('saveQuestionBtn');
            const choicesError = document.getElementById('choicesError');

            let valid = true;
            choicesError.classList.add('hidden');
            choicesError.textContent = '';

            if (!questionText) valid = false;

            if (type === 'multiple_choice') {
                const choices = getChoicesArray();
                if (choices.length < 2) {
                    valid = false;
                    choicesError.textContent = 'Multiple choice questions must have at least 2 choices.';
                    choicesError.classList.remove('hidden');
                } else if (hasDuplicateChoices(choices)) {
                    valid = false;
                    choicesError.textContent = 'Choices must be unique. Please remove duplicate choices.';
                    choicesError.classList.remove('hidden');
                }

                // ensure a correct answer is declared and exists in current choices
                if (selectedCorrectAnswer === -1) {
                    valid = false;
                    if (!choicesError.textContent) {
                        choicesError.textContent = 'Please select a correct answer.';
                        choicesError.classList.remove('hidden');
                    }
                } else {
                    const allChoices = Array.from(document.querySelectorAll('.choice-input')).map(i => i.value.trim());
                    if (!allChoices[selectedCorrectAnswer]) {
                        valid = false;
                        if (!choicesError.textContent) {
                            choicesError.textContent = 'The selected correct answer is empty. Please fill in all choices.';
                            choicesError.classList.remove('hidden');
                        }
                    }
                }
            } else if (type === 'essay') {
                const essayMaxEl = document.getElementById('essayMaxScore');
                if (essayMaxEl) {
                    const v = essayMaxEl.value;
                    if (v === '') {
                        valid = false;
                    } else {
                        const num = parseInt(v, 10);
                        if (isNaN(num) || num < 0) valid = false;
                    }
                } else {
                    valid = false;
                }
            }

            // toggle save button
            if (valid) {
                saveBtn.removeAttribute('disabled');
                saveBtn.classList.remove('opacity-60', 'pointer-events-none');
            } else {
                saveBtn.setAttribute('disabled', 'disabled');
                saveBtn.classList.add('opacity-60', 'pointer-events-none');
            }

            return valid;
        }

        function attachChoiceListeners() {
            document.querySelectorAll('.choice-input').forEach(input => {
                // avoid attaching duplicate listeners
                if (!input._hasListener) {
                    input.addEventListener('input', () => {
                        // when input changes, refresh indices to keep consistency
                        refreshChoiceIndices();
                        validateForm();
                    });
                    input._hasListener = true;
                }
            });
            // also ensure radios update validation when clicked (for cases where onclick updated)
            document.querySelectorAll('.choice-radio').forEach(radio => {
                if (!radio._hasListener) {
                    radio.addEventListener('click', () => {
                        const idx = parseInt(radio.dataset.index);
                        selectCorrectAnswer(idx);
                    });
                    radio._hasListener = true;
                }
            });
        }

        async function saveQuestion(event) {
            event.preventDefault();

            const id = document.getElementById('questionId').value;
            const type = document.getElementById('questionType').value;
            const questionText = document.getElementById('questionText').value.trim();

            // Client-side validation
            if (!questionText) {
                showAlert('Please enter a question.', 'error');
                return;
            }

            // Validate based on question type
            if (type === 'multiple_choice') {
                const choices = Array.from(document.querySelectorAll('.choice-input'))
                    .map(input => input.value.trim())
                    .filter(v => v);

                if (choices.length < 2) {
                    showAlert('Multiple choice questions must have at least 2 choices.', 'error');
                    return;
                }

                // Duplicate choices check
                if (hasDuplicateChoices(choices)) {
                    showAlert('Choices must be unique. Please remove duplicate choices.', 'error');
                    return;
                }

                if (selectedCorrectAnswer === -1) {
                    showAlert('Please select a correct answer.', 'error');
                    return;
                }

                if (!choices[selectedCorrectAnswer]) {
                    showAlert('The selected correct answer is empty. Please fill in all choices.', 'error');
                    return;
                }
            }

            const choices = type === 'multiple_choice'
                ? Array.from(document.querySelectorAll('.choice-input')).map(input => input.value.trim()).filter(v => v)
                : null;

            const correctAnswer = type === 'multiple_choice'
                ? (choices && choices[selectedCorrectAnswer] ? choices[selectedCorrectAnswer] : null)
                : null;

            const data = {
                question: questionText,
                question_type: type,
                choices: choices,
                correct_answer: correctAnswer,
                essay_answer_guide: type === 'essay' ? document.getElementById('essayGuide').value : null,
                essay_max_score: type === 'essay' ? (function(){ const v = document.getElementById('essayMaxScore').value; return v === '' ? null : parseInt(v, 10); })() : null,
                difficulty_level: null,
                category: null,
                tags: null,
            };

            const url = id
                ? `/admin/exam-library/questions/${id}`
                : `/admin/exam-library/series/${seriesId}/questions`;
            const method = id ? 'PUT' : 'POST';

            // Show loader
            const loader = document.getElementById('loader');
            if (loader) loader.classList.remove('hidden');

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
                });

                // Check if response is ok
                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Failed to save question');
                }

                const result = await response.json();

                if (result.success) {
                    showAlert(result.message, 'success');
                    closeQuestionModal();
                    await loadQuestions(); // Reload to verify save
                } else {
                    showAlert(result.message || 'Failed to save question', 'error');
                }
            } catch (error) {
                console.error('Error saving question:', error);
                showAlert(error.message || 'An error occurred while saving. Please try again.', 'error');
            } finally {
                // Always hide loader
                if (loader) loader.classList.add('hidden');
            }
        }

        async function editQuestion(id) {
            const question = allQuestions.find(q => q.id === id);
            if (!question) return;

            document.getElementById('modalTitle').textContent = 'Edit Question';
            document.getElementById('questionId').value = question.id;
            document.getElementById('questionText').value = question.question;
            document.getElementById('questionType').value = question.question_type;

            handleTypeChange();

            if (question.choices && question.choices.length > 0) {
                document.getElementById('choicesList').innerHTML = '';
                choiceCount = 0;
                selectedCorrectAnswer = question.choices.indexOf(question.correct_answer);

                question.choices.forEach((choice, idx) => {
                    const choicesList = document.getElementById('choicesList');
                    const div = document.createElement('div');
                    div.innerHTML = createChoiceElement(choice, idx, false);
                    choicesList.appendChild(div.firstElementChild);
                    choiceCount++;
                });
                // ensure indices/listeners/validation are updated
                refreshChoiceIndices();
                attachChoiceListeners();
                validateForm();
            }

            if (question.essay_answer_guide) {
                document.getElementById('essayGuide').value = question.essay_answer_guide;
            }

            // Populate essay max score if available
            const essayMaxInput = document.getElementById('essayMaxScore');
            if (essayMaxInput) essayMaxInput.value = (question.essay_max_score != null ? question.essay_max_score : '');
            document.getElementById('questionModal').classList.remove('hidden');
        }

        async function duplicateQuestion(id) {
            const question = allQuestions.find(q => q.id === id);
            if (!question) return;

            // Open modal with duplicated data
            document.getElementById('modalTitle').textContent = 'Duplicate Question';
            document.getElementById('questionId').value = ''; // No ID for new question
            document.getElementById('questionText').value = question.question + ' (Copy)';
            document.getElementById('questionType').value = question.question_type;

            handleTypeChange();

            if (question.choices && question.choices.length > 0) {
                document.getElementById('choicesList').innerHTML = '';
                choiceCount = 0;
                selectedCorrectAnswer = question.choices.indexOf(question.correct_answer);

                question.choices.forEach((choice, idx) => {
                    const choicesList = document.getElementById('choicesList');
                    const div = document.createElement('div');
                    div.innerHTML = createChoiceElement(choice, idx, false);
                    choicesList.appendChild(div.firstElementChild);
                    choiceCount++;
                });
                // ensure indices/listeners/validation are updated
                refreshChoiceIndices();
                attachChoiceListeners();
                validateForm();
            }

            if (question.essay_answer_guide) {
                document.getElementById('essayGuide').value = question.essay_answer_guide;
            }
            const essayMaxInputDup = document.getElementById('essayMaxScore');
            if (essayMaxInputDup) essayMaxInputDup.value = (question.essay_max_score != null ? question.essay_max_score : '');

            document.getElementById('questionModal').classList.remove('hidden');
            // Ensure listeners are attached and validation runs for essay fields
            handleTypeChange();
            validateForm();
        }

        async function deleteQuestion(id) {
            const question = allQuestions.find(q => q.id === id);

            if (question.exam_usages_count > 0) {
                showAlert(`This question is currently used in ${question.exam_usages_count} exam(s). You cannot delete it.`, 'error');
                return;
            }

            if (!confirm('Are you sure you want to delete this question? This action cannot be undone.')) return;

            try {
                const response = await fetch(`/admin/exam-library/questions/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const result = await response.json();

                if (result.success) {
                    showAlert(result.message, 'success');
                    loadQuestions();
                } else {
                    showAlert(result.message, 'error');
                }
            } catch (error) {
                showAlert('An error occurred. Please try again.', 'error');
            }
        }

        function showAlert(message, type) {
            const toastType = type === 'success' ? 'success' : (type === 'warning' ? 'warning' : (type === 'error' ? 'error' : 'info'));
            if (typeof window.showAppToast === 'function') {
                window.showAppToast(message, toastType);
                return;
            }

            // Last-resort fallback
            if (typeof window.__nativeAlert === 'function') {
                window.__nativeAlert(String(message));
            }
        }

        function closeAlert() {
            // No-op: retained for compatibility with existing close button markup.
        }
    </script>

@endsection

@push('scripts')
    <script src="https://unpkg.com/feather-icons"></script>
    <script>
        if (window.feather) feather.replace();
    </script>
@endpush

