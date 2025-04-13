<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Api;

use Sangezar\DockerClient\Api\Interface\NetworkInterface;
use Sangezar\DockerClient\Config\NetworkConfig;
use Sangezar\DockerClient\Exception\NotFoundException;
use Sangezar\DockerClient\Exception\OperationFailedException;
use Sangezar\DockerClient\Exception\Validation\InvalidConfigurationException;
use Sangezar\DockerClient\Exception\Validation\InvalidParameterValueException;
use Sangezar\DockerClient\Exception\Validation\MissingRequiredParameterException;

/**
 * Docker Network API client
 */
class Network extends AbstractApi implements NetworkInterface
{
    /**
     * Constants for network driver types
     */
    public const DRIVER_BRIDGE = 'bridge';
    public const DRIVER_HOST = 'host';
    public const DRIVER_OVERLAY = 'overlay';
    public const DRIVER_MACVLAN = 'macvlan';
    public const DRIVER_IPVLAN = 'ipvlan';
    public const DRIVER_NONE = 'none';

    /**
     * Constants for network creation options
     */
    public const SCOPE_LOCAL = 'local';
    public const SCOPE_SWARM = 'swarm';
    public const SCOPE_GLOBAL = 'global';

    public const IPAM_DRIVER_DEFAULT = 'default';
    public const IPAM_DRIVER_NULL = 'null';

