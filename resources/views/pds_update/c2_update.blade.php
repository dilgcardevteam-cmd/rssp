<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Experience & Civil Service - CS Form 212</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
        
        /* Table styles */
        .modern-table {
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .modern-table thead th {
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            color: white;
            font-weight: 600;
            padding: 1rem;
            text-align: left;
            font-size: 0.875rem;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .modern-table tbody tr {
            transition: all 0.2s ease;
        }
        
        .modern-table tbody tr:hover {
            background-color: #f3f4f6;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .modern-table tbody td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .modern-table input,
        .modern-table select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }
        
        .modern-table input:focus,
        .modern-table select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        /* Remove row animation */
        @keyframes slideOut {
            to {
                opacity: 0;
                transform: translateX(-20px);
            }
        }
        
        .removing {
            animation: slideOut 0.3s ease-out forwards;
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
                        Personal Info
                    </div>
                    <span class="text-gray-300">→</span>
                    <div class="progress-step active flex items-center px-4 py-2 rounded-full text-sm font-medium">
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
        <form method="POST" action="{{ route('submit_c2', ['go_to' => 'c3_update']) }}" id="c2_form">
            @csrf
            <input type="hidden" name="work_exp_count" id="work_exp_count" value="0">
            <input type="hidden" name="civil_service_count" id="civil_service_count" value="0">
        <!-- Work Experience Section -->
        <section class="bg-white rounded-2xl shadow-xl p-8 mb-8 animate-slide-in">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <span class="material-icons text-blue-600 mr-3 text-3xl">work_history</span>
                    <h2 class="text-2xl font-bold text-gray-900">IV. Work Experience</h2>
                </div>
            </div>
            
            <p class="text-gray-600 mb-6 text-sm">
                Include private employment. Start from your recent work. Description of duties should be indicated in the attached Work Experience sheet.
            </p>

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-3 mb-6">
                <button id="add-work-exp-btn" class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                    <span class="material-icons mr-2">add_circle</span>
                    Add Work Experience
                </button>
                <button id="clear-work-exp-btn" class="flex items-center px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors duration-200">
                    <span class="material-icons mr-2">delete_sweep</span>
                    Clear All
                </button>
            </div>

            <!-- Empty State -->
            <div id="work-exp-empty" class="hidden text-center py-12 bg-gray-50 rounded-lg">
                <span class="material-icons text-6xl text-gray-300 mb-4">work_off</span>
                <p class="text-gray-500 mb-4">No work experience entries yet.</p>
                <p class="text-sm text-gray-400">Click "Add Work Experience" to get started.</p>
            </div>

            <!-- Work Experience Table -->
            <div class="overflow-x-auto rounded-lg shadow-sm border border-gray-200">
                <table id="work-exp-table" class="modern-table w-full">
                    <thead>
                        <tr>
                            <th class="rounded-tl-lg">No.</th>
                            <th>Inclusive Dates (From)</th>
                            <th>Inclusive Dates (To)</th>
                            <th>Position Title</th>
                            <th>Department / Agency / Office / Company</th>
                            <th>Status</th>
                            <th>Gov't Service</th>
                            <th class="rounded-tr-lg text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Rows will be added dynamically -->
                    </tbody>
                </table>
            </div>

            <p class="text-sm text-gray-500 mt-4 italic">
                * Click the 'Add' button to include additional experience.
            </p>
        </section>

        <!-- Civil Service Eligibility Section -->
        <section class="bg-white rounded-2xl shadow-xl p-8 animate-slide-in">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <span class="material-icons text-blue-600 mr-3 text-3xl">verified</span>
                    <h2 class="text-2xl font-bold text-gray-900">V. Civil Service Eligibility</h2>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-3 mb-6">
                <button id="add-civil-service-btn" class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                    <span class="material-icons mr-2">add_circle</span>
                    Add Eligibility
                </button>
                <button id="clear-civil-service-btn" class="flex items-center px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors duration-200">
                    <span class="material-icons mr-2">delete_sweep</span>
                    Clear All
                </button>
            </div>

            <!-- Empty State -->
            <div id="civil-service-empty" class="hidden text-center py-12 bg-gray-50 rounded-lg">
                <span class="material-icons text-6xl text-gray-300 mb-4">badge</span>
                <p class="text-gray-500 mb-4">No civil service eligibility entries yet.</p>
                <p class="text-sm text-gray-400">Click "Add Eligibility" to get started.</p>
            </div>

            <!-- Civil Service Table -->
            <div class="overflow-x-auto rounded-lg shadow-sm border border-gray-200">
                <table id="civil-service-table" class="modern-table w-full">
                    <thead>
                        <tr>
                            <th class="rounded-tl-lg">Career Service / Board / Bar</th>
                            <th>Rating</th>
                            <th>Date of Examination</th>
                            <th>Place of Examination</th>
                            <th>License Number</th>
                            <th>License Validity</th>
                            <th class="rounded-tr-lg text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Rows will be added dynamically -->
                    </tbody>
                </table>
            </div>

            <p class="text-sm text-gray-500 mt-4 italic">
                * Click the 'Add' button to include additional eligibility.
            </p>
        </section>

        <!-- Navigation -->
        <div class="flex justify-between items-center mt-8">
            <button type="button" onclick="window.location.href='{{ route('pds_update') }}'" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors duration-200 flex items-center">
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
    </main>

    <!-- Floating Action Buttons -->
    <div class="fixed bottom-8 right-8 flex flex-col gap-4">
        <button id="floating-add-work" class="w-14 h-14 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 hover:shadow-xl transition-all duration-200 flex items-center justify-center group">
            <span class="material-icons">work_outline</span>
            <span class="absolute right-full mr-3 bg-gray-800 text-white text-sm px-3 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                Add Work Experience
            </span>
        </button>
        <button id="floating-add-civil" class="w-14 h-14 bg-purple-600 text-white rounded-full shadow-lg hover:bg-purple-700 hover:shadow-xl transition-all duration-200 flex items-center justify-center group">
            <span class="material-icons">card_membership</span>
            <span class="absolute right-full mr-3 bg-gray-800 text-white text-sm px-3 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                Add Civil Service
            </span>
        </button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tables
            const workExpTable = document.getElementById('work-exp-table');
            const civilServiceTable = document.getElementById('civil-service-table');
            const workExpEmpty = document.getElementById('work-exp-empty');
            const civilServiceEmpty = document.getElementById('civil-service-empty');
            
            // Check initial state
            updateEmptyState();
            
            // Work Experience Add Button
            document.getElementById('add-work-exp-btn').addEventListener('click', addWorkExperienceRow);
            document.getElementById('floating-add-work').addEventListener('click', addWorkExperienceRow);
            
            // Civil Service Eligibility Add Button
            document.getElementById('add-civil-service-btn').addEventListener('click', addCivilServiceRow);
            document.getElementById('floating-add-civil').addEventListener('click', addCivilServiceRow);
            
            // Clear buttons
            document.getElementById('clear-work-exp-btn').addEventListener('click', clearWorkExperience);
            document.getElementById('clear-civil-service-btn').addEventListener('click', clearCivilService);
            
            // Add initial rows if empty
            if (workExpTable.querySelector('tbody').children.length === 0) {
                addWorkExperienceRow();
            }
            if (civilServiceTable.querySelector('tbody').children.length === 0) {
                addCivilServiceRow();
            }
            
            // Remove row functionality using event delegation
            document.addEventListener('click', function(e) {
                if (e.target && e.target.closest('.remove-row')) {
                    const row = e.target.closest('tr');
                    const table = row.closest('table');
                    
                    // Add fade-out animation
                    row.classList.add('removing');
                    
                    setTimeout(() => {
                        row.remove();
                        updateEmptyState();
                        
                        // Update numbering for work experience rows
                        if (table.id === 'work-exp-table') {
                            updateWorkExperienceNumbers();
                        }
                    }, 300);
                }
            });
            
            function updateCounts() {
                document.getElementById('work_exp_count').value = workExpTable.querySelector('tbody').children.length;
                document.getElementById('civil_service_count').value = civilServiceTable.querySelector('tbody').children.length;
            }

            // Functions
            function addWorkExperienceRow() {
                const tbody = workExpTable.querySelector('tbody');
                const rowCount = tbody.children.length;
                const newRow = document.createElement('tr');
                newRow.className = 'animate-fade-in';
                
                newRow.innerHTML = `
                    <td class="font-medium text-center">${rowCount + 1}</td>
                    <td>
                        <input type="date" name="work_exp_from[]" class="form-input" required />
                    </td>
                    <td>
                        <input type="date" name="work_exp_to[]" class="form-input" required />
                    </td>
                    <td>
                        <input type="text" name="work_exp_position[]" placeholder="Position Title" class="form-input" required />
                    </td>
                    <td>
                        <input type="text" name="work_exp_department[]" placeholder="Department/Agency" class="form-input" required />
                    </td>
                    <td>
                        <select name="work_exp_status[]" class="form-input" required>
                            <option value="">Select</option>
                            <option value="Permanent">Permanent</option>
                            <option value="Temporary">Temporary</option>
                            <option value="Casual">Casual</option>
                            <option value="Contractual">Contractual</option>
                        </select>
                    </td>
                    <td>
                        <select name="work_exp_govt_service[]" class="form-input" required>
                            <option value="">Y/N</option>
                            <option value="Y">Yes</option>
                            <option value="N">No</option>
                        </select>
                    </td>
                    <td class="text-center">
                        <button type="button" class="remove-row text-red-500 hover:text-red-700 transition-colors duration-200">
                            <span class="material-icons">delete</span>
                        </button>
                    </td>
                `;
                
                tbody.appendChild(newRow);
                updateEmptyState();
                updateCounts();
                
                // Scroll to the new row
                setTimeout(() => {
                    newRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }, 10);
            }
            
            function addCivilServiceRow() {
                const tbody = civilServiceTable.querySelector('tbody');
                const rowCount = tbody.children.length;
                const newRow = document.createElement('tr');
                newRow.className = 'animate-fade-in';
                
                newRow.innerHTML = `
                    <td>
                        <input type="text" name="cs_eligibility_career[]" placeholder="Career Service/Board/Bar" class="form-input" required />
                    </td>
                    <td>
                        <input type="text" name="cs_eligibility_rating[]" placeholder="Rating %" class="form-input" />
                    </td>
                    <td>
                        <input type="date" name="cs_eligibility_date[]" class="form-input" />
                    </td>
                    <td>
                        <input type="text" name="cs_eligibility_place[]" placeholder="Place of Examination" class="form-input" />
                    </td>
                    <td>
                        <input type="text" name="cs_eligibility_license[]" placeholder="License No." class="form-input" />
                    </td>
                    <td>
                        <input type="date" name="cs_eligibility_validity[]" class="form-input" />
                    </td>
                    <td class="text-center">
                        <button type="button" class="remove-row text-red-500 hover:text-red-700 transition-colors duration-200">
                            <span class="material-icons">delete</span>
                        </button>
                    </td>
                `;
                
                tbody.appendChild(newRow);
                updateEmptyState();
                updateCounts();
                
                // Scroll to the new row
                setTimeout(() => {
                    newRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }, 10);
            }
            
            function clearWorkExperience() {
                if (confirm('Are you sure you want to clear all work experience entries?')) {
                    const tbody = workExpTable.querySelector('tbody');
                    tbody.innerHTML = '';
                    updateEmptyState();
                    addWorkExperienceRow(); // Add one empty row
                }
            }
            
            function clearCivilService() {
                if (confirm('Are you sure you want to clear all civil service eligibility entries?')) {
                    const tbody = civilServiceTable.querySelector('tbody');
                    tbody.innerHTML = '';
                    updateEmptyState();
                    addCivilServiceRow(); // Add one empty row
                }
            }
            
            function updateWorkExperienceNumbers() {
                const rows = workExpTable.querySelectorAll('tbody tr');
                rows.forEach((row, index) => {
                    row.cells[0].textContent = index + 1;
                    // Update the name attributes to maintain sequential numbering
                    const inputs = row.querySelectorAll('input, select');
                    inputs.forEach(input => {
                        const name = input.name.replace(/\d+$/, index + 1);
                        input.name = name;
                    });
                });
            }
            
            function updateEmptyState() {
                // Work Experience
                const workExpRows = workExpTable.querySelector('tbody').children.length;
                if (workExpRows === 0) {
                    workExpTable.parentElement.classList.add('hidden');
                    workExpEmpty.classList.remove('hidden');
                } else {
                    workExpTable.parentElement.classList.remove('hidden');
                    workExpEmpty.classList.add('hidden');
                }
                
                // Civil Service
                const civilServiceRows = civilServiceTable.querySelector('tbody').children.length;
                if (civilServiceRows === 0) {
                    civilServiceTable.parentElement.classList.add('hidden');
                    civilServiceEmpty.classList.remove('hidden');
                } else {
                    civilServiceTable.parentElement.classList.remove('hidden');
                    civilServiceEmpty.classList.add('hidden');
                }
                updateCounts();
            }
        });
    </script>
    @include('partials.pds_uppercase_inputs')
    @include('partials.loader')
</body>
</html>
