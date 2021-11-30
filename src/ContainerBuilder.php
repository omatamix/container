<?php declare(strict_types=1);
/**
 * Omatamix Container
 *
 * MIT License
 * 
 * Copyright (c) 2021 Nicholas English
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

/**
 * A builder to help build psr-11 containers.
 */
class ContainerBuilder implements ArrayAccess
{
    /** @var array $values */
    private $values = [];

    /** @var array $frozen */
    private $frozen = [];

    /** @var \SplObjectStorage A container for our builder values. */
    private $storage;

    /**
     * Construct a new container builder.
     *
     * @return void
     */
    public function __construct(array $values = [])
    {
        $this->storage = new SplObjectStorage();
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
     * @return bool
     */
    public function offsetExists($id)
    {
        return array_key_exists($id, $this->values);
    }

    /**
     * Retrieve a identifier's value.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return mixed Entry.
     */
    public function offsetGet($id)
    {
        return $this->retrieve($id);
    }

    /**
     * Put a new value in our values array.
     *
     * @param string $id Identifier of the entry we are putting.
     *
     * @return void
     */
    public function offsetSet($id, $value)
    {
        $this->put($id, $value);
    }

    /**
     * Unsets a parameter or an object.
     *
     * @param string $id The unique identifier for the parameter or object
     */
    public function offsetUnset($id)
    {
        if ($this->offsetExists($id)) {
            if (!isset($this->protected[$id]) && is_callable($this->values[$id]) && $this->storage->contains($this->values[$id])) {
                unset($this->storage[$this->values[$id]], $this->protected[$id]);
            }
            unset($this->values[$id], $this->frozen[$id]);
        }
    }

    /**
     * Retrieve a identifier's value.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return mixed Entry.
     */
    public function retrieve(string $id)
    {
        if ($this->offsetExists($id)) {
            $this->frozen[$id] = true;
            if (is_callable($this->values[$id])) {
                if ($this->storage->contains($this->values[$id])) {
                    return $this->values[$id]($this);
                }
            }
            return $this->values[$id];
        } else {
            throw new Exception\ContainerException('Error while retrieving the entry.');
        }
    }

    /**
     * Put a new value in our values array.
     *
     * @param string $id Identifier of the entry we are putting.
     *
     * @return void
     */
    public function put(string $id, $value): void
    {
        if (isset($this->frozen[$id])) {
            throw new Exception\ContainerException(sprintf('This `%s` identifier is frozen.', $id));
        }
        $this->values[$id] = $value;
    }

    /**
     * Attach a new service to the container.
     *
     * @param callable $service The callable service.
     *
     * @return callable
     */
    public function attach(callable $service): callable
    {
        $this->storage->attach($service);
        return $service;
    }
}