    /**
     * List networks
     *
     * @param array<string, mixed> $filters Filters to apply as JSON or array
     *    - driver: string - Filter by network driver
     *    - id: string - Network ID
     *    - label: string - Filter by network labels
     *    - name: string - Network name
     *    - scope: string - Filter by network scope (swarm, global, or local)
     *    - type: string - Filter by network type (custom or builtin)
     * @return array<int, array<string, mixed>> List of networks
     * @throws InvalidParameterValueException if invalid filters are provided
     */
    public function list(array $filters = []): array
    {
        // Validate filters
        $allowedFilters = ['driver', 'id', 'label', 'name', 'scope', 'type'];
        foreach (array_keys($filters) as $filter) {
            if (! in_array($filter, $allowedFilters)) {
                throw new InvalidParameterValueException(
                    'filters',
                    $filters,
                    implode(', ', $allowedFilters),
                    sprintf(
                        'Filter "%s" is not supported for the list method. Allowed filters: %s',
                        $filter,
                        implode(', ', $allowedFilters)
                    )
                );
            }
        }

        // Validate scope filter if present
        if (isset($filters['scope'])) {
            $allowedScopes = [
                self::SCOPE_LOCAL,
                self::SCOPE_SWARM,
                self::SCOPE_GLOBAL,
            ];

            if (! in_array($filters['scope'], $allowedScopes)) {
                throw new InvalidParameterValueException(
                    'scope',
                    $filters['scope'],
                    implode(', ', $allowedScopes),
                    'The "scope" filter value must be one of: ' . implode(', ', $allowedScopes)
                );
            }
        }

        // Validate type filter if present
        if (isset($filters['type']) && ! in_array($filters['type'], ['custom', 'builtin'])) {
            throw new InvalidParameterValueException(
                'type',
                $filters['type'],
                'custom or builtin',
                'The "type" filter value must be one of: custom or builtin'
            );
        }

        $response = $this->get('/networks', ['query' => ['filters' => json_encode($filters)]]);

        // Ensure the response is an array with integer keys
        $result = [];
        foreach ($response as $item) {
            if (is_array($item)) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * Inspect a network
     *
     * @param string $id Network ID or name
     * @return array<string, mixed> Network details
     * @throws MissingRequiredParameterException if network ID is empty
     * @throws NotFoundException if the network is not found
     */
    public function inspect(string $id): array
    {
        if (empty($id)) {
            throw new MissingRequiredParameterException(
                'id',
                'Network ID cannot be empty'
            );
        }

        try {
            return $this->get("/networks/{$id}");
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), '404') !== false) {
                throw new NotFoundException('network', $id);
            }

            throw $e;
        }
    }

    /**
     * Creates a new network
     *
     * @param NetworkConfig $config Network configuration
     * @return array<string, mixed> Information about the created network
     * @throws InvalidConfigurationException if configuration is invalid
     * @throws OperationFailedException if the operation fails
     */
    public function create(NetworkConfig $config): array
    {
        $configArray = $config->toArray();

        if (isset($configArray['Name']) && is_string($configArray['Name'])) {
            $networkName = $configArray['Name'];
        } else {
            $networkName = null;
        }

        try {
            return $this->post('/networks/create', ['json' => $configArray]);
        } catch (\Exception $e) {
            throw new OperationFailedException(
                'create',
                'network',
                $networkName,
                'Failed to create network: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Connect a container to a network
     *
     * @param string $id Network ID or name
     * @param string $container Container ID or name
     * @param array<string, mixed> $config Connection configuration
     *    - EndpointConfig: array - Endpoint configuration
     *    - IPAddress: string - IPv4 address
     *    - IPv6Address: string - IPv6 address
     *    - Links: array - Links to other containers
     *    - Aliases: array - Names to use in the network
     * @return bool Connection result
     * @throws MissingRequiredParameterException if required parameters are missing
     * @throws NotFoundException if the network or container is not found
     * @throws OperationFailedException if the connection fails
     */
    public function connect(string $id, string $container, array $config = []): bool
    {
        if (empty($id)) {
            throw new MissingRequiredParameterException(
                'id',
                'Network ID cannot be empty'
            );
        }

        if (empty($container)) {
            throw new MissingRequiredParameterException(
                'container',
                'Container ID cannot be empty'
            );
        }

        try {
            $parameters = array_merge(['Container' => $container], $config);
            $this->post("/networks/{$id}/connect", ['json' => $parameters]);

            return true;
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), '404') !== false) {
                // Check if it's the network or container that wasn't found
                if (strpos($e->getMessage(), 'network') !== false) {
                    throw new NotFoundException('network', $id);
                } else {
                    throw new NotFoundException('container', $container);
                }
            }

            if (strpos($e->getMessage(), '403') !== false) {
                throw new OperationFailedException(
                    'connect',
                    'network',
                    $id,
                    'Failed to connect container to network: operation not supported',
                    0,
                    $e
                );
            }

            if (strpos($e->getMessage(), '409') !== false) {
                throw new OperationFailedException(
                    'connect',
                    'network',
                    $id,
                    'Failed to connect container to network: container is already connected',
                    0,
                    $e
                );
            }

            throw new OperationFailedException(
                'connect',
                'network',
                $id,
                sprintf('Failed to connect container "%s" to network', $container),
                0,
                $e
            );
        }
    }

    /**
     * Disconnect a container from a network
     *
     * @param string $networkId ID or name of the network
     * @param string $containerId ID or name of the container
     * @param array<string, mixed> $config Additional configuration parameters
     *    - Force: bool - Force disconnection
     * @return bool Operation result
     * @throws MissingRequiredParameterException if required parameters are missing
     * @throws NotFoundException if the network or container is not found
     * @throws OperationFailedException if the operation fails
     */
    public function disconnect(string $networkId, string $containerId, array $config = []): bool
    {
        if (empty($networkId)) {
            throw new MissingRequiredParameterException(
                'networkId',
                'Network ID cannot be empty'
            );
        }

        if (empty($containerId)) {
            throw new MissingRequiredParameterException(
                'containerId',
                'Container ID cannot be empty'
            );
        }

        try {
            $this->post("/networks/{$networkId}/disconnect", [
                'json' => [
                    'Container' => $containerId,
                    'Force' => false,
                ],
            ]);

            return true;
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), '404') !== false) {
                // Check if it's the network or container that wasn't found
                if (strpos($e->getMessage(), 'network') !== false) {
                    throw new NotFoundException('network', $networkId);
                } else {
                    throw new NotFoundException('container', $containerId);
                }
            }

            if (strpos($e->getMessage(), '403') !== false) {
                throw new OperationFailedException(
                    'disconnect',
                    'network',
                    $networkId,
                    'Failed to disconnect container from network: operation not supported',
                    0,
                    $e
                );
            }

            if (strpos($e->getMessage(), '409') !== false) {
                throw new OperationFailedException(
                    'disconnect',
                    'network',
                    $networkId,
                    'Failed to disconnect container from network: container is not connected',
                    0,
                    $e
                );
            }

            throw new OperationFailedException(
                'disconnect',
                'network',
                $networkId,
                sprintf('Failed to disconnect container "%s" from network', $containerId),
                0,
                $e
            );
        }
    }

    /**
     * Remove a network
     *
     * @param string $id Network ID or name
     * @return bool Removal result
     * @throws MissingRequiredParameterException if network ID is empty
     * @throws NotFoundException if the network is not found
     * @throws OperationFailedException if the removal fails
     */
    public function remove(string $id): bool
    {
        if (empty($id)) {
            throw new MissingRequiredParameterException(
                'id',
                'Network ID cannot be empty'
            );
        }

        try {
            $this->delete("/networks/{$id}");

            return true;
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), '404') !== false) {
                throw new NotFoundException('network', $id);
            }

            if (strpos($e->getMessage(), '409') !== false) {
                throw new OperationFailedException(
                    'remove',
                    'network',
                    $id,
                    'Failed to remove network: network has active endpoints',
                    0,
                    $e
                );
            }

            throw new OperationFailedException(
                'remove',
                'network',
                $id,
                'Failed to remove network',
                0,
                $e
            );
        }
    }

    /**
     * Delete unused networks
     *
     * @param array<string, mixed> $filters Filters to apply
     *    - until: string - Only prune networks created before given timestamp
     *    - label: string - Only prune networks with matching labels
     * @return array<string, mixed> Prune result
     * @throws InvalidParameterValueException if invalid filters are provided
     * @throws OperationFailedException if the operation fails
     */
    public function prune(array $filters = []): array
    {
        // Validate filters
        $allowedFilters = ['until', 'label'];
        foreach (array_keys($filters) as $filter) {
            if (! in_array($filter, $allowedFilters)) {
                throw new InvalidParameterValueException(
                    'filters',
                    $filters,
                    implode(', ', $allowedFilters),
                    sprintf(
                        'Filter "%s" is not supported for the prune method. Allowed filters: %s',
                        $filter,
                        implode(', ', $allowedFilters)
                    )
                );
            }
        }

        try {
            return $this->post('/networks/prune', ['query' => ['filters' => json_encode($filters)]]);
        } catch (\Exception $e) {
            throw new OperationFailedException(
                'prune',
                'networks',
                '',
                'Failed to prune unused networks',
                0,
                $e
            );
        }
    }

    /**
     * Check if a network exists
     *
     * @param string $id Network ID or name
     * @return bool Whether the network exists
     * @throws MissingRequiredParameterException if network ID is empty
     */
    public function exists(string $id): bool
    {
        if (empty($id)) {
            throw new MissingRequiredParameterException(
                'id',
                'Network ID cannot be empty'
            );
        }

        try {
            $response = $this->inspect($id);

            return isset($response['Id']);
        } catch (\Exception $e) {
            return false;
        }
    }
}
