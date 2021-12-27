<?php

namespace Makhnanov\PhpSelfFilling\Test;

use Makhnanov\PhpSelfFilling\SelfFilling;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertSame;

class CamelCaseTest extends TestCase
{
    public function testPositive()
    {
        $o = new class(['id_property' => 1])
        {
            use SelfFilling;

            public int $id_property;

            public function __construct(array $data)
            {
                $this->selfFill($data);
            }
        };
        assertSame(1, $o->id_property);

        $o = new class(['not_id_property' => 2])
        {
            use SelfFilling;

            public int $notIdProperty;

            public function __construct(array $data)
            {
                $this->selfFill($data, fromDataIdToPropertyCamel: true);
            }
        };
        assertSame(2, $o->notIdProperty);
    }
}
