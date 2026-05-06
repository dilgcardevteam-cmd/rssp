<section class="mb-10">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-semibold text-gray-700">{{ $education_type_meta['title'] }}</h3>
        <button
            wire:click="addRow"
            id="add-{{ $education_type }}-btn"
            type="button"
            class="use-loader text-green-600 border border-green-400 font-bold py-2 px-4 rounded-md text-sm 
                   transition-all duration-300 hover:scale-105 hover:bg-green-400 hover:text-white hover:shadow-md 
                   inline-flex items-center gap-2">
            <span class="text-lg">+</span>
            Add {{ ucfirst($education_type) }}
        </button>
    </div>

    <!-- Empty‑state message -->
    @if (empty($education_data))
    <p id="vocational-empty" class="text-gray-500 italic text-center">No {{ $education_type }} records yet.</p>
    @else
    <!-- Dynamic rows get injected here -->
    @foreach ($education_data as $index => $data)

    <div id="vocational-container" class="space-y-6">
        <div class="entry-card bg-gray-50 rounded-lg p-6 card-hover animate-fade-in">
            <div class="flex justify-between items-start mb-4">
                <h4 class="text-lg font-medium text-gray-700">{{ $education_type_meta['title'] }} #{{ $index+1 }}</h4>
                <button wire:click="removeRow({{ $index }})" type="button" class="remove-entry text-red-500 hover:text-red-700">
                    <span class="material-icons">close</span>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- From / To -->
                <div class="relative">
                    <input type="month" name="{{ $education_type }}[{{ $index }}][from]"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                        value="{{ $education_data[$index]['from'] }}">
                    <label class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">From</label>
                </div>
                <div class="relative">
                    <input type="month" name="{{ $education_type }}[{{ $index }}][to]"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                        value="{{ $education_data[$index]['to'] }}">
                    <label class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">To</label>
                </div>

                <!-- School / Program -->
                <div class="relative col-span-2">
                    <input type="text" name="{{ $education_type }}[{{ $index }}][school]" placeholder=" "
                        class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer"
                        value="{{ $education_data[$index]['school'] }}">
                    <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">School Name</label>
                </div>
                <div class="relative col-span-2">
                    <input type="text" name="{{ $education_type }}[{{ $index }}][basic]" placeholder=" "
                        class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer"
                        value="{{ $education_data[$index]['basic'] }}">
                    <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Degree / Course</label>
                </div>

                <!-- Units / Year / Honors -->
                <div class="relative col-span-2">
                    <input type="text" name="{{ $education_type }}[{{ $index }}][earned]" placeholder=" "
                        class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer"
                        value="{{ $education_data[$index]['earned'] }}">
                    <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Units Earned</label>
                </div>
                <div class="relative col-span-2">
                    <input type="text" name="{{ $education_type }}[{{ $index }}][year_graduated]" placeholder=" "
                        class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer"
                        value="{{ $education_data[$index]['year_graduated'] }}">
                    <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Year Graduated</label>
                </div>
                <div class="relative col-span-2">
                    <input type="text" name="{{ $education_type }}[{{ $index }}][academic_honors]" placeholder=" "
                        class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer"
                        value="{{ $education_data[$index]['academic_honors'] }}">
                    <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Honors Received</label>
                </div>
            </div>
        </div>
    </div>
    @endforeach
    @endif
</section>
