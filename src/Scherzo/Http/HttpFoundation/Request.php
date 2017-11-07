<?php
/**
 * This file is part of the Scherzo application framework.
 *
 * @link      https://github.com/paulbloomfield-uk/scherzo
 * @license   [MIT](https://github.com/paulbloomfield-uk/scherzo/blob/master/LICENSE).
 * @copyright Copyright © 2017 [Paul Bloomfield](https://github.com/paulbloomfield-uk).
**/

namespace Scherzo\Http\HttpFoundation;

use Scherzo\Http\RequestInterface;

/**
 * A Scherzo Request.
**/
class Request extends \Symfony\Component\HttpFoundation\Request implements RequestInterface {}
