<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Exception\Validation;

/**
 * Exception thrown when a parameter value is invalid
 */
class InvalidParameterValueException extends ValidationException
{
    private string $parameterName;
    private mixed $actualValue;
    private ?string $expectedFormat;

    /**
     * @param string $parameterName Name of the parameter with invalid value
     * @param mixed $actualValue Current parameter value
     * @param string|null $expectedFormat Expected value format (optional)
     * @param string $message Additional message (automatically generated by default)
     * @param int $code Error code
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct(
        string $parameterName,
        mixed $actualValue,
        ?string $expectedFormat = null,
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        $this->parameterName = $parameterName;
        $this->actualValue = $actualValue;
        $this->expectedFormat = $expectedFormat;

        if (empty($message)) {
            $message = sprintf(
                'Invalid value for parameter "%s": %s',
                $parameterName,
                is_scalar($actualValue)
                    ? var_export($actualValue, true)
                    : gettype($actualValue)
            );

            if ($expectedFormat !== null) {
                $message .= sprintf('. Expected format: %s', $expectedFormat);
            }
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the parameter name
     *
     * @return string
     */
    public function getParameterName(): string
    {
        return $this->parameterName;
    }

    /**
     * Get the current parameter value
     *
     * @return mixed
     */
    public function getActualValue(): mixed
    {
        return $this->actualValue;
    }

    /**
     * Get the expected value format
     *
     * @return string|null
     */
    public function getExpectedFormat(): ?string
    {
        return $this->expectedFormat;
    }
}
