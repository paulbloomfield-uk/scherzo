<?php
/**
 * This file is part of the Scherzo application framework.
 *
 * @link      https://github.com/paulbloomfield-uk/scherzo
 * @license   [MIT](https://github.com/paulbloomfield-uk/scherzo/blob/master/LICENSE).
 * @copyright Copyright © 2017 [Paul Bloomfield](https://github.com/paulbloomfield-uk).
**/

namespace Scherzo\Errors;

use Scherzo\Exception;

class HttpException extends Exception {

/** @var array Allowed methods for a 405 response. */
protected $methods;

/** @var int HTTP status code. */
protected $status;

    /**
     * Constructor.
     *
     * @param  mixed  $message     Error message - see parent.
     * @param  int    $statusCode  The HTTP status code to set.
    **/
    public function __construct($message = null, int $status = 404, \Throwable $previous = null) {
        parent::__construct($message, 0, $previous);
        $this->status = $status;
    }

    /**
     * Get allowed methods for a 405 response.
     *
     * @return array Array of HTTP methods e.g. ['GET', 'PUT'].
    **/
    public function getAllowedMethods() : ?array {
        return $this->allowedMethods;
    }

    /**
     * Set allowed methods for a 405 response.
     *
     * @param  array  $methods  Array of HTTP methods e.g. ['GET', 'PUT'].
     *
     * @return self   Chainable.
    **/
    public function setAllowedMethods(array $methods) : self {
        $this->allowedMethods = $methods;
        return $this;
    }

    /**
     * Get HTTP status code.
     *
     * @return  int  The HTTP status code of the message.
    **/
    public function getStatus() : int {
        return $this->status;
    }

    /**
     * Set HTTP status code.
     *
     * @param  int    $statusCode  The HTTP status code to set.
     *
     * @return self   Chainable.
    **/
    public function setStatus(int $code) : self {
        $this->status = $code;
        return $this;
    }

}
