<?php

namespace Makhnanov\Php81SelfFilling\Test;

use Makhnanov\Php81SelfFilling\Behaviour\Excess;
use Makhnanov\Php81SelfFilling\Behaviour\MissingData;
use Makhnanov\Php81SelfFilling\Exception\ExcessException;
use Makhnanov\Php81SelfFilling\Test\Classes\TestSelfFill;

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
