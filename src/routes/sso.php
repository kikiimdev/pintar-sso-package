<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('sso')->group(function () {
    Route::get('/bind', function (Request $request) {
        return User::redirect_to_authorization_url();
    });

    Route::get('/bind/callback', function (Request $request) {
        try {
            return User::get_user_from_callback($request, true);
        } catch (\Exception $error) {
            return response()->json($error->getMessage(), 500);
        }
    });

    Route::get('/login', function (Request $request) {
        return User::redirect_to_login_page($request);
    });

    Route::get('/login/callback', function (Request $request) {
        try {
            return User::login_from_callback($request, true);
        } catch (\Exception $error) {
            return response()->json($error->getMessage(), 500);
        }
    });
});
