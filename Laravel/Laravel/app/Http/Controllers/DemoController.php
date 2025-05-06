<?php


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str; 

class DemoItemController extends Controller
{
    private static array $items = [];
    private static int $nextId = 1; 

  
    public function index(): JsonResponse
    {
        return response()->json(array_values(self::$items)); 
    }

  
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $id = self::$nextId++;
        $newItem = [
            'id' => $id,
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ];

        self::$items[$id] = $newItem;

        return response()->json($newItem, 201); 
    }

  
    public function show(int $id): JsonResponse
    {
        if (!isset(self::$items[$id])) {
            return response()->json(['message' => 'Item not found'], 404);
        }
        return response()->json(self::$items[$id]);
    }

   
    public function update(Request $request, int $id): JsonResponse
    {
        if (!isset(self::$items[$id])) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255', 
            'description' => 'nullable|string',
        ]);

        $item = &self::$items[$id]; 

        if ($request->has('name')) {
            $item['name'] = $request->input('name');
        }
        if ($request->has('description')) {
            $item['description'] = $request->input('description');
        }
        $item['updated_at'] = now()->toIso8601String();

        return response()->json($item);
    }

  
    public function destroy(int $id): JsonResponse
    {
        if (!isset(self::$items[$id])) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        unset(self::$items[$id]);

        return response()->json(['message' => 'Item deleted successfully'], 200); 
    }
}