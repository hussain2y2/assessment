<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Responses\ResponseController;
use App\Models\Invite;
use App\Models\User;
use App\Models\VerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller
{
    public function RegistrationFromLink($token)
    {
        $invite = Invite::where('token', $token)->first();
        if(!empty($invite)) {
            echo '<h2>Registration Form Open</h2>';
        } else {
            echo '<h2>Link Expire or not correct</h2>';
        }
    }

    public function Create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'userName' => 'required|min:04|max:20',
            'token' => 'required',
            'password' => 'required'
        ]);
        if ($validator->fails()) {
            return ResponseController::sendError($validator->errors(), 400);
        }

        $invite = Invite::where('token', $request->input('token'))->first();

        $user = new User();
        $user->name = $request->input('name');
        $user->user_name = $request->input('userName');
        $user->email = $invite->email;
        $user->password = Hash::make($request->input('password'));
        $user->user_role = 'User';
        $user->save();

        $pin = rand(100000, 999999);

        $verify = new VerifyEmail();
        $verify->user_id = $user->id;
        $verify->pin = $pin;
        $verify->save();

        Mail::to($invite->email)->send(new \App\Mail\VerifyEmail($request->input('name'), $pin));
        $invite->delete();
        return ResponseController::sendResponse(array('message' => 'Your account has been created, please verify it by entering 6 digit pin that has been send to your email.'));
    }

    public function ProfileUpdate(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',
            'user_name' => 'required|min:04|max:20',
            'avatar' => 'mimes:jpeg,jpg,png|required|max:2048' //2mb
        ]);
        if ($validator->fails()) {
            return ResponseController::sendError($validator->errors(), 400);
        }

        $user = User::where('id', Auth::user()->id)->first();

        if(!empty($request->input('name'))) {
            $user->name = $request->input('name');
        }

        if(!empty($request->input('email'))) {
            $user->email = $request->input('email');
        }

        if(!empty($request->input('user_name'))) {
            $user->user_name = $request->input('user_name');
        }

        if($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $file_name = $file->getClientOriginalName();
            $imageDetails = getimagesize($file);
            if($imageDetails[0] == 256 && $imageDetails[1] == 256) {
                $file->move(public_path() . '/avatars/', $file_name);
                $user->avatar = $file_name;
            } else {
                return ResponseController::sendError('Avatar dimension must be 256x256 px.' , 400);
            }
        }

        $user->save();
        return ResponseController::sendResponse(array('message' => 'User profile updated Successfully'));

    }
}
