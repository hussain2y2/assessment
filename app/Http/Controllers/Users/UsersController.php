<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Responses\ResponseController;
use App\Invite;
use App\User;
use App\VerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Validator;

class UsersController extends Controller
{
    public function RegistrationFromLink($token)
    {
        $invite = Invite::where('token', $token)->first();
        if(!empty($invite)) {
            return ResponseController::sendResponse(array('data' => $invite));
        } else {
            return ResponseController::sendError('Link is expired or not correct');
        }
    }

    public function Create(Request $request)
    {

        $invite = Invite::where('token', $request->input('token'))->first();

        $user = new User();
        $user->name = $request->input('userName');
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

//        $to_name = $request->input('userName');
//        $to_email = $invite->email;
//        $data = array('name' => 'Hussain Ahmad', 'Verify Email' => $pin);
//
//        Mail::send('mail', $data, function($message) use ($to_name, $to_email) {
//            $message->to($to_email, $to_name)
//                ->subject('Verify Email');
//            $message->from($_ENV['MAIL_USERNAME'],'ABCD');
//        });

        $invite->delete();
        return ResponseController::sendResponse(array('message' => 'Your account has been made, please verify it by entering 6 digit pin that has been send to your email.' . $pin));
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
