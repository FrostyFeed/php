<?php
use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Builder; 

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $query = Teacher::query();

        $filterableFields = ['id', 'first_name', 'last_name', 'email', 'specialization'];

        foreach ($request->query() as $key => $value) {
            if (in_array($key, $filterableFields) && !empty($value)) {
                if (in_array($key, ['first_name', 'last_name', 'email', 'specialization'])) {
                    $query->where($key, 'LIKE', "%{$value}%");
                } else {
                    $query->where($key, $value); // Точне співпадіння для ID
                }
            }
        }

        if ($request->has('created_at_from') && !empty($request->created_at_from)) {
            $query->whereDate('created_at', '>=', $request->created_at_from);
        }
        if ($request->has('created_at_to') && !empty($request->created_at_to)) {
            $query->whereDate('created_at', '<=', $request->created_at_to);
        }

        $itemsPerPage = $request->input('itemsPerPage', 10);
        $teachers = $query->orderBy('id', 'asc')->paginate($itemsPerPage);

        return response()->json($teachers);
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:teachers',
            'specialization' => 'nullable|string|max:255',
        ]);
        if ($validator->fails()) return response()->json($validator->errors(), 422);
        $teacher = Teacher::create($validator->validated());
        return response()->json($teacher, 201);
    }

    public function show(Teacher $teacher) { return $teacher; }

    public function update(Request $request, Teacher $teacher) {
        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:teachers,email,'.$teacher->id,
            'specialization' => 'nullable|string|max:255',
        ]);
        if ($validator->fails()) return response()->json($validator->errors(), 422);
        $teacher->update($validator->validated());
        return response()->json($teacher);
    }

    public function destroy(Teacher $teacher) {
        $teacher->delete();
        return response()->json(null, 204);
    }
}