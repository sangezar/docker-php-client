<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Cluster\Operations;

use Sangezar\DockerClient\Config\ContainerConfig;
use Sangezar\DockerClient\Exception\NotFoundException;
use Sangezar\DockerClient\Exception\OperationFailedException;
use Sangezar\DockerClient\Exception\Validation\InvalidConfigurationException;
use Sangezar\DockerClient\Exception\Validation\InvalidParameterValueException;
use Sangezar\DockerClient\Exception\Validation\MissingRequiredParameterException;

/**
 * Container operations on all cluster nodes
 */
class ContainerOperations extends AbstractOperations
{
    /**
     * Gets a list of containers from all cluster nodes
     *
     * @param array<string, mixed> $parameters Filtering parameters
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws InvalidParameterValueException if invalid filtering parameters are provided
     */
    public function list(array $parameters = []): array
    {
        // Validation of filtering parameters
        if (isset($parameters['all']) && ! is_bool($parameters['all'])) {
            throw new InvalidParameterValueException(
                'parameters.all',
                $parameters['all'],
                'boolean',
                'Parameter "all" must be a boolean value'
            );
        }

        if (isset($parameters['size']) && ! is_bool($parameters['size'])) {
            throw new InvalidParameterValueException(
                'parameters.size',
                $parameters['size'],
                'boolean',
                'Parameter "size" must be a boolean value'
            );
        }

        if (isset($parameters['limit']) && (! is_int($parameters['limit']) || $parameters['limit'] < 1)) {
            throw new InvalidParameterValueException(
                'parameters.limit',
                $parameters['limit'],
                'positive integer',
                'Parameter "limit" must be a positive integer'
            );
        }

        if (isset($parameters['filters']) && ! is_array($parameters['filters'])) {
            throw new InvalidParameterValueException(
                'parameters.filters',
                $parameters['filters'],
                'array',
                'Parameter "filters" must be an array'
            );
        }

        return $this->executeOnAll(
            fn ($client) => $client->container()->list($parameters)
        );
    }

    /**
     * Creates a container on all cluster nodes
     *
     * @param ContainerConfig $config Container configuration
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws InvalidConfigurationException if the configuration is invalid
     * @throws OperationFailedException if the operation fails
     */
    public function create(ContainerConfig $config): array
    {
        return $this->executeOnAll(
            fn ($client) => $client->container()->create($config)
        );
    }

    /**
     * Gets detailed information about a container on all cluster nodes
     *
     * @param string $containerId Container ID or name
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws MissingRequiredParameterException if container ID is empty
     */
    public function inspect(string $containerId): array
    {
        if (empty($containerId)) {
            throw new MissingRequiredParameterException(
                'containerId',
                'Container ID cannot be empty'
            );
        }

        return $this->executeOnAll(
            fn ($client) => $client->container()->inspect($containerId)
        );
    }

    /**
     * Starts a container on all cluster nodes
     *
     * @param string $containerId Container ID or name
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws MissingRequiredParameterException if container ID is empty
     */
    public function start(string $containerId): array
    {
        if (empty($containerId)) {
            throw new MissingRequiredParameterException(
                'containerId',
                'Container ID cannot be empty'
            );
        }

        return $this->executeOnAll(
            function ($client) use ($containerId) {
                try {
                    return $client->container()->start($containerId);
                } catch (NotFoundException $e) {
                    // Return structured result for "not found" error
                    return [
                        'error' => true,
                        'errorType' => 'not_found',
                        'message' => sprintf('Container "%s" not found on this node', $containerId),
                    ];
                } catch (\Throwable $e) {
                    throw $e; // Re-throw other errors for handling in executeOnAll
                }
            }
        );
    }

    /**
     * Stops a container on all cluster nodes
     *
     * @param string $containerId Container ID or name
     * @param int $timeout Stop timeout in seconds
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws MissingRequiredParameterException if container ID is empty
     * @throws InvalidParameterValueException if timeout is negative
     */
    public function stop(string $containerId, int $timeout = 10): array
    {
        if (empty($containerId)) {
            throw new MissingRequiredParameterException(
                'containerId',
                'Container ID cannot be empty'
            );
        }

        if ($timeout <= 0) {
            throw new InvalidParameterValueException(
                'timeout',
                $timeout,
                'positive integer',
                'Timeout must be greater than zero'
            );
        }

        return $this->executeOnAll(
            function ($client) use ($containerId, $timeout) {
                try {
                    return $client->container()->stop($containerId, $timeout);
                } catch (NotFoundException $e) {
                    // Return structured result for "not found" error
                    return [
                        'error' => true,
                        'errorType' => 'not_found',
                        'message' => sprintf('Container "%s" not found on this node', $containerId),
                    ];
                } catch (\Throwable $e) {
                    throw $e; // Re-throw other errors for handling in executeOnAll
                }
            }
        );
    }

    /**
     * Restarts a container on all cluster nodes
     *
     * @param string $containerId Container ID or name
     * @param int $timeout Stop timeout in seconds
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws MissingRequiredParameterException if container ID is empty
     * @throws InvalidParameterValueException if timeout is negative
     */
    public function restart(string $containerId, int $timeout = 10): array
    {
        if (empty($containerId)) {
            throw new MissingRequiredParameterException(
                'containerId',
                'Container ID cannot be empty'
            );
        }

        if ($timeout <= 0) {
            throw new InvalidParameterValueException(
                'timeout',
                $timeout,
                'positive integer',
                'Timeout must be greater than zero'
            );
        }

        return $this->executeOnAll(
            fn ($client) => $client->container()->restart($containerId, $timeout)
        );
    }

