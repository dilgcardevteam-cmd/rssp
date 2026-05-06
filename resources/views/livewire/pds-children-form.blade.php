<div class="mb-6">
    <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
        <span class="material-icons text-sm mr-2 text-blue-500">child_care</span>
        23. NAME OF CHILDREN (Write full name and list all)
    </h3>
    
    @foreach ($children as $index => $child)
        <div wire:key="child-{{ $index }}" class="border-2 border-gray-200 rounded-lg p-4 mb-4">
            <!-- Header with Remove Button -->
            <div class="flex justify-between items-center mb-4">
                <span class="text-sm font-medium text-gray-600">Child {{ $index + 1 }}</span>
                <button
                    type="button"
                    wire:click.prevent="removeChild({{ $index }})"
                    class="flex items-center justify-center w-8 h-8 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-full transition-all duration-200"
                    title="Remove Child"
                >
                    <span class="text-lg">x</span>
                </button>
            </div>
            
            <!-- Form Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Full Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">FULL NAME</label>
                    <input
                        name="children[{{ $index }}][name]"
                        type="text"
                        wire:model.lazy="children.{{ $index }}.name"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg text-sm md:text-base focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                        placeholder="Full Name (Write full name and list all)">
                </div>
                <!-- Date of Birth -->
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">DATE OF BIRTH</label>
                    <input
                        name="children[{{ $index }}][dob]"
                        type="date"
                        wire:model.lazy="children.{{ $index }}.dob"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition text-sm md:text-base edu-date"
                        max="{{ now()->format('Y-m-d') }}"
                        data-child-dob="true">
                </div>
            </div>
        </div>
    @endforeach

    <!-- Add Child Button - Right Aligned -->
    <div class="w-full flex justify-end">
        <button
            type="button"
            wire:click.prevent="addEmptyChild"
            class="use-loader text-white bg-[#002C76] border border-[#002C76] font-bold py-1 px-4 rounded-md text-sm 
                transition-all duration-300 hover:scale-105 hover:bg-white 
                hover:text-[#002C76] hover:shadow-md inline-flex items-center gap-2"
            >
            <span class="text-lg">+</span>
            Add Another Child
        </button>
    </div>
</div>
