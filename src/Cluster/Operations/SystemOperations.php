<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Cluster\Operations;

use Sangezar\DockerClient\Exception\OperationFailedException;
use Sangezar\DockerClient\Exception\Validation\InvalidParameterValueException;
use Sangezar\DockerClient\Exception\Validation\MissingRequiredParameterException;

/**
 * Docker system operations on all cluster nodes
 */
class SystemOperations extends AbstractOperations
{
    /**
     * Gets Docker version information from all cluster nodes
     *
     * @return array<string, array<string, mixed>|bool> Results for each node
     */
    public function version(): array
    {
        return $this->executeOnAll(
            fn ($client) => $client->system()->version()
        );
    }

    /**
     * Gets system information from all cluster nodes
     *
     * @return array<string, array<string, mixed>|bool> Results for each node
     */
    public function info(): array
    {
        return $this->executeOnAll(
            fn ($client) => $client->system()->info()
        );
    }

    /**
     * Checks authentication with Docker registry on all cluster nodes
     *
     * @param array<string, mixed> $authConfig Authentication configuration
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws MissingRequiredParameterException if configuration doesn't contain required fields
     * @throws InvalidParameterValueException if invalid parameters are provided
     */
    public function auth(array $authConfig): array
    {
        // Check for required fields
        if (empty($authConfig)) {
            throw new MissingRequiredParameterException(
                'authConfig',
                'Authentication configuration cannot be empty'
            );
        }

        // Check based on authentication type
        if (isset($authConfig['username']) && ! isset($authConfig['password'])) {
            throw new MissingRequiredParameterException(
                'authConfig.password',
                'Password is required when username is provided'
            );
        }

        if (! isset($authConfig['username']) && ! isset($authConfig['identitytoken']) && ! isset($authConfig['auth'])) {
            throw new MissingRequiredParameterException(
                'authConfig.username, authConfig.identitytoken or authConfig.auth',
                'At least one authentication method is required (username/password, identity token or auth token)'
            );
        }

        return $this->executeOnAll(
            function ($client) use ($authConfig) {
                try {
                    return $client->system()->auth($authConfig);
                } catch (\Throwable $e) {
                    // Create structured error for return
                    throw new OperationFailedException(
                        'auth',
                        'system',
                        isset($authConfig['serveraddress']) && is_string($authConfig['serveraddress'])
                            ? $authConfig['serveraddress']
                            : 'docker.io',
                        'Authentication failed: ' . $e->getMessage(),
                        0,
                        $e
                    );
                }
            }
        );
    }

    /**
     * Checks Docker API availability on all cluster nodes
     *
     * @return array<string, array<string, mixed>|bool> Results for each node
     */
    public function ping(): array
    {
        // For the ping method, we don't use exception handling because it's specifically designed
        // to return false when the server is unavailable
        return $this->executeOnAll(
            fn ($client) => $client->system()->ping()
        );
    }

    /**
     * Gets event stream from all cluster nodes
     *
     * @param array<string, mixed> $filters Event filters
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws InvalidParameterValueException if invalid filtering parameters are provided
     */
    public function events(array $filters = []): array
    {
        // Validate filters
        if (! empty($filters) && ! is_array($filters)) {
            throw new InvalidParameterValueException(
                'filters',
                $filters,
                'array',
                'Filters must be an array'
            );
        }

        // Validate filter keys
        $allowedFilterKeys = ['container', 'event', 'image', 'label', 'type', 'volume', 'network', 'daemon', 'since', 'until'];

        foreach ($filters as $key => $value) {
            if (! in_array($key, $allowedFilterKeys)) {
                throw new InvalidParameterValueException(
                    'filters.' . $key,
                    $key,
                    implode(', ', $allowedFilterKeys),
                    'Invalid filter key'
                );
            }

            // Check timestamp formats
            if ($key === 'since' || $key === 'until') {
                if (! is_numeric($value) && ! is_string($value)) {
                    throw new InvalidParameterValueException(
                        'filters.' . $key,
                        $value,
                        'timestamp (Unix timestamp or ISO 8601 format)',
                        'Invalid timestamp format: must be numeric or string'
                    );
                }

                if (is_string($value) && ! preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(\.\d+)?(Z|[\+\-]\d{2}:\d{2})$/', $value)) {
                    throw new InvalidParameterValueException(
                        'filters.' . $key,
                        $value,
                        'timestamp (Unix timestamp or ISO 8601 format)',
                        'Invalid ISO 8601 timestamp format'
                    );
                }
            }

            // Check event type
            if ($key === 'type' && ! in_array($value, ['container', 'image', 'volume', 'network', 'daemon'])) {
                throw new InvalidParameterValueException(
                    'filters.type',
                    $value,
                    'container, image, volume, network, daemon',
                    'Invalid event type'
                );
            }
        }

        return $this->executeOnAll(
            function ($client) use ($filters) {
                try {
                    return $client->system()->events($filters);
                } catch (\Throwable $e) {
                    // Create structured error for return
                    throw new OperationFailedException(
                        'events',
                        'system',
                        'events',
                        'Failed to retrieve events: ' . $e->getMessage(),
                        0,
                        $e
                    );
                }
            }
        );
    }

