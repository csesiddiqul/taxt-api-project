<?php

namespace App\Http\Resources\Auth;
use App\Enums\ApprovedStatusEnum;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'profile_image' => $this->profile_image ? asset($this->profile_image) : NULL,
            'status' => $this->status ? ApprovedStatusEnum::from($this->status)->getLabelText() : null,
            'email_verified_at' => $this->email_verified_at,
            'role' => $this->roles,
            'profile' => new ProfileResource($this->whenLoaded('profile')),
        ];
    }
}
