    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission Confirmation - Personal Data Sheet</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    @include('partials.global_toast')
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        
        @media print {
            .no-print {
                display: none;
            }
            
            body {
                background: white;
            }
            
            .print-break {
                page-break-before: always;
            }
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
        
        @keyframes checkmark {
            0% {
                stroke-dasharray: 0 100;
            }
            100% {
                stroke-dasharray: 100 0;
            }
        }
        
        .checkmark-animate {
            animation: checkmark 0.5s ease-out forwards;
        }
        
        /* Glass morphism effect */
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        /* Section styles */
        .info-section {
            border-left: 4px solid #3b82f6;
            padding-left: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .data-row {
            display: flex;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .data-label {
            font-weight: 600;
            color: #374151;
            min-width: 200px;
            flex-shrink: 0;
        }
        
        .data-value {
            color: #000;
            font-weight: 500;
        }
        
        .empty-value {
            color: #9ca3af;
            font-style: italic;
        }
        
        /* Success animation container */
        .success-container {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        
        .success-box {
            background: white;
            padding: 3rem;
            border-radius: 1rem;
            text-align: center;
            max-width: 400px;
            animation: slideIn 0.5s ease-out;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-indigo-50 min-h-screen">
    <!-- Success Animation -->
    <div id="successModal" class="success-container">
        <div class="success-box">
            <div class="w-20 h-20 mx-auto mb-4 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-12 h-12 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" class="checkmark-animate" style="stroke-dasharray: 100; stroke-dashoffset: 100;" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Submission Successful!</h2>
            <p class="text-gray-600 mb-6">Your Personal Data Sheet has been submitted successfully.</p>
            <button onclick="closeSuccessModal()" 
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                View Submission
            </button>
        </div>
    </div>

    <!-- Header -->
    <header class="bg-white shadow-lg sticky top-0 z-50 glass-effect no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <span class="material-icons text-green-600 mr-3">check_circle</span>
                    <h1 class="text-xl font-bold text-gray-900">Submission Confirmation</h1>
                </div>
                <div class="hidden md:flex items-center space-x-4">
                    <span class="text-sm text-gray-500">Reference No: <strong>PDS-2024-001234</strong></span>
                </div>
            </div>
        </div>
    </header>

    <!-- Action Buttons -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 no-print">
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <span class="material-icons text-yellow-400">info</span>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        Please review all information below. You can print this confirmation for your records.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="flex flex-wrap gap-3 mb-6">
            {{-- Download PDF button routed to currently logged-in user --}}
            <button onclick="window.location.href='{{ route('export.pds', ['download' => 1, 'force_fpdi' => 1]) }}'" class="flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200">
                <span class="material-icons mr-2">download</span>
                Download PDF
            </button>

            <button onclick="editForm()" class="flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors duration-200">
                <span class="material-icons mr-2">edit</span>
                Edit Form
            </button>

            <button onclick="submitAnother()" class="flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200">
                <span class="material-icons mr-2">add_circle</span>
                Submit Another
            </button>

            <button type="button" onclick="window.location.href='{{ route('dashboard_user') }}'" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors duration-200 flex items-center">
                <span class="material-icons mr-2">home</span>
                Dashboard
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
        <div class="bg-white rounded-2xl shadow-xl p-8 animate-slide-in">
            <!-- Submission Summary -->
            <div class="bg-green-50 rounded-lg p-6 mb-8">
                <div class="flex items-center">
                    <span class="material-icons text-green-600 mr-3 text-3xl">task_alt</span>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Application Successfully Submitted</h2>
                        <p class="text-gray-600">Submitted on: <strong id="submissionDate">December 15, 2024 at 10:30 AM</strong></p>
                    </div>
                </div>
            </div>

            <!-- Personal Information Section -->
            <section class="mb-8">
                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <span class="material-icons text-blue-600 mr-2">person</span>
                    I. Personal Information
                </h3>
                <div class="info-section">
                    <div class="data-row">
                        <span class="data-label">CS ID No:</span>
                        <span class="data-value" id="conf_csIdNo">N/A</span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">Full Name:</span>
                        <span class="data-value" id="conf_fullName">Juan P. Dela Cruz Jr.</span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">Sex:</span>
                        <span class="data-value" id="conf_sex">Male</span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">Civil Status:</span>
                        <span class="data-value" id="conf_civilStatus">Single</span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">Date of Birth:</span>
                        <span class="data-value" id="conf_dateOfBirth">January 1, 1990</span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">Place of Birth:</span>
                        <span class="data-value" id="conf_placeOfBirth">Manila, Philippines</span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">Height:</span>
                        <span class="data-value" id="conf_height">1.75 m</span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">Weight:</span>
                        <span class="data-value" id="conf_weight">70 kg</span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">Blood Type:</span>
                        <span class="data-value" id="conf_bloodType">O+</span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">PhilHealth No:</span>
                        <span class="data-value" id="conf_philhealthNo">12-345678901-2</span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">TIN No:</span>
                        <span class="data-value" id="conf_tinNo">123-456-789-000</span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">Agency Employee No:</span>
                        <span class="data-value" id="conf_agencyEmployeeNo">2024-001234</span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">Citizenship:</span>
                        <span class="data-value" id="conf_citizenship">Filipino</span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">Residential Address:</span>
                        <span class="data-value" id="conf_residentialAddress">123 Main Street, Barangay 1, Quezon City, Metro Manila 1100</span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">Permanent Address:</span>
                        <span class="data-value" id="conf_permanentAddress">Same as above</span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">Contact Numbers:</span>
                        <span class="data-value" id="conf_contactNumbers">(02) 8123-4567 / +63 917 123 4567</span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">Email Address:</span>
                        <span class="data-value" id="conf_emailAddress">juan.delacruz@email.com</span>
                    </div>
                </div>
            </section>

            <!-- Family Background Section -->
            <section class="mb-8">
                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <span class="material-icons text-blue-600 mr-2">family_restroom</span>
                    II. Family Background
                </h3>
                <div class="info-section">
                    <h4 class="font-semibold text-gray-700 mb-2">Spouse Information</h4>
                    <div class="data-row">
                        <span class="data-label">Spouse Name:</span>
                        <span class="data-value" id="conf_spouseName">N/A</span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">Spouse Occupation:</span>
                        <span class="data-value" id="conf_spouseOccupation">N/A</span>
                    </div>
                    
                    <h4 class="font-semibold text-gray-700 mb-2 mt-4">Parents Information</h4>
                    <div class="data-row">
                        <span class="data-label">Father's Name:</span>
                        <span class="data-value" id="conf_fatherName">Pedro S. Dela Cruz</span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">Mother's Maiden Name:</span>
                        <span class="data-value" id="conf_motherName">Maria G. Santos</span>
                    </div>
                    
                    <h4 class="font-semibold text-gray-700 mb-2 mt-4">Children</h4>
                    <div class="data-row">
                        <span class="data-label">Names of Children:</span>
                        <span class="data-value" id="conf_children">N/A</span>
                    </div>
                </div>
            </section>

            <!-- Educational Background Section -->
            <section class="mb-8">
                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <span class="material-icons text-blue-600 mr-2">school</span>
                    III. Educational Background
                </h3>
                <div class="info-section">
                    <div id="educationEntries" class="space-y-4">
                        <!-- Education entries will be populated here -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-700 mb-2">Elementary</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                                <div><span class="font-medium">School:</span> ABC Elementary School</div>
                                <div><span class="font-medium">Period:</span> 1996-2002</div>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-700 mb-2">Secondary</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                                <div><span class="font-medium">School:</span> XYZ High School</div>
                                <div><span class="font-medium">Period:</span> 2002-2006</div>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-700 mb-2">College</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                                <div><span class="font-medium">School:</span> University of the Philippines</div>
                                <div><span class="font-medium">Period:</span> 2006-2010</div>
                                <div><span class="font-medium">Degree:</span> Bachelor of Science in Computer Science</div>
                                <div><span class="font-medium">Honors:</span> Cum Laude</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Work Experience Section -->
            <section class="mb-8 print-break">
                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <span class="material-icons text-blue-600 mr-2">work_history</span>
                    IV. Work Experience
                </h3>
                <div class="info-section">
                    <div id="workExperienceEntries" class="space-y-4">
                        <!-- Work experience entries will be populated here -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-700">Software Developer</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm mt-2">
                                <div><span class="font-medium">Company:</span> Tech Solutions Inc.</div>
                                <div><span class="font-medium">Period:</span> Jan 2020 - Present</div>
                                <div><span class="font-medium">Salary:</span> PHP 45,000</div>
                                <div><span class="font-medium">Status:</span> Permanent</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Civil Service Eligibility Section -->
            <section class="mb-8">
                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <span class="material-icons text-blue-600 mr-2">verified</span>
                    V. Civil Service Eligibility
                </h3>
                <div class="info-section">
                    <div id="civilServiceEntries" class="space-y-4">
                        <!-- Civil service entries will be populated here -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-700">Career Service Professional</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm mt-2">
                                <div><span class="font-medium">Rating:</span> 85.50%</div>
                                <div><span class="font-medium">Date:</span> October 2019</div>
                                <div><span class="font-medium">Place:</span> Manila</div>
                                <div><span class="font-medium">License No:</span> 12345678</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Learning and Development Section -->
            <section class="mb-8">
                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <span class="material-icons text-blue-600 mr-2">menu_book</span>
                    VI. Learning and Development
                </h3>
                <div class="info-section">
                    <div id="learningEntries" class="space-y-4">
                        <!-- Learning entries will be populated here -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-700">Project Management Professional</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm mt-2">
                                <div><span class="font-medium">Type:</span> Technical</div>
                                <div><span class="font-medium">Hours:</span> 40 hours</div>
                                <div><span class="font-medium">Period:</span> March 2023</div>
                                <div><span class="font-medium">Conducted by:</span> PMI Philippines</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Other Information Section -->
            <section class="mb-8">
                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <span class="material-icons text-blue-600 mr-2">info</span>
                    VII. Other Information
                </h3>
                <div class="info-section">
                    <h4 class="font-semibold text-gray-700 mb-2">Special Skills and Hobbies</h4>
                    <div class="ml-4 mb-4">
                        <ul class="list-disc list-inside text-gray-700" id="conf_skills">
                            <li>Web Development</li>
                            <li>Database Management</li>
                            <li>Photography</li>
                        </ul>
                    </div>
                    
                    <h4 class="font-semibold text-gray-700 mb-2">Non-Academic Distinctions</h4>
                    <div class="ml-4 mb-4">
                        <ul class="list-disc list-inside text-gray-700" id="conf_distinctions">
                            <li>Employee of the Year 2022</li>
                            <li>Best Innovation Award 2023</li>
                        </ul>
                    </div>
                    
                    <h4 class="font-semibold text-gray-700 mb-2">Membership in Organizations</h4>
                    <div class="ml-4">
                        <ul class="list-disc list-inside text-gray-700" id="conf_organizations">
                            <li>Philippine Computer Society</li>
                            <li>Association of IT Professionals</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Questions Section -->
            <section class="mb-8">
                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <span class="material-icons text-blue-600 mr-2">quiz</span>
                    VIII. Questions
                </h3>
                <div class="info-section">
                    <div class="space-y-3">
                        <div class="data-row">
                            <span class="data-label">Related by consanguinity/affinity:</span>
                            <span class="data-value" id="conf_related">No</span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">Found guilty of administrative offense:</span>
                            <span class="data-value" id="conf_adminOffense">No</span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">Criminally charged:</span>
                            <span class="data-value" id="conf_criminalCharged">No</span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">Convicted of crime:</span>
                            <span class="data-value" id="conf_convicted">No</span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">Separated from service:</span>
                            <span class="data-value" id="conf_separated">No</span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">Election candidate:</span>
                            <span class="data-value" id="conf_candidate">No</span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">Person with disability:</span>
                            <span class="data-value" id="conf_pwd">No</span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">Solo parent:</span>
                            <span class="data-value" id="conf_soloParent">No</span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">Indigenous group member:</span>
                            <span class="data-value" id="conf_indigenous">No</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- References Section -->
            <section class="mb-8">
                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <span class="material-icons text-blue-600 mr-2">people</span>
                    IX. References
                </h3>
                <div class="info-section">
                    <div class="space-y-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-700">Reference 1</h4>
                            <div class="text-sm mt-2">
                                <div><span class="font-medium">Name:</span> Dr. Jose Rizal</div>
                                <div><span class="font-medium">Address:</span> 123 Bonifacio St., Manila</div>
                                <div><span class="font-medium">Tel:</span> (02) 8123-4567</div>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-700">Reference 2</h4>
                            <div class="text-sm mt-2">
                                <div><span class="font-medium">Name:</span> Atty. Apolinario Mabini</div>
                                <div><span class="font-medium">Address:</span> 456 Katipunan Ave., QC</div>
                                <div><span class="font-medium">Tel:</span> (02) 8987-6543</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Government ID Section -->
            <section class="mb-8">
                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <span class="material-icons text-blue-600 mr-2">badge</span>
                    X. Government Issued ID
                </h3>
                <div class="info-section">
                    <div class="data-row">
                        <span class="data-label">ID Type:</span>
                        <span class="data-value" id="conf_idType">Driver's License</span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">ID Number:</span>
                        <span class="data-value" id="conf_idNumber">N01-23-456789</span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">Date Issued:</span>
                        <span class="data-value" id="conf_idDateIssued">January 15, 2022</span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">Place of Issuance:</span>
                        <span class="data-value" id="conf_idPlaceIssued">Quezon City</span>
                    </div>
                </div>
            </section>

            <!-- Photo Section -->
            <section class="mb-8">
                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <span class="material-icons text-blue-600 mr-2">photo_camera</span>
                    XI. Photo
                </h3>
                <div class="info-section">
                    <div class="flex justify-center">
                        <div class="border-2 border-gray-300 rounded-lg p-2">
                            <img id="conf_photo" src="https://via.placeholder.com/150x150" alt="Applicant Photo" class="w-32 h-40 object-cover">
                        </div>
                    </div>
                </div>
            </section>

            <!-- Declaration Section -->
            <section class="mb-8">
                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <span class="material-icons text-blue-600 mr-2">verified_user</span>
                    XII. Declaration
                </h3>
                <div class="info-section">
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <p class="text-sm text-gray-700 mb-4">
                            I declare under oath that I have personally accomplished this Personal Data Sheet which is a true, 
                            correct and complete statement pursuant to the provisions of pertinent laws, rules and regulations 
                            of the Republic of the Philippines.
                        </p>
                        <div class="flex items-center text-sm">
                            <span class="material-icons text-green-600 mr-2">check_circle</span>
                            <span class="font-medium">Declaration accepted and consent given</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Footer -->
            <div class="mt-8 pt-8 border-t border-gray-200 text-center text-sm text-gray-600">
                <p class="mb-2">
                    This is a system-generated document. No signature required.
                </p>
                <p>
                    Generated on: <span id="generatedDate">December 15, 2024 at 10:30 AM</span>
                </p>
            </div>
        </div>
    </main>

    <script>
        // Initialize the page with data from form submission
        document.addEventListener('DOMContentLoaded', function() {
            // Set current date and time
            const now = new Date();
            const dateOptions = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true };
            const formattedDate = now.toLocaleDateString('en-US', dateOptions);
            
            document.getElementById('submissionDate').textContent = formattedDate;
            document.getElementById('generatedDate').textContent = formattedDate;
            
            // Load form data from localStorage or session
            loadFormData();
            
            // Auto-hide success modal after animation
            setTimeout(() => {
                closeSuccessModal();
            }, 3000);
        });

        function closeSuccessModal() {
            const modal = document.getElementById('successModal');
            modal.style.display = 'none';
        }

        function loadFormData() {
            // This function would load actual form data from localStorage or passed parameters
            // For now, using placeholder data
            
            // You can retrieve data like this if stored in localStorage:
            // const formData = JSON.parse(localStorage.getItem('pdsFormData') || '{}');
            
            // Example of populating data:
            // document.getElementById('conf_fullName').textContent = 
            //     `${formData.firstName || ''} ${formData.middleName || ''} ${formData.surname || ''} ${formData.nameExtension || ''}`.trim();
        }

        function editForm() {
            // Navigate back to the form with data
            if (confirm('Are you sure you want to edit your submission? Your current data will be preserved.')) {
                window.location.href = '{{ route('display_c1', ['simple' => 1]) }}';
            }
        }

        function submitAnother() {
            if (confirm('Are you sure you want to submit another form? This will clear the current form data.')) {
                // Clear localStorage if used
                localStorage.removeItem('pdsFormData');
                window.location.href = '{{ route('display_c1', ['simple' => 1]) }}';
            }
        }

        function downloadPDF() {
            // In a real application, this would generate a PDF
            showAppToast('PDF download feature will be implemented. For now, please use the Print function and save as PDF.');
            window.print();
        }

        // Function to populate data from form submission
        function populateConfirmationData(formData) {
            // Personal Information
            if (formData.csIdNo) document.getElementById('conf_csIdNo').textContent = formData.csIdNo;
            
            const fullName = `${formData.firstName || ''} ${formData.middleName || ''} ${formData.surname || ''} ${formData.nameExtension || ''}`.trim();
            if (fullName) document.getElementById('conf_fullName').textContent = fullName;
            
            if (formData.sex) document.getElementById('conf_sex').textContent = formData.sex;
            if (formData.civilStatus) document.getElementById('conf_civilStatus').textContent = formData.civilStatus;
            
            // Format date
            if (formData.dateOfBirth) {
                const date = new Date(formData.dateOfBirth);
                document.getElementById('conf_dateOfBirth').textContent = date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            }
            
            // Continue with other fields...
            // This is a template for how you would populate all fields
        }

        // Example function to handle form data passed from previous page
        function handleFormSubmission() {
            // Get URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const formDataString = urlParams.get('data');
            
            if (formDataString) {
                try {
                    const formData = JSON.parse(decodeURIComponent(formDataString));
                    populateConfirmationData(formData);
                } catch (e) {
                    console.error('Error parsing form data:', e);
                }
            }
            
            // Alternative: Get from localStorage
            const storedData = localStorage.getItem('pdsFormData');
            if (storedData) {
                try {
                    const formData = JSON.parse(storedData);
                    populateConfirmationData(formData);
                } catch (e) {
                    console.error('Error parsing stored data:', e);
                }
            }
        }

        // Call this when page loads
        handleFormSubmission();
    </script>
    @include('partials.loader')
</body>
</html>
