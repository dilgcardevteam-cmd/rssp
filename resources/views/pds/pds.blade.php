@extends('layout.pds_layout')
@section('title', 'PDS - Personal Data Sheet')
@section('content')
    <style>
        .pds-responsive-font {
            --pds-font-body: clamp(0.88rem, 0.82rem + 0.22vw, 1rem);
            --pds-font-label: clamp(0.8rem, 0.75rem + 0.2vw, 0.94rem);
            --pds-font-heading: clamp(1.1rem, 0.98rem + 0.9vw, 1.65rem);
            --pds-font-subheading: clamp(0.98rem, 0.9rem + 0.45vw, 1.2rem);
            --pds-font-meta: clamp(0.76rem, 0.72rem + 0.2vw, 0.9rem);
        }

        .pds-responsive-font :is(input, select, textarea, button) {
            font-size: var(--pds-font-body) !important;
            line-height: 1.35;
        }

        .pds-responsive-font label.floating-label {
            font-size: var(--pds-font-label) !important;
        }

        .pds-responsive-font label:not(.floating-label) {
            font-size: var(--pds-font-body) !important;
        }

        .pds-responsive-font h2 {
            font-size: var(--pds-font-heading) !important;
            line-height: 1.2;
        }

        .pds-responsive-font h3 {
            font-size: var(--pds-font-subheading) !important;
            line-height: 1.25;
        }

        .pds-responsive-font p {
            font-size: var(--pds-font-meta) !important;
            line-height: 1.45;
        }
    </style>
    <!-- Main Content -->
    <main class="pds-responsive-font max-w-7xl mx-auto px-2 sm:px-4 lg:px-8 py-4 sm:py-8 mb-20 sm:mb-0" style="padding-top: 0px;">
        @php
    $c1RouteParams = ['go_to' => 'display_c2'];
    if (request()->query('simple')) {
        $c1RouteParams['simple'] = 1;
    }
