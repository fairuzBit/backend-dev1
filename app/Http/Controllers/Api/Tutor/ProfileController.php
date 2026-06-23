<?php

namespace App\Http\Controllers\Api\Tutor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Tutor\TutorProfileService;
use App\Http\Requests\Tutor\UpdateProfileRequest;

/**
 * @tags Tutor Profile
 */
class ProfileController extends Controller
{
    protected $profileService;

    public function __construct(TutorProfileService $profileService)
    {
        $this->profileService = $profileService;
    }
    public function me(Request $request)
    {
        $tutor = $request->user()->tutor;
        if (!$tutor) {
            return response()->json(['message' => 'Anda bukan tutor'], 403);
        }

        $tutor->loadMissing('courses.course', 'availabilitySlots.masterSlot');

        return response()->json([
            'message' => 'Profil tutor berhasil diambil',
            'data' => new \App\Http\Resources\TutorResource($tutor)
        ]);
    }

    /**
     * Update Tutor Profile
     */
    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $tutor = $user->tutor;

        if (!$tutor) {
            return response()->json(['message' => 'Anda bukan tutor'], 403);
        }

        $data = $request->validated();
        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $tutor = $this->profileService->updateProfile($user, $tutor, $data);
        $tutor->loadMissing('courses.course', 'availabilitySlots.masterSlot');

        return response()->json([
            'message' => 'Profil tutor diperbarui',
            'data' => new \App\Http\Resources\TutorResource($tutor)
        ]);
    }

    /**
     * Toggle Tutor availability status (is_active)
     */
    public function toggleStatus(Request $request)
    {
        $tutor = $request->user()->tutor;
        if (!$tutor) {
            return response()->json(['message' => 'Anda bukan tutor'], 403);
        }

        $tutor->update([
            'is_active' => !$tutor->is_active
        ]);

        return response()->json([
            'message' => 'Status ketersediaan berhasil diperbarui',
            'is_active' => (bool) $tutor->is_active
        ]);
    }
}
