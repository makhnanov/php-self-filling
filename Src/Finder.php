<?php

declare(strict_types=1);

namespace Makhnanov\PhpSelfFilling;

use Closure;
use Makhnanov\PhpSelfFilling\Exception\MissingDataException;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use stdClass;

class Finder
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
        protected bool         $fromDataIdToPropertyCamel = false,
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
            if ($this->fromDataIdToPropertyCamel) {
                $dataKeys = [];
                foreach (array_keys($this->data) as $dataKey) {
                    $dataKeys[$dataKey] = $this->convertKeyNameToCamel($dataKey);
                }
            } else {
                $dataKeys = array_keys($this->data);
            }
        } else {
            $dataKeys = [];
            foreach ($this->getDataReflection()->getProperties(ReflectionProperty::IS_PUBLIC) as $dataProperty) {
                $dataKeys[] = $dataProperty->name;
            }
        }

        $excess = [];
        $allDifferences = array_diff($dataKeys, $usefulProperties);
        foreach ($allDifferences as $difference) {
            $excess[$difference] = is_array($this->data) && $this->fromDataIdToPropertyCamel
                ? array_search($this->convertKeyNameToCamel($difference), $dataKeys, true)
                : $this->getDataValue($difference);
        }

        return $excess;
    }

    private function convertKeyNameToId(string $input): string
    {
        $separator = '_';
        return mb_strtolower(
            trim(
                str_replace(
                    '_',
                    $separator,
                    preg_replace(
                        '/(?<=\p{L})(?<!\p{Lu})(\p{Lu})/u',
                        addslashes($separator) . '\1',
                        $input
                    )
                ),
                $separator
            )
        );
    }

    private function convertKeyNameToCamel(string $input): string
    {
        $string = preg_replace('/[^\pL\pN]+/u', ' ', $input);
        $words = preg_split('/\s/u', $string, -1, PREG_SPLIT_NO_EMPTY);
        $wordsWithUppercaseFirstCharacter = array_map(static function (string $string) {
            $encoding = 'UTF-8';
            $firstCharacter = mb_substr($string, 0, 1, $encoding);
            $rest = mb_substr($string, 1, null, $encoding);
            return mb_strtoupper($firstCharacter, $encoding) . $rest;
        }, $words);
        return lcfirst(
            str_replace(
                ' ',
                '',
                implode(' ', $wordsWithUppercaseFirstCharacter)
            )
        );
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
        $propertyName = $this->fromDataIdToPropertyCamel
            ? $this->convertKeyNameToId($property->getName())
            : $property->getName();

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
        return $personal ?? $this->defaultMap['*'] ?? null;
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
                is_a($name, SelfFillableConstruct::class, true)
                && (new ReflectionClass($name))->isInstantiable()
            ) {
                return new $name($dataValue);
            }
        }
        return $dataValue;
    }
}
