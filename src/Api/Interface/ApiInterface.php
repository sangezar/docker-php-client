<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Api\Interface;

use Psr\Http\Client\ClientInterface;

/**
 * Base interface for all API components
 */
interface ApiInterface
{
    /**
     * API component constructor
     *
     * @param ClientInterface $httpClient HTTP client for interacting with Docker API
     * @param array<string, mixed> $options Additional configuration parameters
     */
    public function __construct(ClientInterface $httpClient, array $options = []);
}
