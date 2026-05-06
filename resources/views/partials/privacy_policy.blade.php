<div x-data="{ showNotice: false }" class="inline">
    <button @click="showNotice = true"
        class="border-2 border-[#002C76] font-montserrat text-xl text-black-300 rounded-lg flex items-center gap-3 px-6 py-4 hover:bg-[#002C76] hover:text-white transition">
        <i data-feather="lock" class="w-6 h-6 mt-1"></i> PRIVACY POLICY
    </button>
    <div x-show="showNotice" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        x-transition:enter="transition ease-out duration-300" x-transition:leave="transition ease-in duration-200"
        style="display: none;">
        <div class="bg-white p-6 rounded-xl max-w-lg w-full shadow-lg relative">
            <h2 class="text-xl font-bold text-center text-red-600 mb-4">PRIVACY POLICY</h2>
            <p class="text-sm text-gray-700 leading-relaxed mb-4">
                The DILG-CAR collects your personal data to verify submitted job application documents. Your data will
                be stored securely and erased permanently after the process.
            </p>
            <p class="text-sm text-gray-700 leading-relaxed mb-6">
                For concerns, contact the HR or the Data Protection Officer at <strong>dpo.dilg@gmail.com</strong>.
            </p>
            <button @click="showNotice = false"
                class="absolute bottom-4 right-6 bg-yellow-400 hover:bg-yellow-500 text-black font-semibold text-sm px-4 py-1 rounded-full">
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
    
    /* Modal header */
    [x-show="showNotice"] h2 {
        font-size: 18px !important;
        line-height: 1.3 !important;
        margin-bottom: 16px !important;
    }
    
    /* Modal text content */
    [x-show="showNotice"] p {
        font-size: 14px !important;
        line-height: 1.6 !important;
        margin-bottom: 16px !important;
    }
    
    [x-show="showNotice"] p:last-of-type {
        margin-bottom: 50px !important;
        padding-right: 0 !important;
        word-break: break-word !important;
    }
    
    [x-show="showNotice"] p strong {
        word-break: break-all !important;
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
        font-size: 16px !important;
    }
    
    [x-show="showNotice"] p {
        font-size: 13px !important;
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
    }
    
    [x-show="showNotice"] p:last-of-type {
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

// Handle mobile modal scroll prevention for Privacy Policy
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