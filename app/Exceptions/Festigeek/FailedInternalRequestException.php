<?php

namespace App\Exceptions\Festigeek;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FailedInternalRequestException extends \Exception
{
    /**
     * Request instance
     *
     * @var $request
     */
    protected $request;

    /**
     * Response instance
     *
     * @var $response
     */
    protected $response;

    /**
     * Constructor
     *
     * @param Request  $request  The request object.
     * @param Response $response The response object.
     * @return void
     */
    public function __construct(Request $request, Response $response)
    {
        parent::__construct();
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Get request object
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }
    
    /**
     * Get response object
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

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