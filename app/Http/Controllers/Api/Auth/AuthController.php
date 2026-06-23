<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

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

    /**
     * Login via Supabase OAuth (Google)
     * 
     * @unauthenticated
     */
    public function supabaseLogin(Request $request)
    {
        $request->validate([
            'access_token' => 'required|string',
        ]);

        $accessToken = $request->input('access_token');
        $supabaseUrl = config('services.supabase.url');
        $supabaseAnonKey = config('services.supabase.anon_key');

        if (!$supabaseUrl || !$supabaseAnonKey) {
            return response()->json([
                'message' => 'Supabase URL atau Anon Key belum dikonfigurasi di server.'
            ], 500);
        }

        $supabaseUrl = rtrim($supabaseUrl, '/');
        if (str_ends_with($supabaseUrl, '/rest/v1')) {
            $supabaseUrl = substr($supabaseUrl, 0, -8);
        }
        $supabaseUrl = rtrim($supabaseUrl, '/');

        // Panggil endpoint /auth/v1/user di Supabase untuk memvalidasi access token
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'apikey' => $supabaseAnonKey,
        ])->get($supabaseUrl . '/auth/v1/user');

        if ($response->failed()) {
            return response()->json([
                'message' => 'Token Supabase tidak valid atau sesi telah berakhir.',
                'error' => $response->json()
            ], 401);
        }

        $supabaseUser = $response->json();
        $email = $supabaseUser['email'] ?? null;

        if (!$email) {
            return response()->json([
                'message' => 'Email tidak ditemukan dari data autentikasi.'
            ], 400);
        }

        // Cari atau buat user baru
        $user = User::where('email', $email)->first();

        if (!$user) {
            $metadata = $supabaseUser['user_metadata'] ?? [];
            $fullName = $metadata['full_name'] ?? $metadata['name'] ?? explode('@', $email)[0];
            $avatar = $metadata['avatar_url'] ?? null;

            $user = User::create([
                'name' => $fullName,
                'email' => $email,
                'password' => Hash::make(Str::random(24)),
                'email_verified_at' => now(),
                'avatar' => $avatar,
                'role' => 'learner'
            ]);

            // Pasang role Spatie
            $user->assignRole('learner');
        } else {
            // Update avatar jika belum diset
            $metadata = $supabaseUser['user_metadata'] ?? [];
            $avatar = $metadata['avatar_url'] ?? null;
            if ($avatar && !$user->avatar) {
                $user->avatar = $avatar;
                $user->save();
            }
        }

        // Periksa apakah user di-suspend
        if ($user->suspended_until && now()->lt($user->suspended_until)) {
            return response()->json([
                'message' => 'Akun Anda sedang ditangguhkan.'
            ], 403);
        }

        // Login user dan dapatkan token Laravel JWT
        $token = Auth::guard('api')->login($user);

        return response()->json([
            'message' => 'Login Berhasil',
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => new UserResource($user)
        ]);
    }
}

