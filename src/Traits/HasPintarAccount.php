<?php

namespace Banjarmasinkota\PintarSSO\Traits;

use Illuminate\Http\Request;
use App\Models\PintarAccount;
use Illuminate\Support\Facades\Auth;
use Banjarmasinkota\PintarSSO\PintarSSO;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    $account = request()->user()->pintar_account()->updateOrCreate([
      'pintar_id' => $pintar_account->id,
      'meta' => $pintar_account
    ], [
      'pintar_id' => $pintar_account->id,
      'meta' => $pintar_account
    ]);

    if ($redirect) {
      return response()->redirectToIntended(url(config('pintar_sso.post_bind')));
    }

    return $account;
  }

  public static function redirect_to_login_page()
  {
    $callback_url = url('sso/login/callback');
    $sso = new PintarSSO($callback_url);

    return $sso->redirect_to_authorization_url();
  }

  public static function login_from_callback(Request $request, bool $redirect = false)
  {
    $sso = new PintarSSO();
    $pintar_account = $sso->get_user_from_callback($request);

    // find the user that has $pintar_sso_user->id
    $user = static::whereRelation('pintar_account', 'pintar_id', $pintar_account->id)->first();

    // Login using user id
    Auth::loginUsingId($user->id);

    $sso->log_activity($request, 'LOGIN');

    if ($redirect) {
      return response()->redirectToIntended(url(config('pintar_sso.post_login')));
    }

    return $user;
  }
}
