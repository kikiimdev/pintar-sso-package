<?php

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Banjarmasinkota\PintarSSO\PintarSSO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::prefix('sso')->group(function () {
    Route::get('/bind', function (Request $request) {
        $callback_url = url('sso/bind/callback');
        $sso = new PintarSSO($callback_url);

        return $sso->redirect_to_authorization_url();
    });

    Route::get('/bind/callback', function (Request $request) {
        try {
            $sso = new PintarSSO();

            $pintar_account = $sso->get_user_from_callback($request);
            $user = request()->user()->pintar_account()->updateOrCreate([
                'pintar_id' => $pintar_account->id,
            ], [
                'pintar_id' => $pintar_account->id,
            ]);

            return response()->redirectToIntended(url(config('pintar_sso.post_bind')));
        } catch (\Exception $error) {
            return response()->json($error->getMessage(), 500);
        }
    });

    Route::get('/login', function (Request $request) {
        $callback_url = url('sso/login/callback');
        $sso = new PintarSSO($callback_url);

        return $sso->redirect_to_authorization_url();
    });

    Route::get('/login/callback', function (Request $request) {
        try {
            $sso = new PintarSSO();
            $pintar_account = $sso->get_user_from_callback($request);

            // find the user that has $pintar_sso_user->id
            $user = User::whereRelation('pintar_account', 'pintar_id', $pintar_account->id)->first();

            // Login using user id
            Auth::loginUsingId($user->id);

            $sso->log_activity($request, 'LOGIN');

            return response()->redirectToIntended(url(config('pintar_sso.post_login')));
        } catch (\Exception $error) {
            return response()->json($error->getMessage(), 500);
        }
    });
});
