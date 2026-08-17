<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContentReportResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => match ($this->reportable_type) {
                \App\Models\Event::class => 'events',
                \App\Models\Artist::class => 'artists',
                \App\Models\Venue::class => 'venues',
                default => class_basename($this->reportable_type),
            },
            'target_id' => $this->reportable_id,
            'category' => $this->category,
            'category_label' => $this->categoryLabel(),
            'message' => $this->message,
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
