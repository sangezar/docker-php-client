<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Api;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Sangezar\DockerClient\Api\Interface\ApiInterface;

/**
 * Abstract class with common API logic
 */
abstract class AbstractApi implements ApiInterface
{
    /**
     * HTTP client
     */
    protected ClientInterface $httpClient;

    /**
     * API client options
     *
     * @var array<string, mixed>
     */
    protected array $options;

    /**
     * API component constructor
     *
     * @param ClientInterface $httpClient HTTP client
     * @param array<string, mixed> $options Additional options
     */
    public function __construct(ClientInterface $httpClient, array $options = [])
    {
        $this->httpClient = $httpClient;
        $this->options = $options;
    }

    /**
     * Executes GET request
     *
     * @param string $path URL path
     * @param array<string, mixed> $parameters Request parameters
     * @return array<string, mixed> Request result
     */
    protected function get(string $path, array $parameters = []): array
    {
        return $this->request('GET', $path, $parameters);
    }

    /**
     * Executes POST request
     *
     * @param string $path URL path
     * @param array<string, mixed> $parameters Request parameters
     * @return array<string, mixed> Request result
     */
    protected function post(string $path, array $parameters = []): array
    {
        return $this->request('POST', $path, $parameters);
    }

    /**
     * Executes PUT request
     *
     * @param string $path URL path
     * @param array<string, mixed> $parameters Request parameters
     * @return array<string, mixed> Request result
     */
    protected function put(string $path, array $parameters = []): array
    {
        return $this->request('PUT', $path, $parameters);
    }

    /**
     * Executes DELETE request
     *
     * @param string $path URL path
     * @param array<string, mixed> $parameters Request parameters
     * @return array<string, mixed> Request result
     */
    protected function delete(string $path, array $parameters = []): array
    {
        return $this->request('DELETE', $path, $parameters);
    }

    /**
     * Executes HTTP request
     *
     * @param string $method HTTP method
     * @param string $path URL path
     * @param array<string, mixed> $parameters Request parameters
     * @return array<string, mixed> Request result
     */
    protected function request(string $method, string $path, array $parameters = []): array
    {
        $options = $this->createRequestOptions($parameters);
        $uri = $this->options['base_uri'] . $path;

        $headers = [];
        if (isset($options['headers']) && is_array($options['headers'])) {
            foreach ($options['headers'] as $name => $value) {
                $headers[(string)$name] = (string)$value;
            }
        }

        $request = new Request($method, $uri, $headers);
        $response = $this->httpClient->sendRequest($request);

        return $this->handleResponse($response);
    }

    /**
     * Creates request options
     *
     * @param array<string, mixed> $parameters Request parameters
     * @return array<string, mixed> Request options
     */
    protected function createRequestOptions(array $parameters = []): array
    {
        $options = $this->options;

        if (! empty($parameters)) {
            if (isset($parameters['query']) && is_array($parameters['query'])) {
                $options['query'] = $parameters['query'];
            }

            if (isset($parameters['json']) && is_array($parameters['json'])) {
                $options['json'] = $parameters['json'];
            }

            if (isset($parameters['headers']) && is_array($parameters['headers'])) {
                $options['headers'] = array_merge(
                    is_array($options['headers'] ?? null) ? $options['headers'] : [],
                    $parameters['headers']
                );
            }
        }

        return $options;
    }

    /**
     * Processes API response
     *
     * @param ResponseInterface $response HTTP response
     * @return array<string, mixed> Processed response
     */
    protected function handleResponse(ResponseInterface $response): array
    {
        $content = (string) $response->getBody();

        if (empty($content)) {
            return [];
        }

        $decodedResponse = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        return is_array($decodedResponse) ? $decodedResponse : [];
    }
}
