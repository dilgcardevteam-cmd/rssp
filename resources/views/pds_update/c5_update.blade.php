<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload PDF - CS Form 212</title>
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
        .photo-upload-area {
            border: 2px dashed #3b82f6;
            border-radius: 1rem;
            padding: 3rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .photo-upload-area:hover {
            background-color: #eff6ff;
            border-color: #2563eb;
        }
        
        .photo-upload-area.dragover {
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

        /* certificate styles */
        .cert-upload-area {
            border: 2px dashed #3b82f6;
            border-radius: 1rem;
            padding: 3rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .cert-upload-area:hover {
            background-color: #eff6ff;
            border-color: #2563eb;
        }

        .cert-upload-area.dragover {
            background-color: #dbeafe;
            border-color: #1d4ed8;
            transform: scale(0.98);
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
                        Personal Info
                    </div>
                    <span class="text-gray-300">→</span>
                    <div class="progress-step completed flex items-center px-4 py-2 rounded-full text-sm font-medium">
                        <span class="material-icons text-sm mr-1">check_circle</span>
                        Work Experience
                    </div>
                    <span class="text-gray-300">→</span>
                    <div class="progress-step completed flex items-center px-4 py-2 rounded-full text-sm font-medium">
                         <span class="material-icons text-sm mr-1">check_circle</span>
                        Learning & Development
                    </div>
                    <span class="text-gray-300">→</span>
                    <div class="progress-step completed flex items-center px-4 py-2 rounded-full text-sm font-medium">
                        <span class="material-icons text-sm mr-1">check_circle</span>
                        Other Information
                    </div>
                    <span class="text-gray-300">→</span>
                    <div class="progress-step active flex items-center px-4 py-2 rounded-full text-sm font-medium">
                        <span class="material-icons text-sm mr-1">info</span>
                        Upload PDF
                    </div>
                    
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form method="POST" action="{{ route('finalize_pds', ['go_to' => 'dashboard_user']) }}" enctype="multipart/form-data">
            @csrf
            <section class="bg-white rounded-2xl shadow-xl p-8 animate-slide-in">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Required Documents</h2>
                <div class="w-full mb-6 border-b border-dashed border-gray-300 pb-4">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between w-full gap-2">
                        <div class="w-full md:w-2/3">
                            <h3 class="text-gray-700 font-medium">Application Letter</h3>
                            @if(!empty($documents?->application_letter))
                                <div class="text-sm text-green-600 mt-2">
                                    ✅ File uploaded:
                                    <a href="{{ \App\Support\PreviewUrl::forPath($documents->application_letter) }}" target="_blank" class="underline text-blue-600 hover:text-blue-800">View PDF</a>
                                </div>
                                <label class="inline-flex items-center mt-2 text-sm text-red-500">
                                    <input type="checkbox" name="remove_files[application_letter]" class="mr-2">
                                    Remove this file
                                </label>
                            @else
                                <p class="text-sm text-gray-500 mt-2">No file uploaded yet.</p>
                            @endif
                        </div>
                        <div class="w-full md:w-auto flex justify-end">
                            <label for="cert-upload-application-letter" class="cert-upload-area inline-flex items-center justify-center border border-gray-300 p-1 rounded cursor-pointer">
                                <span class="material-icons text-5xl text-blue-400">cloud_upload</span>
                            </label>
                            <input type="file" id="cert-upload-application-letter" name="cert_uploads[application_letter]" accept="application/pdf" class="hidden">
                        </div>
                    </div>
                </div>
                
                @php $fields = [
                    'pqe_result' => 'Pre-Qualifying Exam (PQE) result',
                    'cert_eligibility' => 'Photocopy of Certificate of Eligibility/Board Rating',
                    'ipcr' => 'Certification of Numerical Rating/Performance Rating/IPCR',
                    'non_academic' => 'Non-Academic awards received',
                    'cert_training' => 'Certified/authenticated copy of Certificates of Training/Participation',
                    'designation_order' => 'List of certified photocopy of duly confirmed Designation Order/s',
                    'transcript_records' => 'Photocopy of Transcript of Records (Baccalaureate Degree)',
                    'photocopy_diploma' => 'Photocopy of Diploma',
                    'grade_masteraldoctorate' => 'Certified photocopy of Certificate of Grades with Masteral/Doctorate units earned',
                    'tor_masteraldoctorate' => 'Certified photocopy of TOR with Masteral/Doctorate Degree',
                    'cert_employment' => 'Certificate of Employment (if any)',
                    'other_documents' => 'Other documents submitted'
                ]; @endphp
                
                @foreach($fields as $key => $label)
                    <div class="w-full mb-6 border-b border-dashed border-gray-300 pb-4">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between w-full gap-2">
                            <div class="w-full md:w-2/3">
                                <h3 class="text-gray-700 font-medium">{{ $label }}</h3>
                                @if(!empty($documents?->{$key}))
                                    <div class="text-sm text-green-600 mt-2">
                                        ✅ File uploaded:
                                        <a href="{{ \App\Support\PreviewUrl::forPath($documents->{$key}) }}" target="_blank" class="underline text-blue-600 hover:text-blue-800">View PDF</a>
                                    </div>
                                    <label class="inline-flex items-center mt-2 text-sm text-red-500">
                                        <input type="checkbox" name="remove_files[{{ $key }}]" class="mr-2">
                                        Remove this file
                                    </label>
                                @else
                                    <p class="text-sm text-gray-500 mt-2">No file uploaded yet.</p>
                                @endif
                            </div>
                            <div class="w-full md:w-auto flex justify-end">
                                <label for="cert-upload-{{ $key }}" class="cert-upload-area inline-flex items-center justify-center border border-gray-300 p-1 rounded cursor-pointer">
                                    <span class="material-icons text-5xl text-blue-400">cloud_upload</span>
                                </label>
                                <input type="file" id="cert-upload-{{ $key }}" name="cert_uploads[{{ $key }}]" accept="application/pdf" class="hidden">
                            </div>
                        </div>
                    </div>
                @endforeach
                
                <!-- Navigation and Submit -->
                <div class="flex justify-between items-center mt-8">
                <button type="button" onclick="window.location.href='{{ route('c4_update') }}'" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors duration-200 flex items-center">
                    <span class="material-icons mr-2">arrow_back</span>
                    Previous
                </button>
        
                <div class="flex justify-end items-center mt-8">
                    <button type="submit" class="px-8 py-3 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition-colors duration-200 flex items-center shadow-lg hover:shadow-xl">
                        <span class="material-icons mr-2">check_circle</span>
                        Submit Application
                    </button>
                </div>
            </section>
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
        // Form submission
        const form = document.querySelector('form');
        form.addEventListener('submit', (e) => {
            // Optional UI feedback
            const submitBtn = e.submitter;
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="material-icons mr-2 animate-spin">refresh</span>Submitting...';
            }
        });
    });
            
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ✅ Add highlight when a file is selected
            const fileInputs = document.querySelectorAll('input[type="file"]');
        
            fileInputs.forEach(input => {
                input.addEventListener('change', function () {
                    const label = input.previousElementSibling;
        
                    if (input.files.length > 0) {
                        // Highlight the icon visually (e.g. background or border or icon color)
                        label.classList.add('bg-green-100', 'border-green-400');
                        const icon = label.querySelector('.material-icons');
                        if (icon) {
                            icon.classList.remove('text-blue-400');
                            icon.classList.add('text-green-500');
                        }
                    } else {
                        // If file is removed, revert highlight
                        label.classList.remove('bg-green-100', 'border-green-400');
                        const icon = label.querySelector('.material-icons');
                        if (icon) {
                            icon.classList.remove('text-green-500');
                            icon.classList.add('text-blue-400');
                        }
                    }
                });
            });
        });
        </script>
        @include('partials.pds_uppercase_inputs')
        @include('partials.loader')
</body>
</html>
