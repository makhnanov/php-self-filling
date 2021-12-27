<?php
/** @noinspection PhpArgumentWithoutNamedIdentifierInspection */

namespace Makhnanov\PhpSelfFilling\Test;

use Makhnanov\PhpSelfFilling\SelfFilling;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class ModifierTest extends TestCase
{
    public function dataProvider(): array
    {
        return [
            [
                [
                    'p1' => 'p1',
                    'p2' => 'p2',
                    'p3' => 'p3',
                    'p4' => 'p4',
                    'p5' => 'p5',
                    'p6' => 'p6',
                ]
            ]
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testPublic($data)
    {
        $o = new class($data)
        {
            use SelfFilling;

            public string $p1 = '';
            protected string $p2 = '';
            private string $p3 = '';
            public static string $p4 = '';
            public readonly string $p5;

            public function __construct($data)
            {
                $this->selfFill($data);
            }

            public function getP2(): string
            {
                return $this->p2;
            }

            public function getP3(): string
            {
                return $this->p3;
            }
        };
        $this->assertSame('p1', $o->p1);
        $this->assertSame('', $o->getP2());
        $this->assertSame('', $o->getP3());
        $this->assertSame('p4', $o::$p4);
        $this->assertSame('p5', $o->p5);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testProtected($data)
    {
        $o = new class($data)
        {
            use SelfFilling;

            public string $p1 = '';
            protected string $p2 = '';
            private string $p3 = '';
            protected static string $p4 = '';
            protected readonly string $p5;

            public function __construct($data)
            {
                $this->selfFill($data, modifier: ReflectionProperty::IS_PROTECTED);
            }

            public function getProtectedStatic(): string
            {
                return self::$p4;
            }

            public function getP2(): string
            {
                return $this->p2;
            }

            public function getP3(): string
            {
                return $this->p3;
            }

            public function getP5(): string
            {
                return $this->p5;
            }
        };
        $this->assertSame('', $o->p1);
        $this->assertSame('p2', $o->getP2());
        $this->assertSame('', $o->getP3());
        $this->assertSame('p4', $o->getProtectedStatic());
        $this->assertSame('p5', $o->getP5());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testPrivate($data)
    {
        $o = new class($data)
        {
            use SelfFilling;

            public string $p1 = '';
            protected string $p2 = '';
            private string $p3 = '';
            private static string $p4 = '';
            private readonly string $p5;

            public function __construct($data)
            {
                $this->selfFill($data, modifier: ReflectionProperty::IS_PRIVATE);
            }

            public function getProtectedStatic(): string
            {
                return self::$p4;
            }

            public function getP5(): string
            {
                return $this->p5;
            }

            public function getP2(): string
            {
                return $this->p2;
            }

            public function getP3(): string
            {
                return $this->p3;
            }
        };
        $this->assertSame('', $o->p1);
        $this->assertSame('', $o->getP2());
        $this->assertSame('p3', $o->getP3());
        $this->assertSame('p4', $o->getProtectedStatic());
        $this->assertSame('p5', $o->getP5());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testStatic($data)
    {
        $o = new class($data)
        {
            use SelfFilling;

            public static string $p1 = '';
            protected static string $p2 = '';
            private static string $p3 = '';
            private readonly string $p4;

            public function __construct($data)
            {
                $this->selfFill($data, modifier: ReflectionProperty::IS_STATIC);
            }

            public function getProtectedStatic(): array
            {
                return [
                    self::$p1,
                    self::$p2,
                    self::$p3,
                ];
            }

            public function getP4(): bool
            {
                return isset($this->p4);
            }
        };
        $static = $o->getProtectedStatic();
        $this->assertSame('p1', $static[0]);
        $this->assertSame('p2', $static[1]);
        $this->assertSame('p3', $static[2]);
        $this->assertFalse($o->getP4());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testReadonly($data)
    {
        $o = new class($data)
        {
            use SelfFilling;

            public readonly string $p1;
            protected readonly string $p2;
            private readonly string $p3;
            private static ?string $p4 = null;

            public function __construct($data)
            {
                $this->selfFill($data, modifier: ReflectionProperty::IS_READONLY);
            }

            public function getStatic(): ?string
            {
                return self::$p4;
            }

            /**
             * @return string
             */
            public function getP2(): string
            {
                return $this->p2;
            }

            /**
             * @return string
             */
            public function getP3(): string
            {
                return $this->p3;
            }
        };
        $this->assertSame('p1', $o->p1);
        $this->assertSame('p2', $o->getP2());
        $this->assertSame('p3', $o->getP3());
        $this->assertNull($o->getStatic());
    }
}
