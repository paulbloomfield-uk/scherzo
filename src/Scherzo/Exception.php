<?php
/**
 * This file is part of the Scherzo application framework.
 *
 * @link      https://github.com/paulbloomfield-uk/scherzo
 * @license   [MIT](https://github.com/paulbloomfield-uk/scherzo/blob/master/LICENSE).
 * @copyright Copyright © 2017 [Paul Bloomfield](https://github.com/paulbloomfield-uk).
**/

namespace Scherzo;

/**
 * Extend base class to provide easier creation and translation of error messages.
**/
class Exception extends \Exception {

    /**
     * Constructor.
     *
     * @param  array|string  $message   Error message or array [$message, $vars].
     * @param  int           $code      A PHP error code.
     * @param  Throwable     $previous  A previous exception if any.
    **/
    public function __construct($message = null, int $code = 0, \Throwable $previous = null) {
        if (is_array($message)) {
            try {
                $message = strtr($message[0], $message[1]);
            } catch (\Throwable $e) {
                $message = 'Could not construct message: ' . $e->getMessage();
            }
        }
        parent::__construct($message, $code, $previous);
    }
}
