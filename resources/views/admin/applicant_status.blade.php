@extends('layout.admin')

@section('title', 'Application Status')

@section('content')
	<div id="page-content" class="space-y-6"> <!-- Main container with spacing -->
		<div class="bg-white p-6 rounded-xl shadow-lg mx-auto font-montserrat">
			@php
				$isCancelledApplication = (bool) ($isCancelledApplication ?? false);
			@endphp
			@if (session('success'))
				<div class="mb-6 px-4 py-3 bg-green-100 border border-green-400 text-green-800 rounded-lg shadow text-sm font-semibold flex items-center justify-between"
					role="alert">
					<span>{{ session('success') }}</span>
					<button onclick="this.parentElement.remove()"
						class="text-green-800 hover:text-red-600 font-bold text-lg">&times;</button>
				</div>
			@endif

			<div class="flex items-center gap-4 border-b border-[#0D2B70] pb-4 mb-6">
				<!-- <button aria-label="Back" onclick="window.location.href='{{ route('applications_list') }}'" -->
				<button onclick="goBack()" class="use-loader group">
					<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#0D2B70] hover:opacity-80 transition"
						fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
						<path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
					</svg>
				</button>
				<h1 class="flex items-center gap-3 py-2 tracking-wide select-none">
					<span class="text-[#0D2B70] text-2xl md:text-3xl lg:text-4xl font-montserrat">
						Applicant Status
					</span>
				</h1>
			</div>

			<form method="POST" action="{{ route('admin.applicant_status.update', [$user_id, $vacancy_id]) }}">
				@csrf
				@if($isCancelledApplication)
					<div class="mb-4 px-4 py-3 bg-red-100 border border-red-300 text-red-800 rounded-lg text-sm font-semibold">
						This application was cancelled by the applicant. Admin actions are locked for this record.
					</div>
				@endif
				<!-- Applicant Header -->
				<div class="mb-6">
					<!-- applicant name and notify applicant button -->
					<div class="flex flex-row justify-between items-center mb-4">
						<h1 class="text-2xl font-bold text-[#002C76]">{{ $applicant_name }}</h1>
						<!-- Save Applicant Remarks button removed as per Phase 3 -->
						@if(!$isCancelledApplication)
							<button id="notify-applicant-btn" type="button" onclick="openNotifyModal()"
								class="text-sm py-1 border bg-[#002C76] text-white px-6 rounded-md hover:scale-105 hover:shadow-md transition duration-150 flex items-center justify-center">
								Notify Applicant
							</button>
						@endif
					</div>
					<!-- Job Details Grid -->
					<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
						<div>
							<div class="text-xs font-semibold text-gray-700 uppercase mb-1">Job Applied:</div>
							<div class="text-sm text-gray-900">{{ $job_applied }}, <b>{{ $vacancy_type }}</b>
								position</div>
						</div>
						<div>
							<div class="text-xs font-semibold text-gray-700 uppercase mb-1">Place of Assignment:
							</div>
							<div class="text-sm text-gray-900">{{ $place_of_assignment }}</div>
						</div>
						<div>
							<div class="text-xs font-semibold text-gray-700 uppercase mb-1">Compensation:</div>
							<div class="text-sm text-gray-900">	PHP {{ number_format($compensation, 2) }}</div>
						</div>
					</div>

					<!-- Main Info Cards -->
					<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

						<!-- Qualification Standards Card -->
						<div
							class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm hover:shadow-md transition-shadow duration-200">
							<div
								class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 pb-2 border-b border-gray-50">
								Qualification Standards</div>
							@if(!$isCancelledApplication)
								<div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
									@php
										$qsFields = [
											'qs_education' => 'Education',
											'qs_eligibility' => 'Eligibility',
											'qs_experience' => 'Experience',
											'qs_training' => 'Training'
										];
									@endphp

									@foreach($qsFields as $field => $label)
										@php
											$val = old($field, $application->$field ?? 'no');
										@endphp
										<div class="flex flex-col gap-2 pb-3 border-b border-gray-50 last:border-0">
											<span class="text-sm font-semibold text-gray-800">{{ $label }}</span>
											<div class="flex items-center gap-2">
												<label class="flex items-center gap-1 cursor-pointer text-xs text-green-600 font-medium">
													<input type="radio" name="{{ $field }}" value="yes"
														class="w-3.5 h-3.5 text-green-600 bg-gray-100 border-gray-300 focus:ring-green-500"
														{{ $val === 'yes' ? 'checked' : '' }}> Qualified
												</label>
												<label class="flex items-center gap-1 cursor-pointer text-xs text-red-600 font-medium ml-1">
													<input type="radio" name="{{ $field }}" value="no"
														class="w-3.5 h-3.5 text-red-600 bg-gray-100 border-gray-300 focus:ring-red-500"
														{{ $val === 'no' ? 'checked' : '' }}> Not Qualified
												</label>
											</div>
										</div>
									@endforeach

									<div class="col-span-1 md:col-span-2 flex items-center justify-between bg-blue-50/50 p-4 rounded-lg">
										<span class="text-sm font-bold text-[#002C76]">Overall Standard</span>
										@php
											$selectedQsResult = old('qs_result', $application->qs_result ?? 'Not Qualified');
										@endphp
										<div class="flex items-center gap-4 ml-auto">
											<label class="flex items-center gap-2 cursor-pointer result-radio-grp text-green-700 font-semibold text-sm">
												<input type="radio" name="qs_result" value="Qualified"
													class="w-4 h-4 text-green-600 bg-gray-100 border-gray-300 focus:ring-green-500"
													{{ $selectedQsResult === 'Qualified' ? 'checked' : '' }}> Qualified
											</label>
											<label class="flex items-center gap-2 cursor-pointer result-radio-grp text-amber-700 font-semibold text-sm">
												<input type="radio" name="qs_result" value="Needs Revisions"
													class="w-4 h-4 text-amber-600 bg-gray-100 border-gray-300 focus:ring-amber-500"
													{{ $selectedQsResult === 'Needs Revisions' ? 'checked' : '' }}> Needs Revisions
											</label>
											<label class="flex items-center gap-2 cursor-pointer result-radio-grp text-red-700 font-semibold text-sm">
												<input type="radio" name="qs_result" value="Not Qualified"
													class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 focus:ring-red-500"
													{{ $selectedQsResult === 'Not Qualified' ? 'checked' : '' }}> Not Qualified
											</label>
										</div>
									</div>
								</div>
							@else
								<div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
									@php
										$qsFieldsReadonly = [
											'qs_education' => 'Education',
											'qs_eligibility' => 'Eligibility',
											'qs_experience' => 'Experience',
											'qs_training' => 'Training'
										];
									@endphp
									@foreach($qsFieldsReadonly as $field => $label)
										@php
											$val = strtolower(trim((string) ($application->$field ?? 'no')));
											$display = $val === 'yes' ? 'Qualified' : 'Not Qualified';
											$color = $val === 'yes' ? 'text-green-600' : 'text-red-600';
										@endphp
										<div class="flex flex-col gap-2 pb-3 border-b border-gray-50 last:border-0">
											<span class="text-sm font-semibold text-gray-800">{{ $label }}</span>
											<span class="text-xs font-semibold {{ $color }}">{{ $display }}</span>
										</div>
									@endforeach
									@php
										$overall = trim((string) ($application->qs_result ?? 'Not Qualified'));
										$overallColor = match ($overall) {
											'Qualified' => 'text-green-700',
											'Needs Revisions' => 'text-amber-700',
											default => 'text-red-700',
										};
									@endphp
									<div class="col-span-1 md:col-span-2 flex items-center justify-between bg-blue-50/50 p-4 rounded-lg">
										<span class="text-sm font-bold text-[#002C76]">Overall Standard</span>
										<span class="text-sm font-semibold {{ $overallColor }}">{{ $overall }}</span>
									</div>
								</div>
							@endif
						</div>

						<!-- Application Progress Card -->
						<div
							class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm hover:shadow-md transition-shadow duration-200 flex flex-col justify-center">
							<div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Application Progress
							</div>
							<div class="flex flex-col gap-3">
								<!-- Progress Bar -->
								<div class="w-full h-2.5 bg-gray-100 rounded-full overflow-hidden">
									<div id="linear-progress-bar"
										class="h-full bg-gradient-to-r from-[#002C76] to-[#0052cc] transition-all duration-700 ease-out"
										style="width: 0%">
									</div>
								</div>

								<!-- Progress Text -->
								<div class="flex items-center justify-between mt-1">
									<div class="flex items-baseline gap-2">
										<span id="progress-percentage" class="text-2xl font-bold text-[#002C76]">0%</span>
										<span class="text-xs text-gray-500 font-medium tracking-wide">
											<span id="progress-count">0/0</span> Documents Verified
										</span>
									</div>

									<!-- Info Icon -->
									<!-- <button type="button"
										onclick="const t = document.getElementById('status-tooltip'); t.classList.toggle('hidden');"
										class="text-gray-400 hover:text-[#002C76] transition-colors p-1 rounded-full hover:bg-gray-50">
										<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
											viewBox="0 0 24 24" stroke="currentColor">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
												d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
										</svg>
									</button> -->
								</div>
							</div>

							<!-- Tooltip -->
							<div id="status-tooltip" class="hidden mt-3 p-3 bg-gray-50 rounded border border-gray-200">
								<div id="document-status" class="text-xs font-semibold text-red-600 mb-2 text-center">
									DOCUMENTS SUBMITTED: INCOMPLETE
								</div>
								<div id="actions-block" class="hidden">
									<div class="text-xs font-semibold text-[#002C76] mb-2 text-center">Actions
										Required</div>
									<div id="checkboxes-container" class="space-y-1.5">
										@foreach(['Pre-Qualifying Exam (PQE)', 'Written Exam', 'Interview', 'Group Orals', 'Competency-Based Assessment (CBA)'] as $step)
											<label
												class="flex items-center gap-2 cursor-pointer group hover:bg-white p-1 rounded">
												<input type="checkbox"
													class="w-3.5 h-3.5 rounded border-gray-300 text-[#002C76] focus:ring-[#002C76]" />
												<span
													class="text-xs text-gray-700 group-hover:text-[#002C76]">{{ $step }}</span>
											</label>
										@endforeach
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				@foreach($documents as $doc)
					<input type="hidden" name="document_statuses[{{ $doc['id'] }}]" id="status-input-{{ $doc['id'] }}"
						value="{{ $doc['status'] ?? 'Pending' }}">
					<input type="hidden" name="document_remarks[{{ $doc['id'] }}]" id="remarks-input-{{ $doc['id'] }}"
						value="{{ $doc['remarks'] ?? '' }}">
				@endforeach

				<!-- lower part -->
				<div class="flex flex-col lg:flex-row gap-4">
					<!-- Left Side Panel - Required Documents -->
					<section aria-label="Required Documents Panel"
					class="w-full lg:w-72 flex-none bg-white rounded-lg border border-gray-300 p-3 shadow-lg flex flex-col">
					<h2 class="text-sm font-bold text-gray-700 mb-3 uppercase tracking-wide flex-none">Required
						Documents
					</h2>
					<p class="text-[11px] text-gray-500 mb-2">
						Documents marked <span class="text-red-600 font-bold">(required)</span> must be uploaded for this vacancy.
					</p>
					<div class="pr-1">
						<ul class="text-xs text-gray-700 space-y-1" id="document-list">
							<!-- Documents will be injected here by JS -->
						</ul>
					</div>
					</section>

					<div id="document-context-menu"
						class="fixed hidden z-[1200] w-44 bg-white border border-gray-200 rounded-md shadow-xl overflow-hidden">
						<button id="context-action-verify" type="button"
							class="w-full text-left px-3 py-2 text-xs text-[#00730A] hover:bg-green-50 transition-colors">
							Verify
						</button>
						<button id="context-action-revision" type="button"
							class="w-full text-left px-3 py-2 text-xs text-[#BC0000] hover:bg-red-50 transition-colors">
							Needs Revision
						</button>
					</div>

					<!-- MIDDLE - Document Preview -->
					<section aria-label="Document Preview"
						class="flex-1 bg-white rounded-xl border border-gray-300 shadow-lg p-6 flex flex-col min-w-0 min-h-[800px]">

						<!-- Document Header -->
						<div
							class="mb-4 w-full flex flex-col sm:flex-row justify-between pb-2 border-b border-gray-400 gap-4 flex-none">
							<!-- document name, status, last modified by -->
							<div class="flex-1 min-w-0">
								<!-- document name -->
								<h2 id="document-title" class="text-xl md:text-2xl font-bold text-[#002C76] mb-1 truncate">
									Select a Document
								</h2>
								<!-- APPROVED = #00730A -->
								<!-- PENDING = #E47E00 -->
								<!-- REJECTED / NEEDS REVISIONS = #BC0000 -->
								<span id="document-status-text" class="text-sm text-gray-600 hidden">Status:
									<span id="document-status-value" class="text-[#E47E00] font-bold"></span>
								</span>
								<p id="document-modified" class="text-sm text-gray-600 hidden">Last modified by:
									<span class="font-medium">{{ $admin_name ?? 'N/A' }}</span>
								</p>
							</div>

              <!-- buttons -->
              <div class="flex flex-row sm:flex-col items-end gap-2 shrink-0">
							@if(!$isCancelledApplication)
								<div class="w-full sm:w-auto">
									<button id="btn-revision" type="button"
										class="w-full sm:w-40 border border-[#BC0000] text-[#BC0000] py-2 px-4 rounded-md text-sm hover:bg-red-50 transition duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
										Needs Revisions
									</button>
								</div>

								<div class="w-full sm:w-auto">
									<button id="btn-verify" type="button"
										class="w-full sm:w-40 border border-[#00730A] text-[#00730A] py-2 px-4 rounded-md text-sm hover:bg-green-50 transition duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
										Verify
									</button>
								</div>
								<div>
								 <p class="text-[#00730A] text-sm mr-0.5">If Verified - No need revisions</p>
								</div>
							@endif
              </div>
						
						</div>
						<!-- Remarks and Buttons Row -->
						@if(!$isCancelledApplication)
						<div id="document-remarks-section"
							class="mb-4 flex flex-col justify-between gap-3 hidden flex-none">
							<!-- Remarks Textarea -->
							<div class="w-full">
								<div class="flex items-center justify-between mb-2">
									<label for="remarks" class="block text-sm font-semibold text-[#002C76]">
										Document Remarks:
										<span id="remarks-status"
											class="text-green-600 text-xs ml-2 opacity-0 transition-opacity duration-500">Saved</span>
									</label>
								</div>

								<div class="w-full">
									<textarea id="remarks" rows="3"
										class="w-full text-sm text-gray-700 rounded-lg p-3 resize-none border border-[#002C76] focus:border-[#0066CC] focus:ring-2 focus:ring-blue-200 transition bg-gray-50"
										{{ $isCancelledApplication ? 'disabled' : '' }}
										placeholder="Add remarks for this document..."></textarea>
								</div>
							</div>
						</div>
						@endif

						<!-- Preview Frame -->
						<div class="flex-1 bg-gray-50 rounded-xl border border-[#002C76] p-0 overflow-hidden relative">
							<div id="preview-loader"
								class="absolute inset-0 flex items-center justify-center bg-gray-100 z-10 hidden">
								<div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#002C76]"></div>
							</div>
							<iframe id="doc-preview" src="about:blank" title="Document Preview"
								class="w-full h-full border-0 bg-white" loading="lazy"></iframe>
						</div>

						<!-- Hidden Toggle (for compatibility) -->
						<div id="toggle-container" class="hidden">
							<input type="checkbox" id="favorite" class="input-toggle hidden" />
						</div>
					</section>
				</div>
		</div>


	</div>

	@if(!$isCancelledApplication)
	<div id="notify-modal" class="fixed inset-0 z-[9998] flex items-center justify-center bg-black/50 hidden">
		<div class="bg-white rounded-lg shadow-2xl w-full h-full md:w-[95%] md:h-[95%] md:rounded-xl flex flex-col">
			<div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center flex-none">
				<h3 class="text-lg font-semibold text-gray-800">Notify Applicant Overview</h3>
				<button type="button" onclick="closeNotifyModal()"
					class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
			</div>
			<div class="px-8 py-6 overflow-y-auto flex-1">
				<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
					<!-- Left Column -->
					<div class="space-y-8">
						<!-- Job Applied -->
						<div>
							<div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Job Applied
							</div>
							<div id="notify-job-applied" class="text-base font-semibold text-gray-800"></div>
							<div class="text-sm text-gray-500 mt-1"><span id="notify-place-of-assignment"></span></div>
						</div>
						<!-- Compensation -->
						<div>
							<div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Compensation
							</div>
							<div id="notify-compensation" class="text-base font-semibold text-gray-800"></div>
						</div>
						<!-- Qualification Standards -->
						<div>
							<div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Qualification
								Standards</div>
							<ul id="notify-qs-list" class="text-sm text-gray-700 space-y-2"></ul>
						</div>
					</div>

					<!-- Right Column -->
					<div class="space-y-8">
						<!-- Application Progress -->
						<div>
							<div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Application
								Progress</div>
							<div class="flex flex-col gap-2">
								<div class="flex items-center justify-between text-sm">
									<span id="notify-progress-percentage" class="font-bold text-[#002C76] text-lg">0%</span>
									<span class="text-gray-500 text-xs font-medium"><span
											id="notify-progress-count">0/0</span> Documents Verified</span>
								</div>
								<div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
									<div id="notify-progress-bar"
										class="h-full bg-gradient-to-r from-[#002C76] to-[#0052cc] transition-all duration-500"
										style="width:0%"></div>
								</div>
							</div>
						</div>

						<!-- Hidden by default, toggled via JS -->
						<div id="notify-action-requirements" class="space-y-8 hidden">
							<!-- Set Deadline -->
							<div id="show-deadline-wrap" class="hidden">
								<button
									type="button"
									id="show-deadline-btn"
									class="inline-flex items-center gap-2 text-xs font-semibold text-[#002C76] border border-[#002C76]/30 rounded-md px-3 py-1.5 hover:bg-[#002C76]/5 transition-colors"
								>
									Show Set Deadline
								</button>
							</div>
							<div id="notify-deadline-section">
								<div class="flex items-center justify-between mb-3">
									<div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Set Deadline</div>
									<button
										type="button"
										id="clear-deadline-btn"
										class="inline-flex items-center justify-center w-6 h-6 rounded-full border border-gray-300 text-gray-500 hover:text-red-600 hover:border-red-300 hover:bg-red-50 transition-colors"
										title="Do not set deadline"
										aria-label="Do not set deadline"
									>
										&times;
									</button>
								</div>
								<div class="flex gap-3">
									<input type="date" name="deadline_date" id="deadline_date"
										class="flex-1 text-sm px-3 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-[#002C76] focus:border-[#002C76] disabled:bg-gray-50 disabled:text-gray-400 outline-none transition-shadow"
										value="{{ old('deadline_date', $application->deadline_date ? \Carbon\Carbon::parse($application->deadline_date)->format('Y-m-d') : '') }}">
									<input type="time" name="deadline_time" id="deadline_time"
										class="flex-1 text-sm px-3 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-[#002C76] focus:border-[#002C76] disabled:bg-gray-50 disabled:text-gray-400 outline-none transition-shadow"
										value="{{ old('deadline_time', $application->deadline_time ? \Carbon\Carbon::parse($application->deadline_time)->format('H:i') : '17:00') }}">
								</div>
								<div id="deadlineWarning" class="text-red-500 text-xs mt-2 hidden font-medium">
									<i data-feather="alert-triangle" class="inline w-3 h-3"></i> Deadline passed
								</div>
							</div>
							<!-- Remarks -->
							<div id="notify-remarks-section">
								<div class="flex items-center justify-between mb-3">
									<div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Qualifiers
										Remarks</div>
									<span id="notify-remarks-status"
										class="text-green-600 text-xs font-medium opacity-0 transition-opacity duration-500">Saved</span>
								</div>
								@php
									$confirmedCount = collect($documents)->where('status', 'Verified')->count();
									$isComplete = $confirmedCount === 17;
									$defaultRemarks = '';
									if ($isComplete) {
										$defaultRemarks = "No further action required. Wait for further instruction on the next assessment phase.";
									} else {
										$deadline = $application->deadline_date && $application->deadline_time
											? \Carbon\Carbon::parse($application->deadline_date . ' ' . $application->deadline_time)->format('F d, Y h:i A')
											: null;
										$defaultRemarks = $deadline
											? "Correct and/or submit the above-noted inconsistencies and/or deficiencies not later than $deadline."
											: '';
									}
								@endphp
								<textarea id="notify-applicant-remarks"
									class="w-full text-sm text-gray-700 border border-gray-200 rounded-lg p-4 focus:outline-none focus:ring-2 focus:ring-[#002C76] focus:border-[#002C76] transition-shadow resize-none bg-gray-50/50"
									rows="4"
									placeholder="Enter remarks for the applicant...">{{ old('application_remarks', $application->application_remarks ?? $defaultRemarks) }}</textarea>
							</div>
						</div>
					</div>
				</div>

				<!-- Required Documents Table -->
				<div class="mt-8 border border-gray-100 rounded-xl overflow-hidden shadow-sm">
					<div class="px-5 py-3 bg-gray-50/80 border-b border-gray-100 flex items-center justify-between">
						<h4 class="text-xs font-bold text-gray-600 uppercase tracking-wider">Required Documents</h4>
						<div class="text-[10px] text-gray-400 font-medium text-right">
							<div>Documents marked <span class="text-red-600 font-bold">(required)</span> must be uploaded for this vacancy.</div>
							<div>With remarks only for items needing revision</div>
						</div>
					</div>
					<table class="min-w-full text-sm">
						<tbody id="notify-documents-body" class="divide-y divide-gray-50 bg-white"></tbody>
					</table>
				</div>
			</div>
			<div class="px-6 py-3 border-t border-gray-200 flex justify-end gap-3 flex-none">
				<button type="button" onclick="closeNotifyModal()"
					class="px-4 py-2 text-xs font-medium text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50">
					Cancel
				</button>
				<button type="button" onclick="notifyApplicant()" id="confirm-notify-btn"
					class="px-4 py-2 text-xs font-medium text-white bg-[#002C76] rounded-md hover:bg-[#003b9c] flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
					{{ $isCancelledApplication ? 'disabled title=Application cancelled. Notifications are disabled.' : '' }}>
					<span>Send Email</span>
				</button>
			</div>
		</div>
	</div>
	@endif

	@include('partials.loader')
	</div>
	</div>

	<script>
		// Global variables from Blade
		const userId = "{{ $user_id }}";
		const vacancyId = "{{ $vacancy_id }}";
		const vacancyType = "{{ $vacancy_type }}"; // Plantilla or COS
		const isCancelledApplication = @json($isCancelledApplication ?? false);
		let documents = @json($documents);
		const requiredDocumentIds = @json($requiredDocumentIds ?? []);
		const requiredDocumentSet = new Set(requiredDocumentIds);
		const QS_RESULT_VALUES = Object.freeze({
			QUALIFIED: 'Qualified',
			NEEDS_REVISIONS: 'Needs Revisions',
			NOT_QUALIFIED: 'Not Qualified',
		});

		function isRequiredDocument(docId) {
			return requiredDocumentSet.has(docId);
		}

		function getApplicationProgressStats() {
			const relevantDocs = documents.filter(doc => isRequiredDocument(doc?.id));
			const docsForProgress = relevantDocs.length > 0 ? relevantDocs : documents;
			const totalDocs = docsForProgress.length;
			const confirmedDocs = docsForProgress.reduce((acc, doc) => (
				doc.status === 'Okay/Confirmed' || doc.status === 'Verified' ? acc + 1 : acc
			), 0);
			const percentage = totalDocs > 0 ? Math.round((confirmedDocs / totalDocs) * 100) : 0;

			return { totalDocs, confirmedDocs, percentage };
		}

		function showCancelledActionBlocked() {
			showAppToast('This application was cancelled by the applicant. No further actions are allowed.');
		}

		function escapeHtml(value) {
			const div = document.createElement('div');
			div.textContent = value ?? '';
			return div.innerHTML;
		}

		function getDocumentLabelHtml(doc) {
			const label = escapeHtml(doc?.text || doc?.name || '');
			const docId = doc?.id || '';
			let suffix = '<span class="text-blue-500 font-semibold ml-1">(if any)</span>';

			if (docId === 'pqe_result') {
				suffix = '<span class="text-blue-500 font-semibold ml-1">(if taken and passed)</span>';
			} else if (isRequiredDocument(docId)) {
				suffix = '<span class="text-red-600 font-semibold ml-1">(required)</span>';
			}

			return `${label} ${suffix}`;
		}

		function docHasUpload(doc) {
			const status = (doc?.status || '').trim();
			// Consider a document as "uploaded / has activity" when it is NOT in a purely not-submitted state
			// and has an actual file preview URL attached.
			if (status === 'Not Submitted' || status === '') return false;
			return !!(doc?.preview && doc.preview !== '');
		}

		function sortDocumentsForRequiredPriority(docList) {
			return [...(docList || [])].sort((a, b) => {
				// Primary: documents with an uploaded/pending file come before not-submitted ones
				const uploadedA = docHasUpload(a) ? 0 : 1;
				const uploadedB = docHasUpload(b) ? 0 : 1;
				if (uploadedA !== uploadedB) return uploadedA - uploadedB;

				// Secondary (within same upload tier): required before "if any"
				const requiredA = isRequiredDocument(a?.id) ? 0 : 1;
				const requiredB = isRequiredDocument(b?.id) ? 0 : 1;
				if (requiredA !== requiredB) return requiredA - requiredB;

				// Tertiary: alphabetical by label
				const labelA = (a?.text || a?.name || a?.id || '').toLowerCase();
				const labelB = (b?.text || b?.name || b?.id || '').toLowerCase();
				return labelA.localeCompare(labelB);
			});
		}

		function getNotifyDocuments(docList) {
			// Only include documents that were actually uploaded by the applicant
			const uploaded = (docList || []).filter(doc => {
				const status = (doc.status || '').trim();
				// Exclude documents that were never submitted / have no file
				if (status === 'Not Submitted') return false;
				// Also exclude if there is no preview URL (no file on record)
				if (!doc.preview || doc.preview === '') return false;
				return true;
			});
			return sortDocumentsForRequiredPriority(uploaded);
		}

		let deadlineHiddenByChoice = false;

		function setDeadlineSectionHidden(hidden, allowRestore = true) {
			const section = document.getElementById('notify-deadline-section');
			const showWrap = document.getElementById('show-deadline-wrap');
			if (!section) return;
			section.classList.toggle('hidden', !!hidden);
			if (showWrap) {
				showWrap.classList.toggle('hidden', !hidden || !allowRestore);
			}
		}

		function getSelectedQsResult() {
			return document.querySelector('input[name="qs_result"]:checked')?.value || QS_RESULT_VALUES.NOT_QUALIFIED;
		}

		function syncNotifyActionRequirementsByQsResult() {
			const selectedQsResult = getSelectedQsResult();
			const actionReqEl = document.getElementById('notify-action-requirements');
			const remarksSectionEl = document.getElementById('notify-remarks-section');
			const clearDeadlineBtn = document.getElementById('clear-deadline-btn');
			const dateInput = document.querySelector('input[name="deadline_date"]');
			const timeInput = document.querySelector('input[name="deadline_time"]');
			const isQualified = selectedQsResult === QS_RESULT_VALUES.QUALIFIED;
			const needsRevisions = selectedQsResult === QS_RESULT_VALUES.NEEDS_REVISIONS;

			if (actionReqEl) {
				actionReqEl.classList.toggle('hidden', isQualified);
			}
			if (remarksSectionEl) {
				remarksSectionEl.classList.toggle('hidden', isQualified);
			}

			if (needsRevisions) {
				deadlineHiddenByChoice = false;
				setDeadlineSectionHidden(false, false);
				if (clearDeadlineBtn) {
					clearDeadlineBtn.classList.add('hidden');
					clearDeadlineBtn.disabled = true;
				}
				if (timeInput && !timeInput.value) {
					timeInput.value = '17:00';
				}
			} else {
				setDeadlineSectionHidden(true, false);
				if (clearDeadlineBtn) {
					clearDeadlineBtn.classList.remove('hidden');
					clearDeadlineBtn.disabled = false;
				}
				if (dateInput) {
					dateInput.value = '';
					dateInput.classList.remove('border-red-500', 'ring-1', 'ring-red-400');
				}
				if (timeInput) {
					timeInput.value = '';
				}
				document.getElementById('deadline-required-error')?.remove();
			}

			checkDeadline();
		}

		document.addEventListener('DOMContentLoaded', function () {
			documents = sortDocumentsForRequiredPriority(documents);

			// Initialize UI
			renderDocuments(documents);
			updateProgressCircle();
			updateQualificationStatus(); // Initial check

			// Initialize deadline check
			checkDeadline();

			// Bind events
			const dateInput = document.querySelector('input[name="deadline_date"]');
			const timeInput = document.querySelector('input[name="deadline_time"]');
			const clearDeadlineBtn = document.getElementById('clear-deadline-btn');
			const showDeadlineBtn = document.getElementById('show-deadline-btn');
			if (dateInput) dateInput.addEventListener('change', checkDeadline);
			if (timeInput) timeInput.addEventListener('change', checkDeadline);
			if (clearDeadlineBtn) {
				clearDeadlineBtn.addEventListener('click', () => {
					deadlineHiddenByChoice = true;
					if (dateInput) dateInput.value = '';
					if (timeInput) timeInput.value = '';
					setDeadlineSectionHidden(true, true);
					checkDeadline();
				});
			}
			if (showDeadlineBtn) {
				showDeadlineBtn.addEventListener('click', () => {
					deadlineHiddenByChoice = false;
					setDeadlineSectionHidden(false, true);
					checkDeadline();
				});
			}

			// Bind radio change events for QS
			const qsRadioNames = ['qs_education', 'qs_eligibility', 'qs_experience', 'qs_training'];
			qsRadioNames.forEach(name => {
				document.querySelectorAll(`input[name="${name}"]`).forEach(radio => {
					radio.addEventListener('change', checkOverallQualification);
				});
			});

			document.querySelectorAll('input[name="qs_result"]').forEach(radio => {
				radio.addEventListener('change', syncNotifyActionRequirementsByQsResult);
			});
		});

		// --- QS Logic ---
		function updateQualificationStatus() {
			// Keep overall result in sync with QS radios and progress rules.
			checkOverallQualification();
		}

		function setQualificationFieldsState(state) {
			const fields = ['qs_education', 'qs_eligibility', 'qs_experience', 'qs_training'];
			const value = state === 'Qualified' ? 'yes' : 'no';
			fields.forEach(field => {
				const target = document.querySelector(`input[name="${field}"][value="${value}"]`);
				if (target) target.checked = true;
			});
		}

		function checkOverallQualification() {
			const selectedQsResult = getSelectedQsResult();
			if (selectedQsResult === QS_RESULT_VALUES.NEEDS_REVISIONS) {
				return;
			}

			const { percentage } = getApplicationProgressStats();
			if (percentage === 100) {
				setQualificationFieldsState('Qualified');
				updateResultButton('Qualified');
				return;
			}

			const fields = ['qs_education', 'qs_eligibility', 'qs_experience', 'qs_training'];
			let allGreen = true;
			let hasRequirements = false;

			fields.forEach(field => {
				const checkedRadio = document.querySelector(`input[name="${field}"]:checked`);
				if (!checkedRadio) return;
				const val = checkedRadio.value;

				if (val === 'na') return; // Skip N/A

				hasRequirements = true;
				if (val !== 'yes') {
					allGreen = false;
				}
			});

			if (hasRequirements && allGreen) {
				updateResultButton('Qualified');
			} else {
				updateResultButton('Not Qualified');
			}
		}

		function updateResultButton(state) {
			const radios = document.querySelectorAll(`input[name="qs_result"]`);
			if (radios.length === 0) return;
			radios.forEach(r => {
				r.checked = (r.value === state);
			});
		}

		// --- Document Logic ---

		// Document Selection State
		let currentSelectedDoc = null;
		const btnVerify = document.getElementById('btn-verify');
		const btnRevision = document.getElementById('btn-revision');
		const docPreview = document.getElementById('doc-preview');
		const previewLoader = document.getElementById('preview-loader');
		const documentContextMenu = document.getElementById('document-context-menu');
		const contextVerifyButton = document.getElementById('context-action-verify');
		const contextRevisionButton = document.getElementById('context-action-revision');
		let contextMenuDocId = null;

		// Keep context menu attached to body for correct fixed positioning.
		if (documentContextMenu && documentContextMenu.parentElement !== document.body) {
			document.body.appendChild(documentContextMenu);
		}

		function closeDocumentContextMenu() {
			if (!documentContextMenu) return;
			documentContextMenu.classList.add('hidden');
			contextMenuDocId = null;
		}

		function getDocumentById(docId) {
			return (documents || []).find(doc => String(doc?.id) === String(docId)) || null;
		}

		function toggleContextActionDisabled(button, disabled, enabledClass, disabledClass) {
			if (!button) return;
			button.disabled = !!disabled;
			button.classList.toggle('opacity-50', !!disabled);
			button.classList.toggle('cursor-not-allowed', !!disabled);
			button.classList.toggle(enabledClass, !disabled);
			button.classList.toggle(disabledClass, !!disabled);
		}

		function executeContextDocumentAction(targetStatus) {
			if (!contextMenuDocId) return;
			const selectedDoc = getDocumentById(contextMenuDocId);
			if (!selectedDoc) {
				closeDocumentContextMenu();
				showAppToast('Unable to find the selected document.');
				return;
			}

			handleDocumentClick(selectedDoc, true);
			closeDocumentContextMenu();
			updateDocumentStatus(targetStatus);
		}

		function openDocumentContextMenu(event, doc) {
			if (isCancelledApplication) {
				showCancelledActionBlocked();
				return;
			}

			if (!documentContextMenu || !doc) return;

			event.preventDefault();
			event.stopPropagation();

			const selectedDoc = getDocumentById(doc.id) || doc;
			handleDocumentClick(selectedDoc, true);
			contextMenuDocId = selectedDoc.id;

			const isVerified = selectedDoc.status === 'Verified' || selectedDoc.status === 'Okay/Confirmed';
			const needsRevision = selectedDoc.status === 'Needs Revision' || selectedDoc.status === 'Disapproved With Deficiency';
			const revisionLocked = !!selectedDoc.revision_locked;

			toggleContextActionDisabled(contextVerifyButton, isVerified, 'hover:bg-green-50', 'bg-green-50');
			toggleContextActionDisabled(contextRevisionButton, needsRevision || revisionLocked, 'hover:bg-red-50', 'bg-red-50');
			if (contextRevisionButton) {
				if (revisionLocked) {
					contextRevisionButton.title = selectedDoc.revision_lock_reason || 'Needs Revision is currently locked for this document.';
				} else {
					contextRevisionButton.removeAttribute('title');
				}
			}

			documentContextMenu.classList.remove('hidden');
			documentContextMenu.style.left = '0px';
			documentContextMenu.style.top = '0px';

			const menuWidth = documentContextMenu.offsetWidth || 176;
			const menuHeight = documentContextMenu.offsetHeight || 80;
			const viewportPadding = 8;

			let left = Number.isFinite(event.clientX) ? event.clientX + 8 : viewportPadding;
			let top = Number.isFinite(event.clientY) ? event.clientY - 6 : viewportPadding;

			if (left + menuWidth + viewportPadding > window.innerWidth) {
				left = Math.max(viewportPadding, window.innerWidth - menuWidth - viewportPadding);
			}
			if (top + menuHeight + viewportPadding > window.innerHeight) {
				top = Math.max(viewportPadding, window.innerHeight - menuHeight - viewportPadding);
			}
			if (top < viewportPadding) {
				top = viewportPadding;
			}

			documentContextMenu.style.left = `${left}px`;
			documentContextMenu.style.top = `${top}px`;
		}

		// Setup Button Listeners once
		if (btnVerify) {
			btnVerify.onclick = (e) => {
				e.preventDefault();
				if (currentSelectedDoc) updateDocumentStatus('Verified');
			};
		}
		if (btnRevision) {
			btnRevision.onclick = (e) => {
				e.preventDefault();
				if (currentSelectedDoc) updateDocumentStatus('Needs Revision');
			};
		}
		if (contextVerifyButton) {
			contextVerifyButton.onclick = (e) => {
				e.preventDefault();
				executeContextDocumentAction('Verified');
			};
		}
		if (contextRevisionButton) {
			contextRevisionButton.onclick = (e) => {
				e.preventDefault();
				executeContextDocumentAction('Needs Revision');
			};
		}
		document.addEventListener('click', (event) => {
			if (!documentContextMenu || documentContextMenu.classList.contains('hidden')) return;
			if (!documentContextMenu.contains(event.target)) {
				closeDocumentContextMenu();
			}
		});
		document.addEventListener('scroll', () => {
			closeDocumentContextMenu();
		}, true);
		window.addEventListener('resize', closeDocumentContextMenu);
		document.addEventListener('keydown', (event) => {
			if (event.key === 'Escape') {
				closeDocumentContextMenu();
			}
		});

		// Helper for status icon
		function getStatusIcon(status) {
			if (status === "Okay/Confirmed" || status === "Verified") {
				return `<svg class="w-4 h-4 inline-block text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>`;
			} else if (status === "Disapproved With Deficiency" || status === "Needs Revision") {
				return `<svg class="w-4 h-4 inline-block text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>`;
			}
			return "";
		}

		function setDocumentRemarksVisibility(show) {
			const section = document.getElementById('document-remarks-section');
			if (!section) return;
			if (show) {
				section.classList.remove('hidden');
			} else {
				section.classList.add('hidden');
			}
		}

		function handleDocumentClick(doc, force = false) {
			closeDocumentContextMenu();

			// Prevent re-clicking same doc to avoid reload
			if (!force && currentSelectedDoc && currentSelectedDoc.id === doc.id) return;

			currentSelectedDoc = doc;

			// Highlight Active Item
			// Since we are re-rendering the whole list anyway in updateDocumentStatus,
			// for simple selection we can just update classes manually to avoid full re-render
			const allButtons = document.querySelectorAll('#document-list button');
			allButtons.forEach(b => {
				b.classList.remove("bg-blue-50", "ring-1", "ring-blue-200");
			});
			const activeLi = document.getElementById(`doc-item-${doc.id}`);
			if (activeLi) {
				const activeBtn = activeLi.querySelector('button');
				if (activeBtn) activeBtn.classList.add("bg-blue-50", "ring-1", "ring-blue-200");
			}

			// Update Header
			document.getElementById('document-title').textContent = doc.name || doc.text;

			const statusTextEl = document.getElementById('document-status-text');
			if (statusTextEl) statusTextEl.classList.remove('hidden');

			const modifiedEl = document.getElementById('document-modified');
			if (modifiedEl) {
				modifiedEl.classList.remove('hidden');
				// Update the name dynamically
				const modifiedSpan = modifiedEl.querySelector('span');
				if (modifiedSpan) {
					modifiedSpan.textContent = doc.last_modified_by || 'N/A';
				}
			}

			updateStatusUI(doc.status);

			// Update Remarks Area
			const remarksEl = document.getElementById('remarks');
			remarksEl.value = doc.remarks || "";

			if (doc.status === "Needs Revision" || doc.status === "Disapproved With Deficiency") {
				setDocumentRemarksVisibility(true);
			} else {
				setDocumentRemarksVisibility(false);
			}

			// Enable/Disable Buttons and Update Text
			if (btnVerify) {
				const isVerified = (doc.status === 'Verified' || doc.status === 'Okay/Confirmed');
				btnVerify.disabled = !!isCancelledApplication;
				btnVerify.textContent = isVerified ? 'Verified' : 'Verify';
				btnVerify.classList.toggle('opacity-50', !!isCancelledApplication);
				btnVerify.classList.toggle('cursor-not-allowed', !!isCancelledApplication);
				btnVerify.classList.toggle('hover:bg-green-50', !isCancelledApplication);
				if (isCancelledApplication) {
					btnVerify.title = 'Application cancelled. Document actions are disabled.';
				} else {
					btnVerify.removeAttribute('title');
				}
				if (isVerified) {
					btnVerify.classList.add('bg-green-50');
				} else {
					btnVerify.classList.remove('bg-green-50');
				}
			}

			if (btnRevision) {
				const needsRevision = (doc.status === 'Needs Revision' || doc.status === 'Disapproved With Deficiency');
				const revisionLocked = !!doc.revision_locked;
				btnRevision.disabled = !!isCancelledApplication || needsRevision || revisionLocked;
				btnRevision.textContent = needsRevision ? 'Needs Revisions' : 'Needs Revisions';
				if (isCancelledApplication) {
					btnRevision.title = 'Application cancelled. Document actions are disabled.';
				} else if (revisionLocked) {
					btnRevision.title = doc.revision_lock_reason || 'Needs Revision is currently locked for this document.';
				} else {
					btnRevision.removeAttribute('title');
				}
				if (isCancelledApplication || needsRevision || revisionLocked) {
					btnRevision.classList.add('opacity-50', 'cursor-not-allowed', 'bg-red-50');
					btnRevision.classList.remove('hover:bg-red-50');
				} else {
					btnRevision.classList.remove('opacity-50', 'cursor-not-allowed', 'bg-red-50');
					btnRevision.classList.add('hover:bg-red-50');
				}
			}

			// Load Preview with Loader
			if (previewLoader) previewLoader.classList.remove('hidden');

			// Small timeout to allow UI to update before iframe load (prevents UI freeze)
			setTimeout(() => {
				if (docPreview) {
					docPreview.onload = () => {
						if (previewLoader) previewLoader.classList.add('hidden');
					};
					docPreview.src = doc.preview || "about:blank";
				}
			}, 10);
		}

		function updateStatusUI(status) {
			const statusValue = document.getElementById('document-status-value');
			statusValue.textContent = status;

			// Reset classes
			statusValue.className = "font-bold";

			if (status === "Verified" || status === "Okay/Confirmed") {
				statusValue.classList.add("text-[#00730A]");
			} else if (status === "Needs Revision" || status === "Disapproved With Deficiency") {
				statusValue.classList.add("text-[#BC0000]");
			} else if (status === "Not Submitted") {
				statusValue.classList.add("text-gray-500");
			} else {
				statusValue.classList.add("text-[#E47E00]");
			}
		}

		async function updateDocumentStatus(newStatus) {
			if (isCancelledApplication) {
				showCancelledActionBlocked();
				return;
			}

			if (!currentSelectedDoc) {
				showAppToast("Please select a document first.");
				return;
			}

			closeDocumentContextMenu();

			const requestingNeedsRevision = newStatus === 'Needs Revision' || newStatus === 'Disapproved With Deficiency';
			if (requestingNeedsRevision && currentSelectedDoc.revision_locked) {
				showAppToast(currentSelectedDoc.revision_lock_reason || 'Needs Revision is currently locked for this document.');
				return;
			}

			const previousSnapshot = {
				status: currentSelectedDoc.status,
				last_modified_by: currentSelectedDoc.last_modified_by,
				remarks: currentSelectedDoc.remarks,
			};

			// Optimistic UI Update
			updateStatusUI(newStatus);
			currentSelectedDoc.status = newStatus;

			// Prepare the current date and time formatting
			const now = new Date();
			const formattedDate = now.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
			const formattedTime = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });

			// Let's use the static admin name passed to the view with timestamp
			const currentAdminName = "{{ Auth::guard('admin')->user()->name ?? 'Me' }}";
			currentSelectedDoc.last_modified_by = `${currentAdminName} on ${formattedDate} ${formattedTime}`;

			// Update the "Last modified by" text immediately
			const modifiedEl = document.getElementById('document-modified');
			if (modifiedEl) {
				const modifiedSpan = modifiedEl.querySelector('span');
				if (modifiedSpan) modifiedSpan.textContent = currentSelectedDoc.last_modified_by;
			}

			// Update visual list item status icon immediately
			const activeLi = document.getElementById(`doc-item-${currentSelectedDoc.id}`);
			if (activeLi) {
				const btn = activeLi.querySelector('button');
				const iconWrapper = btn.querySelector('span:first-child');
				if (iconWrapper) iconWrapper.innerHTML = getStatusIcon(newStatus);

				const textWrapper = btn.querySelector('span:last-child');
				if (textWrapper) {
					textWrapper.className = "text-xs flex-1 break-words ";
					if (newStatus === "Verified" || newStatus === "Okay/Confirmed") {
						textWrapper.classList.add("text-[#00730A]", "font-bold");
					} else if (newStatus === "Needs Revision" || newStatus === "Disapproved With Deficiency") {
						textWrapper.classList.add("text-[#BC0000]", "font-bold");
					} else if (newStatus === "Not Submitted") {
						textWrapper.classList.add("text-gray-400");
					} else {
						textWrapper.classList.add("text-orange-500", "font-medium");
					}
				}
			}

			const remarksEl = document.getElementById('remarks');
			if (newStatus === 'Needs Revision') {
				setDocumentRemarksVisibility(true);
				if (remarksEl) {
					remarksEl.focus();
					if (!remarksEl.value) {
						remarksEl.placeholder = "Add remarks for this document...";
					}
				}
			} else if (newStatus === 'Verified') {
				if (remarksEl) remarksEl.value = "";
				if (currentSelectedDoc) currentSelectedDoc.remarks = "";
				setDocumentRemarksVisibility(false);
			}

			// Immediately re-select the document to trigger all UI states (like disabling buttons)
			handleDocumentClick(currentSelectedDoc, true);

			// Defer heavy updates
			setTimeout(() => {
				updateProgressCircle();
			}, 0);

			try {
				const payload = {
					document_type: currentSelectedDoc.id,
					status: newStatus
				};
				if (newStatus === 'Verified') {
					payload.remarks = "";
				}

				const response = await fetch(`/admin/applicant_status/${userId}/${vacancyId}/update-document`, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
					},
					body: JSON.stringify(payload)
				});

				if (!response.ok) {
					let errorMessage = "Failed to save status.";
					try {
						const errorData = await response.json();
						if (errorData?.message) {
							errorMessage = errorData.message;
						}
					} catch (e) {
						errorMessage = "Failed to save status. Server responded with: " + response.status + " " + response.statusText;
					}
					console.error("Server Error Details:", errorMessage);
					showAppToast(errorMessage);
					throw new Error('Failed to update status');
				}

				let responseData = {};
				try {
					responseData = await response.json();
				} catch (e) {
					responseData = {};
				}
				if (Object.prototype.hasOwnProperty.call(responseData, 'revision_locked')) {
					currentSelectedDoc.revision_locked = !!responseData.revision_locked;
				} else if (requestingNeedsRevision) {
					currentSelectedDoc.revision_locked = true;
				}
				if (Object.prototype.hasOwnProperty.call(responseData, 'revision_lock_reason')) {
					currentSelectedDoc.revision_lock_reason = responseData.revision_lock_reason || '';
				} else if (requestingNeedsRevision) {
					currentSelectedDoc.revision_lock_reason = 'Needs Revision is currently locked for this document.';
				}
				if (Object.prototype.hasOwnProperty.call(responseData, 'revision_requested_count')) {
					currentSelectedDoc.revision_requested_count = responseData.revision_requested_count || 0;
				}
				if (Object.prototype.hasOwnProperty.call(responseData, 'revision_submitted_at')) {
					currentSelectedDoc.revision_submitted_at = responseData.revision_submitted_at || null;
				}
				handleDocumentClick(currentSelectedDoc, true);

			} catch (error) {
				console.error(error);
				currentSelectedDoc.status = previousSnapshot.status;
				currentSelectedDoc.last_modified_by = previousSnapshot.last_modified_by;
				currentSelectedDoc.remarks = previousSnapshot.remarks;
				handleDocumentClick(currentSelectedDoc, true);
			}
		}

		async function quickVerifyDocument(doc) {
			if (isCancelledApplication) {
				showCancelledActionBlocked();
				return;
			}

			if (!doc) return;

			// Keep preview/header in sync with the item being quick-verified.
			handleDocumentClick(doc, true);

			const isVerified = doc.status === 'Verified' || doc.status === 'Okay/Confirmed';
			if (isVerified) return;

			await updateDocumentStatus('Verified');
		}

		// Auto-save Document Remarks
		let docRemarksTimeout;
		const remarksInputEl = document.getElementById('remarks');
		if (remarksInputEl) {
			remarksInputEl.addEventListener('input', function (e) {
				if (!currentSelectedDoc) return;

			const value = e.target.value;
			currentSelectedDoc.remarks = value;

			// Show "Saving..." state immediately
			const statusEl = document.getElementById('remarks-status');
			statusEl.textContent = "Saving...";
			statusEl.classList.remove('opacity-0', 'text-green-600');
			statusEl.classList.add('opacity-100', 'text-orange-500');

			clearTimeout(docRemarksTimeout);
			docRemarksTimeout = setTimeout(async () => {
				try {
					const response = await fetch(`/admin/applicant_status/${userId}/${vacancyId}/update-document`, {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json',
							'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
						},
						body: JSON.stringify({
							document_type: currentSelectedDoc.id,
							remarks: value
						})
					});

					if (!response.ok) {
						// ... error handling ...
					} else {
						// Show Saved
						statusEl.textContent = "Saved";
						statusEl.classList.remove('text-orange-500');
						statusEl.classList.add('text-green-600');

						// Keep "Saved" visible for 2 seconds then fade out
						setTimeout(() => {
							statusEl.classList.remove('opacity-100');
							statusEl.classList.add('opacity-0');
						}, 2000);
					}

				} catch (error) {
					console.error(error);
					statusEl.textContent = "Error";
					statusEl.classList.add('text-red-600');
				}
			}, 500); // 500ms delay to batch keystrokes slightly but feel responsive
			});
		}

		// Auto-save Application Remarks (Moved to Modal)
		let appRemarksTimeout;
		const notifyRemarksInput = document.getElementById('notify-applicant-remarks');
		if (notifyRemarksInput && !isCancelledApplication) {
			notifyRemarksInput.addEventListener('input', function (e) {
				const value = e.target.value;

				const statusEl = document.getElementById('notify-remarks-status');
				if (statusEl) {
					statusEl.classList.remove('opacity-100');
					statusEl.classList.add('opacity-0');
				}

				clearTimeout(appRemarksTimeout);
				appRemarksTimeout = setTimeout(async () => {
					try {
						await fetch(`/admin/applicant_status/${userId}/${vacancyId}/update-remarks`, {
							method: 'POST',
							headers: {
								'Content-Type': 'application/json',
								'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
							},
							body: JSON.stringify({
								application_remarks: value
							})
						});

						if (statusEl) {
							statusEl.classList.remove('opacity-0');
							statusEl.classList.add('opacity-100');
							setTimeout(() => {
								statusEl.classList.remove('opacity-100');
								statusEl.classList.add('opacity-0');
							}, 2000);
						}
					} catch (error) {
						console.error(error);
					}
				}, 1500);
			});
		}

		async function notifyApplicant() {
			if (isCancelledApplication) {
				showCancelledActionBlocked();
				return;
			}

			const selectedQsResult = getSelectedQsResult();
			const deadlineEnabled = selectedQsResult === QS_RESULT_VALUES.NEEDS_REVISIONS;
			if (deadlineEnabled) {
				const dateInput = document.querySelector('input[name="deadline_date"]');
				if (!dateInput || !dateInput.value) {
					dateInput?.classList.add('border-red-500', 'ring-1', 'ring-red-400');
					dateInput?.focus();
					// Show inline error
					let errEl = document.getElementById('deadline-required-error');
					if (!errEl) {
						errEl = document.createElement('p');
						errEl.id = 'deadline-required-error';
						errEl.className = 'text-red-500 text-xs mt-1 font-medium';
						errEl.textContent = 'Please set a deadline date for Needs Revisions.';
						dateInput?.parentElement?.insertAdjacentElement('afterend', errEl);
					}
					dateInput?.addEventListener('input', () => {
						dateInput.classList.remove('border-red-500', 'ring-1', 'ring-red-400');
						document.getElementById('deadline-required-error')?.remove();
					}, { once: true });
					return;
				}
			}

			const btn = document.getElementById('confirm-notify-btn');
			const originalContent = btn.innerHTML;
			btn.disabled = true;
			btn.innerHTML = `
				<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
					<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
					<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
				</svg>
				Sending...
				`;
			btn.classList.add("opacity-75", "cursor-not-allowed");

			try {
				// Gather data to save before notifying
				const dateInput = document.querySelector('input[name="deadline_date"]');
				const timeInput = document.querySelector('input[name="deadline_time"]');

				const payload = {
					deadline_enabled: deadlineEnabled ? 1 : 0,
					deadline_date: deadlineEnabled && dateInput ? (dateInput.value || null) : null,
					deadline_time: deadlineEnabled && timeInput ? (timeInput.value || null) : null,
					qs_education: document.querySelector('input[name="qs_education"]:checked')?.value || 'na',
					qs_eligibility: document.querySelector('input[name="qs_eligibility"]:checked')?.value || 'na',
					qs_experience: document.querySelector('input[name="qs_experience"]:checked')?.value || 'na',
					qs_training: document.querySelector('input[name="qs_training"]:checked')?.value || 'na',
					qs_result: selectedQsResult,
				};

				const response = await fetch(`/admin/applicant_status/${userId}/${vacancyId}/notify`, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
					},
					body: JSON.stringify(payload)
				});

				// Handle non-JSON responses (like 500 errors from Laravel)
				const contentType = response.headers.get("content-type");
				let data;
				if (contentType && contentType.indexOf("application/json") !== -1) {
					data = await response.json();
				} else {
					const text = await response.text();
					throw new Error("Server Error: " + response.status + " " + response.statusText);
				}

				if (response.ok && data && data.success !== false) {
					showAppToast(data.message || "Email sent successfully!");
					closeNotifyModal();
				} else {
					showAppToast(data?.message || "Failed to send email.");
				}
			} catch (error) {
				console.error(error);
				showAppToast("An error occurred while sending the notification: " + error.message);
			} finally {
				btn.disabled = false;
				btn.innerHTML = originalContent;
				btn.classList.remove("opacity-75", "cursor-not-allowed");
			}
		}

		function openNotifyModal() {
			if (isCancelledApplication) {
				showCancelledActionBlocked();
				return;
			}

			const bodyEl = document.getElementById('notify-documents-body');
			const remarksSummaryEl = document.getElementById('notify-applicant-remarks');
			if (!bodyEl || !remarksSummaryEl) return;

			const jobEl = document.getElementById('notify-job-applied');
			const placeEl = document.getElementById('notify-place-of-assignment');
			const compEl = document.getElementById('notify-compensation');
			const deadlineEl = document.getElementById('notify-deadline');
			const qsListEl = document.getElementById('notify-qs-list');
			const progressBarEl = document.getElementById('notify-progress-bar');
			const progressPctEl = document.getElementById('notify-progress-percentage');
			const progressCountEl = document.getElementById('notify-progress-count');

			if (jobEl) {
				jobEl.textContent = "{{ $job_applied }}, {{ $vacancy_type }} position";
			}
			if (placeEl) {
				placeEl.textContent = "{{ $place_of_assignment }}";
			}
			if (compEl) {
				compEl.textContent = "PHP {{ number_format($compensation, 2) }}";
			}
			if (qsListEl) {
				qsListEl.innerHTML = "";
				const qsItems = [
					{ field: 'qs_education', label: 'Education' },
					{ field: 'qs_eligibility', label: 'Eligibility' },
					{ field: 'qs_experience', label: 'Experience' },
					{ field: 'qs_training', label: 'Training' }
				];
				qsItems.forEach(item => {
					const input = document.querySelector(`input[name="${item.field}"]:checked`);
					const value = input ? input.value : '';
					let text = 'N/A';
					let color = 'text-gray-500';
					if (value === 'yes') {
						text = 'Meets standard';
						color = 'text-green-600';
					} else if (value === 'no') {
						text = 'Does not meet standard';
						color = 'text-red-600';
					}
					const li = document.createElement('li');
					li.innerHTML = `<span class="font-semibold">${item.label}:</span> <span class="${color}">${text}</span>`;
					qsListEl.appendChild(li);
				});
			}

			if (progressBarEl && progressPctEl && progressCountEl) {
				const srcPct = document.getElementById('progress-percentage');
				const srcCount = document.getElementById('progress-count');
				const pctText = srcPct ? srcPct.textContent : '0%';
				const countText = srcCount ? srcCount.textContent : '0/0';
				progressPctEl.textContent = pctText;
				progressCountEl.textContent = countText;
				const pctNumber = parseInt(pctText.replace('%', '')) || 0;
				progressBarEl.style.width = pctNumber + '%';
			}

			const notifyDocs = getNotifyDocuments(documents);
			let rowsHtml = "";
			notifyDocs.forEach(doc => {
				const status = doc.status || "";
				const iconHtml = getStatusIcon(status);
				let remarksText = "";
				if (status === "Needs Revision" || status === "Disapproved With Deficiency") {
					remarksText = doc.remarks || "";
				}
				rowsHtml += `
					<tr class="hover:bg-gray-50/50 transition-colors">
						<td class="px-5 py-4 align-top text-gray-800 font-medium w-[40%]">${getDocumentLabelHtml(doc)}</td>
						<td class="px-5 py-4 align-top text-gray-700 w-[25%] whitespace-nowrap">
							<div class="flex items-center gap-2">
								<span>${iconHtml}</span>
								<span class="font-semibold text-xs">${status}</span>
							</div>
						</td>
						<td class="px-5 py-4 align-top text-gray-600 text-xs italic">${remarksText || '<span class="text-gray-300">No remarks</span>'}</td>
					</tr>
				`;
			});
			bodyEl.innerHTML = rowsHtml;

			// Logic to hide/show action requirements based on Overall Standard result.
			syncNotifyActionRequirementsByQsResult();

			const modal = document.getElementById('notify-modal');
			const appWrapper = document.getElementById('app-wrapper');
			if (modal) {
				// Move modal to body so it sits above the blurred wrapper
				document.body.appendChild(modal);
				modal.classList.remove('hidden');
			}
			if (appWrapper) {
				appWrapper.style.filter = 'blur(6px)';
				appWrapper.style.transition = 'filter 0.2s ease';
				appWrapper.style.pointerEvents = 'none';
				appWrapper.style.userSelect = 'none';
			}
		}

		function closeNotifyModal() {
			const modal = document.getElementById('notify-modal');
			const appWrapper = document.getElementById('app-wrapper');
			if (modal) {
				modal.classList.add('hidden');
			}
			if (appWrapper) {
				appWrapper.style.filter = '';
				appWrapper.style.pointerEvents = '';
				appWrapper.style.userSelect = '';
			}
		}

		// Render documents list
		function renderDocuments(docList) {
			const listEl = document.getElementById('document-list');
			if (!listEl) return;
			listEl.innerHTML = "";

			let lastHadUpload = null; // track group transitions

			docList.forEach((doc, index) => {
				const hasUpload = docHasUpload(doc);

				// Insert a divider when transitioning from uploaded group to not-submitted group
				if (lastHadUpload !== null && lastHadUpload === true && !hasUpload) {
					const divider = document.createElement('li');
					divider.setAttribute('aria-hidden', 'true');
					divider.className = "my-2 border-t border-dashed border-gray-300 flex items-center gap-2 px-1";
					const label = document.createElement('span');
					label.className = "text-[10px] text-gray-400 uppercase tracking-wide whitespace-nowrap";
					label.textContent = "Not yet submitted";
					divider.appendChild(label);
					listEl.appendChild(divider);
				}
				lastHadUpload = hasUpload;

				const li = document.createElement('li');
				li.id = `doc-item-${doc.id}`;
				li.className = "mb-1"; // minimal margin

				const btn = document.createElement('button');
				btn.type = "button";
				// Completely standard block button to avoid stacking context issues
				btn.className = "w-full text-left p-2 rounded-md hover:bg-gray-100 flex items-start gap-2 transition-colors duration-150 border border-transparent focus:outline-none focus:ring-2 focus:ring-blue-200";

				if (doc.isBold) btn.classList.add('font-bold');
				if (doc.italic) btn.classList.add('italic');

				// Active state style
				if (currentSelectedDoc && currentSelectedDoc.id === doc.id) {
					btn.classList.add("bg-blue-50", "ring-1", "ring-blue-200");
				}

				let icon = getStatusIcon(doc.status);

				// Setup text color based on status
				let textColorClass = "text-gray-700";

				if (doc.status === "Verified" || doc.status === "Okay/Confirmed") {
					textColorClass = "text-[#00730A] font-bold";
				} else if (doc.status === "Needs Revision" || doc.status === "Disapproved With Deficiency") {
					textColorClass = "text-[#BC0000] font-bold";
				} else if (doc.status === "Not Submitted") {
					textColorClass = "text-gray-400";
				} else {
					textColorClass = "text-orange-500 font-medium";
				}

				const iconWrapper = document.createElement('span');
				iconWrapper.className = "mt-0.5 flex-shrink-0 w-4 h-4 flex items-center justify-center";
				iconWrapper.innerHTML = icon;

				const textWrapper = document.createElement('span');
				textWrapper.className = `${textColorClass} text-xs flex-1 break-words`;
				textWrapper.innerHTML = getDocumentLabelHtml(doc);

				btn.appendChild(iconWrapper);
				btn.appendChild(textWrapper);
				btn.onclick = function (e) {
					e.preventDefault();
					handleDocumentClick(doc);
				};
				btn.addEventListener('contextmenu', async function (e) {
					openDocumentContextMenu(e, doc);
				});

				li.appendChild(btn);
				listEl.appendChild(li);
			});
		}

		// Update Progress Bar
		function updateProgressCircle() {
			const { totalDocs, confirmedDocs, percentage } = getApplicationProgressStats();

			const bar = document.getElementById('linear-progress-bar');
			if (bar) {
				bar.style.width = percentage + '%';
				if (percentage === 100) {
					bar.classList.remove('bg-[#002C76]');
					bar.classList.add('bg-[#10B981]');
				} else {
					bar.classList.add('bg-[#002C76]');
					bar.classList.remove('bg-[#10B981]');
				}
			}

			const percentageText = document.getElementById('progress-percentage');
			if (percentageText) percentageText.textContent = percentage + '%';

			const countText = document.getElementById('progress-count');
			if (countText) countText.textContent = `${confirmedDocs}/${totalDocs}`;

			checkOverallQualification();

			// Tooltip updates can be kept simple or removed if not critical
		}

		// Deadline logic
		function checkDeadline() {
			const dateInput = document.querySelector('input[name="deadline_date"]');
			const timeInput = document.querySelector('input[name="deadline_time"]');
			const warningDiv = document.getElementById("deadlineWarning");
			if (!dateInput || !warningDiv) return;

			const date = dateInput.value;
			const time = timeInput ? (timeInput.value || '23:59:59') : '23:59:59';

			if (!date) {
				warningDiv.classList.add('hidden');
				return;
			}

			const deadline = new Date(`${date}T${time}`);
			const now = new Date();

			if (now > deadline) {
				warningDiv.classList.remove('hidden');
			} else {
				warningDiv.classList.add('hidden');
			}
		}

		function goBack() {
			const referrer = document.referrer;
			if (referrer && referrer !== window.location.href) {
				window.location.href = referrer;
			} else {
				window.history.back();
			}
		}
	</script>

	<style>
		.input-toggle {
			display: none;
		}

		.toggle-label {
			display: flex;
			align-items: center;
			gap: 10px;
			cursor: pointer;
			user-select: none;
		}

		/* Smaller toggle button */
		.toggle-circle {
			position: relative;
			width: 36px;
			height: 36px;
			background-color: #f87171;
			/* rose-400 */
			border-radius: 9999px;
			box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
			transition: background-color 0.3s ease;
		}

		.toggle-circle::after {
			content: 'âœ–ï¸';
			position: absolute;
			top: 3px;
			left: 3px;
			width: 30px;
			height: 30px;
			background-color: #f9fafb;
			/* gray-50 */
			display: flex;
			justify-content: center;
			align-items: center;
			border-radius: 9999px;
			font-size: 16px;
			transform: rotate(-180deg);
			transition: all 0.5s ease;
		}

		.input-toggle:checked+.toggle-label .toggle-circle {
			background-color: #10b981;
			/* emerald-500 */
		}

		.input-toggle:checked+.toggle-label .toggle-circle::after {
			content: 'âœ”ï¸';
			transform: rotate(0deg);
		}

		.toggle-label:hover .toggle-circle::after {
			transform: scale(0.85);
		}

		.toggle-text {
			position: relative;
			overflow: hidden;
			display: grid;
			font-size: 0.875rem;
			/* text-sm */
			font-weight: 600;
			min-width: 110px;
			/* ðŸ‘ˆ Enough room for longer phrases */
			text-align: left;
			/* Optional: aligns text better */
			line-height: 1.2;
		}


		.toggle-text span {
			grid-column: 1;
			grid-row: 1;
			transition: all 0.4s ease-in-out;
			white-space: nowrap;
		}

		.toggle-text .option-1 {
			transform: translateY(0);
			opacity: 1;
		}

		.input-toggle:checked+.toggle-label .toggle-text .option-1 {
			transform: translateY(-100%);
			opacity: 0;
		}

		.toggle-text .option-2 {
			transform: translateY(100%);
			opacity: 0;
		}

		.input-toggle:checked+.toggle-label .toggle-text .option-2 {
			transform: translateY(0);
			opacity: 1;
		}

		@keyframes fadeSlideDown {
			0% {
				opacity: 0;
				transform: translateY(-5px);
			}

			100% {
				opacity: 1;
				transform: translateY(0);
			}
		}

		@keyframes fadeSlideUp {
			0% {
				opacity: 1;
				transform: translateY(0);
			}

			100% {
				opacity: 0;
				transform: translateY(-5px);
			}
		}

		.animate-fadeSlideDown {
			animation: fadeSlideDown 0.3s ease-out;
		}

		.animate-fadeSlideUp {
			animation: fadeSlideUp 0.3s ease-in;
		}
	</style>
@endsection
