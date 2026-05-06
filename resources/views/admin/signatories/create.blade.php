@extends('layout.admin')

@section('content')
<div class="w-full space-y-6 font-montserrat" x-data="logTable()">
    <div class="">
        <h1 class="flex items-center gap-3 w-full border-b border-[#0D2B70] text-white text-4xl font-montserrat py-2 tracking-wide select-none">
            <span class="whitespace-nowrap text-[#0D2B70]">Set Up Regional Director</span>
        </h1>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded relative z-50">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="signatory-form" action="{{ route('signatories.store') }}" method="POST" class="bg-white rounded-lg shadow px-6 py-4 mt-4 space-y-6">
            @csrf

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">First Name <span class="text-red-500">*</span></label>
                    <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#002C76] focus:border-transparent @error('first_name') border-red-500 @enderror" required>
                    @error('first_name')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="middle_name" class="block text-sm font-medium text-gray-700 mb-2">Middle Name</label>
                    <input type="text" id="middle_name" name="middle_name" value="{{ old('middle_name') }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#002C76] focus:border-transparent">
                    @error('middle_name')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Last Name <span class="text-red-500">*</span></label>
                    <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#002C76] focus:border-transparent @error('last_name') border-red-500 @enderror" required>
                    @error('last_name')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>



            <div>
                <label for="designation" class="block text-sm font-medium text-gray-700 mb-2">Designation <span class="text-red-500">*</span></label>
                <input type="text" id="designation" name="designation" value="{{ old('designation') }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#002C76] focus:border-transparent @error('designation') border-red-500 @enderror" required>
                @error('designation')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="office" class="block text-sm font-medium text-gray-700 mb-2">Office <span class="text-red-500">*</span></label>
                <input type="text" id="office" name="office" value="{{ old('office') }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#002C76] focus:border-transparent @error('office') border-red-500 @enderror" required>
                @error('office')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="office_address" class="block text-sm font-medium text-gray-700 mb-2">Office Address <span class="text-red-500">*</span></label>
                <textarea id="office_address" name="office_address" rows="3"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#002C76] focus:border-transparent @error('office_address') border-red-500 @enderror" required>{{ old('office_address') }}</textarea>
                @error('office_address')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex justify-end gap-4">
                <a href="{{ route('signatories.index') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button 
                    @click.prevent="$dispatch('open-confirm-modal')"
                    type="submit"
                    class="px-6 py-2 bg-[#002C76] text-white rounded-lg hover:bg-blue-900 transition-colors">
                    Save Regional Director
                </button>
            </div>
        </form>
    </div>
</div>

<!-- CONFIRMATION MODAL -->
<x-confirm-modal
    title="Save Regional Director"
    message="Are you sure you want to save this Regional Director record?"
    event="open-confirm-modal"
    confirm="confirm-create-signatory"
/>

<script>
    document.addEventListener('confirm-create-signatory', (e) => {
        e.preventDefault();
        document.getElementById('signatory-form').submit();
    })

</script>

@endsection
