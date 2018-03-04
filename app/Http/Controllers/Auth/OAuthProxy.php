<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\Festigeek\Auth\InvalidCredentialsException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client as GuzzleClient;

class OAuthProxy
{
    const REFRESH_TOKEN = 'refreshToken';

    /**
     * Proxy a request to the OAuth server.
     *
     * @param string $grantType what type of grant type should be proxied
     * @param array $data the data to send to the server
     * @throws InvalidCredentialsException
     */
    public function proxy($grantType, array $data = [])
    {
        $data = array_merge($data, [
            'client_id'     => env('OAUTH_PASSWORD_CLIENT_ID'),
            'client_secret' => env('OAUTH_PASSWORD_CLIENT_SECRET'),
            'grant_type'    => $grantType
        ]);

        $client = new GuzzleClient();
        $response = $client->get('/oauth/token', [
            'form_params' => $data
        ]);

        $data = json_decode($response->getBody());

        // Create a refresh token cookie
        $this->cookie->queue(
            self::REFRESH_TOKEN,
            $data->refresh_token,
            864000, // 10 days
            null,
            null,
            false,
            true // HttpOnly
        );

        return [
            'access_token' => $data->access_token,
            'expires_in' => $data->expires_in
        ];
    }
}