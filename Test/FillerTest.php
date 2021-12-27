<?php

namespace Makhnanov\PhpSelfFilling\Test;

use InvalidArgumentException;
use Makhnanov\PhpSelfFilling\SelfFilling;
use Makhnanov\PhpSelfFilling\Test\Classes\TestExtendedFinder;
use Makhnanov\PhpSelfFilling\Test\Classes\TestSelfFill;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertSame;

class FillerTest extends TestCase
{
    public function testExtendPositive()
    {
        $o = new class(['v' => '1'])
        {
            use SelfFilling;

            public string $v;

            public function __construct(array $data)
            {
                $this->selfFill($data, finder: TestExtendedFinder::class);
            }
        };
        assertSame('1', $o->v);
    }

    public function testNegative()
    {
        try {
            new class(['v' => '1'])
            {
                use SelfFilling;

                public string $v;

                public function __construct(array $data)
                {
                    $this->selfFill($data, finder: TestSelfFill::class);
                }
            };
        } catch (InvalidArgumentException $e) {
            assertSame(
                'Finder must be extended from Makhnanov\PhpSelfFilling\Finder class.',
                $e->getMessage()
            );
        }
    }
}
