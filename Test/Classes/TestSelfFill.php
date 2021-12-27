<?php

namespace Makhnanov\PhpSelfFilling\Test\Classes;

use Closure;
use Iterator;
use Makhnanov\PhpSelfFilling\SelfFilling;

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
