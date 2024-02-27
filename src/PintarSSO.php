<?php

namespace Banjarmasinkota\PintarSSO;

use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class PintarSSO {
    protected string $client_id;
    protected string $client_secret;
    protected string $callback_url;
    protected string $base_url;
    public string $authorize_endpoint;
    public string $token_endpoint;
    public string $user_profile_endpoint;

    public function __construct(string $callback_url = "") {
        $this->client_id = config('pintar_sso.client_id');
        $this->client_secret = config('pintar_sso.client_secret');
        $this->callback_url = $callback_url;
        $this->base_url = env('PINTAR_SSO_AUTH_DOMAIN', "http://localhost:3000");
        // $this->base_url = "http://localhost:3000";
        $this->authorize_endpoint = $this->base_url . "/api/oauth/authorize";
        $this->token_endpoint = $this->base_url . "/api/oauth/accessToken";
        $this->user_profile_endpoint = $this->base_url . "/api/oauth/me";
    }

    function get_user_from_callback($request) {
        $code = $request->input('code');
        $state = $request->input('state');
        $storedState = $request->cookie('state');

        if (!$code || !$storedState || $state !== $storedState) {
            // 400
            throw ValidationException::withMessages(['message' => 'Invalid request']);
        }

        $tokens = $this->validate_authorization_code($code);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $tokens['access_token'],
        ])->get($this->user_profile_endpoint);

        return $response['user'];
    }

    function validate_authorization_code(string $code) {
        $response = Http::post($this->token_endpoint, [
            'code' => $code,
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
        ]);

        return $response;
    }

    function create_authorization_url(string $state) {
        $url = $this->authorize_endpoint . '?'
            . 'response_type=code'
            . '&client_id=' . $this->client_id
            . '&state=' . $state
            . '&redirect_uri=' . $this->callback_url;

        return $url;
    }

    function redirect_to_authorization_url(Object $query = null) {
        $state = $this->generate_state();
        $url = $this->create_authorization_url($state);

        $cookieName = "state";
        $cookieValue = $state;
        $cookieExpireMinutes = 10;
        $cookiePath = 0;
        $cookieRequestDomain = null;
        $cookieSecure = env('APP_ENV') === 'production';
        // $cookieSecure = false;
        $cookieHttpOnly = true;

        return response()
            ->redirectTo($url)
            ->cookie(
                $cookieName,
                $cookieValue,
                $cookieExpireMinutes,
                $cookiePath,
                $cookieRequestDomain,
                $cookieSecure,
                $cookieHttpOnly
            )
            ->cookie(
                $cookieValue,
                json_encode($query)
            );
    }

    function generate_state($length = 22) {
        $characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-";
        $random_string = "";
        for ($i = 0; $i < $length; $i++) {
          $random_string .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $random_string;
    }
}
