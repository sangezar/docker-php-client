<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Config;

use Sangezar\DockerClient\Api\Interface\VolumeInterface;
use Sangezar\DockerClient\Exception\Validation\InvalidConfigurationException;
use Sangezar\DockerClient\Exception\Validation\InvalidParameterValueException;

/**
 * Docker volume configuration class
 */
class VolumeConfig
{
    private ?string $name = null;
    private string $driver = VolumeInterface::DRIVER_LOCAL;
    /** @var array<string, string> */
    private array $driverOpts = [];
    /** @var array<string, string> */
    private array $labels = [];

    /**
     * Creates a new volume configuration instance
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Sets the volume name
     *
     * @param string $name Volume name
     * @return $this
     * @throws InvalidParameterValueException if name is invalid
     */
    public function setName(string $name): self
    {
        if (empty($name)) {
            throw new InvalidParameterValueException(
                'name',
                $name,
                'non-empty string',
                'Volume name cannot be empty'
            );
        }

        // Validation of volume name (letters, numbers, underscores, hyphens, dots)
        if (! preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_.-]+$/', $name)) {
            throw new InvalidParameterValueException(
                'name',
                $name,
                'letters, numbers, ._- (first character must be a letter or number)',
                'Volume name contains invalid characters or has incorrect format'
            );
        }

        $this->name = $name;

        return $this;
    }

    /**
     * Sets the volume driver
     *
     * @param string $driver Volume driver
     * @return $this
     * @throws InvalidParameterValueException if driver is invalid
     */
    public function setDriver(string $driver): self
    {
        $allowedDrivers = [
            VolumeInterface::DRIVER_LOCAL,
            VolumeInterface::DRIVER_NFS,
            VolumeInterface::DRIVER_TMPFS,
            VolumeInterface::DRIVER_CIFS,
        ];

        if (! in_array($driver, $allowedDrivers)) {
            throw new InvalidParameterValueException(
                'driver',
                $driver,
                implode(', ', $allowedDrivers),
                'Unknown volume driver. Allowed drivers: ' . implode(', ', $allowedDrivers)
            );
        }

        $this->driver = $driver;

        return $this;
    }

    /**
     * Adds a driver option
     *
     * @param string $key Option key
     * @param string $value Option value
     * @return $this
     */
    public function addDriverOpt(string $key, string $value): self
    {
        $this->driverOpts[$key] = $value;

        return $this;
    }

    /**
     * Configures the volume for NFS usage
     *
     * @param string $serverAddress NFS server IP address
     * @param string $remotePath Path on the NFS server
     * @param array<string|int, string> $options Additional mounting options
     * @return $this
     */
    public function setupNfs(string $serverAddress, string $remotePath, array $options = []): self
    {
        // Check IP address or host format
        if (empty($serverAddress)) {
            throw new InvalidParameterValueException(
                'serverAddress',
                $serverAddress,
                'valid IP address or hostname',
                'NFS server address cannot be empty'
            );
        }

        // Check path on remote server
        if (empty($remotePath)) {
            throw new InvalidParameterValueException(
                'remotePath',
                $remotePath,
                'valid path',
                'Path on NFS server cannot be empty'
            );
        }

        // Set NFS driver
        $this->driver = VolumeInterface::DRIVER_NFS;

        // Create base NFS options
        $device = $serverAddress . ':' . $remotePath;
        $this->driverOpts['type'] = 'nfs';
        $this->driverOpts['device'] = $device;

        // Configure mount options (e.g.: addr=server,rw,nolock,soft)
        $mountOptions = [];
        if (! empty($options)) {
            foreach ($options as $key => $value) {
                if (is_numeric($key)) {
                    $mountOptions[] = $value; // simple parameter without value
                } else {
                    $mountOptions[] = $key . '=' . $value; // parameter=value
                }
            }
        }

        // Add server address if not specified in options
        if (! isset($options['addr'])) {
            array_unshift($mountOptions, 'addr=' . $serverAddress);
        }

        // Set mount options
        if (! empty($mountOptions)) {
            $this->driverOpts['o'] = implode(',', $mountOptions);
        }

        return $this;
    }

    /**
     * Adds a label
     *
     * @param string $key Label key
     * @param string $value Label value
     * @return $this
     */
    public function addLabel(string $key, string $value): self
    {
        $this->labels[$key] = $value;

        return $this;
    }

    /**
     * Converts configuration to array for Docker API
     *
     * @return array<string, mixed>
     * @throws InvalidConfigurationException if configuration is invalid
     */
    public function toArray(): array
    {
        if (empty($this->name)) {
            throw new InvalidConfigurationException(
                'VolumeConfig',
                ['Volume name (Name) is a required parameter']
            );
        }

        $config = [
            'Name' => $this->name,
            'Driver' => $this->driver,
        ];

        if (! empty($this->driverOpts)) {
            $config['DriverOpts'] = $this->driverOpts;
        }

        if (! empty($this->labels)) {
            $config['Labels'] = $this->labels;
        }

        return $config;
    }
}
