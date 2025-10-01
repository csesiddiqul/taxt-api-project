<?php

namespace App\Http\Controllers;

use App\Classes\ImageUpload;
use App\Enums\ApprovedStatusEnum;
use App\Enums\RolesEnum;
use App\Http\API\BaseController;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\Auth\UserRegisterResource;
use App\Http\Resources\Auth\UserResource;
use App\Models\User;
use App\Services\UserRegistrationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use App\Services\SmsService;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\VerifyPhoneRequest;
use App\Http\Requests\Auth\ResendCodeRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class AuthUserController extends BaseController
{
    protected $smsService;
    protected $userRegistrationService;

    public function __construct(SmsService $smsService, UserRegistrationService $userRegistrationService)
    {
        $this->smsService = $smsService;
        $this->userRegistrationService = $userRegistrationService;
    }


    public function createPermission(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:permissions,name',
            'guard_name' => 'nullable|string|in:web',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $permission = Permission::create([
            'name' => $request->name,
            'guard_name' => $request->guard_name ?? 'web',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permission created successfully.',
            'data' => $permission,
        ]);
    }


    public function assignPermissionsToRole(Request $request)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name',
            'role_id' => 'required|exists:roles,id',
        ]);

        $permissions = Permission::whereIn('name', $request->permissions)->pluck('name')->toArray();
        $role = Role::find($request->role_id);

        if ($role) {
            $alreadyAssigned = $role->permissions->pluck('name')->toArray();
            $newPermissions = array_diff($permissions, $alreadyAssigned);
            if (empty($newPermissions)) {
                return $this->sendResponse(
                    'All selected permissions are already assigned to this role.',
                    $alreadyAssigned
                );
            }
            $role->givePermissionTo($newPermissions);
            return $this->sendResponse(
                'Permissions assigned successfully.',
                $newPermissions
            );
        }

        return $this->sendError('Role not found.');
    }



    public function removeRoleHasPermission(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permissions' => 'required|array',
        ]);

        $role = Role::find($request->role_id);

        if ($role) {
            $role->revokePermissionTo($request->permissions);
        }

        return response()->json([
            'message' => 'Permissions removed successfully.',
            'role' => $role
        ]);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('phone', 'password');
        $user = User::where('phone', normalizePhone($credentials['phone']))->first();
        if ($user) {
            if (Auth::attempt(credentials: ['phone' => normalizePhone($credentials['phone']), 'password' => $credentials['password']])) {
                $token = $user->createToken($user->name)->plainTextToken;
                $roles = $user->getRoleNames();
                $permissions = $user->getAllPermissions()->pluck('name');

                return $this->sendResponse('User login successfully.', (new UserRegisterResource($user))
                    ->additional([
                        'token' => $token,
                        'roles' => $roles,
                        'permissions' => $permissions,
                    ]));
            } else {
                return $this->sendError('Unauthorized.', ['password' => 'Invalid password.'], 404);
            }
        } else {
            return $this->sendError('Unauthorized.', ['phone' => 'Phone number not found.'], 404);
        }
    }

    public function register(RegisterRequest $request, ImageUpload $imageUpload)
    {
        /** @var \Illuminate\Http\Request $request */

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->name,
                'phone' => normalizePhone($request->phone),
                'email' => $request->email,
                'status' => ApprovedStatusEnum::NotApproved->value,
                'password' => Hash::make($request->password),
            ]);
            if ($user) {
                $user->remember_token = random_int(100000, 999999);
                $user->token_expires_at = now()->addMinutes(5);
                if ($request->profile_image) {
                    $user->profile_image = $imageUpload->fileUpload(
                        file: $request->profile_image,
                        data: $user,
                        folder: 'profile-images',
                        width: 100,
                        height: 150,
                        fileName: 'photo_' . $user->id
                    );
                }
                $user->save();
                $this->smsService->sendSms($user->phone, 'Verification Code: ' . $user->remember_token);
            }

            if (RolesEnum::Guest->value) {
                $user->assignRole(RolesEnum::Guest->value);
                $this->userRegistrationService->handleStoreUserInfo($user, $request);
            } else {
                DB::rollBack();
                return $this->sendError('Invalid role ID provided.', ['error' => RolesEnum::Guest->value], 400);
            }

            $token = $user->createToken('test')->plainTextToken;
            $roles = $user->getRoleNames();
            $permissions = $user->getAllPermissions()->pluck('name');


            DB::commit();
            return $this->sendResponse('Patient registered successfully.', (new UserRegisterResource($user))->additional([
                'token' => $token,
                'roles' => $roles,
                'permissions' => $permissions,
            ]));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Registration failed. Please try again.', ['error' => $e->getMessage()], 500);
        }
    }

    public function updateUserProfile(Request $request, ImageUpload $imageUpload)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . auth()->id(),
            'phone' => [
                'required',
                'regex:/^(\+8801[3-9]\d{8}|01[3-9]\d{8})$/',
                Rule::unique('users', 'phone')->ignore(auth()->id()),
                function ($attribute, $value, $fail) {
                    $normalized = normalizePhone($value);
                    if (\App\Models\User::where('phone', $normalized)->where('id', '!=', auth()->id())->exists()) {
                        $fail('This phone number is already registered by another user.');
                    }
                },
            ],
            'profile_image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        DB::beginTransaction();

        try {
            $user = auth()->user();

            if ($request->has('name')) {
                $user->name = $request->name;
            }

            if ($request->has('email')) {
                $user->email = $request->email;
            }

            if ($request->has('phone')) {
                $newPhone = normalizePhone($request->phone);
                if ($user->phone !== $newPhone) {
                    $user->phone = $newPhone;
                    $user->email_verified_at = null;
                }
            }

            if ($request->hasFile('profile_image')) {
                $user->profile_image = $imageUpload->fileUpload(
                    file: $request->profile_image,
                    data: $user,
                    folder: 'profile-images',
                    width: 100,
                    height: 150,
                    fileName: 'photo_' . $user->id
                );
            }

            $user->save();

            DB::commit();

            return $this->sendResponse(
                'User profile updated successfully.',
                data: new UserResource($user->load(['roles.permissions']))
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to update profile.', $e->getMessage());
        }
    }


    /**
     * Handle phone verification
     *
     * @param \App\Http\Requests\VerifyPhoneRequest $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function verifyPhone(VerifyPhoneRequest $request)
    {
        $user = User::where('phone',  normalizePhone($request->phone))->first();

        if (!$user) {
            return $this->sendError('User not found.', [], 404);
        }

        if (now()->greaterThan($user->token_expires_at)) {
            return $this->sendError('Verification code expired.', [], 400);
        }
        if ($user->remember_token != $request->code) {
            $user->increment('verification_attempts');

            if ($user->verification_attempts >= 4) {
                return $this->sendError(
                    'Verification attempts exceeded. Please request a new code.',
                    [],
                    400
                );
            }

            return $this->sendError('Invalid verification code.', [], 400);
        }
        $user->update([
            'email_verified_at' => now(),
            'verification_attempts' => 0,
        ]);
        return $this->sendResponse('User verified successfully.', []);
    }


    public function resendVerifyPhone(ResendCodeRequest $request)
    {
        try {
            $user = User::where('phone', normalizePhone($request['phone']))->first();
            if ($user->email_verified_at != null) {
                return $this->sendResponse('User already verified.', []);
            }

            $user->update([
                'remember_token' => random_int(100000, 999999),
                'token_expires_at' => now()->addMinutes(5),
                'verification_attempts' => $user->verification_attempts + 1,
            ]);
            $this->smsService->sendSms($user->phone, 'Your new verification code is: ' . $user->remember_token);

            return $this->sendResponse(
                message: 'A new verification code has been sent to your phone number: ' . substr($user->phone, 0, 3) . 'XXXXX' . substr($user->phone, -3),
                status: 201
            );
        } catch (ModelNotFoundException $exception) {
            return $this->sendError('verification code send failed.', $exception->getMessage());
        } catch (\Exception $exception) {
            return $this->sendError('verification code send failed.', $exception->getMessage());
        }
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'new_password' => ['required', 'min:8', 'confirmed'], // new_password_confirmation field must be present
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'The current password is incorrect.',
            ], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.',
        ]);
    }



    public function forgotPassword(ResendCodeRequest $request)
    {
        $user = User::where('phone', normalizePhone($request->phone))->first();
        if (!$user) {
            return $this->sendError('User not found.', [], 404);
        }
        $token = random_int(100000, 999999);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['phone' => $user->phone],
            [
                'token' => $token,
                'password_reset_expires_at' => now()->addMinutes(30),
                'resend_attempts' => 0,
                'last_resend_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        $this->smsService->sendSms($user->phone, 'Your password reset code is: ' . $token);
        return $this->sendResponse('Password reset code sent to your phone number.', []);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $phone = normalizePhone($request->phone);
        $resetToken = DB::table('password_reset_tokens')
            ->where('phone', $phone)
            ->where('token', $request->token)
            ->where('password_reset_expires_at', '>', now())
            ->first();
        if (!$resetToken) {
            return $this->sendError('Invalid or expired reset token.', [], 400);
        }

        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return $this->sendError('User not found.', [], 404);
        }

        $user->update(['password' => Hash::make($request->password),]);

        DB::table('password_reset_tokens')->where('phone', $phone)->delete();
        return $this->sendResponse('Password has been reset successfully.', []);
    }



    public function resendPasswordCode(ResendCodeRequest $request)
    {
        $user = User::where('phone', normalizePhone($request->phone))->first();
        if (!$user) {
            return $this->sendError('User not found.', [], 404);
        }
        $resetToken = DB::table('password_reset_tokens')->where('phone', $user->phone)->first();
        if ($resetToken && $resetToken->resend_attempts >= 4) {
            return $this->sendError('You have exceeded the maximum number of resend attempts. Please wait before requesting a new code.', [], 400);
        }
        $token = random_int(100000, 999999);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['phone' => $user->phone],
            [
                'token' => $token,
                'password_reset_expires_at' => now()->addMinutes(30),
                'resend_attempts' => $resetToken ? $resetToken->resend_attempts + 1 : 1,
                'last_resend_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'updated_at' => now(),
            ]
        );
        $this->smsService->sendSms($user->phone, 'Your password reset code is: ' . $token);
        return $this->sendResponse('A new password reset code has been sent to your phone number.', []);
    }




    public function userProfile()
    {
        $user = Auth::user();
        if ($user) {
            $roles = $user->getRoleNames(); // Get roles
            $permissions = $user->getAllPermissions()->pluck('name');
            return $this->sendResponse(
                'User Profile information retrieved successfully.',
                (new UserResource($user))
                    ->additional([
                        'roles' => $roles,
                        'permissions' => $permissions,
                    ])
            );
        } else {
            return $this->sendError('Unauthorized.', ['error' => 'User not found.'], 401);
        }
    }






    public function message()
    {

        $data = "(1, 1, 'Subil', 'সুবিল', 'subilup.comilla.gov.bd'),";
        $result = "";
        $rows = explode("),", $data);
        foreach ($rows as $row) {
            $cleanRow = trim($row, "(), \t\n\r\0\x0B");
            $fields = explode(", ", $cleanRow);
            $entry = "['id' => " . (int)$fields[0] . ", 'district_id' => " . (int)$fields[1] .
                ", 'name' => '" . trim($fields[2], "'") . "', 'bn_name' => '" . trim($fields[3], "'") .
                "', 'url' => '" . trim($fields[4], "'") . "'],";
            $result .= $entry . "\n";
        }
        print_r($result);
        return $this->sendResponse('User information retrieved successfully. hi Labib');
    }

    public function sendSms(Request $request)
    {
        $request->validate([
            'to' => 'required|string',
            'message' => 'required|string',
        ]);
        try {
            $result = $this->smsService->sendSms($request->to, $request->message);
            return response()->json(['success' => true, 'result' => $result], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
