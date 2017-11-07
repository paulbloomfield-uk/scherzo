<?php
/**
 * This file is part of the Scherzo application framework.
 *
 * @link      https://github.com/paulbloomfield-uk/scherzo
 * @license   [MIT](https://github.com/paulbloomfield-uk/scherzo/blob/master/LICENSE).
 * @copyright Copyright © 2017 [Paul Bloomfield](https://github.com/paulbloomfield-uk).
**/

namespace Scherzo\Http\HttpFoundation;

use Scherzo\Services\ServiceTrait;

use Scherzo\Http\HttpFoundation\Request;
use Scherzo\Http\HttpFoundation\Response;

/**
 * Process HTTP request and response messages.
 *
 * @package Scherzo
**/
class HttpService {

    use ServiceTrait;

    /**
     *
    **/
    public function getRequestAttribute(Request $request, string $name) {
        return $request->attributes->get($name);
    }

    /**
     *
    **/
    public function setRequestAttribute(Request $request, string $name, $value) : self {
        $request->attributes->set($name, $value);
        return $this;
    }

    /**
     * Get the HTTP request method from a request.
     *
     * @param  Request $request  A request object.
     * @return string  The request method.
    **/
    public function getRequestMethod(Request $request) : string {
        return $request->getMethod();
    }

    /**
     *
    **/
    public function getRequestBasePath(Request $request) : string {
        return $request->getBasePath();
    }

    /**
     *
    **/
    public function getRequestPath(Request $request) : string {
        return $request->getPathInfo();
    }

    /**
     * Create a response object.
     *
     * @param  string  $body      The response body.
     * @param  int     $status    Response status if not 200.
     * @param  array   $headers   Any response headers.
     *
     * @return Response A response object.
    **/
    public function createResponse(string $body, int $status = 200, array $headers = []) {
        return new Response($body, $status, $headers);
    }

    /**
     * Middleware to create the request object.
     *
     * @param  callable  $next     Method to invoke the next link in the chain of responsibility.
     * @param  null      $request  Null because the request hasn't yet been parsed.
    **/
    public function parseRequestMiddleware(callable $next, Request $request = null) : Response {
        // create the request - it all starts here
        $request = Request::createFromGlobals();
        // add important parts of the request to the applicaiton configuration
        // $c->config->baseUrl = $request->getUriForPath(null);
        // return the response from the next handler in the chain
        return $next($request);
    }

    /**
    * Middleware to send the response.
    *
    * @param  callable  $next     Method to invoke the next link in the chain of responsibility.
    * @param  null      $request  The current request.
    **/
    public function sendResponseMiddleware(callable $next, Request $request) : Response {
        // get a response from the next handler in the stack
        $response = $next($request);

        // if (!is_a($response, Response::class)) {
        //     $response = new Response($response);
        // }
        // send the response
        $response->prepare($request);
        $response->send();
        return $response;
    }
}
