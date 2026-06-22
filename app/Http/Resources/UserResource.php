<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Kita hanya mengembalikan data yang penting saja, 
        // created_at dan updated_at sengaja dibuang agar JSON bersih.
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'nim' => $this->nim,
            'phone' => $this->phone,
            'avatar' => $this->avatar ? asset('storage/' . $this->avatar) : null,
            'role' => $this->roles->last()->name ?? 'learner', // Backward compatibility for single role usage (last usually gets the most recently assigned, e.g. tutor)
            'roles' => $this->roles->pluck('name'), // Return all roles as an array
        ];
    }
}
