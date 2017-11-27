<?php
/**
 * This file is part of the Scherzo application framework.
 *
 * @link      https://github.com/paulbloomfield-uk/scherzo
 * @license   [MIT](https://github.com/paulbloomfield-uk/scherzo/blob/master/LICENSE).
 * @copyright Copyright © 2017 [Paul Bloomfield](https://github.com/paulbloomfield-uk).
**/

namespace Scherzo\Container;

use Scherzo\Container\ContainerException;
use Scherzo\Container\ContainerNotFoundException;

/**
 * PSR-11 compliant container.
**/
class Container implements \Psr\Container\ContainerInterface {

    /** @var array Entry definitions. */
    protected $_definitions = [];

    /**
     * Magic method to lazy-load an entry.
     *
     * @param string $id Identifier of the entry to get.
     *
     * @throws ContainerNotFoundException  No entry was found for this identifier.
     *
     * @return mixed The value of the property.
    **/
    public function __get(string $id) {
        if (array_key_exists($id, $this->_definitions)) {
            return $this->load($id);
        }
        throw new ContainerNotFoundException([
            'This container does not have a \':id\' entry', [
                ':id' => $id,
            ]]);
    }

    /**
     * Define an entry or entries for lazy-loading.
     *
     * @param  string  $idOrArray   Identifier of the entry to define.
     * @param  array   $idOrArray   Associative array of definitions keyed by identifier.
     * @param  mixed   $definition  The definition to be set.
     * @return $this   Chainable.
    **/
    public function define($id, $definition = null) : self {
        if (is_array($id)) {
            $this->_definitions = array_merge($this->_definitions, $id);
            return $this;
        }
        $this->_definitions[$id] = $definition;
        return $this;
    }

    /**
     * PSR-11 compatible method to retrieve an entry, lazy-loading it if required.
     *
     * @param string $id Identifier of the entry to get.
     *
     * @throws ContainerNotFoundException  No entry was found for this identifier.
     * @throws ContainerException Error while retrieving the entry.
     *
     * @return mixed The entry.
    **/
    public function get($id) {
        // Return the entry if it exists.
        if (property_exists($this, $id)) {
            return $this->$id;
        }
        // Otherwise try to lazy-load it.
        return $this->__get($id);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to get.
     * @return bool
    **/
    public function has($id) {
        return $id !== '_definitions' && property_exists($this, $id)
            || array_key_exists($id, $this->_definitions);
    }

    /**
     * Load a defined entry.
     *
     * @param  string  $id    Identifier of the definition to load.
     * @param  string  $asId  Optional identifier to load the definition as.
     * @param  bool    $asId  Set to false to return the entry without loading it.
     * @return mixed   The value of the entry.
    **/
    public function load(string $id, $asId = null) {
        if ($asId === null) {
            $asId = $id;
        } elseif ($asId === false) {
            $asId = null;
        }

        // Find the defintion.
        if (!array_key_exists($id, $this->_definitions)) {
            throw new ContainerNotFoundException([
                'Entry ":id" has not been defined in this container', [
                    ':id' => $id,
                ]]);
        }
        $definition = $this->_definitions[$id];

        // Check for an illegal entry id.
        if ($asId === '_definitions') {
            throw new ContainerException([
                'Cannot create a container entry with the id ":id"', [
                    ':id' => $asId,
                ]]);
        }

        $entry = $this->loadDefinition($definition);

        if ($asId === null) {
            return $entry;
        }

        try {
            $this->$asId = $entry;
        } catch (\Throwable $error) {
            throw new ContainerException([
                'Cannot create a container entry with the id ":id"', [
                    ':id' => $asId,
                ]]);
        }
        return $entry;
    }

    /**
     * Load a defined entry.
     *
     * @param  mixed   $definition    The definition to load.
     * @param  string  $asId          Optional identifier to load the definition as.
     * @return mixed   The value of the entry.
    **/
    protected function loadDefinition($definition, string $asId = null) {

        // Load a service from a Closure.
        if ($definition instanceof \Closure) {
            return $definition->call($this, $asId);
        }

        // Load a service from a factory method (now we know it is not a Closure).
        if (is_callable($definition)) {
            return $definition($this, $asId);

        }

        // Load a singleton service.
        if (class_exists($definition)) {
            return new $definition($this, $asId);
        }

        // Load anythuing else.
        return $definition;
    }
}
