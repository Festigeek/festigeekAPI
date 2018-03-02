<?php

namespace App\Exceptions\Festigeek\Auth;

use Exception;

class InvalidCredentialsException extends Exception
{
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