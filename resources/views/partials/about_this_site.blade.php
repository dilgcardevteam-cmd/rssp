<div x-data="{ showAbout: false }" class="inline-block">
    <!-- Trigger Button -->
    <button @click="showAbout = true"
        class="border-2 border-[#002C76] font-montserrat text-xl text-black-300 rounded-lg flex items-center gap-3 px-6 py-4 hover:bg-[#002C76] hover:text-white transition">
        <i data-feather="info" class="w-6 h-6 mt-1"></i> ABOUT THIS SITE
    </button>

    <!-- Modal Overlay -->
    <div 
        x-show="showAbout"
        @click.outside="showAbout = false"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-90"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-90"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
        role="dialog" aria-modal="true" style="display: none;"
    >
        <!-- Modal Box -->
        <div class="relative w-full max-w-2xl rounded-2xl bg-white p-8 md:p-10 shadow-2xl text-gray-800">
            
            <!-- Close Icon -->
            <button @click="showAbout = false"
                class="absolute top-4 right-5 text-[#002C76] hover:text-red-500 text-2xl font-bold transition duration-200"
                aria-label="Close modal">
                ✖
            </button>

            <!-- Header -->
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-[#002C76] text-white mb-4">
                    <i data-feather="info" class="w-6 h-6"></i>
                </div>
                <h2 class="text-2xl md:text-3xl font-extrabold text-[#002C76] tracking-tight">ABOUT THIS SITE</h2>
                <p class="text-base font-medium text-gray-600 italic mt-2">
                    Isa ka bang <span class="text-[#002C76] font-bold">MATINO</span>, <span class="text-[#002C76] font-bold">MAHUSAY</span>, at <span class="text-[#002C76] font-bold">MAAASAHAN</span> na manggagawang Pilipino?
                </p>
            </div>

            <!-- Description -->
            <p class="text-sm md:text-base text-gray-700 leading-relaxed text-justify mb-6">
                This platform streamlines the job application process for aspiring candidates in the <strong>DILG-CAR</strong> region. It offers a seamless and intuitive experience for submitting applications, tracking status, and accessing important employment information.
            </p>

            <!-- Development Team -->
            <div class="items-center justify-center text-center">
                <h3 class="text-base md:text-lg font-bold text-[#002C76] mb-2">DEVELOPMENT TEAM</h3>
                <p class="text-sm md:text-base text-gray-700 font-medium mb-2 leading-relaxed">
                    Saint Louis College<br>
                    City of San Fernando, La Union<br>
                    BS Information Technology <br>
                    On-the-Job Training (OJT) Trainees<br>
                    <span class="text-gray-500 text-sm">(Februay 2026 – May 2026)</span>
                </p>

                <ul class="space-y-1 text-sm md:text font-semibold text-blue-600">

                    <li><a target="_blank">UBUNGEN, AB'CD</a></li>
                    <li><a target="_blank">VALDEZ, JOHN ERROL P.</a></li>
                    <li><a target="_blank">VISAYA, CARL LAURENZ</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
/* Mobile optimizations - Desktop remains unchanged */
@media (max-width: 640px) {
    /* Container adjustments */
    [x-data*="showAbout"] {
        display: block !important;
        width: 100% !important;
    }
    
    /* Trigger button mobile styling */
    [x-data*="showAbout"] > button {
        width: 100% !important;
        font-size: 16px !important;
        padding: 12px 16px !important;
        text-align: center !important;
        justify-content: center !important;
        border-radius: 12px !important;
        line-height: 1.4 !important;
    }
    
    [x-data*="showAbout"] > button i {
        width: 20px !important;
        height: 20px !important;
        margin-top: 0 !important;
    }
    
    /* Modal overlay mobile adjustments */
    [x-show="showAbout"] {
        padding: 12px !important;
        align-items: flex-start !important;
        padding-top: 40px !important;
    }
    
    /* Modal content mobile styling */
    [x-show="showAbout"] > div {
        max-width: none !important;
        width: 100% !important;
        padding: 20px !important;
        border-radius: 16px !important;
        max-height: calc(100vh - 80px) !important;
        overflow-y: auto !important;
    }
    
    /* Close button mobile positioning */
    [x-show="showAbout"] > div > button {
        top: 12px !important;
        right: 16px !important;
        font-size: 20px !important;
        padding: 4px 8px !important;
    }
    
    /* Header section mobile styling */
    [x-show="showAbout"] .text-center.mb-6 {
        margin-bottom: 20px !important;
    }
    
    [x-show="showAbout"] .w-12.h-12 {
        width: 40px !important;
        height: 40px !important;
        margin-bottom: 12px !important;
    }
    
    [x-show="showAbout"] .w-12.h-12 i {
        width: 20px !important;
        height: 20px !important;
    }
    
    [x-show="showAbout"] h2 {
        font-size: 20px !important;
        line-height: 1.3 !important;
        margin-bottom: 8px !important;
    }
    
    [x-show="showAbout"] .text-center.mb-6 p {
        font-size: 14px !important;
        line-height: 1.4 !important;
        margin-top: 8px !important;
        padding: 0 8px !important;
    }
    
    /* Description paragraph mobile styling */
    [x-show="showAbout"] > div > p {
        font-size: 14px !important;
        line-height: 1.6 !important;
        text-align: left !important;
        margin-bottom: 20px !important;
    }
    
    /* Development team section mobile styling */
    [x-show="showAbout"] .items-center.justify-center.text-center {
        text-align: center !important;
    }
    
    [x-show="showAbout"] .items-center.justify-center.text-center h3 {
        font-size: 16px !important;
        margin-bottom: 12px !important;
    }
    
    [x-show="showAbout"] .items-center.justify-center.text-center > p {
        font-size: 12px !important;
        line-height: 1.5 !important;
        margin-bottom: 16px !important;
        padding: 0 4px !important;
    }
    
    [x-show="showAbout"] .items-center.justify-center.text-center > p span {
        font-size: 11px !important;
    }
    
    /* Team member list mobile styling */
    [x-show="showAbout"] ul {
        font-size: 13px !important;
        line-height: 1.4 !important;
        padding: 0 8px !important;
    }
    
    [x-show="showAbout"] ul li {
        margin-bottom: 8px !important;
        word-break: break-word !important;
    }
    
    [x-show="showAbout"] ul li a {
        display: inline-block !important;
        padding: 2px 0 !important;
    }
}

/* Extra small devices */
@media (max-width: 375px) {
    [x-data*="showAbout"] > button {
        font-size: 14px !important;
        padding: 10px 14px !important;
    }
    
    [x-data*="showAbout"] > button i {
        width: 18px !important;
        height: 18px !important;
    }
    
    [x-show="showAbout"] {
        padding: 8px !important;
        padding-top: 30px !important;
    }
    
    [x-show="showAbout"] > div {
        padding: 16px !important;
        max-height: calc(100vh - 60px) !important;
    }
    
    [x-show="showAbout"] h2 {
        font-size: 18px !important;
    }
    
    [x-show="showAbout"] .text-center.mb-6 p {
        font-size: 12px !important;
        padding: 0 4px !important;
    }
    
    [x-show="showAbout"] > div > p {
        font-size: 13px !important;
    }
    
    [x-show="showAbout"] .items-center.justify-center.text-center > p {
        font-size: 11px !important;
    }
    
    [x-show="showAbout"] ul {
        font-size: 12px !important;
    }
}

/* Landscape mobile orientation */
@media (max-width: 640px) and (orientation: landscape) {
    [x-show="showAbout"] {
        padding-top: 20px !important;
        align-items: center !important;
    }
    
    [x-show="showAbout"] > div {
        max-height: calc(100vh - 40px) !important;
        padding: 16px !important;
    }
    
    [x-show="showAbout"] .text-center.mb-6 {
        margin-bottom: 16px !important;
    }
    
    [x-show="showAbout"] > div > p {
        margin-bottom: 16px !important;
    }
    
    [x-show="showAbout"] .items-center.justify-center.text-center > p {
        margin-bottom: 12px !important;
    }
}

/* Tablet portrait mode */
@media (min-width: 641px) and (max-width: 768px) {
    [x-show="showAbout"] > div {
        max-width: 90% !important;
        margin: 0 auto !important;
    }
}
</style>

<script>
// Initialize Feather icons
if (typeof feather !== 'undefined') {
    feather.replace();
}

// Handle mobile modal scroll prevention for About This Site
document.addEventListener('DOMContentLoaded', function() {
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                const modal = mutation.target;
                if (modal.hasAttribute('x-show') && modal.getAttribute('x-show') === 'showAbout') {
                    const isVisible = modal.style.display !== 'none';
                    if (window.innerWidth <= 640) {
                        document.body.style.overflow = isVisible ? 'hidden' : '';
                    }
                }
            }
        });
    });
    
    const aboutModals = document.querySelectorAll('[x-show="showAbout"]');
    aboutModals.forEach(modal => {
        observer.observe(modal, { attributes: true, attributeFilter: ['style'] });
    });
    
    // Handle modal close on mobile back button
    window.addEventListener('popstate', function(event) {
        const aboutModal = document.querySelector('[x-show="showAbout"]');
        if (aboutModal && aboutModal.style.display !== 'none' && window.innerWidth <= 640) {
            // Trigger Alpine.js to close modal
            const button = document.querySelector('[x-data*="showAbout"] > button');
            if (button && button.__x) {
                button.__x.$data.showAbout = false;
            }
        }
    });
});
</script>