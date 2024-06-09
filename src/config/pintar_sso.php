<?php

return [
    'client_id' => env('PINTAR_SSO_CLIENT_ID'),
    'client_secret' => env('PINTAR_SSO_CLIENT_SECRET'),
    'auth_domain' => env('PINTAR_SSO_AUTH_DOMAIN', 'https://sso.banjarmasinkota.go.id'),
    'post_login' => env('PINTAR_SSO_POST_LOGIN'),
    'post_bind' => env('PINTAR_SSO_POST_BIND'),
    'register_url' => env('PINTAR_SSO_REGISTER_URL'),
];
