<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\MasterSlot;
use Illuminate\Http\Request;

/**
 * @tags Master Data
 */
class MasterDataController extends Controller
{
    /**
     * Get all courses for 
     */
    public function courses(Request $request)
    {
        $query = Course::query();

        if ($request->has('max_semester')) {
            $query->where('semester', '<=', $request->max_semester);
        }

        $courses = $query->orderBy('semester', 'asc')->orderBy('name', 'asc')->get(['id', 'name', 'code', 'semester']);
        
        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil daftar mata kuliah',
            'data' => $courses
        ]);
    }

    /**
     * Get all master slots for dropdowns
     */
    public function masterSlots()
    {
        $slots = MasterSlot::orderBy('start_time', 'asc')->get(['id', 'code', 'start_time', 'end_time']);
        
        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil daftar jam belajar',
            'data' => $slots
        ]);
    }
}
