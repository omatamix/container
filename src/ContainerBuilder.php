<?php
declare(strict_types=1);
/**
 * Omatamix Container
 *
 * MIT License
 * 
 * Copyright (c) 2022 Nicholas English
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Omatamix\Container;

use ArrayAccess;
use SplObjectStorage;

class ContainerBuilder implements BuilderInterface, ArrayAccess
{
    /** @var array $raw A list of raw values. */
    private $raw = [];

    /** @var array $values A list of container values. */
    private $values = [];

    /** @var array $frozen A list of frozen container values. */
    private $frozen = [];

    /** @var \SplObjectStorage $services A container for our builder values. */
    private $services;

    /**
     * Construct a new container builder.
     *
     * @param array $values A list of values to set.
     *
     * @return void Returns nothing.
     */
    public function __construct(array $values = [])
    {
        $this->services = new SplObjectStorage();
        foreach ($values as $key => $value) {
            if (!is_string($key)) {
                throw new Exception\ContainerException('Error while setting values.');
            }
            $this->offsetSet($key, $value);
        }
    }

    /**
     * Checks if a parameter or an object is set.
     *
     * @param string $id The unique identifier for the parameter or object
     *
     * @return bool Returns true if the id exists and false if not.
     */
    public function offsetExists($id)
    {
        return array_key_exists($id, $this->values);
    }

    /**
     * Put a new value in our values array.
     *
     * @param string $id Identifier of the entry we are putting.
     *
     * @return void Returns nothing.
     */
    public function offsetSet($id, $value)
    {
        if (!is_string($id)) {
            throw new Exception\ContainerException('Error while setting value.');
        } elseif (isset($this->frozen[$id])) {
            throw new Exception\ContainerException(sprintf('This `%s` identifier is frozen.', $id));
        } elseif (is_array($value) && !(!($value[0] instanceof \Closure) || !method_exists($value[0], '__invoke'))) {
            $this->protected[$id] = $value[1];
            $this->values[$id] = $value[0];
        } else {
            $this->values[$id] = $value;
        }
    }
    
    /**
     * Retrieve a identifier's value.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return mixed Returns a container entry.
     */
    public function offsetGet($id)
    {
        if (!isset($this->values[$id])) {
            throw new Exception\NotFoundException(sprintf('No entry was found for `%s` identifier.', $id));
        } elseif (isset($this->raw[$id])
            || !($this->values[$id] instanceof \Closure)
            || !method_exists($this->values[$id], '__invoke')
            || $this->protected[$id]) {
            return $this->values[$id];
        } elseif (isset($this->services[$this->values[$id]])) {
            return $this->values[$id]($this);
        } else {
            $raw = $this->values[$id];
            $value = $this->values[$id] = $raw($this);
            $this->raw[$id] = $raw;
            $this->frozen[$id] = true;
            return $value;
        }
    }

    /**
     * Unsets a parameter or an object.
     *
     * @param string $id The unique identifier for the parameter or object
     *
     * @return void Returns nothing.
     */
    public function offsetUnset($id)
    {
        if ($this->offsetExists($id)) {
            if (is_object($this->values[$id]) && isset($this->services[$this->values[$id]])) {
                unset($this->services[$this->values[$id]], $this->protected[$id]);
            }
            unset($this->values[$id], $this->frozen[$id], $this->raw[$id]);
        }
    }

    /**
     * Add new service definition.
     *
     * @param callable|\Closure $callable A closure callable or invokable object.
     *
     * @return array Returns the callable in an array.
     */
    public function service($callable, bool $protect = false)
    {
        if (!($callable instanceof \Closure) || !method_exists($callable, '__invoke')) {
            throw new Exception\ContainerException('Callable is not a Closure or invokable object.');
        }
        $this->services->attach($callable);
        return [$callable, $protect];
    }
}
