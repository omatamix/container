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

use Psr\Container\ContainerInterface;

final class Container implements ContainerInterface
{
    /** @var \Omatamix\ContainerBuilder $builder The container builder to wrap. */
    private $container;

    /**
     * Construct a new compatible psr-11 container.
     *
     * @param \Omatamix\ContainerBuilder $builder The container builder to wrap.
     *
     * @return void Returns nothing.
     */
    public function __construct(ContainerBuilder $builder)
    {
        $this->container = $builder;
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @return mixed Returns a container entry.
     */
    public function get(string $id): mixed
    {
        if (!$this->has($id)) {
            throw new Exception\NotFoundException(sprintf('No entry was found for `%s` identifier.', $id));
        } elseif ($value = $this->retrieve($id)) {
            return $value;
        } else {
            throw new Exception\ContainerException('Error while retrieving the entry.');
        }
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool Returns true if the ID exists and false if not.
     */
    public function has(string $id): bool
    {
        return isset($this->container[$id]);
    }
}
