<div x-data="{ showNotice: false }" class="inline">
    <button @click="showNotice = true"
        class="border-2 border-[#002C76] font-montserrat text-xl text-black-300 rounded-lg flex items-center gap-3 px-6 py-4 hover:bg-[#002C76] hover:text-white transition">
        <i data-feather="shield" class="w-6 h-6 mt-1"></i> DATA PRIVACY NOTICE
    </button>
    <div x-show="showNotice" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        x-transition:enter="transition ease-out duration-300" x-transition:leave="transition ease-in duration-200"
        style="display: none;">
        <div class="bg-white p-6 rounded-xl max-w-2xl w-full shadow-lg relative">
            
            <!-- Header -->
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-[#002C76] text-white mb-4">
                    <i data-feather="info" class="w-6 h-6"></i>
                </div>
                <h2 class="text-2xl md:text-3xl font-extrabold text-red-600 tracking-tight">DATA PRIVACY NOTICE</h2>
                <p class="text-base font-medium text-gray-600 mt-2">
                   The DILG-CAR collects your personal data in the forms you may be required to fill out and/or submit in relation to your application for the posted job vacancy to provide verifiable evidence and documentation that the information you provided is true and correct. Your information will be stored in our database and/or secured records locker before being permanently erased from our records. 
                </p>
                <p>
                    Should you wish to withdraw your consent, please contact the DILG-CAR's Human Resource Personnel. If you wish to report any unlawful processing of data for this job application, please contact DILG Data Protection Officer at <span class='font-bold'>dpo.dilg@gmail.com. </span>
                </p>
            </div>
            <button @click="showNotice = false"
                class="absolute bottom-4 right-6 bg-yellow-400 hover:bg-yellow-500 text-black font-semibold text-sm px-4 py-1 rounded-full mt-4">
                ✖ CLOSE
            </button>
        </div>
    </div>
</div>

<style>
/* Mobile optimizations - Desktop remains unchanged */
@media (max-width: 640px) {
    /* Container adjustments */
    [x-data*="showNotice"] {
        display: block !important;
        width: 100% !important;
    }
    
    /* Trigger button mobile styling */
    [x-data*="showNotice"] button:first-child {
        width: 100% !important;
        font-size: 16px !important;
        padding: 12px 16px !important;
        text-align: center !important;
        justify-content: center !important;
        border-radius: 12px !important;
        line-height: 1.4 !important;
    }
    
    [x-data*="showNotice"] button:first-child i {
        width: 20px !important;
        height: 20px !important;
        margin-top: 0 !important;
    }
    
    /* Modal container mobile adjustments */
    [x-show="showNotice"] {
        padding: 16px !important;
        align-items: flex-start !important;
        padding-top: 60px !important;
    }
    
    /* Modal content mobile styling */
    [x-show="showNotice"] > div {
        max-width: none !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 20px !important;
        border-radius: 16px !important;
        max-height: calc(100vh - 120px) !important;
        overflow-y: auto !important;
    }
    
    /* Header section mobile styling */
    [x-show="showNotice"] .text-center.mb-6 {
        margin-bottom: 20px !important;
    }
    
    [x-show="showNotice"] .w-12.h-12 {
        width: 40px !important;
        height: 40px !important;
        margin-bottom: 12px !important;
    }
    
    [x-show="showNotice"] .w-12.h-12 i {
        width: 20px !important;
        height: 20px !important;
    }
    
    [x-show="showNotice"] h2 {
        font-size: 20px !important;
        line-height: 1.3 !important;
        margin-bottom: 8px !important;
    }
    
    [x-show="showNotice"] .text-center.mb-6 p {
        font-size: 14px !important;
        line-height: 1.4 !important;
        margin-top: 8px !important;
        padding: 0 8px !important;
    }
    
    /* Description paragraph mobile styling */
    [x-show="showNotice"] > div > p {
        font-size: 14px !important;
        line-height: 1.6 !important;
        text-align: left !important;
        margin-bottom: 20px !important;
    }
    
    /* Development team section mobile styling */
    [x-show="showNotice"] .items-center.justify-center.text-center {
        text-align: center !important;
        margin-bottom: 50px !important;
    }
    
    [x-show="showNotice"] .items-center.justify-center.text-center h3 {
        font-size: 16px !important;
        margin-bottom: 12px !important;
    }
    
    [x-show="showNotice"] .items-center.justify-center.text-center > p {
        font-size: 12px !important;
        line-height: 1.5 !important;
        margin-bottom: 16px !important;
        padding: 0 4px !important;
    }
    
    [x-show="showNotice"] .items-center.justify-center.text-center > p span {
        font-size: 11px !important;
    }
    
    /* Team member list mobile styling */
    [x-show="showNotice"] ul {
        font-size: 13px !important;
        line-height: 1.4 !important;
        padding: 0 8px !important;
    }
    
    [x-show="showNotice"] ul li {
        margin-bottom: 8px !important;
        word-break: break-word !important;
    }
    
    [x-show="showNotice"] ul li a {
        display: inline-block !important;
        padding: 2px 0 !important;
    }
    
    /* Close button mobile positioning */
    [x-show="showNotice"] button:last-child {
        position: relative !important;
        bottom: auto !important;
        right: auto !important;
        width: 100% !important;
        margin-top: 16px !important;
        padding: 12px 16px !important;
        font-size: 14px !important;
        border-radius: 12px !important;
        text-align: center !important;
    }
}

/* Extra small devices */
@media (max-width: 375px) {
    [x-data*="showNotice"] button:first-child {
        font-size: 14px !important;
        padding: 10px 14px !important;
    }
    
    [x-data*="showNotice"] button:first-child i {
        width: 18px !important;
        height: 18px !important;
    }
    
    [x-show="showNotice"] {
        padding: 12px !important;
        padding-top: 40px !important;
    }
    
    [x-show="showNotice"] > div {
        padding: 16px !important;
    }
    
    [x-show="showNotice"] h2 {
        font-size: 18px !important;
    }
    
    [x-show="showNotice"] .text-center.mb-6 p {
        font-size: 12px !important;
        padding: 0 4px !important;
    }
    
    [x-show="showNotice"] > div > p {
        font-size: 13px !important;
    }
    
    [x-show="showNotice"] .items-center.justify-center.text-center > p {
        font-size: 11px !important;
    }
    
    [x-show="showNotice"] ul {
        font-size: 12px !important;
    }
}

/* Landscape mobile orientation */
@media (max-width: 640px) and (orientation: landscape) {
    [x-show="showNotice"] {
        padding-top: 20px !important;
        align-items: center !important;
    }
    
    [x-show="showNotice"] > div {
        max-height: calc(100vh - 40px) !important;
        padding: 16px !important;
    }
    
    [x-show="showNotice"] .text-center.mb-6 {
        margin-bottom: 16px !important;
    }
    
    [x-show="showNotice"] > div > p {
        margin-bottom: 16px !important;
    }
    
    [x-show="showNotice"] .items-center.justify-center.text-center {
        margin-bottom: 30px !important;
    }
}

/* Tablet portrait mode */
@media (min-width: 641px) and (max-width: 768px) {
    [x-show="showNotice"] > div {
        max-width: 600px !important;
        margin: 0 20px !important;
    }
}
</style>

<script>
// Initialize Feather icons
if (typeof feather !== 'undefined') {
    feather.replace();
}

// Handle mobile modal scroll prevention for Data Privacy
document.addEventListener('DOMContentLoaded', function() {
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                const modal = mutation.target;
                if (modal.hasAttribute('x-show') && modal.getAttribute('x-show') === 'showNotice') {
                    const isVisible = modal.style.display !== 'none';
                    if (window.innerWidth <= 640) {
                        document.body.style.overflow = isVisible ? 'hidden' : '';
                    }
                }
            }
        });
    });
    
    const privacyModals = document.querySelectorAll('[x-show="showNotice"]');
    privacyModals.forEach(modal => {
        observer.observe(modal, { attributes: true, attributeFilter: ['style'] });
    });
});
</script>