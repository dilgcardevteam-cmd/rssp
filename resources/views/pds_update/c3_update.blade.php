<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning & Development - CS Form 212</title>
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
        
        /* Card hover effects */
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        /* Remove animation */
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
                    <div class="progress-step completed flex items-center px-4 py-2 rounded-full text-sm font-medium">
                        <span class="material-icons text-sm mr-1">check_circle</span>
                        Work Experience
                    </div>
                    <span class="text-gray-300">→</span>
                    <div class="progress-step active flex items-center px-4 py-2 rounded-full text-sm font-medium">
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
        <form id="learning-form" class="space-y-8" method="POST" action="{{ route('submit_c3', ['go_to' => 'c4_update']) }}">
            @csrf
            <input type="hidden" name="learning_entry_count" id="learning_entry_count" value="0">
            <input type="hidden" name="voluntary_work_count" id="voluntary_work_count" value="0">
            <!-- Learning and Development Section -->
            <section class="bg-white rounded-2xl shadow-xl p-8 animate-slide-in">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <span class="material-icons text-blue-600 mr-3 text-3xl">school</span>
                        <h2 class="text-2xl font-bold text-gray-900">VI. Learning and Development (L&D) Interventions</h2>
                    </div>
                    <button type="button" id="add-learning-btn" class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        <span class="material-icons mr-2">add_circle</span>
                        Add Training
                    </button>
                </div>
                
                <p class="text-gray-600 mb-6 text-sm">
                    List down all learning and development interventions you have attended. Start from the most recent.
                </p>

                <!-- Learning Entries Container -->
                <div id="learning-container" class="space-y-4">
                    <!-- Entries will be added dynamically -->
                </div>

                <!-- Empty State -->
                <div id="learning-empty" class="text-center py-12 bg-gray-50 rounded-lg">
                    <span class="material-icons text-6xl text-gray-300 mb-4">school</span>
                    <p class="text-gray-500 mb-4">No learning and development entries yet.</p>
                    <button type="button" class="add-learning-trigger px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        <span class="material-icons mr-2">add</span>
                        Add Your First Training
                    </button>
                </div>
            </section>

            <!-- Voluntary Work Section -->
            <section class="bg-white rounded-2xl shadow-xl p-8 animate-slide-in">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <span class="material-icons text-blue-600 mr-3 text-3xl">volunteer_activism</span>
                        <h2 class="text-2xl font-bold text-gray-900">VII. Voluntary Work</h2>
                    </div>
                    <button type="button" id="add-voluntary-btn" class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        <span class="material-icons mr-2">add_circle</span>
                        Add Voluntary Work
                    </button>
                </div>
                
                <p class="text-gray-600 mb-6 text-sm">
                    Include involvement in civic/non-government/people/voluntary organizations.
                </p>

                <!-- Voluntary Work Entries Container -->
                <div id="voluntary-container" class="space-y-4">
                    <!-- Entries will be added dynamically -->
                </div>

                <!-- Empty State -->
                <div id="voluntary-empty" class="text-center py-12 bg-gray-50 rounded-lg">
                    <span class="material-icons text-6xl text-gray-300 mb-4">volunteer_activism</span>
                    <p class="text-gray-500 mb-4">No voluntary work entries yet.</p>
                    <button type="button" class="add-voluntary-trigger px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        <span class="material-icons mr-2">add</span>
                        Add Your First Voluntary Work
                    </button>
                </div>
            </section>

            <!-- Other Information Section -->
            <section class="bg-white rounded-2xl shadow-xl p-8 animate-slide-in">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <span class="material-icons text-blue-600 mr-3 text-3xl">info</span>
                        <h2 class="text-2xl font-bold text-gray-900">VIII. Other Information</h2>
                    </div>
                </div>

                <div class="space-y-8">
                    <!-- Special Skills and Hobbies -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                            <span class="material-icons text-sm mr-2 text-blue-500">sports_esports</span>
                            Special Skills and Hobbies
                        </h3>
                        <div id="skills-container" class="space-y-3">
                            <div class="flex gap-3">
                                <input type="text" name="skills[]" placeholder="Enter special skill or hobby" class="flex-1 px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                                <button type="button" class="add-skill px-4 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors duration-200">
                                    <span class="material-icons">add</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Non-Academic Distinctions -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                            <span class="material-icons text-sm mr-2 text-blue-500">emoji_events</span>
                            Non-Academic Distinctions / Recognition
                        </h3>
                        <div id="distinctions-container" class="space-y-3">
                            <div class="flex gap-3">
                                <input type="text" name="distinctions[]" placeholder="Enter non-academic distinction or recognition" class="flex-1 px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                                <button type="button" class="add-distinction px-4 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors duration-200">
                                    <span class="material-icons">add</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Membership in Organizations -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                            <span class="material-icons text-sm mr-2 text-blue-500">groups</span>
                            Membership in Association/Organization
                        </h3>
                        <div id="organizations-container" class="space-y-3">
                            <div class="flex gap-3">
                                <input type="text" name="organizations[]" placeholder="Enter organization name" class="flex-1 px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                                <button type="button" class="add-organization px-4 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors duration-200">
                                    <span class="material-icons">add</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Navigation -->
            <div class="flex justify-between items-center mt-8">
                <button type="button" onclick="window.location.href='{{ route('c2_update') }}'" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors duration-200 flex items-center">
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize containers
            const learningContainer = document.getElementById('learning-container');
            const voluntaryContainer = document.getElementById('voluntary-container');
            const learningEmpty = document.getElementById('learning-empty');
            const voluntaryEmpty = document.getElementById('voluntary-empty');
            
            // Add Learning Event Listeners
            document.getElementById('add-learning-btn').addEventListener('click', addLearningEntry);
            document.querySelector('.add-learning-trigger').addEventListener('click', function() {
                addLearningEntry();
                learningEmpty.style.display = 'none';
            });
            
            // Add Voluntary Work Event Listeners
            document.getElementById('add-voluntary-btn').addEventListener('click', addVoluntaryEntry);
            document.querySelector('.add-voluntary-trigger').addEventListener('click', function() {
                addVoluntaryEntry();
                voluntaryEmpty.style.display = 'none';
            });
            
            // Add Other Information Event Listeners
            document.querySelector('.add-skill').addEventListener('click', function() {
                addField('skills-container', 'skills[]', 'Enter special skill or hobby');
            });
            
            document.querySelector('.add-distinction').addEventListener('click', function() {
                addField('distinctions-container', 'distinctions[]', 'Enter non-academic distinction or recognition');
            });
            
            document.querySelector('.add-organization').addEventListener('click', function() {
                addField('organizations-container', 'organizations[]', 'Enter organization name');
            });
            
            // Remove entry functionality
            document.addEventListener('click', function(e) {
                if (e.target && e.target.closest('.remove-entry')) {
                    const entry = e.target.closest('.entry-card');
                    entry.classList.add('removing');
                    setTimeout(() => {
                        entry.remove();
                        checkEmptyStates();
                        updateIndices();
                    }, 300);
                }
                
                if (e.target && e.target.closest('.remove-field')) {
                    const field = e.target.closest('.flex');
                    field.classList.add('removing');
                    setTimeout(() => {
                        field.remove();
                    }, 300);
                }
            });

            function updateIndices() {
                // Learning
                const learningRows = document.querySelectorAll('#learning-container .entry-card');
                learningRows.forEach((row, index) => {
                    const i = index + 1;
                    row.querySelector('h4').textContent = `Training #${i}`;
                    
                    const inputs = row.querySelectorAll('input, select');
                    inputs.forEach(input => {
                        if (input.name.includes('learning_')) {
                            const baseName = input.name.split('_').slice(0, -1).join('_');
                            input.name = `${baseName}_${i}`;
                        }
                    });
                });
                document.getElementById('learning_entry_count').value = learningRows.length;

                // Voluntary
                const voluntaryRows = document.querySelectorAll('#voluntary-container .entry-card');
                voluntaryRows.forEach((row, index) => {
                    const i = index + 1;
                    row.querySelector('h4').textContent = `Voluntary Work #${i}`;
                    
                    const inputs = row.querySelectorAll('input, select');
                    inputs.forEach(input => {
                        if (input.name.includes('voluntary_')) {
                            const baseName = input.name.split('_').slice(0, -1).join('_');
                            input.name = `${baseName}_${i}`;
                        }
                    });
                });
                document.getElementById('voluntary_work_count').value = voluntaryRows.length;
            }
            
            function addLearningEntry() {
                const entryCount = document.getElementById('learning-container').children.length;
                const entryHtml = `
                    <div class="entry-card bg-gray-50 rounded-lg p-6 card-hover animate-fade-in">
                        <div class="flex justify-between items-start mb-4">
                            <h4 class="text-lg font-medium text-gray-700">Training #${entryCount + 1}</h4>
                            <button type="button" class="remove-entry text-red-500 hover:text-red-700 transition-colors duration-200">
                                <span class="material-icons">close</span>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="relative">
                                <input type="text" name="learning_title_${entryCount + 1}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                                <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Title of Learning/Training Program</label>
                            </div>
                            <div class="relative">
                                <select name="learning_type_${entryCount + 1}" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                                    <option value="">Select Type</option>
                                    <option value="Managerial">Managerial</option>
                                    <option value="Supervisory">Supervisory</option>
                                    <option value="Technical">Technical</option>
                                    <option value="Others">Others</option>
                                </select>
                                <label class="absolute -top-2 left-3 bg-gray-50 px-1 text-sm text-gray-600">Type of L&D</label>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="relative">
                                    <input type="date" name="learning_from_${entryCount + 1}" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                                    <label class="absolute -top-2 left-3 bg-gray-50 px-1 text-sm text-gray-600">From</label>
                                </div>
                                <div class="relative">
                                    <input type="date" name="learning_to_${entryCount + 1}" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                                    <label class="absolute -top-2 left-3 bg-gray-50 px-1 text-sm text-gray-600">To</label>
                                </div>
                            </div>
                            <div class="relative">
                                <input type="number" name="learning_hours_${entryCount + 1}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                                <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Number of Hours</label>
                            </div>
                            <div class="relative md:col-span-2">
                                <input type="text" name="learning_conducted_${entryCount + 1}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                                <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Conducted/Sponsored By</label>
                            </div>
                        </div>
                    </div>
                `;
                learningContainer.insertAdjacentHTML('beforeend', entryHtml);
                learningEmpty.style.display = 'none';
                updateIndices();
            }
            
            function addVoluntaryEntry() {
                const entryCount = document.getElementById('voluntary-container').children.length;
                const entryHtml = `
                    <div class="entry-card bg-gray-50 rounded-lg p-6 card-hover animate-fade-in">
                        <div class="flex justify-between items-start mb-4">
                            <h4 class="text-lg font-medium text-gray-700">Voluntary Work #${entryCount + 1}</h4>
                            <button type="button" class="remove-entry text-red-500 hover:text-red-700 transition-colors duration-200">
                                <span class="material-icons">close</span>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="relative md:col-span-2">
                                <input type="text" name="voluntary_org_${entryCount + 1}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                                <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Organization Name & Address</label>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="relative">
                                    <input type="date" name="voluntary_from_${entryCount + 1}" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                                    <label class="absolute -top-2 left-3 bg-gray-50 px-1 text-sm text-gray-600">From</label>
                                </div>
                                <div class="relative">
                                    <input type="date" name="voluntary_to_${entryCount + 1}" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                                    <label class="absolute -top-2 left-3 bg-gray-50 px-1 text-sm text-gray-600">To</label>
                                </div>
                            </div>
                            <div class="relative">
                                <input type="number" name="voluntary_hours_${entryCount + 1}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                                <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Number of Hours</label>
                            </div>
                            <div class="relative md:col-span-2">
                                <input type="text" name="voluntary_position_${entryCount + 1}" placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                                <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Position/Nature of Work</label>
                            </div>
                        </div>
                    </div>
                `;
                voluntaryContainer.insertAdjacentHTML('beforeend', entryHtml);
                voluntaryEmpty.style.display = 'none';
                updateIndices();
            }
            
            function addField(containerId, fieldName, placeholder) {
                const container = document.getElementById(containerId);
                const fieldHtml = `
                    <div class="flex gap-3 animate-fade-in">
                        <input type="text" name="${fieldName}" placeholder="${placeholder}" class="flex-1 px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                        <button type="button" class="remove-field px-4 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors duration-200">
                            <span class="material-icons">remove</span>
                        </button>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', fieldHtml);
            }
            
            function checkEmptyStates() {
                // Check learning entries
                if (learningContainer.children.length === 0) {
                    learningEmpty.style.display = 'block';
                } else {
                    learningEmpty.style.display = 'none';
                }
                
                // Check voluntary entries
                if (voluntaryContainer.children.length === 0) {
                    voluntaryEmpty.style.display = 'block';
                } else {
                    voluntaryEmpty.style.display = 'none';
                }
            }
        });
    </script>
    @include('partials.pds_uppercase_inputs')
    @include('partials.loader')
</body>
</html>
