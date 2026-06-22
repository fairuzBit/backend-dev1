<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Admin\AdminUserService;
use Exception;

class AdminUserController extends Controller
{
    protected $userService;

    public function __construct(AdminUserService $userService)
    {
        $this->userService = $userService;
    }
    /**
     * Daftar Semua Pengguna
     */
    public function index()
    {
        $users = $this->userService->getAllUsers();

        return response()->json([
            'data' => $users
        ]);
    }

    /**
     * Blokir/Hapus Pengguna
     */
    public function destroy(Request $request, $id)
    {
        try {
            $this->userService->destroyUser($id, $request->user()->id);

            return response()->json([
                'message' => 'Pengguna berhasil diblokir/dihapus'
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