    /**
     * Gets data usage information from all cluster nodes
     *
     * @return array<string, array<string, mixed>|bool> Results for each node
     */
    public function dataUsage(): array
    {
        return $this->executeOnAll(
            function ($client) {
                try {
                    return $client->system()->dataUsage();
                } catch (\Throwable $e) {
                    // Create structured error for return
                    throw new OperationFailedException(
                        'dataUsage',
                        'system',
                        'data_usage',
                        'Failed to retrieve data usage information: ' . $e->getMessage(),
                        0,
                        $e
                    );
                }
            }
        );
    }

    /**
     * Gets disk space usage information from all cluster nodes
     *
     * @return array<string, array<string, mixed>|bool> Results for each node
     */
    public function df(): array
    {
        return $this->executeOnAll(
            function ($client) {
                try {
                    return $client->system()->df();
                } catch (\Throwable $e) {
                    // Create structured error for return
                    throw new OperationFailedException(
                        'df',
                        'system',
                        'disk_usage',
                        'Failed to retrieve disk usage information: ' . $e->getMessage(),
                        0,
                        $e
                    );
                }
            }
        );
    }

    /**
     * Removes unused containers on all cluster nodes
     *
     * @param array<string, mixed> $filters Cleanup filters
     * @return array<string, array<string, mixed>|bool> Results for each node
     */
    public function pruneContainers(array $filters = []): array
    {
        return $this->executeOnAll(
            function ($client) use ($filters) {
                try {
                    return $client->system()->pruneContainers($filters);
                } catch (\Throwable $e) {
                    // Create structured error for return
                    throw new OperationFailedException(
                        'pruneContainers',
                        'system',
                        'containers',
                        'Failed to prune containers: ' . $e->getMessage(),
                        0,
                        $e
                    );
                }
            }
        );
    }

    /**
     * Removes unused images on all cluster nodes
     *
     * @param array<string, mixed> $filters Cleanup filters
     * @return array<string, array<string, mixed>|bool> Results for each node
     */
    public function pruneImages(array $filters = []): array
    {
        return $this->executeOnAll(
            function ($client) use ($filters) {
                try {
                    return $client->system()->pruneImages($filters);
                } catch (\Throwable $e) {
                    // Create structured error for return
                    throw new OperationFailedException(
                        'pruneImages',
                        'system',
                        'images',
                        'Failed to prune images: ' . $e->getMessage(),
                        0,
                        $e
                    );
                }
            }
        );
    }

    /**
     * Removes unused networks on all cluster nodes
     *
     * @param array<string, mixed> $filters Cleanup filters
     * @return array<string, array<string, mixed>|bool> Results for each node
     */
    public function pruneNetworks(array $filters = []): array
    {
        return $this->executeOnAll(
            function ($client) use ($filters) {
                try {
                    return $client->system()->pruneNetworks($filters);
                } catch (\Throwable $e) {
                    // Create structured error for return
                    throw new OperationFailedException(
                        'pruneNetworks',
                        'system',
                        'networks',
                        'Failed to prune networks: ' . $e->getMessage(),
                        0,
                        $e
                    );
                }
            }
        );
    }

    /**
     * Removes unused volumes on all cluster nodes
     *
     * @param array<string, mixed> $filters Cleanup filters
     * @return array<string, array<string, mixed>|bool> Results for each node
     */
    public function pruneVolumes(array $filters = []): array
    {
        return $this->executeOnAll(
            function ($client) use ($filters) {
                try {
                    return $client->system()->pruneVolumes($filters);
                } catch (\Throwable $e) {
                    // Create structured error for return
                    throw new OperationFailedException(
                        'pruneVolumes',
                        'system',
                        'volumes',
                        'Failed to prune volumes: ' . $e->getMessage(),
                        0,
                        $e
                    );
                }
            }
        );
    }

    /**
     * Removes all unused resources on all cluster nodes
     *
     * @param array<string, mixed> $filters Cleanup filters
     * @return array<string, array<string, mixed>|bool> Results for each node
     */
    public function prune(array $filters = []): array
    {
        return $this->executeOnAll(
            function ($client) use ($filters) {
                try {
                    return $client->system()->prune($filters);
                } catch (\Throwable $e) {
                    // Create structured error for return
                    throw new OperationFailedException(
                        'prune',
                        'system',
                        'all',
                        'Failed to prune all resources: ' . $e->getMessage(),
                        0,
                        $e
                    );
                }
            }
        );
    }
}
