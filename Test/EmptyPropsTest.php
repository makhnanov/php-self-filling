<?php

namespace Makhnanov\PhpSelfFilling\Test;

use Makhnanov\PhpSelfFilling\SelfFill;
use PHPUnit\Framework\TestCase;

class EmptyPropsTest extends TestCase
{
    public function testPositive()
    {
        $o = new SelfFill();
        $o->selfFill();
        $this->assertSame([], $o->selfFillErrors);
        $this->assertSame([], $o->selfFillMissingData);
        $this->assertSame([], $o->selfFillExcess);
    }
}
