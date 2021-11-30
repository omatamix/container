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

namespace Omatamix\Container\Test;

use Omatamix\Container\Container;
use Omatamix\Container\ContainerBuilder;
use PHPUnit\Framework\TestCase;

class Foo
{
    public $num = 5;
}

class Bar
{
    public $num = 9;
}

class Dos
{
    private $foo;
    private $bar;
    
    public function __construct(Foo $foo, Bar $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }

    public function add()
    {
        return $this->foo->num + $this->bar->num;
    }
}

class BuilderTest extends TestCase
{
    /**
     * @return void Returns nothing.
     */
    public function testString(): void
    {
        $builder = new ContainerBuilder();
        $builder['name'] = 'Nicholas';
        $this->assertEquals($builder['name'], 'Nicholas');
        $this->assertTrue(isset($builder['name']));
        unset($builder['name']);
        $this->assertTrue(!isset($builder['name']));
        $builder['name'] = 'Nicholas';
        $builder['isDeveloper'] = 'Yes';
        $psrContainer = new Container($builder);
        $this->assertTrue($psrContainer->has('name'));
        $this->assertTrue($psrContainer->has('isDeveloper'));
        $this->assertEquals($psrContainer->get('name'), 'Nicholas');
        $this->assertEquals($psrContainer->get('isDeveloper'), 'Yes');
        $this->assertTrue(!$psrContainer->has('randomId'));
    }

    /**
     * @return void Returns nothing.
     */
    public function testInt(): void
    {
        $builder = new ContainerBuilder();
        $builder['num'] = 1;
        $this->assertEquals($builder['num'], 1);
        $this->assertTrue(isset($builder['num']));
        unset($builder['num']);
        $this->assertTrue(!isset($builder['num']));
        $builder['num1'] = 1;
        $builder['num2'] = 2;
        $psrContainer = new Container($builder);
        $this->assertTrue($psrContainer->has('num1'));
        $this->assertTrue($psrContainer->has('num2'));
        $this->assertEquals($psrContainer->get('num1'), 1);
        $this->assertEquals($psrContainer->get('num2'), 2);
        $this->assertTrue(!$psrContainer->has('randomId'));
    }

    /**
     * @return void Returns nothing.
     */
    public function testFloat(): void
    {
        $builder = new ContainerBuilder();
        $builder['float'] = 1.11;
        $this->assertEquals($builder['float'], 1.11);
        $this->assertTrue(isset($builder['float']));
        unset($builder['float']);
        $this->assertTrue(!isset($builder['float']));
        $builder['float1'] = 1.11;
        $builder['float2'] = 2.22;
        $psrContainer = new Container($builder);
        $this->assertTrue($psrContainer->has('float1'));
        $this->assertTrue($psrContainer->has('float2'));
        $this->assertEquals($psrContainer->get('float1'), 1.11);
        $this->assertEquals($psrContainer->get('float2'), 2.22);
        $this->assertTrue(!$psrContainer->has('randomId'));
    }

    /**
     * @return void Returns nothing.
     */
    public function testService(): void
    {
        $builder = new ContainerBuilder();
        $builder['foo'] = $builder->service(function ($c) {
            return new Foo();
        });
        $this->assertTrue(isset($builder['foo']));
        $this->assertEquals($builder['foo']->num, 5);
        $builder['bar'] = $builder->service(function ($c) {
            return new Bar();
        });
        $this->assertTrue(isset($builder['bar']));
        $this->assertEquals($builder['bar']->num, 9);
        $builder['dos'] = $builder->service(function ($c) {
            return new Dos($c['foo'], $c['bar']);
        });
        $this->assertTrue(isset($builder['dos']));
        $this->assertEquals($builder['dos']->add(), 14);
    }
}
