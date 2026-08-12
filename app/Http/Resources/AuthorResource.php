<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //return parent::toArray($request);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'nationality' => $this->nationality,
            'birth_date' => $this->birth_date?->toDateString(),
            'work_count' => $this->whenCounted('works'),
            'works' => WorkResource::collection($this->whenLoaded('works')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