@endphp
<form id="myForm" class="no-spinner space-y-4 sm:space-y-8" action="{{ route('submit_c1', $c1RouteParams) }}" method="POST" x-data="{ civilStatus: '{{ old('civil_status', session('form.c1.civil_status')) }}' }">
            @csrf
            <!-- Personal Information Section -->
            <section class="bg-white rounded-lg sm:rounded-2xl shadow-xl p-4 sm:p-8 animate-slide-in">

                <div class="flex flex-col sm:flex-row justify-end gap-2">
                    <!-- <a
                        href="{{ route('export.pds') }}"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg border-2 border-rose-700 bg-rose-700 px-4 py-3 text-sm sm:text-base font-montserrat font-semibold text-white shadow-sm transition-all duration-200 hover:border-rose-800 hover:bg-rose-800 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-rose-700/30"
                    >
                        <span class="material-icons text-lg sm:text-xl">picture_as_pdf</span>
                        Export to PDF
                    </a> -->
                    {{-- <a
                        id="exportAnnexH1Btn"
                        href="{{ route('pds.export_annex_h1_excel') }}"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg border-2 border-emerald-700 bg-emerald-700 px-4 py-3 text-sm sm:text-base font-montserrat font-semibold text-white shadow-sm transition-all duration-200 hover:border-emerald-800 hover:bg-emerald-800 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-emerald-700/30"
                    >
                        <span class="material-icons text-lg sm:text-xl">download</span>
                        Export to Excel (unstable)
                    </a> --}}
                <button
                        type="button"
                        id="importPdsExcelBtn" 
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg border-2 border-[#002C76] bg-[#002C76] px-4 py-3 text-sm sm:text-base font-montserrat font-semibold text-white shadow-sm transition-all duration-200 hover:border-[#001F5A] hover:bg-[#001F5A] hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[#002C76]/30 disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:border-[#002C76] disabled:hover:bg-[#002C76] disabled:hover:text-white"
                    >
                        <span class="material-icons text-lg sm:text-xl">upload_file</span>
                        Import from Excel
                    </button>
                </div>

                <div class="flex items-center mb-4 sm:mb-6">
                    <span class="material-icons text-blue-600 mr-2 sm:mr-3 text-2xl sm:text-3xl">badge</span>
                    <h2 class="text-lg sm:text-2xl font-bold text-gray-900">I. PERSONAL INFORMATION</h2>

                    
                </div>

                <p class="text-gray-600 mb-4 sm:mb-6 text-xs sm:text-sm">
                    Print legibly. Tick appropriate boxes and use separate sheet if necessary. Indicate N/A if not applicable. DO NOT ABBREVIATE.
                </p>

                
        

                <!-- CS ID Number -->
                <!-- <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6 mb-4 sm:mb-6">
                    <div class="relative w-full sm:w-[400px]">
                        <input
                            type="number"
                            id="cs_id_no"
                            name="cs_id_no"
                            disabled
                            value="{{ old('cs_id_no', session('form.c1.cs_id_no')) }}"
                            placeholder=" "
                            style="-moz-appearance: textfield; -webkit-appearance: textfield;"
                            class="floating-label-input peer w-full px-3 py-2 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm normal-case"
                        />
                        <label
                            for="cs_id_no"
                            class="floating-label absolute left-3 top-2.5 text-xs sm:text-sm text-gray-500 pointer-events-none"
                        >
                            CS ID No. (Do not fill up. For CSC Use Only)
                        </label>

                    </div>
                </div> -->

                <!-- Name Fields -->
                <div class="mobile-stack md:grid md:grid-cols-4 gap-4 sm:gap-6 mb-4 sm:mb-6">
                    <div class="relative rounded-lg">
                        <input type="text" id="surname" name="surname" value="{{ old('surname', session('form.c1.surname')) }}" placeholder=" " required class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="surname" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">1. Surname <span class="text-red-500">*</span></label>
                    </div>
                    <div class="relative">
                        <input type="text" id="first_name" name="first_name" value="{{ old('first_name', session('form.c1.first_name')) }}" placeholder=" " required class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="first_name" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">2. First Name <span class="text-red-500">*</span></label>
                    </div>
                    <div class="relative">
                        <input type="text" id="middle_name" name="middle_name" value="{{ old('middle_name', session('form.c1.middle_name')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="middle_name" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Middle Name</label>
                    </div>
                    <div class="relative">
                        <input type="text" id="name_extension" name="name_extension" value="{{ old('name_extension', session('form.c1.name_extension')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="name_extension" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">NAME EXTENSION (JR., SR.)</label>
                    </div>
                </div>

                <!-- Personal Details -->
                <div class="mobile-stack md:grid md:grid-cols-4 gap-4 rounded-lg p-4 sm:gap-6 mb-4 sm:mb-6">
                    <div class="relative">
                        <input type="text" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', session('form.c1.date_of_birth')) }}" required autocomplete="bday" inputmode="numeric" data-uppercase="off" data-dob-input class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm sm:text-base">
                        <label for="date_of_birth" class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">3. DATE OF BIRTH <span class="text-red-500">*</span></label>
                        <!-- <label for="date_of_birth" class="absolute -top-2 left-3 bg-white px-1 text-xs text-gray-600 ml-[50%]">(dd/mm/yyyy) </label> -->
                    </div>
                    <div class="relative md:col-span-2">
                        <input type="text" id="place_of_birth" name="place_of_birth" value="{{ old('place_of_birth', session('form.c1.place_of_birth')) }}" placeholder=" " required class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="place_of_birth" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">4. Place of Birth <span class="text-red-500">*</span></label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">5. SEX AT BIRTH <span class="text-red-500">*</span></label>
                        <div class="flex space-x-4 sm:space-x-6">
                            <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors text-sm sm:text-base">
                                <input type="radio" name="sex" value="male" {{ old('sex', session('form.c1.sex')) == 'male' ? 'checked' : '' }} class="mr-2 text-blue-600 focus:ring-blue-500" required>
                                <span>Male</span>
                            </label>
                            <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors text-sm sm:text-base">
                                <input type="radio" name="sex" value="female" {{ old('sex', session('form.c1.sex')) == 'female' ? 'checked' : '' }} class="mr-2 text-blue-600 focus:ring-blue-500">
                                <span>Female</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Civil Status and Physical Info Row -->
                <div class="mobile-stack md:grid md:grid-cols-4 gap-4 sm:gap-6 mb-4 sm:mb-6">
                    <div class="relative">
                        <select id="civil_status" name="civil_status" x-model="civilStatus" required class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all appearance-none bg-white text-sm sm:text-base">
                            <option value="" disabled {{ old('civil_status', session('form.c1.civil_status')) == '' ? 'selected' : '' }}>Select Civil Status</option>
                            <option value="single" {{ old('civil_status', session('form.c1.civil_status')) == 'single' ? 'selected' : '' }}>Single</option>
                            <option value="married"{{ old('civil_status', session('form.c1.civil_status')) == 'married' ? 'selected' : '' }}>Married</option>
                            <option value="widowed"{{ old('civil_status', session('form.c1.civil_status')) == 'widowed' ? 'selected' : '' }}>Widowed</option>
                            <option value="separated"{{ old('civil_status', session('form.c1.civil_status')) == 'separated' ? 'selected' : '' }}>Separated</option>
                            <option value="other"{{ old('civil_status', session('form.c1.civil_status')) == 'other' ? 'selected' : '' }}>Other/s</option>
                        </select>
                        <label class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">6. Civil Status <span class="text-red-500">*</span></label>
                    </div>
                    <!-- Physical Info -->
                    <div class="relative">
                        <input type="number" style="-moz-appearance: textfield; -webkit-appearance: textfield;" required step="0.01" id="height" name="height" value="{{ old('height', session('form.c1.height')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="height" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">7. HEIGHT (Cm) <span class="text-red-500">*</span></label>
                    </div>
                    <div class="relative">
                        <input type="number" style="-moz-appearance: textfield; -webkit-appearance: textfield;" required step="0.1" id="weight" name="weight" value="{{ old('weight', session('form.c1.weight')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="weight" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">8. Weight (kg) <span class="text-red-500">*</span></label>
                    </div>
                    <div class="relative">
                        @php
                            $blood = old('blood_type', session('form.c1.blood_type'));
                            $validBlood = ['A+','A-','B+','B-','AB+','AB-','O+','O-'];
                        @endphp
                        <select id="blood_type" name="blood_type" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all appearance-none bg-white text-sm sm:text-base" required>
                            <option value="" disabled {{ $blood == '' ? 'selected' : '' }}>Select Blood Type</option>
                            @foreach($validBlood as $bt)
                                <option value="{{ $bt }}" {{ $blood === $bt ? 'selected' : '' }}>{{ $bt }}</option>
                            @endforeach
                            @if($blood && !in_array($blood, $validBlood))
                                <option value="{{ $blood }}" selected>{{ $blood }}</option>
                            @endif
                        </select>
                        <label for="blood_type" class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">9. Blood Type<span class="text-red-500">*</span></label>
                    </div>
                </div>

                <!-- ID Numbers -->
                <div class="mobile-stack md:grid md:grid-cols-3 gap-4 sm:gap-6 mb-4 sm:mb-6">
                    <div class="relative">
                        <input type="number" style="-moz-appearance: textfield; -webkit-appearance: textfield;" id="gsis_id_no" name="gsis_id_no" value="{{ old('gsis_id_no', session('form.c1.gsis_id_no')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="gsis_id_no" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">10. UMID ID NO.</label>
                    </div>
                    <div class="relative">
                        <input type="number" style="-moz-appearance: textfield; -webkit-appearance: textfield;" id="pagibig_id_no" name="pagibig_id_no" value="{{ old('pagibig_id_no', session('form.c1.pagibig_id_no')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="pagibig_id_no" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">11. PAG-IBIG ID NO.</label>
                    </div>
                    <div class="relative">
                        <input type="number" style="-moz-appearance: textfield; -webkit-appearance: textfield;" id="philhealth_no" name="philhealth_no" value="{{ old('philhealth_no', session('form.c1.philhealth_no')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="philhealth_no" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">12. PHILHEALTH NO.</label>
                    </div>
                    <div class="relative">
                        <input type="number" style="-moz-appearance: textfield; -webkit-appearance: textfield;" id="sss_id_no" name="sss_id_no" value="{{ old('sss_id_no', session('form.c1.sss_id_no')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="sss_id_no" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">13. PhilSys Number (PSN):</label>
                    </div>
                    <div class="relative">
                        <input type="number" style="-moz-appearance: textfield; -webkit-appearance: textfield;" id="tin_no" name="tin_no" value="{{ old('tin_no', session('form.c1.tin_no')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="tin_no" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">14. TIN NO.</label>
                    </div>
                    <div class="relative">
                        <input type="number" style="-moz-appearance: textfield; -webkit-appearance: textfield;" id="agency_employee_no" name="agency_employee_no" value="{{ old('agency_employee_no', session('form.c1.agency_employee_no')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="agency_employee_no" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">15. AGENCY EMPLOYEE NO.</label>
                    </div>
                </div>

                <!-- Additional Personal Info -->
                <div class="mobile-stack md:grid md:grid-cols-2 gap-4 sm:gap-6 mb-4 sm:mb-6">
                    <div x-data="{ citizenship: '{{ old('citizenship', session('form.c1.citizenship')) }}', dualType: '{{ old('dual_type', session('form.c1.dual_type')) }}' }" class="space-y-4">
                        <label class="block text-gray-700 font-medium mb-2 text-sm sm:text-base">16. CITIZENSHIP <span class="text-red-500">*</span></label>

                        <!-- Primary citizenship options -->
                        <div class="flex flex-col sm:flex-row gap-2">
                            <label class="inline-flex items-center text-sm sm:text-base">
                                <input type="radio" name="citizenship" value="Filipino" x-model="citizenship"
                                       class="text-blue-600 border-gray-300 focus:ring-blue-500" required
                                       {{ old('citizenship', session('form.c1.citizenship')) == 'Filipino' ? 'checked' : '' }}>
                                <span class="ml-2 text-gray-700">Filipino</span>
                            </label>

                            <label class="inline-flex items-center text-sm sm:text-base">
                                <input type="radio" name="citizenship" value="Dual Citizenship" x-model="citizenship"
                                       class="text-blue-600 border-gray-300 focus:ring-blue-500"
                                       {{ old('citizenship', session('form.c1.citizenship')) == 'Dual Citizenship' ? 'checked' : '' }}>
                                <span class="ml-2 text-gray-700">Dual Citizenship</span>
                            </label>
                        </div>

                        <!-- Show only when Dual Citizenship is selected -->
                        <div x-show="citizenship === 'Dual Citizenship'" class="space-y-4 mt-4">
                            <!-- Sub-options -->
                            <label class="block text-gray-700 font-medium mb-2 text-sm sm:text-base">If holder of dual citizenship, please indicate the details.</label>
                            <div class="flex flex-col sm:flex-row gap-2">
                                <label class="inline-flex items-center text-sm sm:text-base">
                                    <input type="radio" name="dual_type" value="By Birth" x-model="dualType"
                                           class="text-blue-600 border-gray-300 focus:ring-blue-500"
                                           {{ old('dual_type', session('form.c1.dual_type')) == 'By Birth' ? 'checked' : '' }}>
                                    <span class="ml-2 text-gray-700">By Birth</span>
                                </label>
                                <label class="inline-flex items-center text-sm sm:text-base">
                                    <input type="radio" name="dual_type" value="By Naturalization" x-model="dualType"
                                           class="text-blue-600 border-gray-300 focus:ring-blue-500"
                                           {{ old('dual_type', session('form.c1.dual_type')) == 'By Naturalization' ? 'checked' : '' }}>
                                    <span class="ml-2 text-gray-700">By Naturalization</span>
                                </label>
                            </div>

                            <!-- Input for specifying country -->
                            <div>
                                <label for="dual_country" class="block text-gray-500 text-sm mb-1">Specify Country</label>
                                <input type="text" id="dual_country" name="dual_country"
                                       value="{{ old('dual_country', session('form.c1.dual_country')) }}"
                                       class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition text-sm sm:text-base"
                                       placeholder="Enter country of second citizenship">
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Contact Information Section -->
            <section class="bg-white rounded-lg sm:rounded-2xl shadow-xl p-4 sm:p-8 animate-slide-in">
                <div class="flex items-center mb-4 sm:mb-6">
                    <span class="material-icons text-blue-600 mr-2 sm:mr-3 text-2xl sm:text-3xl">home</span>
                    <h2 class="text-lg sm:text-2xl font-bold text-gray-900">17. RESIDENTIAL ADDRESS</h2>
                </div>
                <div class="mobile-stack md:grid md:grid-cols-3 gap-4 sm:gap-6">
                    <div class="relative">
                        <input type="text" id="res_house_no" name="res_house_no" value="{{ old('res_house_no', session('form.c1.res_house_no')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="res_house_no" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">House/Block/Lot No.</label>
                    </div>
                    <div class="relative">
                        <input type="text" id="res_street" name="res_street" value="{{ old('res_street', session('form.c1.res_street')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="res_street" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Street</label>
                    </div>
                    <div class="relative">
                        <input type="text" id="res_sub_vil" name="res_sub_vil" value="{{ old('res_sub_vil', session('form.c1.res_sub_vil')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="res_sub_vil" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Subdivision/Village</label>
                    </div>
                    <div class="relative">
                        {{-- Hidden fallback so the value is submitted even if the async PSGC select hasn't loaded yet --}}
                        <input type="hidden" id="res_province_hidden" value="{{ old('res_province', session('form.c1.res_province')) }}">
                        <select required id="res_province" name="res_province" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all appearance-none bg-white text-sm sm:text-base"></select>
                        <label for="res_province" class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">Province <span class="text-red-500">*</span></label>
                    </div>
                    <div class="relative">
                        <input type="hidden" id="res_city_hidden" value="{{ old('res_city', session('form.c1.res_city')) }}">
                        <select required id="res_city" name="res_city" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all appearance-none bg-white text-sm sm:text-base"></select>
                        <label for="res_city" class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">City/Municipality <span class="text-red-500">*</span></label>
                    </div>
                    <div class="relative">
                        <input type="hidden" id="res_brgy_hidden" value="{{ old('res_brgy', session('form.c1.res_brgy')) }}">
                        <select required id="res_brgy" name="res_brgy" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all appearance-none bg-white text-sm sm:text-base"></select>
                        <label for="res_brgy" class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">Barangay <span class="text-red-500">*</span></label>
                    </div>
                    <div class="relative">
                        <input pattern="[0-9]{4}" type="text" maxlength="4" inputmode="nzumeric" required id="res_zipcode" name="res_zipcode" value="{{ old('res_zipcode', session('form.c1.res_zipcode')) }}" placeholder="" class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="res_zipcode" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">ZIP Code <span class="text-red-500">*</span></label>
                    </div>
                </div>
            </section>

            <!-- Permanent Address Section -->
            <section class="bg-white rounded-lg sm:rounded-2xl shadow-xl p-4 sm:p-8 animate-slide-in mt-2">
                <div class="flex items-center mb-4 sm:mb-6">
                    <span class="material-icons text-blue-600 mr-2 sm:mr-3 text-2xl sm:text-3xl">home</span>
                    <h2 class="text-lg sm:text-2xl font-bold text-gray-900">18. PERMANENT ADDRESS</h2>
                </div>
                <div class="mb-4 w-full flex justify-end">
                    <button type="button" id="copy_res_to_per" 
                    class="border-2 border-[#002C76] bg-[#002C76] text-white rounded-lg px-4 py-2 text-sm sm:text-base font-montserrat 
                    hover:bg-white hover:text-[#002C76] transition">
                        Copy from Residential Address
                    </button>
                </div>
                <div class="mobile-stack md:grid md:grid-cols-3 gap-4 sm:gap-6">
                    <div class="relative">
                        <input type="text" id="per_house_no" name="per_house_no" value="{{ old('per_house_no', session('form.c1.per_house_no')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="per_house_no" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">House/Block/Lot No.</label>
                    </div>
                    <div class="relative">
                        <input type="text" id="per_street" name="per_street" value="{{ old('per_street', session('form.c1.per_street')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="per_street" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Street</label>
                    </div>
                    <div class="relative">
                        <input type="text" id="per_sub_vil" name="per_sub_vil" value="{{ old('per_sub_vil', session('form.c1.per_sub_vil')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="per_sub_vil" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Subdivision/Village</label>
                    </div>
                    <div class="relative">
                        <input type="hidden" id="per_province_hidden" value="{{ old('per_province', session('form.c1.per_province')) }}">
                        <select required id="per_province" name="per_province" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all appearance-none bg-white text-sm sm:text-base"></select>
                        <label for="per_province" class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">Province<span class="text-red-500">*</span></label>
                    </div>
                    <div class="relative">
                        <input type="hidden" id="per_city_hidden" value="{{ old('per_city', session('form.c1.per_city')) }}">
                        <select required id="per_city" name="per_city" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all appearance-none bg-white text-sm sm:text-base"></select>
                        <label for="per_city" class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">City/Municipality<span class="text-red-500">*</span></label>
                    </div>
                    <div class="relative">
                        <input type="hidden" id="per_brgy_hidden" value="{{ old('per_brgy', session('form.c1.per_brgy')) }}">
                        <select required id="per_brgy" name="per_brgy" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all appearance-none bg-white text-sm sm:text-base"></select>
                        <label for="per_brgy" class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">Barangay<span class="text-red-500">*</span></label>
                    </div>
                    <div class="relative">
                        <input pattern="[0-9]{4}" maxlength="4" type="text" inputmode="numeric" required id="per_zipcode" name="per_zipcode" value="{{ old('per_zipcode', session('form.c1.per_zipcode')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="per_zipcode" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">ZIP Code<span class="text-red-500">*</span></label>
                    </div>
                </div>
            </section>

            <section class="bg-white rounded-lg sm:rounded-2xl shadow-xl p-4 sm:p-8 animate-slide-in mt-2">
                <div class="flex items-center mb-4 sm:mb-6">
                    <span class="material-icons text-blue-600 mr-2 sm:mr-3 text-2xl sm:text-3xl">phone</span>
                    <h2 class="text-lg sm:text-2xl font-bold text-gray-900">CONTACT INFORMATION</h2>
                </div>
                <div class="mobile-stack md:grid md:grid-cols-3 gap-4 sm:gap-6">
                    <div class="relative">
                        <input type="tel" style="-moz-appearance: textfield; -webkit-appearance: textfield;" pattern="^(?:0\d{9,10}|\(02\)\s?\d{4}\s?\d{4})$" maxlength="16" id="telephone_no" name="telephone_no" value="{{ old('telephone_no', session('form.c1.telephone_no')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="telephone_no" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">19. TELEPHONE NO.</label>
                        <p class="mt-1 text-xs text-gray-500">Format: (XX) XXXX XXXX</p>
                    </div>
                    <div class="relative">
                        <input required type="tel" style="-moz-appearance: textfield; -webkit-appearance: textfield;" maxlength="13" id="mobile_no" name="mobile_no" value="{{ old('mobile_no', session('form.c1.mobile_no')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base"
                            pattern="^09\d{2}\s\d{3}\s\d{4}$"
                            inputmode="numeric"

                        >
                        <label for="mobile_no" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">20. MOBILE NO. <span class="text-red-500">*</span></label>
                        <p class="mt-1 text-xs text-gray-500">Format: 09XX XXX XXXX</p>
                    </div>
                    <div class="relative">
                        <input type="email" id="email_address" name="email_address" value="{{ old('email_address', session('form.c1.email_address')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="email_address" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">21. E-MAIL ADDRESS</label>
                    </div>
                </div>
            </section>

            <!-- Family Background Section -->
            <section id="family-background" class="bg-white rounded-lg sm:rounded-2xl shadow-xl p-4 sm:p-8 animate-slide-in">
                <div class="flex items-center mb-4 sm:mb-6">
                    <span class="material-icons text-blue-600 mr-2 sm:mr-3 text-2xl sm:text-3xl">family_restroom</span>
                    <h2 class="text-lg sm:text-2xl font-bold text-gray-900">II. FAMILY BACKGROUND</h2>
                </div>

                <p class="text-gray-600 mb-4 sm:mb-6 text-xs sm:text-sm">
                    Write full name and list all requested details.
                </p>

                <!-- Spouse Information -->
                <!-- Spouse Information -->
                <div class="mb-6 sm:mb-8"
                    x-effect="
                        const isNA = civilStatus === 'single';
                        $el.querySelectorAll('input[id^=\'spouse_\']').forEach(f => {
                            if (isNA) {
                                f.value = 'N/A';
                                f.readOnly = true;
                                f.classList.add('bg-gray-100', 'text-gray-400', 'cursor-not-allowed');
                                f.classList.remove('focus:border-blue-500', 'focus:ring-2', 'focus:ring-blue-200');
                            } else {
                                if (f.value === 'N/A') f.value = '';
                                f.readOnly = false;
                                f.classList.remove('bg-gray-100', 'text-gray-400', 'cursor-not-allowed');
                                f.classList.add('focus:border-blue-500', 'focus:ring-2', 'focus:ring-blue-200');
                            }
                        });
                    "
                >
                    <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-4 flex items-center gap-3">
                        <span class="material-icons text-sm mr-2 text-blue-500">favorite</span>
                        22. Spouse Information
                        <span x-show="civilStatus === 'single'"
                              x-cloak
                              class="inline-flex items-center gap-1 rounded-full bg-amber-100 border border-amber-300 px-3 py-0.5 text-xs font-semibold text-amber-700">
                            <span class="material-icons" style="font-size:13px;">info</span>
                            N/A &mdash; Not applicable for Single
                        </span>
                    </h3>

                    <div class="mobile-stack md:grid md:grid-cols-4 gap-4 sm:gap-6 mb-4">
                        <div class="relative">
                            <input type="text" id="spouse_surname" name="spouse_surname" value="{{ old('spouse_surname', session('form.c1.spouse_surname')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="spouse_surname" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Spouse's Surname</label>
                        </div>
                        <div class="relative">
                            <input type="text" id="spouse_first_name" name="spouse_first_name" value="{{ old('spouse_first_name', session('form.c1.spouse_first_name')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="spouse_first_name" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Spouse's First Name</label>
                        </div>
                        <div class="relative">
                            <input type="text" id="spouse_middle_name" name="spouse_middle_name" value="{{ old('spouse_middle_name', session('form.c1.spouse_middle_name')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="spouse_middle_name" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Spouse's Middle Name</label>
                        </div>
                        <div class="relative">
                            <input type="text" id="spouse_name_extension" name="spouse_name_extension" value="{{ old('spouse_name_extension', session('form.c1.spouse_name_extension')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="spouse_name_extension" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Spouse's Name Ext.</label>
                        </div>
                    </div>
                    <div class="mobile-stack md:grid md:grid-cols-4 gap-4 sm:gap-6">
                        <div class="relative">
                            <input type="text" id="spouse_occupation" name="spouse_occupation" value="{{ old('spouse_occupation', session('form.c1.spouse_occupation')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="spouse_occupation" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Occupation</label>
                        </div>
                        <div class="relative">
                            <input type="text" id="spouse_employer" name="spouse_employer" value="{{ old('spouse_employer', session('form.c1.spouse_employer')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="spouse_employer" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Employer/Business Name</label>
                        </div>
                        <div class="relative">
                            <input type="text" id="spouse_business_address" name="spouse_business_address" value="{{ old('spouse_business_address', session('form.c1.spouse_business_address')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="spouse_business_address" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Business Address</label>
                        </div>
                        <div class="relative">
                            <input type="tel" style="-moz-appearance: textfield; -webkit-appearance: textfield;" pattern="^0\d{9,10}$" maxlength="11" id="spouse_telephone" name="spouse_telephone" value="{{ old('spouse_telephone', session('form.c1.spouse_telephone')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="spouse_telephone" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Telephone No.</label>
                        </div>
                    </div>
                </div>

                <!-- Children Information Placeholder -->
                <div class="mb-6 sm:mb-8">
                    @livewire('pds-children-form', [
                        'children' => (array) old('children', session('form.c1.children', []))
                    ])
                </div>

                <!-- Parents Information -->
                <div class="mb-6 sm:mb-8">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-4 flex items-center">
                        <span class="material-icons text-sm mr-2 text-blue-500">escalator_warning</span>
                        PARENTS INFORMATION
                    </h3>
                    <div class="mobile-stack md:grid md:grid-cols-4 gap-4 sm:gap-6 mb-4">
                        <div class="relative">
                            <input type="text" id="father_surname" name="father_surname" value="{{ old('father_surname', session('form.c1.father_surname')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="father_surname" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">24. Father's Surname</label>
                        </div>
                        <div class="relative">
                            <input type="text" id="father_first_name" name="father_first_name" value="{{ old('father_first_name', session('form.c1.father_first_name')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="father_first_name" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Father's First Name</label>
                        </div>
                        <div class="relative">
                            <input type="text" id="father_middle_name" name="father_middle_name" value="{{ old('father_middle_name', session('form.c1.father_middle_name')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="father_middle_name" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Father's Middle Name</label>
                        </div>
                        <div class="relative">
                            <input type="text" id="father_name_extension" name="father_name_extension" value="{{ old('father_name_extension', session('form.c1.father_name_extension')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="father_name_extension" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Father's Name Ext.</label>
                        </div>
                    </div>
                    <div class="mobile-stack md:grid md:grid-cols-3 gap-4 sm:gap-6">
                        <div class="relative">
                            <input required type="text" id="mother_maiden_surname" name="mother_maiden_surname" value="{{ old('mother_maiden_surname', session('form.c1.mother_maiden_surname')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="mother_maiden_surname" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">25. Mother's Maiden Surname <span class="text-red-500">*</span></label>
                        </div>
                        <div class="relative">
                            <input required type="text" id="mother_maiden_first_name" name="mother_maiden_first_name" value="{{ old('mother_maiden_first_name', session('form.c1.mother_maiden_first_name')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="mother_maiden_first_name" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Mother's First Name<span class="text-red-500">*</span></label>
                        </div>
                        <div class="relative">
                            <input type="text" id="mother_maiden_middle_name" name="mother_maiden_middle_name" value="{{ old('mother_maiden_middle_name', session('form.c1.mother_maiden_middle_name')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="mother_maiden_middle_name" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Mother's Middle Name</label>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Educational Background Section -->
            <!-- Educational Background Section -->
<section id="educational-background" class="bg-white rounded-lg sm:rounded-2xl shadow-xl p-4 sm:p-8 animate-slide-in">
    <div class="flex items-center mb-4 sm:mb-6">
        <span class="material-icons text-blue-600 mr-2 sm:mr-3 text-2xl sm:text-3xl">school</span>
        <h2 class="text-lg sm:text-2xl font-bold text-gray-900">III. EDUCATIONAL BACKGROUND</h2>
    </div>

    <!-- Elementary -->
    @php
        $educationBg = auth()->user()?->educationalBackground;

        $elemBasicPrefill = old('elem_basic');
        if ($elemBasicPrefill === null || trim((string) $elemBasicPrefill) === '') {
            $elemBasicPrefill = session('form.c1.elem_basic');
        }
        if (($elemBasicPrefill === null || trim((string) $elemBasicPrefill) === '') && $educationBg) {
            $elemBasicPrefill = $educationBg->elem_basic;
        }

        $jhsBasicPrefill = old('jhs_basic');
        if ($jhsBasicPrefill === null || trim((string) $jhsBasicPrefill) === '') {
            $jhsBasicPrefill = session('form.c1.jhs_basic');
        }
        if (($jhsBasicPrefill === null || trim((string) $jhsBasicPrefill) === '') && $educationBg) {
            $jhsBasicPrefill = $educationBg->jhs_basic;
        }
    @endphp
    @php
        $normalizeEducationDateForInput = static function ($value) {
            $value = is_string($value) ? trim($value) : '';

            if ($value === '') {
                return '';
            }

            // Return just the year if it's a 4-digit number
            if (preg_match('/^\d{4}$/', $value)) {
                return $value;
            }

            // Extract year from full date formats
            try {
                if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $value)) {
                    return \Carbon\Carbon::createFromFormat('d-m-Y', $value)->format('Y');
                }

                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                    return \Carbon\Carbon::createFromFormat('Y-m-d', $value)->format('Y');
                }
            } catch (\Throwable $e) {
                return '';
            }

            return '';
        };
    @endphp
    <div class="mb-8" data-education-section="elementary">
        <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-4">ELEMENTARY</h3>
        <div class="mobile-stack md:grid md:grid-cols-4 gap-4 sm:gap-6" data-education-date-range>
            <div class="relative md:col-span-2">
                <input required type="text" id="elem_school" name="elem_school" value="{{ old('elem_school', session('form.c1.elem_school')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                <label for="elem_school" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">School Name<span class="text-red-500">*</span></label>
            </div>
            <div class="relative md:col-span-2">
                <label for="elem_basic" class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">Basic Education/Degree/Course</label>
                <input type="text" id="elem_basic" name="elem_basic" value="PRIMARY" readonly aria-readonly="true" class="text-gray-700 bg-gray-100 w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm sm:text-base">
            </div>
            <div class="relative">
                <input required type="text" id="elem_from" name="elem_from" maxlength="4" pattern="[0-9]{4}" inputmode="numeric" placeholder="YYYY" value="{{ $normalizeEducationDateForInput(old('elem_from', session('form.c1.elem_from'))) }}" data-education-date-role="from" autocomplete="off" class="edu-date w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm sm:text-base">
                <label class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">From<span class="text-red-500">*</span></label>
            </div>
            <div class="relative">
                <input required type="text" id="elem_to" name="elem_to" maxlength="4" pattern="[0-9]{4}" inputmode="numeric" placeholder="YYYY" value="{{ $normalizeEducationDateForInput(old('elem_to', session('form.c1.elem_to'))) }}" data-education-date-role="to" autocomplete="off" class="edu-date w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm sm:text-base">
                <label class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">To<span class="text-red-500">*</span></label>
                <p class="error-message hidden" data-education-date-error aria-live="polite"></p>
            </div>
            <div class="relative md:col-span-2">
                <input pattern="(?:[0-9]{4}|[Nn][\/]?[Aa])" maxlength="4" type="text" inputmode="text" id="elem_year_graduated" name="elem_year_graduated" value="{{ old('elem_year_graduated', session('form.c1.elem_year_graduated')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                <label for="elem_year_graduated" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Year Graduated</label>
            </div>
            <div class="relative md:col-span-2 hidden" data-earned-wrapper-for="elem_earned">
                <input type="text" id="elem_earned" name="elem_earned" value="{{ old('elem_earned', session('form.c1.elem_earned')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                <label for="elem_earned" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-xs sm:text-sm">Highest Level/Units Earned (if not graduated) <span class="text-red-500">*</span></label>
            </div>
            <div class="relative md:col-span-2">
                <input type="text" id="elem_academic_honors" name="elem_academic_honors" value="{{ old('elem_academic_honors', session('form.c1.elem_academic_honors')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                <label for="elem_academic_honors" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Scholarship/Academic Honors Received</label>
            </div>
        </div>
    </div>

    <!-- Secondary -->
    <div class="my-6" data-education-section="secondary">
        <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-4">SECONDARY</h3>
        <div class="mobile-stack md:grid md:grid-cols-4 gap-4 sm:gap-6" data-education-date-range>
            <div class="relative md:col-span-2">
                <input type="text" id="jhs_school" name="jhs_school" value="{{ old('jhs_school', session('form.c1.jhs_school')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                <label for="jhs_school" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">School Name</label>
            </div>
            <div class="relative md:col-span-2">
                <label for="jhs_basic" class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">Basic Education/Degree/Course</label>
                @php
                    $jhsBasicValue = trim((string) $jhsBasicPrefill);
                    $validJhsValues = ['JUNIOR HIGH SCHOOL', 'SENIOR HIGH SCHOOL', 'HIGH SCHOOL'];
                    $isJhsFirst = !in_array(strtoupper($jhsBasicValue), $validJhsValues);
                @endphp
                <select id="jhs_basic" name="jhs_basic" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all bg-white text-sm sm:text-base">
                    <option value="JUNIOR HIGH SCHOOL" {{ strtoupper($jhsBasicValue) === 'JUNIOR HIGH SCHOOL' ? 'selected' : ($isJhsFirst ? 'selected' : '') }}>Junior High School</option>
                    <option value="SENIOR HIGH SCHOOL" {{ strtoupper($jhsBasicValue) === 'SENIOR HIGH SCHOOL' ? 'selected' : '' }}>Senior High School</option>
                    <option value="HIGH SCHOOL" {{ strtoupper($jhsBasicValue) === 'HIGH SCHOOL' ? 'selected' : '' }}>High School</option>
                </select>
            </div>
            <div class="relative">
                <input type="text" id="jhs_from" name="jhs_from" maxlength="4" pattern="[0-9]{4}" inputmode="numeric" placeholder="YYYY" value="{{ $normalizeEducationDateForInput(old('jhs_from', session('form.c1.jhs_from'))) }}" data-education-date-role="from" autocomplete="off" class="edu-date w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm sm:text-base">
                <label for="jhs_from" class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">From</label>
            </div>
            <div class="relative">
                <input type="text" id="jhs_to" name="jhs_to" maxlength="4" pattern="[0-9]{4}" inputmode="numeric" placeholder="YYYY" value="{{ $normalizeEducationDateForInput(old('jhs_to', session('form.c1.jhs_to'))) }}" data-education-date-role="to" autocomplete="off" class="edu-date w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm sm:text-base">
                <label for="jhs_to" class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">To</label>
                <p class="error-message hidden" data-education-date-error aria-live="polite"></p>
            </div>
            <div class="relative md:col-span-2">
                <input pattern="(?:[0-9]{4}|[Nn][\/]?[Aa])" maxlength="4" type="text" inputmode="text" id="jhs_year_graduated" name="jhs_year_graduated" value="{{ old('jhs_year_graduated', session('form.c1.jhs_year_graduated')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                <label for="jhs_year_graduated" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Year Graduated</label>
            </div>

            <div class="relative md:col-span-2 hidden" data-earned-wrapper-for="jhs_earned">
                <input type="text" id="jhs_earned" name="jhs_earned" value="{{ old('jhs_earned', session('form.c1.jhs_earned')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                <label for="jhs_earned" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-xs sm:text-sm">Highest Level/Units Earned (if not graduated) <span class="text-red-500">*</span></label>
            </div>

            <div class="relative md:col-span-2">
                <input type="text" id="jhs_academic_honors" name="jhs_academic_honors" value="{{ old('jhs_academic_honors', session('form.c1.jhs_academic_honors')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                <label for="jhs_academic_honors" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Scholarship/Academic Honors Received</label>
            </div>
        </div>
    </div>

    <!-- Vocational / Trade Course Placeholder -->
    <div class="mb-6 mt-[80px]" data-education-section="vocational">
        @include('partials.pds-education-form', [
            'education_type' => 'vocational',
            'education_type_meta' => ['title' => 'Vocational / Trade Course'],
            'education_data' => $vocational_schools
        ])
    </div>

    <!-- College Placeholder -->
    <div class="mb-6 mt-[80px]" data-education-section="college">
        @include('partials.pds-education-form', [
            'education_type' => 'college',
            'education_type_meta' => ['title' => 'College'],
            'education_data' => $college_schools
        ])
    </div>

    <!-- Graduate Studies Placeholder -->
    <div class="mb-6 mt-[80px]" data-education-section="grad">
        @include('partials.pds-education-form', [
            'education_type' => 'grad',
            'education_type_meta' => ['title' => 'Graduate Studies'],
            'education_data' => $grad_schools
        ])
    </div>
</section>

            <!-- Navigation -->
            <div class="flex flex-col sm:flex-row justify-between items-center mt-6 sm:mt-8 gap-4">
                <div class="w-full sm:w-auto flex flex-col sm:flex-row gap-3">
                    
                    <input type="file" id="pdsExcelFileInput" class="hidden" accept=".xlsx,.xls">
                </div>
                {{-- <button type="button" onclick="window.location.href='{{ route('dashboard_user') }}'" class="use-loader w-full sm:w-auto px-4 sm:px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors duration-200 flex items-center justify-center text-sm sm:text-base">
                    <span class="material-icons mr-2 text-lg sm:text-xl">home</span>
                    Dashboard
                </button> --}}
                <button type="submit" class="w-full sm:w-auto px-4 sm:px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors duration-200 flex items-center justify-center text-sm sm:text-base">
                    Save
                    <span class="material-icons ml-2 text-lg sm:text-xl">arrow_forward</span>
                </button>
            </div>
        </form>

        <!-- Warning Footer -->
        <footer class="mt-8 sm:mt-12 text-center text-xs sm:text-sm text-gray-600 px-4">
            <p class="mb-2">
                <strong>WARNING:</strong> Any misrepresentation made in the Personal Data Sheet and the Work Experience Sheet shall cause the filing of administrative/criminal case/s against the person concerned.
            </p>
            <p>CS FORM 212 (Revised 2025), Page 1 of 4.</p>
        </footer>
    </main>

    <!-- Error Alerts Placeholder -->
    <div id="errorAlerts" class="hidden">
        <!-- Error alerts would be dynamically inserted here -->
    </div>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    function submit(location) {
        const form = document.querySelector('#myForm');
        const simpleParam = new URLSearchParams(window.location.search).get('simple');
        const simpleQuery = simpleParam ? `?simple=${simpleParam}` : '';
        form.action = `/pds/submit_c1/${location}${simpleQuery}`;
        form.requestSubmit();
    }
    function parsePdsEducationDate(value) {
    const normalized = String(value || '').trim();
    if (!normalized) {
        return null;
    }

    // Handle year-only input (YYYY)
    if (/^\d{4}$/.test(normalized)) {
        const year = parseInt(normalized, 10);
        // Return a date object set to Dec 31 of that year for comparison purposes
        return new Date(year, 11, 31);
    }

    let parsed;

    if (/^\d{4}-\d{2}-\d{2}$/.test(normalized)) {
        const [year, month, day] = normalized.split('-').map(Number);
        parsed = new Date(year, month - 1, day);

        if (
            Number.isNaN(parsed.getTime()) ||
            parsed.getFullYear() !== year ||
            parsed.getMonth() !== month - 1 ||
            parsed.getDate() !== day
        ) {
            return null;
        }

        return parsed;
    }

    const match = normalized.match(/^(\d{2})-(\d{2})-(\d{4})$/);
    if (!match) {
        return null;
    }

    const day = Number(match[1]);
    const month = Number(match[2]);
    const year = Number(match[3]);
    parsed = new Date(year, month - 1, day);

    if (
        Number.isNaN(parsed.getTime()) ||
        parsed.getFullYear() !== year ||
        parsed.getMonth() !== month - 1 ||
        parsed.getDate() !== day
    ) {
        return null;
    }

    return parsed;
}

    function updateSecondaryBasicEducation() {
        const elemTo = document.getElementById('elem_to');
        const jhsBasic = document.getElementById('jhs_basic');
        if (!jhsBasic) return;

        const parsed = elemTo ? parsePdsEducationDate(elemTo.value) : null;
        const year = parsed ? parsed.getFullYear() : null;
        const beforeSeniorSplit = year && year < 2016;

        // No N/A option — always show meaningful education types.
        const desiredOptions = beforeSeniorSplit
            ? ['HIGH SCHOOL']
            : ['JUNIOR HIGH SCHOOL', 'SENIOR HIGH SCHOOL'];

        const currentValue = String(jhsBasic.value || '').trim();
        const normalizedCurrent = currentValue.toUpperCase();
        const knownSecondaryValues = ['HIGH SCHOOL', 'JUNIOR HIGH SCHOOL', 'SENIOR HIGH SCHOOL', 'N/A'];

        // Build options list (no N/A).
        const optionsList = [...desiredOptions];
        if (knownSecondaryValues.includes(normalizedCurrent) && !optionsList.includes(normalizedCurrent) && normalizedCurrent !== 'N/A') {
            optionsList.push(normalizedCurrent);
        }

        const existingOptions = Array.from(jhsBasic.options).map((o) => o.value);
        const optionsChanged = optionsList.length !== existingOptions.length || optionsList.some((v, i) => v !== existingOptions[i]);

        if (optionsChanged) {
            jhsBasic.innerHTML = '';
            optionsList.forEach((val) => {
                const opt = document.createElement('option');
                opt.value = val;
                opt.textContent = val
                    .replace(/\bHIGH SCHOOL\b/, 'High School')
                    .replace(/JUNIOR/i, 'Junior')
                    .replace(/SENIOR/i, 'Senior');
                jhsBasic.appendChild(opt);
            });
        }

        // Select current value if valid, otherwise default to first option.
        const matchingOption = optionsList.find((value) => value.toUpperCase() === normalizedCurrent);
        if (matchingOption) {
            jhsBasic.value = matchingOption;
        } else {
            jhsBasic.value = optionsList[0];
        }
    }

    /**
     * When the secondary "Highest Level/Units Earned" field has a value, the user
     * hasn't completed secondary education. Auto-set all College and Graduate Studies
     * fields to N/A and remove date required constraints, because those levels are
     * not applicable. Vocational/Trade Course is left untouched.
     */
    function syncCollegeGradWhenSecondaryEarned() {
        const jhsEarned = document.getElementById('jhs_earned');
        if (!jhsEarned) return;

        const hasEarned = jhsEarned.value !== null &&
            String(jhsEarned.value || '').trim() !== '' &&
            !isEmptyOrNaEducationValue(String(jhsEarned.value || '').trim());

        ['college', 'grad'].forEach((section) => {
            const container = document.querySelector('[data-education-section="' + section + '"]');
            if (!container) return;

            container.querySelectorAll('input, select, textarea').forEach((el) => {
                if (el.type === 'hidden') return;

                if (hasEarned) {
                    // Save original required state and remove it.
                    if (el.required) el.dataset.earnedDisabledRequired = '1';
                    el.required = false;
                    el.setCustomValidity && el.setCustomValidity('');

                    // Set field value to N/A (skip date/number inputs — clear them instead).
                    if (el.type === 'date' || el.type === 'number') {
                        el.value = '';
                    } else if (el.tagName === 'SELECT') {
                        // Try to select an N/A option; if none, use first option.
                        const naOpt = Array.from(el.options).find(
                            (o) => String(o.value).trim().toLowerCase().replace(/\s/g, '') === 'n/a' ||
                                   String(o.value).trim().toLowerCase().replace(/\s/g, '') === 'na'
                        );
                        if (naOpt) { el.value = naOpt.value; }
                        else if (el.options.length > 0) { el.selectedIndex = 0; }
                    } else {
                        el.value = 'N/A';
                    }
                } else {
                    // Restore required state.
                    if (el.dataset.earnedDisabledRequired === '1') {
                        el.required = true;
                        delete el.dataset.earnedDisabledRequired;
                    }
                    // Only clear N/A values that were auto-set (leave user-entered values).
                    if (el.tagName !== 'SELECT' && el.type !== 'date' && el.type !== 'number') {
                        if (String(el.value || '').trim() === 'N/A') {
                            el.value = '';
                        }
                    }
                }
            });
        });
    }

    function toggleSecondaryRequired() {
        const jhsBasic = document.getElementById('jhs_basic');
        const jhsSchool = document.getElementById('jhs_school');
        const jhsFrom = document.getElementById('jhs_from');
        const jhsTo = document.getElementById('jhs_to');

        if (!jhsBasic) return;

        const isNa = jhsBasic.value === 'N/A' || jhsBasic.value === '';

        // Toggle required attribute based on N/A selection
        if (jhsSchool) jhsSchool.required = !isNa;
        if (jhsFrom) jhsFrom.required = !isNa;
        if (jhsTo) jhsTo.required = !isNa;

        // Update labels to show/hide required asterisk
        const schoolLabel = jhsSchool ? jhsSchool.closest('.relative')?.querySelector('label') : null;
        const fromLabel = jhsFrom ? jhsFrom.closest('.relative')?.querySelector('label') : null;
        const toLabel = jhsTo ? jhsTo.closest('.relative')?.querySelector('label') : null;

        if (schoolLabel) {
            schoolLabel.innerHTML = isNa ? 'School Name' : 'School Name<span class="text-red-500">*</span>';
        }
        if (fromLabel) {
            fromLabel.innerHTML = isNa ? 'From' : 'From<span class="text-red-500">*</span>';
        }
        if (toLabel) {
            toLabel.innerHTML = isNa ? 'To' : 'To<span class="text-red-500">*</span>';
        }
    }

    function setSecondaryAndHigherEducationEnabled(enabled) {
        // Vocational remains available even when elementary is marked as not graduated.
        const sectionNames = ['secondary', 'college', 'grad'];
        const fields = [];

        sectionNames.forEach((section) => {
            const container = document.querySelector('[data-education-section="' + section + '"]');
            if (!container) return;

            container.querySelectorAll('input, select, textarea').forEach((el) => {
                fields.push(el);
            });
        });

        fields.forEach((el) => {
            if (enabled) {
                el.disabled = false;
                if (el.dataset.wasRequired === '1') {
                    el.required = true;
                }
            } else {
                if (el.required) {
                    el.dataset.wasRequired = '1';
                }
                el.required = false;

                if (el.type === 'checkbox' || el.type === 'radio') {
                    el.checked = false;
                } else if (el.tagName === 'SELECT') {
                    const naOption = Array.from(el.options || []).find((option) => {
                        const normalized = String(option.value || '').trim().toLowerCase().replace(/\s+/g, '');
                        return normalized === 'n/a' || normalized === 'na' || normalized === 'n\\a';
                    });

                    if (naOption) {
                        el.value = naOption.value;
                    } else {
                        el.selectedIndex = 0;
                    }
                } else if (el.tagName === 'TEXTAREA') {
                    el.value = 'N/A';
                } else if (el.type === 'date' || el.type === 'datetime-local' || el.type === 'month' || el.type === 'time' || el.type === 'week' || el.type === 'number') {
                    // Keep non-textual inputs valid while disabled.
                    el.value = '';
                } else {
                    el.value = 'N/A';
                }

                el.disabled = true;
            }
        });
    }
    function isEmptyOrNaEducationValue(value) {
        const normalized = String(value || '').trim().toLowerCase().replace(/\s+/g, '');
        return normalized === '' || normalized === 'n/a' || normalized === 'na' || normalized === 'n\\a';
    }
    function syncHighestLevelVisibilityByYear(yearId, levelId) {
        const yearInput = document.getElementById(yearId);
        const levelInput = document.getElementById(levelId);
        const levelWrapper = document.querySelector('[data-earned-wrapper-for="' + levelId + '"]');

        if (!yearInput || !levelInput || !levelWrapper) return;

        const hasYearGraduated = !isEmptyOrNaEducationValue(yearInput.value);

        levelWrapper.classList.toggle('hidden', hasYearGraduated);
        levelInput.disabled = hasYearGraduated;
        levelInput.required = !hasYearGraduated;

        if (hasYearGraduated) {
            levelInput.value = '';
            levelInput.setCustomValidity('');
        }
    }
    function clearEducationSectionsForGraduateTransition() {
        const sectionNames = ['secondary', 'college', 'grad'];

        sectionNames.forEach((section) => {
            const container = document.querySelector('[data-education-section="' + section + '"]');
            if (!container) return;

            container.querySelectorAll('input, select, textarea').forEach((el) => {
                if (el.type === 'hidden') {
                    return;
                }

                if (el.type === 'checkbox' || el.type === 'radio') {
                    el.checked = false;
                    return;
                }

                if (el.tagName === 'SELECT') {
                    const emptyOption = Array.from(el.options || []).find((option) => String(option.value || '').trim() === '');
                    if (emptyOption) {
                        el.value = '';
                    } else if (el.options.length > 0) {
                        el.selectedIndex = 0;
                    }
                    return;
                }

                el.value = '';
            });
        });
    }
    function syncElementaryYearGraduatedState(isUserChange = false) {
        const elemYearInput = document.getElementById('elem_year_graduated');
        if (!elemYearInput) return;

        const hasYearGraduated = !isEmptyOrNaEducationValue(elemYearInput.value);

        if (hasYearGraduated) {
            setSecondaryAndHigherEducationEnabled(true);
            toggleSecondaryRequired(); // Ensure required state matches N/A selection
            syncHighestLevelVisibilityByYear('jhs_year_graduated', 'jhs_earned');
        } else {
            // Only clear secondary/college/grad data when the user actively clears the
            // elementary year field AND those sections are currently empty (no existing data).
            // On initial page load, we must NOT wipe pre-filled secondary/college/grad data.
            const alreadyHasData = hasSecondaryOrHigherEducationValues();
            if (isUserChange && !alreadyHasData) {
                clearEducationSectionsForGraduateTransition();
            }
            // Always disable the sections when elementary year is absent so validation
            // is not enforced on them, but do NOT overwrite any existing values.
            const sectionNames = ['secondary', 'college', 'grad'];
            sectionNames.forEach((section) => {
                const container = document.querySelector('[data-education-section="' + section + '"]');
                if (!container) return;
                container.querySelectorAll('input, select, textarea').forEach((el) => {
                    if (el.required) el.dataset.wasRequired = '1';
                    el.required = false;
                    // Do NOT set el.disabled here — that strips values from form submission.
                });
            });

            const jhsEarnedInput = document.getElementById('jhs_earned');
            const jhsEarnedWrapper = document.querySelector('[data-earned-wrapper-for="jhs_earned"]');
            if (jhsEarnedInput) {
                jhsEarnedInput.required = false;
                jhsEarnedInput.setCustomValidity('');
            }
            if (jhsEarnedWrapper) {
                jhsEarnedWrapper.classList.add('hidden');
            }
        }

        syncHighestLevelVisibilityByYear('elem_year_graduated', 'elem_earned');
    }
    function hasSecondaryOrHigherEducationValues() {
        // Do not treat vocational values as requiring elementary graduate state.
        const sectionNames = ['secondary', 'college', 'grad'];

        return sectionNames.some((section) => {
            const container = document.querySelector('[data-education-section="' + section + '"]');
            if (!container) return false;

            const fields = container.querySelectorAll('input, select, textarea');
            for (let i = 0; i < fields.length; i++) {
                const el = fields[i];
                if (el.type === 'hidden') continue;
                if (typeof el.value === 'string' && el.value.trim() !== '') {
                    return true;
                }
            }
            return false;
        });
    }
    function validateElementaryToAndSecondaryFrom() {
        const elemTo = document.getElementById('elem_to');
        const jhsFrom = document.getElementById('jhs_from');
        if (!elemTo || !jhsFrom) return;

        jhsFrom.setCustomValidity('');

        const elemToDate = parsePdsEducationDate(elemTo.value);
        const jhsFromDate = parsePdsEducationDate(jhsFrom.value);

        if (!elemToDate || !jhsFromDate) {
            jhsFrom.removeAttribute('min');
            return;
        }

        jhsFrom.min = formatPdsEducationDateForInput(elemToDate);
        if (jhsFromDate.getTime() < elemToDate.getTime()) {
            jhsFrom.setCustomValidity('Secondary "From" date must not be before Elementary "To" date.');
            jhsFrom.reportValidity();
        }
    }

    function validateSecondaryToAndCollegeFrom() {
        const jhsTo = document.getElementById('jhs_to');
        if (!jhsTo) return;

        const jhsToDate = parsePdsEducationDate(jhsTo.value);

        const collegeContainer = document.getElementById('college-container');
        if (!collegeContainer) return;

        const collegeFromInputs = collegeContainer.querySelectorAll('[data-education-date-role="from"]');

        collegeFromInputs.forEach(collegeFrom => {
            collegeFrom.setCustomValidity('');

            const collegeFromDate = parsePdsEducationDate(collegeFrom.value);

            if (!jhsToDate || !collegeFromDate) {
                collegeFrom.removeAttribute('min');
                return;
            }

            collegeFrom.min = formatPdsEducationDateForInput(jhsToDate);
            if (collegeFromDate.getTime() < jhsToDate.getTime()) {
                collegeFrom.setCustomValidity('College "From" date must not be before Secondary "To" date.');
                collegeFrom.reportValidity();
            }
        });
    }

    function togglePdsEducationDateRangeState(rangeEl, state) {
        const fromInput = rangeEl.querySelector('[data-education-date-role="from"]');
        const toInput = rangeEl.querySelector('[data-education-date-role="to"]');
        const errorEl = rangeEl.querySelector('[data-education-date-error]');
        const nextStateKey = JSON.stringify(state);
        const currentStateKey = rangeEl.dataset.educationDateState || '';

        if (!fromInput || !toInput) {
            return;
        }

        if (currentStateKey === nextStateKey) {
            return;
        }

        rangeEl.dataset.educationDateState = nextStateKey;
        fromInput.setCustomValidity(state.fromMessage || '');
        toInput.setCustomValidity(state.toMessage || '');

        fromInput.classList.toggle('error-field', Boolean(state.fromInvalid));
        fromInput.setAttribute('aria-invalid', state.fromInvalid ? 'true' : 'false');

        toInput.classList.toggle('error-field', Boolean(state.toInvalid));
        toInput.setAttribute('aria-invalid', state.toInvalid ? 'true' : 'false');

        if (errorEl) {
            errorEl.textContent = state.inlineMessage || '';
            errorEl.classList.toggle('hidden', !state.inlineMessage);
        }
    }
function formatPdsEducationDateForInput(date) {
    if (!date) return '';
    const year = date.getFullYear();
    return `${year}`;  // Return just the year
}

function addPdsEducationDays(date, days) {
    const shifted = new Date(date);
    shifted.setDate(shifted.getDate() + days);
    return shifted;
}

    function validatePdsEducationDateRange(rangeEl) {
    if (!rangeEl) return;

    const fromInput = rangeEl.querySelector('[data-education-date-role="from"]');
    const toInput = rangeEl.querySelector('[data-education-date-role="to"]');
    if (!fromInput || !toInput) return;

    const fromValue = (fromInput.value || '').trim();
    const toValue = (toInput.value || '').trim();
    
    const fromYear = /^\d{4}$/.test(fromValue) ? parseInt(fromValue, 10) : null;
    const toYear = /^\d{4}$/.test(toValue) ? parseInt(toValue, 10) : null;
    
    const fromHasInvalidFormat = Boolean(fromValue && !fromYear);
    const toHasInvalidFormat = Boolean(toValue && !toYear);
    const hasRangeError = Boolean(fromYear && toYear && fromYear >= toYear);

    if (fromHasInvalidFormat) {
        togglePdsEducationDateRangeState(rangeEl, {
            fromInvalid: true,
            toInvalid: false,
            fromMessage: 'Enter a valid year (YYYY).',
            toMessage: '',
            inlineMessage: 'Enter a valid From year (YYYY).',
        });
        return;
    }

    if (toHasInvalidFormat) {
        togglePdsEducationDateRangeState(rangeEl, {
            fromInvalid: false,
            toInvalid: true,
            fromMessage: '',
            toMessage: 'Enter a valid year (YYYY).',
            inlineMessage: 'Enter a valid To year (YYYY).',
        });
        return;
    }

    if (hasRangeError) {
        togglePdsEducationDateRangeState(rangeEl, {
            fromInvalid: true,
            toInvalid: true,
            fromMessage: 'The "From" year must be earlier than the "To" year.',
            toMessage: 'The "To" year must be later than the "From" year.',
            inlineMessage: 'From year must be earlier than To year.',
        });
        return;
    }

    togglePdsEducationDateRangeState(rangeEl, {
        fromInvalid: false,
        toInvalid: false,
        fromMessage: '',
        toMessage: '',
        inlineMessage: '',
    });
}


    function bindPdsEducationDateRange(rangeEl) {
        if (!rangeEl || rangeEl.dataset.educationDateBound === '1') {
            validatePdsEducationDateRange(rangeEl);
            return;
        }

        const fromInput = rangeEl.querySelector('[data-education-date-role="from"]');
        const toInput = rangeEl.querySelector('[data-education-date-role="to"]');
        if (!fromInput || !toInput) {
            return;
        }

        const validate = () => validatePdsEducationDateRange(rangeEl);
        ['input', 'change', 'blur'].forEach((eventName) => {
            fromInput.addEventListener(eventName, validate);
            toInput.addEventListener(eventName, validate);
        });

        rangeEl.dataset.educationDateBound = '1';
        validate();
    }
    function initPdsEducationDateRanges(scopeEl = document) {
        const root = scopeEl || document;
        const rangeEls = [];

        if (typeof root.matches === 'function' && root.matches('[data-education-date-range]')) {
            rangeEls.push(root);
        }
        if (typeof root.querySelectorAll === 'function') {
            rangeEls.push(...root.querySelectorAll('[data-education-date-range]'));
        }

        rangeEls.forEach(bindPdsEducationDateRange);
    }
    window.initPdsEducationDateRanges = initPdsEducationDateRanges;

    function getMinimumAllowedDob() {
        const cutoff = new Date();
        cutoff.setHours(0, 0, 0, 0);
        cutoff.setFullYear(cutoff.getFullYear() - 18);
        return cutoff;
    }
    function formatPdsDateForDisplay(date) {
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = String(date.getFullYear());
        return `${day}-${month}-${year}`;
    }
    function autoAdjustDobIfUnderage(dobInput, pickerInstance = null) {
        const raw = (dobInput?.value || '').trim();
        if (!raw) {
            return false;
        }

        const parsedDob = parsePdsEducationDate(raw);
        if (!parsedDob) {
            return false;
        }

        parsedDob.setHours(0, 0, 0, 0);
        const minimumAllowedDob = getMinimumAllowedDob();
        if (parsedDob.getTime() <= minimumAllowedDob.getTime()) {
            return false;
        }

        if (pickerInstance && typeof pickerInstance.setDate === 'function') {
            pickerInstance.setDate(minimumAllowedDob, false, 'd-m-Y');
        } else {
            dobInput.value = formatPdsDateForDisplay(minimumAllowedDob);
        }

        return true;
    }
    function validateDobAge(showMessage = true, autoAdjust = false, pickerInstance = null) {
        const dobInput = document.querySelector('[data-dob-input]');
        if (!dobInput) return true;

        const raw = (dobInput.value || '').trim();
        if (!raw) {
            dobInput.setCustomValidity('');
            return true;
        }

        if (autoAdjust && autoAdjustDobIfUnderage(dobInput, pickerInstance)) {
            dobInput.setCustomValidity('');
            return true;
        }

        const dob = parsePdsEducationDate(raw);
        const minimumAllowedDob = getMinimumAllowedDob();

        let message = '';
        if (!dob) {
            message = 'Enter a valid date in dd-mm-yyyy format.';
        } else {
            dob.setHours(0, 0, 0, 0);
            if (dob.getTime() > minimumAllowedDob.getTime()) {
                message = 'Applicant must be at least 18 years old.';
            }
        }

        dobInput.setCustomValidity(message);
        if (message && showMessage) {
            dobInput.reportValidity();
            return false;
        }

        return true;
    }

    const dobPicker = flatpickr('#date_of_birth', {
        dateFormat: 'd-m-Y',
        allowInput: true,
        disableMobile: true,
        maxDate: getMinimumAllowedDob(),
        onChange: (_, __, instance) => validateDobAge(false, true, instance),
        onClose: (_, __, instance) => validateDobAge(false, true, instance),
        onValueUpdate: (_, __, instance) => validateDobAge(false, true, instance),
    });

    document.addEventListener('DOMContentLoaded', function () {
        initPdsEducationDateRanges(document);

        updateSecondaryBasicEducation();
        const elemTo = document.getElementById('elem_to');
        if (elemTo) {
            ['input', 'change', 'blur'].forEach((evt) => {
                elemTo.addEventListener(evt, updateSecondaryBasicEducation);
                elemTo.addEventListener(evt, validateElementaryToAndSecondaryFrom);
            });
        }
        const jhsFrom = document.getElementById('jhs_from');
        if (jhsFrom) {
            ['input', 'change', 'blur'].forEach((evt) => {
                jhsFrom.addEventListener(evt, validateElementaryToAndSecondaryFrom);
            });
        }

        const jhsTo = document.getElementById('jhs_to');
        if (jhsTo) {
            ['input', 'change', 'blur'].forEach((evt) => {
                jhsTo.addEventListener(evt, validateSecondaryToAndCollegeFrom);
            });
        }

        const collegeContainer = document.getElementById('college-container');
        if (collegeContainer) {
            ['input', 'change', 'blur'].forEach((evt) => {
                collegeContainer.addEventListener(evt, (e) => {
                    if (e.target && e.target.matches('[data-education-date-role="from"]')) {
                        validateSecondaryToAndCollegeFrom();
                    }
                });
            });
        }

        const elemYearGraduated = document.getElementById('elem_year_graduated');
        if (elemYearGraduated) {
            ['input', 'change', 'blur'].forEach((evt) => {
                // Pass isUserChange=true so clearing the field only wipes empty sections
                elemYearGraduated.addEventListener(evt, () => syncElementaryYearGraduatedState(true));
            });
        }
        const jhsYearGraduated = document.getElementById('jhs_year_graduated');
        if (jhsYearGraduated) {
            ['input', 'change', 'blur'].forEach((evt) => {
                jhsYearGraduated.addEventListener(evt, () => syncHighestLevelVisibilityByYear('jhs_year_graduated', 'jhs_earned'));
            });
        }

        // Initial page load — pass false so existing secondary/college/grad data is preserved.
        syncElementaryYearGraduatedState(false);
        validateElementaryToAndSecondaryFrom();
        validateSecondaryToAndCollegeFrom();

        // Wire: when jhs_earned (Highest Level/Units Earned) changes, auto-fill college/grad with N/A.
        const jhsEarnedInput = document.getElementById('jhs_earned');
        if (jhsEarnedInput) {
            ['input', 'change', 'blur'].forEach((evt) => {
                jhsEarnedInput.addEventListener(evt, syncCollegeGradWhenSecondaryEarned);
            });
        }
        // Apply on initial load too (in case jhs_earned is already filled from session).
        syncCollegeGradWhenSecondaryEarned();

        // Toggle secondary fields required state based on basic education selection
        const jhsBasic = document.getElementById('jhs_basic');
        if (jhsBasic) {
            jhsBasic.addEventListener('change', toggleSecondaryRequired);
            toggleSecondaryRequired(); // Initial call on page load
        }

        const dobInput = document.querySelector('[data-dob-input]');
        if (dobInput) {
            dobInput.addEventListener('blur', () => validateDobAge(true, true, dobPicker));
            dobInput.addEventListener('change', () => validateDobAge(false, true, dobPicker));
            dobInput.addEventListener('input', () => dobInput.setCustomValidity(''));
            validateDobAge(false, true, dobPicker);
        }

        const showSystemLoader = (message = 'Loading...') => {
            const loader = document.getElementById('loader');
            if (!loader) return;

            loader.classList.remove('hidden');
            loader.classList.remove('pds-loading-nonblocking');
            loader.setAttribute('aria-busy', 'true');

            const loaderText = document.getElementById('loader-text');
            if (loaderText) {
                loaderText.textContent = message;
            }

            const loaderLive = document.getElementById('loader-live');
            if (loaderLive) {
                loaderLive.textContent = message;
            }
        };

        const form = document.querySelector('#myForm');
        if (form) {
            form.addEventListener('submit', (event) => {
                if (!validateDobAge(true, true, dobPicker)) {
                    event.preventDefault();
                    event.stopPropagation();
                    return;
                }

                showSystemLoader('Saving changes...');
            });
        }
    });
    const psgcApiBase = @json(url('/psgc'));
    const perProvince = document.querySelector('#per_province');
    const perCity = document.querySelector('#per_city');
    const perBrgy = document.querySelector('#per_brgy');
    const resProvince = document.querySelector('#res_province');
    const resCity = document.querySelector('#res_city');
    const resBrgy = document.querySelector('#res_brgy');
    const PSGC_CACHE_KEY = 'psgc:c1:v2';
    const PSGC_LEGACY_CACHE_KEYS = ['psgc:c1:v1'];
    const PSGC_CACHE_TTL_MS = 24 * 60 * 60 * 1000;
    const PSGC_MIN_PROVINCE_COUNT = 50;
    const psgcClientCache = {
        provinces: null,
        citiesByProvince: new Map(),
        barangaysByCity: new Map(),
        cityByCode: new Map(),
    };
    function isValidPsgcCode(raw) {
        return /^\d{10}$/.test(String(raw || '').trim());
    }
    function isValidPsgcName(raw) {
        return /[A-Za-z]/.test(String(raw || '').trim());
    }
    function sanitizePsgcEntries(entries) {
        if (!Array.isArray(entries)) return [];
        return entries
            .filter((entry) => entry && typeof entry === 'object')
            .map((entry) => ({
                code: String(entry.code || '').trim(),
                name: String(entry.name || '').trim(),
                zip_code: entry.zip_code == null ? null : String(entry.zip_code).trim(),
            }))
            .filter((entry) => isValidPsgcCode(entry.code) && isValidPsgcName(entry.name));
    }
    function hydratePsgcClientCache() {
        try {
            PSGC_LEGACY_CACHE_KEYS.forEach((legacyKey) => sessionStorage.removeItem(legacyKey));
            const raw = sessionStorage.getItem(PSGC_CACHE_KEY);
            if (!raw) return;
            const parsed = JSON.parse(raw);
            if (!parsed || typeof parsed !== 'object') return;
            const savedAt = Number(parsed.savedAt || 0);
            if (!savedAt || (Date.now() - savedAt) > PSGC_CACHE_TTL_MS) {
                sessionStorage.removeItem(PSGC_CACHE_KEY);
                return;
            }
            const provinces = sanitizePsgcEntries(parsed.provinces);
            if (provinces.length >= PSGC_MIN_PROVINCE_COUNT) {
                psgcClientCache.provinces = provinces;
            } else if (Array.isArray(parsed.provinces)) {
                sessionStorage.removeItem(PSGC_CACHE_KEY);
                return;
            }
            if (parsed.citiesByProvince && typeof parsed.citiesByProvince === 'object') {
                Object.entries(parsed.citiesByProvince).forEach(([k, v]) => {
                    const entries = sanitizePsgcEntries(v);
                    if (entries.length > 0) {
                        psgcClientCache.citiesByProvince.set(String(k), entries);
                    }
                });
            }
            if (parsed.barangaysByCity && typeof parsed.barangaysByCity === 'object') {
                Object.entries(parsed.barangaysByCity).forEach(([k, v]) => {
                    const entries = sanitizePsgcEntries(v);
                    if (entries.length > 0) {
                        psgcClientCache.barangaysByCity.set(String(k), entries);
                    }
                });
            }
            if (parsed.cityByCode && typeof parsed.cityByCode === 'object') {
                Object.entries(parsed.cityByCode).forEach(([k, v]) => {
                    psgcClientCache.cityByCode.set(String(k), String(v || ''));
                });
            }
        } catch (e) {}
    }
    function persistPsgcClientCache() {
        try {
            const payload = {
                savedAt: Date.now(),
                provinces: Array.isArray(psgcClientCache.provinces) ? psgcClientCache.provinces : null,
                citiesByProvince: Object.fromEntries(psgcClientCache.citiesByProvince),
                barangaysByCity: Object.fromEntries(psgcClientCache.barangaysByCity),
                cityByCode: Object.fromEntries(psgcClientCache.cityByCode),
            };
            sessionStorage.setItem(PSGC_CACHE_KEY, JSON.stringify(payload));
        } catch (e) {}
    }
    hydratePsgcClientCache();
    const userStorageKey = @json(
        auth()->check()
            ? ('uid:' . auth()->id())
            : 'guest'
    );
    const pageKey = 'pds:' + userStorageKey + ':' + window.location.pathname.toLowerCase();
    let savedState = {};
    try { savedState = JSON.parse(sessionStorage.getItem(pageKey) || '{}'); } catch(e) {}
    function readState(){ try { return JSON.parse(sessionStorage.getItem(pageKey) || '{}'); } catch(e){ return {}; } }
    function writeState(k, v){
        const s = readState();
        if (typeof v === 'string' && v.trim() === '') {
            delete s[k];
        } else if (v === null || v === undefined) {
            delete s[k];
        } else {
            s[k] = v;
        }
        try { sessionStorage.setItem(pageKey, JSON.stringify(s)); } catch(e){}
    }
    function stateOrFallback(key, fallback) {
        const v = savedState[key];
        if (v === undefined || v === null) return fallback;
        if (typeof v === 'string' && v.trim() === '') return fallback;
        return String(v);
    }

    const perProvinceName = stateOrFallback('per_province', "{{ old('per_province', session('form.c1.per_province')) }}");
    const perCityName = stateOrFallback('per_city', "{{ old('per_city', session('form.c1.per_city')) }}");
    const perBrgyName = stateOrFallback('per_brgy', "{{ old('per_brgy', session('form.c1.per_brgy')) }}");
    const resProvinceName = stateOrFallback('res_province', "{{ old('res_province', session('form.c1.res_province')) }}");
    const resCityName = stateOrFallback('res_city', "{{ old('res_city', session('form.c1.res_city')) }}");
    const resBrgyName = stateOrFallback('res_brgy', "{{ old('res_brgy', session('form.c1.res_brgy')) }}");

    [
        'per_zipcode',
        'res_zipcode',
        'per_house_no',
        'per_street',
        'per_sub_vil',
        'res_house_no',
        'res_street',
        'res_sub_vil'
    ].forEach(id=>{
        const el = document.getElementById(id);
        if (!el) return;
        if (
            savedState[id] !== undefined
            && savedState[id] !== null
            && String(savedState[id]).trim() !== ''
        ) {
            el.value = String(savedState[id]);
        }
        const handler = () => {
            writeState(id, el.value);
        };
        el.addEventListener('input', handler);
        el.addEventListener('change', handler);
    });
    function setRadio(name, val){
        if (!val) return;
        const target = document.querySelector('input[name="'+name+'"][value="'+val+'"]');
        if (target) { target.checked = true; target.dispatchEvent(new Event('change')); }
    }
    function onlyDigits(value) {
        return String(value || '').replace(/\D+/g, '');
    }
    function enforceTelephoneInputLimit() {
        const input = document.getElementById('telephone_no');
        if (!input) return;
        const hadFormatting = /[()\s]/.test(input.value);
        const digits = onlyDigits(input.value).slice(0, 10);
        if (!hadFormatting) {
            input.value = digits;
            return;
        }

        let formatted = '';
        if (digits.length > 0) {
            formatted = '(' + digits.slice(0, 2);
            if (digits.length >= 2) formatted += ')';
            if (digits.length > 2) formatted += ' ' + digits.slice(2, 6);
            if (digits.length > 6) formatted += ' ' + digits.slice(6, 10);
        }
        input.value = formatted.trim();
    }
    function enforceMobileInputLimit() {
        const input = document.getElementById('mobile_no');
        if (!input) return;

        let digits = onlyDigits(input.value).slice(0, 11);
        if (digits.length === 0) {
            input.value = '';
            return;
        }

        if (digits.length >= 1 && digits[0] !== '0') {
            digits = '0' + digits.slice(1);
        }
        if (digits.length >= 2 && digits[1] !== '9') {
            digits = '09' + digits.slice(2);
        }
        if (digits.length === 1) {
            digits = '0';
        } else if (digits.length >= 2) {
            digits = '09' + digits.slice(2);
        }
        digits = digits.slice(0, 11);

        let formatted = digits.slice(0, 4);
        if (digits.length > 4) formatted += ' ' + digits.slice(4, 7);
        if (digits.length > 7) formatted += ' ' + digits.slice(7, 11);
        input.value = formatted.trim();
    }
    function enforceZipCodeInputLimit() {
        ['res_zipcode', 'per_zipcode'].forEach((id) => {
            const input = document.getElementById(id);
            if (!input) return;
            input.value = onlyDigits(input.value).slice(0, 4);
        });
    }
    function hookRadio(name){
        document.querySelectorAll('input[name="'+name+'"]').forEach(r=>{
            r.addEventListener('change', ()=>{ if (r.checked) writeState(name, r.value); });
        });
    }
    setRadio('sex', savedState.sex ?? "{{ old('sex', session('form.c1.sex')) }}");
    hookRadio('sex');
    const civil = document.getElementById('civil_status');
    if (civil){
        const preset = savedState.civil_status ?? "{{ old('civil_status', session('form.c1.civil_status')) }}";
        if (preset){ civil.value = preset; civil.dispatchEvent(new Event('change')); }
        civil.addEventListener('change', ()=> writeState('civil_status', civil.value));
    }
    setRadio('citizenship', savedState.citizenship ?? "{{ old('citizenship', session('form.c1.citizenship')) }}");
    hookRadio('citizenship');
    setRadio('dual_type', savedState.dual_type ?? "{{ old('dual_type', session('form.c1.dual_type')) }}");
    hookRadio('dual_type');
    const dualCountry = document.getElementById('dual_country');
    if (dualCountry){
        const dualCountryPreset = stateOrFallback('dual_country', "{{ old('dual_country', session('form.c1.dual_country')) }}");
        if (dualCountryPreset){
            dualCountry.value = dualCountryPreset;
        }
        const handler = ()=> writeState('dual_country', dualCountry.value);
        dualCountry.addEventListener('input', handler);
        dualCountry.addEventListener('change', handler);
    }
    const telephoneInput = document.getElementById('telephone_no');
    if (telephoneInput) {
        enforceTelephoneInputLimit();
        telephoneInput.addEventListener('input', enforceTelephoneInputLimit);
    }
    const mobileInput = document.getElementById('mobile_no');
    if (mobileInput) {
        enforceMobileInputLimit();
        mobileInput.addEventListener('input', enforceMobileInputLimit);
    }
    ['res_zipcode', 'per_zipcode'].forEach((id) => {
        const zipInput = document.getElementById(id);
        if (!zipInput) return;
        enforceZipCodeInputLimit();
        zipInput.addEventListener('input', enforceZipCodeInputLimit);
        zipInput.addEventListener('change', enforceZipCodeInputLimit);
    });
    function normalizePlaceText(value) {
        return String(value || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/\bcity of\b/g, 'city')
            .replace(/\bmunicipality of\b/g, 'municipality')
            .replace(/\bmun\.?\b/g, 'municipality')
            .replace(/\bbrgy\.?\b/g, 'barangay')
            .replace(/[^a-z0-9]/g, '');
    }
    function resolvePreselectText(items, textKey, valueKey, preselectText) {
        const raw = String(preselectText || '').trim();
        if (!raw) return null;
        const byCode = items.find(i => String(i[valueKey] || '').trim() === raw);
        if (byCode) return String(byCode[textKey]);
        const exact = items.find(i => String(i[textKey] || '').trim().toLowerCase() === raw.toLowerCase());
        if (exact) return String(exact[textKey]);
        const normalizedTarget = normalizePlaceText(raw);
        const normalized = items.find(i => normalizePlaceText(i[textKey]) === normalizedTarget);
        return normalized ? String(normalized[textKey]) : null;
    }
    function setOptions(select, items, textKey, valueKey, preselectText) {
        select.innerHTML = '';
        const ph = document.createElement('option');
        ph.value = '';
        ph.textContent = 'Select';
        ph.selected = true;
        select.appendChild(ph);
        select._list = items;
        const resolvedPreselect = resolvePreselectText(items, textKey, valueKey, preselectText);
        items.forEach(i => {
            const opt = document.createElement('option');
            opt.value = i[textKey];
            opt.textContent = i[textKey];
            opt.dataset.code = i[valueKey];
            if (resolvedPreselect && i[textKey] === resolvedPreselect) {
                opt.selected = true;
            }
            select.appendChild(opt);
        });
    }
    function getSelectedCode(select) {
        const opt = select.options[select.selectedIndex];
        return opt ? opt.dataset.code : '';
    }
    function setSelectByCode(select, code) {
        if (!select || !code) return false;
        const targetCode = String(code);
        const option = Array.from(select.options || []).find(opt => String(opt.dataset.code || '') === targetCode);
        if (!option) return false;
        select.value = option.value;
        return true;
    }
    function waitForSelectOptionCode(select, code, timeoutMs = 5000, intervalMs = 100) {
        if (!select || !code) return Promise.resolve(false);
        const targetCode = String(code);
        const startedAt = Date.now();
        return new Promise(resolve => {
            const timer = setInterval(() => {
                const hasOption = Array.from(select.options || []).some(opt => String(opt.dataset.code || '') === targetCode);
                if (hasOption) {
                    clearInterval(timer);
                    resolve(true);
                    return;
                }
                if (Date.now() - startedAt >= timeoutMs) {
                    clearInterval(timer);
                    resolve(false);
                }
            }, intervalMs);
        });
    }
    function loadProvinces(select, preselectText, onDone) {
        if (Array.isArray(psgcClientCache.provinces)) {
            const data = psgcClientCache.provinces;
            setOptions(select, data, 'name', 'code', preselectText);
            if (onDone) onDone(data.find(p => p.name === select.value)?.code || getSelectedCode(select));
            return;
        }

        fetch(psgcApiBase + '/provinces')
            .then(r => r.ok ? r.json() : Promise.reject())
            .then(data => {
                psgcClientCache.provinces = Array.isArray(data) ? data : [];
                persistPsgcClientCache();
                setOptions(select, data, 'name', 'code', preselectText);
                if (onDone) onDone(data.find(p => p.name === preselectText)?.code || getSelectedCode(select));
            })
            .catch(() => {
                setOptions(select, [], 'name', 'code', null);
                if (onDone) onDone('');
            });
    }
    function loadCities(provinceCode, select, preselectText, onDone) {
        if (!provinceCode) {
            setOptions(select, [], 'name', 'code', null);
            if (onDone) onDone('');
            return;
        }

        const normalizedProvinceCode = String(provinceCode);
        if (psgcClientCache.citiesByProvince.has(normalizedProvinceCode)) {
            const data = psgcClientCache.citiesByProvince.get(normalizedProvinceCode) || [];
            setOptions(select, data, 'name', 'code', preselectText);
            if (onDone) onDone(data.find(c => c.name === select.value)?.code || getSelectedCode(select));
            return;
        }

        fetch(psgcApiBase + '/provinces/' + encodeURIComponent(provinceCode) + '/cities-municipalities')
            .then(r => r.ok ? r.json() : Promise.reject())
            .then(data => {
                psgcClientCache.citiesByProvince.set(normalizedProvinceCode, Array.isArray(data) ? data : []);
                persistPsgcClientCache();
                setOptions(select, data, 'name', 'code', preselectText);
                if (onDone) onDone(data.find(c => c.name === preselectText)?.code || getSelectedCode(select));
            })
            .catch(() => {
                setOptions(select, [], 'name', 'code', null);
                if (onDone) onDone('');
            });
    }
    function loadBarangays(cityCode, select, preselectText) {
        if (!cityCode) {
            setOptions(select, [], 'name', 'code', null);
            return;
        }

        const normalizedCityCode = String(cityCode);
        if (psgcClientCache.barangaysByCity.has(normalizedCityCode)) {
            const data = psgcClientCache.barangaysByCity.get(normalizedCityCode) || [];
            setOptions(select, data, 'name', 'code', preselectText);
            return;
        }

        fetch(psgcApiBase + '/cities-municipalities/' + encodeURIComponent(cityCode) + '/barangays')
            .then(r => r.ok ? r.json() : Promise.reject())
            .then(data => {
                psgcClientCache.barangaysByCity.set(normalizedCityCode, Array.isArray(data) ? data : []);
                persistPsgcClientCache();
                setOptions(select, data, 'name', 'code', preselectText);
            })
            .catch(() => {
                setOptions(select, [], 'name', 'code', null);
            });
    }
    loadProvinces(perProvince, perProvinceName, (provCode) => {
        loadCities(provCode, perCity, perCityName, (cityCode) => {
            loadBarangays(cityCode, perBrgy, perBrgyName);
        });
    });
    loadProvinces(resProvince, resProvinceName, (provCode) => {
        loadCities(provCode, resCity, resCityName, (cityCode) => {
            loadBarangays(cityCode, resBrgy, resBrgyName);
        });
    });
    perProvince.addEventListener('change', e => {
        writeState('per_province', perProvince.value);
        // Clear dependent fields from state
        writeState('per_city', '');
        writeState('per_brgy', '');
        loadCities(getSelectedCode(perProvince), perCity, null, (cityCode) => {
            loadBarangays(cityCode, perBrgy, null);
        });
    });
    perCity.addEventListener('change', e => { 
        const code = getSelectedCode(perCity); 
        writeState('per_city', perCity.value);
        // Clear dependent field from state
        writeState('per_brgy', '');
        loadBarangays(code, perBrgy, null); 
    });
    perBrgy.addEventListener('change', e => {
        writeState('per_brgy', perBrgy.value);
    });

    resProvince.addEventListener('change', e => {
        writeState('res_province', resProvince.value);
        // Clear dependent fields from state
        writeState('res_city', '');
        writeState('res_brgy', '');
        loadCities(getSelectedCode(resProvince), resCity, null, (cityCode) => {
            loadBarangays(cityCode, resBrgy, null);
        });
    });
    resCity.addEventListener('change', e => { 
        const code = getSelectedCode(resCity); 
        writeState('res_city', resCity.value);
        // Clear dependent field from state
        writeState('res_brgy', '');
        loadBarangays(code, resBrgy, null); 
    });
    resBrgy.addEventListener('change', e => {
        writeState('res_brgy', resBrgy.value);
    });

    // --- Hidden fallback sync for address selects ---
    // When a select gets a value, keep the hidden input in sync so the fallback is current.
    // On form submit, if a select is still empty (PSGC API not yet resolved), inject the
    // fallback value as a real option so the form posts the correct address text.
    const addressSelectPairs = [
        { selectId: 'res_province', hiddenId: 'res_province_hidden' },
        { selectId: 'res_city',     hiddenId: 'res_city_hidden' },
        { selectId: 'res_brgy',     hiddenId: 'res_brgy_hidden' },
        { selectId: 'per_province', hiddenId: 'per_province_hidden' },
        { selectId: 'per_city',     hiddenId: 'per_city_hidden' },
        { selectId: 'per_brgy',     hiddenId: 'per_brgy_hidden' },
    ];

    addressSelectPairs.forEach(({ selectId, hiddenId }) => {
        const sel = document.getElementById(selectId);
        const hid = document.getElementById(hiddenId);
        if (!sel || !hid) return;

        // Keep hidden input in sync whenever the select changes.
        sel.addEventListener('change', () => {
            if (sel.value) hid.value = sel.value;
        });
    });

    // Before the form submits, ensure every address select has a value.
    // If the select is empty but the hidden fallback has a value, inject it as a selected option.
    const myForm = document.getElementById('myForm');
    if (myForm) {
        myForm.addEventListener('submit', function () {
            addressSelectPairs.forEach(({ selectId, hiddenId }) => {
                const sel = document.getElementById(selectId);
                const hid = document.getElementById(hiddenId);
                if (!sel || !hid) return;
                const fallback = (hid.value || '').trim();
                if (!sel.value && fallback) {
                    // Check if this option already exists to avoid duplicates.
                    const existing = Array.from(sel.options).find(o => o.value === fallback);
                    if (!existing) {
                        const opt = document.createElement('option');
                        opt.value = fallback;
                        opt.textContent = fallback;
                        sel.appendChild(opt);
                    }
                    sel.value = fallback;
                }
            });
        }, true); // capture phase so it runs before validation
    }
    document.querySelector('#copy_res_to_per').addEventListener('click', async () => {
        document.querySelector('#per_house_no').value = document.querySelector('#res_house_no').value;
        document.querySelector('#per_street').value = document.querySelector('#res_street').value;
        document.querySelector('#per_sub_vil').value = document.querySelector('#res_sub_vil').value;
        ['per_house_no', 'per_street', 'per_sub_vil'].forEach(id => {
            const input = document.getElementById(id);
            if (input) input.dispatchEvent(new Event('change'));
        });

        const resProvinceCode = getSelectedCode(resProvince);
        const resCityCode = getSelectedCode(resCity);
        const resBrgyCode = getSelectedCode(resBrgy);

        if (resProvinceCode && setSelectByCode(perProvince, resProvinceCode)) {
            perProvince.dispatchEvent(new Event('change'));
        } else {
            perProvince.value = resProvince.value;
            perProvince.dispatchEvent(new Event('change'));
        }

        if (resCityCode) {
            const hasCity = await waitForSelectOptionCode(perCity, resCityCode);
            if (hasCity && setSelectByCode(perCity, resCityCode)) {
                perCity.dispatchEvent(new Event('change'));
            } else {
                perCity.value = resCity.value;
                perCity.dispatchEvent(new Event('change'));
            }
        }

        if (resBrgyCode) {
            const hasBrgy = await waitForSelectOptionCode(perBrgy, resBrgyCode);
            if (hasBrgy && setSelectByCode(perBrgy, resBrgyCode)) {
                perBrgy.dispatchEvent(new Event('change'));
            } else {
                perBrgy.value = resBrgy.value;
                perBrgy.dispatchEvent(new Event('change'));
            }
        }

        const resZip = document.querySelector('#res_zipcode');
        const perZip = document.querySelector('#per_zipcode');
        if (resZip && perZip) {
            perZip.value = resZip.value;
            perZip.dispatchEvent(new Event('change'));
        }
    });
    document.addEventListener('DOMContentLoaded', function () {
        function val(id) {
            const el = document.getElementById(id);
            return el ? (el.value || el.textContent || '') : '';
        }
        function radioVal(name){ const el=document.querySelector('input[name="'+name+'"]:checked'); return el?el.value:''; }
        const yearLevelPairs = [
            ['elem_year_graduated', 'elem_earned'],
            ['jhs_year_graduated', 'jhs_earned'],
        ];
        function isEmptyOrNa(value) {
            const normalized = String(value || '').trim().toLowerCase().replace(/\s+/g, '');
            return normalized === '' || normalized === 'n/a' || normalized === 'na' || normalized === 'n\\a';
        }
        function syncLevelRequiredByYear(yearId, levelId) {
            const year = document.getElementById(yearId);
            const level = document.getElementById(levelId);
            if (!year || !level) return;

            level.required = isEmptyOrNa(year.value);
            if (!level.required) {
                level.setCustomValidity('');
            }
        }
        function requireYearOrLevel(yearId, levelId) {
            const year = document.getElementById(yearId);
            const level = document.getElementById(levelId);
            if (!year || !level) return true;

            [year, level].forEach((el) => el.setCustomValidity(''));

            const hasYear = !isEmptyOrNa(year.value);
            const hasLevel = String(level.value || '').trim().length > 0;

            if (!hasYear && !hasLevel) {
                const msg = 'Provide Year Graduated or Highest Level/Units Earned.';
                year.setCustomValidity(msg);
                level.setCustomValidity(msg);
                return false;
            }

            return true;
        }
        function requiredValid(){
            const form=document.getElementById('myForm'); if(!form) return false;
            yearLevelPairs.forEach(([yearId, levelId]) => syncLevelRequiredByYear(yearId, levelId));
            const els=form.querySelectorAll('[required]');
            const radios=new Set(); let ok=true;
            els.forEach(el=>{
                if(el.type==='radio'){ radios.add(el.name); }
                else if(el.tagName==='SELECT'){ if(!el.value) ok=false; }
                else { if(!el.checkValidity() || !String(el.value).trim()) ok=false; }
            });
            radios.forEach(n=>{ if(!document.querySelector('input[name="'+n+'"]:checked')) ok=false; });
            const c=radioVal('citizenship');
            if(c==='Dual Citizenship'){
                if(!radioVal('dual_type')) ok=false;
                const dc=document.getElementById('dual_country'); if(!dc || !dc.value.trim()) ok=false;
            }

            const elemOk = requireYearOrLevel('elem_year_graduated', 'elem_earned');
            const jhsOk = requireYearOrLevel('jhs_year_graduated', 'jhs_earned');
            
            // Check if we're in simple mode (preview should be available with basic info)
            const urlParams = new URLSearchParams(window.location.search);
            const isSimpleMode = urlParams.get('simple') === '1';
            
            // In simple mode, only require basic personal info fields
            if (isSimpleMode) {
                const basicFields = ['surname', 'first_name', 'date_of_birth', 'sex', 'civil_status'];
                let basicOk = true;
                
                basicFields.forEach(fieldName => {
                    const field = document.querySelector(`[name="${fieldName}"]`);
                    if (field) {
                        if (field.type === 'radio') {
                            if (!document.querySelector(`input[name="${fieldName}"]:checked`)) {
                                basicOk = false;
                            }
                        } else if (!field.value || !field.value.trim()) {
                            basicOk = false;
                        }
                    }
                });
                
                return basicOk && elemOk && jhsOk;
            }
            
            return ok && elemOk && jhsOk;
        }
        function updatePreviewBtn(){
            const btn=document.getElementById('pdsPreviewBtn'); if(!btn) return;
            const ok=requiredValid();
            btn.disabled=!ok;
            if(ok){
                btn.classList.remove('bg-gray-400','cursor-not-allowed','opacity-60');
                btn.classList.add('bg-blue-600','hover:bg-blue-700');
            }else{
                btn.classList.add('bg-gray-400','cursor-not-allowed','opacity-60');
                btn.classList.remove('bg-blue-600','hover:bg-blue-700');
            }
        }
        const form=document.getElementById('myForm');
        if(form){
            form.addEventListener('input', updatePreviewBtn);
            form.addEventListener('change', updatePreviewBtn);
        }
        yearLevelPairs.forEach(([yearId, levelId]) => {
            const year = document.getElementById(yearId);
            const level = document.getElementById(levelId);
            if (!year || !level) return;

            const syncPair = () => {
                syncLevelRequiredByYear(yearId, levelId);
                requireYearOrLevel(yearId, levelId);
            };

            ['input', 'change', 'blur'].forEach((eventName) => {
                year.addEventListener(eventName, syncPair);
                level.addEventListener(eventName, () => requireYearOrLevel(yearId, levelId));
            });

            syncPair();
        });
        const copyBtn=document.getElementById('copy_res_to_per');
        if(copyBtn){ copyBtn.addEventListener('click', function(){ setTimeout(updatePreviewBtn, 100); }); }
        function radio(name) {
            const el = document.querySelector('input[name="'+name+'"]:checked');
            return el ? el.value : '';
        }
        function set(id, text) {
            const el = document.getElementById(id);
            if (el) el.textContent = text || '';
        }
        function buildAddress(prefix) {
            const house = val(prefix + '_house_no');
            const street = val(prefix + '_street');
            const sub = val(prefix + '_sub_vil');
            const brgy = val(prefix + '_brgy');
            const city = val(prefix + '_city');
            const prov = val(prefix + '_province');
            const zip = val(prefix + '_zipcode');
            return 'House/Block/Lot No.: ' + house + ' â€¢ Street: ' + street + ' â€¢ Subdivision/Village: ' + sub + ' â€¢ Barangay: ' + brgy + ' â€¢ City/Municipality: ' + city + ' â€¢ Province: ' + prov + ' â€¢ ZIP Code: ' + zip;
        }
        function populatePreview() {
            set('preview_surname', val('surname'));
            set('preview_name_extension', val('name_extension'));
            set('preview_first_name', val('first_name'));
            set('preview_middle_name', val('middle_name'));
            set('preview_date_of_birth', val('date_of_birth'));
            set('preview_citizenship', radio('citizenship'));
            const dualType = radio('dual_type');
            const dualCountry = val('dual_country');
            set('preview_dual_type', dualType);
            set('preview_dual_country', dualCountry);
            set('preview_place_of_birth', val('place_of_birth'));
            const sex = radio('sex');
            document.getElementById('preview_sex_male_dot')?.classList.toggle('checked', sex === 'male');
            document.getElementById('preview_sex_female_dot')?.classList.toggle('checked', sex === 'female');
            const cit = radio('citizenship');
            document.getElementById('preview_cit_fil')?.classList.toggle('checked', cit === 'Filipino');
            document.getElementById('preview_cit_dual')?.classList.toggle('checked', cit === 'Dual Citizenship');
            document.getElementById('preview_dual_birth_dot')?.classList.toggle('checked', radio('dual_type') === 'By Birth');
            document.getElementById('preview_dual_nat_dot')?.classList.toggle('checked', radio('dual_type') === 'By Naturalization');
            set('preview_res_house', val('res_house_no'));
            set('preview_res_street', val('res_street'));
            set('preview_res_sub', val('res_sub_vil'));
            set('preview_res_brgy', val('res_brgy'));
            set('preview_res_city', val('res_city'));
            set('preview_res_province', val('res_province'));
            set('preview_res_zip', val('res_zipcode'));
            set('preview_civil_status', val('civil_status'));
            set('preview_per_house', val('per_house_no'));
            set('preview_per_street', val('per_street'));
            set('preview_per_sub', val('per_sub_vil'));
            set('preview_per_brgy', val('per_brgy'));
            set('preview_per_city', val('per_city'));
            set('preview_per_province', val('per_province'));
            set('preview_per_zip', val('per_zipcode'));
            set('preview_height', val('height'));
            set('preview_telephone', val('telephone_no'));
            set('preview_weight', val('weight'));
            set('preview_mobile', val('mobile_no'));
            set('preview_blood_type', val('blood_type'));
            set('preview_email', val('email_address'));
            set('preview_gsis', val('gsis_id_no'));
            set('preview_tin', val('tin_no'));
            set('preview_pagibig', val('pagibig_id_no'));
            set('preview_agency', val('agency_employee_no'));
            set('preview_philhealth', val('philhealth_no'));
            set('preview_sss', val('sss_id_no'));
            set('preview_spouse_surname', val('spouse_surname'));
            set('preview_spouse_name_extension', val('spouse_name_extension'));
            set('preview_spouse_first_name', val('spouse_first_name'));
            set('preview_spouse_middle_name', val('spouse_middle_name'));
            set('preview_spouse_occupation', val('spouse_occupation'));
            set('preview_spouse_employer', val('spouse_employer'));
            set('preview_spouse_business_address', val('spouse_business_address'));
            set('preview_spouse_telephone', val('spouse_telephone'));
            set('preview_father_surname', val('father_surname'));
            set('preview_father_name_extension', val('father_name_extension'));
            set('preview_father_first_name', val('father_first_name'));
            set('preview_father_middle_name', val('father_middle_name'));
            set('preview_mother_maiden_surname', val('mother_maiden_surname'));
            set('preview_mother_maiden_first_name', val('mother_maiden_first_name'));
            set('preview_mother_maiden_middle_name', val('mother_maiden_middle_name'));
            set('preview_elem_school', val('elem_school'));
            set('preview_elem_basic', val('elem_basic'));
            set('preview_elem_period', (val('elem_from') || '') + (val('elem_to') ? ' - ' + val('elem_to') : ''));
            set('preview_elem_year', val('elem_year_graduated'));
            set('preview_elem_honors', val('elem_academic_honors'));
            set('preview_jhs_school', val('jhs_school'));
            set('preview_jhs_basic', val('jhs_basic'));
            set('preview_jhs_period', (val('jhs_from') || '') + (val('jhs_to') ? ' - ' + val('jhs_to') : ''));
            set('preview_jhs_year', val('jhs_year_graduated'));
            set('preview_jhs_honors', val('jhs_academic_honors'));
            const childrenNames = Array.from(document.querySelectorAll('input[name^="children"][name$="[name]"]'));
            const childrenDobs  = Array.from(document.querySelectorAll('input[name^="children"][name$="[dob]"]'));
            const rows = [];
            const size = Math.max(childrenNames.length, childrenDobs.length);
            for (let i = 0; i < size; i++) {
                const name = childrenNames[i]?.value || '';
                const dob  = childrenDobs[i]?.value || '';
                if (name || dob) {
                    rows.push(
                        '<div style="display:flex; gap:12px; margin:4px 0;">' +
                        '<span class="underline" style="min-width:220px;">' + (name) + '</span>' +
                        '<span class="muted">DOB:</span>' +
                        '<span class="underline" style="min-width:140px;">' + (dob) + '</span>' +
                        '</div>'
                    );
                }
            }
            const holder = document.getElementById('preview_children');
            if (holder) holder.innerHTML = rows.join('') || '';
        }
        const openBtn = document.getElementById('pdsPreviewBtn');
        if (openBtn) {
            openBtn.addEventListener('click', async function () {
                if (openBtn.disabled) return;
                
                // Show a small indication that we are preparing the preview
                const originalText = openBtn.innerHTML;
                openBtn.innerHTML = '<span class="material-icons text-sm animate-spin mr-1">autorenew</span>Preparing...';
                openBtn.disabled = true;

                try {
                    // Force an immediate autosave so the preview reflects the latest changes
                    if (window.__pdsAutosaveNow) {
                        await window.__pdsAutosaveNow({ force: true, maxWaitMs: 5000 });
                    }

                    populatePreview();
                    const frame = document.getElementById('pdsPdfPreviewFrame');
                    if (frame) {
                        const previewSrc = frame.dataset.previewSrc || '/export-pds?preview=1';
                        const separator = previewSrc.includes('?') ? '&' : '?';
                        frame.src = previewSrc + separator + 'ts=' + Date.now();
                    }
                    const overlay = document.getElementById('pdsPreviewOverlay');
                    if (overlay) overlay.classList.remove('hidden');
                } finally {
                    openBtn.innerHTML = originalText;
                    openBtn.disabled = false;
                }
            });
        }
        document.addEventListener('click', function (e) {
            const closeEl = (e.target && e.target.id === 'pdsPreviewClose') ? e.target : (e.target && e.target.closest && e.target.closest('#pdsPreviewClose'));
            if (closeEl) {
                const overlay = document.getElementById('pdsPreviewOverlay');
                if (overlay) overlay.classList.add('hidden');
            }
        });
        updatePreviewBtn();
    });
</script>
<script>
    (function () {
        const form = document.getElementById('myForm');
        if (!form) return;

        const autosaveUrl = @json(route('pds.autosave', ['section' => 'c1']));
        const AUTOSAVE_INTERVAL_MS = 30000;
        let isDirty = false;
        let isSubmitting = false;
        let inFlight = false;
        let queued = false;

        const markDirty = () => { isDirty = true; };
        form.addEventListener('input', markDirty);
        form.addEventListener('change', markDirty);
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
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
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

        async function flushDraftBeforeExport() {
            await flushDraftNow({ force: true, maxWaitMs: 10000 });
        }

        window.__pdsAutosaveNow = flushDraftNow;

        setInterval(() => saveDraft(false), AUTOSAVE_INTERVAL_MS);

        document.addEventListener('visibilitychange', () => {
            if (document.hidden && isDirty) {
                saveDraft(true);
            }
        });

        window.addEventListener('beforeunload', () => {
            if (!isDirty || isSubmitting || !navigator.sendBeacon) return;
            const formData = new FormData(form);
            navigator.sendBeacon(autosaveUrl, formData);
        });

        const exportBtn = document.getElementById('exportAnnexH1Btn');
        if (exportBtn) {
            exportBtn.addEventListener('click', async (event) => {
                event.preventDefault();
                const targetUrl = exportBtn.getAttribute('href');
                if (!targetUrl) return;

                exportBtn.classList.add('opacity-60', 'cursor-not-allowed');
                exportBtn.setAttribute('aria-disabled', 'true');

                try {
                    await flushDraftBeforeExport();
                } finally {
                    window.location.href = targetUrl;
                }
            });
        }
    })();
</script>
<script>
    (function () {
        const importBtn = document.getElementById('importPdsExcelBtn');
        const fileInput = document.getElementById('pdsExcelFileInput');
        const form = document.getElementById('myForm');
        if (!importBtn || !fileInput || !form) return;

        const importUrl = @json(route('pds.import_c1_excel'));

        const setButtonLoading = (loading) => {
            importBtn.disabled = loading;
            importBtn.classList.toggle('opacity-60', loading);
            importBtn.classList.toggle('cursor-not-allowed', loading);
            importBtn.innerHTML = loading
                ? '<span class="material-icons text-lg sm:text-xl animate-spin">autorenew</span>Importing...'
                : '<span class="material-icons text-lg sm:text-xl">upload_file</span>Import from Excel';
        };

        const notify = (message, type = 'error') => {
            if (typeof showNotification === 'function') {
                showNotification(message, type);
                return;
            }
            showAppToast(message);
        };

        const dispatchInputEvents = (element) => {
            if (!element) return;
            element.dispatchEvent(new Event('input', { bubbles: true }));
            element.dispatchEvent(new Event('change', { bubbles: true }));
        };

        const setByName = (name, value) => {
            const field = form.querySelector(`[name="${name}"]`);
            if (!field) return;
            field.value = value ?? '';
            dispatchInputEvents(field);
        };

        const setRadio = (name, value) => {
            const radios = form.querySelectorAll(`input[name="${name}"]`);
            let hasMatch = false;
            radios.forEach((radio) => {
                const shouldCheck = value && String(radio.value).toLowerCase() === String(value).toLowerCase();
                radio.checked = shouldCheck;
                if (radio.checked) {
                    hasMatch = true;
                    dispatchInputEvents(radio);
                }
            });
            if (!hasMatch && radios.length > 0) {
                dispatchInputEvents(radios[0]);
            }
        };

        const waitFor = async (check, timeoutMs = 8000, intervalMs = 120) => {
            const started = Date.now();
            while (Date.now() - started < timeoutMs) {
                if (check()) return true;
                await new Promise((resolve) => setTimeout(resolve, intervalMs));
            }
            return false;
        };

        const normalizePlaceText = (value) => {
            return String(value || '')
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/\bcity of\b/g, 'city')
                .replace(/\bmunicipality of\b/g, 'municipality')
                .replace(/\bmun\.?\b/g, 'municipality')
                .replace(/\bbrgy\.?\b/g, 'barangay')
                .replace(/[^a-z0-9]/g, '');
        };

        const findBestSelectOption = (select, targetValue) => {
            const raw = String(targetValue || '').trim();
            if (!raw) return null;

            const options = Array.from(select.options).filter((opt) => opt.value && String(opt.value).trim() !== '');
            if (!options.length) return null;

            const exact = options.find((opt) => String(opt.value).trim().toLowerCase() === raw.toLowerCase());
            if (exact) return exact;

            const normalizedTarget = normalizePlaceText(raw);
            const normalizedExact = options.find((opt) => normalizePlaceText(opt.value) === normalizedTarget);
            if (normalizedExact) return normalizedExact;

            let best = null;
            let bestScore = 0;
            options.forEach((opt) => {
                const nv = normalizePlaceText(opt.value);
                if (!nv || !normalizedTarget) return;

                let score = 0;
                if (nv.includes(normalizedTarget) || normalizedTarget.includes(nv)) {
                    score = Math.min(nv.length, normalizedTarget.length);
                } else {
                    const targetTokens = normalizedTarget.match(/[a-z0-9]+/g) || [];
                    const valueTokens = nv.match(/[a-z0-9]+/g) || [];
                    const tokenHits = targetTokens.filter((t) => valueTokens.includes(t)).length;
                    score = tokenHits;
                }

                if (score > bestScore) {
                    bestScore = score;
                    best = opt;
                }
            });

            return bestScore > 0 ? best : null;
        };

        const waitAndSetSelect = async (id, value) => {
            if (!value) return;
            const select = document.getElementById(id);
            if (!select) return;
            const ok = await waitFor(() => select.options.length > 1);
            if (!ok) return;
            const matched = findBestSelectOption(select, value);
            if (!matched) return;
            select.value = matched.value;
            dispatchInputEvents(select);
        };

        const applyAddress = async (prefix, values) => {
            setByName(`${prefix}_house_no`, values.house_no || '');
            setByName(`${prefix}_street`, values.street || '');
            setByName(`${prefix}_sub_vil`, values.sub_vil || '');

            await waitAndSetSelect(`${prefix}_province`, values.province || '');
            await waitAndSetSelect(`${prefix}_city`, values.city || '');
            await waitAndSetSelect(`${prefix}_brgy`, values.brgy || '');
            setByName(`${prefix}_zipcode`, values.zipcode || '');
        };

        const getAddChildButton = () => {
            return Array.from(document.querySelectorAll('button')).find((btn) => btn.textContent.includes('Add Another Child')) || null;
        };

        const ensureChildRows = async (targetCount) => {
            if (targetCount <= 0) return;
            const addBtn = getAddChildButton();
            if (!addBtn) return;

            const countRows = () => form.querySelectorAll('input[name^="children"][name$="[name]"]').length;
            while (countRows() < targetCount) {
                addBtn.click();
                await waitFor(() => countRows() >= targetCount, 3000, 100);
            }
        };

        const normalizeDateInputValue = (value) => {
            const raw = typeof value === 'string' ? value.trim() : '';
            if (!raw) return '';

            if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) {
                return raw;
            }

            const ddMmYyyyDash = raw.match(/^(\d{2})-(\d{2})-(\d{4})$/);
            if (ddMmYyyyDash) {
                return `${ddMmYyyyDash[3]}-${ddMmYyyyDash[2]}-${ddMmYyyyDash[1]}`;
            }

            const ddMmYyyySlash = raw.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
            if (ddMmYyyySlash) {
                return `${ddMmYyyySlash[3]}-${ddMmYyyySlash[2]}-${ddMmYyyySlash[1]}`;
            }

            return '';
        };

        const fillChildren = async (children) => {
            if (!Array.isArray(children)) return;
            await ensureChildRows(children.length);

            const nameInputs = Array.from(form.querySelectorAll('input[name^="children"][name$="[name]"]'));
            const dobInputs = Array.from(form.querySelectorAll('input[name^="children"][name$="[dob]"]'));

            nameInputs.forEach((input) => {
                input.value = '';
                dispatchInputEvents(input);
            });
            dobInputs.forEach((input) => {
                input.value = '';
                dispatchInputEvents(input);
            });

            children.forEach((child, index) => {
                if (nameInputs[index]) {
                    nameInputs[index].value = child?.name || '';
                    dispatchInputEvents(nameInputs[index]);
                }
                if (dobInputs[index]) {
                    dobInputs[index].value = normalizeDateInputValue(child?.dob || '');
                    dispatchInputEvents(dobInputs[index]);
                }
            });
        };

        const fillEducationRow = (type, rowData) => {
            const row = Array.isArray(rowData) ? rowData[0] : null;
            const values = row || { from: '', to: '', school: '', basic: '', earned: '', year_graduated: '', academic_honors: '' };
            setByName(`${type}[0][from]`, values.from || '');
            setByName(`${type}[0][to]`, values.to || '');
            setByName(`${type}[0][school]`, values.school || '');
            setByName(`${type}[0][basic]`, values.basic || '');
            setByName(`${type}[0][earned]`, values.earned || '');
            setByName(`${type}[0][year_graduated]`, values.year_graduated || '');
            setByName(`${type}[0][academic_honors]`, values.academic_honors || '');
        };

        const applyPayload = async (payload) => {
            const fields = payload?.fields || {};
            Object.entries(fields).forEach(([name, value]) => {
                if (['sex', 'civil_status', 'citizenship', 'dual_type'].includes(name)) return;
                setByName(name, value);
            });

            setRadio('sex', fields.sex || '');
            setByName('civil_status', fields.civil_status || '');
            setRadio('citizenship', fields.citizenship || '');
            setRadio('dual_type', fields.dual_type || '');

            await applyAddress('res', {
                house_no: fields.res_house_no,
                street: fields.res_street,
                sub_vil: fields.res_sub_vil,
                brgy: fields.res_brgy,
                city: fields.res_city,
                province: fields.res_province,
                zipcode: fields.res_zipcode,
            });
            await applyAddress('per', {
                house_no: fields.per_house_no,
                street: fields.per_street,
                sub_vil: fields.per_sub_vil,
                brgy: fields.per_brgy,
                city: fields.per_city,
                province: fields.per_province,
                zipcode: fields.per_zipcode,
            });

            await fillChildren(payload.children || []);
            fillEducationRow('vocational', payload.vocational || []);
            fillEducationRow('college', payload.college || []);
            fillEducationRow('grad', payload.grad || []);
        };

        importBtn.addEventListener('click', () => fileInput.click());

        fileInput.addEventListener('change', async () => {
            const file = fileInput.files?.[0];
            if (!file) return;

            const isExcel = /\.(xlsx|xls)$/i.test(file.name);
            if (!isExcel) {
                notify('Please select a valid Excel file (.xlsx or .xls).', 'error');
                fileInput.value = '';
                return;
            }

            const formData = new FormData();
            formData.append('pds_excel', file);

            const csrf = form.querySelector('input[name="_token"]')?.value || '';
            setButtonLoading(true);

            try {
                const response = await fetch(importUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const result = await response.json().catch(() => ({}));
                if (!response.ok) {
                    notify(result.message || 'Import failed. Please verify the file and try again.', 'error');
                    return;
                }

                await applyPayload(result.data || {});
                notify(result.message || 'Excel imported successfully.', 'success');

                if (Array.isArray(result.warnings)) {
                    result.warnings.forEach((warning) => notify(warning, 'warning'));
                }

                if (result.missing_report && result.missing_report.missing_in_excel_template) {
                    notify('Import coverage report is available. Some sections/fields (e.g., WES) are not in the Annex H-1 Excel template.', 'warning');
                    console.info('PDS Excel import coverage report:', result.missing_report);
                }
            } catch (error) {
                notify('Import failed due to a network or server error. Please try again.', 'error');
            } finally {
                setButtonLoading(false);
                fileInput.value = '';
            }
        });
    })();
