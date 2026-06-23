<?php

namespace App\Http\Controllers\Api\Learner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Learner\UpdateProfileRequest;
use App\Services\Learner\ProfileService;
use App\Http\Resources\UserResource;

/**
 * @tags Learner Profile
 */
class ProfileController extends Controller
{
    protected $profileService;

    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    public function me(Request $request)
    {
        // 1. Minta data ke Service
        $user = $this->profileService->getProfile($request->user()->id);

        // 2. Bungkus data menggunakan Resource
        return response()->json([
            'success' => true,
            'message' => 'Data user berhasil diambil',
            'data' => new UserResource($user)
        ]);
    }

    public function update(UpdateProfileRequest $request)
    {

        $data = $request->validated();
        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user = $this->profileService->updateProfile($request->user()->id, $data);

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui',
            'data' => new UserResource($user)
        ]);
    }

    public function tutorApplicationStatus(Request $request)
    {
        $status = $this->profileService->tutorApplicationStatus($request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Status pendaftaran tutor berhasil diambil',
            'data' => [
                'status' => $status
            ]
        ]);
    }
}
