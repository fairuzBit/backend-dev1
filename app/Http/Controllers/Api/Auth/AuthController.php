<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @tags Authentication
 */
class AuthController extends Controller
{
    /**
     * Register
     * 
     * @unauthenticated
     */
    public function register(RegisterRequest $request, UserService $userService)
    {
        $data = $request->validated();
        $user = $userService->registerUser($data);

        return response()->json([
            'message' => 'Registrasi Berhasil', 
            'user' => new UserResource($user)
        ], 201); // 201 adalah standar HTTP untuk "Resource Created"
    }

    /**
     * Login
     * 
     * @unauthenticated
     */
    public function login(LoginRequest $request)
    {
        // Validasi sepenuhnya ditangani oleh LoginRequest (Thin Controller)
        $credentials = $request->validated();

        if ($token = Auth::guard('api')->attempt($credentials)) {
            // Kita bungkus Auth::user() menggunakan UserResource
            return response()->json([
                'message' => 'Login Berhasil',
                'access_token' => $token,
                'token_type' => 'bearer',
                'user' => new UserResource(Auth::guard('api')->user())
            ]);
        }
        
        $userExists = \App\Models\User::where('email', $credentials['email'])->exists();
        if (!$userExists) {
            return response()->json(['message' => 'Akun tidak terdaftar, silakan daftar terlebih dahulu'], 401);
        }

        return response()->json(['message' => 'Email atau Password salah'], 401);
    }

    /**
     * Get Current Logged In User
     * 
     * @authenticated
     */
    public function getUser(Request $request)
    {
        return response()->json([
            'user' => new UserResource($request->user())
        ]);
    }

    /**
     * Logout / Invalidate Token
     * 
     * @authenticated
     */
    public function logout()
    {
        Auth::guard('api')->logout();

        return response()->json([
            'message' => 'Berhasil logout'
        ]);
    }
}
