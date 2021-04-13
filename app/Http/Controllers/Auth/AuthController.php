<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
        $user = $request->user();
        $success['token'] =  $user->createToken('token')->accessToken;
        return $this->sendResponse($success);
    }

    public function logout(Request $request)
    {

        $isUser = $request->user()->token()->revoke();
        if($isUser){
            $success['message'] = "Successfully logged out.";
            return $this->sendResponse($success);
        }
        else{
            $error = "Something went wrong.";
            return $this->sendResponse($error);
        }


    }
}
