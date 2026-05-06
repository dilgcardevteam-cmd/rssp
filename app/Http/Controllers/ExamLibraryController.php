<?php

namespace App\Http\Controllers;

use App\Models\QuestionSeries;
use App\Models\LibraryQuestion;
use App\Models\ExamLibraryUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExamLibraryController extends Controller
{
    /**
     * Display the exam library page
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = QuestionSeries::with(['creator', 'questions'])
            ->withCount('questions');

        if ($search) {
            $query->where('series_name', 'LIKE', '%' . $search . '%')
                ->orWhere('description', 'LIKE', '%' . $search . '%');
        }

        $series = $query->orderByDesc('created_at')->get();

        if ($request->ajax()) {
            return response()->json($series);
        }

        return view('admin.exam_library.index', compact('series'));
    }

    /**
     * Store a new question series
     */
    public function storeSeries(Request $request)
    {
        try {
            $validated = $request->validate([
                'series_name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            DB::beginTransaction();

            $series = QuestionSeries::create([
                'series_name' => $validated['series_name'],
                'description' => $validated['description'] ?? null,
                'created_by' => Auth::guard('admin')->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Question series created successfully!',
                'series' => $series->load(['creator', 'questions']),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $e->errors()),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating question series: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create question series. Please try again.',
            ], 500);
        }
    }

    /**
     * Update a question series
     */
    public function updateSeries(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'series_name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            DB::beginTransaction();

            $series = QuestionSeries::findOrFail($id);
            $series->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Question series updated successfully!',
                'series' => $series->load(['creator', 'questions']),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Question series not found.',
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $e->errors()),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating question series: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update question series. Please try again.',
            ], 500);
        }
    }

    /**
     * Delete a question series
     */
    public function deleteSeries($id)
    {
        try {
            DB::beginTransaction();

            $series = QuestionSeries::findOrFail($id);

            // Check if any questions in this series are being used in exams
            $usedQuestions = LibraryQuestion::where('series_id', $id)
                ->whereHas('examUsages')
                ->count();

            if ($usedQuestions > 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete this series. {$usedQuestions} question(s) are currently being used in exams.",
                ], 400);
            }

            $series->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Question series deleted successfully!',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Question series not found.',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error deleting question series: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete question series. Please try again.',
            ], 500);
        }
    }

    /**
     * Get questions for a specific series
     */
    public function getSeriesQuestions($seriesId, Request $request)
    {
        // If this is a direct page visit (not AJAX), show the questions page
        if (!$request->ajax() && !$request->has('ajax') && !$request->has('search') && !$request->has('type') && !$request->has('difficulty')) {
            $series = QuestionSeries::with(['creator', 'questions'])->findOrFail($seriesId);
            return view('admin.exam_library.questions', compact('series'));
        }

        // Otherwise, return JSON for AJAX requests
        $search = $request->input('search');
        $type = $request->input('type');
        $difficulty = $request->input('difficulty');

        $query = LibraryQuestion::where('series_id', $seriesId)
            ->withCount('examUsages');

        if ($search) {
            $query->where('question', 'LIKE', '%' . $search . '%');
        }

        if ($type) {
            $query->where('question_type', $type);
        }

        if ($difficulty) {
            $query->where('difficulty_level', $difficulty);
        }

        $questions = $query->orderByDesc('created_at')->get();

        return response()->json($questions);
    }

    /**
     * Store a new question in a series
     */
    public function storeQuestion(Request $request, $seriesId)
    {
        try {
            $validated = $request->validate([
                'question' => 'required|string',
                'question_type' => 'required|in:multiple_choice,essay',
                'choices' => 'nullable|array',
                'choices.*' => 'nullable|string',
                'correct_answer' => 'nullable|string',
                'essay_answer_guide' => 'nullable|string',
                'essay_max_score' => 'nullable|integer|min:0',
                'difficulty_level' => 'nullable|in:easy,medium,hard',
                'category' => 'nullable|string|max:255',
                'tags' => 'nullable|array',
            ]);

            DB::beginTransaction();

            // Verify series exists
            $series = QuestionSeries::findOrFail($seriesId);

            // Additional validation based on question type
            if ($validated['question_type'] === 'multiple_choice') {
                if (empty($validated['choices']) || count($validated['choices']) < 2) {
                    throw new \Exception('Multiple choice questions must have at least 2 choices.');
                }
                if (empty($validated['correct_answer'])) {
                    throw new \Exception('Multiple choice questions must have a correct answer.');
                }
            }

            // Additional validation for essay max score
            if ($validated['question_type'] === 'essay') {
                $ems = $request->input('essay_max_score', null);
                if ($ems === null || $ems === '' || !is_numeric($ems) || (int)$ems < 0) {
                    throw new \Exception('Essay questions require a valid max score (0 or greater).');
                }
            }

            $question = LibraryQuestion::create([
                'series_id' => $seriesId,
                'question' => $validated['question'],
                'question_type' => $validated['question_type'],
                'choices' => $validated['choices'] ?? null,
                'correct_answer' => $validated['correct_answer'] ?? null,
                'essay_answer_guide' => $validated['essay_answer_guide'] ?? null,
                'essay_max_score' => $request->input('essay_max_score', null),
                'difficulty_level' => $validated['difficulty_level'] ?? null,
                'category' => $validated['category'] ?? null,
                'tags' => $validated['tags'] ?? null,
            ]);

            // Verify the question was actually saved
            if (!$question || !$question->id) {
                throw new \Exception('Failed to save question to database.');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Question created successfully!',
                'question' => $question->fresh()->loadCount('examUsages'),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Question series not found.',
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', array_map(fn($errors) => implode(', ', $errors), $e->errors())),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating question: ' . $e->getMessage(), [
                'series_id' => $seriesId,
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Failed to create question. Please try again.',
            ], 500);
        }
    }

    /**
     * Update a question
     */
    public function updateQuestion(Request $request, $questionId)
    {
        try {
            $validated = $request->validate([
                'question' => 'required|string',
                'question_type' => 'required|in:multiple_choice,essay',
                'choices' => 'nullable|array',
                'choices.*' => 'nullable|string',
                'correct_answer' => 'nullable|string',
                'essay_answer_guide' => 'nullable|string',
                'essay_max_score' => 'nullable|integer|min:0',
                'difficulty_level' => 'nullable|in:easy,medium,hard',
                'category' => 'nullable|string|max:255',
                'tags' => 'nullable|array',
            ]);

            DB::beginTransaction();

            $question = LibraryQuestion::findOrFail($questionId);

            // Additional validation based on question type
            if ($validated['question_type'] === 'multiple_choice') {
                if (empty($validated['choices']) || count($validated['choices']) < 2) {
                    throw new \Exception('Multiple choice questions must have at least 2 choices.');
                }
                if (empty($validated['correct_answer'])) {
                    throw new \Exception('Multiple choice questions must have a correct answer.');
                }
            }
            if ($validated['question_type'] === 'essay') {
                $ems = $request->input('essay_max_score', null);
                if ($ems === null || $ems === '' || !is_numeric($ems) || (int)$ems < 0) {
                    throw new \Exception('Essay questions require a valid max score (0 or greater).');
                }
            }

            // Check if question is used in exams
            $usageCount = $question->examUsages()->count();

            $question->update(array_merge($validated, [
                'essay_max_score' => $request->input('essay_max_score', $question->essay_max_score),
            ]));

            // Verify the update was successful
            $question = $question->fresh();
            if (!$question) {
                throw new \Exception('Failed to update question in database.');
            }

            DB::commit();

            if ($usageCount > 0) {
                return response()->json([
                    'success' => true,
                    'message' => "Question updated successfully! This change will affect {$usageCount} exam(s) using this question.",
                    'question' => $question->loadCount('examUsages'),
                    'affected_exams' => $usageCount,
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Question updated successfully!',
                    'question' => $question->loadCount('examUsages'),
                ]);
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Question not found.',
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', array_map(fn($errors) => implode(', ', $errors), $e->errors())),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating question: ' . $e->getMessage(), [
                'question_id' => $questionId,
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Failed to update question. Please try again.',
            ], 500);
        }
    }

    /**
     * Delete a question
     */
    public function deleteQuestion($questionId)
    {
        try {
            DB::beginTransaction();

            $question = LibraryQuestion::findOrFail($questionId);

            // Check if question is being used in any exam
            if ($question->isUsedInExams()) {
                $usageCount = $question->examUsages()->count();
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete this question. It is currently being used in {$usageCount} exam(s).",
                ], 400);
            }

            $deleted = $question->delete();

            if (!$deleted) {
                throw new \Exception('Failed to delete question from database.');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Question deleted successfully!',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Question not found.',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error deleting question: ' . $e->getMessage(), [
                'question_id' => $questionId
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete question. Please try again.',
            ], 500);
        }
    }

    /**
     * Get all questions for selection (when creating/editing exams)
     */
    public function getQuestionsForSelection(Request $request)
    {
        $search = $request->input('search');
        $seriesId = $request->input('series_id');
        $type = $request->input('type');
        $difficulty = $request->input('difficulty');

        $query = LibraryQuestion::with('series');

        if ($search) {
            $query->where('question', 'LIKE', '%' . $search . '%');
        }

        if ($seriesId) {
            $query->where('series_id', $seriesId);
        }

        if ($type) {
            $query->where('question_type', $type);
        }

        if ($difficulty) {
            $query->where('difficulty_level', $difficulty);
        }

        $questions = $query->orderBy('series_id')->orderBy('created_at')->get();

        return response()->json($questions);
    }
}
