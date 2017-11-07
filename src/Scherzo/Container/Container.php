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
class Container {

    /** @var array Entry definitions. */
    protected $definitions = [];

    /** @var array Container entries. */
    protected $entries = [];

    /**
     * Get magic method.
     *
     * Provide access to entries as $c->entry as well as $c->get('entry').
     *
     * @param string $id Identifier of the entry to get.
     * @return mixed The value of the property.
    **/
    public function __get(string $id) {
        return $this->get($id);
    }

    /**
     * Set magic method.
     *
     * Provide access to entries as `$c->entry = $foo` as well as `$c->set('entry', $foo)`.
     *
     * @param string $id Identifier of the entry to set.
     * @param mixed  $value The value to be set.
    **/
    public function __set(string $id, $value) {
        $this->set($id, $value);
    }

    /**
     * Define a array of entries.
     *
     * @param  array $id The property definitions as an array [$id => $value].
     * @return $this Chainable.
    **/
    public function defineArray(array $definitions) : self {
        $this->definitions = array_merge($this->definitions, $definitions);
        return $this;
    }

    /**
     * Finds an entry of the container by its identifier, lazy-loads it if required, and returns it.
     *
     * @param string $id Identifier of the entry to get.
     *
     * @throws ContainerNotFoundException  No entry was found for this identifier.
     * @throws ContainerException Error while retrieving the entry.
     *
     * @return mixed The Entry.
     */
    public function get($id) {
        try {
            if (!array_key_exists($id, $this->entries)) {
                if (array_key_exists($id, $this->definitions)) {
                    $this->entries[$id] = $this->loadDefinedEntry($id);
                } else {
                    throw new ContainerNotFoundException([
                        'Entry :id does not exist in this container', [
                            ':id' => $id,
                        ]]);
                }
            }
            return $this->entries[$id];
        } catch (ContainerNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw $e;
            throw new ContainerException([
                'Could not retrieve entry \':id\'. :msg]', [
                    ':id' => $id,
                    ':msg' => $e->getMessage(),
                ]], 0, $e);
        }
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
        return array_key_exists($id, $this->entries)
            || array_key_exists($id, $this->definitions);
    }

    /**
     * Define an entry for lazy-loading.
     *
     * @param string $id         Identifier of the entry to define.
     * @param mixed  $definition The definition to be set.
     * @return $this Chainable.
    **/
    public function define(string $id, $definition) {
        $this->definitions[$id] = $definition;
        return $this;
    }

    /**
     * Set an entry.
     *
     * @param string $id    Identifier of the entry to set.
     * @param mixed  $value The value to be set.
     * @return $this   Chainable.
    **/
    public function set(string $id, $value) : self {
        $this->entries[$id] = $value;
        return $this;
    }

    /**
     * Load a defined entry.
     *
     * @param string $id Identifier of the entry to load.
     * @return mixed The value of the entry.
    **/
    protected function loadDefinedEntry(string $id) {
        $definition = $this->definitions[$id];
        if (is_callable($definition)) {
            return call($definition, $this);
        } else {
            return new $definition($this, $id);
        }
    }

}
