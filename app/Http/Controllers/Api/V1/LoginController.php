<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    function checkUserRegMobile(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'phone' => 'required'
        ]);

        if ($validator->fails())
            return response(["response" => 400], 400);
        
        // ✅ LOCAL DEV BYPASS START
        if (app()->environment('local') && env('SKIP_RECAPTCHA_IN_DEV') === 'true') {
            $user = User::where('phone', $request->phone)->first();
            if (!$user) {
                return response([
                    "response" => 200,
                    "status" => false,
                    'message' => "Not Exists",
                    'data' => null,
                ], 200);
            }
            $user->tokens()->delete();
            $token = $user->createToken('my-app-token')->plainTextToken;

            return response([
                "response" => 200,
                "status" => true,
                'message' => "Login skipped reCAPTCHA in local dev",
                'data' => $user,
                'token' => $token,
            ], 200);
        }
        // ✅ LOCAL DEV BYPASS END

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response([
                "response" => 201,
                "status" => false,
                'message' => 'These credentials do not match our records.'
            ], 200);
        } else {
            return response([
                "response" => 201,
                "status" => true,
                'message' => 'User exists'
            ], 200);
        }
    }
    function loginMobile(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'phone' => 'required'
        ]);

        if ($validator->fails())
            return response(["response" => 400], 400);

        $user = User::where('phone', $request->phone)->where('is_deleted', '=', false)->first();

        if (!$user) {
            return response([
                "response" => 200,
                "status" => false,
                'message' => "Not Exists",
                'data' => null,

            ], 200);
        }
        $user->tokens()->delete();
        $token = $user->createToken('my-app-token')->plainTextToken;


        $response = [
            "response" => 200,
            "status" => true,
            'message' => "Successfully",
            'data' => $user,
            'token' => $token,
        ];

        return response($response, 200);
    }
    function ReLoginMobile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
        ]);

        if ($validator->fails()) {
            return response(["response" => 400], 400);
        }

         // ✅ LOCAL DEV BYPASS START
        if (app()->environment('local') && env('SKIP_RECAPTCHA_IN_DEV') === 'true') {
            $user = User::where('phone', $request->phone)->first();
            if (!$user) {
                return response([
                    "response" => 200,
                    "status" => false,
                    'message' => "User does not exist",
                    'data' => null,
                ], 200);
            }

            return response([
                "response" => 200,
                "status" => true,
                'message' => "ReLogin skipped reCAPTCHA in local dev",
                'data' => $user,
            ], 200);
        }
        // ✅ LOCAL DEV BYPASS END

        $user = User::where('phone', $request->phone)->where('is_deleted', '=', false)->first();

        if (!$user) {
            return response([
                "response" => 200,
                "status" => false,
                'message' => "User does not exist",
                'data' => null,
            ], 200);
        }

        return response([
            "response" => 200,
            "status" => true,
            'message' => "Successfully retrieved user data",
            'data' => $user,
        ], 200);
    }



    function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                "response" => 201,
                "status" => false,
                'message' => 'These credentials do not match our records.'
            ], 200);
        }
        $user->tokens()->delete();
        $token = $user->createToken('my-app-token')->plainTextToken;


        $user->role = DB::table("users_role_assign")
            ->select(
                'users_role_assign.*',
                'roles.name as name',
            )
            ->Join('roles', 'roles.id', '=', 'users_role_assign.role_id')
            ->where('users_role_assign.user_id', '=', $user->id)
            ->get();

        $response = [
            "response" => 200,
            "status" => true,
            'message' => "Successfully",
            'data' => $user,
            //  'roles'=> $roles, 
            'token' => $token,
        ];

        return response($response, 200);
    }


    public function logout(Request $request) 
    {
        // Get the authenticated user
        $user = $request->user();
    
        // Revoke the current user's token
        $user->currentAccessToken()->delete();
    
        // Update FCM and web FCM tokens to null
        $user->update([
            'fcm' => null,
            'web_fcm' => null,
        ]);
    
        $dataModel= User::where("id", $user->id)->first();
        $dataModel->fcm = null ;
        $dataModel->web_fcm = null ;
        $dataModel->save();
        return response([
            "response" => 200,
            "status" => true,
            "message" => "Logged out successfully."
        ], 200);
    }
}
