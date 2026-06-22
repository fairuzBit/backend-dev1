<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\StoreCourseRequest;
use App\Http\Requests\Admin\UpdateCourseRequest;
use App\Http\Requests\Admin\StoreMasterSlotRequest;
use App\Services\Admin\MasterDataService;

/**
 * @tags Admin Master Data
 */
class MasterDataController extends Controller
{
    protected $masterDataService;

    public function __construct(MasterDataService $masterDataService)
    {
        $this->masterDataService = $masterDataService;
    }
    // ==========================================
    // CRUD UNTUK COURSE (MATA KULIAH)
    // ==========================================

    public function storeCourse(StoreCourseRequest $request)
    {
        $course = $this->masterDataService->storeCourse($request->validated());

        return response()->json([
            'message' => 'Mata Kuliah berhasil ditambahkan',
            'data' => $course
        ]);
    }

    public function updateCourse(UpdateCourseRequest $request, $id)
    {
        $course = $this->masterDataService->updateCourse($id, $request->validated());

        return response()->json([
            'message' => 'Mata Kuliah berhasil diperbarui',
            'data' => $course
        ]);
    }

    public function destroyCourse($id)
    {
        $this->masterDataService->destroyCourse($id);

        return response()->json([
            'message' => 'Mata Kuliah berhasil dihapus'
        ]);
    }

    // ==========================================
    // CRUD UNTUK MASTER SLOT (JAM KETERSEDIAAN)
    // ==========================================

    public function storeSlot(StoreMasterSlotRequest $request)
    {
        $slot = $this->masterDataService->storeSlot($request->validated());

        return response()->json([
            'message' => 'Master Slot berhasil ditambahkan',
            'data' => $slot
        ]);
    }

    public function destroySlot($id)
    {
        $this->masterDataService->destroySlot($id);

        return response()->json([
            'message' => 'Master Slot berhasil dihapus'
        ]);
    }
}
