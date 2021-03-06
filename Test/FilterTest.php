<?php

namespace Makhnanov\PhpSelfFilling\Test;

use Makhnanov\PhpSelfFilling\SelfFilling;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class FilterTest extends TestCase
{
    public function testEmpty()
    {
        $o = new class
        {
            use SelfFilling;

            public string $p1;
        };
        $o->selfFill(['p1' => 'p1'], filterMap: []);
        $this->assertSame('p1', $o->p1);

        $o = new class
        {
            use SelfFilling;

            public string $p1;
        };
        $o->selfFill(['p1' => 'p1'], filterMap: ['*' => function (ReflectionProperty $property, mixed $value) {
            return str_repeat($value, 2);
        }]);
        $this->assertSame('p1p1', $o->p1);
    }
}
