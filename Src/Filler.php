<?php

declare(strict_types=1);

namespace Makhnanov\Php81SelfFilling;

use Closure;
use Makhnanov\Php81SelfFilling\Exception\MissingDataException;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use stdClass;

class Filler
{
    protected ReflectionClass $receiverReflection;

    protected ?ReflectionClass $dataReflection;

    /** @var ReflectionProperty[] */
    public readonly array $receiverProperties;

    protected array $exclude;

    final public function __construct(
        protected object       $receiver,
        protected array|object $data,
        protected array        $defaultMap = [],
        protected array        $filterMap = [],
        string|array           $exclude = [],
        ?int                   $modifier = ReflectionProperty::IS_PUBLIC,
    ) {
        $this->exclude = is_array($exclude)
            ? $exclude
            : explode('|', $exclude);
        $this->receiverReflection = new ReflectionClass($this->receiver);
        /** @psalm-suppress PossiblyNullArgument */
        $this->receiverProperties = array_filter(array_map(function (ReflectionProperty $property) {
            return $this->inExclude($property->name) ? null : $property;
        }, $this->receiverReflection->getProperties($modifier)));
        $this->dataReflection = is_array($this->data)
            ? null
            : new ReflectionClass($this->data);
    }

    private function inExclude(string $propertyName): bool
    {
        foreach ($this->exclude as $onePattern) {
            if (@preg_match($onePattern, $propertyName)) {
                return true;
            }
        }
        return in_array($propertyName, $this->exclude, true);
    }

    /**
     * @throws ReflectionException
     */
    public function detectExcess(): array
    {
        $usefulProperties = [];
        foreach ($this->receiverProperties as $property) {
            $usefulProperties[] = $property->name;
        }

        if (is_array($this->data)) {
            $dataKeys = array_keys($this->data);
        } else {
            $dataKeys = [];
            foreach ($this->getDataReflection()->getProperties(ReflectionProperty::IS_PUBLIC) as $dataProperty) {
                $dataKeys[] = $dataProperty->name;
            }
        }

        $excess = [];
        $keysDiff = array_diff($dataKeys, $usefulProperties);
        foreach ($keysDiff as $keyDiff) {
            $excess[$keyDiff] = $this->getDataValue($keyDiff);
        }

        return $excess;
    }

    /**
     * @throws ReflectionException
     */
    private function getDataReflection(): ReflectionClass
    {
        if (!isset($this->dataReflection)) {
            throw new ReflectionException();
        }
        return $this->dataReflection;
    }

    /**
     * @throws MissingDataException
     * @throws ReflectionException
     */
    public function getValue(ReflectionProperty $property, bool $replaceWithDefault): mixed
    {
        $propertyName = $property->getName();
        if ($this->existInData($propertyName)) {

            $dataValue = $this->getDataValue($propertyName);

            $filter = $this->getFilter($propertyName);
            if ($filter) {
                $dataValue = $filter($property, $dataValue);
            }

            if (is_null($property->getType())) {
                return $dataValue;
            }

            return $this->transform($property, $dataValue);
        }

        if (!$replaceWithDefault) {
            throw new MissingDataException("There are no $property->name in data.");
        }

        return $this->getDefault($property);
    }

    /**
     * @throws ReflectionException
     */
    private function existInData(string $propertyName): bool
    {
        if (is_array($this->data)) {
            return array_key_exists($propertyName, $this->data);
        }

        if ($this->data instanceof stdClass) {
            return property_exists($this->data, $propertyName);
        }

        $needProperty = $this->getDataReflection()->getProperty($propertyName);
        return $needProperty->isPublic() && $needProperty->isInitialized($this->data);
    }

    private function getDataValue(int|string $key): mixed
    {
        return is_array($this->data) ? $this->data[$key] : $this->data->$key;
    }

    private function getFilter(string $propertyName): ?Closure
    {
        foreach ($this->filterMap as $filterKey => $filter) {
            if (
                @preg_match($filterKey, $propertyName)
                || in_array($propertyName, explode('|', $filterKey), true)
            ) {
                return $filter;
            }
        }
        return $this->filterMap['*'] ?? null;
    }

    public function getDefault(ReflectionProperty $property): mixed
    {
        foreach (array_keys($this->defaultMap) as $inputDefaultKey) {
            if (!is_string($inputDefaultKey) || $inputDefaultKey === '*') {
                continue;
            }
            if (
                @preg_match($inputDefaultKey, $property->name)
                || in_array($property->name, explode('|', $inputDefaultKey), true)
            ) {
                $personal = $this->defaultMap[$inputDefaultKey];
            }
        }
        return $personal ?? $this->defaultMap['*'];
    }

    /**
     * @throws ReflectionException
     */
    private function transform(ReflectionProperty $property, mixed $dataValue): mixed
    {
        $type = $property->getType();
        /** @psalm-suppress UndefinedMethod */
        if ($type && !$type->isBuiltin()) {
            /** @psalm-suppress UndefinedMethod */
            $name = $type->getName();
            /** @psalm-suppress UndefinedClass */
            if (
                is_a($name, FillableConstruct::class, true)
                && (new ReflectionClass($name))->isInstantiable()
            ) {
                return new $name($dataValue);
            }
        }
        return $dataValue;
    }
}
