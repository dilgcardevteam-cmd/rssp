<!-- Logout Modal -->
<div 
    x-show="showLogoutModal" 
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-100"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-md px-4"
    style="display: none;"
>
    <div 
        @click.outside="showLogoutModal = false"
        class="bg-white w-full max-w-sm md:max-w-md rounded-xl p-6 shadow-lg"
    >
        <h2 class="text-lg font-bold text-[#002C76] mb-3 text-center">Confirm Logout</h2>
        <p class="text-gray-700 text-sm mb-6 text-center">Are you sure you want to log out?</p>
        <div class="flex flex-col sm:flex-row justify-center sm:justify-end gap-3">
            <button 
                @click="showLogoutModal = false" 
                class="w-full sm:w-auto px-4 py-2 text-sm font-semibold text-gray-600 bg-gray-100 rounded hover:bg-gray-200"
            >
                Cancel
            </button>
            <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto">
                @csrf
                <button 
                    type="submit" 
                    class="w-full sm:w-auto px-4 py-2 bg-[#C9282D] text-white text-sm font-semibold rounded hover:bg-red-700"
                >
                    Log Out
                </button>
            </form>
        </div>
    </div>
</div>
