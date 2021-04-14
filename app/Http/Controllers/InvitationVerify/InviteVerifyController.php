<?php

namespace App\Http\Controllers\InvitationVerify;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Responses\ResponseController;
use App\Mail\InvitationEmail;
use App\Models\Invite;
use App\Models\User;
use App\Models\VerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Psy\Util\Str;

class InviteVerifyController extends Controller
{
    public function ProcessInvite(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            return ResponseController::sendError($validator->errors(), 400);
        }

        if (Invite::where('email', $request->input('email'))->exists()) {
            return ResponseController::sendError('There exists an invite with this email!', 409);
        }

        do {
            $token = \Illuminate\Support\Str::random(20);
        } while (Invite::where('token', $token)->first());

        Invite::create([
            'token' => $token,
            'email' => $request->input('email')
        ]);

        $url = URL::temporarySignedRoute(
            'registrationFromLink', now()->addMinutes(1440), ['name' => $request->input('name'), 'token' => $token]
        );

        Mail::to($to_email = $request->input('email'))->send(new InvitationEmail($request->input('name'), $url));
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
            return ResponseController::sendError('You entered Invalid Pin', 417);
        }

        $pin->delete();
        return ResponseController::sendResponse(array('message' => 'Your account has been verified successfully'));
    }
}
