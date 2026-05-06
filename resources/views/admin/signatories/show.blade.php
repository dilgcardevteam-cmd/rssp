@extends('layout.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-[#002C76]">Signatory Details</h1>
            <div class="flex gap-2">
                <a href="{{ route('signatories.edit', $signatory->id ?? '#') }}" class="px-4 py-2 bg-[#002C76] text-white rounded-lg hover:bg-blue-900 transition-colors">
                    Edit
                </a>
                <form action="{{ route('signatories.destroy', $signatory->id ?? '#') }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors" onclick="return confirm('Are you sure?')">
                        Delete
                    </button>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500 font-medium">First Name</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $signatory->first_name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Middle Name</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $signatory->middle_name ?? '-' }}</p>
                </div>
            </div>

            <div>
                <p class="text-sm text-gray-500 font-medium">Last Name</p>
                <p class="text-lg font-semibold text-gray-900">{{ $signatory->last_name ?? '-' }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500 font-medium">Designation</p>
                <p class="text-lg font-semibold text-gray-900">{{ $signatory->designation ?? '-' }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500 font-medium">Office</p>
                <p class="text-lg font-semibold text-gray-900">{{ $signatory->office ?? '-' }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500 font-medium">Office Address</p>
                <p class="text-lg font-semibold text-gray-900">{{ $signatory->office_address ?? '-' }}</p>
            </div>
        </div>

        <div class="mt-6">
            <a href="{{ route('signatories.index') }}" class="text-[#002C76] hover:underline">
                ← Back to Signatories
            </a>
        </div>
    </div>
</div>
@endsection
