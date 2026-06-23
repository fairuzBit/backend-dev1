<?php

namespace App\Http\Controllers\Api\Learner;

use App\Http\Controllers\Controller;
use App\Http\Resources\TutorResource;
use App\Services\Learner\TutorDiscoveryService;

/**
 * @tags Learner - Tutor Discovery
 */
class TutorDiscoveryController extends Controller
{
    protected $tutorService;

    public function __construct(TutorDiscoveryService $tutorService)
    {
        $this->tutorService = $tutorService;
    }

    /**
     * Menampilkan Katalog Tutor
     */
    public function index(\Illuminate\Http\Request $request)
    {
        $filters = $request->only(['search', 'course_id', 'day', 'time']);
        $tutors = $this->tutorService->getAllTutors($filters);

        // Menggunakan Resource::collection pada hasil paginate() akan otomatis menyertakan meta & links
        return TutorResource::collection($tutors)->additional([
            'success' => true,
            'message' => 'Berhasil mengambil daftar tutor'
        ]);
    }

    /**
     * Menampilkan Detail Satu Profil Tutor
     */
    public function show(string $id)
    {
        try {
            $tutor = $this->tutorService->getTutorById((int) $id);
            
            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengambil detail profil tutor',
                'data' => new TutorResource($tutor)
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tutor tidak ditemukan'
            ], 404);
        }
    }
}
