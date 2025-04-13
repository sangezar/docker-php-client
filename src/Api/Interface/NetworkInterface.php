<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Api\Interface;

use Sangezar\DockerClient\Config\NetworkConfig;
use Sangezar\DockerClient\Exception\NotFoundException;
use Sangezar\DockerClient\Exception\OperationFailedException;
use Sangezar\DockerClient\Exception\Validation\InvalidConfigurationException;
use Sangezar\DockerClient\Exception\Validation\InvalidParameterValueException;
use Sangezar\DockerClient\Exception\Validation\MissingRequiredParameterException;

/**
 * Interface for working with Docker networks
 */
interface NetworkInterface extends ApiInterface
{
    /** Constants for network drivers */
    public const DRIVER_BRIDGE = 'bridge';
    public const DRIVER_HOST = 'host';
    public const DRIVER_OVERLAY = 'overlay';
    public const DRIVER_MACVLAN = 'macvlan';
    public const DRIVER_IPVLAN = 'ipvlan';
    public const DRIVER_NONE = 'none';

    /** Constants for IPAM drivers */
    public const IPAM_DRIVER_DEFAULT = 'default';
    public const IPAM_DRIVER_NULL = 'null';

    /** Constants for network scope */
    public const SCOPE_LOCAL = 'local';
    public const SCOPE_SWARM = 'swarm';
    public const SCOPE_GLOBAL = 'global';

    /**
     * Gets a list of networks
     *
     * @param array<string, mixed> $filters Filters (driver, id, name, scope)
     * @return array<int, array<string, mixed>> List of networks
     * @throws InvalidParameterValueException if invalid parameters are provided
     */
    public function list(array $filters = []): array;

    /**
     * Gets detailed information about a network
     *
     * @param string $id Network ID or name
     * @return array<string, mixed> Detailed network information
     * @throws MissingRequiredParameterException if network ID is empty
     * @throws NotFoundException if network is not found
     * @throws OperationFailedException if the operation fails
     */
    public function inspect(string $id): array;

    /**
     * Creates a new network
     *
     * @param NetworkConfig $config Network configuration
     * @return array<string, mixed> Created network data
     * @throws InvalidConfigurationException if configuration is invalid
     * @throws OperationFailedException if the operation fails
     */
    public function create(NetworkConfig $config): array;

    /**
     * Connects a container to a network
     *
     * @param string $networkId Network ID or name
     * @param string $containerId Container ID or name
     * @param array<string, mixed> $config Connection settings
     * @return bool Operation success
     * @throws MissingRequiredParameterException if network ID or container ID is empty
     * @throws NotFoundException if network or container is not found
     * @throws OperationFailedException if the operation fails
     */
    public function connect(string $networkId, string $containerId, array $config = []): bool;

    /**
     * Disconnects a container from a network
     *
     * @param string $networkId Network ID or name
     * @param string $containerId Container ID or name
     * @param array<string, mixed> $config Disconnection settings
     * @return bool Operation success
     * @throws MissingRequiredParameterException if network ID or container ID is empty
     * @throws NotFoundException if network or container is not found
     * @throws OperationFailedException if the operation fails
     */
    public function disconnect(string $networkId, string $containerId, array $config = []): bool;

    /**
     * Removes a network
     *
     * @param string $id Network ID or name
     * @return bool Operation success
     * @throws MissingRequiredParameterException if network ID is empty
     * @throws NotFoundException if network is not found
     * @throws OperationFailedException if the operation fails
     */
    public function remove(string $id): bool;

    /**
     * Checks if a network exists
     *
     * @param string $id Network ID or name
     * @return bool Whether the network exists
     * @throws MissingRequiredParameterException if network ID is empty
     */
    public function exists(string $id): bool;

    /**
     * Removes unused networks
     *
     * @param array<string, mixed> $filters Filters for removal
     * @return array<string, mixed> Operation result
     * @throws InvalidParameterValueException if invalid parameters are provided
     * @throws OperationFailedException if the operation fails
     */
    public function prune(array $filters = []): array;
}
