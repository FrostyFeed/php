<?php

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|string|in:'.User::ROLE_CLIENT.','.User::ROLE_MANAGER.','.User::ROLE_ADMIN,
        ]);
        if ($validator->fails()) return response()->json($validator->errors(), 422);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);
        $token = auth('api')->login($user); 
        return $this->respondWithToken($token, $user, 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), ['email' => 'required|email', 'password' => 'required|string']);
        if ($validator->fails()) return response()->json($validator->errors(), 422);

        $credentials = $request->only('email', 'password');
        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $this->respondWithToken($token, auth('api')->user());
    }

    public function me() { return response()->json(auth('api')->user()); }
    public function logout() { auth('api')->logout(); return response()->json(['message' => 'Successfully logged out']); }
    public function refresh() { return $this->respondWithToken(auth('api')->refresh(), auth('api')->user());}

    protected function respondWithToken($token, $user, $status = 200)
    {
        return response()->json([
            'access_token' => $token, 'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60, 
            'user' => $user->only(['id', 'name', 'email', 'role']) 
        ], $status);
    }
}