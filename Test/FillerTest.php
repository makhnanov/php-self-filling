<?php

namespace Makhnanov\PhpSelfFilling\Test;

use InvalidArgumentException;
use Makhnanov\PhpSelfFilling\SelfFill;
use Makhnanov\PhpSelfFilling\SelfFilling;
use Makhnanov\PhpSelfFilling\Test\Classes\TestExtendedFiller;
use Makhnanov\PhpSelfFilling\Test\Classes\TestSelfFill;
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
                'Filler must be extended from Makhnanov\PhpSelfFilling\Filler class.',
                $e->getMessage()
            );
        }
    }
}
