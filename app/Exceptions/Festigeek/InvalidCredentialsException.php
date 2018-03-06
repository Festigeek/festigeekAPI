<?php
/**
 * Created by IntelliJ IDEA.
 * User: Iosis
 * Date: 06.03.2018
 * Time: 00:10
 */

namespace App\Exceptions\Festigeek;

class InvalidCredentialsException extends \Exception
{

    /**
     * InvalidCredentialsException constructor.
     */
    public function __construct() { }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        return response()->json(['error' => 'Invalid Credentials'], 401);
    }
}