<?php

namespace App\Http\Resources\Auth;

use App\Enums\ApprovedStatusEnum;
use App\Enums\StatusEnum;
use App\Http\Resources\BloodDonor\BloodDonorInfoResource;
use App\Http\Resources\Patient\PatientInfoResource;
use App\Models\PatientInfo;
use Illuminate\Http\Resources\Json\JsonResource;

class UserRegisterResource extends JsonResource
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
            'profile_image' => $this->profile_image ? asset($this->profile_image) : null,
            'status' => $this->status ? ApprovedStatusEnum::from($this->status)->getLabelText() : null,
            'token' => $this->additional['token'] ?? null,
            'email_verified_at' => $this->email_verified_at,
            'roles' => $this->additional['roles'] ?? null,
            'permissions' => $this->additional['permissions'] ?? null,
            'created_at' => $this->created_at->format('Y-m-d\TH:i:s.u\Z'),
            'updated_at' => $this->updated_at->format('Y-m-d\TH:i:s.u\Z'),
        ];
    }
}
