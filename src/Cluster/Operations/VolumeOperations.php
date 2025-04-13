<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Cluster\Operations;

use Sangezar\DockerClient\Config\VolumeConfig;
use Sangezar\DockerClient\Exception\NotFoundException;
use Sangezar\DockerClient\Exception\OperationFailedException;
use Sangezar\DockerClient\Exception\Validation\InvalidParameterValueException;
use Sangezar\DockerClient\Exception\Validation\MissingRequiredParameterException;

/**
 * Docker volume operations on all cluster nodes
 */
class VolumeOperations extends AbstractOperations
{
    /**
     * Gets a list of volumes from all cluster nodes
     *
     * @param array<string, mixed> $parameters Filtering parameters
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws InvalidParameterValueException if invalid filtering parameters are provided
     */
    public function list(array $parameters = []): array
    {
        // Validate filters
        if (! empty($parameters) && ! is_array($parameters)) {
            throw new InvalidParameterValueException(
                'parameters',
                $parameters,
                'array',
                'Parameters must be an array'
            );
        }

        return $this->executeOnAll(
            fn ($client) => $client->volume()->list($parameters)
        );
    }

    /**
     * Creates a volume on all cluster nodes
     *
     * @param string|VolumeConfig $nameOrConfig Volume name or volume configuration
     * @param array<string, mixed> $parameters Additional parameters (used only if first parameter is a string)
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws MissingRequiredParameterException if volume name is empty
     * @throws InvalidParameterValueException if additional parameters are invalid
     */
    public function create($nameOrConfig, array $parameters = []): array
    {
        if ($nameOrConfig instanceof VolumeConfig) {
            // Convert configuration to array
            $parameters = $nameOrConfig->toArray();
            $name = $parameters['Name'] ?? '';

            if (empty($name)) {
                throw new MissingRequiredParameterException(
                    'name',
                    'Volume name cannot be empty in VolumeConfig'
                );
            }
        } else {
            $name = $nameOrConfig;
            if (empty($name)) {
                throw new MissingRequiredParameterException(
                    'name',
                    'Volume name cannot be empty'
                );
            }
        }

        // Validate additional parameters
        if (! empty($parameters) && ! is_array($parameters)) {
            throw new InvalidParameterValueException(
                'parameters',
                $parameters,
                'array',
                'Additional parameters must be an array'
            );
        }

        return $this->executeOnAll(
            function ($client) use ($name, $parameters) {
                try {
                    return $client->volume()->create($name, $parameters);
                } catch (\Throwable $e) {
                    // Create structured error for return
                    $configArray = array_merge(['Name' => $name], $parameters);
                    $name = is_string($name) ? $name : null;
                    $volumeName = is_string($configArray['Name']) ? $configArray['Name'] : $name;

                    throw new OperationFailedException(
                        'create',
                        'volume',
                        $volumeName,
                        'Failed to create volume: ' . $e->getMessage(),
                        0,
                        $e
                    );
                }
            }
        );
    }

    /**
     * Gets detailed information about a volume on all cluster nodes
     *
     * @param string $name Volume name
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws MissingRequiredParameterException if volume name is empty
     */
    public function inspect(string $name): array
    {
        if (empty($name)) {
            throw new MissingRequiredParameterException(
                'name',
                'Volume name cannot be empty'
            );
        }

        return $this->executeOnAll(
            function ($client) use ($name) {
                try {
                    return $client->volume()->inspect($name);
                } catch (NotFoundException $e) {
                    // Return structured result for "not found" error
                    return [
                        'error' => true,
                        'errorType' => 'not_found',
                        'message' => sprintf('Volume "%s" not found on this node', $name),
                    ];
                } catch (\Throwable $e) {
                    throw $e; // Re-throw other errors for handling in executeOnAll
                }
            }
        );
    }

    /**
     * Removes a volume on all cluster nodes
     *
     * @param string $name Volume name
     * @param bool $force Remove the volume even if it's in use
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws MissingRequiredParameterException if volume name is empty
     */
    public function remove(string $name, bool $force = false): array
    {
        if (empty($name)) {
            throw new MissingRequiredParameterException(
                'name',
                'Volume name cannot be empty'
            );
        }

        return $this->executeOnAll(
            function ($client) use ($name, $force) {
                try {
                    return $client->volume()->remove($name, $force);
                } catch (NotFoundException $e) {
                    // Return structured result for "not found" error
                    return [
                        'error' => true,
                        'errorType' => 'not_found',
                        'message' => sprintf('Volume "%s" not found on this node', $name),
                    ];
                } catch (\Throwable $e) {
                    throw $e; // Re-throw other errors for handling in executeOnAll
                }
            }
        );
    }

    /**
     * Removes unused volumes on all cluster nodes
     *
     * @param array<string, mixed> $parameters Filtering parameters
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws InvalidParameterValueException if invalid filtering parameters are provided
     */
    public function prune(array $parameters = []): array
    {
        // Validate filters
        if (! empty($parameters) && ! is_array($parameters)) {
            throw new InvalidParameterValueException(
                'parameters',
                $parameters,
                'array',
                'Parameters must be an array'
            );
        }

        return $this->executeOnAll(
            fn ($client) => $client->volume()->prune($parameters)
        );
    }

    /**
     * Checks if a volume exists on all cluster nodes
     *
     * @param string $name Volume name
     * @return array<string, array<string, mixed>|bool> Whether the volume exists on each node
     * @throws MissingRequiredParameterException if volume name is empty
     */
    public function exists(string $name): array
    {
        if (empty($name)) {
            throw new MissingRequiredParameterException(
                'name',
                'Volume name cannot be empty'
            );
        }

        return $this->executeOnAll(
            fn ($client) => $client->volume()->exists($name)
        );
    }

    /**
     * Checks if a volume with the specified name exists on all cluster nodes
     *
     * @param string $name Volume name
     * @return bool True if the volume exists on all nodes, false if it's missing on at least one
     * @throws MissingRequiredParameterException if volume name is empty
     */
    public function existsOnAllNodes(string $name): bool
    {
        if (empty($name)) {
            throw new MissingRequiredParameterException(
                'name',
                'Volume name cannot be empty'
            );
        }

        $results = $this->exists($name);

        // Check if all results = true
        foreach ($results as $nodeResult) {
            if ($nodeResult !== true) {
                return false;
            }
        }

        return true;
    }

    /**
     * Gets a list of nodes where a volume with the specified name exists
     *
     * @param string $name Volume name
     * @return array<string> Array of node names where the volume is found
     * @throws MissingRequiredParameterException if volume name is empty
     */
    public function getNodesWithVolume(string $name): array
    {
        if (empty($name)) {
            throw new MissingRequiredParameterException(
                'name',
                'Volume name cannot be empty'
            );
        }

        $results = $this->exists($name);
        $nodesWithVolume = [];

        foreach ($results as $nodeName => $exists) {
            if ($exists === true) {
                $nodesWithVolume[] = $nodeName;
            }
        }

        return $nodesWithVolume;
    }
}
