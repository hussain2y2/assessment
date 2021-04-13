<?php

namespace App\Http\Controllers\InvitationVerify;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Responses\ResponseController;
use App\Invite;
use App\User;
use App\VerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Validator;
use Psy\Util\Str;

class InviteVerifyController extends Controller
{
    public function ProcessInvite(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'name' => 'required'
        ]);

        $validator->after(function ($validator) use ($request) {
            if (Invite::where('email', $request->input('email'))->exists()) {
                return ResponseController::sendError('There exists an invite with this email!', 409);
            }
        });

        if ($validator->fails()) {
            return ResponseController::sendError($validator->errors(), 400);
        }

        // generate unique token for every invitation
        do {
            $token = \Illuminate\Support\Str::random(20);
        } while (Invite::where('token', $token)->first());

        Invite::create([
            'token' => $token,
            'email' => $request->input('email')
        ]);

        $url = URL::temporarySignedRoute(
            'registration', now()->addMinutes(1440), ['token' => $token]
        );


        $to_name = $request->input('name');
        $to_email = $request->input('email');
        $data = array('name' => 'Hussain Ahmad', 'invitation_link' => $url);

        Mail::send('mail', $data, function($message) use ($to_name, $to_email) {
            $message->to($to_email, $to_name)
                ->subject('Invitation Link');
            $message->from($_ENV['MAIL_USERNAME'],'ABC');
        });

        return ResponseController::sendResponse(array('message' => 'The Invite has been sent successfully'));
    }

    public function verifyEmail(Request $request) {
        $validator = Validator::make($request->all(), [
            'pin' => 'required'
        ]);
        if ($validator->fails()) {
            return ResponseController::sendError($validator->errors(), 400);
        }

        $pin = VerifyEmail::where('user_id', Auth::user()->id)->where('pin', $request->input('pin'))->first();
        if(!empty($pin) && !is_null($pin)) {
            $user = User::where('id', Auth::user()->id)->first();
            $user->active = true;
            $user->verified = true;
            $user->save();
        } else {
            return ResponseController::sendError('Invalid Pin');
        }

        $pin->delete();
        return ResponseController::sendResponse(array('message' => 'Your account verified successfully'));
    }
}
