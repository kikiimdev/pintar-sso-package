<?php

namespace Banjarmasinkota\PintarSSO;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class PintarSSO {
    protected string $client_id;
    protected string $client_secret;
    protected string $callback_url;
    protected string $base_url;
    public string $authorize_endpoint;
    public string $token_endpoint;
    public string $user_profile_endpoint;
    public string $activity_endpoint;

    public function __construct(string $callback_url = "") {
        $this->client_id = config('pintar_sso.client_id');
        $this->client_secret = config('pintar_sso.client_secret');
        $this->callback_url = $callback_url;
        $this->base_url = config('pintar_sso.auth_domain');
        $this->authorize_endpoint = $this->base_url . "/api/oauth/authorize";
        $this->token_endpoint = $this->base_url . "/api/oauth/accessToken";
        $this->user_profile_endpoint = $this->base_url . "/api/oauth/me";
        $this->activity_endpoint = $this->base_url . "/api/v1/activity";
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

        // $this->log_activity($request, 'LOGIN');

        return (object) $response['user'];
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

    function log_activity(Request $request, $type = 'LOG') {
        $user = auth()->user();

        $pintar_account = $user->pintar_account;
        $meta = [];
        $meta['userId'] = $user->id;
        $path = $request->getPathInfo();
        $query = $request->all();

        $meta['path'] = $path;
        $meta['query'] = $query;
        $meta['user-agent'] = $request->header('user-agent');
        $meta['ip'] = $request->ip();

        $response = Http::withHeaders([
            'clientId' => $this->client_id,
            'clientSecret' => $this->client_secret,
        ])
        ->post($this->activity_endpoint, [
            'userId' => $pintar_account->pintar_id,
            'type' => $type,
            'meta' => $meta,
        ]);

        return $response;

    }

    function find_many_role() {
        $response = Http::withHeaders([
            'clientId' => $this->client_id,
            'clientSecret' => $this->client_secret,
        ])
        ->get($this->base_url . "/api/v1/role");

        return $response;
    }

    function update_user_role(String $pintar_id, String $role_id) {
        $response = Http::withHeaders([
            'clientId' => $this->client_id,
            'clientSecret' => $this->client_secret,
        ])
        ->put($this->base_url . "/api/v1/user/" . $pintar_id . "/role", [
            'roleId' => $role_id
        ]);

        return $response;
    }

    function get_user_by_pintar_id(String $pintar_id) {
        $response = Http::withHeaders([
            'clientId' => $this->client_id,
            'clientSecret' => $this->client_secret,
        ])
        ->get($this->base_url . "/api/v1/user/" . $pintar_id);

        return $response;
    }
}
