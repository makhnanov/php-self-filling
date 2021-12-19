<?php

namespace Makhnanov\Php81SelfFilling\Test;

use Makhnanov\Php81SelfFilling\Exception\TypeErrorException;
use Makhnanov\Php81SelfFilling\SelfFill;
use Makhnanov\Php81SelfFilling\Test\Classes\TestEnum;
use Makhnanov\Php81SelfFilling\Test\Classes\TestIterator;
use Makhnanov\Php81SelfFilling\Test\Classes\TestSelfFill;
use PHPUnit\Framework\TestCase;
use stdClass;
use TypeError;

class DefaultTest extends TestCase
{
    private ?TestSelfFill $fixture;

    protected function setUp(): void
    {
        $this->fixture = new TestSelfFill;
    }

    public function testMapPositive()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->fixture->selfFill([], [
            'boolProperty' => false,
            'intProperty' => 777,
            'floatPropertyFirst' => 7,
            'floatPropertySecond' => 8.88,
            'stringProperty' => 'This is string.',
            'arrayProperty' => ['first' => 1],
            'iterableProperty' => new TestIterator(),
            'objectProperty' => new stdClass(),
            'enumProperty' => TestEnum::default,
            'nullProperty' => null,
            'closureProperty' => function () {
                return 10;
            },
            'singleton' => new TestSelfFill(),
        ]);
        $this->assertSame(false, $this->fixture->boolProperty);
        $this->assertSame(777, $this->fixture->intProperty);
        $this->assertSame(7.0, $this->fixture->floatPropertyFirst);
        $this->assertSame(8.88, $this->fixture->floatPropertySecond);
        $this->assertSame('This is string.', $this->fixture->stringProperty);
        $this->assertSame(['first' => 1], $this->fixture->arrayProperty);
        $this->assertInstanceOf(TestIterator::class, $this->fixture->iterableProperty);
        $this->assertInstanceOf(stdClass::class, $this->fixture->objectProperty);
        $this->assertSame(TestEnum::default, $this->fixture->enumProperty);
        $this->assertSame(null, $this->fixture->nullProperty);
        $this->assertSame(10, ($this->fixture->closureProperty)());
        $this->assertInstanceOf($this->fixture::class, TestSelfFill::$singleton);
    }

    public function testMapNotOverride()
    {
        $o = new class(['strength' => 1]) extends SelfFill
        {
            public int $strength;

            public function __construct(array $data)
            {
                $this->selfFill($data, ['strength' => 10]);
            }
        };
        $this->assertSame(1, $o->strength);
    }

    public function testMapNegative()
    {
        $this->expectException(TypeError::class);
        new class extends SelfFill
        {
            public int $strength;

            public function __construct()
            {
                $this->selfFill([], ['strength' => 'strength']);
            }
        };
    }

    public function testValuePositive()
    {
        $this->fixture->selfFill([], exclude: [
            'boolProperty',
            'intProperty',
            'floatPropertyFirst',
            'floatPropertySecond',
            'stringProperty',
            'arrayProperty',
        ]);
        $notNullProperties = [
            'iterableProperty',
            'objectProperty',
            'enumProperty',
            'nullProperty',
            'closureProperty',
        ];
        foreach ($notNullProperties as $property) {
            $this->assertNull($this->fixture->$property);
        }
        $this->assertNull(TestSelfFill::$singleton);

        $o = new class extends SelfFill
        {
            public int $strength;

            public function __construct()
            {
                $this->selfFill([], defaultMap: ['*' => 10]);
            }
        };
        $this->assertSame(10, $o->strength);
    }

    public function testValueNotOverride()
    {
        $o = new class extends SelfFill
        {
            public int $strength;

            public function __construct()
            {
                $this->selfFill(['strength' => 6], defaultMap: ['*' => 10]);
            }
        };
        $this->assertSame(6, $o->strength);
    }

    public function testValueNegative()
    {
        $this->expectException(TypeError::class);
        $o = new class extends SelfFill
        {
            public int $strength;

            public function __construct()
            {
                $this->selfFill([]);
            }
        };
        $this->assertNull($o->strength);
    }

    public function testPositiveConcatenate()
    {
        $o = new class extends SelfFill
        {
            public bool $a1;
            public bool $a2;
            public bool $b1;
            public bool $b2;
            public bool $_1;

            public function __construct()
            {
                $this->selfFill(defaultMap: [
                    'a1|a2' => true,
                    'b1|b2' => false,
                    '*' => true
                ]);
            }
        };
        self::assertTrue($o->a1);
        self::assertTrue($o->a2);
        self::assertFalse($o->b1);
        self::assertFalse($o->b2);
        self::assertTrue($o->_1);
    }

    public function testPositiveRegex()
    {
        $o = new class extends SelfFill
        {
            public bool $a1;
            public bool $a2;
            public bool $b1;
            public bool $b2;
            public ?bool $_1;

            public function __construct()
            {
                $this->selfFill(defaultMap: [
                    '/^a/' => true,
                    '/^b/' => false,
                ]);
            }
        };
        self::assertTrue($o->a1);
        self::assertTrue($o->a2);
        self::assertFalse($o->b1);
        self::assertFalse($o->b2);
        self::assertNull($o->_1);
    }

    public function testBad1()
    {
        $o = new class extends SelfFill
        {
            public bool $a1;

            public function __construct()
            {
                $this->selfFill(defaultMap: [
                    '*' => true,
                    1 => '',
                    2
                ]);
            }
        };
        $this->assertTrue(isset($o->a1));
    }
}
