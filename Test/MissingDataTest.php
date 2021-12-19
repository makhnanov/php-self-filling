<?php

namespace Makhnanov\Php81SelfFilling\Test;

use Makhnanov\Php81SelfFilling\Behaviour\MissingData;
use Makhnanov\Php81SelfFilling\Exception\MissingDataException;
use Makhnanov\Php81SelfFilling\SelfFill;
use PHPUnit\Framework\TestCase;

class MissingDataTest extends TestCase
{
    public function testPositive()
    {
        try {
            $o = new class extends SelfFill
            {
                public $a;
            };
            $o->selfFill(missingDataBehaviour: MissingData::THROW_AFTER_FIRST);
        } catch (MissingDataException $e) {
            $this->assertSame('There are no a in data.', $e->getMessage());
        }
    }
}
