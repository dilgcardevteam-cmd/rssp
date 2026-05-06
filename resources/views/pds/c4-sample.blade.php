@extends('layout.pds_layout')
@section('title','Upload PDF')
@section('content')
    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form id="myForm" method="POST" action="/pds/finalize/display_final_pds" enctype="multipart/form-data">
            @csrf

            <!-- Required Documents -->
            <section class="bg-white rounded-2xl shadow-xl p-8 animate-slide-in">
                <div class="flex items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Required Documents</h2>
                </div>
                <div class="flex items-center mb-6">
                    <p class="text-base font-semibold -mt-8 text-gray-900">Reminder: If you need to upload multiple files for a single document, please combine them into one file.</p>
                </div>

                <!--Application Letter-->
                <!--
                <div class="w-full mb-6 border-b border-dashed border-gray-300 pb-4">
                    <div class="flex items-center justify-between w-full">
                        <h3 class="text-gray-700 font-medium">Application Letter</h3>
                        @if(isset($documents['application_letter']) && $documents['application_letter']->status === 'Okay/Confirmed
')
                            <div class="text-green-600 text-sm font-semibold">
                                ✔ This document is already approved.
                            </div>
                        @else
                            <label for="cert-upload-application-letter"
                                class="cert-upload-area inline-flex items-center justify-center border border-gray-300 p-1 rounded cursor-pointer">
                                <span class="material-icons text-5xl {{ !empty($documents['application_letter']) ? 'text-green-500' : 'text-blue-400' }}">
                                    cloud_upload
                                </span>
                            </label>
                            <input type="file" 
                                id="cert-upload-application-letter" 
                                name="cert_uploads[application_letter]" 
                                accept="application/pdf" 
                                class="absolute opacity-0 w-px h-px"
                                {{ empty($documents['application_letter']) ? 'required' : '' }}>
                        @endif
                    </div>
                </div>
            -->
                <!-- Pre Qualifying Exam -->
                <div class="w-full mb-6 border-b border-dashed border-gray-300 pb-4">
                    <div class="flex items-center justify-between w-full">
                        <h3 class="text-gray-700 font-medium">
                            Pre-Qualifying Exam (PQE) result
                            <a href="https://docs.google.com/forms/d/e/1FAIpQLSf7PF9_Chb7_tgLgsKFkYzGV9kDLkpvpw8Dikol8rEEUDu4jA/viewform" target="_blank" class="text-blue-600 hover:underline">
                            (if you don't have PQE click here)
                            </a>
                        </h3>
                        @if(isset($documents['pqe_result']) && $documents['pqe_result']->status === 'Okay/Confirmed
')
                            <div class="text-green-600 text-sm font-semibold">
                                ✔ This document is already approved.
                            </div>
                        @else
                            <label for="cert-upload-pqe-result"
                                class="cert-upload-area inline-flex items-center justify-center border border-gray-300 p-1 rounded cursor-pointer">
                                <span class="material-icons text-5xl {{ !empty($documents['pqe_result']) ? 'text-green-500' : 'text-blue-400' }}">
                                    cloud_upload
                                </span>
                            </label>
                            <input type="file" id="cert-upload-pqe-result" name="cert_uploads[pqe_result]" accept="application/pdf" class="hidden">
                        @endif
                    </div>
                </div>
                <!--Certificate of Eligibility -->
                <div class="w-full mb-6 border-b border-dashed border-gray-300 pb-4">
                    <div class="flex items-center justify-between w-full">
                        <h3 class="text-gray-700 font-medium">Certificate of Eligibility/Board Rating</h3>
                         @if(isset($documents['cert_eligibility']) && $documents['cert_eligibility']->status === 'Okay/Confirmed
')
                            <div class="text-green-600 text-sm font-semibold">
                                ✔ This document is already approved.
                            </div>
                        @else
                            <label for="cert-upload-cert-eligibility"
                                class="cert-upload-area inline-flex items-center justify-center border border-gray-300 p-1 rounded cursor-pointer">
                                <span class="material-icons text-5xl {{ !empty($documents['cert_eligibility']) ? 'text-green-500' : 'text-blue-400' }}">
                                    cloud_upload
                                </span>
                            </label>

                            <input type="file" id="cert-upload-cert-eligibility" name="cert_uploads[cert_eligibility]" accept="application/pdf" class="hidden">
                        @endif
                    </div>
                </div>
                <!-- IPCR -->
                <div class="w-full mb-6 border-b border-dashed border-gray-300 pb-4">
                    <div class="flex items-center justify-between w-full">
                        <h3 class="text-gray-700 font-medium">Certification of Numerical Rating/Performance Rating/IPCR</h3>
                        @if(isset($documents['ipcr']) && $documents['ipcr']->status === 'Okay/Confirmed
')
                            <div class="text-green-600 text-sm font-semibold">
                                ✔ This document is already approved.
                            </div>
                        @else
                            <label for="cert-upload-ipcr"
                                class="cert-upload-area inline-flex items-center justify-center border border-gray-300 p-1 rounded cursor-pointer">
                                <span class="material-icons text-5xl {{ !empty($documents['ipcr']) ? 'text-green-500' : 'text-blue-400' }}">
                                    cloud_upload
                                </span>
                            </label>
                            <input type="file" id="cert-upload-ipcr" name="cert_uploads[ipcr]" accept="application/pdf" class="hidden">
                        @endif
                    </div>
                </div>
                <!-- Non-Acad -->
                <div class="w-full mb-6 border-b border-dashed border-gray-300 pb-4">
                    <div class="flex items-center justify-between w-full">
                        <h3 class="text-gray-700 font-medium">Non-Academic awards received</h3>
                        @if(isset($documents['non_academic']) && $documents['non_academic']->status === 'Okay/Confirmed')
                            <div class="text-green-600 text-sm font-semibold">
                                ✔ This document is already approved.
                            </div>
                        @else
                            <label for="cert-upload-non-academic"
                                class="cert-upload-area inline-flex items-center justify-center border border-gray-300 p-1 rounded cursor-pointer">
                                <span class="material-icons text-5xl {{ !empty($documents['non_academic']) ? 'text-green-500' : 'text-blue-400' }}">
                                    cloud_upload
                                </span>
                            </label>
                            <input type="file" id="cert-upload-non-academic" name="cert_uploads[non_academic]" accept="application/pdf" class="hidden">
                        @endif
                    </div>
                </div>
                <!-- Cert of Training -->
                <div class="w-full mb-6 border-b border-dashed border-gray-300 pb-4">
                    <div class="flex items-center justify-between w-full">
                        <h3 class="text-gray-700 font-medium">Certified/authenticated copy of Certifcates of Training/Participation</h3>
                        @if(isset($documents['cert_training']) && $documents['cert_training']->status === 'Okay/Confirmed')
                            <div class="text-green-600 text-sm font-semibold">
                                ✔ This document is already approved.
                            </div>
                        @else
                            <label for="cert-upload-cert-training"
                                class="cert-upload-area inline-flex items-center justify-center border border-gray-300 p-1 rounded cursor-pointer">
                                <span class="material-icons text-5xl {{ !empty($documents['cert_training']) ? 'text-green-500' : 'text-blue-400' }}">
                                    cloud_upload
                                </span>
                            </label>
                            <input type="file" id="cert-upload-cert-training" name="cert_uploads[cert_training]" accept="application/pdf" class="hidden">
                        @endif
                    </div>
                </div>
                <!-- DO/s -->
                <div class="w-full mb-6 border-b border-dashed border-gray-300 pb-4">
                    <div class="flex items-center justify-between w-full">
                        <h3 class="text-gray-700 font-medium">List of certified photopcopy of duly confirmed Designation Order/s</h3>
                        @if(isset($documents['designation_order']) && $documents['designation_order']->status === 'Okay/Confirmed')
                            <div class="text-green-600 text-sm font-semibold">
                                ✔ This document is already approved.
                            </div>
                        @else
                            <label for="cert-upload-designation-order"
                                class="cert-upload-area inline-flex items-center justify-center border border-gray-300 p-1 rounded cursor-pointer">
                                <span class="material-icons text-5xl {{ !empty($documents['designation_order']) ? 'text-green-500' : 'text-blue-400' }}">
                                    cloud_upload
                                </span>
                            </label>
                            <input type="file" id="cert-upload-designation-order" name="cert_uploads[designation_order]" accept="application/pdf" class="hidden">
                        @endif
                    </div>
                </div>
                <!-- Transcript -->
                <div class="w-full mb-6 border-b border-dashed border-gray-300 pb-4">
                    <div class="flex items-center justify-between w-full">
                        <h3 class="text-gray-700 font-medium">Transcript of Records (Baccalaureate Degree)</h3>
                         @if(isset($documents['transcript_records']) && $documents['transcript_records']->status === 'Okay/Confirmed')
                            <div class="text-green-600 text-sm font-semibold">
                                ✔ This document is already approved.
                            </div>
                        @else
                            <label for="cert-upload-transcript-records"
                                class="cert-upload-area inline-flex items-center justify-center border border-gray-300 p-1 rounded cursor-pointer">
                                <span class="material-icons text-5xl {{ !empty($documents['transcript_records']) ? 'text-green-500' : 'text-blue-400' }}">
                                    cloud_upload
                                </span>
                            </label>
                            <input type="file" id="cert-upload-transcript-records" name="cert_uploads[transcript_records]" 
                                accept="application/pdf" class="absolute opacity-0 w-px h-px"
                                {{ empty($documents['transcript_records']) ? 'required' : '' }}>
                        @endif
                    </div>
                </div>
                <!-- Diploma -->
                <div class="w-full mb-6 border-b border-dashed border-gray-300 pb-4">
                    <div class="flex items-center justify-between w-full">
                        <h3 class="text-gray-700 font-medium">Diploma</h3>
                        @if(isset($documents['photocopy_diploma']) && $documents['photocopy_diploma']->status === 'Okay/Confirmed')
                            <div class="text-green-600 text-sm font-semibold">
                                ✔ This document is already approved.
                            </div>
                        @else
                            <label for="cert-upload-photocopy-diploma"
                                class="cert-upload-area inline-flex items-center justify-center border border-gray-300 p-1 rounded cursor-pointer">
                                <span class="material-icons text-5xl {{ !empty($documents['photocopy_diploma']) ? 'text-green-500' : 'text-blue-400' }}">
                                    cloud_upload
                                </span>
                            </label>
                            <input type="file" id="cert-upload-photocopy-diploma" name="cert_uploads[photocopy_diploma]" 
                                accept="application/pdf" class="absolute opacity-0 w-px h-px"
                                {{ empty($documents['photocopy_diploma']) ? 'required' : '' }}>
                        @endif
                    </div>
                </div>
                <!-- Grade of Masteral/Docotrate  -->
                <div class="w-full mb-6 border-b border-dashed border-gray-300 pb-4">
                    <div class="flex items-center justify-between w-full">
                        <h3 class="text-gray-700 font-medium">Certified photocopy of Certificate of Grades with Masteral/Doctorate units earned</h3>
                        @if(isset($documents['grade_masteraldoctorate']) && $documents['grade_masteraldoctorate']->status === 'Okay/Confirmed')
                            <div class="text-green-600 text-sm font-semibold">
                                ✔ This document is already approved.
                            </div>
                        @else
                            <label for="cert-upload-grade-masteraldoctorate"
                                class="cert-upload-area inline-flex items-center justify-center border border-gray-300 p-1 rounded cursor-pointer">
                                <span class="material-icons text-5xl {{ !empty($documents['grade_masteraldoctorate']) ? 'text-green-500' : 'text-blue-400' }}">
                                    cloud_upload
                                </span>
                            </label>

                            <input type="file" id="cert-upload-grade-masteraldoctorate" name="cert_uploads[grade_masteraldoctorate]" accept="application/pdf" class="hidden">
                        @endif
                    </div>
                </div>
                <!-- TOR -->
                <div class="w-full mb-6 border-b border-dashed border-gray-300 pb-4">
                    <div class="flex items-center justify-between w-full">
                        <h3 class="text-gray-700 font-medium">Certified photopcopy of TOR with Masteral/Doctorate Degree</h3>
                        @if(isset($documents['tor_masteraldoctorate']) && $documents['tor_masteraldoctorate']->status === 'Okay/Confirmed')
                            <div class="text-green-600 text-sm font-semibold">
                                ✔ This document is already approved.
                            </div>
                        @else
                            <label for="cert-upload-tor-masteraldoctorate"
                                class="cert-upload-area inline-flex items-center justify-center border border-gray-300 p-1 rounded cursor-pointer">
                                <span class="material-icons text-5xl {{ !empty($documents['tor_masteraldoctorate']) ? 'text-green-500' : 'text-blue-400' }}">
                                    cloud_upload
                                </span>
                            </label>
                            <input type="file" id="cert-upload-tor-masteraldoctorate" name="cert_uploads[tor_masteraldoctorate]" accept="application/pdf" class="hidden">
                        @endif
                    </div>
                </div>
                <!-- Cert of Employment -->
                <div class="w-full mb-6 border-b border-dashed border-gray-300 pb-4">
                    <div class="flex items-center justify-between w-full">
                        <h3 class="text-gray-700 font-medium">Certificate of Employment (if any)</h3>
                        @if(isset($documents['cert_employment']) && $documents['cert_employment']->status === 'Okay/Confirmed')
                            <div class="text-green-600 text-sm font-semibold">
                                ✔ This document is already approved.
                            </div>
                        @else
                            <label for="cert-upload-cert-employment"
                                class="cert-upload-area inline-flex items-center justify-center border border-gray-300 p-1 rounded cursor-pointer">
                                <span class="material-icons text-5xl {{ !empty($documents['cert_employment']) ? 'text-green-500' : 'text-blue-400' }}">
                                    cloud_upload
                                </span>
                            </label>
                            <input type="file" id="cert-upload-cert-employment" name="cert_uploads[cert_employment]" accept="application/pdf" class="hidden">
                        @endif
                    </div>
                </div>
                <!-- Other Docs -->
                <div class="w-full mb-6 border-b border-dashed border-gray-300 pb-4">
                    <div class="flex items-center justify-between w-full">
                        <h3 class="text-gray-700 font-medium">Other documents submitted</h3>
                        @if(isset($documents['other_documents']) && $documents['other_documents']->status === 'Okay/Confirmed')
                            <div class="text-green-600 text-sm font-semibold">
                                ✔ This document is already approved.
                            </div>
                        @else
                            <label for="cert-upload-other-documents"
                                class="cert-upload-area inline-flex items-center justify-center border border-gray-300 p-1 rounded cursor-pointer">
                                <span class="material-icons text-5xl {{ !empty($documents['other_documents']) ? 'text-green-500' : 'text-blue-400' }}">
                                    cloud_upload
                                </span>
                            </label>
                            <input type="file" id="cert-upload-other-documents" name="cert_uploads[other_documents]" accept="application/pdf" class="hidden">
                        @endif
                    </div>
                </div>

            <!-- Declaration Section -->
            <section class="bg-white rounded-2xl shadow-xl p-8 animate-slide-in">
                <div class="flex items-center mb-6">
                    <span class="material-icons text-blue-600 mr-3 text-3xl">verified_user</span>
                    <h2 class="text-2xl font-bold text-gray-900">Declaration</h2>
                </div>

                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg mb-6">
                    <div class="flex">
                        <div class="flex-1">
                            <p class="text-sm text-yellow-800 leading-relaxed">
                                42. I declare under oath that I have personally accomplished this Personal Data Sheet which is a true, correct and complete statement pursuant to the provisions of pertinent laws, rules and regulations of the Republic of the Philippines. I authorize the agency head/authorized representative to verify/validate the contents stated herein.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="space-y-4">
                    <label class="flex items-start cursor-pointer hover:bg-gray-50 p-3 rounded-lg transition-colors">
                        <input type="checkbox" name="declaration" class="mt-1 mr-3" required>
                        <span class="text-gray-700">
                            I certify that all information provided in this form is true and correct to the best of my knowledge.
                        </span>
                    </label>

                    <label class="flex items-start cursor-pointer hover:bg-gray-50 p-3 rounded-lg transition-colors">
                        <input type="checkbox" name="consent" class="mt-1 mr-3" required>
                        <span class="text-gray-700">
                            I consent to the collection and processing of my personal data in accordance with the Data Privacy Act of 2012.
                        </span>
                    </label>

                    <label class="flex items-start cursor-pointer hover:bg-gray-50 p-3 rounded-lg transition-colors">
                         <input type="checkbox" name="confirmation" class="mt-1 mr-3" required>
                         <span class="text-gray-700">
                              I confirm that all uploaded documents are correct, complete, and accurately represent the required information.
                         </span>
                    </label>
                </div>

            </section>

            <!-- Navigation and Submit -->
            <div class="flex justify-between items-center mt-8">
                <button type="button" onclick="window.location.href='{{ route('display_c4', ['simple' => 1]) }}'" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors duration-200 flex items-center">
                    <span class="material-icons mr-2">arrow_back</span>
                    Previous
                </button>
                <button type="submit" class="px-8 py-3 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition-colors duration-200 flex items-center shadow-lg hover:shadow-xl">
                    <span class="material-icons mr-2">check_circle</span>
                    Submit Application
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
@endsection