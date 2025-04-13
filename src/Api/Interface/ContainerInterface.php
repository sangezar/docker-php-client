<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Api\Interface;

use Sangezar\DockerClient\Config\ContainerConfig;
use Sangezar\DockerClient\Exception\NotFoundException;
use Sangezar\DockerClient\Exception\OperationFailedException;
use Sangezar\DockerClient\Exception\Validation\InvalidConfigurationException;
use Sangezar\DockerClient\Exception\Validation\InvalidParameterValueException;
use Sangezar\DockerClient\Exception\Validation\MissingRequiredParameterException;

/**
 * Interface for working with Docker containers
 */
interface ContainerInterface extends ApiInterface
{
    /**
     * Gets a list of containers
     *
     * @param array<string, mixed> $parameters Filtering parameters
     * @return array<int, array<string, mixed>> List of containers
     * @throws InvalidParameterValueException if invalid parameters are provided
     */
    public function list(array $parameters = []): array;

    /**
     * Creates a new container
     *
     * @param ContainerConfig $config Container configuration
     * @return array<string, mixed> Created container data
     * @throws MissingRequiredParameterException if required parameters are not specified
     * @throws InvalidParameterValueException if invalid parameters are provided
     * @throws InvalidConfigurationException if configuration is invalid
     * @throws OperationFailedException if the operation fails
     */
    public function create(ContainerConfig $config): array;

    /**
     * Gets detailed information about a container
     *
     * @param string $containerId Container ID or name
     * @return array<string, mixed> Detailed container information
     * @throws MissingRequiredParameterException if container ID is empty
     * @throws NotFoundException if container is not found
     * @throws OperationFailedException if the operation fails
     */
    public function inspect(string $containerId): array;

    /**
     * Starts a container
     *
     * @param string $containerId Container ID or name
     * @return bool Operation success
     * @throws MissingRequiredParameterException if container ID is empty
     * @throws NotFoundException if container is not found
     * @throws OperationFailedException if the operation fails
     */
    public function start(string $containerId): bool;

    /**
     * Stops a container
     *
     * @param string $containerId Container ID or name
     * @param int $timeout Stop timeout in seconds
     * @return bool Operation success
     * @throws MissingRequiredParameterException if container ID is empty
     * @throws InvalidParameterValueException if timeout is negative
     * @throws NotFoundException if container is not found
     * @throws OperationFailedException if the operation fails
     */
    public function stop(string $containerId, int $timeout = 10): bool;

    /**
     * Restarts a container
     *
     * @param string $containerId Container ID or name
     * @param int $timeout Stop timeout in seconds
     * @return bool Operation success
     * @throws MissingRequiredParameterException if container ID is empty
     * @throws InvalidParameterValueException if timeout is negative
     * @throws NotFoundException if container is not found
     * @throws OperationFailedException if the operation fails
     */
    public function restart(string $containerId, int $timeout = 10): bool;

    /**
     * Sends a signal to a container to stop it
     *
     * @param string $id Container ID or name
     * @param string|null $signal Signal to send (default: SIGKILL)
     * @return bool Operation success
     * @throws InvalidParameterValueException if signal is invalid
     * @throws OperationFailedException if the operation fails
     */
    public function kill(string $id, string $signal = null): bool;

    /**
     * Removes a container
     *
     * @param string $containerId Container ID or name
     * @param bool $force Force remove a running container
     * @param bool $removeVolumes Remove anonymous volumes with the container
     * @return bool Operation success
     * @throws MissingRequiredParameterException if container ID is empty
     * @throws NotFoundException if container is not found
     * @throws OperationFailedException if the operation fails
     */
    public function remove(string $containerId, bool $force = false, bool $removeVolumes = false): bool;

    /**
     * Gets container logs
     *
     * @param string $containerId Container ID or name
     * @param array<string, mixed> $parameters Log request parameters
     * @return array<string, mixed> Container logs
     * @throws MissingRequiredParameterException if container ID is empty
     * @throws InvalidParameterValueException if invalid parameters are provided
     * @throws NotFoundException if container is not found
     * @throws OperationFailedException if the operation fails
     */
    public function logs(string $containerId, array $parameters = []): array;

    /**
     * Gets container resource usage statistics
     *
     * @param string $containerId Container ID or name
     * @param bool $stream Stream real-time statistics
     * @return array<string, mixed> Container statistics
     * @throws MissingRequiredParameterException if container ID is empty
     * @throws NotFoundException if container is not found
     * @throws OperationFailedException if the operation fails
     */
    public function stats(string $containerId, bool $stream = false): array;

    /**
     * Checks if a container exists
     *
     * @param string $containerId Container ID or name
     * @return bool Whether the container exists
     * @throws MissingRequiredParameterException if container ID is empty
     */
    public function exists(string $containerId): bool;
}
