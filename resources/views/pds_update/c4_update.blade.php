<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Other Information - CS Form 212</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    @include('partials.global_toast')
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
            from { opacity: 0; }
            to { opacity: 1; }
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
        
        /* Radio and checkbox custom styles */
        input[type="radio"], input[type="checkbox"] {
            -webkit-appearance: none;
            appearance: none;
            width: 1.25rem;
            height: 1.25rem;
            border: 2px solid #d1d5db;
            border-radius: 0.25rem;
            transition: all 0.15s ease;
            position: relative;
            cursor: pointer;
        }
        
        input[type="radio"] {
            border-radius: 50%;
        }
        
        input[type="radio"]:checked, input[type="checkbox"]:checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }
        
        input[type="radio"]:checked::after, input[type="checkbox"]:checked::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        
        input[type="radio"]:checked::after {
            width: 0.5rem;
            height: 0.5rem;
            background: white;
            border-radius: 50%;
        }
        
        input[type="checkbox"]:checked::after {
            width: 0.375rem;
            height: 0.625rem;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: translate(-50%, -60%) rotate(45deg);
        }
        
        /* Photo upload area styles */
        .upload-area {
            border: 2px dashed #3b82f6;
            border-radius: 1rem;
            padding: 3rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .upload-area:hover {
            background-color: #eff6ff;
            border-color: #2563eb;
        }
        
        .upload-area.dragover {
            background-color: #dbeafe;
            border-color: #1d4ed8;
            transform: scale(0.98);
        }
        
        #photo-preview {
            max-width: 200px;
            max-height: 200px;
            margin: 0 auto;
            display: none;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .question-card {
            border-left: 4px solid #3b82f6;
            padding-left: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .detail-input {
            margin-top: 1rem;
            padding: 1rem;
            background-color: #f3f4f6;
            border-radius: 0.5rem;
            display: none;
        }
        
        .detail-input.show {
            display: block;
            animation: fadeIn 0.3s ease-out;
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
                    <div class="progress-step completed flex items-center px-4 py-2 rounded-full text-sm font-medium">
                        <span class="material-icons text-sm mr-1">check_circle</span>
                        PERSONAL INFORMATION
                    </div>
                    <span class="text-gray-300">â†’</span>
                    <div class="progress-step completed flex items-center px-4 py-2 rounded-full text-sm font-medium">
                        <span class="material-icons text-sm mr-1">check_circle</span>
                        WORK EXPERIENCE
                    </div>
                    <span class="text-gray-300">â†’</span>
                    <div class="progress-step completed flex items-center px-4 py-2 rounded-full text-sm font-medium">
                        <span class="material-icons text-sm mr-1">check_circle</span>
                        LEARNING & DEVELOPMENT
                    </div>
                    <span class="text-gray-300">â†’</span>
                    <div class="progress-step active flex items-center px-4 py-2 rounded-full text-sm font-medium">
                        <span class="material-icons text-sm mr-1">info</span>
                        OTHER INFORMATION
                    </div>
                    <span class="text-gray-300">â†’</span>
                    <div class="progress-step flex items-center px-4 py-2 rounded-full text-sm font-medium bg-gray-100 text-gray-500">
                        <span class="material-icons text-sm mr-1">info</span>
                        UPLOAD PDF
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form id="other-info-form" class="space-y-8" method="POST" action="{{ route('submit_c4', ['go_to' => 'display_c4', 'open_docs' => 1]) }}" enctype="multipart/form-data">
            @csrf
            <!-- Related Third Degree Section -->
            <section class="bg-white rounded-2xl shadow-xl p-8 animate-slide-in">
                <div class="flex items-center mb-6">
                    <span class="material-icons text-blue-600 mr-3 text-3xl">family_restroom</span>
                    <h2 class="text-2xl font-bold text-gray-900">IX. Related Third Degree</h2>
                </div>
                
                <div class="question-card">
                    <p class="text-gray-700 font-medium mb-3">
                        Are you related by consanguinity or affinity to the appointing or recommending authority, or to the Bureau or Department where you will be appointed?
                    </p>
                    <div class="flex gap-6">
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="related_third_degree" value="yes" class="mr-2">
                            <span>Yes</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="related_third_degree" value="no" class="mr-2" checked>
                            <span>No</span>
                        </label>
                    </div>
                    <div id="related-details" class="detail-input">
                        <label class="block text-sm font-medium text-gray-700 mb-2">If YES, give details:</label>
                        <textarea name="related_third_degree_details" rows="3" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all resize-none"></textarea>
                    </div>
                </div>
            </section>

            <!-- Administrative Offense Section -->
            <section class="bg-white rounded-2xl shadow-xl p-8 animate-slide-in">
                <div class="flex items-center mb-6">
                    <span class="material-icons text-blue-600 mr-3 text-3xl">gavel</span>
                    <h2 class="text-2xl font-bold text-gray-900">X. Administrative Offenses</h2>
                </div>
                
                <div class="question-card">
                    <p class="text-gray-700 font-medium mb-3">
                        Have you ever been found guilty of any administrative offense?
                    </p>
                    <div class="flex gap-6">
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="admin_offense" value="yes" class="mr-2">
                            <span>Yes</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="admin_offense" value="no" class="mr-2" checked>
                            <span>No</span>
                        </label>
                    </div>
                    <div id="admin-details" class="detail-input">
                        <label class="block text-sm font-medium text-gray-700 mb-2">If YES, give details:</label>
                        <textarea name="admin_offense_details" rows="3" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all resize-none"></textarea>
                    </div>
                </div>

                <div class="question-card mt-6">
                    <p class="text-gray-700 font-medium mb-3">
                        Have you been criminally charged before any court?
                    </p>
                    <div class="flex gap-6">
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="criminal_charged" value="yes" class="mr-2">
                            <span>Yes</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="criminal_charged" value="no" class="mr-2" checked>
                            <span>No</span>
                        </label>
                    </div>
                    <div id="criminal-details" class="detail-input">
                        <label class="block text-sm font-medium text-gray-700 mb-2">If YES, give details:</label>
                        <textarea name="criminal_charged_details" rows="3" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all resize-none"></textarea>
                    </div>
                </div>

                <div class="question-card mt-6">
                    <p class="text-gray-700 font-medium mb-3">
                        Have you ever been convicted of any crime or violation of any law, decree, ordinance or regulation by any court or tribunal?
                    </p>
                    <div class="flex gap-6">
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="convicted" value="yes" class="mr-2">
                            <span>Yes</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="convicted" value="no" class="mr-2" checked>
                            <span>No</span>
                        </label>
                    </div>
                    <div id="convicted-details" class="detail-input">
                        <label class="block text-sm font-medium text-gray-700 mb-2">If YES, give details:</label>
                        <textarea name="convicted_details" rows="3" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all resize-none"></textarea>
                    </div>
                </div>
            </section>

            <!-- Other Questions Section -->
            <section class="bg-white rounded-2xl shadow-xl p-8 animate-slide-in">
                <div class="flex items-center mb-6">
                    <span class="material-icons text-blue-600 mr-3 text-3xl">quiz</span>
                    <h2 class="text-2xl font-bold text-gray-900">XI. Other Information</h2>
                </div>
                
                <div class="question-card">
                    <p class="text-gray-700 font-medium mb-3">
                        Have you ever been separated from the service in any of the following modes: resignation, dismissal, removal, retirement, dropped from rolls, AWOL, or otherwise?
                    </p>
                    <div class="flex gap-6">
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="separated" value="yes" class="mr-2">
                            <span>Yes</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="separated" value="no" class="mr-2" checked>
                            <span>No</span>
                        </label>
                    </div>
                    <div id="separated-details" class="detail-input">
                        <label class="block text-sm font-medium text-gray-700 mb-2">If YES, give details:</label>
                        <textarea name="separated_details" rows="3" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all resize-none"></textarea>
                    </div>
                </div>

                <div class="question-card mt-6">
                    <p class="text-gray-700 font-medium mb-3">
                        Have you ever been a candidate in a national or local election held within the last year (except Barangay election)?
                    </p>
                    <div class="flex gap-6">
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="candidate" value="yes" class="mr-2">
                            <span>Yes</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="candidate" value="no" class="mr-2" checked>
                            <span>No</span>
                        </label>
                    </div>
                    <div id="candidate-details" class="detail-input">
                        <label class="block text-sm font-medium text-gray-700 mb-2">If YES, give details:</label>
                        <textarea name="candidate_details" rows="3" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all resize-none"></textarea>
                    </div>
                </div>

                <!-- Special Status Questions -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8 pb-6 border-b-2 border-gray-200">
                    <div class="question-card">
                        <p class="text-gray-700 font-medium mb-3">Are you a person with disability?</p>
                        <div class="flex gap-4">
                            <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                                <input type="radio" name="pwd" value="yes" class="mr-2">
                                <span>Yes</span>
                            </label>
                            <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                                <input type="radio" name="pwd" value="no" class="mr-2" checked>
                                <span>No</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="question-card">
                        <p class="text-gray-700 font-medium mb-3">Are you a solo parent?</p>
                        <div class="flex gap-4">
                            <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                                <input type="radio" name="solo_parent" value="yes" class="mr-2">
                                <span>Yes</span>
                            </label>
                            <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                                <input type="radio" name="solo_parent" value="no" class="mr-2" checked>
                                <span>No</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="question-card">
                        <p class="text-gray-700 font-medium mb-3">Are you a member of any indigenous group?</p>
                        <div class="flex gap-4">
                            <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                                <input type="radio" name="indigenous" value="yes" class="mr-2">
                                <span>Yes</span>
                            </label>
                            <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                                <input type="radio" name="indigenous" value="no" class="mr-2" checked>
                                <span>No</span>
                            </label>
                        </div>
                    </div>
                </div>
            </section>

            <!-- References Section -->
            <section class="bg-white rounded-2xl shadow-xl p-8 animate-slide-in">
                <div class="flex items-center mb-6">
                    <span class="material-icons text-blue-600 mr-3 text-3xl">people</span>
                    <h2 class="text-2xl font-bold text-gray-900">XII. References</h2>
                </div>
                
                <p class="text-gray-600 mb-6 text-sm">
                    Person not related by consanguinity or affinity to applicant/appointee
                </p>

                <div class="space-y-6">
                    <!-- Reference 1 -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Reference 1</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="relative">
                                <input type="text" name="ref1_name" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                                <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Name</label>
                            </div>
                            <div class="relative">
                                <input type="tel" name="ref1_tel" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                                <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Telephone No.</label>
                            </div>
                            <div class="relative md:col-span-2">
                                <input type="text" name="ref1_address" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                                <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Address</label>
                            </div>
                        </div>
                    </div>

                    <!-- Reference 2 -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Reference 2</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="relative">
                                <input type="text" name="ref2_name" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                                <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Name</label>
                            </div>
                            <div class="relative">
                                <input type="tel" name="ref2_tel" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                                <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Telephone No.</label>
                            </div>
                            <div class="relative md:col-span-2">
                                <input type="text" name="ref2_address" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                                <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Address</label>
                            </div>
                        </div>
                    </div>

                    <!-- Reference 3 -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Reference 3</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="relative">
                                <input type="text" name="ref3_name" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                                <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Name</label>
                            </div>
                            <div class="relative">
                                <input type="tel" name="ref3_tel" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                                <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Telephone No.</label>
                            </div>
                            <div class="relative md:col-span-2">
                                <input type="text" name="ref3_address" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                                <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Address</label>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Government ID Section -->
            <section class="bg-white rounded-2xl shadow-xl p-8 animate-slide-in">
                <div class="flex items-center mb-6">
                    <span class="material-icons text-blue-600 mr-3 text-3xl">badge</span>
                    <h2 class="text-2xl font-bold text-gray-900">XIII. Government Issued ID</h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="relative">
                        <select name="id_type" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all appearance-none bg-white">
                            <option value="">Select ID Type</option>
                            <option value="passport">Passport</option>
                            <option value="gsis">GSIS</option>
                            <option value="sss">SSS</option>
                            <option value="philhealth">PhilHealth</option>
                            <option value="drivers">Driver's License</option>
                            <option value="prc">PRC</option>
                            <option value="voters">Voter's ID</option>
                            <option value="other">Other</option>
                        </select>
                        <label class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">ID Type</label>
                    </div>
                    
                    <div class="relative">
                        <input type="text" name="id_number" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                        <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">ID Number</label>
                    </div>
                    
                    <div class="relative">
                        <input type="date" name="id_date_issued" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                        <label class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">Date Issued</label>
                    </div>
                    
                    <div class="relative">
                        <input type="text" name="id_place_issued" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                        <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Place of Issuance</label>
                    </div>
                </div>
            </section>

            <!-- Photo Upload Section -->
            <section class="bg-white rounded-2xl shadow-xl p-8 animate-slide-in">
                <div class="flex items-center mb-6">
                    <span class="material-icons text-blue-600 mr-3 text-3xl">photo_camera</span>
                    <h2 class="text-2xl font-bold text-gray-900">XIV. Photo Upload</h2>
                </div>
                
                <div class="upload-area" id="upload-area">
                    <input type="file" id="photo-upload" name="photo_upload" accept="image/*" class="hidden">
                    <span class="material-icons text-6xl text-blue-400 mb-4 block">cloud_upload</span>
                    <p class="text-gray-700 font-medium mb-2">Click to upload or drag and drop</p>
                    <p class="text-sm text-gray-500">Passport size photo (4.5cm x 3.5cm)</p>
                    <p class="text-xs text-gray-400 mt-2">PNG, JPG, GIF up to 10MB</p>
                    <img id="photo-preview" alt="Preview" class="mt-4" src="https://storage.googleapis.com/workspace-0f70711f-8b4e-4d94-86f1-2a93ccde5887/image/6a2ab7dd-3bd3-47b1-9901-acc999fb4e67.png">
                </div>
                
                <div class="mt-4 text-sm text-gray-600">
                    <p class="flex items-center mb-2">
                        <span class="material-icons text-yellow-500 mr-2">warning</span>
                        Photo must be passport size (4.5 cm x 3.5 cm / approx. 170px x 133px)
                    </p>
                    <p class="flex items-center">
                        <span class="material-icons text-yellow-500 mr-2">info</span>
                        No computer generated or photocopied picture
                    </p>
                </div>
            </section>

             <!-- Navigation -->
             <div class="flex justify-between items-center mt-8">
                <button type="button" onclick="window.location.href='{{ route('c3_update') }}'" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors duration-200 flex items-center">
                    <span class="material-icons mr-2">arrow_back</span>
                    Previous
                </button>
                <button type="button" onclick="window.location.href='{{ route('submit_update') }}'" class="px-6 py-3 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition-colors duration-200 flex items-center">
                    Update
                    <span class="material-icons ml-3">upgrade</span>
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
            <p>CS Form No. 212 (Revised 2017)</p>
        </footer>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle Yes/No questions with details
            const radioQuestions = [
                { name: 'related_third_degree', detailId: 'related-details' },
                { name: 'admin_offense', detailId: 'admin-details' },
                { name: 'criminal_charged', detailId: 'criminal-details' },
                { name: 'convicted', detailId: 'convicted-details' },
                { name: 'separated', detailId: 'separated-details' },
                { name: 'candidate', detailId: 'candidate-details' }
            ];
            
            radioQuestions.forEach(question => {
                const radios = document.querySelectorAll(`input[name="${question.name}"]`);
                const detailDiv = document.getElementById(question.detailId);
                
                radios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        if (this.value === 'yes') {
                            detailDiv.classList.add('show');
                            detailDiv.querySelector('textarea').required = true;
                        } else {
                            detailDiv.classList.remove('show');
                            detailDiv.querySelector('textarea').required = false;
                            detailDiv.querySelector('textarea').value = '';
                        }
                    });
                });
            });
            
            // Photo upload functionality
            const uploadArea = document.getElementById('upload-area');
            const photoUpload = document.getElementById('photo-upload');
            const photoPreview = document.getElementById('photo-preview');
            
            uploadArea.addEventListener('click', () => photoUpload.click());
            
            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.classList.add('dragover');
            });
            
            uploadArea.addEventListener('dragleave', () => {
                uploadArea.classList.remove('dragover');
            });
            
            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
                
                const files = e.dataTransfer.files;
                if (files.length > 0 && files[0].type.startsWith('image/')) {
                    handlePhotoUpload(files[0]);
                }
            });
            
            photoUpload.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    handlePhotoUpload(e.target.files[0]);
                }
            });
            
            function handlePhotoUpload(file) {
                if (file.size > 10 * 1024 * 1024) {
                    showAppToast('File size must be less than 10MB');
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = (e) => {
                    photoPreview.src = e.target.result;
                    photoPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
    @include('partials.pds_uppercase_inputs')
    @include('partials.loader')
</body>
</html>
