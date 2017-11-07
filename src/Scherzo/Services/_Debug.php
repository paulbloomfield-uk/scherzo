<?php
/**
 * This file is part of the Scherzo application framework.
 *
 * @link      https://github.com/paulbloomfield-uk/scherzo
 * @license   [MIT](https://github.com/paulbloomfield-uk/scherzo/blob/master/LICENSE).
 * @copyright Copyright © 2017 [Paul Bloomfield](https://github.com/paulbloomfield-uk).
**/

namespace Scherzo\Services;

use Scherzo\Service;
use Scherzo\Exception;

class Debug extends Service {

    /** @var Default settings. */
    protected $defaultSettings = [
    ];

    /**
     * Initialise - this is called by the parent constructor.
    **/
    public function init() {
        // Create the logger
        $this->logger = new Logger('default');
        // Now add some handlers
        $this->logger->pushHandler(new StreamHandler($this->settings['logPath'], Logger::DEBUG));
    }

    /**
     * Initialise - this is called by the parent constructor.
    **/
    public function logger(string $name = 'default') {
        return $this->loggers[$name];
    }

}
