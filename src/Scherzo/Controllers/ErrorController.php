<?php
/**
 * This file is part of the Scherzo application framework.
 *
 * @link      https://github.com/paulbloomfield-uk/scherzo
 * @license   [MIT](https://github.com/paulbloomfield-uk/scherzo/blob/master/LICENSE).
 * @copyright Copyright © 2017 [Paul Bloomfield](https://github.com/paulbloomfield-uk).
**/

namespace Scherzo\Controllers;

use Scherzo\Controllers\ControllerTrait;

use Scherzo\Http\ResponseInterface as Response;

/**
 * Base class for a controller to interact with the Slim framework.
**/
class ErrorController {

    use ControllerTrait;

    public function handleUncaught(\Throwable $error) : Response {
        $whoops = new \Whoops\Run;
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
        $whoops->writeToOutput(false);
        $whoops->allowQuit(false);
        return $this->createErrorResponse($whoops->handleException($error));
    }

}
