<?php
/**
 * This file is part of the Scherzo application framework.
 *
 * @link      https://github.com/paulbloomfield-uk/scherzo
 * @license   [MIT](https://github.com/paulbloomfield-uk/scherzo/blob/master/LICENSE).
 * @copyright Copyright © 2017 [Paul Bloomfield](https://github.com/paulbloomfield-uk).
**/
namespace Scherzo\Services;

use Scherzo\Exception;

class ErrorHandler {
    use ServiceTrait;

    /**
     * Handle an error which has not been handled by the pipeline.
     *
     * @param  Throwable  $error  The error that has been caught.
    **/
    public function handleUnhandledError(\Throwable $error) {
        $whoops = new \Whoops\Run;
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
        // $whoops->writeToOutput(false);
        // $whoops->allowQuit(false);
        // return $this->getErrorResponse($whoops->handleException($error));
        $whoops->handleException($error);
    }
}
