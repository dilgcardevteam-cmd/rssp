@extends('layout.admin')
@section('title', 'DILG - Manage Exam')
@section('content')

<main class="w-full h-full flex flex-col overflow-hidden px-4 lg:px-0">
    <!-- header -->
    <section class="flex-none flex flex-col lg:flex-row items-start lg:items-center justify-between gap-3 max-w-full border-b border-[#0D2B70] pb-2">
        <div class="flex items-center gap-4">
            <button aria-label="Back" onclick="window.location.href='{{ route('admin_exam_management') }}'" class="use-loader group">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#0D2B70] hover:opacity-80 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <h1 class="flex items-center gap-3 py-2 tracking-wide select-none">
                <span class="text-[#0D2B70] text-2xl md:text-3xl lg:text-4xl font-montserrat">Exam Overview</span>
            </h1>
        </div>

        <!-- EXAM STATUS BANNER (Compact) -->
        @php
            // Determine exam status
            $isExamActive = false;
            $isExamCompleted = false; // New flag
            $statusMessage = '';
            $statusClass = '';
            $isExamDay = false;
            $isBeforeStart = false;
            $isWithinOneHourBeforeStart = false;
            $qualifiedCount = isset($qualifiedApplicants) ? $qualifiedApplicants->count() : 0;
            $attendanceCount = isset($attendanceApplicants) ? $attendanceApplicants->count() : 0;
            $willAttendCount = isset($attendanceApplicants) ? $attendanceApplicants->where('attendance_status', 'will_attend')->count() : 0;
            $lobbyCount = count($participants ?? []);
            $activeBatch = (int) ($selectedBatch ?? request('batch', 1));
            $showQualifiedWorkflow = $activeBatch === 1;
            $questionsCount = \App\Models\ExamItems::where('vacancy_id', $vacancy->vacancy_id)->where('batch_no', $activeBatch)->count();
            $hasQuestions = $questionsCount > 0;

            if(isset($examDetails->date) && isset($examDetails->time)) {
                 $startDateTime = \Carbon\Carbon::parse($examDetails->date . ' ' . $examDetails->time);
                 
                 // Use time_end if available, otherwise fallback to duration
                 if (isset($examDetails->time_end)) {
                     $endDateTime = \Carbon\Carbon::parse($examDetails->date . ' ' . $examDetails->time_end);
                 } else {
                     $endDateTime = $startDateTime->copy()->addMinutes($examDetails->duration ?? 0);
                 }
                 
                 $now = now();
                 $isExamDay = \Carbon\Carbon::parse($examDetails->date)->isSameDay($now);
                 $isBeforeStart = $now->lt($startDateTime);
                 $oneHourBefore = $startDateTime->copy()->subHour();
                 $isWithinOneHourBeforeStart = $now->between($oneHourBefore, $startDateTime);

                 if ($now->gt($endDateTime)) {
                     // Current time is after end time
                     $isExamCompleted = true; // Set completed flag
                     $statusMessage = 'Exam Completed';
                     $statusClass = 'bg-green-100 text-green-800 border-green-400';
                 } elseif (($examDetails->is_started ?? false) || $now->between($startDateTime, $endDateTime)) {
                     // Exam is explicitly started OR current time is within window
                     $isExamActive = true;
                     $statusMessage = 'Exam in Progress';
                     $statusClass = 'bg-yellow-100 text-yellow-800 border-yellow-400';
                 } elseif ($now->lt($startDateTime)) {
                     // Current time is before start time (Future)
                     $statusMessage = 'Exam Scheduled';
                     $statusClass = 'bg-blue-100 text-blue-800 border-blue-400';
                 }
            } else {
                // Default status if details are not fully set
                $statusMessage = 'Not Scheduled';
                $statusClass = 'bg-gray-100 text-gray-800 border-gray-400';
            }
        @endphp

        @if($statusMessage)
            <div class="px-4 py-1 border-l-4 rounded shadow-sm flex items-center gap-3 {{ $statusClass }} lg:mr-4 w-full lg:w-auto">
                <div class="flex items-center gap-2">
                    @if($isExamActive)
                        <span class="relative flex h-2 w-2">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2 w-2 bg-yellow-500"></span>
                        </span>
                    @endif
                    <span class="font-bold uppercase text-xs tracking-wide">{{ $statusMessage }}</span>
                </div>
                @if($isExamActive || $isExamCompleted)
                    <span class="text-[10px] font-semibold opacity-80 hidden md:inline">Editing disabled</span>
                @endif
            </div>
        @endif
        <!-- END EXAM STATUS BANNER -->
    </section>  

    <!-- OLD SCHEDULE -->
    <section class="flex-none rounded-xl">
        <!-- Top Row: Info and Buttons -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-2 mb-2">
            <!-- Left Info -->
            <div class="text-sm text-[#002C76] font-montserrat">
                <span class="text-xl md:text-2xl lg:text-3xl font-semibold">
                    {{ $vacancy->position_title }} |

                    @if($questionsCount > 0)
                        <span class="text-xl md:text-2xl lg:text-3xl font-normal">
                            {{ $questionsCount }}-question examination
                        </span>
                    @else
                        <span class="text-xl md:text-2xl lg:text-3xl font-normal text-red-600 font-bold animate-pulse">
                            No questions yet
                        </span>
                    @endif
                </span>
                <p class="text-xs md:text-sm"><span class="font-bold">VACANCY ID:</span> {{ $vacancy->vacancy_id }}, {{ $vacancy->vacancy_type }} Position | <span class="font-bold">BATCH {{ $activeBatch }}</span></p>
                <!-- <div class="mt-1">
                    @if($questionsCount > 0)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] md:text-xs font-bold bg-blue-100 text-blue-800 border border-blue-300">
                            {{ $questionsCount }}-question examination
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] md:text-xs font-bold bg-red-100 text-red-700 border border-red-300">
                            No questions yet
                        </span>
                    @endif
                </div> -->
            </div>

            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-slate-600 uppercase tracking-wide">Batch</span>
                @for($batchNoBtn = 1; $batchNoBtn <= 3; $batchNoBtn++)
                    <a href="{{ route('admin.manage_exam', ['vacancy_id' => $vacancy->vacancy_id, 'batch' => $batchNoBtn]) }}"
                       class="px-3 py-1 rounded-md border text-sm font-semibold {{ $activeBatch === $batchNoBtn ? 'bg-[#0D2B70] text-white border-[#0D2B70]' : 'bg-white text-[#0D2B70] border-[#0D2B70]' }}">
                        {{ $batchNoBtn }}
                    </a>
                @endfor
            </div>

        </div>

        <!-- horizontal rule -->
        <div class="border-t border-gray-300 my-2"></div>

    </section>
    <div class="flex-1 flex flex-col lg:flex-row gap-4 overflow-hidden">
        
        <!-- LEFT COLUMN: Tabs + Content (70% width) -->
        <div class="w-full lg:w-[70%] flex flex-col overflow-hidden border-r border-gray-200 pr-4">
            <!-- Tab Navigation (Moved inside left col) -->
            <div class="flex-none flex gap-6 border-b border-gray-200 mb-4">
                <button id="tab-questions" onclick="switchTab('exam-questions')"
                    class="tab-button pb-2 font-bold text-[#0D2B70] border-b-2 border-[#0D2B70] transition-all duration-200 text-sm uppercase tracking-wide">
                    Exam Questions
                    <!-- @if($qualifiedApplicants->count() > 0)
                        <span class="ml-2 bg-[#0D2B70] text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full align-middle">
                            {{ $qualifiedApplicants->count() }}
                        </span>
                    @endif -->
                </button>
                @if($showQualifiedWorkflow)
                    <button id="tab-qualified" onclick="switchTab('qualified')"
                        class="tab-button pb-2 font-bold text-gray-400 border-b-2 border-transparent hover:text-[#0D2B70] transition-all duration-200 text-sm uppercase tracking-wide">
                        Qualified Applicants
                        @if($qualifiedApplicants->count() > 0)
                            <span class="ml-2 bg-[#0D2B70] text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full align-middle">
                                {{ $qualifiedApplicants->count() }}
                            </span>
                        @endif
                    </button>
                @endif
                <button id="tab-attendance" onclick="switchTab('attendance')"
                    class="tab-button pb-2 font-bold text-gray-400 border-b-2 border-transparent hover:text-[#0D2B70] transition-all duration-200 text-sm uppercase tracking-wide">
                    Attendance
                    @if($attendanceCount > 0)
                        <span id="attendanceCountBadge" class="ml-2 bg-[#0D2B70] text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full align-middle">
                            {{ $attendanceCount }}
                        </span>
                    @endif
                </button>
                <button id="tab-lobby" onclick="switchTab('lobby')"
                    class="tab-button pb-2 font-bold text-gray-400 border-b-2 border-transparent hover:text-[#0D2B70] transition-all duration-200 text-sm uppercase tracking-wide">
                    Exam Monitor
                </button>
            </div>
            
            <!-- Tab Content: Exam Questions -->
            <div id="content-exam-questions" class="tab-content flex-1 flex flex-col min-h-0 overflow-hidden">
                <div class="flex flex-col gap-4 overflow-y-auto pr-2">
                    <h2 class="text-xl font-bold text-[#0D2B70] mb-2">Examination Questions</h2>
                    @php
                        $examQuestions = \App\Models\ExamItems::where('vacancy_id', $vacancy->vacancy_id)->where('batch_no', $activeBatch)->orderBy('created_at', 'asc')->get();
                    @endphp
                    @if($examQuestions->count() > 0)
                        <ol class="list-decimal pl-6 space-y-4">
                            @foreach($examQuestions as $q)
                                <li class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                                    <div class="font-semibold text-[#0D2B70] mb-2">{!! nl2br(e($q->question)) !!}</div>
                                    @if(($q->is_essay ?? false) || ($q->type ?? '') === 'essay')
                                        <div class="italic text-slate-600">Essay Question</div>
                                    @else
                                        @php
                                            $questionChoices = is_array($q->choices)
                                                ? $q->choices
                                                : (json_decode($q->choices, true) ?? []);
                                        @endphp
                                        <ul class="list-disc pl-6 space-y-1">
                                            @foreach($questionChoices as $choice)
                                                <li class="mb-1">{!! nl2br(e($choice)) !!}</li>
                                            @endforeach
                                        </ul>
                                        @if(!empty($q->ans))
                                            <div class="mt-2 text-xs text-green-700">Correct Answer: <span class="font-bold">{!! nl2br(e($q->ans)) !!}</span></div>
                                        @endif
                                    @endif
                                    <div class="mt-2 text-xs text-gray-400">Question #{{ $loop->iteration }}</div>
                                </li>
                            @endforeach
                        </ol>
                    @else
                        <div class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center">
                            <div class="max-w-md">
                                <h3 class="text-2xl font-bold text-[#0D2B70]">There are no questions yet.</h3>
                                <p class="mt-2 text-sm text-slate-600">
                                    Add a question to start building the exam, or open the editor to manage, update, and remove questions.
                                </p>
                                <!-- <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
                                    <button type="button" onclick="handleEditClick(event)"
                                        {{ (($isExamActive && $isExamDay) || $isExamCompleted) ? 'disabled' : '' }}
                                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#002C76] px-6 py-3 font-bold text-white transition hover:scale-[1.02] disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:scale-100">
                                        <x-heroicon-o-plus class="w-5 h-5" />
                                        <span>Add Your First Question</span>
                                    </button>
                                    <span class="text-sm font-medium text-slate-400">or use the library button on the right</span>
                                </div> -->
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
            @if($showQualifiedWorkflow)
            <!-- Tab Content: Qualified Applicants -->
            <div id="content-qualified" class="tab-content hidden flex-1 flex flex-col min-h-0 overflow-hidden">
                <div class="flex-none flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                    <!-- Search Bar -->
                    <form onsubmit="return false;" class="relative w-full max-w-xs">
                        <input id="searchInputQualified" type="search" placeholder="Search applicants" aria-label="Search"
                            class="pl-10 pr-4 py-1.5 rounded-full border border-[#0D2B70] placeholder:text-[#7D93B3] placeholder:font-semibold text-[#0D2B70] focus:outline-none focus:ring-2 focus:ring-[#0D2B70] focus:ring-offset-1 w-full" />
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="w-5 h-5 text-[#7D93B3] absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                        </svg>
                    </form>
                </div>

                <!-- Table Container -->
                <div class="flex-1 flex flex-col min-h-0 overflow-hidden border border-[#0D2B70] rounded-xl">
                    <div class="flex-1 overflow-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-[#0D2B70] text-white sticky top-0 z-10">
                                <tr>
                                    <th class="py-3 px-6 font-normal">Name</th>
                                    <th class="py-3 px-6 font-normal">Email</th>
                                    <th class="py-3 px-6 font-normal">Application Date</th>
                                    <th class="py-3 px-6 font-normal text-center">Attendance</th>
                                    <th class="py-3 px-6 font-normal text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="qualified-applicants-list" class="divide-y divide-[#0D2B70]">
                                @forelse ($qualifiedApplicants as $applicant)
                                    <tr class="text-[#0D2B70] select-none hover:bg-blue-50 transition-colors duration-200">
                                        <td class="py-2.5 px-6 font-semibold">{{ $applicant['name'] }}</td>
                                        <!-- <td class="py-2.5 px-6">{{ $applicant['email'] }}</td> -->
                                        <td class="py-2.5 px-6 max-w-[200px] truncate"> {{ $applicant['email'] }}</td>
                                        <td class="py-2.5 px-6">{{ $applicant['application_date'] }}</td>
                                        <td class="py-2.5 px-6 text-center">
                                            @if($applicant['has_attendance_response'])
                                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 flex items-center justify-center gap-1"
                                                    title="Confirmed: {{ $applicant['attendance_responded_at'] ?: 'N/A' }}">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    Confirmed
                                                </span>
                                            @elseif($applicant['attendance_prompt_sent'])
                                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 flex items-center justify-center gap-1"
                                                    title="Sent: {{ $applicant['attendance_prompt_sent_at'] ? \Carbon\Carbon::parse($applicant['attendance_prompt_sent_at'])->format('M d, Y h:i A') : 'N/A' }}">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    Sent
                                                </span>
                                            @else
                                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
                                                    Not Sent
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 px-6 text-center">
                                            <a
                                                href="{{ route('admin.applicant_status', ['user_id' => $applicant['user_id'], 'vacancy_id' => $applicant['vacancy_id']]) }}"
                                                target="_blank"
                                                class="text-[#0D2B70] border border-[#0D2B70] font-bold py-1 px-4 rounded-md text-sm transition-all duration-300 hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md flex items-center gap-2 mx-auto">
                                                <x-heroicon-o-eye class="w-4 h-4" />
                                                <span>View</span>
                                            </a>
                                            <!-- <button
                                                onclick="window.location.href='{{ route('admin.applicant_status', ['user_id' => $applicant['user_id'], 'vacancy_id' => $applicant['vacancy_id']]) }}'"
                                                class="text-[#0D2B70] border border-[#0D2B70] font-bold py-1 px-4 rounded-md text-sm transition-all duration-300 hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md flex items-center gap-2 mx-auto">
                                                <x-heroicon-o-eye class="w-4 h-4" />
                                                <span>View</span>
                                            </button> -->
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-10 text-gray-500 text-xl">
                                            No qualified applicants found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <div id="content-attendance" class="tab-content hidden flex-1 flex flex-col min-h-0 overflow-hidden">
                @if(!$showQualifiedWorkflow)
                    <div class="mb-3 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-[#0D2B70]">
                        <p><span class="font-bold">Batch {{ $activeBatch }} Schedule:</span> {{ $examDetails->date ?? 'Not set' }} {{ $examDetails->time ?? '' }}</p>
                        <p class="mt-1"><span class="font-bold">Message:</span> {{ $examDetails->message ?? 'No message yet.' }}</p>
                        <p class="mt-1 text-xs text-slate-600">Use the right panel to update time/message and click <span class="font-semibold">Save and Notify Applicants</span>.</p>
                    </div>
                @endif
                <div class="flex-1 flex flex-col min-h-0 overflow-hidden border border-[#0D2B70] rounded-xl">
                    <div class="flex-1 overflow-auto">
                        <table class="w-full table-fixed border-collapse text-left">
                            <thead class="bg-[#0D2B70] text-white sticky top-0 z-10">
                                <tr>
                                    <th class="w-[24%] py-3 px-6 font-normal">Name</th>
                                    <th class="w-[18%] py-3 px-6 font-normal text-center">Attendance</th>
                                    <th class="w-[20%] py-3 px-6 font-normal text-center">Responded</th>
                                    <th class="w-[24%] py-3 px-6 font-normal">Remark</th>
                                    <th class="w-[14%] py-3 px-6 font-normal text-center">Override</th>
                                </tr>
                            </thead>
                            <tbody id="attendance-applicants-list" class="divide-y divide-[#0D2B70]">
                                @forelse ($attendanceApplicants as $applicant)
                                    <tr id="attendance-row-{{ $applicant['user_id'] }}" class="group text-[#0D2B70] hover:bg-blue-50 transition-colors duration-200">
                                        <td class="w-[24%] py-2.5 px-6 font-semibold whitespace-nowrap overflow-hidden text-ellipsis">{{ $applicant['name'] }}</td>
                                        <td class="w-[18%] py-2.5 px-6 text-center">
                                            <span class="attendance-status-badge inline-flex whitespace-nowrap px-3 py-1 rounded-full text-xs font-semibold {{ $applicant['attendance_badge_class'] }}">
                                                {{ $applicant['attendance_label'] }}
                                            </span>
                                        </td>
                                        <td class="w-[20%] py-2.5 px-6 text-center text-sm text-slate-600 whitespace-nowrap">
                                            {{ $applicant['attendance_responded_at'] ?: '-' }}
                                        </td>
                                        <td class="w-[24%] py-2.5 px-6 text-sm text-slate-600">
                                            @php
                                                $attendanceRemark = trim((string) ($applicant['attendance_remark'] ?: 'None provided'));
                                                $hasAttendanceRemark = filled($applicant['attendance_remark']);
                                            @endphp
                                            <button
                                                type="button"
                                                class="attendance-remark-trigger inline-flex max-w-full items-center rounded-full border px-3 py-1 text-xs font-medium {{ $hasAttendanceRemark ? 'border-slate-200 bg-slate-50 text-slate-700 shadow-sm hover:border-[#0D2B70]/30 hover:bg-white' : 'border-slate-200 bg-slate-100 text-slate-500 italic' }}"
                                                data-tooltip-title="Attendance Remark"
                                                data-tooltip-content="{{ $attendanceRemark }}"
                                                aria-label="View full attendance remark"
                                            >
                                                <span class="block max-w-full truncate whitespace-nowrap">{{ $attendanceRemark }}</span>
                                            </button>
                                        </td>
                                        <td class="w-[14%] py-2.5 px-6">
                                            <div class="flex items-center justify-center gap-2">
                                                @php
                                                    $isWillAttend = ($applicant['attendance_status'] ?? null) === 'will_attend';
                                                    $isWillNotAttend = ($applicant['attendance_status'] ?? null) === 'will_not_attend';
                                                @endphp
                                                <button type="button"
                                                    onclick="overrideAttendanceStatus({{ $applicant['user_id'] }}, 'will_attend')"
                                                    title="{{ $isWillAttend ? 'Already marked as Will Attend' : 'Mark as Will Attend' }}"
                                                    aria-label="Mark as Will Attend"
                                                    {{ $isWillAttend ? 'disabled' : '' }}
                                                    class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-green-700 text-green-700 transition hover:bg-green-700 hover:text-white disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-green-700">
                                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                        <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-7.2 7.2a1 1 0 01-1.415.005L3.3 9.206a1 1 0 111.4-1.428l4.08 4.002 6.5-6.49a1 1 0 011.424 0z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                                <button type="button"
                                                    onclick="overrideAttendanceStatus({{ $applicant['user_id'] }}, 'will_not_attend', @js($applicant['attendance_remark']))"
                                                    title="{{ $isWillNotAttend ? 'Already marked as Will Not Attend' : 'Mark as Will Not Attend' }}"
                                                    aria-label="Mark as Will Not Attend"
                                                    {{ $isWillNotAttend ? 'disabled' : '' }}
                                                    class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-red-700 text-red-700 transition hover:bg-red-700 hover:text-white disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-red-700">
                                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                        <path fill-rule="evenodd" d="M5.293 5.293a1 1 0 011.414 0L10 8.586l3.293-3.293a1 1 0 111.414 1.414L11.414 10l3.293 3.293a1 1 0 01-1.414 1.414L10 11.414l-3.293 3.293a1 1 0 01-1.414-1.414L8.586 10 5.293 6.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-10 text-center text-gray-500 text-xl">
                                            No attendance responses yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tab Content: EXAM MONITOR (Phase 2 - Participants Table) -->
            <div id="content-lobby" class="tab-content hidden flex-1 flex flex-col min-h-0 overflow-hidden border border-[#0D2B70] rounded-xl">
                 <div class="flex-none bg-[#0D2B70] text-white">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-[#0D2B70] text-white">
                            <tr>
                                <th class="py-2.5 px-3 md:py-3 md:px-6 text-left text-xs md:text-sm tracking-wider w-[25%] md:w-[25%]">Name</th>
                                <th class="py-2.5 px-3 md:py-3 md:px-6 text-center text-xs md:text-sm tracking-wider w-[15%] md:w-[15%]">MC</th>
                                <th class="py-2.5 px-3 md:py-3 md:px-6 text-center text-xs md:text-sm tracking-wider w-[15%] md:w-[15%]">Essay</th>
                                <th class="py-2.5 px-3 md:py-3 md:px-6 text-center text-xs md:text-sm tracking-wider w-[20%]">Status</th>
                                <th class="py-2.5 px-3 md:py-3 md:px-6 text-center text-xs md:text-sm tracking-wider w-[25%]">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>

                <!-- EXAM MONITOR TABLE -->
                <div class="flex-1 overflow-y-auto bg-white">
                    <div class="flex items-center justify-between p-2 bg-gray-50 border-b border-[#0D2B70]">
                        <div class="flex items-center gap-3">
                            <span id="lobbyLastUpdated" class="text-xs text-gray-400"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button id="pauseExamBtn" onclick="toggleExamPause()" @if(!($isExamActive ?? false)) disabled @endif
                                title="@if($isExamActive) Pause exam @else Exam not started @endif"
                                class="text-xs bg-amber-500 border border-amber-600 text-white hover:bg-amber-600 px-3 py-1 rounded transition-colors duration-200 flex items-center gap-1 @if(!($isExamActive ?? false)) opacity-50 cursor-not-allowed @endif">
                                <x-heroicon-o-pause class="w-3 h-3" />
                                Pause Exam
                            </button>
                            <button id="refreshLobbyBtn" onclick="fetchLobbyData(true)" class="text-xs bg-white border border-[#0D2B70] text-[#0D2B70] hover:bg-[#0D2B70] hover:text-white px-3 py-1 rounded transition-colors duration-200 flex items-center gap-1">
                                <x-heroicon-o-arrow-path class="w-3 h-3" />
                                Refresh Now
                            </button>
                        </div>
                    </div>
                    <table class="w-full text-left border-collapse">
                        <tbody id="exam-lobby-tbody" class="bg-white divide-y divide-gray-200">
                            @if (count($participants) > 0)
                                @foreach ($participants as $index => $p)
                                <tr class="hover:bg-blue-50 transition-colors duration-200">
                                    <!-- Name -->
                                    <td class="py-2.5 px-3 md:py-3 md:px-6 text-[#0D2B70] font-semibold text-xs md:text-sm w-[25%] md:w-[25%]">
                                        {{ $user_name[$index] ?? 'Unknown User' }}
                                                                            <div class="mt-1 text-[10px] md:text-xs text-gray-600 font-medium">
                                        <span class="whitespace-nowrap">Switches: {{ (int) ($p->tab_switch_count ?? $p->tab_violations ?? 0) }}</span>
                                        <span class="mx-1 text-gray-400">|</span>
                                        <span class="whitespace-nowrap">Tamper: {{ (int) ($p->tamper_logs_count ?? 0) }}</span>
                                    </div>
                                    </td>


                                    <!-- MC Score -->
                                    <td class="py-2.5 px-3 md:py-3 md:px-6 text-center text-[#0D2B70] font-medium text-xs md:text-sm w-[15%] md:w-[15%]">
                                        {{ $p->mc_score_str ?? '-' }}
                                    </td>

                                    <!-- Essay Score -->
                                    <td class="py-2.5 px-3 md:py-3 md:px-6 text-center text-[#0D2B70] font-medium text-xs md:text-sm w-[15%] md:w-[15%]">
                                        {{ $p->essay_score_str ?? '-' }}
                                    </td>

                                    <!-- Status -->
                                    <td class="py-2.5 px-3 md:py-3 md:px-6 text-center w-[20%]">
                                        <div class="inline-flex items-center gap-1 md:gap-2 text-[#0D2B70] font-medium text-xs md:text-sm">
                                            @php
                                                $statusColors = [
                                                    'ready' => '#4ade80',        // green-400
                                                    'in-progress' => '#facc15',  // yellow-400
                                                    'submitted' => '#3b82f6',    // blue-500
                                                    'pending' => '#f75555',      // red
                                                ];

                                                $status = strtolower($p->status ?? 'pending');
                                                $color = $statusColors[$status] ?? '#9ca3af'; // gray-400 as default
                                            @endphp
                                            <i class="fa-solid fa-circle text-xs" style="color: {{ $color }}"></i>
                                            <span>{{ $p->status ?? 'Pending' }}</span>
                                        </div>
                                    </td>

                                    <!-- Action Button -->
                                     <td class="py-2.5 px-3 md:py-3 md:px-6 text-center">
                                        @php
                                            $resumeAction = (array) ($p->resume_action ?? []);
                                        @endphp
                                        <div class="inline-flex flex-row flex-nowrap items-center justify-center gap-2">
                                            <a href="{{ route('admin.view_exam', ['vacancy_id' => $p->vacancy_id, 'user_id' => $p->user_id]) }}?batch={{ $activeBatch }}" target="_blank"
                                                class="text-[#0D2B70] border border-[#0D2B70] font-bold py-1.5 px-3 md:py-2 md:px-6 rounded-md text-xs md:text-sm
                                                    transition-all duration-150 ease-[cubic-bezier(0.4,0,0.2,1)]
                                                    hover:scale-105 hover:bg-[#002C76] hover:text-white hover:shadow-md inline-flex items-center gap-1 md:gap-2 whitespace-nowrap shrink-0">
                                                <x-heroicon-o-eye class="w-3 h-3 md:w-4 md:h-4" />
                                                <span class="hidden sm:inline">View</span>
                                            </a>
                                            <button type="button"
                                                onclick="toggleApplicantPause({{ $p->user_id }})"
                                                class="inline-flex items-center gap-1 rounded-md border border-amber-600 px-3 py-1.5 text-xs font-bold text-amber-700 transition-all duration-150 hover:bg-amber-600 hover:text-white">
                                                <x-heroicon-o-pause class="w-3 h-3" />
                                                <span>{{ !empty($p->exam_paused_at) ? 'Resume' : 'Pause' }}</span>
                                            </button>
                                            @if(!empty($resumeAction['can_resume']))
                                                <button
                                                    type="button"
                                                    onclick="triggerResumeExamConfirm({{ $p->user_id }})"
                                                    title="Resume exam with {{ $resumeAction['remaining_label'] ?? 'saved' }} remaining"
                                                    class="border border-emerald-700 bg-emerald-600 text-white font-bold py-1.5 px-3 md:py-2 md:px-4 rounded-md text-xs md:text-sm transition-all duration-150 hover:scale-105 hover:bg-emerald-700 hover:shadow-md inline-flex items-center gap-1 md:gap-2 whitespace-nowrap shrink-0">
                                                    <svg class="w-3 h-3 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-4.586-2.65A1 1 0 009 9.385v5.23a1 1 0 001.166.967l4.586-2.65a1 1 0 000-1.732z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <span>Resume</span>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                    <!-- <td class="py-3 px-3 md:py-4 md:px-6 text-center w-[25%]">
                                        <button target="_blank" onclick="window.location.href='{{ route('admin.view_exam', ['vacancy_id' => $p->vacancy_id, 'user_id' => $p->user_id]) }}'"
                                            class="text-[#0D2B70] border border-[#0D2B70] font-bold py-1.5 px-3 md:py-2 md:px-6 rounded-md text-xs md:text-sm
                                                transition-all duration-150 ease-[cubic-bezier(0.4,0,0.2,1)]
                                                hover:scale-105 hover:bg-[#002C76] hover:text-white hover:shadow-md inline-flex items-center gap-1 md:gap-2">
                                            <x-heroicon-o-eye class="w-3 h-3 md:w-4 md:h-4" />
                                            <span class="hidden sm:inline">View</span>
                                        </button>
                                    </td> -->
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                            <td colspan="5" class="py-10 text-center text-gray-500">
                                                <p class="text-xl font-semibold">There are no participants yet.</p>
                                            </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

        </div>



        <!--     scheduling form, buttons -->
        <!-- RIGHT COLUMN: Scheduling form -->
        <div class="w-full lg:w-[30%] lg:min-w-[320px] flex flex-col mt-4 lg:mt-0 pl-2 overflow-y-auto">
            <form id="examDetailsForm" class="flex flex-col gap-4">
            @csrf

            <!-- PANEL 1: EXAM QUESTIONS -->
            <div id="panel-questions" class="flex flex-col gap-3">
                <button type="button" onclick="handleEditClick(event)"
                        {{ (($isExamActive && $isExamDay) || $isExamCompleted) ? 'disabled' : '' }}
                        class="w-full py-3 bg-white border-2 border-[#0D2B70] rounded-lg text-[#0D2B70] font-bold text-sm hover:scale-[1.02] flex items-center justify-center gap-2 transition-transform disabled:opacity-50 disabled:hover:scale-100">
                    Manage Questions
                </button>
            </div>

            <!-- PANEL 2: SCHEDULE FORM (Qualified Applicants tab) -->
            <div id="panel-schedule" class="flex flex-col gap-3 hidden">
                <span class="text-xl text-[#0D2B70] font-bold border-b border-gray-200 pb-2 mb-1">
                    {{ $showQualifiedWorkflow ? 'Schedule Exam' : 'Attendance Batch Schedule' }}
                </span>

                <div class="flex flex-col">
                    <label for="venue" class="text-[#0D2B70] font-bold text-xs mb-1">Venue <span class="text-red-500">*</span></label>
                    <input type="text" id="venue" name="place" required
                        value="{{ $examDetails->place ?? '' }}"
                        {{ ($isExamActive || $isExamCompleted || ($examDetails && $examDetails->details_saved)) ? 'disabled' : '' }}
                        class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-[#0D2B70] focus:border-[#0D2B70] placeholder-gray-400 disabled:bg-gray-100"
                        placeholder="Enter venue" />
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="flex flex-col">
                        <label for="date" class="text-[#0D2B70] font-bold text-xs mb-1">Date <span class="text-red-500">*</span></label>
                        <input type="date" id="date" name="date" required
                            value="{{ $examDetails->date ?? '' }}"
                            {{ ($isExamActive || $isExamCompleted || ($examDetails && $examDetails->details_saved)) ? 'disabled' : '' }}
                            class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-[#0D2B70] disabled:bg-gray-100" />
                    </div>

                    <div class="flex flex-col">
                        <label for="time" class="text-[#0D2B70] font-bold text-xs mb-1">Time <span class="text-red-500">*</span></label>
                        <input class="font-sm h-full" type="time" id="time" name="time" required
                            value="{{ $examDetails->time ?? '' }}"
                            {{ ($isExamActive || $isExamCompleted || ($examDetails && $examDetails->details_saved)) ? 'disabled' : '' }}
                            class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-[#0D2B70] disabled:bg-gray-100" />
                    </div>
                </div>

                <div class="flex flex-col">
                    <label for="message" class="text-[#0D2B70] font-bold text-xs mb-1">Message <span class="text-red-500">*</span></label>
                    <textarea id="message" name="message" rows="3" required
                        {{ ($isExamActive || $isExamCompleted || ($examDetails && $examDetails->details_saved)) ? 'disabled' : '' }}
                        class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-[#0D2B70] disabled:bg-gray-100 placeholder-gray-400 resize"
                        placeholder="Enter message for applicants">{{ $examDetails->message ?? '' }}</textarea>
                </div>

                <input type="hidden" id="time_end_hidden" name="time_end" value="{{ $examDetails->time_end ?? '' }}">
                <input type="hidden" id="duration" name="duration" value="{{ $examDetails->duration ?? '' }}">

                <div class="flex flex-col gap-2 mt-4">
                    <div class="flex flex-col">
                        <div>
                            <button type="submit" id="saveNotifyButton" name="action" value="save_notify" 
                                    {{ ($isExamActive || $isExamCompleted || ($examDetails && $examDetails->details_saved) || ($qualifiedCount < 1)) ? 'disabled' : '' }}
                                    class="w-full py-2 bg-[#0D2B70] border-2 border-[#0D2B70] rounded-lg text-white font-bold text-sm hover:scale-[1.02] flex items-center justify-center gap-2 transition-transform disabled:opacity-50 disabled:hover:scale-100">
                                Save and Notify Applicants
                            </button>
                        </div>
                        <div>
                            <p id="scheduleNotifyMeta" class="text-xs text-gray-600 italic text-left mt-1 {{ $scheduleNotifiedAt ? '' : 'hidden' }}">
                                @if($scheduleNotifiedAt)
                                    Sent by: <b id="scheduleNotifySender">{{ $scheduleNotifiedByName ?? 'An admin' }}</b>
                                    on <b id="scheduleNotifyTime">{{ \Carbon\Carbon::parse($scheduleNotifiedAt)->format('M d, h:i A') }}</b>
                                @endif
                            </p>
                        </div>
                    </div>

                    <button type="button" onclick="handleEditClick(event)"
                            {{ (($isExamActive && $isExamDay) || $isExamCompleted) ? 'disabled' : '' }}
                            class="w-full py-2 bg-white border-2 border-[#0D2B70] rounded-lg text-[#0D2B70] font-bold text-sm hover:scale-[1.02] flex items-center justify-center gap-2 transition-transform disabled:opacity-50 disabled:hover:scale-100">
                        Edit Questions
                    </button>
                </div>
            </div>

            <!-- PANEL 2: EXAM MONITOR (Hidden initially) -->
            <div id="panel-monitor" class="flex flex-col gap-3 hidden">
                <!-- Header -->
                <span class="text-xl text-[#0D2B70] font-bold border-b border-gray-200 pb-2 mb-1">
                    Exam Monitor
                </span>

                <!-- START (Autofilled) & END -->
                <div class="grid grid-cols-2 gap-2">
                    <div class="flex flex-col">
                        <label class="text-[#0D2B70] font-bold text-xs mb-1">Start</label>
                        <input type="text" id="monitor_start" readonly
                            value="{{ !empty($examDetails->time) ? \Carbon\Carbon::createFromFormat('H:i:s', strlen($examDetails->time) === 5 ? $examDetails->time . ':00' : $examDetails->time)->format('g:i A') : '--:--' }}"
                            class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm bg-gray-100 text-gray-600 cursor-not-allowed" />
                        <p class="text-[10px] text-red-500 mt-0.5">*start field is autofilled*</p>
                    </div>
                    <div class="flex flex-col">
                        <label for="monitor_end" class="text-[#0D2B70] font-bold text-xs mb-1">End <span class="text-red-500">*</span></label>
                        <input type="time" id="monitor_end" 
                            value="{{ $examDetails->time_end ?? '' }}"
                            {{ ($isExamActive || $isExamCompleted) ? 'disabled' : '' }}
                            class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-[#0D2B70] disabled:bg-gray-100" />
                    </div>
                </div>

                <!-- ACTION BUTTONS: MONITOR -->
                <div class="flex flex-col gap-2 mt-4">
                    <button type="button" id="openMonitorRecipientModalButton" onclick="openMonitorRecipientModal()"
                            {{ ($qualifiedCount < 1 || $examDetails->link_sent || $isExamActive || $isExamCompleted || !$isExamDay || ($willAttendCount < 1)) ? 'disabled' : '' }}
                            title="{{ $qualifiedCount < 1 ? 'No qualified applicants are available yet.' : 'Choose which applicants should receive exam links.' }}"
                            class="w-full py-2 bg-white border-2 border-[#0D2B70] rounded-lg text-[#0D2B70] font-bold text-sm hover:scale-[1.02] flex items-center justify-center gap-2 transition-transform disabled:opacity-50 disabled:hover:scale-100">
                        <x-heroicon-o-user-group class="w-4 h-4" />
                        Send Selected Links
                    </button>
                    <button type="button" id="sendLinkButton" onclick="triggerSendLinkConfirm('{{ $vacancy->vacancy_id }}')" 
                            {{ (!$examDetails || !$examDetails->details_saved || $examDetails->link_sent || $isExamActive || $isExamCompleted || !$isExamDay || ($willAttendCount < 1)) ? 'disabled' : '' }}
                            title="{{ $willAttendCount < 1 ? 'No applicants are marked as Will Attend yet.' : 'Send exam links to applicants marked as Will Attend.' }}"
                            class="w-full py-2 bg-[#0D2B70] border-2 border-[#0D2B70] rounded-lg text-white font-bold text-sm hover:scale-[1.02] flex items-center justify-center gap-2 transition-transform disabled:opacity-50 disabled:hover:scale-100">
                        Send Link via Email
                    </button>
                    <p id="sendLinkMeta" class="text-xs text-gray-600 italic text-left mt-1 {{ $linkSentAt ? '' : 'hidden' }}">
                        @if($linkSentAt)
                            Sent by: <b id="sendLinkSender">{{ $linkSentByName ?? 'An admin' }}</b>
                            on <b id="sendLinkTime">{{ \Carbon\Carbon::parse($linkSentAt)->format('M d, h:i A') }}</b>
                        @endif
                    </p>
                    
                    <button type="button" id="startExamButton" onclick="triggerStartExamConfirm('{{ $vacancy->vacancy_id }}')" 
                            {{ (!$examDetails || !$examDetails->link_sent || $isExamActive || $isExamCompleted || !$isExamDay || !$hasQuestions || ($lobbyCount < 1)) ? 'disabled' : '' }}
                            class="w-full py-2 bg-white border-2 border-[#0D2B70] rounded-lg text-[#0D2B70] font-bold text-sm hover:scale-[1.02] flex items-center justify-center gap-2 transition-transform disabled:opacity-50 disabled:hover:scale-100">
                        Start Exam
                    </button>
                </div>
            </div>

            <!-- PANEL 3: ATTENDANCE SUMMARY (Hidden initially) -->
            <div id="panel-attendance" class="flex flex-col gap-3 hidden">
                <span class="text-xl text-[#0D2B70] font-bold border-b border-gray-200 pb-2 mb-1">
                    Attendance Summary
                </span>

                <div class="grid gap-3">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Responses</p>
                        <p id="attendanceResponsesCount" class="mt-2 text-3xl font-bold text-[#0D2B70]">{{ $attendanceCount }}</p>
                    </div>
                    <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-green-700">Will Attend</p>
                        <p id="attendanceWillAttendCount" class="mt-2 text-3xl font-bold text-green-800">{{ $willAttendCount }}</p>
                    </div>
                    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-red-700">Will Not Attend</p>
                        <p id="attendanceWillNotAttendCount" class="mt-2 text-3xl font-bold text-red-800">{{ $attendanceCount - $willAttendCount }}</p>
                    </div>
                </div>
            </div>

            </form>
        </div>
                                        
    </div>


    @include('partials.loader')
    <div id="monitorRecipientModal" class="hidden fixed inset-0 z-[10040] backdrop-blur-sm p-4 opacity-0 transition-opacity duration-200">
        <div class="flex min-h-full items-center justify-center">
            <div id="monitorRecipientModalPanel" class="flex max-h-[85vh] w-full max-w-[54rem] translate-y-4 transform flex-col overflow-hidden rounded-2xl bg-white opacity-0 shadow-2xl ring-1 ring-black/5 transition duration-200 ease-out sm:scale-95">
                <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4 sm:px-6">
                    <div>
                        <h3 class="text-xl font-bold text-[#0D2B70]">Send Exam Links</h3>
                        <p class="mt-1 text-sm text-slate-500">Select applicants marked as Will Attend, then send their exam lobby links from this list.</p>
                    </div>
                    <button type="button" onclick="closeMonitorRecipientModal()"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                        aria-label="Close send exam links modal">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="flex-1 overflow-hidden px-5 pb-5 pt-4 sm:px-6">
                    <div class="mb-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-slate-500">
                            Applicants who are still waiting on attendance confirmation will stay disabled here.
                        </p>
                        <div class="flex items-center justify-end gap-3">
                            <span id="monitorSelectedCount" class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-[#0D2B70]">0 selected</span>
                            <button id="monitorNotifySelectedButton" type="button" onclick="notifySelected()" disabled
                                class="inline-flex items-center gap-2 whitespace-nowrap rounded-lg bg-[#0D2B70] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#002C76] disabled:cursor-not-allowed disabled:opacity-50">
                                <x-heroicon-o-paper-airplane class="w-4 h-4 transform rotate-90" />
                                Send Link
                            </button>
                        </div>
                    </div>
                    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                        <div class="max-h-[48vh] overflow-auto">
                            <table class="w-full table-fixed border-collapse text-left">
                                <thead class="sticky top-0 z-10 bg-slate-100 text-[#0D2B70]">
                                    <tr>
                                        <th class="w-14 py-3 px-4 font-normal">
                                            <input type="checkbox" id="monitorSelectAll" onchange="toggleSelectAll(this)"
                                                class="w-4 h-4 rounded border-gray-300 text-[#0D2B70] focus:ring-[#0D2B70] cursor-pointer">
                                        </th>
                                        <th class="w-[40%] py-3 px-4 font-normal">Name</th>
                                        <th class="w-[24%] py-3 px-4 font-normal text-center">Attendance</th>
                                        <th class="w-[24%] py-3 px-4 font-normal text-center">Exam Link</th>
                                    </tr>
                                </thead>
                                <tbody id="monitor-recipient-list" class="divide-y divide-slate-200">
                                    @foreach ($qualifiedApplicants as $applicant)
                                        <tr class="text-[#0D2B70] hover:bg-slate-50 transition-colors duration-150">
                                            <td class="py-3 px-4">
                                                <input type="checkbox" value="{{ $applicant['id'] }}"
                                                    data-user-id="{{ $applicant['user_id'] }}"
                                                    data-can-receive-link="{{ $applicant['can_receive_exam_link'] ? '1' : '0' }}"
                                                    {{ $applicant['can_receive_exam_link'] ? '' : 'disabled' }}
                                                    onchange="updateSelectedCount()"
                                                    title="{{ $applicant['can_receive_exam_link'] ? 'Eligible to receive exam link' : 'Only applicants marked as Will Attend can receive the exam link' }}"
                                                    class="monitor-applicant-checkbox w-4 h-4 rounded border-gray-300 text-[#0D2B70] focus:ring-[#0D2B70] cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                                            </td>
                                            <td class="py-3 px-4 font-semibold truncate">{{ $applicant['name'] }}</td>
                                            <td class="py-3 px-4 text-center">
                                                <span class="inline-flex items-center justify-center rounded-full px-3 py-1 text-xs font-semibold {{ $applicant['attendance_badge_class'] }}">
                                                    {{ $applicant['attendance_label'] }}
                                                </span>
                                            </td>
                                            <td class="py-3 px-4 text-center">
                                                @if($applicant['is_read'])
                                                    <span class="inline-flex items-center justify-center rounded-full px-3 py-1 text-xs font-semibold bg-green-100 text-green-800">
                                                        Opened
                                                    </span>
                                                @elseif($applicant['link_sent'])
                                                    <span class="inline-flex items-center justify-center rounded-full px-3 py-1 text-xs font-semibold bg-blue-100 text-blue-800">
                                                        Sent
                                                    </span>
                                                @elseif($applicant['can_receive_exam_link'])
                                                    <span class="inline-flex items-center justify-center rounded-full px-3 py-1 text-xs font-semibold bg-amber-100 text-amber-800">
                                                        Ready to Send
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center justify-center rounded-full px-3 py-1 text-xs font-semibold bg-slate-100 text-slate-500">
                                                        Waiting
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Confirmation Modals -->
    <x-confirm-modal 
        title="Save & Notify Applicants"
        message="Save exam details and notify qualified applicants?"
        event="open-save-notify-confirm"
        confirm="confirm-save-notify"
    />
    <x-confirm-modal 
        title="Send Exam Links"
        message="Send exam lobby links to all applicants marked as Will Attend?"
        event="open-send-link-confirm"
        confirm="confirm-send-link"
    />
    <x-confirm-modal 
        title="Start Exam"
        message="Start the exam now? All ready participants will enter the exam."
        event="open-start-exam-confirm"
        confirm="confirm-start-exam"
    />
    <x-confirm-modal
        title="Resume Exam"
        message="Reopen this applicant's submitted exam attempt from saved progress and restore the remaining time? Use this when a submission happened accidentally."
        event="open-resume-exam-confirm"
        confirm="confirm-resume-exam"
        confirmText="Resume"
        tone="success"
    />
</main>
<div id="attendanceRemarkTooltip"
    class="pointer-events-none fixed z-[1200] hidden w-[24rem] max-w-[calc(100vw-2rem)] rounded-2xl border border-slate-200 bg-white p-4 text-sm text-slate-700 shadow-2xl ring-1 ring-slate-100">
    <p id="attendanceRemarkTooltipTitle" class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Attendance Remark</p>
    <p id="attendanceRemarkTooltipContent" class="mt-2 whitespace-pre-line break-words leading-relaxed"></p>
</div>
@php
    $examRealtimeConnection = (string) config('broadcasting.default');
    $examRealtimeOptions = (array) data_get(config('broadcasting.connections'), $examRealtimeConnection . '.options', []);
    $examRealtimeKey = (string) data_get(config('broadcasting.connections'), $examRealtimeConnection . '.key', '');
    $examRealtimeEnabled = in_array($examRealtimeConnection, ['reverb', 'pusher'], true) && $examRealtimeKey !== '';
    $examRealtimeConfig = [
        'enabled' => $examRealtimeEnabled,
        'key' => $examRealtimeKey,
        'wsHost' => (string) ($examRealtimeOptions['host'] ?? request()->getHost()),
        'wsPort' => (int) ($examRealtimeOptions['port'] ?? 80),
        'wssPort' => (int) ($examRealtimeOptions['port'] ?? 443),
        'forceTLS' => (bool) ($examRealtimeOptions['useTLS'] ?? request()->isSecure()),
    ];
@endphp
@if ($examRealtimeEnabled)
    <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
@endif
<script>
    // Confirmation wrappers
    function triggerSendLinkConfirm(vacancyId) {
        window._pendingVacancyId = vacancyId;
        window.dispatchEvent(new CustomEvent('open-send-link-confirm'));
    }
    function triggerStartExamConfirm(vacancyId) {
        window._pendingVacancyId = vacancyId;
        window.dispatchEvent(new CustomEvent('open-start-exam-confirm'));
    }
    function triggerResumeExamConfirm(userId) {
        window._pendingResumeUserId = userId;
        window.dispatchEvent(new CustomEvent('open-resume-exam-confirm'));
    }
    // Confirm handlers
    window.addEventListener('confirm-send-link', () => {
        const id = window._pendingVacancyId;
        if (id) sendExamLink(id);
    });
    window.addEventListener('confirm-start-exam', () => {
        const id = window._pendingVacancyId;
        if (id) startExam(id);
    });
    window.addEventListener('confirm-resume-exam', () => {
        const userId = window._pendingResumeUserId;
        if (userId) resumeApplicantExam(userId);
    });

    // Send exam link via email (executes after confirmation)
    function sendExamLink(vacancyId) {

        const sendLinkButton = document.getElementById('sendLinkButton');
        const originalText = sendLinkButton.innerHTML;
        sendLinkButton.disabled = true;
        sendLinkButton.innerHTML = '<span>Sending...</span>';

        fetch(`/admin/exam_management/${vacancyId}/notify?${batchQuery}`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                "Accept": "application/json"
            },
            body: JSON.stringify({
                vacancy_id: vacancyId
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => { 
                    throw new Error(data.message || 'Failed to send links');
                });
            }
            return response.json();
        })
        .then(data => {
            if(data.success) {
                showAppToast(data.message || "Exam links sent successfully.");
                updateSendLinkMeta(currentAdminDisplayName, data.link_sent_at || data.notified_at || null);
                // Mark links as sent on client and update Start Exam state
                linkSentClient = true;
                updateStartButtonState();
                refreshMonitorRecipients();
                // Keep Send Link button disabled
                sendLinkButton.innerHTML = originalText;
            } else {
                sendLinkButton.disabled = false;
                sendLinkButton.innerHTML = originalText;
                showAppToast("Failed to send links: " + data.message);
            }
        })
        .catch(error => {
            console.error("Error:", error);
            sendLinkButton.disabled = false;
            sendLinkButton.innerHTML = originalText;
            showAppToast("An error occurred: " + error.message);
        });
    }

    // Start exam function (executes after confirmation)
    function startExam(vacancyId) {

        const startButton = document.getElementById('startExamButton');
        const originalText = startButton.innerHTML;
        startButton.disabled = true;
        startButton.innerHTML = '<span>Starting...</span>';

        fetch(`/admin/exam_management/${vacancyId}/start?${batchQuery}`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => { 
                    throw new Error(data.message || 'Failed to start exam');
                });
            }
            return response.json();
        })
        .then(data => {
            if(data.success) {
                showAppToast("Exam started successfully!");
                window.location.reload(); // Reload to update status
            } else {
                startButton.disabled = false;
                startButton.innerHTML = originalText;
                showAppToast("Failed to start exam: " + data.message);
            }
        })
        .catch(error => {
            console.error("Error:", error);
            startButton.disabled = false;
            startButton.innerHTML = originalText;
            showAppToast("An error occurred: " + error.message);
        });
    }

    function resumeApplicantExam(userId) {
        fetch(`/admin/exam_management/{{ $vacancy->vacancy_id }}/resume/${userId}?${batchQuery}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({})
        })
        .then(async (response) => {
            const data = await response.json().catch(() => ({}));
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Failed to resume exam.');
            }
            return data;
        })
        .then((data) => {
            showAppToast(data.message || 'Exam resumed successfully.');
            queueLobbyFetch('resume-success', 0, true);
                if (data.exam && typeof data.exam.is_paused !== 'undefined') {
                    examPausedClient = !!data.exam.is_paused;
                    updatePauseButtonState();
                }
        })
        .catch((error) => {
            console.error('Resume exam error:', error);
            showAppToast(error.message || 'Unable to resume the exam.');
        })
        .finally(() => {
            window._pendingResumeUserId = null;
        });
    }


    const saveNotifyBtnEl = document.getElementById('saveNotifyButton');
    if (saveNotifyBtnEl) {
        saveNotifyBtnEl.addEventListener('click', function(e) {
            // Only intercept when this button would submit
            if (!this.disabled) {
                e.preventDefault();
                window.dispatchEvent(new CustomEvent('open-save-notify-confirm'));
            }
        });
    }

    // After confirmation, submit form via existing handler
    window.addEventListener('confirm-save-notify', () => {
        const formEl = document.getElementById('examDetailsForm');
        const btn = document.getElementById('saveNotifyButton');
        if (formEl && btn && !btn.disabled) {
            if (formEl.requestSubmit) {
                formEl.requestSubmit(btn);
            } else {
                // Fallback
                btn.disabled = true;
                formEl.submit();
            }
        }
    });

    document.getElementById('examDetailsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        console.log('Form submitted');
        
        // Validate times before submission
        const startTime = document.getElementById('time').value;
        const endTime = document.getElementById('time_end_hidden').value;
        
        if (startTime && endTime && endTime <= startTime) {
            showAppToast('End time must be after start time. Please correct the times.');
            return;
        }
        
        // Hide the loader immediately if it was triggered by the global listener
        const loader = document.getElementById('loader');
        if (loader) {
            loader.classList.add('hidden');
            // Ensure z-index doesn't block clicks even if hidden class fails
            loader.style.display = 'none'; 
        }

        const vacancyId = '{{ $vacancy->vacancy_id }}';
        const submitAction = e.submitter?.value || '';
        const isSaveNotify = submitAction === 'save_notify';
        const formData = new FormData(this);

        // Disabled fields are excluded from FormData, so always append the persisted schedule values explicitly.
        formData.set('place', document.getElementById('venue')?.value || '');
        formData.set('date', document.getElementById('date')?.value || '');
        formData.set('time', document.getElementById('time')?.value || '');
        formData.set('time_end', document.getElementById('time_end_hidden')?.value || '');
        formData.set('duration', document.getElementById('duration')?.value || '');
        formData.set('message', document.getElementById('message')?.value || '');
        
        // Append action if submitting via the Save & Notify button
        if (isSaveNotify) {
            formData.append('notify', '1');
            console.log('Notify flag set to true');
        }

        const submitButton = document.getElementById('saveNotifyButton');
        const originalText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<span>Saving...</span>';

        console.log('Sending request to:', `/admin/exam_management/${vacancyId}/details/save`);

        fetch(`/admin/exam_management/${vacancyId}/details/save?${batchQuery}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);
            
            // Read response as text first to avoid "body stream already read" error
            return response.text().then(text => {
                console.log('Raw response:', text);
                
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Failed to parse JSON:', e);
                    data = { success: false, message: text || 'Invalid server response' };
                }
                
                if (!response.ok) {
                    console.error('Error response:', data);
                    throw new Error(data.message || `Server error: ${response.status}`);
                }
                
                return data;
            });
        })
        .then(data => {
            console.log('Success response:', data);
            submitButton.innerHTML = originalText;
            
            if (data.success) {
                hasExamDetailsClient = true;
                detailsSavedClient = true;

                const saveNotifyButton = document.getElementById('saveNotifyButton');
                if (saveNotifyButton) {
                    saveNotifyButton.disabled = true;
                    saveNotifyButton.classList.add('opacity-50', 'cursor-not-allowed');
                }

                // Disable schedule fields after the main schedule is saved.
                document.getElementById('venue').disabled = true;
                document.getElementById('date').disabled = true;
                document.getElementById('time').disabled = true;
                document.getElementById('message').disabled = true;
                const monitorEnd = document.getElementById('monitor_end');
                if (monitorEnd) monitorEnd.disabled = true;

                // Re-evaluate Send Link button state (uses latest lobby count)
                updateSendLinkButtonState(currentLobbyCount);
                validateForm();
                
                let msg = "Exam details saved successfully!";
                if (data.notified) {
                    updateScheduleNotifyMeta(currentAdminDisplayName, data.notified_at || null);
                    fetchQualifiedApplicants(document.getElementById('searchInputQualified')?.value || '');
                    msg += " " + (data.notify_message || "Applicants have been notified.");
                }
                showAppToast(msg);
                
                // Optionally reload the page to reflect changes
                // window.location.reload();
            } else {
                submitButton.disabled = false;
                console.error('Save failed:', data.message);
                showAppToast("Failed to save exam details: " + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
            console.error('Error caught:', error);
            console.error('Error message:', error.message);
            showAppToast("An error occurred while saving exam details.\n\nError: " + error.message + "\n\nPlease check the browser console and Laravel logs for more details.");
        });
    });

    // Server-provided counts and flags for gating logic
    const qualifiedCount = @json(isset($qualifiedApplicants) ? $qualifiedApplicants->count() : 0);
    const initialQualifiedApplicants = @json(isset($qualifiedApplicants) ? $qualifiedApplicants->values() : []);
    const currentAdminDisplayName = @json(optional(auth('admin')->user())->name ?? optional(auth('admin')->user())->email ?? 'An admin');
    let attendanceResponseCountClient = @json(isset($attendanceApplicants) ? $attendanceApplicants->count() : 0);
    let willAttendCountClient = @json(isset($attendanceApplicants) ? $attendanceApplicants->where('attendance_status', 'will_attend')->count() : 0);
    let currentLobbyCount = @json(count($participants ?? []));
    let hasExamDetailsClient = @json(!is_null($examDetails ?? null));
    let detailsSavedClient = @json($examDetails && $examDetails->details_saved);
    const linkSentConst = @json($examDetails && $examDetails->link_sent);
    let linkSentClient = linkSentConst;
    let examPausedClient = @json((bool) ($examDetails?->exam_paused_at ?? false));
    let examPauseResumeCountdownTimer = null;
    let examPauseActionInProgress = false;
    const isExamActiveConst = @json($isExamActive);
    const isExamCompletedConst = @json($isExamCompleted);
    const isExamDayConst = @json($isExamDay);
    const hasQuestionsConst = @json($hasQuestions ?? false);

    function formatMetaTimestamp(value) {
        if (!value) return '';
        const parsed = new Date(String(value).replace(' ', 'T'));
        if (Number.isNaN(parsed.getTime())) return String(value);
        return parsed.toLocaleString('en-US', {
            month: 'short',
            day: '2-digit',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        }).replace(',', '');
    }

    function updateScheduleNotifyMeta(sender, timestamp) {
        const wrapper = document.getElementById('scheduleNotifyMeta');
        const senderEl = document.getElementById('scheduleNotifySender');
        const timeEl = document.getElementById('scheduleNotifyTime');
        if (!wrapper || !senderEl || !timeEl || !timestamp) return;
        senderEl.textContent = sender || 'An admin';
        timeEl.textContent = formatMetaTimestamp(timestamp);
        wrapper.classList.remove('hidden');
    }

    function updateSendLinkMeta(sender, timestamp) {
        const wrapper = document.getElementById('sendLinkMeta');
        const senderEl = document.getElementById('sendLinkSender');
        const timeEl = document.getElementById('sendLinkTime');
        if (!wrapper || !senderEl || !timeEl || !timestamp) return;
        senderEl.textContent = sender || 'An admin';
        timeEl.textContent = formatMetaTimestamp(timestamp);
        wrapper.classList.remove('hidden');
    }

    function syncAttendanceSummaryCounts(applicants) {
        const rows = Array.isArray(applicants) ? applicants : [];
        attendanceResponseCountClient = rows.length;
        willAttendCountClient = rows.filter((applicant) => applicant?.attendance_status === 'will_attend').length;
    }

    function refreshAttendanceSummary() {
        const responsesEl = document.getElementById('attendanceResponsesCount');
        const willAttendEl = document.getElementById('attendanceWillAttendCount');
        const willNotAttendEl = document.getElementById('attendanceWillNotAttendCount');
        const badgeEl = document.getElementById('attendanceCountBadge');

        if (responsesEl) responsesEl.textContent = attendanceResponseCountClient;
        if (willAttendEl) willAttendEl.textContent = willAttendCountClient;
        if (willNotAttendEl) willNotAttendEl.textContent = Math.max(attendanceResponseCountClient - willAttendCountClient, 0);

        if (badgeEl) {
            badgeEl.textContent = attendanceResponseCountClient;
        }
    }

    // Helper: update Send Link button state using attendance responses and flags
    function updateSendLinkButtonState(participantsCount) {
        currentLobbyCount = participantsCount;
        const btn = document.getElementById('sendLinkButton');
        if (!btn) return;
        const noWillAttendYet = willAttendCountClient < 1;
        const shouldEnable = hasExamDetailsClient
            && detailsSavedClient
            && !linkSentClient
            && !isExamActiveConst
            && !isExamCompletedConst
            && isExamDayConst
            && !noWillAttendYet;

        let disabledReason = 'Send exam links to applicants marked as Will Attend.';
        if (!hasExamDetailsClient || !detailsSavedClient) {
            disabledReason = 'Please save exam details first.';
        } else if (linkSentClient) {
            disabledReason = 'Exam links were already sent.';
        } else if (isExamActiveConst || isExamCompletedConst) {
            disabledReason = 'Exam links can no longer be sent for this schedule.';
        } else if (!isExamDayConst) {
            disabledReason = 'Exam links can only be sent on the scheduled exam day.';
        } else if (noWillAttendYet) {
            disabledReason = 'No applicants are marked as Will Attend yet.';
        }

        btn.disabled = !shouldEnable;
        btn.title = disabledReason;
        btn.classList.toggle('opacity-50', !shouldEnable);
        btn.classList.toggle('cursor-not-allowed', !shouldEnable);
    }

    // Helper: update Start Exam button state using flags and lobby count
    function updateStartButtonState() {
        const btn = document.getElementById('startExamButton');
        if (!btn) return;
        const shouldEnable = hasExamDetailsClient
            && linkSentClient
            && !isExamActiveConst
            && !isExamCompletedConst
            && isExamDayConst
            && hasQuestionsConst
            && currentLobbyCount > 0;
        btn.disabled = !shouldEnable;
        btn.classList.toggle('opacity-50', !shouldEnable);
        btn.classList.toggle('cursor-not-allowed', !shouldEnable);
    }

    function updatePauseButtonState() {
        const btn = document.getElementById('pauseExamBtn');
        if (!btn) return;

        btn.innerHTML = examPausedClient
            ? '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-4.586-2.65A1 1 0 009 9.385v5.23a1 1 0 001.166.967l4.586-2.65a1 1 0 000-1.732z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v18"></path></svg> Resume Exam'
            : '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21a9 9 0 100-18 9 9 0 000 18z"></path></svg> Pause Exam';
        btn.classList.toggle('bg-emerald-600', examPausedClient);
        btn.classList.toggle('border-emerald-700', examPausedClient);
        btn.classList.toggle('hover:bg-emerald-700', examPausedClient);
        btn.classList.toggle('bg-amber-500', !examPausedClient);
        btn.classList.toggle('border-amber-600', !examPausedClient);
        btn.classList.toggle('hover:bg-amber-600', !examPausedClient);
    }

    function toggleExamPause() {
        const action = examPausedClient ? 'resume' : 'pause';
        const pauseButton = document.getElementById('pauseExamBtn');

        if (examPauseActionInProgress) {
            return;
        }

        if (examPausedClient) {
            examPauseActionInProgress = true;
            if (pauseButton) {
                pauseButton.disabled = true;
                pauseButton.innerHTML = '<span class="inline-flex items-center gap-2"><span class="h-2 w-2 animate-pulse rounded-full bg-white"></span>Resuming in 2s</span>';
            }

            let secondsRemaining = 2;
            examPauseResumeCountdownTimer = window.setInterval(() => {
                secondsRemaining -= 1;
                if (pauseButton) {
                    pauseButton.innerHTML = `<span class="inline-flex items-center gap-2"><span class="h-2 w-2 animate-pulse rounded-full bg-white"></span>Resuming in ${Math.max(secondsRemaining, 0)}s</span>`;
                }

                if (secondsRemaining <= 0) {
                    window.clearInterval(examPauseResumeCountdownTimer);
                    examPauseResumeCountdownTimer = null;

                    fetch(`/admin/exam_management/{{ $vacancy->vacancy_id }}/pause?${batchQuery}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({})
                    })
                    .then(async (response) => {
                        const data = await response.json().catch(() => ({}));
                        if (!response.ok || !data.success) {
                            throw new Error(data.message || `Failed to ${action} exam`);
                        }
                        return data;
                    })
                    .then((data) => {
                        examPausedClient = !!data.paused;
                        updatePauseButtonState();
                        showAppToast(data.message || (examPausedClient ? 'Exam paused.' : 'Exam resumed.'));
                        queueLobbyFetch('pause-toggle', 0, true);
                    })
                    .catch((error) => {
                        showAppToast(error.message || 'Unable to update exam pause state.');
                        updatePauseButtonState();
                    })
                    .finally(() => {
                        examPauseActionInProgress = false;
                    });
                }
            }, 1000);

            return;
        }

        examPauseActionInProgress = true;
        fetch(`/admin/exam_management/{{ $vacancy->vacancy_id }}/pause?${batchQuery}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({})
        })
        .then(async (response) => {
            const data = await response.json().catch(() => ({}));
            if (!response.ok || !data.success) {
                throw new Error(data.message || `Failed to ${action} exam`);
            }
            return data;
        })
        .then((data) => {
            examPausedClient = !!data.paused;
            updatePauseButtonState();
            showAppToast(data.message || (examPausedClient ? 'Exam paused.' : 'Exam resumed.'));
            queueLobbyFetch('pause-toggle', 0, true);
        })
        .catch((error) => {
            showAppToast(error.message || 'Unable to update exam pause state.');
        })
        .finally(() => {
            examPauseActionInProgress = false;
        });
    }

    function toggleApplicantPause(userId) {
        fetch(`/admin/exam_management/{{ $vacancy->vacancy_id }}/pause/${userId}?${batchQuery}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({})
        })
        .then(async (response) => {
            const data = await response.json().catch(() => ({}));
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Failed to update examinee pause state');
            }
            return data;
        })
        .then((data) => {
            showAppToast(data.message || 'Examinee pause state updated.');
            queueLobbyFetch('applicant-pause', 0, true);
        })
        .catch((error) => {
            showAppToast(error.message || 'Unable to update examinee pause state.');
        });
    }

    // Form validation - enable Save button only when all required fields are filled
    function validateForm() {
        const venue = document.getElementById('venue').value.trim();
        const date = document.getElementById('date').value.trim();
        const time = document.getElementById('time').value.trim();
        const message = document.getElementById('message').value.trim();
        const saveButton = document.getElementById('saveNotifyButton');
        
        // Ensure hidden end time is set (default to +1 hour if empty)
        const timeEndInput = document.getElementById('time_end_hidden');
        if (time && !timeEndInput.value) {
            // Auto-set end time to 1 hour later
            const [hours, minutes] = time.split(':');
            const dateObj = new Date();
            dateObj.setHours(parseInt(hours) + 1);
            dateObj.setMinutes(parseInt(minutes));
            const endHours = String(dateObj.getHours()).padStart(2, '0');
            const endMinutes = String(dateObj.getMinutes()).padStart(2, '0');
            timeEndInput.value = `${endHours}:${endMinutes}`;
        }

        const allFilled = venue && date && time && message;
        
        // Only enable if all fields are filled AND details haven't been saved yet AND qualified applicants exist
        const hasQualified = qualifiedCount > 0;

        if (allFilled && !detailsSavedClient && hasQualified) {
            saveButton.disabled = false;
            saveButton.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            saveButton.disabled = true;
            saveButton.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }

    // Add event listeners to form fields for validation
    const formFields = ['venue', 'date', 'time', 'message', 'monitor_end'];
    formFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', validateForm);
            field.addEventListener('change', validateForm);
        }
    });


    // Run validation on page load and initialize Send Link button state
    validateForm();
    updateSendLinkButtonState(currentLobbyCount);
    updateStartButtonState();
    updatePauseButtonState();
    renderMonitorRecipients(initialQualifiedApplicants);
    updateSelectedCount();
    // Sync initial state
    syncMonitorFields();

    // Auto-calculate duration
    const startTimeInput = document.getElementById('time');
    const timeEndHidden = document.getElementById('time_end_hidden');
    const durationInput = document.getElementById('duration');

    function calculateDuration() {
        const start = startTimeInput.value;
        let end = timeEndHidden.value;

        if (start && !end) {
             // Default 1 hour
             const [h, m] = start.split(':');
             const d = new Date();
             d.setHours(parseInt(h) + 1);
             d.setMinutes(parseInt(m));
             end = `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
             timeEndHidden.value = end;
        }

        if (start && end) {
            // Check if end is before start (next day not supported for simplicity)
            if (end <= start) {
                // Force end to be start + 1 hour
                 const [h, m] = start.split(':');
                 const d = new Date();
                 d.setHours(parseInt(h) + 1);
                 d.setMinutes(parseInt(m));
                 end = `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
                 timeEndHidden.value = end;
            }

            const startDate = new Date(`1970-01-01T${start}:00`);
            const endDate = new Date(`1970-01-01T${end}:00`);
            
            let diffMs = endDate - startDate;
            let diffMins = Math.round(diffMs / 60000);

            if (diffMins > 0) {
                durationInput.value = diffMins;
            }
        }
        
        syncMonitorFields();
        validateForm();
    }

    if (startTimeInput) {
        startTimeInput.addEventListener('input', calculateDuration);
        startTimeInput.addEventListener('change', calculateDuration);
    }

    const monitorEndInput = document.getElementById('monitor_end');
    if (monitorEndInput && timeEndHidden) {
        const syncMonitorEnd = () => {
            timeEndHidden.value = monitorEndInput.value;
            calculateDuration();
        };

        monitorEndInput.addEventListener('input', syncMonitorEnd);
        monitorEndInput.addEventListener('change', syncMonitorEnd);
    }

    // Prevent navigation if exam is active and user tries to edit
    function handleEditClick(e) {
        e.preventDefault();
        const isExamActive = @json($isExamActive);
        const isExamCompleted = @json($isExamCompleted);
        
        if (isExamActive) {
            showAppToast('Cannot edit questions while an exam is currently in progress.');
            return;
        }

        if (isExamCompleted) {
            showAppToast('Cannot edit questions after the exam has been completed.');
            return;
        }
        
        
        window.location.href = '{{ route('admin.exam.edit', $vacancy->vacancy_id) }}?batch=' + encodeURIComponent(selectedBatch);
    }

    // ========================================
    // TAB SWITCHING
    // ========================================
    function switchTab(tab) {
        const tabs = showQualifiedWorkflow
            ? ['exam-questions', 'qualified', 'attendance', 'lobby']
            : ['exam-questions', 'attendance', 'lobby'];
        const panelQuestions = document.getElementById('panel-questions');
        const panelSchedule = document.getElementById('panel-schedule');
        const panelMonitor = document.getElementById('panel-monitor');
        const panelAttendance = document.getElementById('panel-attendance');

            // First, hide ALL tab content panels and deactivate all buttons
            tabs.forEach(t => {
                const tabBtnId = t === 'exam-questions' ? 'tab-questions' : `tab-${t}`;
                const tabBtn = document.getElementById(tabBtnId);
                const content = document.getElementById(`content-${t}`);

                if (tabBtn) {
                    tabBtn.classList.remove('border-[#0D2B70]', 'text-[#0D2B70]');
                    tabBtn.classList.add('border-transparent', 'text-gray-400');
                }
                if (content) {
                    content.classList.add('hidden');
                }
            });

            // Then, activate only the selected tab
            const tabBtnId = tab === 'exam-questions' ? 'tab-questions' : `tab-${tab}`;
            const tabBtn = document.getElementById(tabBtnId);
            const content = document.getElementById(`content-${tab}`);

            if (tabBtn) {
                tabBtn.classList.add('border-[#0D2B70]', 'text-[#0D2B70]');
                tabBtn.classList.remove('border-transparent', 'text-gray-400');
            }
            if (content) {
                content.classList.remove('hidden');
            }

            // Toggle Right Panels based on active tab
            if (tab === 'exam-questions') {
                // Exam Questions tab has only the edit button
                if (panelQuestions) panelQuestions.classList.remove('hidden');
                if (panelSchedule) panelSchedule.classList.add('hidden');
                if (panelMonitor) panelMonitor.classList.add('hidden');
                if (panelAttendance) panelAttendance.classList.add('hidden');
                stopLobbyPolling();
                stopAttendancePolling();
            } else if (tab === 'qualified') {
                // Qualified tab: show scheduling form
                if (panelQuestions) panelQuestions.classList.add('hidden');
                if (panelSchedule) panelSchedule.classList.remove('hidden');
                if (panelMonitor) panelMonitor.classList.add('hidden');
                if (panelAttendance) panelAttendance.classList.add('hidden');
                stopLobbyPolling();
                stopAttendancePolling();
            } else if (tab === 'attendance') {
                // Attendance tab: show attendance panel
                if (panelQuestions) panelQuestions.classList.add('hidden');
                if (panelSchedule) panelSchedule.classList.toggle('hidden', showQualifiedWorkflow);
                if (panelMonitor) panelMonitor.classList.add('hidden');
                if (panelAttendance) panelAttendance.classList.remove('hidden');
                stopLobbyPolling();
                startAttendancePolling();
            } else if (tab === 'lobby') {
                // Lobby/Monitor tab: show monitor panel
                if (panelQuestions) panelQuestions.classList.add('hidden');
                if (panelSchedule) panelSchedule.classList.add('hidden');
                if (panelMonitor) panelMonitor.classList.remove('hidden');
                if (panelAttendance) panelAttendance.classList.add('hidden');
                queueLobbyFetch('tab-open', 0);
                startLobbyPolling();
                stopAttendancePolling();
                // Sync Monitor fields
                syncMonitorFields();
            }
    }

    function syncMonitorFields() {
        const timeInput = document.getElementById('time');
        const timeEndInput = document.getElementById('time_end_hidden');
        const monitorStart = document.getElementById('monitor_start');
        const monitorEnd = document.getElementById('monitor_end');

        if (timeInput && monitorStart) {
            const value = timeInput.value;
            if (!value) {
                monitorStart.value = '--:--';
            } else {
                const parts = value.split(':');
                const hour = parseInt(parts[0], 10);
                const minute = parts[1] ?? '00';
                if (Number.isNaN(hour)) {
                    monitorStart.value = value;
                } else {
                    const suffix = hour >= 12 ? 'PM' : 'AM';
                    const hour12 = (hour % 12) || 12;
                    monitorStart.value = `${hour12}:${minute} ${suffix}`;
                }
            }
        }
        if (timeEndInput && monitorEnd) monitorEnd.value = timeEndInput.value || '';
    }

    function openMonitorRecipientModal() {
        const modal = document.getElementById('monitorRecipientModal');
        const panel = document.getElementById('monitorRecipientModalPanel');
        if (!modal) return;

        window.clearTimeout(window._monitorRecipientModalTimer);
        refreshMonitorRecipients();
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        requestAnimationFrame(() => {
            modal.classList.remove('opacity-0');
            modal.classList.add('opacity-100');

            if (panel) {
                panel.classList.remove('opacity-0', 'translate-y-4', 'sm:scale-95');
                panel.classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
            }
        });
    }

    function closeMonitorRecipientModal() {
        const modal = document.getElementById('monitorRecipientModal');
        const panel = document.getElementById('monitorRecipientModalPanel');
        if (!modal) return;

        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0');

        if (panel) {
            panel.classList.remove('opacity-100', 'translate-y-0', 'sm:scale-100');
            panel.classList.add('opacity-0', 'translate-y-4', 'sm:scale-95');
        }

        window.clearTimeout(window._monitorRecipientModalTimer);
        window._monitorRecipientModalTimer = window.setTimeout(() => {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }, 200);
    }


    // ========================================
    // CHECKBOX MANAGEMENT
    // ========================================
    function toggleSelectAll(checkbox) {
        const checkboxes = document.querySelectorAll('.monitor-applicant-checkbox:not(:disabled)');
        checkboxes.forEach(cb => {
            cb.checked = checkbox.checked;
        });
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const checkboxes = document.querySelectorAll('.monitor-applicant-checkbox:checked');
        const count = checkboxes.length;
        const countDisplay = document.getElementById('monitorSelectedCount');
        if (countDisplay) {
            countDisplay.textContent = `${count} selected`;
        }

        // Update button state
        const notifyBtn = document.getElementById('monitorNotifySelectedButton');
        if (notifyBtn) {
            if (count > 0) {
                notifyBtn.disabled = false;
                notifyBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                notifyBtn.classList.add('opacity-100', 'cursor-pointer');
            } else {
                notifyBtn.disabled = true;
                notifyBtn.classList.add('opacity-50', 'cursor-not-allowed');
                notifyBtn.classList.remove('opacity-100', 'cursor-pointer');
            }
        }

        // Update select all checkbox state
        const selectAllCheckbox = document.getElementById('monitorSelectAll');
        const totalCheckboxes = document.querySelectorAll('.monitor-applicant-checkbox:not(:disabled)');
        if (!selectAllCheckbox) {
            return;
        }
        // Prevent division by zero if no checkboxes exist
        if (totalCheckboxes.length > 0) {
            selectAllCheckbox.checked = count > 0 && count === totalCheckboxes.length;
            selectAllCheckbox.indeterminate = count > 0 && count < totalCheckboxes.length;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        }
    }

    function overrideAttendanceStatus(userId, attendanceStatus, existingRemark = '') {
        const statusLabel = attendanceStatus === 'will_attend' ? 'Will Attend' : 'Will Not Attend';
        let remark = existingRemark || '';

        if (attendanceStatus === 'will_not_attend') {
            const prompted = window.prompt('Enter a remark for marking this applicant as Will Not Attend.', remark);
            if (prompted === null) {
                return;
            }
            remark = prompted;
            if (!remark.trim()) {
                showAppToast('A remark is required when marking an applicant as Will Not Attend.');
                return;
            }
        }

        fetch(`/admin/exam_management/{{ $vacancy->vacancy_id }}/attendance/${userId}?${batchQuery}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                attendance_status: attendanceStatus,
                attendance_remark: remark
            })
        })
        .then(async (response) => {
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Failed to update attendance status.');
            }
            return data;
        })
        .then((data) => {
            showAppToast(data.message || `Attendance updated to ${statusLabel}.`);
            willAttendCountClient = Number(data.will_attend_count || 0);
            if (attendanceStatus === 'will_attend') {
                attendanceResponseCountClient = Math.max(attendanceResponseCountClient, 1);
            }
            refreshAttendanceSummary();
            fetchQualifiedApplicants(document.getElementById('searchInputQualified')?.value || '');
            refreshMonitorRecipients();
        })
            .then(() => fetchAttendanceApplicants())
            .catch((error) => {
            console.error('Attendance override failed:', error);
            showAppToast(error.message || 'Unable to update attendance status.');
        });
    }

    function notifySelected() {
        const selectedCheckboxes = document.querySelectorAll('.monitor-applicant-checkbox:checked');
        const userIds = Array.from(selectedCheckboxes).map(cb => cb.dataset.userId);

        if (userIds.length === 0) return;

        const btn = document.getElementById('monitorNotifySelectedButton');
        const originalContent = btn.innerHTML;
        const originalOpacity = btn.classList.contains('opacity-50'); 
        
        btn.disabled = true;
        btn.innerHTML = `<span class="animate-pulse">Sending...</span>`;
        btn.classList.add('opacity-75', 'cursor-wait');

        fetch(`/admin/exam_management/{{ $vacancy->vacancy_id }}/notify-selected?${batchQuery}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ user_ids: userIds })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAppToast(data.message);
                // Refresh both tables
                const search = document.getElementById('searchInputQualified').value;
                fetchQualifiedApplicants(search);
                refreshMonitorRecipients();
                closeMonitorRecipientModal();
            } else {
                showAppToast('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAppToast('An error occurred while sending notifications.');
        })
        .finally(() => {
            // Re-enable handled by updateSelectedCount when table refreshes (checkboxes lost)
            // But if error occurred and table didn't refresh, checkboxes are still there
            btn.disabled = false;
            btn.innerHTML = originalContent;
            btn.classList.remove('opacity-75', 'cursor-wait');
            updateSelectedCount();
        });
    }

    // ========================================
    // SEARCH FUNCTIONALITY
    // ========================================
    const searchInputQualified = document.getElementById('searchInputQualified');
    
    // Debounce function
    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            const context = this;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), wait);
        };
    }

    const handleQualifiedSearch = debounce(function () {
        const search = searchInputQualified.value.trim();
        fetchQualifiedApplicants(search);
    }, 500);

    if (searchInputQualified) {
        searchInputQualified.addEventListener('input', handleQualifiedSearch);
    }

    function fetchQualifiedApplicants(search = '') {
        const params = new URLSearchParams({
            search: search
        });

        fetch(`/admin/exam_management/{{ $vacancy->vacancy_id }}/qualified?${params.toString()}&${batchQuery}`, {
            headers: { 
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateQualifiedApplicantsTable(data.applicants);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function updateQualifiedApplicantsTable(applicants) {
        const tbody = document.getElementById('qualified-applicants-list');
        
        if (applicants.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-10 text-gray-500 text-xl">
                        No qualified applicants found.
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = applicants.map(app => `
            <tr class="text-[#0D2B70] select-none hover:bg-blue-50 transition-colors duration-200">
                <td class="py-2.5 px-6 font-semibold">${app.name}</td>
                <td class="py-2.5 px-6 max-w-[200px] truncate">${app.email}</td>
                <td class="py-2.5 px-6">${app.application_date}</td>
                <td class="py-2.5 px-6 text-center">
                    ${app.has_attendance_response
                        ? `<span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 flex items-center justify-center gap-1" title="Confirmed: ${app.attendance_responded_at || 'N/A'}">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Confirmed
                        </span>`
                        : (app.attendance_prompt_sent
                            ? `<span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 flex items-center justify-center gap-1" title="Sent: ${app.attendance_prompt_sent_at || 'N/A'}">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Sent
                            </span>`
                            : `<span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">Not Sent</span>`
                        )
                    }
                </td>
                <td class="py-2.5 px-6 text-center">
                    <a
                        href="/admin/applicant_status/${app.user_id}/${app.vacancy_id}"
                        target="_blank"
                        class="text-[#0D2B70] border border-[#0D2B70] font-bold py-1 px-4 rounded-md text-sm transition-all duration-300 hover:scale-105 hover:bg-[#0D2B70] hover:text-white hover:shadow-md flex items-center gap-2 mx-auto">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <span>View</span>
                    </a>
                </td>
            </tr>
        `).join('');
    }

    function refreshMonitorRecipients() {
        fetch(`/admin/exam_management/{{ $vacancy->vacancy_id }}/qualified?${batchQuery}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderMonitorRecipients(data.applicants);
            }
        })
        .catch(error => console.error('Error refreshing monitor recipients:', error));
    }

    function renderMonitorRecipients(applicants) {
        const tbody = document.getElementById('monitor-recipient-list');
        if (!tbody) return;

        if (!applicants.length) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="py-8 text-center text-sm text-slate-500">
                        No applicants available for link selection yet.
                    </td>
                </tr>
            `;
            updateSelectedCount();
            return;
        }

        tbody.innerHTML = applicants.map(app => `
            <tr class="text-[#0D2B70] hover:bg-slate-50 transition-colors duration-150">
                <td class="py-2.5 px-4">
                    <input type="checkbox" value="${app.id}"
                        data-user-id="${app.user_id}"
                        data-can-receive-link="${app.can_receive_exam_link ? '1' : '0'}"
                        ${app.can_receive_exam_link ? '' : 'disabled'}
                        onchange="updateSelectedCount()"
                        title="${app.can_receive_exam_link ? 'Eligible to receive exam link' : 'Only applicants marked as Will Attend can receive the exam link'}"
                        class="monitor-applicant-checkbox w-4 h-4 rounded border-gray-300 text-[#0D2B70] focus:ring-[#0D2B70] cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                </td>
                <td class="py-2.5 px-4 font-semibold truncate">${app.name}</td>
                <td class="py-2.5 px-4 text-center">
                    <span class="inline-flex items-center justify-center rounded-full px-3 py-1 text-xs font-semibold ${app.attendance_badge_class}">
                        ${app.attendance_label}
                    </span>
                </td>
                <td class="py-2.5 px-4 text-center">
                    ${app.is_read
                        ? `<span class="inline-flex items-center justify-center rounded-full px-3 py-1 text-xs font-semibold bg-green-100 text-green-800">Opened</span>`
                        : (app.link_sent
                            ? `<span class="inline-flex items-center justify-center rounded-full px-3 py-1 text-xs font-semibold bg-blue-100 text-blue-800">Sent</span>`
                            : (app.can_receive_exam_link
                                ? `<span class="inline-flex items-center justify-center rounded-full px-3 py-1 text-xs font-semibold bg-amber-100 text-amber-800">Ready to Send</span>`
                                : `<span class="inline-flex items-center justify-center rounded-full px-3 py-1 text-xs font-semibold bg-slate-100 text-slate-500">Waiting</span>`
                            )
                        )
                    }
                </td>
            </tr>
        `).join('');

        updateSelectedCount();
    }

    const monitorRecipientModal = document.getElementById('monitorRecipientModal');
    if (monitorRecipientModal) {
        monitorRecipientModal.addEventListener('click', function (event) {
            if (event.target === monitorRecipientModal) {
                closeMonitorRecipientModal();
            }
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && monitorRecipientModal && !monitorRecipientModal.classList.contains('hidden')) {
            closeMonitorRecipientModal();
        }
    });

    const attendanceRemarkTooltip = document.getElementById('attendanceRemarkTooltip');
    const attendanceRemarkTooltipTitle = document.getElementById('attendanceRemarkTooltipTitle');
    const attendanceRemarkTooltipContent = document.getElementById('attendanceRemarkTooltipContent');
    let activeAttendanceRemarkTrigger = null;

    function positionAttendanceRemarkTooltip(trigger) {
        if (!attendanceRemarkTooltip || !trigger) return;

        const rect = trigger.getBoundingClientRect();
        const tooltipRect = attendanceRemarkTooltip.getBoundingClientRect();
        const gap = 12;
        const viewportPadding = 12;

        let left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
        left = Math.max(viewportPadding, Math.min(left, window.innerWidth - tooltipRect.width - viewportPadding));

        let top = rect.bottom + gap;
        if (top + tooltipRect.height > window.innerHeight - viewportPadding) {
            top = rect.top - tooltipRect.height - gap;
        }

        top = Math.max(viewportPadding, top);

        attendanceRemarkTooltip.style.left = `${left}px`;
        attendanceRemarkTooltip.style.top = `${top}px`;
    }

    function showAttendanceRemarkTooltip(trigger) {
        if (!attendanceRemarkTooltip || !trigger) return;

        attendanceRemarkTooltipTitle.textContent = trigger.dataset.tooltipTitle || 'Attendance Remark';
        attendanceRemarkTooltipContent.textContent = trigger.dataset.tooltipContent || '';
        attendanceRemarkTooltip.classList.remove('hidden');
        activeAttendanceRemarkTrigger = trigger;
        positionAttendanceRemarkTooltip(trigger);
    }

    function hideAttendanceRemarkTooltip() {
        attendanceRemarkTooltip?.classList.add('hidden');
        activeAttendanceRemarkTrigger = null;
    }

    function bindAttendanceRemarkTooltips() {
        document.querySelectorAll('.attendance-remark-trigger').forEach((trigger) => {
            if (trigger.dataset.tooltipBound === '1') return;
            trigger.dataset.tooltipBound = '1';

            trigger.addEventListener('mouseenter', () => showAttendanceRemarkTooltip(trigger));
            trigger.addEventListener('mouseleave', hideAttendanceRemarkTooltip);
            trigger.addEventListener('focus', () => showAttendanceRemarkTooltip(trigger));
            trigger.addEventListener('blur', hideAttendanceRemarkTooltip);
        });
    }

    bindAttendanceRemarkTooltips();
    window.addEventListener('scroll', () => {
        if (activeAttendanceRemarkTrigger) {
            positionAttendanceRemarkTooltip(activeAttendanceRemarkTrigger);
        }
    }, true);
    window.addEventListener('resize', () => {
        if (activeAttendanceRemarkTrigger) {
            positionAttendanceRemarkTooltip(activeAttendanceRemarkTrigger);
        }
    });

    // ========================================
    // ATTENDANCE POLLING & AJAX
    // ========================================
    let attendanceFetchInFlight = null;
    let attendancePollingInterval = null;
    const ATTENDANCE_POLL_MS = 2000; // Poll every 2 seconds

    function fetchAttendanceApplicants() {
        if (attendanceFetchInFlight) {
            return attendanceFetchInFlight;
        }

        attendanceFetchInFlight = fetch(`/admin/exam_management/{{ $vacancy->vacancy_id }}/attendance-data?${batchQuery}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const applicants = Array.isArray(data.applicants) ? data.applicants : [];
                syncAttendanceSummaryCounts(applicants);
                updateAttendanceApplicantsTable(applicants);
                refreshAttendanceSummary();
                updateSendLinkButtonState(currentLobbyCount);
                updateStartButtonState();
            }
        })
        .catch(error => {
            console.error('Error fetching attendance data:', error);
        })
        .finally(() => {
            attendanceFetchInFlight = null;
        });

        return attendanceFetchInFlight;
    }

    function startAttendancePolling() {
        if (attendancePollingInterval) clearInterval(attendancePollingInterval);
        attendancePollingInterval = setInterval(() => fetchAttendanceApplicants(), ATTENDANCE_POLL_MS);
        // Fetch immediately on start
        fetchAttendanceApplicants();
    }

    function stopAttendancePolling() {
        if (attendancePollingInterval) clearInterval(attendancePollingInterval);
        attendancePollingInterval = null;
    }

    function updateAttendanceApplicantsTable(applicants) {
        const tbody = document.getElementById('attendance-applicants-list');
        
        if (applicants.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="py-10 text-center text-gray-500 text-xl">
                        No attendance responses yet.
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = applicants.map(app => {
            const isWillAttend = app.attendance_status === 'will_attend';
            const isWillNotAttend = app.attendance_status === 'will_not_attend';
            const attendanceRemark = (app.attendance_remark || 'None provided').trim();
            const hasAttendanceRemark = !!app.attendance_remark;

            return `
                <tr id="attendance-row-${app.user_id}" class="group text-[#0D2B70] hover:bg-blue-50 transition-colors duration-200">
                    <td class="w-[24%] py-2.5 px-6 font-semibold whitespace-nowrap overflow-hidden text-ellipsis">${app.name}</td>
                    <td class="w-[18%] py-2.5 px-6 text-center">
                        <span class="attendance-status-badge inline-flex whitespace-nowrap px-3 py-1 rounded-full text-xs font-semibold ${app.attendance_badge_class}">
                            ${app.attendance_label}
                        </span>
                    </td>
                    <td class="w-[20%] py-2.5 px-6 text-center text-sm text-slate-600 whitespace-nowrap">
                        ${app.attendance_responded_at || '-'}
                    </td>
                    <td class="w-[24%] py-2.5 px-6 text-sm text-slate-600">
                        <button
                            type="button"
                            class="attendance-remark-trigger inline-flex max-w-full items-center rounded-full border px-3 py-1 text-xs font-medium ${hasAttendanceRemark ? 'border-slate-200 bg-slate-50 text-slate-700 shadow-sm hover:border-[#0D2B70]/30 hover:bg-white' : 'border-slate-200 bg-slate-100 text-slate-500 italic'}"
                            data-tooltip-title="Attendance Remark"
                            data-tooltip-content="${attendanceRemark}"
                            aria-label="View full attendance remark"
                        >
                            <span class="block max-w-full truncate whitespace-nowrap">${attendanceRemark}</span>
                        </button>
                    </td>
                    <td class="w-[14%] py-2.5 px-6">
                        <div class="flex items-center justify-center gap-2">
                            <button type="button"
                                onclick="overrideAttendanceStatus(${app.user_id}, 'will_attend')"
                                title="${isWillAttend ? 'Already marked as Will Attend' : 'Mark as Will Attend'}"
                                aria-label="Mark as Will Attend"
                                ${isWillAttend ? 'disabled' : ''}
                                class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-green-700 text-green-700 transition hover:bg-green-700 hover:text-white disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-green-700">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-7.2 7.2a1 1 0 01-1.415.005L3.3 9.206a1 1 0 111.4-1.428l4.08 4.002 6.5-6.49a1 1 0 011.424 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <button type="button"
                                onclick="overrideAttendanceStatus(${app.user_id}, 'will_not_attend', '${(app.attendance_remark || '').replace(/'/g, "\\'")}')"
                                title="${isWillNotAttend ? 'Already marked as Will Not Attend' : 'Mark as Will Not Attend'}"
                                aria-label="Mark as Will Not Attend"
                                ${isWillNotAttend ? 'disabled' : ''}
                                class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-red-700 text-red-700 transition hover:bg-red-700 hover:text-white disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-red-700">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.293 5.293a1 1 0 011.414 0L10 8.586l3.293-3.293a1 1 0 111.414 1.414L11.414 10l3.293 3.293a1 1 0 01-1.414 1.414L10 11.414l-3.293 3.293a1 1 0 01-1.414-1.414L8.586 10 5.293 6.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        // Re-bind tooltips after table update
        bindAttendanceRemarkTooltips();
    }

    // ========================================
    // LOBBY POLLING & AJAX
    // ========================================
    const vacancyId = @json($vacancy->vacancy_id);
    const selectedBatch = @json((int) ($selectedBatch ?? request('batch', 1)));
    const showQualifiedWorkflow = @json((bool) ($showQualifiedWorkflow ?? false));
    const batchQuery = `batch=${encodeURIComponent(selectedBatch)}`;

    if (!showQualifiedWorkflow) {
        switchTab('attendance');
    }
    const examRealtimeConfig = @json($examRealtimeConfig);
    let lobbyPollingInterval = null;
    let lobbyFetchInFlight = null;
    let lobbyFetchQueued = false;
    let lobbyFetchTimer = null;
    let realtimeClient = null;
    let realtimeConnected = false;
    const FAST_POLL_MS = 3000;
    const SAFETY_POLL_MS = 15000;
    const violationSnapshotByUser = new Map();
    const VIOLATION_ALERT_COOLDOWN_MS = 1500;
    let lastViolationAlertAt = 0;
    let violationAlertAudioContext = null;

    function getParticipantViolationTotal(participant) {
        const tabSwitchCount = Number(participant?.tab_switch_count || 0);
        const tamperLogsCount = Number(participant?.tamper_logs_count || 0);
        return tabSwitchCount + tamperLogsCount;
    }

    function seedViolationSnapshot(participants) {
        violationSnapshotByUser.clear();
        (participants || []).forEach((participant) => {
            const userId = Number(participant?.user_id || 0);
            if (!userId) return;
            violationSnapshotByUser.set(userId, getParticipantViolationTotal(participant));
        });
    }

    function playViolationAlertSound() {
        const nowMs = Date.now();
        if ((nowMs - lastViolationAlertAt) < VIOLATION_ALERT_COOLDOWN_MS) {
            return;
        }
        lastViolationAlertAt = nowMs;

        try {
            const AudioContextClass = window.AudioContext || window.webkitAudioContext;
            if (!AudioContextClass) return;

            if (!violationAlertAudioContext) {
                violationAlertAudioContext = new AudioContextClass();
            }

            if (violationAlertAudioContext.state === 'suspended') {
                violationAlertAudioContext.resume().catch(() => {});
            }

            const startAt = violationAlertAudioContext.currentTime + 0.01;
            const gain = violationAlertAudioContext.createGain();
            gain.connect(violationAlertAudioContext.destination);
            gain.gain.setValueAtTime(0.0001, startAt);
            gain.gain.exponentialRampToValueAtTime(0.28, startAt + 0.01);
            gain.gain.exponentialRampToValueAtTime(0.0001, startAt + 0.62);

            const firstTone = violationAlertAudioContext.createOscillator();
            firstTone.type = 'square';
            firstTone.frequency.setValueAtTime(1000, startAt);
            firstTone.connect(gain);
            firstTone.start(startAt);
            firstTone.stop(startAt + 0.22);

            const secondTone = violationAlertAudioContext.createOscillator();
            secondTone.type = 'square';
            secondTone.frequency.setValueAtTime(740, startAt + 0.28);
            secondTone.connect(gain);
            secondTone.start(startAt + 0.28);
            secondTone.stop(startAt + 0.6);
        } catch (error) {
            console.debug('Violation alert audio unavailable:', error);
        }
    }

    function notifyViolationIncrease(participants) {
        if (!Array.isArray(participants) || participants.length === 0) {
            violationSnapshotByUser.clear();
            return;
        }

        if (violationSnapshotByUser.size === 0) {
            seedViolationSnapshot(participants);
            return;
        }

        let hasViolationIncrease = false;
        participants.forEach((participant) => {
            const userId = Number(participant?.user_id || 0);
            if (!userId) return;

            const currentTotal = getParticipantViolationTotal(participant);
            const previousTotal = violationSnapshotByUser.get(userId);

            if (typeof previousTotal === 'number' && currentTotal > previousTotal) {
                hasViolationIncrease = true;
            }

            violationSnapshotByUser.set(userId, currentTotal);
        });

        if (!hasViolationIncrease) {
            return;
        }

        playViolationAlertSound();
    }

    function startLobbyPolling() {
        if (lobbyPollingInterval) clearInterval(lobbyPollingInterval);
        lobbyPollingInterval = setInterval(() => fetchLobbyData(false, 'poll'), realtimeConnected ? SAFETY_POLL_MS : FAST_POLL_MS);
    }

    function stopLobbyPolling() {
        if (lobbyPollingInterval) clearInterval(lobbyPollingInterval);
        lobbyPollingInterval = null;
    }

    function queueLobbyFetch(reason = 'queued', delay = 0, isManual = false) {
        if (lobbyFetchTimer) clearTimeout(lobbyFetchTimer);
        lobbyFetchTimer = setTimeout(() => {
            lobbyFetchTimer = null;
            fetchLobbyData(isManual, reason);
        }, delay);
    }

    function initLobbyRealtime() {
        if (!examRealtimeConfig.enabled || typeof window.Pusher === 'undefined') return;

        try {
            realtimeClient = new window.Pusher(examRealtimeConfig.key, {
                wsHost: examRealtimeConfig.wsHost,
                wsPort: examRealtimeConfig.wsPort,
                wssPort: examRealtimeConfig.wssPort,
                forceTLS: !!examRealtimeConfig.forceTLS,
                enabledTransports: ['ws', 'wss'],
                authEndpoint: '/broadcasting/auth',
                auth: {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    }
                }
            });

            const monitorChannel = realtimeClient.subscribe(`private-exam-monitor.${vacancyId}`);
            monitorChannel.bind('exam.progress.updated', (data) => {
                console.log('exam.progress.updated event received:', data);
                
                // Handle attendance updates - trigger immediate fetch
                if (data.type === 'attendance_updated') {
                    console.log('Attendance update detected, fetching latest data immediately...');
                    fetchAttendanceApplicants();
                    return;
                }

                // Handle other exam progress updates (lobby visibility-dependent)
                if (document.getElementById('content-lobby').classList.contains('hidden')) return;
                queueLobbyFetch('realtime-event', 80);
            });

            realtimeClient.connection.bind('connected', () => {
                realtimeConnected = true;
                if (!document.getElementById('content-lobby').classList.contains('hidden')) {
                    startLobbyPolling();
                    queueLobbyFetch('realtime-connected', 0);
                }
            });

            realtimeClient.connection.bind('disconnected', () => {
                realtimeConnected = false;
                if (!document.getElementById('content-lobby').classList.contains('hidden')) {
                    startLobbyPolling();
                }
            });

            realtimeClient.connection.bind('unavailable', () => {
                realtimeConnected = false;
                if (!document.getElementById('content-lobby').classList.contains('hidden')) {
                    startLobbyPolling();
                }
            });

            realtimeClient.connection.bind('error', () => {
                realtimeConnected = false;
                if (!document.getElementById('content-lobby').classList.contains('hidden')) {
                    startLobbyPolling();
                }
            });
        } catch (error) {
            console.error('Lobby realtime init failed:', error);
            realtimeConnected = false;
        }
    }

    function fetchLobbyData(isManual = false, reason = 'manual') {
        if (lobbyFetchInFlight) {
            lobbyFetchQueued = true;
            return lobbyFetchInFlight;
        }

        const btn = document.getElementById('refreshLobbyBtn');
        const icon = btn?.querySelector('svg');

        if (isManual && btn) {
            btn.disabled = true;
            icon?.classList.add('animate-spin');
        }

        lobbyFetchInFlight = fetch(`/admin/exam_management/${vacancyId}/lobby-data?${batchQuery}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                notifyViolationIncrease(data.participants);
                updateLobbyTable(data.participants);
                updateLastUpdatedTime();
                const count = Array.isArray(data.participants) ? data.participants.length : 0;
                updateSendLinkButtonState(count);
                updateStartButtonState();
            }
        })
        .catch(error => console.error(`Error fetching lobby data (${reason}):`, error))
        .finally(() => {
            lobbyFetchInFlight = null;
            if (isManual && btn) {
                btn.disabled = false;
                icon?.classList.remove('animate-spin');
            }
            if (lobbyFetchQueued) {
                lobbyFetchQueued = false;
                queueLobbyFetch('queued', 120);
            }
        });

        return lobbyFetchInFlight;
    }

    function updateLastUpdatedTime() {
        const el = document.getElementById('lobbyLastUpdated');
        if (el) {
            const now = new Date();
            el.textContent = 'Last updated: ' + now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }
    }

    function updateLobbyTable(participants) {
        const tbody = document.getElementById('exam-lobby-tbody');
        
        if (participants.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="py-10 text-center text-gray-500">
                        <p class="text-xl font-semibold">There are no participants yet.</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = participants.map(p => `
            <tr class="hover:bg-blue-50 transition-colors duration-200">
                <!-- Name -->
                <td class="py-2.5 px-3 md:py-3 md:px-6 text-[#0D2B70] font-semibold text-xs md:text-sm w-[25%] md:w-[25%]">
                    ${p.name}
                </td>

                <!-- MC Score -->
                <td class="py-2.5 px-3 md:py-3 md:px-6 text-center text-[#0D2B70] font-medium text-xs md:text-sm w-[15%] md:w-[15%]">
                    ${p.mc_score}
                </td>

                <!-- Essay Score -->
                <td class="py-2.5 px-3 md:py-3 md:px-6 text-center text-[#0D2B70] font-medium text-xs md:text-sm w-[15%] md:w-[15%]">
                    ${p.essay_score}
                </td>

                <!-- Status -->
                <td class="py-2.5 px-3 md:py-3 md:px-6 text-center w-[20%]">
                    <div class="inline-flex items-center gap-1 md:gap-2 text-[#0D2B70] font-medium text-xs md:text-sm">
                        <i class="fa-solid fa-circle text-xs" style="color: ${p.status_color}"></i>
                        <span class="capitalize">${p.status}</span>
                    </div>
                </td>

                <!-- Action Button -->
                <td class="py-2.5 px-3 md:py-3 md:px-6 text-center w-[25%]">
                    <div class="inline-flex flex-row flex-nowrap items-center justify-center gap-2">
                        <a href="/admin/exam_management/${p.vacancy_id}/view_exam/${p.user_id}?batch=${encodeURIComponent(selectedBatch)}" target="_blank" rel="noopener noreferrer"
                            class="text-[#0D2B70] border border-[#0D2B70] font-bold py-1.5 px-3 md:py-2 md:px-6 rounded-md text-xs md:text-sm
                                transition-all duration-150 ease-[cubic-bezier(0.4,0,0.2,1)]
                                hover:scale-105 hover:bg-[#002C76] hover:text-white hover:shadow-md inline-flex items-center gap-1 md:gap-2 whitespace-nowrap shrink-0">
                            <svg class="w-3 h-3 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <span class="hidden sm:inline">View</span>
                        </a>
                        ${p.resume_action && p.resume_action.can_resume ? `
                            <button type="button"
                                onclick="triggerResumeExamConfirm(${p.user_id})"
                                title="Resume exam with ${p.resume_action.remaining_label || 'saved'} remaining"
                                class="border border-emerald-700 bg-emerald-600 text-white font-bold py-1.5 px-3 md:py-2 md:px-4 rounded-md text-xs md:text-sm transition-all duration-150 hover:scale-105 hover:bg-emerald-700 hover:shadow-md inline-flex items-center gap-1 md:gap-2 whitespace-nowrap shrink-0">
                                <svg class="w-3 h-3 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-4.586-2.65A1 1 0 009 9.385v5.23a1 1 0 001.166.967l4.586-2.65a1 1 0 000-1.732z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Resume</span>
                            </button>
                        ` : ''}
                    </div>
                    <div class="mt-1 text-[10px] md:text-xs text-gray-600 font-medium">
                        <span class="whitespace-nowrap">Switches: ${Number(p.tab_switch_count || 0)}</span>
                        <span class="mx-1 text-gray-400">|</span>
                        <span class="whitespace-nowrap">Tamper: ${Number(p.tamper_logs_count || 0)}</span>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    // Stop polling if user leaves page (though modern browsers throttle this anyway)
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopLobbyPolling();
        } else {
             // Only resume if we are on the lobby tab
             if (!document.getElementById('content-lobby').classList.contains('hidden')) {
                 queueLobbyFetch('visibility', 0);
                 startLobbyPolling();
             }
        }
    });

    initLobbyRealtime();

</script>

@endsection
