<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Responses\ResponseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors());
        }

        $credentials = request(['email', 'password']);
        if(!Auth::attempt($credentials)){
            $error = "Unauthorized";
            return $this->sendError($error, 401);
        }
        $user = Auth::user();
        if (!$user->verified) {
            $token =  $user->createToken('token')->accessToken;
            return ResponseController::sendResponse(array('message' => 'Your Email is not Verified', '_token' => $token), 403);
        }
        if (!$user->active) {
            return ResponseController::sendError('User is not active', 403);
        }
        $token =  $user->createToken('token')->accessToken;
        return ResponseController::sendResponse(array('_token' => $token));
    }

    public function logout(Request $request)
    {

        $isUser = $request->user()->token()->revoke();
        if($isUser){
            return ResponseController::sendResponse(array('message' => 'Successfully logged out'));
        }
        else{
            return ResponseController::sendError('Something went wrong', 500);
        }


    }
}
