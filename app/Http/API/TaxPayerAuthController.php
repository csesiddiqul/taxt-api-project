<?php

namespace App\Http\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TaxPayer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Services\SmsService;

class TaxPayerAuthController extends BaseController
{

    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }


    public function getUserInfo()
    {
        return response()->json(['success' => true, 'client' => auth()->user()]);
    }
    // Register new client
    public function register(Request $request)
    {
        $request->validate([
            'ClientNo' => 'required|unique:Client_Information,ClientNo',
            'password' => 'required|min:8|confirmed', // password_confirmation
            'phone' => 'required|unique:Client_Information,phone',
        ]);

        $client = TaxPayer::create([
            'ClientNo' => $request->ClientNo,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
        ]);

        // Generate verification code
        $code = rand(100000, 999999);
        $client->phone_verification_code = $code;
        $client->save();

        // TODO: Send $code via SMS

        return response()->json([
            'success' => true,
            'message' => 'Registered successfully. Verification code sent.',
            'client_id' => $client->id
        ]);
    }

    // Verify phone
    public function verifyPhone(Request $request)
    {
        $request->validate([
            'ClientNo' => 'required',
            'code' => 'required',
        ]);

        $client = TaxPayer::where('ClientNo', $request->ClientNo)
            ->where('phone_verification_code', $request->code)
            ->first();

        if (!$client) {
            return response()->json(['success' => false, 'message' => 'Invalid code'], 400);
        }

        $client->is_phone_verified = true;
        $client->phone_verification_code = null;
        $client->save();

        return response()->json(['success' => true, 'message' => 'Phone verified successfully']);
    }

    // Resend verification code
    public function resendVerifyPhone(Request $request)
    {
        $request->validate(['ClientNo' => 'required']);
        $client = TaxPayer::where('ClientNo', $request->ClientNo)->first();
        if (!$client) return response()->json(['success' => false, 'message' => 'Client not found'], 404);
        $code = rand(100000, 999999);
        $client->phone_verification_code = $code;
        $client->save();
        $result = $this->smsService->sendSms($client->BillingAddress,  $client->phone_verification_code);

        // if (is_string($result) && str_contains($result, 'Error')) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Failed to send SMS',
        //         'error' => $result,
        //     ], 500);
        // }

        return response()->json([
            'success' => true,
            'message' => 'Verification code sent successfully.',
            'sms_response' => $result,
        ], 200);
    }

    // Login
    public function login(Request $request)
    {
        $request->validate([
            'ClientNo' => 'required',
            'password' => 'required',
        ]);

        $client = TaxPayer::where('ClientNo', $request->ClientNo)->first();

        if (!$client) {
            return $this->sendError('Unauthorized.', ['ClientNo' => 'Client number not found.'], 404);
        }

        if (!Hash::check($request->password, $client->password)) {
            return $this->sendError('Unauthorized.', ['password' => 'Invalid password.'], 404);
        }



        // Token create
        $tokenResult = $client->createToken(
            'api-token',
            ['*'],
            now()->addHours(1)
        );

        $token = $tokenResult->plainTextToken;

        return $this->sendResponse(
            'Client login successfully.',
            [
                'token' => $token,
                'client' => $client,
            ]
        );
    }


    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'Logged out']);
    }

    // Forgot password (send code)
    public function forgotPassword(Request $request)
    {
        $request->validate(['ClientNo' => 'required']);
        $client = TaxPayer::where('ClientNo', $request->ClientNo)->first();

        if (!$client) return response()->json(['success' => false, 'message' => 'Client not found'], 404);

        $code = rand(100000, 999999);
        $client->phone_verification_code = $code;
        $client->save();

        // TODO: Send $code via SMS

        $result = $this->smsService->sendSms($client->BillingAddress,  $client->phone_verification_code);

        return response()->json(['success' => true, 'message' => 'Password reset code sent']);
    }

    // Reset password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'ClientNo' => 'required',
            'code' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $client = TaxPayer::where('ClientNo', $request->ClientNo)
            ->where('phone_verification_code', $request->code)
            ->first();

        if (!$client) return response()->json(['success' => false, 'message' => 'Invalid code'], 400);

        $client->password = Hash::make($request->password);
        $client->phone_verification_code = null;
        $client->save();

        return response()->json(['success' => true, 'message' => 'Password reset successfully']);
    }
}
