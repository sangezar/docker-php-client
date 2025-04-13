<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Exception\Validation;

use Sangezar\DockerClient\Exception\ApiException;

/**
 * Base class for all validation-related exceptions
 */
class ValidationException extends ApiException
{
    /**
     * @param string $message Error message
     * @param int $code Error code
     * @param \Throwable|null $previous Previous exception that caused this error
     */
    public function __construct(string $message, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
