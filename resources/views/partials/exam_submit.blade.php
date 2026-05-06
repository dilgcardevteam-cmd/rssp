<!-- exam_submit.blade.php -->
<div x-data="{ showSubmitConfirm: false }" class="inline">
    <!-- Trigger Button -->
    <button 
        @click="allowFocusLoss = true; showSubmitConfirm = true"
        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-semibold transition">
        Submit
    </button>

    <!-- Modal Overlay -->
    <div x-show="showSubmitConfirm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:leave="transition ease-in duration-200"
         class="fixed inset-0 bg-black/60 backdrop-blur-md flex items-center justify-center z-50"
         style="display: none;"
         @keydown.escape.window="allowFocusLoss = false; showSubmitConfirm = false">

        <!-- Modal Box -->
        <div class="bg-white p-8 rounded-2xl max-w-md w-full shadow-2xl relative">
            <!-- Close Button (optional X) -->
            <button 
                @click="allowFocusLoss = false; showSubmitConfirm = false"
                class="absolute top-4 right-4 text-gray-400 text-xl font-bold hover:text-red-600">
                &times;
            </button>

            <!-- Title -->
            <h2 class="text-2xl font-extrabold text-[#002C76] text-center mb-2">Submission</h2>

            <!-- Content -->
            <p class="text-gray-700 text-sm text-center mb-6">
                Click <span class="font-semibold text-[#0D2B70]">Submit</span> to finalize your answers.
            </p>

            <!-- Buttons -->
            <div class="flex justify-center gap-4">
                <!-- Cancel Button -->
                <button 
                    @click="allowFocusLoss = false; showSubmitConfirm = false"
                    class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-full font-semibold transition">
                    Cancel
                </button>
            <!-- Submit Button -->
            <button 
                @click="window.isSubmitting = true; document.getElementById('exam-form').submit()"
                class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-full font-semibold transition">
                Submit
            </button>

            </div>
        </div>
    </div>
</div>
