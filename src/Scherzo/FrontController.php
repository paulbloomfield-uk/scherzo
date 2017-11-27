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

    /** @var array Default settings. */
    protected $defaults = [
        'app' => [
            'container' => Container::class,
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

    /** @var array Current settings. */
    protected $settings;

    /** @var array All config options. */
    protected $config;

    /** @var Container DI container. */
    protected $container;

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
     *
    **/
    public function __construct() {
        // don't want any uncaught errors
        set_error_handler(function ($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) {
                // This error code is not included in error_reporting
                return;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        // get the app settings
        $options = func_get_args();
        array_unshift($options, $this->defaults);
        $this->config = call_user_func_array('array_merge_recursive', $options);
        $this->settings = $this->config['app'];
    }

    /**
     * Run the application.
     *
     * @param  array  $options  Array of configuration settings arrays.
    **/
    public function run() : void {
        try {
            if (isset($this->settings['namespace'])) {
                // print_r($this->settings); die;
                $namespace = $this->settings['namespace'];
                $path = $this->settings['appDir'] . strtr("/src/$namespace", '\\', '/');
                $this->settings['loader']->addPsr4("$namespace\\", $path);
            }

            // create the container
            $this->container = new $this->settings['container'];
            // $this->container->startTime = $time;

            // create the config service
            $this->container->config = $this->config;

            // add services to the container for lazy-loading
            $this->container->define($this->container->config['services']);

            // load configurtation for use by services, and reload app settings to avoid conflict
            // $this->settings = $this->container->config->load($options)->get('app');

            // build the http stack
            $stack = (new HandlerStack($this->container))->pushMultiple($this->httpStack);

            // invoke the stack
            $request = $response = null;
            $stack($request, $response);
            // echo sprintf("\n %.2f ms", (microtime(true) - $this->container->startTime) * 1000);

        } catch (\Throwable $error) {
            try {
                (new \Scherzo\Controllers\ErrorController($this->container, null))
                    ->handleUncaught($error)->send();
                return;
            } catch (\Throwable $e) {
                throw new \Exception('Error ' . $e->getMessage() . ' handling previous error', 0, $error);
            }
        }
    }

}
