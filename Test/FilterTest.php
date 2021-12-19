<?php

namespace Makhnanov\Php81SelfFilling\Test;

use Makhnanov\Php81SelfFilling\SelfFill;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class FilterTest extends TestCase
{
    public function testEmpty()
    {
        $o = new class extends SelfFill
        {
            public string $p1;
        };
        $o->selfFill(['p1' => 'p1'], filterMap: []);
        $this->assertSame('p1', $o->p1);

        $o = new class extends SelfFill
        {
            public string $p1;
        };
        $o->selfFill(['p1' => 'p1'], filterMap: ['*' => function (ReflectionProperty $property, mixed $value) {
            return str_repeat($value, 2);
        }]);
        $this->assertSame('p1p1', $o->p1);
    }
}
