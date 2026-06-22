<?php

namespace App\Services\Admin;

use App\Models\Course;
use App\Models\MasterSlot;

class MasterDataService
{
    public function storeCourse(array $data)
    {
        return Course::create($data);
    }

    public function updateCourse(int $id, array $data)
    {
        $course = Course::findOrFail($id);
        $course->update($data);
        return $course;
    }

    public function destroyCourse(int $id)
    {
        $course = Course::findOrFail($id);
        $course->delete();
        return true;
    }

    public function storeSlot(array $data)
    {
        return MasterSlot::create($data);
    }

    public function destroySlot(int $id)
    {
        $slot = MasterSlot::findOrFail($id);
        $slot->delete();
        return true;
    }
}
