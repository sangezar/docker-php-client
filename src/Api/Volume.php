<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Api;

use Sangezar\DockerClient\Api\Interface\VolumeInterface;
use Sangezar\DockerClient\Config\VolumeConfig;
use Sangezar\DockerClient\Exception\NotFoundException;
use Sangezar\DockerClient\Exception\OperationFailedException;
use Sangezar\DockerClient\Exception\Validation\InvalidParameterValueException;
use Sangezar\DockerClient\Exception\Validation\MissingRequiredParameterException;

/**
 * Docker Volume API client
 */
class Volume extends AbstractApi implements VolumeInterface
{
    /**
     * Constants for volume driver types
     */
    public const DRIVER_LOCAL = 'local';
    public const DRIVER_NFS = 'nfs';
    public const DRIVER_TMPFS = 'tmpfs';
    public const DRIVER_BTRFS = 'btrfs';
    public const DRIVER_VIEUX_BRIDGE = 'vieux-bridge';
    public const DRIVER_VFS = 'vfs';
    public const DRIVER_CIFS = 'cifs';

    /**
     * Constants for driver options
     */
    public const OPT_TYPE = 'type';
    public const OPT_DEVICE = 'device';
    public const OPT_O = 'o';
    public const OPT_SIZE = 'size';

    /**
     * List volumes
     *
     * @param array<string, mixed> $filters Filters to apply as JSON or array
     *    - driver: string - Filter by volume driver
     *    - label: string - Filter by volume label
     *    - name: string - Filter by volume name
     *    - dangling: bool - Filter volumes that are dangling (not referenced by any container)
     * @return array<string, mixed> List of volumes
     * @throws InvalidParameterValueException if invalid filters are provided
     */
    public function list(array $filters = []): array
    {
        // Validate filters
        $allowedFilters = ['driver', 'label', 'name', 'dangling'];
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

        // Validate dangling filter if present
        if (isset($filters['dangling']) && ! is_bool($filters['dangling'])) {
            throw new InvalidParameterValueException(
                'dangling',
                $filters['dangling'],
                'true or false',
                'The "dangling" filter value must be boolean'
            );
        }

        return $this->get('/volumes', ['query' => ['filters' => json_encode($filters)]]);
    }

    /**
     * Create a volume
     *
     * @param VolumeConfig $config Volume configuration
     * @return array<string, mixed> Created volume details
     * @throws OperationFailedException if the creation fails
     */
    public function create(VolumeConfig $config): array
    {
        $name = 'unnamed';

        try {
            $configArray = $config->toArray();
            if (isset($configArray['Name'])) {
                $name = $configArray['Name'];
            }

            return $this->post('/volumes/create', ['json' => $configArray]);
        } catch (\Exception $e) {
            throw new OperationFailedException(
                'create',
                'volume',
                $name,
                'Failed to create volume: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Inspect a volume
     *
     * @param string $name Volume name
     * @return array<string, mixed> Volume details
     * @throws MissingRequiredParameterException if volume name is empty
     * @throws NotFoundException if the volume is not found
     */
    public function inspect(string $name): array
    {
        if (empty($name)) {
            throw new MissingRequiredParameterException(
                'name',
                'Volume name cannot be empty'
            );
        }

        try {
            return $this->get("/volumes/{$name}");
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), '404') !== false) {
                throw new NotFoundException('volume', $name);
            }

            throw $e;
        }
    }

    /**
     * Remove a volume
     *
     * @param string $name Volume name
     * @param bool $force Force removal of the volume even if it's in use
     * @return bool Removal result
     * @throws MissingRequiredParameterException if volume name is empty
     * @throws NotFoundException if the volume is not found
     * @throws OperationFailedException if the removal fails
     */
    public function remove(string $name, bool $force = false): bool
    {
        if (empty($name)) {
            throw new MissingRequiredParameterException(
                'name',
                'Volume name cannot be empty'
            );
        }

        try {
            $this->delete("/volumes/{$name}", [
                'query' => ['force' => $force],
            ]);

            return true;
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), '404') !== false) {
                throw new NotFoundException('volume', $name);
            }

            if (strpos($e->getMessage(), '409') !== false) {
                throw new OperationFailedException(
                    'remove',
                    'volume',
                    $name,
                    'Failed to remove volume: volume is in use',
                    0,
                    $e
                );
            }

            throw new OperationFailedException(
                'remove',
                'volume',
                $name,
                'Failed to remove volume',
                0,
                $e
            );
        }
    }

    /**
     * Delete unused volumes
     *
     * @param array<string, mixed> $filters Filters to select volumes for pruning
     *    - label: string - Only remove volumes with matching labels
     *    - all: bool - Remove all unused volumes, not just anonymous ones
     * @return array<string, mixed> Pruning result with information about volumes removed
     * @throws InvalidParameterValueException if invalid filters are provided
     * @throws OperationFailedException if the pruning fails
     */
    public function prune(array $filters = []): array
    {
        // Validate filters
        $allowedFilters = ['label', 'all'];
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

        // Validate all filter if present
        if (isset($filters['all']) && ! is_bool($filters['all'])) {
            throw new InvalidParameterValueException(
                'all',
                $filters['all'],
                'true or false',
                'The "all" filter value must be boolean'
            );
        }

        try {
            return $this->post('/volumes/prune', ['query' => ['filters' => json_encode($filters)]]);
        } catch (\Exception $e) {
            throw new OperationFailedException(
                'prune',
                'volumes',
                '',
                'Failed to prune unused volumes',
                0,
                $e
            );
        }
    }

    /**
     * Check if a volume exists
     *
     * @param string $name Volume name
     * @return bool Whether the volume exists
     * @throws MissingRequiredParameterException if volume name is empty
     */
    public function exists(string $name): bool
    {
        if (empty($name)) {
            throw new MissingRequiredParameterException(
                'name',
                'Volume name cannot be empty'
            );
        }

        try {
            $response = $this->inspect($name);

            return isset($response['Name']);
        } catch (\Exception $e) {
            return false;
        }
    }
}
