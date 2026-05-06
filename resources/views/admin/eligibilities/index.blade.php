@extends('layout.admin')
@section('title', 'Eligibilities')
@section('content')
<div class="p-6 max-w-5xl mx-auto space-y-6 font-montserrat">
    @if ($errors->any())
        <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded relative z-50">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="bg-green-50 border border-green-300 text-green-700 px-4 py-3 rounded text-sm relative z-50">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl border border-blue-200 shadow p-6">
        <h2 class="text-xl font-bold text-[#002C76] mb-4">Add Eligibility</h2>
        <form method="POST" action="{{ route('admin.eligibilities.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700">Eligibility Name<span class="text-red-600">*</span></label>
                <input
                    type="text"
                    name="name"
                    required
                    class="w-full border border-gray-300 rounded px-3 py-2"
                    placeholder="e.g., CSC Professional Eligibility"
                    value="{{ old('name') }}">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Legal Basis</label>
                <input
                    type="text"
                    name="legal_basis"
                    class="w-full border border-gray-300 rounded px-3 py-2"
                    placeholder="e.g., RA 1080"
                    value="{{ old('legal_basis') }}">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Level</label>
                <input
                    type="text"
                    name="level"
                    class="w-full border border-gray-300 rounded px-3 py-2"
                    placeholder="e.g., First Level"
                    value="{{ old('level') }}">
            </div>
            <div class="md:col-span-3">
                <button type="submit" class="px-4 py-2 bg-[#0D2B70] hover:bg-[#002C76] text-white rounded shadow">Create</button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-blue-200 shadow p-6">
        <h2 class="text-xl font-bold text-[#002C76] mb-4">Eligibility List</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="text-left px-3 py-2">Eligibility Name</th>
                        <th class="text-left px-3 py-2">Legal Basis</th>
                        <th class="text-left px-3 py-2">Level</th>
                        <th class="text-left px-3 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($eligibilities as $item)
                    <tr class="border-t">
                        <td class="px-3 py-2">{{ $item->name }}</td>
                        <td class="px-3 py-2">{{ $item->legal_basis ?: 'N/A' }}</td>
                        <td class="px-3 py-2">{{ $item->level ?: 'N/A' }}</td>
                        <td class="px-3 py-2 space-x-2">
                            <button
                                type="button"
                                onclick="openEdit({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ addslashes((string) $item->legal_basis) }}', '{{ addslashes((string) $item->level) }}')"
                                class="text-blue-600 hover:underline">
                                Edit
                            </button>
                            <form method="POST" action="{{ route('admin.eligibilities.destroy', $item->id) }}" class="inline-block" onsubmit="return confirm('Delete this eligibility?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-3 py-2 text-gray-500">No records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="editModal" class="fixed inset-0 bg-black bg-opacity-40 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-lg">
        <h3 class="text-lg font-bold text-[#002C76] mb-4">Edit Eligibility</h3>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Eligibility Name<span class="text-red-600">*</span></label>
                    <input id="edit_name" type="text" name="name" required class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Legal Basis</label>
                    <input id="edit_legal_basis" type="text" name="legal_basis" class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Level</label>
                    <input id="edit_level" type="text" name="level" class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
            </div>
            <div class="mt-4 flex justify-end space-x-2">
                <button type="button" onclick="closeEdit()" class="px-4 py-2 bg-gray-200 rounded">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-[#0D2B70] text-white rounded">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(id, name, legalBasis, level) {
    document.getElementById('editForm').action = "{{ url('/admin/utilities/eligibilities') }}/" + id;
    document.getElementById('edit_name').value = name || '';
    document.getElementById('edit_legal_basis').value = legalBasis || '';
    document.getElementById('edit_level').value = level || '';
    document.getElementById('editModal').classList.remove('hidden');
    document.getElementById('editModal').classList.add('flex');
}

function closeEdit() {
    document.getElementById('editModal').classList.add('hidden');
    document.getElementById('editModal').classList.remove('flex');
}
</script>
@endsection

