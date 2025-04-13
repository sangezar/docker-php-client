<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Config;

use Sangezar\DockerClient\Api\Interface\NetworkInterface;
use Sangezar\DockerClient\Exception\Validation\InvalidConfigurationException;
use Sangezar\DockerClient\Exception\Validation\InvalidParameterValueException;

/**
 * Docker network configuration class
 */
class NetworkConfig
{
    private ?string $name = null;
    private string $driver = NetworkInterface::DRIVER_BRIDGE;
    private bool $enableIPv6 = false;
    private bool $internal = false;
    private bool $attachable = false;
    /** @var array{Driver: string, Config: array<int, array<string, string>>} */
    private array $ipam = [
        'Driver' => NetworkInterface::IPAM_DRIVER_DEFAULT,
        'Config' => [],
    ];
    /** @var array<string, string> */
    private array $options = [];
    /** @var array<string, string> */
    private array $labels = [];
    private ?string $scope = null;

    /**
     * Creates a new network configuration instance
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Sets the network name
     *
     * @param string $name Network name
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
                'Network name cannot be empty'
            );
        }

        // Validation of network name (letters, numbers, underscores, hyphens)
        if (! preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_.-]+$/', $name)) {
            throw new InvalidParameterValueException(
                'name',
                $name,
                'letters, numbers, ._- (first character must be a letter or number)',
                'Network name contains invalid characters or has incorrect format'
            );
        }

        $this->name = $name;

        return $this;
    }

    /**
     * Sets the network driver
     *
     * @param string $driver Network driver
     * @return $this
     * @throws InvalidParameterValueException if driver is invalid
     */
    public function setDriver(string $driver): self
    {
        $allowedDrivers = [
            NetworkInterface::DRIVER_BRIDGE,
            NetworkInterface::DRIVER_HOST,
            NetworkInterface::DRIVER_OVERLAY,
            NetworkInterface::DRIVER_MACVLAN,
            NetworkInterface::DRIVER_IPVLAN,
            NetworkInterface::DRIVER_NONE,
        ];

        if (! in_array($driver, $allowedDrivers)) {
            throw new InvalidParameterValueException(
                'driver',
                $driver,
                implode(', ', $allowedDrivers),
                'Unknown network driver. Allowed drivers: ' . implode(', ', $allowedDrivers)
            );
        }

        $this->driver = $driver;

        return $this;
    }

    /**
     * Sets IPv6 support
     *
     * @param bool $enable Enable IPv6 support
     * @return $this
     */
    public function setEnableIPv6(bool $enable = true): self
    {
        $this->enableIPv6 = $enable;

        return $this;
    }

    /**
     * Sets network as internal
     *
     * @param bool $internal Whether the network is internal
     * @return $this
     */
    public function setInternal(bool $internal = true): self
    {
        $this->internal = $internal;

        return $this;
    }

    /**
     * Sets whether containers can be attached to the network
     *
     * @param bool $attachable Whether containers can be attached
     * @return $this
     */
    public function setAttachable(bool $attachable = true): self
    {
        $this->attachable = $attachable;

        return $this;
    }

    /**
     * Sets the network scope
     *
     * @param string $scope Network scope
     * @return $this
     * @throws InvalidParameterValueException if scope is invalid
     */
    public function setScope(string $scope): self
    {
        $allowedScopes = [
            NetworkInterface::SCOPE_LOCAL,
            NetworkInterface::SCOPE_SWARM,
            NetworkInterface::SCOPE_GLOBAL,
        ];

        if (! in_array($scope, $allowedScopes)) {
            throw new InvalidParameterValueException(
                'scope',
                $scope,
                implode(', ', $allowedScopes),
                'Unknown network scope. Allowed values: ' . implode(', ', $allowedScopes)
            );
        }

        $this->scope = $scope;

        return $this;
    }

    /**
     * Adds a subnet to IPAM configuration
     *
     * @param string $subnet Subnet (CIDR format)
     * @param string|null $gateway Gateway (IP address)
     * @param string|null $ipRange IP range (CIDR format)
     * @return $this
     * @throws InvalidParameterValueException if parameters are invalid
     */
    public function addSubnet(string $subnet, ?string $gateway = null, ?string $ipRange = null): self
    {
        // Validate subnet (CIDR format)
        if (! preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}\/[0-9]{1,2}$/', $subnet)) {
            throw new InvalidParameterValueException(
                'subnet',
                $subnet,
                'CIDR format (e.g. 192.168.0.0/24)',
                'Subnet must be in CIDR format'
            );
        }

        $subnetConfig = ['Subnet' => $subnet];

        // Add gateway if specified
        if ($gateway !== null) {
            // Validate gateway IP address
            if (! filter_var($gateway, FILTER_VALIDATE_IP)) {
                throw new InvalidParameterValueException(
                    'gateway',
                    $gateway,
                    'valid IP address',
                    'Gateway must be a valid IP address'
                );
            }

            $subnetConfig['Gateway'] = $gateway;
        }

        // Add IP range if specified
        if ($ipRange !== null) {
            // Validate IP range (CIDR format)
            if (! preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}\/[0-9]{1,2}$/', $ipRange)) {
                throw new InvalidParameterValueException(
                    'ipRange',
                    $ipRange,
                    'CIDR format (e.g. 192.168.0.0/24)',
                    'IP range must be in CIDR format'
                );
            }

            $subnetConfig['IPRange'] = $ipRange;
        }

        // Add subnet configuration to IPAM
        $this->ipam['Config'][] = $subnetConfig;

        return $this;
    }

    /**
     * Sets the IPAM driver
     *
     * @param string $driver IPAM driver
     * @return $this
     * @throws InvalidParameterValueException if driver is invalid
     */
    public function setIpamDriver(string $driver): self
    {
        $allowedDrivers = [
            NetworkInterface::IPAM_DRIVER_DEFAULT,
            NetworkInterface::IPAM_DRIVER_NULL,
        ];

        if (! in_array($driver, $allowedDrivers)) {
            throw new InvalidParameterValueException(
                'ipamDriver',
                $driver,
                implode(', ', $allowedDrivers),
                'Unknown IPAM driver. Allowed drivers: ' . implode(', ', $allowedDrivers)
            );
        }

        $this->ipam['Driver'] = $driver;

        return $this;
    }

    /**
     * Adds a driver option
     *
     * @param string $key Driver option key
     * @param string $value Driver option value
     * @return $this
     */
    public function addOption(string $key, string $value): self
    {
        $this->options[$key] = $value;

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
                'NetworkConfig',
                ['Network name (Name) is a required parameter']
            );
        }

        $config = [
            'Name' => $this->name,
            'Driver' => $this->driver,
            'EnableIPv6' => $this->enableIPv6,
            'Internal' => $this->internal,
            'Attachable' => $this->attachable,
            'IPAM' => $this->ipam,
        ];

        if (! empty($this->options)) {
            $config['Options'] = $this->options;
        }

        if (! empty($this->labels)) {
            $config['Labels'] = $this->labels;
        }

        if ($this->scope !== null) {
            $config['Scope'] = $this->scope;
        }

        return $config;
    }
}
