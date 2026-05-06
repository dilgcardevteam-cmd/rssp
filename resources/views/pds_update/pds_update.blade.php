<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Data Sheet - CS Form 212</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
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

        /* Custom focus styles */
        .custom-focus:focus {
            outline: none;
            box-shadow: 0 0 0 2px #ffffff, 0 0 0 4px #3b82f6;
            border-color: #3b82f6;
        }

        /* Floating label styles */
        .floating-label {
            transition: all 0.2s ease-out;
        }

        .floating-label-input:focus + .floating-label,
        .floating-label-input:not(:placeholder-shown) + .floating-label {
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

        /* Progress indicator */
        .progress-step {
            transition: all 0.3s ease;
        }

        .progress-step.active {
            background-color: #3b82f6;
            color: white;
        }

        .progress-step.completed {
            background-color: #10b981;
            color: white;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-indigo-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-lg sticky top-0 z-50 glass-effect">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <span class="material-icons text-blue-600 mr-3">article</span>
                    <h1 class="text-xl font-bold text-gray-900">Personal Data Sheet</h1>
                </div>
                <div class="hidden md:flex items-center space-x-4">
                    <span class="text-sm text-gray-500">CS Form No. 212 (Revised 2017)</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Progress Indicator -->
    <div class="bg-white shadow-sm sticky top-16 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2 overflow-x-auto">
                    <div class="progress-step active flex items-center px-4 py-2 rounded-full text-sm font-medium">
                        <span class="material-icons text-sm mr-1">person</span>
                        Personal Info
                    </div>
                    <span class="text-gray-300">→</span>
                    <div class="progress-step flex items-center px-4 py-2 rounded-full text-sm font-medium bg-gray-100 text-gray-500">
                        <span class="material-icons text-sm mr-1">work</span>
                        Work Experience
                    </div>
                    <span class="text-gray-300">→</span>
                    <div class="progress-step flex items-center px-4 py-2 rounded-full text-sm font-medium bg-gray-100 text-gray-500">
                        <span class="material-icons text-sm mr-1">school</span>
                        Learning & Development
                    </div>
                    <span class="text-gray-300">→</span>
                    <div class="progress-step flex items-center px-4 py-2 rounded-full text-sm font-medium bg-gray-100 text-gray-500">
                        <span class="material-icons text-sm mr-1">info</span>
                        Other Information
                    </div>
                    <span class="text-gray-300">→</span>
                    <div class="progress-step flex items-center px-4 py-2 rounded-full text-sm font-medium bg-gray-100 text-gray-500">
                        <span class="material-icons text-sm mr-1">info</span>
                        Upload PDF
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <span class="material-icons text-red-500">error</span>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700 font-bold">
                            Please fix the following errors:
                        </p>
                        <ul class="mt-2 list-disc list-inside text-sm text-red-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif
        <form class="space-y-8" method="POST" action="{{ route('submit_c1', ['go_to' => 'c2_update']) }}">
            @csrf
            <!-- Personal Information Section -->
            <section class="bg-white rounded-2xl shadow-xl p-8 animate-slide-in">
                <div class="flex items-center mb-6">
                    <span class="material-icons text-blue-600 mr-3 text-3xl">badge</span>
                    <h2 class="text-2xl font-bold text-gray-900">Personal Information</h2>
                </div>

                <p class="text-gray-600 mb-6 text-sm">
                    Print legibly. Tick appropriate boxes and use separate sheet if necessary. Indicate N/A if not applicable. DO NOT ABBREVIATE.
                </p>

                <!-- CS ID Number -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="relative">
                        <input type="text" id="cs_id_no" name="cs_id_no" value="{{ old('cs_id_no', session('form.cs_id_no')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                        <label for="cs_id_no" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">CS ID No.</label>
                    </div>
                </div>

                <!-- Name Fields -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="relative">
                        <input type="text" id="surname" name="surname" value="{{ old('surname', session('form.surname')) }}" placeholder=" " required class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                        <label for="surname" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Surname *</label>
                    </div>
                    <div class="relative">
                        <input type="text" id="first_name" name="first_name" value="{{ old('first_name', session('form.first_name')) }}" placeholder=" " required class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                        <label for="first_name" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">First Name *</label>
                    </div>
                    <div class="relative">
                        <input type="text" id="middle_name" name="middle_name" value="{{ old('middle_name', session('form.middle_name')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                        <label for="middle_name" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Middle Name</label>
                    </div>
                    <div class="relative">
                        <input type="text" id="name_extension" name="name_extension" value="{{ old('name_extension', session('form.name_extension')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                        <label for="name_extension" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Name Ext.</label>
                    </div>
                </div>

                <!-- Personal Details -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sex</label>
                        <div class="flex space-x-6">
                            <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                                <input type="radio" name="sex" value="male" {{ old('sex', session('form.sex')) == 'male' ? 'checked' : '' }} class="mr-2 text-blue-600 focus:ring-blue-500">
                                <span>Male</span>
                            </label>
                            <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                                <input type="radio" name="sex" value="female" {{ old('sex', session('form.sex')) == 'male' ? 'checked' : '' }} class="mr-2 text-blue-600 focus:ring-blue-500">
                                <span>Female</span>
                            </label>
                        </div>
                    </div>

                    <div class="relative">
                        <select id="civil_status" name="civil_status" value="{{ old('civil_status', session('form.civil_status')) }}" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all appearance-none bg-white">
                            <option value="" disabled selected>Select Civil Status</option>
                            <option value="single">Single</option>
                            <option value="married">Married</option>
                            <option value="widowed">Widowed</option>
                            <option value="separated">Separated</option>
                            <option value="other">Other/s</option>
                        </select>
                        <label class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">Civil Status *</label>
                    </div>

                    <div class="relative">
                        <input type="text" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', session('form.date_of_birth')) }}" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                        <label for="date_of_birth" class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">Date of Birth *</label>
                    </div>
                </div>

                </div>
                <!-- Physical Info -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-6 mb-6">
                    <div class="relative">
                        <input type="number" step="0.01" id="height" name="height" value="{{ old('height', session('form.height')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                        <label for="height" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Height (cm)</label>
                    </div>
                    <div class="relative">
                        <input type="number" step="0.1" id="weight" name="weight" value="{{ old('weight', session('form.weight')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                        <label for="weight" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Weight (kg)</label>
                    </div>
                    <div class="relative">
                        @php
                            $blood = old('blood_type', session('form.blood_type'));
                            $validBlood = ['A+','A-','B+','B-','AB+','AB-','O+','O-'];
                        @endphp
                        <select id="blood_type" name="blood_type" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all appearance-none bg-white" required>
                            <option value="" disabled {{ $blood == '' ? 'selected' : '' }}>Select Blood Type</option>
                            @foreach($validBlood as $bt)
                                <option value="{{ $bt }}" {{ $blood === $bt ? 'selected' : '' }}>{{ $bt }}</option>
                            @endforeach
                            @if($blood && !in_array($blood, $validBlood))
                                <option value="{{ $blood }}" selected>{{ $blood }}</option>
                            @endif
                        </select>
                        <label for="blood_type" class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">Blood Type</label>
                    </div>
                </div>

                <!-- ID Numbers -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="relative">
                        <input type="text" id="philhealth_no" name="philhealth_no" value="{{ old('philhealth_no', session('form.philhealth_no')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                        <label for="philhealth_no" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">PhilHealth No.</label>
                    </div>
                    <div class="relative">
                        <input type="text" id="tin_no" name="tin_no" value="{{ old('tin_no', session('form.tin_no')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                        <label for="tin_no" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">TIN No.</label>
                    </div>
                    <div class="relative">
                        <input type="text" id="agency_employee_no" name="agency_employee_no" value="{{ old('agency_employee_no', session('form.agency_employee_no')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                        <label for="agency_employee_no" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Agency Employee No.</label>
                    </div>
                    <div class="relative">
                        <input type="text" id="agency_employee_no" name="agency_employee_no" value="{{ old('agency_employee_no', session('form.agency_employee_no')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                        <label for="agency_employee_no" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">GSIS ID N.</label>
                    </div>
                    <div class="relative">
                        <input type="text" id="agency_employee_no" name="agency_employee_no" value="{{ old('agency_employee_no', session('form.agency_employee_no')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                        <label for="agency_employee_no" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none"> PAGIBIG ID No.</label>
                    </div>
                    <div class="relative">
                        <input type="text" id="agency_employee_no" name="agency_employee_no" value="{{ old('agency_employee_no', session('form.agency_employee_no')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                        <label for="agency_employee_no" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">SSS ID No.</label>
                    </div>
                </div>
                
                <!-- Additional Personal Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="relative">
                        <input type="text" id="place_of_birth" name="place_of_birth" value="{{ old('place_of_birth', session('form.place_of_birth')) }}" placeholder=" " required class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                        <label for="place_of_birth" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Place of Birth *</label>
                    </div>
                    <div x-data="{ citizenship: '{{ old('citizenship', session('form.citizenship')) }}', dualType: '{{ old('dual_type', session('form.dual_type')) }}' }" class="space-y-4">
    <label class="block text-gray-700 font-medium mb-2">Citizenship *</label>

    <!-- Primary citizenship options -->
    <div class="flex flex-row gap-2">
        <label class="inline-flex items-center">
            <input type="radio" name="citizenship" value="Filipino" x-model="citizenship"
                   class="text-blue-600 border-gray-300 focus:ring-blue-500">
            <span class="ml-2 text-gray-700">Filipino</span>
        </label>

        <label class="inline-flex items-center">
            <input type="radio" name="citizenship" value="Dual Citizenship" x-model="citizenship"
                   class="text-blue-600 border-gray-300 focus:ring-blue-500">
            <span class="ml-2 text-gray-700">Dual Citizenship</span>
        </label>
    </div>

    <!-- Show only when Dual Citizenship is selected -->
    <div x-show="citizenship === 'Dual Citizenship'" class="space-y-4 mt-4">

        <!-- Sub-options -->
        <label class="block text-gray-700 font-medium mb-2">Type of Dual Citizenship</label>
        <div class="flex flex-row gap-2">
            <label class="inline-flex items-center">
                <input type="radio" name="dual_type" value="By Birth" x-model="dualType"
                       class="text-blue-600 border-gray-300 focus:ring-blue-500">
                <span class="ml-2 text-gray-700">By Birth</span>
            </label>
            <label class="inline-flex items-center">
                <input type="radio" name="dual_type" value="By Naturalization" x-model="dualType"
                       class="text-blue-600 border-gray-300 focus:ring-blue-500">
                <span class="ml-2 text-gray-700">By Naturalization</span>
            </label>
        </div>

        <!-- Input for specifying country -->
        <div>
            <label for="dual_country" class="block text-gray-500 text-sm mb-1">Specify Country</label>
            <input type="text" id="dual_country" name="dual_country"
                   value="{{ old('dual_country', session('form.dual_country')) }}"
                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                   placeholder="Enter country of second citizenship">
        </div>
    </div>
</div>
                </div>
            </section>

            <!-- Contact Information Section -->
            <section class="bg-white rounded-2xl shadow-xl p-8 animate-slide-in">
    <div class="flex items-center mb-6">
                    <span class="material-icons text-blue-600 mr-3 text-3xl">home</span>
                    <h2 class="text-2xl font-bold text-gray-900">Residential Address</h2>
                </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="relative">
            <input type="text" id="res_house_no" name="res_house_no" value="{{ old('res_house_no', session('form.res_house_no')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
            <label for="res_house_no" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">House/Block/Lot No.</label>
        </div>
        <div class="relative">
            <input type="text" id="res_street" name="res_street" value="{{ old('res_street', session('form.res_street')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
            <label for="res_street" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Street Name</label>
        </div>
        <div class="relative">
            <input type="text" id="res_sub_vil" name="res_sub_vil" value="{{ old('res_sub_vil', session('form.res_sub_vil')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
            <label for="res_sub_vil" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Subdivision/Village</label>
        </div>
        <div class="relative">
            <input type="text" id="res_brgy" name="res_brgy" value="{{ old('res_brgy', session('form.res_brgy')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
            <label for="res_brgy" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Barangay</label>
        </div>
        <div class="relative">
            <input type="text" id="res_city" name="res_city" value="{{ old('res_city', session('form.res_city')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
            <label for="res_city" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">City/Municipality</label>
        </div>
        <div class="relative">
            <input type="text" id="res_province" name="res_province" value="{{ old('res_province', session('form.res_province')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
            <label for="res_province" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Province</label>
        </div>
        <div class="relative">
            <input type="text" id="res_zipcode" name="res_zipcode" value="{{ old('res_zipcode', session('form.res_zipcode')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
            <label for="res_zipcode" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Zip Code</label>
        </div>
    </div>
    </section>

            <!-- Permanent Address Section -->
    <section class="bg-white rounded-2xl shadow-xl p-8 animate-slide-in mt-2">
        <div class="flex items-center mb-6">
                    <span class="material-icons text-blue-600 mr-3 text-3xl">home</span>
                    <h2 class="text-2xl font-bold text-gray-900">Permanent Address</h2>
                </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="relative">
            <input type="text" id="per_house_no" name="per_house_no" value="{{ old('per_house_no', session('form.per_house_no')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
            <label for="per_house_no" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">House/Block/Lot No.</label>
        </div>
        <div class="relative">
            <input type="text" id="per_street" name="per_street" value="{{ old('per_street', session('form.per_street')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
            <label for="per_street" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Street Name</label>
        </div>
        <div class="relative">
            <input type="text" id="per_sub_vil" name="per_sub_vil" value="{{ old('per_sub_vil', session('form.per_sub_vil')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
            <label for="per_sub_vil" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Subdivision/Village</label>
        </div>
        <div class="relative">
            <input type="text" id="per_brgy" name="per_brgy" value="{{ old('per_brgy', session('form.per_brgy')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
            <label for="per_brgy" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Barangay</label>
        </div>
        <div class="relative">
            <input type="text" id="per_city" name="per_city" value="{{ old('per_city', session('form.per_city')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
            <label for="per_city" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">City/Municipality</label>
        </div>
        <div class="relative">
            <input type="text" id="per_province" name="per_province" value="{{ old('per_province', session('form.per_province')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
            <label for="per_province" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Province</label>
        </div>
        <div class="relative">
            <input type="text" id="per_zipcode" name="per_zipcode" value="{{ old('per_zipcode', session('form.per_zipcode')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
            <label for="per_zipcode" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Zip Code</label>
        </div>
    </div>
</section>

            <!-- Family Background Section -->
            <section class="bg-white rounded-2xl shadow-xl p-8 animate-slide-in">
                <div class="flex items-center mb-6">
                    <span class="material-icons text-blue-600 mr-3 text-3xl">family_restroom</span>
                    <h2 class="text-2xl font-bold text-gray-900">Family Background</h2>
                </div>

                <p class="text-gray-600 mb-6 text-sm">
                    Write full name and list all requested details.
                </p>

                <!-- Spouse Information -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                        <span class="material-icons text-sm mr-2 text-blue-500">favorite</span>
                        Spouse Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                        <div class="relative">
                            <input type="text" id="spouse_surname" name="spouse_surname" value="{{ old('spouse_surname', session('form.spouse_surname')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                            <label for="spouse_surname" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Spouse's Surname</label>
                        </div>
                        <div class="relative">
                            <input type="text" id="spouse_first_name" name="spouse_first_name" value="{{ old('spouse_first_name', session('form.spouse_first_name')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                            <label for="spouse_first_name" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Spouse's First Name</label>
                        </div>
                        <div class="relative">
                            <input type="text" id="spouse_middle_name" name="spouse_middle_name" value="{{ old('spouse_middle_name', session('form.spouse_middle_name')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                            <label for="spouse_middle_name" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Spouse's Middle Name</label>
                        </div>
                        <div class="relative">
                        <input type="text" id="name_extension" name="name_extension" value="{{ old('name_extension', session('form.name_extension')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                        <label for="name_extension" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Name Ext.</label>
                    </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="relative">
                            <input type="text" id="spouse_occupation" name="spouse_occupation" value="{{ old('spouse_occupation', session('form.spouse_occupation')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                            <label for="spouse_occupation" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Occupation</label>
                        </div>
                        <div class="relative">
                            <input type="text" id="spouse_employer" name="spouse_employer" value="{{ old('spouse_employer', session('form.spouse_employer')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                            <label for="spouse_employer" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Employer/Business Name</label>
                        </div>
                        <div class="relative">
                            <input type="text" id="spouse_telephone" name="spouse_telephone" value="{{ old('spouse_telephone', session('form.spouse_telephone')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                            <label for="spouse_telephone" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Telephone No.</label>
                        </div>
                    </div>
                </div>

                <!-- Parents Information -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                        <span class="material-icons text-sm mr-2 text-blue-500">escalator_warning</span>
                        Parents Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                        <div class="relative">
                            <input type="text" id="father_surname" name="father_surname" value="{{ old('father_surname', session('form.father_surname')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                            <label for="father_surname" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Father's Surname</label>
                        </div>
                        <div class="relative">
                            <input type="text" id="father_first_name" name="father_first_name" value="{{ old('father_first_name', session('form.father_first_name')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                            <label for="father_first_name" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Father's First Name</label>
                        </div>
                        <div class="relative">
                            <input type="text" id="father_middle_name" name="father_middle_name" value="{{ old('father_middle_name', session('form.father_middle_name')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                            <label for="father_middle_name" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Father's Middle Name</label>
                        </div>
                        <div class="relative">
                        <input type="text" id="name_extension" name="name_extension" value="{{ old('name_extension', session('form.name_extension')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                        <label for="name_extension" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Name Ext.</label>
                    </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="relative">
                            <input type="text" id="mother_maiden_surname" name="mother_maiden_surname" value="{{ old('mother_maiden_surname', session('form.mother_maiden_surname')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                            <label for="mother_maiden_surname" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Mother's Maiden Surname</label>
                        </div>
                        <div class="relative">
                            <input type="text" id="mother_maiden_first_name" name="mother_maiden_first_name" value="{{ old('mother_maiden_first_name', session('form.mother_maiden_first_name')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                            <label for="mother_maiden_first_name" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Mother's First Name</label>
                        </div>
                        <div class="relative">
                            <input type="text" id="mother_maiden_middle_name" name="mother_maiden_middle_name" value="{{ old('mother_maiden_middle_name', session('form.mother_maiden_middle_name')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                            <label for="mother_maiden_middle_name" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Mother's Middle Name</label>
                        </div>
                    </div>
                </div>

                <!-- Children Information -->

<div x-data="{ children: {{ old('children', json_encode(session('form.children', [['name' => '', 'dob' => '']])) ) }} }">
    <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
        <span class="material-icons text-sm mr-2 text-blue-500">child_care</span>
        Children Information
    </h3>

    <template x-for="(child, index) in children" :key="index">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <!-- Full Name -->
            <div class="relative">
                <label :for="'child_name_' + index" class="block text-sm text-gray-500 mb-1">Full Name</label>
                <input type="text" :id="'child_name_' + index" :name="'children[' + index + '][name]'" x-model="child.name"
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition" 
                    placeholder="Full Name">
            </div>

            <!-- Date of Birth -->
            <div class="relative">
                <label :for="'child_dob_' + index" class="block text-sm text-gray-500 mb-1">Date of Birth</label>
                <input type="date" :id="'child_dob_' + index" :name="'children[' + index + '][dob]'" x-model="child.dob"
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
            </div>
        </div>
    </template>

    <!-- Add Button -->
    <button type="button" @click="children.push({ name: '', dob: '' })"
        class="mt-2 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition">
        + Add Another Child
    </button>
</div>

            </section>

            <!-- Educational Background Section -->
            <section class="bg-white rounded-2xl shadow-xl p-8 animate-slide-in">
                <div class="flex items-center mb-6">
                    <span class="material-icons text-blue-600 mr-3 text-3xl">school</span>
                    <h2 class="text-2xl font-bold text-gray-900">Educational Background</h2>
                </div>

                <p class="text-gray-600 mb-6 text-sm">
                    Continue on separate sheet if necessary. Write full name of school and honors.
                </p>

                @php
                    $formatMonthValue = static function ($value) {
                        $value = is_string($value) ? trim($value) : '';

                        if ($value === '') {
                            return '';
                        }

                        try {
                            if (preg_match('/^\d{4}-\d{2}$/', $value)) {
                                return $value;
                            }

                            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $value)) {
                                return \Carbon\Carbon::createFromFormat('d-m-Y', $value)->format('Y-m');
                            }

                            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                                return \Carbon\Carbon::createFromFormat('Y-m-d', $value)->format('Y-m');
                            }
                        } catch (\Throwable $e) {
                            return '';
                        }

                        return '';
                    };

                    $collegeEducation = $college_schools[0] ?? [];
                    $gradEducation = $grad_schools[0] ?? [];
                @endphp

                <!-- Elementary -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Elementary</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="relative">
                            <input type="text" aria-label="From date" id="elem_from" name="elem_from" value="{{ old('elem_from', session('form.c1.elem_from')) }}" class="edu-date w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                            <label class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">From</label>
                        </div>
                        <div class="relative">
                            <input type="text" aria-label="To date" id="elem_to" name="elem_to" value="{{ old('elem_to', session('form.c1.elem_to')) }}" class="edu-date w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                            <label class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">To</label>
                        </div>
                        <div class="relative col-span-2">
                            <input type="text" id="elem_school" name="elem_school" value="{{ old('elem_school', session('form.c1.elem_school')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                            <label for="elem_school" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">School Name</label>
                        </div>
                        <div class="relative col-span-2">
                            <input type="text" id="elem_basic" name="elem_basic" value="{{ old('elem_basic', session('form.c1.elem_basic')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                            <label for="elem_basic" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Basic Education/Degree/Course</label>
                        </div>
                        <div class="relative col-span-2">
                            <input type="text" id="elem_earned" name="elem_earned" value="{{ old('elem_earned', session('form.c1.elem_earned')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                            <label for="elem_earned" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Highest Level Units Earned</label>
                        </div>
                        <div class="relative col-span-2">
                            <input type="text" id="elem_year_graduated" name="elem_year_graduated" value="{{ old('elem_year_graduated', session('form.c1.elem_year_graduated')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                            <label for="elem_year_graduated" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Year Graduated</label>
                        </div>
                        <div class="relative col-span-2">
                            <input type="text" id="elem_academic_honors" name="elem_academic_honors" value="{{ old('elem_academic_honors', session('form.c1.elem_academic_honors')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                            <label for="elem_academic_honors" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Scholarship/Academic Honors Recieved</label>
                        </div>
                    </div>
                </div>

                <!-- Secondary -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Secondary</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="relative">
                            <input type="text" aria-label="From date" id="jhs_from" name="jhs_from" value="{{ old('jhs_from', $data['jhs_from'] ?? session('form.c1.jhs_from')) }}" class="edu-date w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                            <label class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">From</label>
                        </div>
                        <div class="relative">
                            <input type="text" aria-label="To date" id="jhs_to" name="jhs_to" value="{{ old('jhs_to', $data['jhs_to'] ?? session('form.c1.jhs_to')) }}" class="edu-date w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                            <label class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">To</label>
                        </div>
                        <div class="relative col-span-2">
                            <input type="text" id="jhs_school" name="jhs_school" value="{{ old('jhs_school', $data['jhs_school'] ?? session('form.c1.jhs_school')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                            <label for="jhs_school" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">School Name</label>
                        </div>
                        <div class="relative col-span-2">
                            <input type="text" id="jhs_basic" name="jhs_basic" value="{{ old('jhs_basic', $data['jhs_basic'] ?? session('form.c1.jhs_basic')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                            <label for="jhs_basic" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Basic Education/Degree/Course</label>
                        </div>
                        <div class="relative col-span-2">
                            <input type="text" id="jhs_earned" name="jhs_earned" value="{{ old('jhs_earned', $data['jhs_earned'] ?? session('form.c1.jhs_earned')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                            <label for="jhs_earned" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Highest Level Units Earned</label>
                        </div>
                        <div class="relative col-span-2">
                            <input type="text" id="jhs_year_graduated" name="jhs_year_graduated" value="{{ old('jhs_year_graduated', $data['jhs_year_graduated'] ?? session('form.c1.jhs_year_graduated')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                            <label for="jhs_year_graduated" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Year Graduated</label>
                        </div>
                        <div class="relative col-span-2">
                            <input type="text" id="jhs_academic_honors" name="jhs_academic_honors" value="{{ old('jhs_academic_honors', $data['jhs_academic_honors'] ?? session('form.c1.jhs_academic_honors')) }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                            <label for="jhs_academic_honors" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Scholarship/Academic Honors Recieved</label>
                        </div>
                    </div>
                </div>

                <!-- College -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">College</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="relative">
                            <input type="month" id="college_from" name="college[0][from]" value="{{ old('college.0.from', $formatMonthValue($collegeEducation['from'] ?? '')) }}" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                            <label class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">From</label>
                        </div>
                        <div class="relative">
                            <input type="month" id="college_to" name="college[0][to]" value="{{ old('college.0.to', $formatMonthValue($collegeEducation['to'] ?? '')) }}" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                            <label class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">To</label>
                        </div>
                        <div class="relative col-span-2">
                            <input type="text" id="college_school" name="college[0][school]" value="{{ old('college.0.school', $collegeEducation['school'] ?? '') }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                            <label for="college_school" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">School Name</label>
                        </div>
                        <div class="relative col-span-2">
                            <input type="text" id="college_basic" name="college[0][basic]" value="{{ old('college.0.basic', $collegeEducation['basic'] ?? '') }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                            <label for="college_basic" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Basic Education/Degree/Course</label>
                        </div>
                        <div class="relative col-span-2">
                            <input type="text" id="college_earned" name="college[0][earned]" value="{{ old('college.0.earned', $collegeEducation['earned'] ?? '') }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                            <label for="college_earned" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Highest Level Units Earned</label>
                        </div>
                        <div class="relative col-span-2">
                            <input type="text" id="college_year_graduated" name="college[0][year_graduated]" value="{{ old('college.0.year_graduated', $collegeEducation['year_graduated'] ?? '') }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                            <label for="college_year_graduated" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Year Graduated</label>
                        </div>
                        <div class="relative col-span-2">
                            <input type="text" id="college_academic_honors" name="college[0][academic_honors]" value="{{ old('college.0.academic_honors', $collegeEducation['academic_honors'] ?? '') }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                            <label for="college_academic_honors" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Scholarship/Academic Honors Recieved</label>
                        </div>
                    </div>
                </div>

                <!-- Graduate Studies -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Graduate Studies</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="relative">
                            <input type="month" id="grad_from" name="grad[0][from]" value="{{ old('grad.0.from', $formatMonthValue($gradEducation['from'] ?? '')) }}" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                            <label class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">From</label>
                        </div>
                        <div class="relative">
                            <input type="month" id="grad_to" name="grad[0][to]" value="{{ old('grad.0.to', $formatMonthValue($gradEducation['to'] ?? '')) }}" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                            <label class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">To</label>
                        </div>
                        <div class="relative col-span-2">
                            <input type="text" id="grad_school" name="grad[0][school]" value="{{ old('grad.0.school', $gradEducation['school'] ?? '') }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                            <label for="grad_school" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">School Name</label>
                        </div>  
                        <div class="relative col-span-2">
                            <input type="text" id="grad_basic" name="grad[0][basic]" value="{{ old('grad.0.basic', $gradEducation['basic'] ?? '') }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                            <label for="grad_basic" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Basic Education/Degree/Course</label>
                        </div>
                        <div class="relative col-span-2">
                            <input type="text" id="grad_earned" name="grad[0][earned]" value="{{ old('grad.0.earned', $gradEducation['earned'] ?? '') }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                            <label for="grad_earned" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Highest Level Units Earned</label>
                        </div>
                        <div class="relative col-span-2">
                            <input type="text" id="grad_year_graduated" name="grad[0][year_graduated]" value="{{ old('grad.0.year_graduated', $gradEducation['year_graduated'] ?? '') }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                            <label for="grad_year_graduated" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Year Graduated</label>
                        </div>
                        <div class="relative col-span-2">
                            <input type="text" id="grad_academic_honors" name="grad[0][academic_honors]" value="{{ old('grad.0.academic_honors', $gradEducation['academic_honors'] ?? '') }}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                            <label for="grad_academic_honors" class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Scholarship/Academic Honors Recieved</label>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Navigation -->
            <div class="flex justify-between items-center mt-8">
                <button type="button" disabled class="px-6 py-3 bg-gray-300 text-gray-500 rounded-lg font-semibold cursor-not-allowed opacity-50">
                    Previous
                </button>
                <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors duration-200 flex items-center">
                    Next
                    <span class="material-icons ml-2">arrow_forward</span>
                </button>
            </div>
        </form>

        <!-- Warning Footer -->
        <footer class="mt-12 text-center text-sm text-gray-600">
            <p class="mb-2">
                <strong>WARNING:</strong> Any misrepresentation made in the Personal Data Sheet and the Work Experience Sheet shall cause the filing of administrative/criminal case/s against the person concerned.
            </p>
            <p>CS Form No. 212 (Revised 2017). Read the attached guide to filling out the Personal Data Sheet before accomplishing the form.</p>
        </footer>
        @include('partials.pds_uppercase_inputs')
        @include('partials.loader')
    </main>
</body>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    flatpickr("#date_of_birth", {dateFormat: "d-m-Y", allowInput: true});
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.edu-date').forEach(function(el) {
            flatpickr(el, {dateFormat: "d-m-Y", allowInput: true});
        });
    });
</script>
</html>
