<?php
/**
 * This file is part of the Scherzo application framework.
 *
 * @link      https://github.com/paulbloomfield-uk/scherzo
 * @license   [MIT](https://github.com/paulbloomfield-uk/scherzo/blob/master/LICENSE).
 * @copyright Copyright © 2017 [Paul Bloomfield](https://github.com/paulbloomfield-uk).
**/

namespace Scherzo\Router;

/**
 * Thrown by a Container if an entry is not found.
**/
class RouterMethodNotAllowedException extends RouterException {
    /** @var array */
    protected $allowedMethods = [];

    public function getAllowedMethods() : array {
        return $this->allowedMethods;
    }

    public function setAllowedMethods(array $methods) : self {
        $this->allowedMethods = $methods;
        return $this;
    }
}
