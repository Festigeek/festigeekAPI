<?php
namespace App\Services;

use App\User;
use App\Services\Proxy;

class OAuthProxy extends Proxy
{
    const REFRESH_TOKEN = 'refreshToken';

    public function __construct(){
        parent::__construct(app());
    }

    /**
     * Attempt to create an access token using user credentials
     *
     * @param string $email
     * @param string $password
     */
    public function attemptLogin($email, $password)
    {
        try {
            $user = User::where('email', $email)->firstOrFail();
            return $this->proxy('password', [
                'username' => $email,
                'password' => $password
            ]);
        }
        catch(\Exception $e){
            throw new InvalidCredentialsException();
        }
    }

    /**
     * Attempt to refresh the access token used a refresh token that
     * has been saved in a cookie
     */
    public function attemptRefresh()
    {
        $refreshToken = $this->request->cookie(self::REFRESH_TOKEN);

        return $this->proxy('refresh_token', [
            'refresh_token' => $refreshToken
        ]);
    }

    /**
     * Request a proxy call to the OAuth server.
     *
     * @param string $grantType what type of grant type should be proxied
     * @param array $data the data to send to the server
     * @throws InvalidCredentialsException
     */
    public function request($grantType, array $data = [])
    {
        $data = array_merge($data, [
            'client_id'     => env('OAUTH_PASSWORD_CLIENT_ID'),
            'client_secret' => env('OAUTH_PASSWORD_CLIENT_SECRET'),
            'grant_type'    => $grantType
        ]);

        $proxyResponse = parent::doRequest('post', '/oauth/token', $data);
        $data = json_decode($proxyResponse->content());

        $response = response()->json([
            'success' => 'Authenticated.', 
            'token' => $data->access_token,
            'token_type' => 'bearer',
            'expires_in' => $data->expires_in
        ])->cookie(
            self::REFRESH_TOKEN,
            $data->refresh_token,
            864000, // 10 days
            null,
            null,
            false,
            true // HttpOnly
        );

        return $response;
    }
}