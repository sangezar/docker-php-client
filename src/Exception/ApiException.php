<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Exception;

/**
 * Base class for Docker API related exceptions
 */
class ApiException extends \RuntimeException
{
    /**
     * API response data
     *
     * @var array<string, mixed>|null
     */
    protected ?array $responseData = null;

    /**
     * HTTP status code of the response
     */
    protected ?int $statusCode = null;

    /**
     * @param string $message Error message
     * @param int $code Error code
     * @param \Throwable|null $previous Previous exception
     * @param array<string, mixed>|null $responseData API response data
     * @param int|null $statusCode HTTP status code of the response
     */
    public function __construct(
        string $message,
        int $code = 0,
        ?\Throwable $previous = null,
        ?array $responseData = null,
        ?int $statusCode = null
    ) {
        $this->responseData = $responseData;
        $this->statusCode = $statusCode;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get API response data
     *
     * @return array<string, mixed>|null
     */
    public function getResponseData(): ?array
    {
        return $this->responseData;
    }

    /**
     * Get HTTP status code of the response
     *
     * @return int|null
     */
    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    /**
     * Create exception from response data
     *
     * @param array<string, mixed> $responseData API response data
     * @param int $statusCode HTTP status code of the response
     * @return self
     */
    public static function fromResponse(array $responseData, int $statusCode): self
    {
        $message = $responseData['message'] ?? sprintf('API error with status %d', $statusCode);
        $code = $responseData['code'] ?? 0;

        if (! is_string($message)) {
            $message = 'Unknown error';
        }

        if (! is_int($code)) {
            $code = 0;
        }

        return new self((string)$message, (int)$code, null, $responseData, $statusCode);
    }
}
