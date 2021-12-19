<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Makhnanov\Php81SelfFilling\Test;

use Closure;
use Iterator;
use JsonException;
use Makhnanov\Php81SelfFilling\Test\Classes\TestEnum;
use Makhnanov\Php81SelfFilling\Test\Classes\TestIterator;
use Makhnanov\Php81SelfFilling\Test\Classes\TestSelfFill;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class DataTest extends TestCase
{
    private TestSelfFill $fixture;

    protected function setUp(): void
    {
        $this->fixture = new TestSelfFill;
    }

    public function dataProvider(): array
    {
        $dataAsArray = [
            'boolProperty' => true,
            'intProperty' => 1,
            'floatPropertyFirst' => 1,
            'floatPropertySecond' => 1.6,
            'stringProperty' => 'Hello PHP',
            'arrayProperty' => [],
            'nullProperty' => null,
        ];

        $stdClass = new stdClass();
        $stdClass->boolProperty = true;
        $stdClass->intProperty = 1;
        $stdClass->floatPropertyFirst = 1;
        $stdClass->floatPropertySecond = 1.6;
        $stdClass->stringProperty = 'Hello PHP';
        $stdClass->arrayProperty = [];
        $stdClass->iterableProperty = new TestIterator();
        $stdClass->objectProperty = new class()
        {
        };
        $stdClass->enumProperty = TestEnum::default;
        $stdClass->nullProperty = null;
        $stdClass->closureProperty = function ($value) {
        };
        $stdClass->singleton = new TestSelfFill;

        $anonymousClass = new class
        {
            public function __construct(
                public $objectProperty = null,
                public $closureProperty = null,
                public $boolProperty = true,
                public $intProperty = 1,
                public $floatPropertyFirst = 1,
                public $floatPropertySecond = 1.6,
                public $stringProperty = 'Hello PHP',
                public $arrayProperty = [],
                public $iterableProperty = new TestIterator(),
                public $enumProperty = TestEnum::default,
                public $nullProperty = null,
                public $singleton = null,
            ) {
                $this->objectProperty = new class()
                {
                };
                $this->closureProperty = function ($value) {
                };
            }
        };

        return [
            [$dataAsArray],
            [json_encode($dataAsArray)],
            [$stdClass],
            [$anonymousClass],
        ];
    }

    /**
     * @dataProvider dataProvider
     * @noinspection PhpUndefinedVariableInspection
     */
    public function testPositive($data): void
    {
        $this->fixture->selfFill($data);
        $this->assert(!(is_array($data) || is_string($data)));

        is_object($data) && !(new ReflectionClass($data))->isAnonymous()
            ? $this->assertInstanceOf(TestSelfFill::class, $this->fixture::$singleton)
            : $this->assertNull($this->fixture::$singleton);
    }

    public function assert(bool $full = true)
    {
        $fixture = $this->fixture;
        $this->assertSame(true, $fixture->boolProperty);
        $this->assertSame(1, $fixture->intProperty);
        $this->assertSame(1.0, $fixture->floatPropertyFirst);
        $this->assertSame(1.6, $fixture->floatPropertySecond);
        $this->assertSame('Hello PHP', $fixture->stringProperty);
        $this->assertSame([], $fixture->arrayProperty);
        $this->assertNull($fixture->nullProperty);

        $full
            ? $this->assertInstanceOf(Iterator::class, $fixture->iterableProperty)
            : $this->assertNull($fixture->iterableProperty);

        $full
            ? $this->assertTrue((new ReflectionClass($fixture->objectProperty))->isAnonymous())
            : $this->assertNull($fixture->objectProperty);

        $this->assertSame($full ? TestEnum::default : null, $fixture->enumProperty);

        $full
            ? $this->assertInstanceOf(Closure::class, $fixture->closureProperty)
            : $this->assertNull($fixture->closureProperty);
    }

    public function testNegativeString(): void
    {
        $this->expectException(JsonException::class);
        $this->fixture->selfFill('');
    }
}
