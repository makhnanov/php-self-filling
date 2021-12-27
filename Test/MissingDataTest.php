<?php

namespace Makhnanov\PhpSelfFilling\Test;

use Makhnanov\PhpSelfFilling\Behaviour\MissingData;
use Makhnanov\PhpSelfFilling\Exception\MissingDataException;
use Makhnanov\PhpSelfFilling\SelfFilling;
use PHPUnit\Framework\TestCase;

class MissingDataTest extends TestCase
{
    public function testPositive()
    {
        try {
            $o = new class
            {
                use SelfFilling;

                public $a;
            };
            $o->selfFill(missingDataBehaviour: MissingData::THROW_AFTER_FIRST);
        } catch (MissingDataException $e) {
            $this->assertSame('There are no a in data.', $e->getMessage());
        }
    }
}
