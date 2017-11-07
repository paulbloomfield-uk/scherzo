<?php
/**
 * This file is part of the Scherzo application framework.
 *
 * @link      https://github.com/paulbloomfield-uk/scherzo
 * @license   [MIT](https://github.com/paulbloomfield-uk/scherzo/blob/master/LICENSE).
 * @copyright Copyright © 2017 [Paul Bloomfield](https://github.com/paulbloomfield-uk).
**/

namespace Scherzo\Controllers;

use Scherzo\Http\RequestInterface as Request;
use Scherzo\Http\ResponseInterface as Response;

use Scherzo\Container\Container;

/**
 * Base class for a controller to interact with the Symfony HTTP Foundation component.
**/
trait ControllerTrait {

    /** @var Container Dependencies container. */
    protected $container;

    /** @var Request HTTP request to respond to. */
    protected $request;

    /** @var Response HTTP response dependency. */
    protected $response;

    /**
     * Constructor.
     *
    **/
    public function __construct(Container $container, Request $request = null) {
        $this->container = $container;
        $this->request   = $request;
    }

    /**
     * Get an HTTP Not Found response.
     *
     * @TODO deal with the body, including passing an array of errors in an API response.
    **/
    protected function createNotFoundResponse($body = null) : Response {
        if ($body === null) {
            $path = $this->container->http->getRequestBasePath($this->request) . $this->container->http->getRequestPath($this->request);
            $body = strtr(
                'Not Found  ":path"', [
                ':path' => $path,
            ]);
        }
        return $this->createResponse($body, 404);
    }

    /**
     * @TODO handle a body as an array to give a JSON response.
    **/
    protected function createErrorResponse($body, int $status = 500) : Response {
        return $this->createResponse($body, $status);
    }

    /**
     * @TODO handle a body as an array to give a JSON response.
    **/
    protected function createResponse($body, int $status = 200, array $headers = []) : Response {
        return $this->container->http->createResponse((string)$body, $status, $headers);
    }
}
