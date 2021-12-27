<?php

namespace Makhnanov\PhpSelfFilling\Test;

use Makhnanov\PhpSelfFilling\SelfFilling;
use PHPUnit\Framework\TestCase;

class ExcludeTest extends TestCase
{
    public function testPositive()
    {
        $o = new class()
        {
            use SelfFilling;

            public string $p1 = '';
            public string $p2 = '';
            public string $p3 = '';
        };
        $o->selfFill([
            'p1' => 'p1',
            'p2' => 'p2',
            'p3' => 'p3',
        ], exclude: ['p2']);
        $this->assertSame('p1', $o->p1);
        $this->assertSame('', $o->p2);
        $this->assertSame('p3', $o->p3);
    }

    public function testRegex()
    {
        $o = new class()
        {
            use SelfFilling;

            public string $p1 = '';
            public string $_p2 = '';
            public string $_p3 = '';
            public string $b = '';
        };
        $o->selfFill([
            'p1' => 'p1',
            '_p2' => 'p2',
            '_p3' => 'p3',
            'b' => 'b',
        ], exclude: ['/^_/', '/^b$/']);
        self::assertSame('p1', $o->p1);
        self::assertSame('', $o->_p2);
        self::assertSame('', $o->_p3);
        self::assertSame('', $o->b);
    }
}
