<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'action_url' => $this->action_url,
            'is_read' => (bool) $this->is_read,
            
            // Format waktu menjadi ramah manusia (Contoh: "5 menit yang lalu")
            'time_ago' => $this->created_at->diffForHumans(),
            
            // Kategori hari (Hari Ini, Kemarin, dll) untuk membantu Frontend mengelompokkan UI
            'group' => $this->created_at->isToday() ? 'HARI INI' : ($this->created_at->isYesterday() ? 'KEMARIN' : strtoupper($this->created_at->translatedFormat('d F Y'))),
            
            'created_at' => $this->created_at,
            'read_at' => $this->read_at ? $this->read_at->format('Y-m-d H:i') : null,
        ];
    }
}
