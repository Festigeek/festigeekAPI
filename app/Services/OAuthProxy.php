<?php
namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Exceptions\Festigeek\InvalidCredentialsException;

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
            $response = $this->request('password', [
                'username' => $email,
                'password' => $password
            ]);

            return $response;
        } catch (InvalidCredentialsException $exception) {
            return response()->json(['error' => 'Authentication error', 'infos' => $exception->getMessage()], 401);
        }
    }

    /**
     * Attempt to refresh the access token used a refresh token that
     * has been saved in a cookie
     */
    public function attemptRefresh(Request $request)
    {
        $refreshToken = $request->cookie(self::REFRESH_TOKEN);

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
    private function request($grantType, array $data = [])
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

    /**
     * Logs out the user. We revoke access token and refresh token.
     * Also instruct the client to forget the refresh cookie.
     */
    public function logout(Request $request)
    {
        $accessToken = $request->user()->token();

        DB::table('oauth_refresh_tokens')
        ->where('access_token_id', $accessToken->id)
        ->update(['revoked' => true]);

        $accessToken->revoke();

        return response()->json(['Logout successful'], 204)->withCookie($request->cookie->forget(self::REFRESH_TOKEN));
    }
}