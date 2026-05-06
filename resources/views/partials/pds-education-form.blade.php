<section class="mb-10">
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

        /* Floating label styles */
        .floating-label {
            transition: all 0.2s ease-out;
        }

        .floating-label-input:focus + .floating-label,
        .floating-label-input:not(:placeholder-shown) + .floating-label {
            transform: translateY(-1.25rem) scale(0.85);
            color: #6B7280;
            background-color: white;
            padding: 0 0.25rem;
        }

        /* Mobile stack for better mobile experience */
        .mobile-stack > div {
            margin-bottom: 1rem;
        }

        @media (min-width: 768px) {
            .mobile-stack > div {
                margin-bottom: 0;
            }
        }

        /* Entry card styles */
        .entry-card {
            background: white;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .entry-card:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        @media (min-width: 640px) {
            .entry-card {
                padding: 1.5rem;
            }
        }

        /* Button styles */
        .add-btn {
            background-color: #2563eb;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
        }

        .add-btn:hover {
            background-color: #1d4ed8;
        }

        @media (min-width: 640px) {
            .add-btn {
                font-size: 1rem;
                padding: 0.5rem 1rem;
            }
        }

        .remove-btn {
            color: #ef4444;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 0.25rem;
            transition: all 0.2s;
        }

        .remove-btn:hover {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .required-asterisk {
            color: #f59e0b;
        }
    </style>
    <!-- add-btn w-full sm:w-auto justify-center sm:justify-start -->
     
     <!-- use-loader text-green-600 border border-green-400 font-bold py-1 px-4 rounded-md text-sm 
            transition-all duration-300 hover:scale-105 hover:bg-green-400 
            hover:text-white hover:shadow-md inline-flex items-center gap-2 mx-auto sm:w-auto w-full justify-center sm:justify-start -->

    <div class="mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 gap-3">
            <h3 class="text-base sm:text-lg font-semibold text-gray-700">{{ strtoupper($education_type_meta['title']) }}</h3>
            <button type="button" 
            class="use-loader text-white bg-[#002C76] border border-[#002C76] font-bold py-1 px-4 
            rounded-md text-sm transition-all duration:300 hover:scal-105 hover:bg-white
            hover:text-[#002C76] hover:shadow-md inline-flex items-center gap-2
            w-full sm:w-auto justify-center sm:justify-start" 
            data-education-add="{{ $education_type }}"
            onclick="addEducationRow('{{ $education_type }}')">
                <span class="material-icons" style="font-size: 20px;">add</span>
                Add {{ ucfirst($education_type) }}
            </button>
        </div>

        @php
            $oldEducationData = old($education_type, $education_data ?? []);

            if (empty($oldEducationData)) {
                $oldEducationData[] = [
                    'from' => '',
                    'to' => '',
                    'school' => '',
                    'basic' => '',
                    'earned' => '',
                    'year_graduated' => '',
                    'academic_honors' => '',
                ];
            }
@endphp

        @php
            $normalizeEducationDateForInput = static function ($value) {
                $value = is_string($value) ? trim($value) : '';

                if ($value === '') {
                    return '';
                }

                if (preg_match('/^\d{4}$/', $value)) {
                    return $value;
                }

                try {
                    if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $value)) {
                        return \Carbon\Carbon::createFromFormat('d-m-Y', $value)->format('Y');
                    }

                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                        return \Carbon\Carbon::createFromFormat('Y-m-d', $value)->format('Y');
                    }

                    if (preg_match('/^\d{2}-\d{4}$/', $value)) {
                        return \Carbon\Carbon::createFromFormat('m-Y', $value)->format('Y');
                    }
                } catch (\Throwable $e) {
                    return '';
                }

                return '';
            };
        @endphp

        <div id="{{ $education_type }}-container">
            @foreach ($oldEducationData as $index => $data)
                <div class="education-entry animate-slide-in" data-index="{{ $index }}" data-education-date-range>
                    <div class="entry-card">
                        <div class="flex justify-between items-start mb-4">
                            <h4 class="entry-number text-base sm:text-lg font-medium text-gray-700">#{{ $index + 1 }}</h4>
                            <button type="button" class="remove-btn" onclick="removeEducationRow(this, '{{ $education_type }}')">
                                <span class="material-icons">close</span>
                            </button>
                        </div>

                        <div class="mobile-stack md:grid md:grid-cols-4 gap-4 sm:gap-6">
                            <div class="relative md:col-span-2">
                                <input type="text"
                                       name="{{ $education_type }}[{{ $index }}][school]"
                                       value="{{ old($education_type.'.'.$index.'.school', $data['school'] ?? '') }}"
                                       placeholder=" "
                                       class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base"
                                       {{ $education_type == 'college' ? 'required' : '' }}>
                                <label class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">
                                    School Name {!! $education_type == 'college' ? '<span class="text-red-500">*</span>' : '' !!}
                                </label>
                            </div>

                            @if (in_array($education_type, ['college', 'grad'], true))
                                <div class="relative md:col-span-2">
                                    <input type="text"
                                           name="{{ $education_type }}[{{ $index }}][basic]"
                                           value="{{ old($education_type.'.'.$index.'.basic', $data['basic'] ?? '') }}"
                                           placeholder=" "
                                           data-program-input
                                           data-program-source="{{ $education_type === 'grad' ? 'grad' : 'college' }}"
                                           class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base"
                                           {{ $education_type === 'college' ? 'required' : '' }}>
                                    <label class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">
                                        Degree/Course{!! $education_type === 'college' ? ' <span class="text-red-500">*</span>' : '' !!}
                                    </label>
                                    <div
                                        class="absolute left-0 right-0 z-40 mt-1 hidden rounded-xl border border-slate-200 bg-white shadow-lg"
                                        data-program-role="menu"
                                        data-program-source="{{ $education_type === 'grad' ? 'grad' : 'college' }}"
                                    >
                                        <div class="max-h-56 overflow-auto py-1" data-program-role="options" data-program-source="{{ $education_type === 'grad' ? 'grad' : 'college' }}"></div>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">Search from the list or type if your degree/course is not available.</p>
                                </div>
                            @else
                                <div class="relative md:col-span-2">
                                    <input type="text"
                                           name="{{ $education_type }}[{{ $index }}][basic]"
                                           value="{{ old($education_type.'.'.$index.'.basic', $data['basic'] ?? '') }}"
                                           placeholder=" "
                                           class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base"
                                           {{ $education_type == 'college' ? 'required' : '' }}>
                                    <label class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">
                                        Degree/Course{!! $education_type == 'college' ? '<span class="text-red-500">*</span>' : '' !!}
                                    </label>
                                </div>
                            @endif

                            <div class="relative">
                                <input type="text"
                                       aria-label="From date"
                                       name="{{ $education_type }}[{{ $index }}][from]"
                                       value="{{ $normalizeEducationDateForInput(old($education_type.'.'.$index.'.from', $data['from'] ?? '')) }}"
                                       data-education-date-role="from"
                                       autocomplete="off"
                                    maxlength="4"
                                    pattern="[0-9]{4}"
                                    inputmode="numeric"
                                    placeholder="YYYY"
                                       class="edu-date w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm sm:text-base"
                                       {{ $education_type == 'college' ? 'required' : '' }}>
                                <label class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">
                                    From{!! $education_type == 'college' ? '<span class="text-red-500">*</span>' : '' !!}
                                </label>
                            </div>

                            <div class="relative">
                                <input type="text"
                                       aria-label="To date"
                                       name="{{ $education_type }}[{{ $index }}][to]"
                                       value="{{ $normalizeEducationDateForInput(old($education_type.'.'.$index.'.to', $data['to'] ?? '')) }}"
                                       data-education-date-role="to"
                                       autocomplete="off"
                                    maxlength="4"
                                    pattern="[0-9]{4}"
                                    inputmode="numeric"
                                    placeholder="YYYY"
                                       class="edu-date w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm sm:text-base"
                                       {{ $education_type == 'college' ? 'required' : '' }}>
                                <label class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">
                                    To{!! $education_type == 'college' ? '<span class="text-red-500">*</span>' : '' !!}
                                </label>
                                <p class="error-message hidden" data-education-date-error aria-live="polite"></p>
                            </div>

                            <div class="relative md:col-span-2">
                                <input type="text"
                                       pattern="(?:[0-9]{4}|[Nn][/]?[Aa])"
                                       maxlength="4"
                                       inputmode="text"
                                       name="{{ $education_type }}[{{ $index }}][year_graduated]"
                                       value="{{ old($education_type.'.'.$index.'.year_graduated', $data['year_graduated'] ?? '') }}"
                                       placeholder=" "
                                       class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base"
                                       data-education-year-graduated
                                       {{ $education_type == 'college' ? '' : '' }}>
                                <label class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">
                                    Year Graduated
                                </label>
                            </div>

                            <div class="relative md:col-span-2">
                                <input type="text"
                                       name="{{ $education_type }}[{{ $index }}][earned]"
                                       value="{{ old($education_type.'.'.$index.'.earned', $data['earned'] ?? '') }}"
                                       placeholder=" "
                                       class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base"
                                       data-education-earned>
                                <label class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-xs sm:text-sm">
                                    Highest Level/Units Earned (if not graduated){!! $education_type == 'college' ? '<span class="text-red-500 earned-required-asterisk">*</span>' : '' !!}
                                </label>
                            </div>

                            <div class="relative md:col-span-2">
                                <input type="text"
                                       name="{{ $education_type }}[{{ $index }}][academic_honors]"
                                       value="{{ old($education_type.'.'.$index.'.academic_honors', $data['academic_honors'] ?? '') }}"
                                       placeholder=" "
                                       class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                                <label class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">
                                    Scholarship/Academic Honors Received
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Hidden template -->
        <template id="{{ $education_type }}-template">
            <div class="education-entry animate-slide-in" data-index="__INDEX__" data-education-date-range>
                <div class="entry-card">
                    <div class="flex justify-between items-start mb-4">
                        <h4 class="entry-number text-base sm:text-lg font-medium text-gray-700">#__DISPLAY_INDEX__</h4>
                        <button type="button" class="remove-btn" onclick="removeEducationRow(this, '{{ $education_type }}')">
                            <span class="material-icons">close</span>
                        </button>
                    </div>

                    <div class="mobile-stack md:grid md:grid-cols-4 gap-4 sm:gap-6">
                        <div class="relative md:col-span-2">
                            <input type="text"
                                   name="{{ $education_type }}[__INDEX__][school]"
                                   value=""
                                   placeholder=" "
                                   class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base"
                                   {{ $education_type == 'college' ? 'required' : '' }}>
                            <label class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">
                                School Name{!! $education_type == 'college' ? ' <span class="text-red-500">*</span>' : '' !!}
                            </label>
                        </div>

                        @if (in_array($education_type, ['college', 'grad'], true))
                            <div class="relative md:col-span-2">
                                <input type="text"
                                       name="{{ $education_type }}[__INDEX__][basic]"
                                       value=""
                                       placeholder=" "
                                       data-program-input
                                       data-program-source="{{ $education_type === 'grad' ? 'grad' : 'college' }}"
                                       class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base"
                                       {{ $education_type === 'college' ? 'required' : '' }}>
                                <label class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">
                                    Degree/Course{!! $education_type === 'college' ? ' <span class="text-red-500">*</span>' : '' !!}
                                </label>
                                <div
                                    class="absolute left-0 right-0 z-40 mt-1 hidden rounded-xl border border-slate-200 bg-white shadow-lg"
                                    data-program-role="menu"
                                    data-program-source="{{ $education_type === 'grad' ? 'grad' : 'college' }}"
                                >
                                    <div class="max-h-56 overflow-auto py-1" data-program-role="options" data-program-source="{{ $education_type === 'grad' ? 'grad' : 'college' }}"></div>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Search from the list or type if your degree/course is not available.</p>
                            </div>
                        @else
                            <div class="relative md:col-span-2">
                                <input type="text"
                                       name="{{ $education_type }}[__INDEX__][basic]"
                                       value=""
                                       placeholder=" "
                                       class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base"
                                       {{ $education_type == 'college' ? 'required' : '' }}>
                                <label class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">
                                    Degree/Course{!! $education_type == 'college' ? ' <span class="text-red-500">*</span>' : '' !!}
                                </label>
                            </div>
                        @endif

                        <div class="relative">
                             <input type="text"
                                    aria-label="From date"
                                    name="{{ $education_type }}[__INDEX__][from]"
                                    value=""
                                    data-education-date-role="from"
                                    autocomplete="off"
                                 maxlength="4"
                                 pattern="[0-9]{4}"
                                 inputmode="numeric"
                                 placeholder="YYYY"
                                    class="edu-date w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm sm:text-base"
                                    {{ $education_type == 'college' ? 'required' : '' }}>
                            <label class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">
                                From{!! $education_type == 'college' ? ' <span class="text-red-500">*</span>' : '' !!}
                            </label>
                        </div>

                        <div class="relative">
                             <input type="text"
                                    aria-label="To date"
                                    name="{{ $education_type }}[__INDEX__][to]"
                                    value=""
                                    data-education-date-role="to"
                                    autocomplete="off"
                                 maxlength="4"
                                 pattern="[0-9]{4}"
                                 inputmode="numeric"
                                 placeholder="YYYY"
                                    class="edu-date w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm sm:text-base"
                                    {{ $education_type == 'college' ? 'required' : '' }}>
                             <label class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">
                                 To{!! $education_type == 'college' ? ' <span class="text-red-500">*</span>' : '' !!}
                             </label>
                             <p class="error-message hidden" data-education-date-error aria-live="polite"></p>
                         </div>

                        <div class="relative md:col-span-2">
                            <input type="text"
                                   pattern="(?:[0-9]{4}|[Nn][/]?[Aa])"
                                   maxlength="4"
                                   inputmode="text"
                                   name="{{ $education_type }}[__INDEX__][year_graduated]"
                                   value=""
                                   placeholder=" "
                                   class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base"
                                   data-education-year-graduated
                                   {{ $education_type == 'college' ? '' : '' }}>
                            <label class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">
                                Year Graduated
                            </label>
                        </div>

                        <div class="relative md:col-span-2">
                            <input type="text"
                                   name="{{ $education_type }}[__INDEX__][earned]"
                                   value=""
                                   placeholder=" "
                                   class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base"
                                   data-education-earned>
                            <label class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-xs sm:text-sm">
                                Highest Level/Units Earned (if not graduated){!! $education_type == 'college' ? ' <span class="text-red-500 earned-required-asterisk">*</span>' : '' !!}
                            </label>
                        </div>

                        <div class="relative md:col-span-2">
                            <input type="text"
                                   name="{{ $education_type }}[__INDEX__][academic_honors]"
                                   value=""
                                   placeholder=" "
                                   class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">
                                Scholarship/Academic Honors Received
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <script>
            function normalizeProgramValue(value) {
                return String(value || '').trim().replace(/\s+/g, ' ');
            }

            function normalizeProgramSource(source) {
                const normalized = String(source || '').trim().toLowerCase();
                return normalized === 'grad' ? 'grad' : 'college';
            }

            function sourceLevels(source) {
                const normalized = normalizeProgramSource(source);
                return normalized === 'grad'
                    ? ['MASTERAL', 'DOCTORATE']
                    : ['COLLEGE'];
            }

            function ensureProgramStores() {
                if (!window.__pdsProgramOptionsBySource || typeof window.__pdsProgramOptionsBySource !== 'object') {
                    window.__pdsProgramOptionsBySource = {};
                }
                if (!window.__pdsProgramLoadedBySource || typeof window.__pdsProgramLoadedBySource !== 'object') {
                    window.__pdsProgramLoadedBySource = {};
                }
            }

            function setProgramOptions(source, programNames) {
                ensureProgramStores();
                const normalizedSource = normalizeProgramSource(source);

                const uniqueMap = new Map();
                (Array.isArray(programNames) ? programNames : []).forEach((name) => {
                    const normalized = normalizeProgramValue(name);
                    if (!normalized) {
                        return;
                    }
                    const key = normalized.toLowerCase();
                    if (!uniqueMap.has(key)) {
                        uniqueMap.set(key, normalized);
                    }
                });

                window.__pdsProgramOptionsBySource[normalizedSource] = Array.from(uniqueMap.values())
                    .sort((a, b) => a.localeCompare(b));
            }

            function currentProgramOptions(source) {
                ensureProgramStores();
                const normalizedSource = normalizeProgramSource(source);
                const values = window.__pdsProgramOptionsBySource[normalizedSource];
                return Array.isArray(values) ? values : [];
            }

            function rowMenu(row, source) {
                const normalizedSource = normalizeProgramSource(source);
                return row?.querySelector(`[data-program-role="menu"][data-program-source="${normalizedSource}"]`) || null;
            }

            function rowOptionsWrap(row, source) {
                const normalizedSource = normalizeProgramSource(source);
                return row?.querySelector(`[data-program-role="options"][data-program-source="${normalizedSource}"]`) || null;
            }

            function rowProgramInput(row, source) {
                const normalizedSource = normalizeProgramSource(source);
                return row?.querySelector(`input[data-program-input][data-program-source="${normalizedSource}"]`) || null;
            }

            function closeProgramMenuForRow(row, source) {
                const input = rowProgramInput(row, source);
                const menu = rowMenu(row, source);
                if (!menu) {
                    return;
                }
                menu.classList.add('hidden');
                if (input) {
                    input.setAttribute('aria-expanded', 'false');
                }
            }

            function closeAllProgramMenus() {
                document.querySelectorAll('.education-entry').forEach((row) => {
                    closeProgramMenuForRow(row, 'college');
                    closeProgramMenuForRow(row, 'grad');
                });
            }

            function filterProgramOptions(query, source) {
                const normalizedQuery = normalizeProgramValue(query).toLowerCase();
                const options = currentProgramOptions(source);
                if (!normalizedQuery) {
                    return options;
                }
                return options.filter((name) => name.toLowerCase().includes(normalizedQuery));
            }

            function renderProgramOptionsList(row, source) {
                const input = rowProgramInput(row, source);
                const optionsWrap = rowOptionsWrap(row, source);
                const normalizedSource = normalizeProgramSource(source);
                if (!input || !optionsWrap) {
                    return;
                }

                const filtered = filterProgramOptions(input.value, normalizedSource).slice(0, 200);
                if (filtered.length === 0) {
                    optionsWrap.innerHTML = '<div class="px-3 py-2 text-sm text-slate-500">No matches found. Keep typing to use your degree/course.</div>';
                    return;
                }

                const selectedValue = normalizeProgramValue(input.value).toLowerCase();
                optionsWrap.innerHTML = filtered.map((name) => {
                    const selectedClass = selectedValue === name.toLowerCase() ? ' bg-slate-100 font-medium' : '';
                    const safeAttr = name
                        .replace(/&/g, '&amp;')
                        .replace(/"/g, '&quot;');
                    const safeHtml = name
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;');
                    return `<button type="button" class="block w-full px-3 py-2 text-left text-sm text-slate-700 transition hover:bg-slate-100 focus:bg-slate-100${selectedClass}" data-program-role="option" data-program-source="${normalizedSource}" data-label="${safeAttr}">${safeHtml}</button>`;
                }).join('');
            }

            function openProgramMenuForRow(row, source) {
                const normalizedSource = normalizeProgramSource(source);
                const input = rowProgramInput(row, normalizedSource);
                const menu = rowMenu(row, normalizedSource);
                if (!input || !menu || input.disabled) {
                    closeProgramMenuForRow(row, normalizedSource);
                    return;
                }

                closeAllProgramMenus();
                renderProgramOptionsList(row, normalizedSource);
                menu.classList.remove('hidden');
                input.setAttribute('aria-expanded', 'true');
            }

            function bindProgramDropdownRow(row, source) {
                const normalizedSource = normalizeProgramSource(source);
                const input = rowProgramInput(row, normalizedSource);
                const optionsWrap = rowOptionsWrap(row, normalizedSource);
                const boundKey = normalizedSource === 'grad' ? 'gradProgramBound' : 'collegeProgramBound';

                if (!input || !optionsWrap || row.dataset[boundKey] === '1') {
                    return;
                }

                row.dataset[boundKey] = '1';
                input.setAttribute('autocomplete', 'off');
                input.setAttribute('aria-expanded', 'false');

                input.addEventListener('focus', () => openProgramMenuForRow(row, normalizedSource));
                input.addEventListener('click', () => openProgramMenuForRow(row, normalizedSource));
                input.addEventListener('input', () => {
                    openProgramMenuForRow(row, normalizedSource);
                });

                input.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        closeProgramMenuForRow(row, normalizedSource);
                        return;
                    }

                    if (event.key === 'Enter') {
                        const menu = rowMenu(row, normalizedSource);
                        if (menu && !menu.classList.contains('hidden')) {
                            const firstOption = optionsWrap.querySelector(`button[data-program-role="option"][data-program-source="${normalizedSource}"]`);
                            if (firstOption) {
                                event.preventDefault();
                                firstOption.click();
                            }
                        }
                    }
                });

                optionsWrap.addEventListener('click', (event) => {
                    const target = event.target instanceof HTMLElement
                        ? event.target.closest(`button[data-program-role="option"][data-program-source="${normalizedSource}"]`)
                        : null;
                    if (!target) {
                        return;
                    }

                    const label = normalizeProgramValue(target.getAttribute('data-label') || '');
                    if (!label) {
                        return;
                    }

                    input.value = label;
                    closeProgramMenuForRow(row, normalizedSource);
                });

                input.addEventListener('blur', () => {
                    window.setTimeout(() => {
                        if (!row.contains(document.activeElement)) {
                            closeProgramMenuForRow(row, normalizedSource);
                        }
                    }, 120);
                });
            }

            function bindProgramDropdownRows(scopeEl, source) {
                const root = scopeEl || document;
                const normalizedSource = normalizeProgramSource(source);
                const rows = Array.from(root.querySelectorAll('.education-entry'));
                rows.forEach((row) => bindProgramDropdownRow(row, normalizedSource));
            }

            function mapProgramRowsFromPayload(payload) {
                const rows = Array.isArray(payload?.data) ? payload.data : [];
                return rows
                    .map((item) => ({
                        level: String(item?.level || 'COLLEGE').trim().toUpperCase(),
                        name: normalizeProgramValue(item?.name || ''),
                    }))
                    .filter((item) => item.name !== '');
            }

            function deriveOptionsFromMasterRows(source) {
                const normalizedSource = normalizeProgramSource(source);
                const levels = sourceLevels(normalizedSource);
                const masterRows = Array.isArray(window.__pdsProgramsMasterRows) ? window.__pdsProgramsMasterRows : [];

                return masterRows
                    .filter((row) => levels.includes(String(row.level || '').toUpperCase()))
                    .map((row) => row.name)
                    .filter((name) => name !== '');
            }

            async function loadProgramOptions(source) {
                const normalizedSource = normalizeProgramSource(source);
                ensureProgramStores();

                if (window.__pdsProgramLoadedBySource[normalizedSource] === true) {
                    return;
                }

                if (window.__pdsProgramsMasterLoaded === true) {
                    setProgramOptions(normalizedSource, deriveOptionsFromMasterRows(normalizedSource));
                    window.__pdsProgramLoadedBySource[normalizedSource] = true;
                    return;
                }

                if (window.__pdsProgramsMasterLoadingPromise) {
                    await window.__pdsProgramsMasterLoadingPromise;
                    setProgramOptions(normalizedSource, deriveOptionsFromMasterRows(normalizedSource));
                    window.__pdsProgramLoadedBySource[normalizedSource] = true;
                    return;
                }

                const requestUrl = @json(route('pds.programs.list'));
                window.__pdsProgramsMasterLoadingPromise = fetch(requestUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                })
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error('Failed to load program options.');
                        }
                        return response.json();
                    })
                    .then((payload) => {
                        window.__pdsProgramsMasterRows = mapProgramRowsFromPayload(payload);
                        window.__pdsProgramsMasterLoaded = true;
                    })
                    .catch(() => {
                        window.__pdsProgramsMasterRows = [];
                        window.__pdsProgramsMasterLoaded = true;
                    })
                    .finally(() => {
                        window.__pdsProgramsMasterLoadingPromise = null;
                    });

                await window.__pdsProgramsMasterLoadingPromise;
                setProgramOptions(normalizedSource, deriveOptionsFromMasterRows(normalizedSource));
                window.__pdsProgramLoadedBySource[normalizedSource] = true;
            }

            function syncCollegeEarnedRequired(type) {
                if (type !== 'college') return;

                const container = document.getElementById(`${type}-container`);
                if (!container) return;

                const rows = container.querySelectorAll('.education-entry');
                rows.forEach((row) => {
                    const yearInput = row.querySelector('input[data-education-year-graduated]');
                    const earnedInput = row.querySelector('input[data-education-earned]');
                    const asterisk = row.querySelector('.earned-required-asterisk');
                    if (!yearInput || !earnedInput) return;

                    const normalizedYear = String(yearInput.value || '').trim().toLowerCase().replace(/\s+/g, '');
                    const hasYearGraduated = normalizedYear !== '' && normalizedYear !== 'n/a' && normalizedYear !== 'na' && normalizedYear !== 'n\\a';
                    earnedInput.required = !hasYearGraduated;
                    if (asterisk) {
                        asterisk.classList.toggle('hidden', hasYearGraduated);
                    }

                    if (hasYearGraduated) {
                        earnedInput.setCustomValidity('');
                    }
                });
            }

            function bindCollegeEarnedRequired(type) {
                if (type !== 'college') return;

                const container = document.getElementById(`${type}-container`);
                if (!container || container.dataset.earnedRuleBound === '1') return;

                container.dataset.earnedRuleBound = '1';

                const handler = (event) => {
                    const target = event.target;
                    if (!target) return;
                    if (
                        target.matches('input[data-education-year-graduated]') ||
                        target.matches('input[data-education-earned]')
                    ) {
                        syncCollegeEarnedRequired(type);
                    }
                };

                container.addEventListener('input', handler);
                container.addEventListener('change', handler);
                syncCollegeEarnedRequired(type);
            }

            function initEducationFlatpickr(scopeEl) {
                try {
                    if (!scopeEl) return;
                    const targets = scopeEl.querySelectorAll('input.edu-date');
                    targets.forEach(el => {
                        el.setAttribute('autocomplete', 'off');
                    });
                } catch (e) {}
            }

            function setCollegeNotAttendedValues(row, type = 'college') {
                if (!row) return;

                const textFieldSelectors = [
                    'input[name*="[school]"]',
                    'input[name*="[basic]"]',
                    'input[name*="[year_graduated]"]',
                    'input[name*="[earned]"]',
                    'input[name*="[academic_honors]"]',
                ];

                textFieldSelectors.forEach((selector) => {
                    const field = row.querySelector(selector);
                    if (field) {
                        field.value = 'N/A';
                    }
                });

                row.querySelectorAll('input[type="date"][name*="[' + type + ']"], .edu-date[name*="[' + type + ']"]').forEach((field) => {
                    field.value = '';
                });
            }

            function clearCollegeRowValues(row) {
                if (!row) return;
                row.querySelectorAll('input, select, textarea').forEach((field) => {
                    if (field.type === 'hidden') {
                        return;
                    }
                    if (field.type === 'checkbox' || field.type === 'radio') {
                        field.checked = false;
                        return;
                    }
                    field.value = '';
                });
            }

            function addEducationRow(type) {
                const container = document.getElementById(`${type}-container`);
                const template = document.getElementById(`${type}-template`).innerHTML;
                const currentCount = container.querySelectorAll('.education-entry').length;

                let newRowHtml = template
                    .replace(/__INDEX__/g, currentCount)
                    .replace(/__DISPLAY_INDEX__/g, currentCount + 1);

                container.insertAdjacentHTML('beforeend', newRowHtml);
                initEducationFlatpickr(container);
                if (typeof window.initPdsEducationDateRanges === 'function') {
                    window.initPdsEducationDateRanges(container);
                }
                bindCollegeEarnedRequired(type);
                syncCollegeEarnedRequired(type);
                if (type === 'college' || type === 'grad') {
                    const programSource = type === 'grad' ? 'grad' : 'college';
                    bindProgramDropdownRows(container, programSource);
                }
                if (type === 'college') {
                    applyCollegeNotAttendedState(false);
                }
            }

            function removeEducationRow(button, type) {
                const entry = button.closest('.education-entry');
                entry.remove();
                refreshEducationIndices(type);
            }

            function refreshEducationIndices(type) {
                const container = document.getElementById(`${type}-container`);
                const entries = container.querySelectorAll('.education-entry');

                entries.forEach((entry, index) => {
                    entry.dataset.index = index;
                    const h4 = entry.querySelector('.entry-number');
                    if (h4) {
                        h4.textContent = `#${index + 1}`;
                    }

                    const inputs = entry.querySelectorAll('input');
                    inputs.forEach(input => {
                        let nameAttr = input.getAttribute('name');
                        if (nameAttr) {
                            nameAttr = nameAttr.replace(/\$\d+\$/, `[${index}]`);
                            input.setAttribute('name', nameAttr);
                        }
                    });
                });
                initEducationFlatpickr(container);
                if (typeof window.initPdsEducationDateRanges === 'function') {
                    window.initPdsEducationDateRanges(container);
                }
                bindCollegeEarnedRequired(type);
                syncCollegeEarnedRequired(type);
                if (type === 'college' || type === 'grad') {
                    const programSource = type === 'grad' ? 'grad' : 'college';
                    bindProgramDropdownRows(container, programSource);
                }
                if (type === 'college') {
                    applyCollegeNotAttendedState(false);
                }
            }
            document.addEventListener('DOMContentLoaded', () => {
                const container = document.getElementById('{{ $education_type }}-container');
                const educationType = @json($education_type);
                const programSource = (educationType === 'college' || educationType === 'grad')
                    ? (educationType === 'grad' ? 'grad' : 'college')
                    : '';
                initEducationFlatpickr(container);
                if (typeof window.initPdsEducationDateRanges === 'function') {
                    window.initPdsEducationDateRanges(container);
                }
                bindCollegeEarnedRequired('{{ $education_type }}');
                syncCollegeEarnedRequired('{{ $education_type }}');
                if (programSource) {
                    bindProgramDropdownRows(container, programSource);
                    loadProgramOptions(programSource);
                    if (!window.__pdsProgramOutsideClickBound) {
                        window.__pdsProgramOutsideClickBound = true;
                        document.addEventListener('click', (event) => {
                            if (!(event.target instanceof Node)) {
                                return;
                            }
                            if (!event.target.closest('[data-program-input]') && !event.target.closest('[data-program-role="menu"]')) {
                                closeAllProgramMenus();
                            }
                        });
                    }
                }
                const form = document.getElementById('myForm');
                if (form && '{{ $education_type }}' === 'college' && form.dataset.collegeEarnedSyncOnSubmit !== '1') {
                    form.dataset.collegeEarnedSyncOnSubmit = '1';
                    form.addEventListener('submit', () => {
                        syncCollegeEarnedRequired('college');
                    });
                }

            });
        </script>
    </div>
</section>
