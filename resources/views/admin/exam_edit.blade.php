@extends('layout.admin')
@section('title', 'DILG - Edit Exam')

@push('styles')
    <!-- Import Montserrat font from Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        .font-montserrat {
            font-family: 'Montserrat', sans-serif;
        }
    </style>
@endpush

@section('content')
    <div id="examEditorRoot" x-data="examEditor()" class="w-full max-w-full font-montserrat">

        <!-- Header -->
        <section class="flex items-center space-x-4 mb-4 max-w-full border-b border-[#0D2B70]">
            <!-- BACK BUTTON -->
            <button aria-label="Back" @click.prevent="handleBackClick('{{ route('admin.manage_exam', $vacancy_id) }}?batch={{ (int) ($selectedBatch ?? request('batch', 1)) }}')"
                class="use-loader group">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#0D2B70] hover:opacity-80 transition"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <h1 class="flex items-center gap-3 w-full py-2 tracking-wide select-none">
                <span class="text-[#0D2B70] text-4xl font-montserrat whitespace-nowrap">Edit Exam Questions</span>
            </h1>
        </section>

        <div class="flex flex-row justify-between items-center gap-2">
            <span class="text-[#0D2B70] text-2xl font-montserrat whitespace-nowrap">{{ $vacancy->position_title }},
                {{ $vacancy->vacancy_type }} position</span>
            <button type="button" @click="openExamLibrary()"
                class="border border-[#002C76] hover:bg-blue-900 text-[#002C76] hover:text-white font-bold py-2 px-6 rounded inline-flex items-center gap-2">
                <span>Add from Exam Library</span>
            </button>
        </div>

        <!-- Question Modal (same design as exam library questions) -->
        <div id="questionModal"
            class="hidden fixed inset-0 z-[10020] bg-slate-900/60 backdrop-blur-md flex items-center justify-center overflow-y-auto">
            <div class="bg-white rounded-xl shadow-2xl p-8 max-w-4xl w-full mx-4 my-8 max-h-[90vh] overflow-y-auto">
                <h2 id="modalTitle" class="text-2xl font-bold text-[#0D2B70] mb-6">Add Question</h2>

                <form id="questionFormModal" onsubmit="saveQuestionModal(event)">
                    <input type="hidden" id="modalQuestionIndex" value="">

                    <div class="mb-4">
                        <label for="questionTextModal" class="block text-sm font-semibold text-gray-700 mb-2">Question <span class="text-red-500">*</span></label>
                        <input type="text" id="questionTextModal" required
                            class="w-full px-4 py-2 border border-blue-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0D2B70]"
                            placeholder="Enter your question here...">
                    </div>

                    <div class="mb-4">
                        <label for="questionTypeModal" class="block text-sm font-semibold text-gray-700 mb-2">Question Type
                        <span class="text-red-500">*</span></label>
                        <select id="questionTypeModal" required onchange="handleTypeChangeModal()"
                            class="w-full h-10 cursor-pointer px-4 rounded-md border border-[#0D2B70] text-[#0D2B70] font-semibold bg-white focus:outline-none focus:ring-2 focus:ring-[#0D2B70]">
                            <option value="multiple_choice">Multiple Choice</option>
                            <option value="essay">Essay</option>
                        </select>
                    </div>

                    <div id="choicesContainerModal" class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Choices</label>
                        <div id="choicesListModal" class="space-y-2"></div>
                        <div class="flex items-center gap-3 mt-2" id="addChoiceContainerModal">
                            <div class="w-5 h-5 rounded-full border-2 border-gray-300 flex-shrink-0"></div>
                            <button type="button" onclick="addChoiceModal()" id="addChoiceBtnModal"
                                class="text-sm text-gray-500 hover:text-[#0D2B70] font-medium hover:underline">Add option</button>
                        </div>
                        <p class="italic text-sm text-red-500 mt-2" id="choiceTipModal">Tick the option to declare as answer.</p>
                        <p class="italic text-sm text-red-500 mt-2 hidden" id="choicesErrorModal"></p>
                    </div>

                    <div id="essayGuideContainerModal" class="mb-4 hidden">
                        <label for="essayGuideModal" class="block text-sm font-semibold text-gray-700 mb-2">Answer Guide
                            (Optional)</label>
                        <textarea id="essayGuideModal" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0D2B70]"
                            placeholder="Answer here."></textarea>
                    </div>

                    <div class="flex gap-4 justify-end">
                        <button type="button" onclick="closeQuestionModal()"
                            class="border-2 border-gray-300 hover:bg-gray-50 text-gray-700 font-bold py-2 px-6 rounded-lg transition">Cancel</button>
                        <button type="submit" id="saveQuestionModalBtn"
                            class="bg-[#002C76] hover:bg-blue-900 text-white font-bold py-2 px-6 rounded-lg transition-all duration-200 hover:scale-105">
                            <i class="fa-solid fa-floppy-disk mr-2"></i>
                            Save Question
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Confirmation Modal -->
        <template x-teleport="body">
            <div x-show="showConfirmModal" x-cloak class="fixed inset-0 z-[10030] overflow-y-auto" style="display: none;"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                <!-- Backdrop -->
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" @click="if(!isProcessing) closeModal()">
                </div>

                <!-- Modal Panel -->
                <div class="relative flex min-h-full items-center justify-center p-4">
                    <div class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl ring-1 ring-black/5 transition-all"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                        <div class="px-6 py-5 border-b border-slate-100">
                            <div class="flex items-start gap-4">
                                <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-full"
                                    :class="(modalType === 'discard' || modalType === 'back') ? 'bg-red-100' : 'bg-blue-100'">
                                    <template x-if="modalType === 'discard' || modalType === 'back'">
                                        <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                        </svg>
                                    </template>
                                    <template x-if="modalType === 'save'">
                                        <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </template>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-lg font-bold text-slate-900 leading-6" x-text="modalTitle"></h3>
                                    <p class="mt-2 text-sm text-slate-600 leading-6" x-text="modalMessage"></p>
                                </div>
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-slate-50 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                            <button type="button"
                                class="inline-flex w-full sm:w-auto justify-center rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-100 transition"
                                :disabled="isProcessing" @click="closeModal()">
                                Cancel
                            </button>
                            <button type="button"
                                class="inline-flex w-full sm:w-auto justify-center rounded-lg px-4 py-2.5 text-sm font-semibold text-white shadow-sm flex items-center gap-2 transition"
                                :class="(modalType === 'discard' || modalType === 'back') ? 'bg-red-600 hover:bg-red-500' : 'bg-[#002C76] hover:bg-[#0A2259]'"
                                :disabled="isProcessing" @click="confirmAction()">
                                <svg x-show="isProcessing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                    </circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span
                                    x-text="isProcessing ? 'Saving...' : (modalType === 'save' ? 'Save Changes' : (modalType === 'discard' ? 'Discard All' : 'Leave Page'))"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Question Form -->
        <form method="POST" id="examForm" @submit.prevent="handleSaveClick"
            action="{{ route('admin.exam.update', $vacancy_id) }}">
            @csrf
            <input type="hidden" name="questions" :value="JSON.stringify(questions)">
            <input type="hidden" name="batch" value="{{ (int) ($selectedBatch ?? request('batch', 1)) }}">

            <!-- Empty state -->
            <div x-show="questions.length === 0" class="text-center text-gray-500 mt-10">
                <p class="text-xl font-semibold">There are no questions yet.</p>
                <button type="button"
                    class="mt-4 bg-[#002C76] hover:bg-blue-900 text-white font-bold py-2 px-6 rounded inline-flex items-center gap-2"
                    @click="addQuestion()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Your First Question
                </button>
            </div>

            <!-- Question List -->
            <div class="space-y-8" x-show="questions.length > 0">
                <template x-for="(q, index) in questions" :key="index">
                    <div class="p-6 bg-white rounded-lg shadow border border-gray-200 w-full relative">
                        <!-- Question Label -->
                        <!-- <div class="mb-4 font-regular text-lg" x-text="`Question ${index + 1} of ${questions.length}`"></div> -->

                        <!-- Question Body -->
                        <div class="">
                            <!-- <textarea required class="w-full h-40 resize-none border border-blue-300 rounded-lg p-4" placeholder="Enter your question..." x-model="q.text"></textarea> -->
                            <div class="flex flex-row justify-between items-center gap-2">
                                <input type="text" required class="w-full border border-blue-300 rounded-lg h-10 px-4" @input="checkForChanges"
                                    placeholder="Untitled Question" x-model="q.duration">
                                <div>

                                    <select id="typeOfQuestion" x-model="q.type" @change="checkForChanges" class="h-10 cursor-pointer px-4 rounded-md border border-[#0D2B70] text-[#0D2B70] font-semibold bg-white
                                                focus:outline-none focus:ring-2 focus:ring-[#0D2B70] focus:ring-offset-1">
                                        <option value="MCQ">MCQ</option>
                                        <option value="Essay">Essay</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Essay Max Score -->
                            <div class="mt-3" x-show="q.type === 'Essay'">
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Max Score</label>
                                <input type="number" min="0" x-model="q.essayMax" @input="checkForChanges"
                                    class="w-40 border border-blue-300 rounded-lg h-10 px-3" placeholder="e.g., 50">
                            </div>

                            <!-- MCQ Choices -->
                            <div class="mt-4 space-y-2" x-show="q.type === 'MCQ'">
                                <template x-for="(option, optIndex) in q.choices" :key="optIndex">
                                    <div class="flex items-center gap-3 group">
                                        <!-- Radio Button (Functional) -->
                                        <div @click="q.correctAnswer = optIndex; checkForChanges()"
                                            class="w-5 h-5 rounded-full border-2 flex-shrink-0 cursor-pointer transition-all flex items-center justify-center"
                                            :class="q.correctAnswer === optIndex ? 'border-[#0D2B70] bg-[#0D2B70]' : 'border-gray-400 hover:border-[#0D2B70]'">
                                            <div x-show="q.correctAnswer === optIndex"
                                                class="w-2 h-2 rounded-full bg-white"></div>
                                        </div>

                                        <!-- Option Input -->
                                        <input type="text" x-model="q.choices[optIndex]" @input="checkForChanges"
                                            class="w-full border-b border-transparent hover:border-gray-300 focus:border-[#0D2B70] focus:outline-none py-1 px-2 transition-colors"
                                            placeholder="Option">

                                        <!-- Remove Option Button -->
                                        <button type="button" @click="removeOption(index, optIndex)"
                                            class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-red-500 transition-opacity p-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                                fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                </template>

                                <!-- Add Option / Add Other -->
                                <div class="flex items-center gap-3">
                                    <div class="w-5 h-5 rounded-full border-2 border-gray-300 flex-shrink-0"></div>
                                    <div class="flex items-center gap-1 text-sm text-gray-500">
                                        <button type="button" @click="addOption(index)"
                                            class="hover:underline hover:text-[#0D2B70] font-medium">
                                            Add option
                                        </button>
                                        <!-- <span>or</span>
                                                <button type="button" class="hover:underline hover:text-[#0D2B70] font-medium">
                                                    add "Other"
                                                </button> -->
                                    </div>
                                </div>
                            </div>

                            <!-- TIP, DUPLICATE, AND REMOVE BUTTON -->
                            <div class="flex flex-row justify-between items-center gap-2">
                                <span class="italic text-sm text-[#0D2B70]">
                                    Tick the option to declare as answer.
                                </span>
                                <div class="flex gap-2">
                                    <!-- Duplicate Button -->
                                    <button type="button" @click="duplicateQuestion(index)" title="Duplicate this question"
                                        class="text-white font-bold p-3 rounded-lg 
                                                    transition-all duration-200 hover:scale-105 hover:shadow-md
                                                    relative group">
                                        <i class="fa-solid fa-copy text-[#0D2B70] text-xl"></i>
                                        <span
                                            class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1 
                                                            bg-gray-800 text-white text-xs rounded-md whitespace-nowrap
                                                            opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                                            Duplicate Question
                                        </span>
                                    </button>

                                    <!-- Remove Button -->
                                    <button type="button" @click="removeQuestion(index)" title="Remove this question" class="text-white font-bold p-3 rounded-lg 
                                                    transition-all duration-200 hover:scale-105 hover:shadow-md
                                                    relative group">
                                        <i class="fa-solid fa-trash text-red-700 text-xl"></i>
                                        <span
                                            class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1 
                                                            bg-gray-800 text-white text-xs rounded-md whitespace-nowrap
                                                            opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                                            Remove Question
                                        </span>
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </template>
            </div>

            <!-- Actions -->
            <div class="mt-10 flex flex-row justify-between items-center gap-4" x-show="questions.length > 0">
                <!-- Add More Questions - Left -->
                <button type="button" class="border-2 border-[#002C76] hover:bg-[#002C76] hover:scale-105 
                                text-[#002C76] hover:text-white font-bold py-2 px-6 rounded-lg 
                                flex items-center gap-2 transition-all duration-200" @click="addQuestion()">
                    <i class="fa-solid fa-plus text-lg"></i>
                    <span>Add More Questions</span>
                </button>

                <!-- Discard and Save - Right -->
                <div class="flex gap-3">
                    <button type="button" @click="handleDiscardClick" class="border-2 border-red-600 hover:bg-red-600 hover:text-white 
                                    text-red-600 font-bold py-2 px-6 rounded-lg 
                                    transition-all duration-200 hover:scale-105 hover:shadow-md">
                        <i class="fa-solid fa-trash-can mr-2"></i>
                        Discard
                    </button>
                    <button type="submit" :disabled="!hasChanges"
                        :class="hasChanges ? 'bg-[#002C76] hover:scale-105 hover:shadow-md' : 'bg-gray-400 cursor-not-allowed'"
                        class="text-white font-bold py-2 px-6 rounded-lg transition-all duration-200">
                        <i class="fa-solid fa-floppy-disk mr-2"></i>
                        Save
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <!-- Feather Icons -->
    <script src="https://unpkg.com/feather-icons"></script>
    <script>
        // Modal choice helpers for exam editor
        let choiceCountModal = 0;
        let selectedCorrectAnswerModal = -1;

        function createChoiceElementModal(value = '', index = choiceCountModal) {
            return `
                <div class="flex items-center gap-3 group">
                    <div onclick="selectCorrectAnswerModal(${index})" 
                        class="w-5 h-5 rounded-full border-2 flex-shrink-0 cursor-pointer transition-all flex items-center justify-center choice-radio-modal"
                        data-index="${index}" style="border-color: ${selectedCorrectAnswerModal === index ? '#0D2B70' : '#9CA3AF'}; background-color: ${selectedCorrectAnswerModal === index ? '#0D2B70' : 'transparent'};">
                        <div class="w-2 h-2 rounded-full bg-white" style="display: ${selectedCorrectAnswerModal === index ? 'block' : 'none'};"></div>
                    </div>
                    <input type="text" class="choice-input-modal flex-1 border-b border-transparent hover:border-gray-300 focus:border-[#0D2B70] focus:outline-none py-1 px-2 transition-colors" 
                        placeholder="Option ${index + 1}" value="${value}" data-index="${index}">
                    <button type="button" onclick="removeChoiceModal(${index})" class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-red-500 transition-opacity p-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            `;
        }

        function addChoiceModal(value = '') {
            const choicesList = document.getElementById('choicesListModal');
            const div = document.createElement('div');
            div.innerHTML = createChoiceElementModal(value, choiceCountModal);
            choicesList.appendChild(div.firstElementChild);
            choiceCountModal++;
            attachChoiceListenersModal();
            validateModalForm();
        }

        function removeChoiceModal(index) {
            const choicesList = document.getElementById('choicesListModal');
            const choices = choicesList.querySelectorAll('.group');
            choices.forEach(choice => {
                const input = choice.querySelector('.choice-input-modal');
                if (input && parseInt(input.dataset.index) === index) {
                    choice.remove();
                }
            });
            refreshChoiceIndicesModal();
            validateModalForm();
        }

        function selectCorrectAnswerModal(index) {
            selectedCorrectAnswerModal = index;
            document.querySelectorAll('.choice-radio-modal').forEach(radio => {
                const radioIndex = parseInt(radio.dataset.index);
                const isSelected = radioIndex === index;
                radio.style.borderColor = isSelected ? '#0D2B70' : '#9CA3AF';
                radio.style.backgroundColor = isSelected ? '#0D2B70' : 'transparent';
                const dot = radio.querySelector('div');
                if (dot) dot.style.display = isSelected ? 'block' : 'none';
            });
            validateModalForm();
        }

        function refreshChoiceIndicesModal() {
            const choicesList = document.getElementById('choicesListModal');
            const groups = Array.from(choicesList.querySelectorAll('.group'));
            let foundSelected = false;
            groups.forEach((group, i) => {
                const radio = group.querySelector('.choice-radio-modal');
                const input = group.querySelector('.choice-input-modal');
                if (radio) {
                    radio.dataset.index = i;
                    radio.setAttribute('onclick', `selectCorrectAnswerModal(${i})`);
                }
                if (input) {
                    input.dataset.index = i;
                    input.placeholder = `Option ${i + 1}`;
                }
                if (radio) {
                    const dot = radio.querySelector('div');
                    const isSelected = dot && (dot.style.display === 'block');
                    if (isSelected) {
                        selectedCorrectAnswerModal = i;
                        foundSelected = true;
                    }
                }
            });
            if (!foundSelected) selectedCorrectAnswerModal = -1;
            choiceCountModal = groups.length;
        }

        function attachChoiceListenersModal() {
            document.querySelectorAll('.choice-input-modal').forEach(input => {
                if (!input._hasListener) {
                    input.addEventListener('input', () => { refreshChoiceIndicesModal(); validateModalForm(); });
                    input._hasListener = true;
                }
            });
            document.querySelectorAll('.choice-radio-modal').forEach(radio => {
                if (!radio._hasListener) {
                    radio.addEventListener('click', () => selectCorrectAnswerModal(parseInt(radio.dataset.index)));
                    radio._hasListener = true;
                }
            });
        }

        function getChoicesArrayModal() {
            return Array.from(document.querySelectorAll('.choice-input-modal')).map(i => i.value.trim()).filter(v => v !== '');
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

        function validateModalForm() {
            const type = document.getElementById('questionTypeModal').value;
            const questionText = document.getElementById('questionTextModal').value.trim();
            const choicesError = document.getElementById('choicesErrorModal');

            let valid = true;
            choicesError.classList.add('hidden');
            choicesError.textContent = '';

            if (!questionText) valid = false;

            if (type === 'multiple_choice') {
                const choices = getChoicesArrayModal();
                if (choices.length < 2) {
                    valid = false;
                    choicesError.textContent = 'Multiple choice questions must have at least 2 choices.';
                    choicesError.classList.remove('hidden');
                } else if (hasDuplicateChoices(choices)) {
                    valid = false;
                    choicesError.textContent = 'Choices must be unique. Please remove duplicate choices.';
                    choicesError.classList.remove('hidden');
                }

                if (selectedCorrectAnswerModal === -1) {
                    valid = false;
                    if (!choicesError.textContent) {
                        choicesError.textContent = 'Please select a correct answer.';
                        choicesError.classList.remove('hidden');
                    }
                } else {
                    const allChoices = Array.from(document.querySelectorAll('.choice-input-modal')).map(i => i.value.trim());
                    if (!allChoices[selectedCorrectAnswerModal]) {
                        valid = false;
                        if (!choicesError.textContent) {
                            choicesError.textContent = 'The selected correct answer is empty. Please fill in all choices.';
                            choicesError.classList.remove('hidden');
                        }
                    }
                }
            }

            return valid;
        }

        function handleTypeChangeModal() {
            const type = document.getElementById('questionTypeModal').value;
            const choicesContainer = document.getElementById('choicesContainerModal');
            const essayGuideContainer = document.getElementById('essayGuideContainerModal');
            // Create or find essay max input
            let essayMaxContainer = document.getElementById('essayMaxContainerModal');
            if (!essayMaxContainer) {
                essayMaxContainer = document.createElement('div');
                essayMaxContainer.id = 'essayMaxContainerModal';
                essayMaxContainer.className = 'mb-4 hidden';
                essayMaxContainer.innerHTML = `
                    <label for="essayMaxScoreModal" class="block text-sm font-semibold text-gray-700 mb-2">Max Score <span class="text-red-500">*</span></label>
                    <input type="number" id="essayMaxScoreModal" min="0" placeholder="e.g., 50"
                        class="w-40 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0D2B70]">
                `;
                // Insert after essay guide
                const ref = document.getElementById('essayGuideContainerModal');
                ref.parentNode.insertBefore(essayMaxContainer, ref.nextSibling);
            }
            const addChoiceBtn = document.getElementById('addChoiceContainerModal');

            if (type === 'multiple_choice') {
                choicesContainer.classList.remove('hidden');
                essayGuideContainer.classList.add('hidden');
                essayMaxContainer.classList.add('hidden');
                addChoiceBtn.classList.remove('hidden');
                if (choiceCountModal === 0) for (let i=0;i<4;i++) addChoiceModal();
            } else {
                choicesContainer.classList.add('hidden');
                essayGuideContainer.classList.remove('hidden');
                essayMaxContainer.classList.remove('hidden');
            }
        }

        function mountModalToBody(modalId) {
            const modal = document.getElementById(modalId);
            if (modal && modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
        }

        function setModalScreenFocusLock() {
            const questionModal = document.getElementById('questionModal');
            const questionModalOpen = !!questionModal && !questionModal.classList.contains('hidden');

            const alpineRoot = document.getElementById('examEditorRoot');
            const comp = alpineRoot && alpineRoot.__x ? alpineRoot.__x.$data : null;
            const confirmModalOpen = !!(comp && comp.showConfirmModal);

            document.body.classList.toggle('overflow-hidden', questionModalOpen || confirmModalOpen);
        }

        document.addEventListener('DOMContentLoaded', () => {
            mountModalToBody('questionModal');
            setModalScreenFocusLock();
        });

        function openQuestionModalForEdit(index) {
            // populate modal with question data from Alpine component
            const alpineRoot = document.getElementById('examEditorRoot');
            const comp = alpineRoot && alpineRoot.__x ? alpineRoot.__x.$data : null;
            if (!comp) return;
            const q = comp.questions[index];
            document.getElementById('modalQuestionIndex').value = index;
            document.getElementById('modalTitle').textContent = 'Edit Question';
            document.getElementById('questionTextModal').value = q.duration || q.text || '';
            document.getElementById('questionTypeModal').value = (q.type && q.type.toLowerCase && q.type.toLowerCase() === 'essay') ? 'essay' : 'multiple_choice';
            document.getElementById('choicesListModal').innerHTML = '';
            choiceCountModal = 0;
            selectedCorrectAnswerModal = -1;
            if (q.choices && q.choices.length) {
                q.choices.forEach((c, idx) => { addChoiceModal(c); });
                if (typeof q.correctAnswer === 'number' && q.correctAnswer >=0) selectCorrectAnswerModal(q.correctAnswer);
            } else if (document.getElementById('questionTypeModal').value === 'multiple_choice') {
                for (let i=0;i<4;i++) addChoiceModal();
            }
            handleTypeChangeModal();
            document.getElementById('questionModal').classList.remove('hidden');
            setModalScreenFocusLock();
        }

        function openQuestionModalForCreate() {
            document.getElementById('modalQuestionIndex').value = '';
            document.getElementById('modalTitle').textContent = 'Add Question';
            document.getElementById('questionFormModal').reset();
            document.getElementById('choicesListModal').innerHTML = '';
            choiceCountModal = 0;
            selectedCorrectAnswerModal = -1;
            // default MCQ with 4 choices
            document.getElementById('questionTypeModal').value = 'multiple_choice';
            for (let i=0;i<4;i++) addChoiceModal();
            handleTypeChangeModal();
            document.getElementById('questionModal').classList.remove('hidden');
            setModalScreenFocusLock();
        }

        function closeQuestionModal() {
            document.getElementById('questionModal').classList.add('hidden');
            setModalScreenFocusLock();
        }

        function saveQuestionModal(e) {
            e.preventDefault();
            if (!validateModalForm()) return;

            const index = document.getElementById('modalQuestionIndex').value;
            const text = document.getElementById('questionTextModal').value.trim();
            const type = document.getElementById('questionTypeModal').value;
            const choices = (type === 'multiple_choice') ? getChoicesArrayModal() : [];
                        const correctAnswer = (type === 'multiple_choice' && selectedCorrectAnswerModal>=0) ? choices[selectedCorrectAnswerModal] : '';
            const isEssay = type === 'essay';

            const alpineRoot = document.getElementById('examEditorRoot');
            const comp = alpineRoot && alpineRoot.__x ? alpineRoot.__x.$data : null;
            if (!comp) return;

            let essayMax = null;
            const essayMaxEl = document.getElementById('essayMaxScoreModal');
            if (isEssay && essayMaxEl) {
                const v = essayMaxEl.value;
                essayMax = v === '' ? null : parseInt(v, 10);
            }

            const mapped = {
                text: text,
                duration: text,
                type: isEssay ? 'Essay' : 'MCQ',
                answer: correctAnswer || '',
                choices: choices.length ? choices : (isEssay ? [] : ['Option 1']),
                correctAnswer: (selectedCorrectAnswerModal>=0 ? selectedCorrectAnswerModal : -1),
                essayMax: essayMax
            };

            if (index === '') {
                comp.questions.push(mapped);
            } else {
                comp.questions.splice(parseInt(index), 1, mapped);
            }
            comp.checkForChanges();
            closeQuestionModal();
        }
    </script>
    <script>
        document.addEventListener('alpine:init', () => {
            feather.replace();
        });

        function examEditor() {
            return {
                questions: [], // Start with no questions
                originalQuestionsSnapshot: '', // JSON snapshot of original questions for change detection
                hasChanges: false, // Track if there are unsaved changes
                showConfirmModal: false,
                modalType: '', // 'discard' or 'save'
                modalTitle: '',
                modalMessage: '',
                isProcessing: false, // Loading state for modal
                pendingBackUrl: '', // URL to navigate to after confirmation

                init() {
                        const data = @json($exam_items);
                    // console.log('Loaded Exam Data:', data); // Debugging

                    this.questions = data.map(q => {
                        let parsedChoices = [];
                        // Ensure choices is an array for the new format
                        if (q.choices) {
                            // If it's already an array (from JSON decode or new format)
                            if (Array.isArray(q.choices)) {
                                parsedChoices = q.choices;
                            }
                            // If it's an object {A:..., B:...} (legacy format), convert to array
                            else if (typeof q.choices === 'object') {
                                parsedChoices = Object.values(q.choices).filter(val => val !== '');
                            }
                            // If it's a string (from DB), try to parse
                            else if (typeof q.choices === 'string') {
                                try {
                                    const parsed = JSON.parse(q.choices);
                                    if (Array.isArray(parsed)) {
                                        parsedChoices = parsed;
                                    } else if (typeof parsed === 'object') {
                                        parsedChoices = Object.values(parsed).filter(val => val !== '');
                                    }
                                } catch (e) { console.error('Error parsing choices', e); }
                            }
                        }

                        // Ensure at least one empty option if none exist
                        if (parsedChoices.length === 0 && parseInt(q.is_essay) !== 1) {
                            parsedChoices = ['Option 1'];
                        }

                        // Find correct answer index
                        let correctAnswerIndex = -1;
                        if (q.ans && parsedChoices.length > 0) {
                            correctAnswerIndex = parsedChoices.indexOf(q.ans);
                        }

                        // Determine text vs duration
                        // Prioritize 'question' column from DB which is mapped to 'q.question'
                        // If empty, check if there's a legacy 'duration' (unlikely for question text but kept for safety)
                        let questionText = q.question || '';

                        // If still empty, check if the object has a 'text' property (unlikely from DB but possible in JS)
                        if (!questionText && q.text) questionText = q.text;

                            return {
                            text: questionText,
                            type: parseInt(q.is_essay) === 1 ? 'Essay' : 'MCQ',
                            answer: q.ans || '',
                            duration: questionText, // Use question as default for the input field model (x-model="q.duration")
                            choices: parsedChoices,
                                correctAnswer: correctAnswerIndex,
                                essayMax: q.essay_max_score ?? null
                        };
                    });

                    // Save original state for change detection
                    this.originalQuestionsSnapshot = JSON.stringify(this.questions);
                    this.hasChanges = false;

                    const restoredDraft = this.restoreDraftFromStorage();
                    if (restoredDraft) {
                        this.questions = restoredDraft;
                        this.checkForChanges();
                    }

                    // If there are imported questions from Exam Library, merge them
                    try {
                        const imported = localStorage.getItem('importedQuestions');
                        if (imported) {
                            const payload = JSON.parse(imported);
                            if (payload && Array.isArray(payload.questions)) {
                                // Map imported questions to editor format and append
                                payload.questions.forEach(q => {
                                    // q likely has {question, question_type, choices, correct_answer}
                                    let parsedChoices = [];
                                    if (q.choices) {
                                        if (Array.isArray(q.choices)) parsedChoices = q.choices;
                                        else if (typeof q.choices === 'object') parsedChoices = Object.values(q.choices).filter(v => v !== '');
                                        else if (typeof q.choices === 'string') {
                                            try { const p = JSON.parse(q.choices); if (Array.isArray(p)) parsedChoices = p; } catch(e){}
                                        }
                                    }

                                    const isEssay = q.question_type === 'essay' || q.is_essay === 1 || (q.type && q.type.toLowerCase() === 'essay');
                                    const text = q.question || q.text || '';
                                    const correctIndex = parsedChoices.length > 0 && q.correct_answer ? parsedChoices.indexOf(q.correct_answer) : -1;

                                    this.questions.push({
                                        text: text,
                                        type: isEssay ? 'Essay' : 'MCQ',
                                        answer: q.correct_answer || '',
                                        duration: text,
                                        choices: parsedChoices.length ? parsedChoices : (isEssay ? [] : ['Option 1']),
                                        correctAnswer: correctIndex,
                                        essayMax: q.essay_max_score ?? null
                                    });
                                });
                                this.checkForChanges();
                            }
                            // remove the import payload after consuming
                            localStorage.removeItem('importedQuestions');
                        }
                    } catch (e) { console.error('Error importing questions', e); }

                    // Ensure icons are rendered after data load
                    this.$nextTick(() => {
                        if (window.feather) feather.replace();
                    });
                },

                getDraftStorageKey() {
                    return 'examEditorDraft_{{ $vacancy_id }}_batch_{{ (int) ($selectedBatch ?? request('batch', 1)) }}';
                },

                persistDraftToStorage() {
                    try {
                        const normalized = this.questions.map(question => this.normalizeQuestion(question));
                        localStorage.setItem(this.getDraftStorageKey(), JSON.stringify({
                            vacancy_id: '{{ $vacancy_id }}',
                            batch: '{{ (int) ($selectedBatch ?? request('batch', 1)) }}',
                            questions: normalized
                        }));
                    } catch (error) {
                        console.error('Failed to persist exam draft', error);
                    }
                },

                restoreDraftFromStorage() {
                    try {
                        const raw = localStorage.getItem(this.getDraftStorageKey());
                        if (!raw) {
                            return null;
                        }

                        const payload = JSON.parse(raw);
                        if (!payload || !Array.isArray(payload.questions)) {
                            return null;
                        }

                        return payload.questions.map(question => this.normalizeQuestion(question));
                    } catch (error) {
                        console.error('Failed to restore exam draft', error);
                        return null;
                    }
                },

                clearDraftStorage() {
                    localStorage.removeItem(this.getDraftStorageKey());
                },

                openExamLibrary() {
                    this.persistDraftToStorage();
                    window.location.href = "{{ url('/admin/exam-library/select') }}?return={{ urlencode(url()->current() . '?batch=' . ((int) ($selectedBatch ?? request('batch', 1)))) }}";
                },

                addQuestion() {
                    this.questions.push({
                        text: '',
                        type: 'MCQ',
                        answer: '',
                        choices: ['Option 1'], // Start with Option 1
                        correctAnswer: -1 // Initialize with no selection
                    });
                    this.checkForChanges();
                    this.$nextTick(() => feather.replace());
                },

                addOption(questionIndex) {
                    const currentLength = this.questions[questionIndex].choices.length;
                    this.questions[questionIndex].choices.push(`Option ${currentLength + 1}`);
                    this.checkForChanges();
                },

                removeOption(questionIndex, optionIndex) {
                    this.questions[questionIndex].choices.splice(optionIndex, 1);
                    this.checkForChanges();
                },
                removeQuestion(index) {
                    this.questions.splice(index, 1);
                    this.checkForChanges();
                },
                moveUp(index) {
                    if (index > 0) {
                        [this.questions[index], this.questions[index - 1]] = [this.questions[index - 1], this.questions[index]];
                        this.checkForChanges();
                    }
                },
                moveDown(index) {
                    if (index < this.questions.length - 1) {
                        [this.questions[index], this.questions[index + 1]] = [this.questions[index + 1], this.questions[index]];
                        this.checkForChanges();
                    }
                },
                duplicateQuestion(index) {
                    const questionToClone = this.questions[index];
                    const clonedQuestion = {
                        text: questionToClone.text,
                        type: questionToClone.type,
                        answer: questionToClone.answer,
                        duration: questionToClone.duration,
                        choices: [...questionToClone.choices], // Deep copy the array
                        correctAnswer: questionToClone.correctAnswer // Copy correct answer index
                    };

                    // Insert right after the current question
                    this.questions.splice(index + 1, 0, clonedQuestion);
                    this.checkForChanges();

                    // Re-initialize icons for the new element
                    this.$nextTick(() => {
                        if (window.feather) feather.replace();
                    });
                },

                handleBackClick(url) {
                    if (this.hasChanges) {
                        this.modalType = 'back';
                        this.modalTitle = 'Unsaved Changes';
                        this.modalMessage = 'You have unsaved changes. Are you sure you want to leave? All unsaved progress will be lost.';
                        this.pendingBackUrl = url;
                        this.showConfirmModal = true;
                        setModalScreenFocusLock();
                    } else {
                        window.location.href = url;
                    }
                },

                handleDiscardClick() {
                    this.modalType = 'discard';
                    this.modalTitle = 'Discard All Questions';
                    this.modalMessage = 'Are you sure you want to discard all questions? This action cannot be undone and all current progress will be lost.';
                    this.showConfirmModal = true;
                    setModalScreenFocusLock();
                },

                handleSaveClick() {
                    if (!this.validateForm()) {
                        return; // Stop if validation fails
                    }
                    this.modalType = 'save';
                    this.modalTitle = 'Save Exam Changes';
                    this.modalMessage = 'Are you sure you want to save these changes? This will update the exam questions for all applicants.';
                    this.showConfirmModal = true;
                    setModalScreenFocusLock();
                },

                closeModal() {
                    if (this.isProcessing) return;
                    this.showConfirmModal = false;
                    this.modalType = '';
                    setModalScreenFocusLock();
                },

                showToastNotification(message, type = 'success') {
                    if (typeof window.showAppToast === 'function') {
                        window.showAppToast(message, type);
                        return;
                    }
                    console.log(message);
                },

                normalizeQuestion(question) {
                    const type = String(question?.type || 'MCQ').toLowerCase() === 'essay' ? 'Essay' : 'MCQ';
                    const text = typeof question?.duration === 'string'
                        ? question.duration
                        : (typeof question?.text === 'string' ? question.text : '');

                    let choices = Array.isArray(question?.choices)
                        ? question.choices.map(choice => String(choice ?? ''))
                        : [];

                    let correctAnswer = Number.isInteger(question?.correctAnswer)
                        ? question.correctAnswer
                        : parseInt(question?.correctAnswer, 10);

                    if (!Number.isInteger(correctAnswer)) {
                        correctAnswer = -1;
                    }

                    let answer = typeof question?.answer === 'string' ? question.answer : '';

                    if (type === 'Essay') {
                        choices = [];
                        correctAnswer = -1;
                        answer = '';
                    } else {
                        if (choices.length === 0) {
                            choices = ['Option 1'];
                        }

                        if (correctAnswer < 0 || correctAnswer >= choices.length) {
                            correctAnswer = answer ? choices.indexOf(answer) : -1;
                        }

                        answer = correctAnswer >= 0 ? choices[correctAnswer] : answer;
                    }

                    const essayMaxValue = question?.essayMax;
                    const parsedEssayMax = essayMaxValue === '' || essayMaxValue === null || essayMaxValue === undefined
                        ? null
                        : parseInt(essayMaxValue, 10);

                    return {
                        text,
                        duration: text,
                        type,
                        answer,
                        choices,
                        correctAnswer,
                        essayMax: type === 'Essay' && Number.isInteger(parsedEssayMax)
                            ? Math.max(0, parsedEssayMax)
                            : null
                    };
                },

                markCurrentStateAsSaved() {
                    this.questions = this.questions.map(question => this.normalizeQuestion(question));
                    this.originalQuestionsSnapshot = JSON.stringify(this.questions);
                    this.hasChanges = false;

                    this.$nextTick(() => {
                        if (window.feather) feather.replace();
                    });
                },

                async confirmAction() {
                    if (this.modalType === 'back') {
                        this.clearDraftStorage();
                        window.location.href = this.pendingBackUrl;
                    } else if (this.modalType === 'discard') {
                        this.questions = [];
                        this.originalQuestionsSnapshot = JSON.stringify(this.questions);
                        this.hasChanges = false;
                        this.clearDraftStorage();
                        this.showConfirmModal = false;
                        this.modalType = '';
                        setModalScreenFocusLock();
                        this.showToastNotification('Questions discarded.', 'success');
                    } else if (this.modalType === 'save') {
                        // Set processing state to true
                        this.isProcessing = true;

                        // Use fetch to submit data with explicit questions JSON
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                        
                        const actionUrl = document.getElementById('examForm').getAttribute('action');
                        try {
                            const response = await fetch(actionUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                },
                                body: JSON.stringify({
                                    vacancy_id: '{{ $vacancy_id }}',
                                    batch: '{{ (int) ($selectedBatch ?? request('batch', 1)) }}',
                                    questions: JSON.stringify(this.questions)
                                })
                            });

                            const contentType = response.headers.get('content-type') || '';
                            const data = contentType.includes('application/json')
                                ? await response.json()
                                : { message: 'Received an unexpected server response while saving.' };

                            if (!response.ok) {
                                throw new Error(data.msg || data.message || 'Failed to save exam');
                            }

                            if (data.success || data.message) {
                                this.markCurrentStateAsSaved();
                                this.clearDraftStorage();
                                this.showConfirmModal = false;
                                this.modalType = '';
                                setModalScreenFocusLock();
                                this.showToastNotification('Questions saved successfully!', 'success');
                            } else {
                                throw new Error(data.msg || data.message || 'Failed to save exam');
                            }
                        } catch (error) {
                            console.error('Error saving exam:', error);
                            this.showConfirmModal = false;
                            this.modalType = '';
                            setModalScreenFocusLock();
                            this.showToastNotification('Error saving exam: ' + error.message, 'error');
                        } finally {
                            this.isProcessing = false;
                        }
                    }
                },

                validateForm() {
                    // Check if there are any questions
                    if (this.questions.length === 0) {
                        // It's technically valid to save an empty exam, but let's confirm
                        // Reuse modal logic here for empty exam? Or just allow it.
                        // For now, let's treat it as valid but maybe prompt?
                        // The requirement says "check if all questions... are filled". If 0 questions, it's trivially true.
                        // But usually you want at least 1.
                        // Let's allow it but maybe the user wants a warning.
                        // The prompt "You are about to save an exam with NO questions" is good.
                        // We can handle this inside the modal message dynamically if we want.
                    }

                    for (let i = 0; i < this.questions.length; i++) {
                        const q = this.questions[i];

                        // 1. Check if question text is empty (using duration field as question text based on UI)
                        if (!q.duration || q.duration.trim() === '') {
                            this.showToastNotification(`Question ${i + 1} is empty. Please enter the question text.`, 'error');
                            return false;
                        }

                        // 2. Validation for MCQ
                        if (q.type === 'MCQ') {
                            // Check if choices exist
                            if (q.choices.length < 2) {
                                this.showToastNotification(`Question ${i + 1} (MCQ) must have at least 2 options.`, 'error');
                                return false;
                            }

                            // Check empty options
                            for (let j = 0; j < q.choices.length; j++) {
                                if (!q.choices[j] || q.choices[j].trim() === '') {
                                    this.showToastNotification(`Question ${i + 1} has an empty option at position ${j + 1}.`, 'error');
                                    return false;
                                }
                            }

                            // Check if correct answer is selected
                            if (q.correctAnswer === undefined || q.correctAnswer === null || q.correctAnswer < 0 || q.correctAnswer >= q.choices.length) {
                                this.showToastNotification(`Please select a correct answer for Question ${i + 1}.`, 'error');
                                return false;
                            }
                        } else if (q.type === 'Essay') {
                            // Validate essay max score
                            if (q.essayMax === undefined || q.essayMax === null || q.essayMax === '') {
                                this.showToastNotification(`Please set a max score for Essay Question ${i + 1}.`, 'error');
                                return false;
                            }
                            const num = parseInt(q.essayMax, 10);
                            if (isNaN(num) || num < 0) {
                                this.showToastNotification(`Max score for Essay Question ${i + 1} must be 0 or greater.`, 'error');
                                return false;
                            }
                        }
                    }

                    return true;
                },

                checkForChanges() {
                    // Compare current questions with original snapshot
                    const currentSnapshot = JSON.stringify(this.questions);
                    this.hasChanges = currentSnapshot !== this.originalQuestionsSnapshot;
                }
            }
        }
    </script>
@endpush
