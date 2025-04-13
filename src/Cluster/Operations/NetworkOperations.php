<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Cluster\Operations;

use Sangezar\DockerClient\Config\NetworkConfig;
use Sangezar\DockerClient\Exception\NotFoundException;
use Sangezar\DockerClient\Exception\Validation\InvalidParameterValueException;
use Sangezar\DockerClient\Exception\Validation\MissingRequiredParameterException;

/**
 * Docker network operations on all cluster nodes
 */
class NetworkOperations extends AbstractOperations
{
    /**
     * Gets a list of networks from all cluster nodes
     *
     * @param array<string, mixed> $parameters Filtering parameters
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws InvalidParameterValueException if invalid filtering parameters are provided
     */
    public function list(array $parameters = []): array
    {
        // Validation of filters
        if (! empty($parameters) && ! is_array($parameters)) {
            throw new InvalidParameterValueException(
                'parameters',
                $parameters,
                'array',
                'Parameters must be an array'
            );
        }

        return $this->executeOnAll(
            fn ($client) => $client->network()->list($parameters)
        );
    }

    /**
     * Creates a network on all cluster nodes
     *
     * @param string|NetworkConfig $nameOrConfig Network name or network configuration
     * @param array<string, mixed> $parameters Additional parameters (used only if first parameter is a string)
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws MissingRequiredParameterException if network name is empty
     * @throws InvalidParameterValueException if additional parameters are invalid
     */
    public function create($nameOrConfig, array $parameters = []): array
    {
        if ($nameOrConfig instanceof NetworkConfig) {
            // Convert configuration to array
            $parameters = $nameOrConfig->toArray();
            $name = $parameters['Name'] ?? '';

            if (empty($name)) {
                throw new MissingRequiredParameterException(
                    'name',
                    'Network name cannot be empty in NetworkConfig'
                );
            }
        } else {
            $name = $nameOrConfig;
            if (empty($name)) {
                throw new MissingRequiredParameterException(
                    'name',
                    'Network name cannot be empty'
                );
            }
        }

        return $this->executeOnAll(
            fn ($client) => $client->network()->create($name, $parameters)
        );
    }

    /**
     * Gets detailed information about a network on all cluster nodes
     *
     * @param string $id Network identifier
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws MissingRequiredParameterException if network identifier is empty
     */
    public function inspect(string $id): array
    {
        if (empty($id)) {
            throw new MissingRequiredParameterException(
                'id',
                'Network ID or name cannot be empty'
            );
        }

        return $this->executeOnAll(
            function ($client) use ($id) {
                try {
                    return $client->network()->inspect($id);
                } catch (NotFoundException $e) {
                    // Return structured result for "not found" error
                    return [
                        'error' => true,
                        'errorType' => 'not_found',
                        'message' => sprintf('Network "%s" not found on this node', $id),
                    ];
                } catch (\Throwable $e) {
                    throw $e; // Re-throw other errors for handling in executeOnAll
                }
            }
        );
    }

    /**
     * Connects a container to a network on all cluster nodes
     *
     * @param string $id Network identifier
     * @param string $container Container identifier
     * @param array<string, mixed> $parameters Additional parameters
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws MissingRequiredParameterException if network or container identifier is empty
     * @throws InvalidParameterValueException if additional parameters are invalid
     */
    public function connect(string $id, string $container, array $parameters = []): array
    {
        if (empty($id)) {
            throw new MissingRequiredParameterException(
                'id',
                'Network ID or name cannot be empty'
            );
        }

        if (empty($container)) {
            throw new MissingRequiredParameterException(
                'container',
                'Container ID or name cannot be empty'
            );
        }

        return $this->executeOnAll(
            function ($client) use ($id, $container, $parameters) {
                try {
                    return $client->network()->connect($id, $container, $parameters);
                } catch (NotFoundException $e) {
                    // Return structured result for "not found" error
                    return [
                        'error' => true,
                        'errorType' => 'not_found',
                        'message' => sprintf('Network "%s" or container "%s" not found on this node', $id, $container),
                    ];
                } catch (\Throwable $e) {
                    throw $e; // Re-throw other errors for handling in executeOnAll
                }
            }
        );
    }