    /**
     * Removes a container on all cluster nodes
     *
     * @param string $containerId Container ID or name
     * @param bool $force Force remove a running container
     * @param bool $removeVolumes Remove volumes with the container
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws MissingRequiredParameterException if container ID is empty
     */
    public function remove(string $containerId, bool $force = false, bool $removeVolumes = false): array
    {
        if (empty($containerId)) {
            throw new MissingRequiredParameterException(
                'containerId',
                'Container ID cannot be empty'
            );
        }

        return $this->executeOnAll(
            fn ($client) => $client->container()->remove($containerId, $force, $removeVolumes)
        );
    }

    /**
     * Gets logs from a container on all cluster nodes
     *
     * @param string $containerId Container ID or name
     * @param array<string, mixed> $parameters Log request parameters
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws MissingRequiredParameterException if container ID is empty
     * @throws InvalidParameterValueException if invalid parameters are provided
     */
    public function logs(string $containerId, array $parameters = []): array
    {
        if (empty($containerId)) {
            throw new MissingRequiredParameterException(
                'containerId',
                'Container ID cannot be empty'
            );
        }

        // Validation of log parameters
        if (isset($parameters['follow']) && ! is_bool($parameters['follow'])) {
            throw new InvalidParameterValueException(
                'parameters.follow',
                $parameters['follow'],
                'boolean',
                'Parameter "follow" must be a boolean value'
            );
        }

        if (isset($parameters['stdout']) && ! is_bool($parameters['stdout'])) {
            throw new InvalidParameterValueException(
                'parameters.stdout',
                $parameters['stdout'],
                'boolean',
                'Parameter "stdout" must be a boolean value'
            );
        }

        if (isset($parameters['stderr']) && ! is_bool($parameters['stderr'])) {
            throw new InvalidParameterValueException(
                'parameters.stderr',
                $parameters['stderr'],
                'boolean',
                'Parameter "stderr" must be a boolean value'
            );
        }

        if (isset($parameters['timestamps']) && ! is_bool($parameters['timestamps'])) {
            throw new InvalidParameterValueException(
                'parameters.timestamps',
                $parameters['timestamps'],
                'boolean',
                'Parameter "timestamps" must be a boolean value'
            );
        }

        if (isset($parameters['tail']) && ! is_string($parameters['tail']) && ! is_int($parameters['tail'])) {
            throw new InvalidParameterValueException(
                'parameters.tail',
                $parameters['tail'],
                'string or integer',
                'Parameter "tail" must be a string or integer'
            );
        }

        return $this->executeOnAll(
            fn ($client) => $client->container()->logs($containerId, $parameters)
        );
    }

    /**
     * Gets container resource usage statistics on all cluster nodes
     *
     * @param string $containerId Container ID or name
     * @param bool $stream Stream statistics
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws MissingRequiredParameterException if container ID is empty
     */
    public function stats(string $containerId, bool $stream = false): array
    {
        if (empty($containerId)) {
            throw new MissingRequiredParameterException(
                'containerId',
                'Container ID cannot be empty'
            );
        }

        return $this->executeOnAll(
            fn ($client) => $client->container()->stats($containerId, $stream)
        );
    }

    /**
     * Checks if a container exists on all cluster nodes
     *
     * @param string $containerId Container ID or name
     * @return array<string, array<string, mixed>|bool> Whether the container exists on each node
     * @throws MissingRequiredParameterException if container ID is empty
     */
    public function exists(string $containerId): array
    {
        if (empty($containerId)) {
            throw new MissingRequiredParameterException(
                'containerId',
                'Container ID cannot be empty'
            );
        }

        return $this->executeOnAll(
            fn ($client) => $client->container()->exists($containerId)
        );
    }

    /**
     * Checks if a container exists on all cluster nodes
     *
     * @param string $containerId Container ID
     * @return bool Whether the container exists on all nodes
     * @throws MissingRequiredParameterException if container ID is empty
     */
    public function existsOnAllNodes(string $containerId): bool
    {
        if (empty($containerId)) {
            throw new MissingRequiredParameterException(
                'containerId',
                'Container ID cannot be empty'
            );
        }

        $results = $this->exists($containerId);

        // Check if all results = true
        foreach ($results as $nodeResult) {
            if ($nodeResult !== true) {
                return false;
            }
        }

        return true;
    }

    /**
     * Gets list of nodes where the container exists
     *
     * @param string $containerId Container ID
     * @return array<int, string> List of node names
     * @throws MissingRequiredParameterException if container ID is empty
     */
    public function getNodesWithContainer(string $containerId): array
    {
        if (empty($containerId)) {
            throw new MissingRequiredParameterException(
                'containerId',
                'Container ID cannot be empty'
            );
        }

        $results = $this->exists($containerId);
        $nodesWithContainer = [];

        foreach ($results as $nodeName => $exists) {
            if ($exists === true) {
                $nodesWithContainer[] = $nodeName;
            }
        }

        return $nodesWithContainer;
    }
}
