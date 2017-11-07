<?php
/**
 * This file is part of the Scherzo application framework.
 *
 * @link      https://github.com/paulbloomfield-uk/scherzo
 * @license   [MIT](https://github.com/paulbloomfield-uk/scherzo/blob/master/LICENSE).
 * @copyright Copyright © 2017 [Paul Bloomfield](https://github.com/paulbloomfield-uk).
**/

namespace Scherzo\Container;

use Scherzo\Exception;

/**
 * Thrown by a Container if an entry is not found.
**/
class ContainerException extends Exception
    implements \Psr\Container\ContainerExceptionInterface {}
