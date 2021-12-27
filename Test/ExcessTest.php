<?php

namespace Makhnanov\PhpSelfFilling\Test;

use Makhnanov\PhpSelfFilling\Behaviour\Excess;
use Makhnanov\PhpSelfFilling\Behaviour\MissingData;
use Makhnanov\PhpSelfFilling\Exception\ExcessException;
use Makhnanov\PhpSelfFilling\Test\Classes\TestSelfFill;

class ExcessTest extends ExcludeTest
{
    public function testPositive()
    {
        $o = new TestSelfFill();
        $o->selfFill(
            ['boolProperty' => true, 'unrealProperty' => 'unrealProperty', ''],
            missingDataBehaviour: MissingData::IGNORE
        );
        $this->assertTrue($o->boolProperty);
        $this->assertSame([
            'unrealProperty' => 'unrealProperty',
            0 => '',
        ], $o->selfFillExcess);
    }

    public function testException()
    {
        $o = new TestSelfFill();
        try {
            $o->selfFill(
                ['boolProperty' => true, 'unrealProperty' => 'unrealProperty', ''],
                excessBehaviour: Excess::THROW
            );
        } catch (ExcessException $e) {
            $this->assertSame('Data unrealProperty, 0 are excess.', $e->getMessage());
            $this->assertSame([
                'unrealProperty' => 'unrealProperty',
                0 => '',
            ], $e->getExcess());
        }
        try {
            $o->selfFill(
                ['boolProperty' => true, 'unrealProperty' => 'unrealProperty'],
                excessBehaviour: Excess::THROW
            );
        } catch (ExcessException $e) {
            $this->assertSame('Data unrealProperty is excess.', $e->getMessage());
            $this->assertSame([
                'unrealProperty' => 'unrealProperty',
            ], $e->getExcess());
        }
    }
}
