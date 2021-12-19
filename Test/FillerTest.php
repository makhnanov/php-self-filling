<?php

namespace Makhnanov\Php81SelfFilling\Test;

use InvalidArgumentException;
use Makhnanov\Php81SelfFilling\SelfFill;
use Makhnanov\Php81SelfFilling\SelfFilling;
use Makhnanov\Php81SelfFilling\Test\Classes\TestExtendedFiller;
use Makhnanov\Php81SelfFilling\Test\Classes\TestSelfFill;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertSame;

class FillerTest extends TestCase
{
    public function testExtendPositive()
    {
        $o = new class(['v' => '1']) extends SelfFill
        {
            use SelfFilling;

            public string $v;

            public function __construct(array $data)
            {
                $this->selfFill($data, filler: TestExtendedFiller::class);
            }
        };
        assertSame('1', $o->v);
    }

    public function testNegative()
    {
        try {
            new class(['v' => '1']) extends SelfFill
            {
                use SelfFilling;

                public string $v;

                public function __construct(array $data)
                {
                    $this->selfFill($data, filler: TestSelfFill::class);
                }
            };
        } catch (InvalidArgumentException $e) {
            assertSame(
                'Filler must be extended from Makhnanov\Php81SelfFilling\Filler class.',
                $e->getMessage()
            );
        }
    }
}
