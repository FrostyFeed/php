<?php
use App\Http\Controllers\Controller;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LessonController extends Controller
{
    public function index(Request $request)
    {
        $query = Lesson::query()->with(['subject', 'teacher']); 
        $filterableFields = ['id', 'topic', 'homework', 'subject_id', 'teacher_id'];

        foreach ($request->query() as $key => $value) {
            if (in_array($key, $filterableFields) && !empty($value)) {
                if (in_array($key, ['topic', 'homework'])) {
                    $query->where($key, 'LIKE', "%{$value}%");
                } elseif (in_array($key, ['subject_id', 'teacher_id'])) {
                    $query->where($key, $value);
                } else {
                    $query->where($key, $value);
                }
            }
        }
        if ($request->has('lesson_date') && !empty($request->lesson_date)) {
            $query->whereDate('lesson_date', $request->lesson_date);
        }
        if ($request->has('lesson_date_from') && !empty($request->lesson_date_from)) {
            $query->where('lesson_date', '>=', $request->lesson_date_from . ' 00:00:00');
        }
        if ($request->has('lesson_date_to') && !empty($request->lesson_date_to)) {
            $query->where('lesson_date', '<=', $request->lesson_date_to . ' 23:59:59');
        }

        $itemsPerPage = $request->input('itemsPerPage', 10);
        $lessons = $query->orderBy('lesson_date', 'desc')->paginate($itemsPerPage);
        return response()->json($lessons);
    }
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'lesson_date' => 'required|date',
            'topic' => 'required|string|max:255',
            'homework' => 'nullable|string',
        ]);
        if ($validator->fails()) return response()->json($validator->errors(), 422);
        $lesson = Lesson::create($validator->validated());
        return response()->json($lesson->load(['subject', 'teacher']), 201);
    }
    public function show(Lesson $lesson) { return $lesson->load(['subject', 'teacher']); }
    public function update(Request $request, Lesson $lesson) {
        $validator = Validator::make($request->all(), [
            'subject_id' => 'sometimes|required|exists:subjects,id',
            'teacher_id' => 'sometimes|required|exists:teachers,id',
            'lesson_date' => 'sometimes|required|date',
            'topic' => 'sometimes|required|string|max:255',
            'homework' => 'nullable|string',
        ]);
        if ($validator->fails()) return response()->json($validator->errors(), 422);
        $lesson->update($validator->validated());
        return response()->json($lesson->load(['subject', 'teacher']));
    }
    public function destroy(Lesson $lesson) {
        $lesson->delete();
        return response()->json(null, 204);
    }
}