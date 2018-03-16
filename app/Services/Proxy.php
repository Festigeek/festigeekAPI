<?php
namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use App\Exceptions\Festigeek\FailedInternalRequestException;

class Proxy
{
     /**
     * The app instance
     *
     * @var $app
     */
    protected $app;

    /**
     * Constructor
     *
     * @param Application $app The app instance.
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Make an internal request
     *
     * @param string $action   The HTTP verb to use.
     * @param string $resource The API resource to look up.
     * @param array  $data     The request body.
     * @throws FailedInternalRequestException Request could not be synced.
     * @return \Illuminate\Http\Response
     */
    public function doRequest(string $action, string $resource, array $data = [])
    {
        // Create request
        $request = Request::create($resource, $action, $data, [], [], [
            'HTTP_Accept' => 'application/json',
        ]);

        // Get response
        $response = $this->app->handle($request);
        dd($response);
        if ($response->getStatusCode() >= 400) {
            throw new FailedInternalRequestException($request, $response);
        }

        // Dispatch the request
        return $response;
    }
}