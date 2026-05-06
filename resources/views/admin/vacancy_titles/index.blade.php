@extends('layout.admin')
@section('title', 'Vacancy Titles')
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
        <h2 class="text-xl font-bold text-[#002C76] mb-4">Add Vacancy Title</h2>
        <form method="POST" action="{{ route('admin.vacancy_titles.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700">Position Title<span class="text-red-600">*</span></label>
                <input type="text" name="position_title" required class="w-full border border-gray-300 rounded px-3 py-2" placeholder="e.g., Engineer III" value="{{ old('position_title') }}">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Salary Grade/Pay Grade<span class="text-red-600">*</span></label>
                <div class="flex rounded border border-gray-300 overflow-hidden focus-within:ring-2 focus-within:ring-[#0D2B70]">
                    <span class="inline-flex items-center px-3 bg-gray-100 text-gray-600 font-semibold border-r border-gray-300 select-none">SG-</span>
                    <input type="text" id="sg_number_create" inputmode="numeric" maxlength="2" pattern="[0-9]{1,2}" required
                        class="w-full px-3 py-2 focus:outline-none"
                        placeholder="18"
                        value="{{ old('salary_grade') ? preg_replace('/^SG-/i', '', old('salary_grade')) : '' }}"
                        oninput="syncSalaryGrade(this, 'salary_grade_hidden')">
                </div>
                <input type="hidden" id="salary_grade_hidden" name="salary_grade" value="{{ old('salary_grade') }}">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Monthly Salary<span class="text-red-600">*</span></label>
                <input type="number" step="0.01" min="0" name="monthly_salary" required class="w-full border border-gray-300 rounded px-3 py-2" placeholder="e.g., 45000" value="{{ old('monthly_salary') }}">
            </div>
            <div class="md:col-span-3">
                <button type="submit" class="px-4 py-2 bg-[#0D2B70] hover:bg-[#002C76] text-white rounded shadow">Create</button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-blue-200 shadow p-6">
        <h2 class="text-xl font-bold text-[#002C76] mb-4">Existing Titles</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="text-left px-3 py-2">Position Title</th>
                        <th class="text-left px-3 py-2">Salary Grade/Pay Grade</th>
                        <th class="text-left px-3 py-2">Monthly Salary</th>
                        <th class="text-left px-3 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($titles as $t)
                    <tr class="border-t">
                        <td class="px-3 py-2">{{ $t->position_title }}</td>
                        <td class="px-3 py-2">{{ $t->salary_grade }}</td>
                        <td class="px-3 py-2">{{ number_format($t->monthly_salary, 2) }}</td>
                        <td class="px-3 py-2 space-x-2">
                            <form method="POST" action="{{ route('admin.vacancy_titles.update', $t->id) }}" class="inline-block">
                                @csrf @method('PUT')
                                <input type="hidden" name="position_title" value="{{ $t->position_title }}">
                                <input type="hidden" name="salary_grade" value="{{ $t->salary_grade }}">
                                <input type="hidden" name="monthly_salary" value="{{ $t->monthly_salary }}">
                                <button type="button" onclick="openEdit({{ $t->id }}, '{{ addslashes($t->position_title) }}', '{{ addslashes($t->salary_grade) }}', '{{ $t->monthly_salary }}')" class="text-blue-600 hover:underline">Edit</button>
                            </form>
                            <form method="POST" action="{{ route('admin.vacancy_titles.destroy', $t->id) }}" class="inline-block" onsubmit="return confirm('Delete this title?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-3 py-2 text-gray-500">No records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="editModal" class="fixed inset-0 bg-black bg-opacity-40 hidden items-center justify-center z-50">
  <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-lg">
    <h3 class="text-lg font-bold text-[#002C76] mb-4">Edit Vacancy Title</h3>
    <form id="editForm" method="POST">
      @csrf @method('PUT')
      <div class="grid grid-cols-1 gap-4">
        <div>
          <label class="block text-sm font-semibold text-gray-700">Position Title<span class="text-red-600">*</span></label>
          <input id="edit_position_title" type="text" name="position_title" required class="w-full border border-gray-300 rounded px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700">Salary Grade/Pay Grade<span class="text-red-600">*</span></label>
          <div class="flex rounded border border-gray-300 overflow-hidden focus-within:ring-2 focus-within:ring-[#0D2B70]">
              <span class="inline-flex items-center px-3 bg-gray-100 text-gray-600 font-semibold border-r border-gray-300 select-none">SG-</span>
              <input type="text" id="edit_salary_grade" inputmode="numeric" maxlength="2" pattern="[0-9]{1,2}" required
                  class="w-full px-3 py-2 focus:outline-none"
                  placeholder="18"
                  oninput="syncSalaryGrade(this, 'edit_salary_grade_hidden')">
          </div>
          <input type="hidden" id="edit_salary_grade_hidden" name="salary_grade">
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700">Monthly Salary<span class="text-red-600">*</span></label>
          <input id="edit_monthly_salary" type="number" step="0.01" min="0" name="monthly_salary" required class="w-full border border-gray-300 rounded px-3 py-2">
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
function sanitizeSalaryGradeInput(rawValue) {
  const digits = String(rawValue || '').replace(/\D/g, '').slice(0, 2);
  if (!digits) {
    return '';
  }
  const number = Number(digits);
  if (!Number.isInteger(number) || number < 1 || number > 99) {
    return '';
  }
  return String(number);
}

function syncSalaryGrade(input, hiddenId) {
  const sanitized = sanitizeSalaryGradeInput(input.value);
  input.value = sanitized;
  document.getElementById(hiddenId).value = sanitized ? 'SG-' + sanitized : '';
}

function openEdit(id, title, sg, salary) {
  document.getElementById('editForm').action = "{{ url('/admin/utilities/vacancy-titles') }}/" + id;
  document.getElementById('edit_position_title').value = title;
  const sgNum = sanitizeSalaryGradeInput((sg || '').replace(/^SG-/i, ''));
  document.getElementById('edit_salary_grade').value = sgNum;
  document.getElementById('edit_salary_grade_hidden').value = sgNum ? 'SG-' + sgNum : '';
  document.getElementById('edit_monthly_salary').value = salary || 0;
  document.getElementById('editModal').classList.remove('hidden');
  document.getElementById('editModal').classList.add('flex');
}
function closeEdit() {
  document.getElementById('editModal').classList.add('hidden');
  document.getElementById('editModal').classList.remove('flex');
}

document.addEventListener('DOMContentLoaded', function () {
  const createInput = document.getElementById('sg_number_create');
  if (createInput) {
    syncSalaryGrade(createInput, 'salary_grade_hidden');
  }
});
</script>
@endsection
