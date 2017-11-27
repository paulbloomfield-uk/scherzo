<?php
/**
 * This file is part of the Scherzo application framework.
 *
 * @link      https://github.com/paulbloomfield-uk/scherzo
 * @license   [MIT](https://github.com/paulbloomfield-uk/scherzo/blob/master/LICENSE).
 * @copyright Copyright © 2017 [Paul Bloomfield](https://github.com/paulbloomfield-uk).
**/

namespace Scherzo\Router;

use Scherzo\Router\RouterException;

use Scherzo\Services\ServiceTrait;

use Scherzo\Http\RequestInterface as Request;
use Scherzo\Http\ResponseInterface as Response;

// external dependency
use FastRoute\RouteCollector as Collector;
use FastRoute\RouteParser\Std as Parser;
use FastRoute\DataGenerator\GroupCountBased as DataGenerator;
use FastRoute\Dispatcher\GroupCountBased as Matcher;

class Router {

    use ServiceTrait;

    protected $routes;

    /**
     * Initialize - this is called by the parent constructor.
    **/
    public function initialize() {
        $this->routes = new Collector(new Parser, new DataGenerator);
    }

    /**
     * Add a route.
     *
     * @param  string|array  $methods  Methods for the route.
     * @param  string        $pattern  Pattern to match.
     * @param  mixed         $definition  Definition of how to handle the route.
     *
     * @return  $this  Chainable.
    **/
    public function addRoute($method, string $route, $definition) : self {
        $this->routes->addRoute($method, $route, $definition);
        return $this;
    }

    /**
     * Add many routes..
    **/
    public function addRoutes(array $routes) : self {
        $callback = function ($route) {
            $this->addRoute($route[0], $route[1], $route[2]);
        };
        array_walk($routes, $callback);
        return $this;
    }

    // TODO: public function dispatch(Request $request, Container $c) : Response {
    public function match(string $method, string $path) {

        $dispatcher = new Matcher($this->routes->getData());
        $routeInfo = $dispatcher->dispatch($method, $path);
        switch ($routeInfo[0]) {
            case Matcher::FOUND:
                return [
                    'method' => $method,
                    'path' => $path,
                    'route' => $routeInfo[1],
                    'vars' => $routeInfo[2],
                ];
            case Matcher::NOT_FOUND:
                throw new RouterNotFoundException("Not Found $method $path");
            case Matcher::METHOD_NOT_ALLOWED:
                throw (new RouterMethodNotAllowedException(
                    "Method $method not allowed for path $path"))
                    ->setAllowedMethods($routeInfo[1]);
            default:
                throw new RouterException(
                    "Invalid code [$routeInfo[0]] returned from route matcher");
        }
    }

    /**
     * Middleware to match a route.
     *
     * @todo Move this somewhere else - it does not need to be part of either the Router service or
     *       the Http service.
     *
     * @param  callable  $next     Method to invoke the next link in the chain of responsibility.
     * @param  Request   $request  Null because the request hasn't yet been parsed.
     * @return Response  The response from the rest of the pipeline.
    **/
    public function matchRouteMiddleware(callable $next, Request &$request = null, Response &$response = null) : void {
        $http = $this->container->http;
        $router = $this->container->router;

        $router->addRoutes($this->container->config['routes']);

        $method = $http->getRequestMethod($request);
        $path = $http->getRequestPath($request);

        try {
            $route = $router->match($method, $path);
        } catch (\Scherzo\Router\RouterException $e) {
            $response = $http->createResponse('Not Found', 404);
            return;
        }

        $http->setRequestAttribute($request, 'route', $route);

        $next($request, $response);
    }

    /**
     * Middleware to execute a route.
     *
     * @todo Move this somewhere else - it does not need to be part of either the Router service or
     *       the Http service.
     *
     * @param  callable  $next     Method to invoke the next link in the chain of responsibility.
     * @param  Request   $request  Null because the request hasn't yet been parsed.
     * @return Response  The response from the rest of the pipeline.
    **/
    public function executeRouteMiddleware(callable $next, Request &$request = null, Response &$response = null) : void {

        $http = $this->container->http;
        $route = $http->getRequestAttribute($request, 'route');
        $action = $route['route'];
        $vars = $route['vars'];

        if ($action instanceof \Closure) {
            // Load a controller from a Closure.
            $controllerResponse = $action->call($this->container, $vars, $request, $response);
        } elseif (class_exists($action[0])) {
            // Load a controller class.
            $controller = new $action[0]($this->container, $request, $response);
            $method = $action[1];
            $controllerResponse = $controller->$method($route['vars']);
        } else {
            throw new RouterException([
                'Could not execute route for action ":action"', [
                ':action' => $action[0],
            ]]);
        }
        // a response object is OK, but it is the reference that needs modifying
        if ($controllerResponse instanceof Response) {
            $response = $controllerResponse;
            return;
        }
        // if we have a void response this is good
        if ($controllerResponse === null && $response instanceof Response) {
            return;
        }
        // otherwise, lets create the response
        $response = $http->createResponse($controllerResponse ?? 'Null response');
        return;
    }
}
