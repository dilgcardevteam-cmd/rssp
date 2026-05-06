@extends('layout.admin')

@section('title', ($user->name ?? 'Examinee') . ' - Answers')

@push('styles')
    <!-- Montserrat Font -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body, .font-montserrat {
            font-family: 'Montserrat', sans-serif !important;
        }
    </style>
@endpush

@section('content')
<div class="px-6 mb-6 flex justify-between items-center font-montserrat">
    <div class="flex items-center gap-4">
        <!-- Back Button -->
        <button aria-label="Go back" title="Go back"
            class="w-12 h-12 rounded-full bg-[#D8DCE3] flex justify-center items-center text-[#1E3664] hover:bg-[#c0c7d8] transition"
            onclick="window.history.back()">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="#1E3664" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        <div>
            <h2 id="applicant-name" class="text-2xl font-bold uppercase text-[#002C76]">{{ $user->name }}</h2>
            <p class="uppercase text-sm font-semibold text-gray-700 tracking-wide">Examination Answers</p>
            <p class="text-xs font-medium text-gray-500">For Position: <span class="font-semibold text-[#002C76]">{{ $vacancy->position_title }}</span></p>
        </div>
    </div>

    <div class="flex flex-col items-end space-y-1 text-sm text-gray-700 font-montserrat">
        <div class="text-right leading-tight">
            <p><span class="font-medium text-gray-600">Last Refreshed:</span> <span id="last-refreshed">--</span></p>
            <p><span class="font-medium text-gray-600">Score:</span> <span id="score">--</span></p>
        </div>
        <button onclick="renderAnswers()" aria-label="Refresh answers" title="Refresh answers"
            class="mt-2 w-10 h-10 rounded-full bg-blue-600 hover:bg-blue-700 flex justify-center items-center transition shadow-md">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <polyline points="1 4 1 10 7 10"></polyline>
                <polyline points="23 20 23 14 17 14"></polyline>
                <path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"></path>
            </svg>
        </button>
    </div>
</div>

<div id="question-container" class="px-6 pb-10 font-montserrat"></div>

<script>
    const questions = @json($questions);
    const answers = @json($answers);

    function renderAnswers() {
        const container = document.getElementById('question-container');
        const refreshedEl = document.getElementById('last-refreshed');
        const scoreEl = document.getElementById('score');
        container.innerHTML = '';

        let correctCount = 0;
        let totalMcqs = 0;

        questions.forEach((q, index) => {
            const userAnswer = (answers[q.id] || answers[q.question_id] || '').trim();
            let isCorrect = false;
            let statusBadge = '';

            if (q.type === 'mcq' || q.type === 'multiple_choice') {
                totalMcqs++;
                isCorrect = userAnswer.toLowerCase() === (q.correct_answer || '').trim().toLowerCase();
                if (isCorrect) correctCount++;
                
                statusBadge = `
                    <span class="text-sm font-semibold px-3 py-1 rounded-full ${isCorrect ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}">
                        ${isCorrect ? 'Correct' : 'Incorrect'}
                    </span>`;
            } else {
                statusBadge = `<span class="text-sm font-semibold px-3 py-1 rounded-full bg-blue-100 text-blue-700">Essay</span>`;
            }

            const div = document.createElement('div');
            div.className = 'bg-white rounded-xl border border-blue-200 shadow-md p-6 mb-6 max-w-3xl mx-auto';

            div.innerHTML = `
                <div class="flex justify-between items-center mb-1">
                    <p class="text-lg font-semibold text-gray-800">QUESTION ${index + 1} of ${questions.length}</p>
                    ${statusBadge}
                </div>
                <p class="mb-3 text-gray-700">${q.question_text || q.question}</p>
                <div class="bg-gray-100 text-gray-900 rounded px-4 py-3">
                    <p class="whitespace-pre-line"><strong class="mr-1">Answer:</strong>${userAnswer || '<span class="text-gray-400 italic">No answer provided</span>'}</p>
                </div>
            `;
            container.appendChild(div);
        });

        // Update refreshed time and score
        refreshedEl.textContent = new Date().toLocaleString();
        scoreEl.textContent = totalMcqs > 0 ? `${correctCount} / ${totalMcqs} (MCQs)` : 'N/A';
    }

    document.addEventListener('DOMContentLoaded', renderAnswers);
</script>
@include('partials.loader')
@endsection
