<?php

namespace Makhnanov\Php81SelfFilling;

use JsonException;
use ReflectionException;
use Throwable;

class SelfFillConstruct extends SelfFill implements FillableConstruct
{
    /**
     * @throws Throwable
     * @throws Exception\ExcessException
     * @throws ReflectionException
     * @throws Exception\MissingDataException
     * @throws JsonException
     */
    public function __construct(string|array|object $data = [])
    {
        $this->selfFill($data);
    }
}
