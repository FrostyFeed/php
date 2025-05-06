<?php


use App\Http\Controllers\Controller; 
use Illuminate\Http\JsonResponse;   
use Illuminate\Http\Request;      

class DemoController extends Controller
{
    public function helloWorld(): JsonResponse
    {
        $data = [
            'message' => 'Привіт, Світ!',
            'description' => 'Це простий JSON-відповідь від Laravel контролера.',
            'timestamp' => now(), 
        ];

        return response()->json($data);
    }
}