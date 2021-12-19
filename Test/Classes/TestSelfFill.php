<?php

namespace Makhnanov\Php81SelfFilling\Test\Classes;

use Closure;
use Iterator;
use Makhnanov\Php81SelfFilling\SelfFilling;

class TestSelfFill
{
    use SelfFilling;

    public bool $boolProperty;
    public int $intProperty;
    public float $floatPropertyFirst;
    public float $floatPropertySecond;
    public string $stringProperty;
    public array $arrayProperty;
    public ?Iterator $iterableProperty;
    public ?object $objectProperty;
    public ?TestEnum $enumProperty;
    public ?string $nullProperty;
    public ?Closure $closureProperty;
    public static ?self $singleton;
}
