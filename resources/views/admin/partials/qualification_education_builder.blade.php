@php
    $educationRequirementValue = old('qualification_education', $formSource?->qualification_education ?? '');
@endphp

<div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 md:col-span-2">
    <h3 class="text-sm font-semibold text-slate-900">Education requirement</h3>

    <input type="hidden" id="qualification_education" name="qualification_education" value="{{ $educationRequirementValue }}">
    <input type="hidden" id="qualification_education_config" name="qualification_education_config" value="">

    <div class="mt-4 rounded-xl border border-slate-200 bg-white p-4">
        <label for="minimum_education_code" class="mb-2 block text-sm font-medium text-slate-700">
            Highest educational attainment <span class="text-red-600">*</span>
        </label>
        <select id="minimum_education_code" name="minimum_education_code" class="{{ $fieldInput }}">
            <option value="">Select education</option>
            <option value="HIGH_SCHOOL_GRAD">Junior High School Graduate</option>
            <option value="SENIOR_HIGH_SCHOOL_GRAD">Senior High School Graduate</option>
            <option value="COLLEGE_2Y">Completion of 2 Years in College</option>
            <option value="BACHELOR">Bachelors Degree</option>
            <option value="MASTERAL">Masteral Degree</option>
            <option value="DOCTORATE">Doctorate Degree</option>
        </select>

        <div id="education_detail_wrap" class="mt-4 hidden rounded-xl border border-slate-200 bg-slate-50 p-3">
            <p id="education_detail_group_label" class="mb-2 text-sm font-medium text-slate-700">Degree requirement</p>
            <div class="grid gap-2 md:grid-cols-2">
                <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700">
                    <input id="education_detail_any" type="radio" name="education_detail_mode" value="ANY" class="h-4 w-4 border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span id="education_detail_any_label">Any bachelors degree</span>
                </label>
                <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700">
                    <input id="education_detail_specific" type="radio" name="education_detail_mode" value="SPECIFIC" class="h-4 w-4 border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span id="education_detail_specific_label">Specific bachelors degree</span>
                </label>
            </div>
        </div>

        <div id="education_specific_picker_wrap" class="mt-4 hidden">
            <label id="education_specific_picker_label" class="mb-2 block text-sm font-medium text-slate-700">Degree</label>
            <div id="education_specific_rows" class="space-y-2"></div>
            <button
                type="button"
                id="education_add_specific_btn"
                class="mt-2 inline-flex items-center text-sm font-medium text-blue-700 hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500/30 rounded-md px-1 py-0.5"
            >
                + Add another accepted degree/course
            </button>

            <template id="education_specific_row_template">
                <div class="education-specific-row flex items-start gap-2">
                    <div class="relative flex-1">
                        <input
                            type="text"
                            autocomplete="off"
                            class="{{ $fieldInput }}"
                            placeholder="Search or select degree/course"
                            data-role="specific-input">
                        <div
                            class="absolute left-0 right-0 z-40 mt-1 hidden rounded-xl border border-slate-200 bg-white shadow-lg"
                            data-role="specific-menu"
                        >
                            <div class="max-h-56 overflow-auto py-1" data-role="specific-options"></div>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="mt-2 hidden rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50"
                        data-role="remove-specific-row"
                    >
                        Remove
                    </button>
                </div>
            </template>
        </div>
    </div>

    <div id="education_preview_wrap" class="mt-4 hidden rounded-xl border border-slate-200 bg-white p-3">
        <p id="education_preview_text" class="text-sm text-slate-900"></p>
    </div>

    <p id="qualification_education_error" class="mt-2 hidden text-sm text-red-600">
        Education requirement is required.
    </p>
</div>
