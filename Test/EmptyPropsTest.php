<?php

namespace Makhnanov\PhpSelfFilling\Test;

use Makhnanov\PhpSelfFilling\SelfFilling;
use PHPUnit\Framework\TestCase;

class EmptyPropsTest extends TestCase
{
    public function testPositive()
    {
        $o = new class{ use SelfFilling; };
        $o->selfFill();
        $this->assertSame([], $o->selfFillErrors);
        $this->assertSame([], $o->selfFillMissingData);
        $this->assertSame([], $o->selfFillExcess);
    }
}
