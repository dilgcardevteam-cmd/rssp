<?php

namespace App\Http\Controllers;

use App\Models\VacancyTitle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class VacancyTitleController extends Controller
{
    public function index()
    {
        if ((Auth::guard('admin')->user()->role ?? null) !== 'superadmin') {
            abort(403);
        }
        $titles = VacancyTitle::orderBy('position_title')->get();
        return view('admin.vacancy_titles.index', compact('titles'));
    }

    public function store(Request $request)
    {
        if ((Auth::guard('admin')->user()->role ?? null) !== 'superadmin') {
            abort(403);
        }
        $validated = $request->validate([
            'position_title' => 'required|string|max:255|unique:vacancy_titles,position_title',
            'salary_grade' => ['required', 'regex:/^SG-([1-9]|[1-9][0-9])$/'],
            'monthly_salary' => 'required|numeric|min:0',
        ], [
            'salary_grade.regex' => 'Salary Grade/Pay Grade must be between SG-1 and SG-99.',
        ]);
        VacancyTitle::create($validated);
        return redirect()->route('admin.vacancy_titles.index')->with('success', 'Vacancy title created.');
    }

    public function update(Request $request, $id)
    {
        if ((Auth::guard('admin')->user()->role ?? null) !== 'superadmin') {
            abort(403);
        }
        $title = VacancyTitle::findOrFail($id);
        $validated = $request->validate([
            'position_title' => 'required|string|max:255|unique:vacancy_titles,position_title,' . $title->id,
            'salary_grade' => ['required', 'regex:/^SG-([1-9]|[1-9][0-9])$/'],
            'monthly_salary' => 'required|numeric|min:0',
        ], [
            'salary_grade.regex' => 'Salary Grade/Pay Grade must be between SG-1 and SG-99.',
        ]);
        $title->update($validated);
        return redirect()->route('admin.vacancy_titles.index')->with('success', 'Vacancy title updated.');
    }

    public function destroy($id)
    {
        if ((Auth::guard('admin')->user()->role ?? null) !== 'superadmin') {
            abort(403);
        }
        $title = VacancyTitle::findOrFail($id);
        $title->delete();
        return redirect()->route('admin.vacancy_titles.index')->with('success', 'Vacancy title deleted.');
    }

    public function listJson()
    {
        // Allow any authenticated admin/viewer to fetch titles for vacancy forms
        if (!Auth::guard('admin')->check()) {
            abort(403);
        }
        $data = VacancyTitle::orderBy('position_title')->get(['id', 'position_title', 'salary_grade', 'monthly_salary']);
        return response()->json(['success' => true, 'data' => $data]);
    }
}
