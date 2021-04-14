<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => ['guest:api']], function () {
    Route::get('/registration/{token}', 'Users\UsersController@RegistrationFromLink')->name('registrationFromLink');
    Route::POST('/user/registration', 'Users\UsersController@Create')->name('register');
    Route::POST('/user/login', 'Auth\AuthController@login')->name('login');
});
Route::group(['middleware' => 'auth:api'], function() {
    Route::POST('/user/profile/update', 'Users\UsersController@ProfileUpdate')->name('ProfileUpdate');

    Route::POST('/verify/email', 'InvitationVerify\InviteVerifyController@verifyEmail')->name('verify_email');
    Route::group(['middleware' => ['role:Admin']], function () {
        Route::post('/users/invite', 'InvitationVerify\InviteVerifyController@ProcessInvite')->name('processInvite');
    });
});
