<?php

declare(strict_types=1);

namespace Makhnanov\Php81SelfFilling;

use Makhnanov\Php81SelfFilling\Behaviour\ErrorBehaviour;
use Makhnanov\Php81SelfFilling\Behaviour\Excess;
use Makhnanov\Php81SelfFilling\Behaviour\MissingData;
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
        string              $filler = Filler::class,
    ): void;
}
