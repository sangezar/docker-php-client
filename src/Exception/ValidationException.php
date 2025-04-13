<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Exception;

/**
 * Виняток для помилок валідації параметрів
 */
class ValidationException extends \InvalidArgumentException
{
    /**
     * Конструктор
     *
     * @param string $message Повідомлення про помилку
     * @param int $code Код помилки
     * @param \Throwable|null $previous Попередній виняток
     */
    public function __construct(string $message, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Створює виняток для невалідного значення параметра
     *
     * @param string $paramName Назва параметра
     * @param mixed $value Передане значення
     * @param string $expectedType Очікуваний тип або формат
     * @return self
     */
    public static function invalidValue(string $paramName, $value, string $expectedType): self
    {
        $valueType = is_object($value) ? get_class($value) : gettype($value);
        $valueStr = is_scalar($value) ? (string)$value : $valueType;

        return new self(
            sprintf(
                'Invalid value for parameter "%s": got "%s", expected %s',
                $paramName,
                $valueStr,
                $expectedType
            )
        );
    }

    /**
     * Створює виняток для обов'язкового параметра, який відсутній
     *
     * @param string $paramName Назва параметра
     * @return self
     */
    public static function requiredParameter(string $paramName): self
    {
        return new self(
            sprintf('Parameter "%s" is required', $paramName)
        );
    }

    /**
     * Створює виняток для невідомого значення з переліку допустимих
     *
     * @param string $paramName Назва параметра
     * @param mixed $value Передане значення
     * @param array<int|string, mixed> $allowedValues Масив допустимих значень
     * @return self
     */
    public static function unknownValue(string $paramName, $value, array $allowedValues): self
    {
        $valueStr = is_scalar($value) ? (string)$value : (is_object($value) ? get_class($value) : gettype($value));

        return new self(
            sprintf(
                'Unknown value "%s" for parameter "%s". Allowed values: %s',
                $valueStr,
                $paramName,
                implode(', ', array_map(function ($val) {
                    return is_scalar($val) ? (string)$val : (is_object($val) ? get_class($val) : gettype($val));
                }, $allowedValues))
            )
        );
    }
}