</script>
<div id="pdsPreviewOverlay" class="hidden fixed inset-0 z-[100] bg-black bg-opacity-50 p-4 sm:p-8 flex items-center justify-center">
    <div class="bg-white w-full max-w-6xl max-h-[90vh] overflow-auto rounded-xl shadow-2xl">
        <div class="flex items-center justify-between px-4 sm:px-6 py-3 border-b">
            <h3 class="text-base sm:text-lg font-semibold text-gray-900">Personal Data Sheet Preview</h3>
            <button id="pdsPreviewClose" class="p-2 rounded hover:bg-gray-100">
                <span class="material-icons">close</span>
            </button>
        </div>
        <div class="p-4 sm:p-6">
            <div class="mb-3 text-xs text-gray-500">Preview is rendered from the PDF template and auto-filled from your saved PDS data.</div>
            <div class="w-full h-[75vh] border border-gray-200 rounded-lg overflow-hidden bg-gray-50">
                <iframe
                    id="pdsPdfPreviewFrame"
                    title="PDS PDF Preview"
                    src="about:blank"
                    data-preview-src="/export-pds?preview=1&force_fpdi=1"
                    class="w-full h-full"
                ></iframe>
            </div>
        </div>
    </div>
</div>
@include('partials.loader')
@endsection
