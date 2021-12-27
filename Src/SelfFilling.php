<?php

declare(strict_types=1);

namespace Makhnanov\PhpSelfFilling;

use InvalidArgumentException;
use JetBrains\PhpStorm\Immutable;
use JsonException;
use Makhnanov\PhpSelfFilling\Behaviour\ErrorBehaviour;
use Makhnanov\PhpSelfFilling\Behaviour\Excess;
use Makhnanov\PhpSelfFilling\Behaviour\MissingData;
use Makhnanov\PhpSelfFilling\Exception\ExcessException;
use Makhnanov\PhpSelfFilling\Exception\MissingDataException;
use ReflectionException;
use ReflectionProperty;
use Throwable;

trait SelfFilling
{
    /** @var array<string, Throwable> */
    #[Immutable(Immutable::PRIVATE_WRITE_SCOPE)]
    public array $selfFillErrors = [];

    /** @var array<int, string> */
    #[Immutable(Immutable::PRIVATE_WRITE_SCOPE)]
    public array $selfFillMissingData = [];

    /**
     * RU: Лишние входные данные которые никак не используются.
     * EN: Extra input data is not used in any way.
     *
     * @var array
     */
    #[Immutable(Immutable::PRIVATE_WRITE_SCOPE)]
    public array $selfFillExcess = [];

    /**
     * Самозаполнение свойств
     *
     * @param string|array|object $data # Test ready
     *                                  RU: Входные внешние данные. Допускается массив, объект, или строка.
     *                                      Строка будет преобразована в массив стандартной функцией json_decode().
     *                                      Если тип свойства не встроенный то:
     *                                          в случе если это массив:
     *                                              ToDo:
     *                                          в случае если это объект:
     *                                              ToDo:
     *                                  EN: Input external data
     *                                      ToDo:
     *
     * @param array $defaultMap # Test ready
     *                          RU: Массив для значений по умолчанию.
     *                              Будет заполняться если $missedDataBehaviour == MissingData::REPLACE_WITH_DEFAULT.
     *                              Возможно объединение свойств через | для указания одного значения для нескольких.
     *                              Возможно указание регулярного выражения для одного значения для нескольких свойств.
     *                              Для всех свойств не подходящих под синтаксис | или синтаксис регулярного выражения
     *                                  будет применяться ['*' => null] по умолчанию, и его тоже можно изменить.
     *                          EN: Array of defaults.
     *                              Properties will got defaults if $missedDataBehaviour == MissingData::REPLACE_WITH_DEFAULT
     *                              Also you can use | syntax or regex.
     *                              If there are no satisfying condition ['*' => null] will be used.
     *                          Examples:
     *                              $class->selfFill(defaultMap: [
     *                                  'fistProperty|secondProperty' => 'Default value 1',
     *                                  'thirdProperty|fourthProperty' => 'Another value',
     *                              ]);
     *                              $class->selfFill(defaultMap: [
     *                                  '/^_/' => null,
     *                                  '/^(is|can)/' => false,
     *                              ]);
     *                              $class->selfFill(defaultMap: ['*' => 'Null will be replaced by this string for all']);
     *
     * @param array $filterMap
     *
     * @param array $exclude # Test ready
     *                       RU: Свойства, которые не надо заполнять.
     *                           Можно перечислять строками в массиве.
     *                           Также можно указывать через регулярные выражения.
     *                       EN: Properties list for ignore filling.
     *                           Array of property names or regex strings
     *                       Examples:
     *                           $class->selfFill(exclude: ['fistProperty', 'secondProperty', '/^_/']);
     *
     * @param ErrorBehaviour $errorBehaviour
     * @param MissingData $missingDataBehaviour
     * @param Excess $excessBehaviour
     *
     * @param int $modifier # Test
     *                      RU: Модификатор доступа свойства. Отвечает за то - какие свойства будут заполняться.
     *                          Рекомендованные значения - это константы класса ReflectionProperty
     *                          Значение по умолчанию ReflectionProperty::IS_PUBLIC
     *                              означает что будут заполняться только публичные свойства.
     *                          В случае указания неверного значения будет выброшено ReflectionException
     *                      EN:
     *                          ToDo:
     *                      Available values:
     *                          ReflectionProperty::IS_PUBLIC
     *                          ReflectionProperty::IS_PROTECTED
     *                          ReflectionProperty::IS_PRIVATE
     *                          ReflectionProperty::IS_STATIC
     *                          ReflectionProperty::IS_READONLY
     *
     *
     * @param string $finder # Test ready
     *                       RU: Класс - наполнитель в котором происходит вся бизнес логика заполнения свойств.
     *                           По умолчанию это Makhnanov\PhpSelfFilling\Finder,
     *                           но его можно изменить и использовать наследника
     *                       EN: Class - filler, which include filling business logic.
     *                           By default Makhnanov\PhpSelfFilling\Finder using. You can extend it and use child.
     *                       Examples:
     *                           $class->selfFill(filler: App\Models\ExtendedFiller::class);
     *
     * @param bool $fromDataIdToPropertyCamel
     *
     * @throws ExcessException
     * @throws JsonException RU: Если данные в $data это строка с не валидным JSON
     *                       EN: If param $data has invalid JSON
     * @throws MissingDataException
     * @throws ReflectionException
     * @throws Throwable
     */
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
        bool                $fromDataIdToPropertyCamel = false,
    ): void {
        $exclude = array_merge($exclude, [
            'selfFillExcess',
            'selfFillErrors',
            'selfFillMissingData'
        ]);

        if (is_string($data)) {
            $data = json_decode($data, true, flags: JSON_THROW_ON_ERROR);
        }

        if (!is_a($finder, Finder::class, true)) {
            throw new InvalidArgumentException('Finder must be extended from ' . Finder::class . ' class.');
        }

        /** @var Finder $finder */
        $finder = new $finder(
            $this,
            $data,
            $defaultMap,
            $filterMap,
            $exclude,
            $modifier,
            $fromDataIdToPropertyCamel,
        );

        $this->selfFillExcess = $finder->detectExcess();

        if ($excessBehaviour === Excess::THROW && $this->selfFillExcess) {
            throw new ExcessException($this->selfFillExcess);
        }

        foreach ($finder->receiverProperties as $property) {
            try {
                try {
                    $property->setValue($this, $finder->getValue(
                        $property,
                        $missingDataBehaviour === MissingData::REPLACE_WITH_DEFAULT
                    ));
                } catch (MissingDataException $e) {
                    $this->selfFillMissingData[] = $property->name;
                    if ($missingDataBehaviour === MissingData::THROW_AFTER_FIRST) {
                        throw $e;
                    }
                }

            } catch (Throwable $e) {
                $this->selfFillErrors[$property->name] = $e;
                if ($errorBehaviour === ErrorBehaviour::THROW_AFTER_FIRST) {
                    throw $e;
                }
                if ($errorBehaviour === ErrorBehaviour::REPLACE_WITH_DEFAULT) {
                    $property->setValue($this, $finder->getDefault($property));
                }
            }
        }
    }
}
