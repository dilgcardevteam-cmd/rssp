<!-- exam_timesup.blade.php -->
<div x-show="showTimesUp"
     x-transition:enter="transition ease-out duration-300"
     x-transition:leave="transition ease-in duration-200"
     class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
     style="display: none;">
    <div class="bg-white p-8 rounded-2xl max-w-md w-full shadow-2xl text-center">
        <h2 class="text-2xl font-extrabold text-[#002C76] mb-4">Time's Up!</h2>
        <p class="text-gray-700 mb-6">Your exam time has ended. Please submit your answers.</p>
        <button type="button"
            @click="if (!window.isSubmitting && window.confirm('Submit your exam now? This action cannot be undone.')) { window.prepareSubmit(); }"
            class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-full font-semibold transition">
            Submit
        </button>
    </div>
</div>
