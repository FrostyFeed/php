<?php
use App\Http\Controllers\Controller;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GradeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $itemsPerPage = $request->input('itemsPerPage', 10);
        if (!is_numeric($itemsPerPage) || $itemsPerPage <= 0) {
            $itemsPerPage = 10;
        }
        $itemsPerPage = min($itemsPerPage, 100); 

        $query = Grade::query()->with(['student', 'lesson.subject', 'lesson.teacher']);

        $query->applyFilters($request->query());

        $grades = $query->orderBy('date_given', 'desc') 
                         ->paginate($itemsPerPage)
                         ->withQueryString(); 

        return response()->json($grades);
    }
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'lesson_id' => 'required|exists:lessons,id',
            'grade_value' => 'required|string|max:50',
            'comment' => 'nullable|string',
            'date_given' => 'required|date',
        ]);
        if ($validator->fails()) return response()->json($validator->errors(), 422);
        $existingGrade = Grade::where('student_id', $request->student_id)
                              ->where('lesson_id', $request->lesson_id)
                              ->first();
        if ($existingGrade) {
            return response()->json(['message' => 'Grade for this student and lesson already exists.'], 409);
        }
        $grade = Grade::create($validator->validated());
        return response()->json($grade->load(['student', 'lesson.subject', 'lesson.teacher']), 201);
    }
    public function show(Grade $grade) { return $grade->load(['student', 'lesson.subject', 'lesson.teacher']); }
    public function update(Request $request, Grade $grade) {
        $validator = Validator::make($request->all(), [
            'grade_value' => 'sometimes|required|string|max:50',
            'comment' => 'nullable|string',
            'date_given' => 'sometimes|required|date',
        ]);
        if ($validator->fails()) return response()->json($validator->errors(), 422);
        $grade->update($validator->validated());
        return response()->json($grade->load(['student', 'lesson.subject', 'lesson.teacher']));
    }
    public function destroy(Grade $grade) {
        $grade->delete();
        return response()->json(null, 204);
    }
}