<?php
/**
 * This file is part of the Scherzo application framework.
 *
 * @link      https://github.com/paulbloomfield-uk/scherzo
 * @license   [MIT](https://github.com/paulbloomfield-uk/scherzo/blob/master/LICENSE).
 * @copyright Copyright © 2017 [Paul Bloomfield](https://github.com/paulbloomfield-uk).
**/

namespace Scherzo\HttpHandler\Diactoros;

use Scherzo\Services\ServiceTrait;

use Scherzo\Exception;

// the external dependencies are alised to these classes for convenience
use Scherzo\Request;
use Scherzo\Response;

// external dependency for HTTP messaging
use Zend\Diactoros\ServerRequest as BaseRequest;
use Zend\Diactoros\Response as BaseResponse;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\ServerRequestFactory;

/**
 * Process HTTP request and response messages.
 *
 * @package Scherzo
**/
class HttpService {

    use ServiceTrait;

    /**
     * Create a response object.
     *
     * @param  string  $body      The response body.
     * @param  int     $status    Response status if not 200.
     * @param  array   $headers   Any response headers.
     * @param  string  $protocol  For future enhancement.
     *
     * @return Response A response object.
    **/
    public function createResponse(string $body, int $status = 200, array $headers = []) {

        return new HtmlResponse($body, $status, $headers);

    }

    /**
     * Middleware to create the request object.
     *
     * @param  callable  $next     Method to invoke the next link in the chain of responsibility.
     * @param  null      $request  Null because the request hasn't yet been parsed.
    **/
    public function parseRequest(callable $next, Request $request = null) : Response {
        // create the request - it all starts here
        $request = ServerRequestFactory::fromGlobals();
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
    public function sendResponse(callable $next, Request $request) : Response {
        // get a response from the next handler in the stack
        $response = $next($request);

        // if (!is_a($response, Response::class)) {
        //     $response = new Response($response);
        // }

        // send the response
        $emitter = new \Zend\Diactoros\Response\SapiEmitter();
        $emitter->emit($response);
        return $response;
    }

    /**
     * Initialise - this is called by the parent constructor.
    **/
    protected function initialize() {
        // alias HTTP class dependencies as this implementation doesn't overload them
        class_alias(BaseRequest::class, Request::class);
        class_alias(BaseResponse::class, Response::class);
    }
}
