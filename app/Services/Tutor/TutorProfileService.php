<?php

namespace App\Services\Tutor;

use App\Models\Tutor;
use Exception;

class TutorProfileService
{
    /**
     * Update tutor profile
     */
    public function updateProfile($user, Tutor $tutor, array $data)
    {
        $userData = collect($data)->only(['name', 'nim', 'phone', 'avatar'])->toArray();
        if (!empty($userData)) {
            $user->update($userData);
        }

        $tutorData = collect($data)->only(['bio', 'skills', 'price', 'portfolio_urls'])->toArray();
        if (isset($data['price_per_session'])) {
            $tutorData['price'] = $data['price_per_session'];
        }
        
        if (isset($data['certificate_files']) && is_array($data['certificate_files'])) {
            $certPaths = [];
            foreach ($data['certificate_files'] as $certFile) {
                $certPaths[] = $certFile->store('certificates', 'public');
            }
            if ($tutor->certificate_files) {
                foreach ($tutor->certificate_files as $oldPath) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
                }
            }
            $tutorData['certificate_files'] = $certPaths;
        }

        if (!empty($tutorData)) {
            $tutor->update($tutorData);
        }
        return $tutor;
    }
}
