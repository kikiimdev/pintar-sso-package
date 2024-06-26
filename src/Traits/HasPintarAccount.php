<?php

namespace Banjarmasinkota\PintarSSO\Traits;

use Illuminate\Http\Request;
use App\Models\PintarAccount;
use Illuminate\Support\Facades\Auth;
use Banjarmasinkota\PintarSSO\PintarSSO;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cookie;

trait HasPintarAccount
{
    public function pintar_account(): HasOne
    {
        return $this->hasOne(PintarAccount::class);
    }

    public static function redirect_to_authorization_url()
    {
        $callback_url = url('sso/bind/callback');
        $sso = new PintarSSO($callback_url);
        return $sso->redirect_to_authorization_url();
    }

    public static function get_user_from_callback(Request $request, bool $redirect = false)
    {
        $sso = new PintarSSO();
        $pintar_account = $sso->get_user_from_callback($request);
        $meta = json_decode(json_encode($pintar_account));
        $account = request()->user()->pintar_account()->updateOrCreate(
            [
                'user_id' => auth()->user()->id,
                'pintar_id' => $pintar_account->id,
            ],
            [
                'meta' => $meta
            ]
        );

        if ($redirect) {
            return response()->redirectToIntended(url(config('pintar_sso.post_bind')));
        }

        return $account;
    }

    public static function redirect_to_login_page(Request $request)
    {
        $callback_url = url('sso/login/callback');
        $sso = new PintarSSO($callback_url);


        $query = null;
        if ($request->has('authorized')) {
            $query['authorized'] = true;
        }

        $redirect_to = $request->query('redirect_to');
        if ($redirect_to) {
            $query['redirect_to'] = $redirect_to;
            cookie()->queue('redirect_to', $redirect_to, 10);
        }

        return $sso->redirect_to_authorization_url($query);
    }

    public static function login_from_callback(Request $request, bool $redirect = false)
    {
        $sso = new PintarSSO();
        $pintar_account = $sso->get_user_from_callback($request);

        // find the user that has $pintar_sso_user->id
        $user = static::whereRelation('pintar_account', 'pintar_id', $pintar_account->id)->first();
        if (!$user) {
            $register_url = config('pintar_sso.register_url');
            return response()->redirectToIntended(url($register_url . '?pintar_id=' . $pintar_account->id . '&name=' . $pintar_account->name));
        }

        // Login using user id
        Auth::loginUsingId($user->id);

        $meta = json_decode(json_encode($pintar_account));
        $account = request()->user()->pintar_account()->updateOrCreate(
            [
                'user_id' => $user->id,
                'pintar_id' => $pintar_account->id,
            ],
            [
                'meta' => $meta
            ]
        );

        $sso->log_activity($request, 'LOGIN');

        if ($redirect) {
            $redirect_to = Cookie::get('redirect_to');
            if ($redirect_to) {
                return response()->redirectToIntended(url($redirect_to));
            }

            return response()->redirectToIntended(url(config('pintar_sso.post_login')));
        }

        return $user;
    }
}
