<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Api\Interface;

use Sangezar\DockerClient\Config\VolumeConfig;
use Sangezar\DockerClient\Exception\NotFoundException;
use Sangezar\DockerClient\Exception\OperationFailedException;
use Sangezar\DockerClient\Exception\Validation\InvalidConfigurationException;
use Sangezar\DockerClient\Exception\Validation\InvalidParameterValueException;
use Sangezar\DockerClient\Exception\Validation\MissingRequiredParameterException;

/**
 * Interface for working with Docker volumes
 */
interface VolumeInterface extends ApiInterface
{
    /** Constants for volume drivers */
    public const DRIVER_LOCAL = 'local';
    public const DRIVER_NFS = 'nfs';
    public const DRIVER_TMPFS = 'tmpfs';
    public const DRIVER_CIFS = 'cifs';
    public const DRIVER_BTRFS = 'btrfs';
    public const DRIVER_VIEUX_BRIDGE = 'vieux-bridge';
    public const DRIVER_VFS = 'vfs';

    /** Constants for driver options */
    public const OPT_TYPE = 'type';
    public const OPT_DEVICE = 'device';
    public const OPT_O = 'o';
    public const OPT_SIZE = 'size';

    /**
     * Gets a list of volumes
     *
     * @param array<string, mixed> $filters Filters (driver, label, name)
     * @return array<string, mixed> List of volumes
     * @throws InvalidParameterValueException if invalid parameters are provided
     * @throws OperationFailedException if the operation fails
     */
    public function list(array $filters = []): array;

    /**
     * Gets detailed information about a volume
     *
     * @param string $name Volume name
     * @return array<string, mixed> Detailed volume information
     * @throws MissingRequiredParameterException if volume name is empty
     * @throws NotFoundException if volume is not found
     * @throws OperationFailedException if the operation fails
     */
    public function inspect(string $name): array;

    /**
     * Creates a new volume
     *
     * @param VolumeConfig $config Volume configuration
     * @return array<string, mixed> Created volume data
     * @throws InvalidConfigurationException if configuration is invalid
     * @throws OperationFailedException if the operation fails
     */
    public function create(VolumeConfig $config): array;

    /**
     * Removes a volume
     *
     * @param string $name Volume name
     * @param bool $force Force removal
     * @return bool Operation success
     * @throws MissingRequiredParameterException if volume name is empty
     * @throws NotFoundException if volume is not found
     * @throws OperationFailedException if the operation fails
     */
    public function remove(string $name, bool $force = false): bool;

    /**
     * Checks if a volume exists
     *
     * @param string $name Volume name
     * @return bool Whether the volume exists
     * @throws MissingRequiredParameterException if volume name is empty
     */
    public function exists(string $name): bool;

    /**
     * Removes unused volumes
     *
     * @param array<string, mixed> $filters Filters for removal
     * @return array<string, mixed> Operation result
     * @throws InvalidParameterValueException if invalid parameters are provided
     * @throws OperationFailedException if the operation fails
     */
    public function prune(array $filters = []): array;
}
