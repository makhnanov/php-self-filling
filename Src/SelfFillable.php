<?php /** @noinspection SpellCheckingInspection */

declare(strict_types=1);

namespace Makhnanov\PhpSelfFilling;

use Makhnanov\PhpSelfFilling\Behaviour\ErrorBehaviour;
use Makhnanov\PhpSelfFilling\Behaviour\Excess;
use Makhnanov\PhpSelfFilling\Behaviour\MissingData;
use ReflectionProperty;

interface SelfFillable
{
    public function selfFill(
        string|array|object $data = [],
        array               $defaultMap = [],
        array               $filterMap = [],
        array               $exclude = [],
        ErrorBehaviour      $errorBehaviour = ErrorBehaviour::THROW_AFTER_FIRST,
        MissingData         $missingDataBehaviour = MissingData::REPLACE_WITH_DEFAULT,
        Excess              $excessBehaviour = Excess::IGNORE,
        int                 $modifier = ReflectionProperty::IS_PUBLIC,
        string              $finder = Finder::class,
        bool                $toCamel = false,
    ): void;
}
