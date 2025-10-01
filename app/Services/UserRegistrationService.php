<?php

namespace App\Services;;

use App\Models\User;
use App\Http\Requests\RegisterRequest;
use App\Models\Address;
use App\Classes\ImageUpload;

class UserRegistrationService
{
    /**
     * Handle role-specific data for the user.
     *
     * @param User $user
     * @param RegisterRequest $request
     * @return void
     */


    public function handleStoreUserInfo(User $user, $request) {}


    public function handleUpdatePatientInfo($request, $id): void {}
}
