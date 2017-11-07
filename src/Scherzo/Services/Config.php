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

class Config {

    /** @var Container Dependencies container. */
    protected $_container;

    /** @var string The id of this service in the container. */
    protected $_name;

    /** @var array Settings. */
    protected $_config = [];

    /**
     * Constructor.
     *
     * @param  string  Dependencies container.
     * @param  string  The id of this service in the container.
    **/
    public function __construct($container = null, $name = null, array $config) {
        $this->_container = $container;
        $this->_name = $name;
        foreach ($config as $options) {
            $this->_config = array_merge_recursive($this->_config, $options);
        }
    }

    /**
     * Magic method to get a **group** of settings.
     *
     * @param  string $name The name of the setting group to get.
     * @return array  The requested setting group.
    **/
    public function __get(string $name) {
        return $this->get($name);
    }

    /**
     * Get a setting.
     *
     * @param  string $group    The name of the settings group.
     * @param  string $name     The name of the setting to get.
     * @param  mixed  $default  The default to return [null].
     * @param  bool   $throw    Throw an exception instead of returning $default if not set [false].
     * @return mixed  The requested setting.
    **/
    public function get(string $group, string $name = null, $default = null, bool $throw = false) {
        if (!array_key_exists($group, $this->_config)) {
            if ($throw) {
                throw new Exception([
                    'Configuration group \':group\' does not exist', [
                    ':group' => $group,
                ]]);
            } else {
                return $default;
            }
        }
        if ($name === null) {
            return $this->_config[$group];
        }
        if (!array_key_exists($name, $this->_config[$group])) {
            if ($throw) {
                throw new Exception([
                    'Configuration key \':name\' does not exist in group \':group\'', [
                    ':name' => $name,
                    ':group' => $group,
                ]]);
            } else {
                return $default;
            }
        }
        return $this->_config[$group][$name];
    }
}
