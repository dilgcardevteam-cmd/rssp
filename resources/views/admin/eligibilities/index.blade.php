@extends('layout.admin')
@section('title', 'Eligibilities')
@section('content')
@php
    $entryRows = old('items');
    if (!is_array($entryRows) || empty($entryRows)) {
        $entryRows = [
            ['name' => '', 'legal_basis' => '', 'level' => ''],
        ];
    }
@endphp
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
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
            <div>
                <h2 class="text-xl font-bold text-[#002C76]">Add Eligibilities</h2>
                <p class="text-sm text-gray-600 mt-1">Add multiple eligibility presets, then save them in one submit.</p>
            </div>
            <button type="button" id="addEligibilityRow" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-[#0D2B70] text-[#0D2B70] rounded shadow-sm hover:bg-[#EAF2FF]">
                + Add another field
            </button>
        </div>
        <form method="POST" action="{{ route('admin.eligibilities.store') }}" id="eligibilityBatchForm" class="space-y-4">
            @csrf
            <div id="eligibilityRows" class="space-y-4"></div>
            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-[#0D2B70] hover:bg-[#002C76] text-white rounded shadow">Save All</button>
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
const initialEligibilityRows = @json(array_values($entryRows));

function escapeEligibilityHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function renderEligibilityRow(index, row = {}) {
    return `
        <div class="eligibility-row rounded-xl border border-blue-100 bg-slate-50 p-4" data-row-index="${index}">
            <div class="flex items-center justify-between gap-3 mb-4">
                <h3 class="text-sm font-bold uppercase tracking-wide text-[#002C76]">Eligibility ${index + 1}</h3>
                <button type="button" class="remove-eligibility-row text-sm font-semibold text-red-600 hover:text-red-700">
                    Remove
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Eligibility Name<span class="text-red-600">*</span></label>
                    <input
                        type="text"
                        name="items[${index}][name]"
                        required
                        class="w-full border border-gray-300 rounded px-3 py-2"
                        placeholder="e.g., CSC Professional Eligibility"
                        value="${escapeEligibilityHtml(row.name || '')}">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Legal Basis</label>
                    <input
                        type="text"
                        name="items[${index}][legal_basis]"
                        class="w-full border border-gray-300 rounded px-3 py-2"
                        placeholder="e.g., RA 1080"
                        value="${escapeEligibilityHtml(row.legal_basis || '')}">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Level</label>
                    <input
                        type="text"
                        name="items[${index}][level]"
                        class="w-full border border-gray-300 rounded px-3 py-2"
                        placeholder="e.g., First Level"
                        value="${escapeEligibilityHtml(row.level || '')}">
                </div>
            </div>
        </div>
    `;
}

function syncEligibilityRows(rows) {
    const container = document.getElementById('eligibilityRows');
    if (!container) {
        return;
    }

    container.innerHTML = rows.map((row, index) => renderEligibilityRow(index, row)).join('');
    container.querySelectorAll('.remove-eligibility-row').forEach((button) => {
        button.addEventListener('click', function () {
            const rowElements = Array.from(container.querySelectorAll('.eligibility-row'));
            if (rowElements.length <= 1) {
                const rowElement = button.closest('.eligibility-row');
                if (!rowElement) {
                    return;
                }
                rowElement.querySelectorAll('input').forEach((input) => {
                    input.value = '';
                });
                return;
            }

            const rowElement = button.closest('.eligibility-row');
            if (!rowElement) {
                return;
            }

            const indexToRemove = Number(rowElement.dataset.rowIndex);
            const nextRows = collectEligibilityRows().filter((_, index) => index !== indexToRemove);
            syncEligibilityRows(nextRows);
        });
    });
}

function collectEligibilityRows() {
    const container = document.getElementById('eligibilityRows');
    if (!container) {
        return [];
    }

    return Array.from(container.querySelectorAll('.eligibility-row')).map((rowElement) => ({
        name: rowElement.querySelector('input[name$="[name]"]')?.value || '',
        legal_basis: rowElement.querySelector('input[name$="[legal_basis]"]')?.value || '',
        level: rowElement.querySelector('input[name$="[level]"]')?.value || '',
    }));
}

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

document.addEventListener('DOMContentLoaded', function () {
    syncEligibilityRows(initialEligibilityRows);

    const addButton = document.getElementById('addEligibilityRow');
    if (addButton) {
        addButton.addEventListener('click', function () {
            const nextRows = collectEligibilityRows();
            nextRows.push({ name: '', legal_basis: '', level: '' });
            syncEligibilityRows(nextRows);
        });
    }
});
</script>
@endsection
