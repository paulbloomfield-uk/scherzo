<?php
/**
 * This file is part of the Scherzo application framework.
 *
 * @link      https://github.com/paulbloomfield-uk/scherzo
 * @license   [MIT](https://github.com/paulbloomfield-uk/scherzo/blob/master/LICENSE).
 * @copyright Copyright © 2017 [Paul Bloomfield](https://github.com/paulbloomfield-uk).
**/

namespace Scherzo;

use Scherzo\Exception;
use Scherzo\Container\Container;
use Scherzo\Pipeline\HandlerStack;

// services
use Scherzo\Services\Config;
use Scherzo\Http\HttpFoundation\HttpService as Http;
// use Scherzo\Http\Diactoros\HttpService as Http;
use Scherzo\Router\Router;

/**
 * Scherzo Front Controller.
**/
class FrontController {

    /** @var Container DI container. */
    protected $container;

    /** @var array Default settings. */
    protected $defaults = [
        'app' => [
            'container' => Container::class,
            'config' => Config::class,
        ],
        'services' => [
            'http' => Http::class,
            // 'errors' => Errors::class,
            'router' => Router::class,
        ],
        'routes' => [],
        'middleware' => [],
        'routeMiddleware' => [],
    ];

    /** @var array Http handler stacks. */
    // HttpFoundation Request::createFromGlobals();
    // HttpFoundation Request::createFromGlobals();
    protected $httpStack = [
        // ['errors', 'middleware'],
        ['http', 'parseRequestMiddleware', 'Parse request'],
        // ['logger', 'middleware'],
        ['http', 'sendResponseMiddleware', 'Send response'],
        // ['$this', 'insertAppMiddlewareMiddleware'],
        ['router', 'matchRouteMiddleware', 'Match route'],
        // ['$this', 'insertRouteMiddlewareMiddleware'],
        ['router', 'executeRouteMiddleware', 'Execute route'],
    ];

    /**
     * Run the application.
     *
     * @param  array  $options  Array of configuration settings arrays.
    **/
    public function run(array $options = []) : void {
        // $time = microtime(true);
        try {

            // don't want any uncaught errors
            set_error_handler(function ($severity, $message, $file, $line) {
                if (!(error_reporting() & $severity)) {
                    // This error code is not included in error_reporting
                    return;
                }
                throw new \ErrorException($message, 0, $severity, $file, $line);
            });

            // get the app settings
            $appSettings = $this->defaults['app'];
            foreach ($options as $config) {
                if (isset($config['app'])) {
                    $appSettings = array_merge_recursive($appSettings, $config['app']);
                }
            }

            $namespace = $appSettings['namespace'];
            $path = $appSettings['appDir'];

            $appSettings['loader']->addPsr4("$namespace\\", "$path/src/$namespace");

            // create the container
            $this->container = new $appSettings['container'];
            // $this->container->startTime = $time;

            // create the config service
            array_push($options, $this->defaults);
            $this->container->config = new $appSettings['config']($this, 'config', $options);

            // add services to the container for lazy-loading
            $this->container->defineArray($this->container->config->services);

            // load configurtation for use by services, and reload app settings to avoid conflict
            // $this->settings = $this->container->config->load($options)->get('app');

            // build the http stack
            $stack = (new HandlerStack($this->container))->pushMultiple($this->httpStack);
            $stack->push(
                function ($next, $request) {
                    return $this->container->http->createResponse('Hello Old World');
            });

            // invoke the stack
            $response = $stack();
            // echo sprintf("\n %.2f ms", (microtime(true) - $this->container->startTime) * 1000);

        } catch (\Throwable $error) {
            try {
                (new \Scherzo\Controllers\ErrorController($this->container, null))
                    ->handleUncaught($error)->send();
                return;
            } catch (\Throwable $e) {
                throw new \Exception ('Error (' . $e->getMessage()
                    . ') handling previous error (' . $error->getMessage() . ')', 0, $error);
            }
        }
    }

}