    /**
     * Disconnects a container from a network on all cluster nodes
     *
     * @param string $id Network identifier
     * @param string $container Container identifier
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws MissingRequiredParameterException if network or container identifier is empty
     */
    public function disconnect(string $id, string $container): array
    {
        if (empty($id)) {
            throw new MissingRequiredParameterException(
                'id',
                'Network ID or name cannot be empty'
            );
        }

        if (empty($container)) {
            throw new MissingRequiredParameterException(
                'container',
                'Container ID or name cannot be empty'
            );
        }

        return $this->executeOnAll(
            function ($client) use ($id, $container) {
                try {
                    return $client->network()->disconnect($id, $container);
                } catch (NotFoundException $e) {
                    // Return structured result for "not found" error
                    return [
                        'error' => true,
                        'errorType' => 'not_found',
                        'message' => sprintf('Network "%s" or container "%s" not found on this node', $id, $container),
                    ];
                } catch (\Throwable $e) {
                    throw $e; // Re-throw other errors for handling in executeOnAll
                }
            }
        );
    }

    /**
     * Removes a network on all cluster nodes
     *
     * @param string $id Network identifier
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws MissingRequiredParameterException if network identifier is empty
     */
    public function remove(string $id): array
    {
        if (empty($id)) {
            throw new MissingRequiredParameterException(
                'id',
                'Network ID or name cannot be empty'
            );
        }

        return $this->executeOnAll(
            function ($client) use ($id) {
                try {
                    return $client->network()->remove($id);
                } catch (NotFoundException $e) {
                    // Return structured result for "not found" error
                    return [
                        'error' => true,
                        'errorType' => 'not_found',
                        'message' => sprintf('Network "%s" not found on this node', $id),
                    ];
                } catch (\Throwable $e) {
                    throw $e; // Re-throw other errors for handling in executeOnAll
                }
            }
        );
    }

    /**
     * Removes unused networks on all cluster nodes
     *
     * @param array<string, mixed> $parameters Filtering parameters
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws InvalidParameterValueException if invalid filtering parameters are provided
     */
    public function prune(array $parameters = []): array
    {
        // Validation of filters
        if (! empty($parameters) && ! is_array($parameters)) {
            throw new InvalidParameterValueException(
                'parameters',
                $parameters,
                'array',
                'Parameters must be an array'
            );
        }

        return $this->executeOnAll(
            fn ($client) => $client->network()->prune($parameters)
        );
    }

    /**
     * Checks if a network exists on all cluster nodes
     *
     * @param string $id Network identifier
     * @return array<string, array<string, mixed>|bool> Whether the network exists on each node
     * @throws MissingRequiredParameterException if network identifier is empty
     */
    public function exists(string $id): array
    {
        if (empty($id)) {
            throw new MissingRequiredParameterException(
                'id',
                'Network ID or name cannot be empty'
            );
        }

        return $this->executeOnAll(
            fn ($client) => $client->network()->exists($id)
        );
    }

    /**
     * Checks if a network with the specified ID exists on all cluster nodes
     *
     * @param string $id Network ID or name
     * @return bool True if the network exists on all nodes, false if it's missing on at least one
     * @throws MissingRequiredParameterException if network ID is empty
     */
    public function existsOnAllNodes(string $id): bool
    {
        if (empty($id)) {
            throw new MissingRequiredParameterException(
                'id',
                'Network ID or name cannot be empty'
            );
        }

        $results = $this->exists($id);

        // Check if all results = true
        foreach ($results as $nodeResult) {
            if ($nodeResult !== true) {
                return false;
            }
        }

        return true;
    }

    /**
     * Gets a list of nodes where a network with the specified ID exists
     *
     * @param string $id Network ID or name
     * @return array<string> Array of node names where the network is found
     * @throws MissingRequiredParameterException if network ID is empty
     */
    public function getNodesWithNetwork(string $id): array
    {
        if (empty($id)) {
            throw new MissingRequiredParameterException(
                'id',
                'Network ID or name cannot be empty'
            );
        }

        $results = $this->exists($id);
        $nodesWithNetwork = [];

        foreach ($results as $nodeName => $exists) {
            if ($exists === true) {
                $nodesWithNetwork[] = $nodeName;
            }
        }

        return $nodesWithNetwork;
    }
}
