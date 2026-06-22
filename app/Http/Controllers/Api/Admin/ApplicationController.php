<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Admin\ApplicationService;

/**
 * @tags Admin Applications
 */
class ApplicationController extends Controller
{
    protected $applicationService;

    public function __construct(ApplicationService $applicationService)
    {
        $this->applicationService = $applicationService;
    }

    public function index()
    {
        $applications = $this->applicationService->getAllApplications();

        return response()->json([
            'data' => $applications->map(function ($app) {
                return [
                    'id' => $app->id,
                    'user_id' => $app->user_id,
                    'name' => $app->user->name ?? 'Unknown',
                    'email' => $app->user->email ?? 'Unknown',
                    'avatar' => ($app->user && $app->user->avatar) ? (str_starts_with($app->user->avatar, 'data:image') || str_starts_with($app->user->avatar, 'http') ? $app->user->avatar : asset('storage/' . $app->user->avatar)) : null,
                    'status' => $app->status,
                    'created_at' => $app->created_at->format('d M Y'),
                    'documents' => collect($app->transcript_files)->map(function ($path, $index) {
                        return [
                            'type' => 'transcript',
                            'name' => 'Transkrip_Smt_' . ($index + 1) . '.pdf',
                            'label' => 'Transkrip Smt ' . ($index + 1),
                            'url' => asset('storage/' . $path)
                        ];
                    })->merge(collect($app->certificate_files ?? [])->map(function ($path, $index) {
                        $ext = pathinfo($path, PATHINFO_EXTENSION);
                        return [
                            'type' => 'certificate',
                            'name' => 'Sertifikat_' . ($index + 1) . '.' . $ext,
                            'label' => 'Sertifikat ' . ($index + 1),
                            'url' => asset('storage/' . $path)
                        ];
                    }))->merge(collect($app->portfolio_urls ?? [])->map(function ($url, $index) {
                        return [
                            'type' => 'portfolio',
                            'name' => parse_url($url, PHP_URL_HOST) ?? 'Link Eksternal',
                            'label' => 'Portofolio ' . ($index + 1),
                            'url' => $url
                        ];
                    }))->toArray(),
                    'matkul' => $app->course ? [$app->course->name] : [],
                    'keahlian' => ($app->user && $app->user->tutor && $app->user->tutor->bio) ? [$app->user->tutor->bio] : [],
                ];
            })
        ]);
    }

    public function approve(Request $request, $id)
    {
        $app = $this->applicationService->approveApplication($id, $request->user()->id);

        return response()->json([
            'message' => 'Tutor disetujui',
            'data' => $app
        ]);
    }

    public function reject(Request $request, $id)
    {
        // Tangkap alasan dari frontend
        $reason = $request->input('admin_note');
        $app = $this->applicationService->rejectApplication($id, $reason, $request->user()->id);

        return response()->json([
            'message' => 'Tutor ditolak',
            'data' => $app
        ]);
    }

    public function destroy($id)
    {
        $this->applicationService->deleteApplication($id);

        return response()->json([
            'message' => 'Pengajuan berhasil dihapus',
        ]);
    }
}
