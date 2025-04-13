# NetworkConfig

The `NetworkConfig` class is part of the `Sangezar\DockerClient\Config` namespace and provides a convenient interface for configuring Docker networks.

## Namespace

```php
namespace Sangezar\DockerClient\Config;
```

## Description

The `NetworkConfig` class allows you to create and configure Docker network configurations that can be used by the API client to create new networks.

## Creating an Instance

```php
$networkConfig = NetworkConfig::create();
```

## Methods

### create(): self

Static method for creating a new network configuration instance.

```php
$networkConfig = NetworkConfig::create();
```

### setName(string $name): self

Sets the network name.

**Parameters:**
- `$name` - Network name

**Exceptions:**
- `InvalidParameterValueException` - if the network name is empty or invalid.

```php
$networkConfig->setName('my-network');
```

### setDriver(string $driver): self

Sets the network driver.

**Parameters:**
- `$driver` - Network driver. Valid values:
  - `bridge` - standard bridge
  - `host` - uses the host's network stack
  - `overlay` - overlay network
  - `macvlan` - MAC VLAN network
  - `ipvlan` - IP VLAN network
  - `none` - no network

**Exceptions:**
- `InvalidParameterValueException` - if the driver is unknown.

```php
$networkConfig->setDriver('bridge');
```

### setEnableIPv6(bool $enable = true): self

Enables or disables IPv6 support.

**Parameters:**
- `$enable` - `true` to enable IPv6, `false` to disable.

```php
$networkConfig->setEnableIPv6(true);
```

### setInternal(bool $internal = true): self

Sets whether the network should be internal.

**Parameters:**
- `$internal` - `true` for internal network, `false` for external.

```php
$networkConfig->setInternal(true);
```

### setAttachable(bool $attachable = true): self

Sets whether containers can be attached to the network.

**Parameters:**
- `$attachable` - `true` if containers can join, otherwise `false`.

```php
$networkConfig->setAttachable(true);
```

### setScope(string $scope): self

Sets the network scope.

**Parameters:**
- `$scope` - Network scope. Valid values:
  - `local` - local network
  - `swarm` - swarm network
  - `global` - global network

**Exceptions:**
- `InvalidParameterValueException` - if the scope is unknown.

```php
$networkConfig->setScope('local');
```

### addSubnet(string $subnet, ?string $gateway = null, ?string $ipRange = null): self

Adds a subnet to the IPAM configuration.

**Parameters:**
- `$subnet` - Subnet in CIDR format (e.g., 192.168.0.0/24)
- `$gateway` - Gateway IP address (optional)
- `$ipRange` - IP range in CIDR format (optional)

**Exceptions:**
- `InvalidParameterValueException` - if parameters are invalid.

```php
$networkConfig->addSubnet('192.168.1.0/24', '192.168.1.1');
```

### setIpamDriver(string $driver): self

Sets the IPAM driver.

**Parameters:**
- `$driver` - IPAM driver. Valid values:
  - `default` - standard driver
  - `null` - null driver

**Exceptions:**
- `InvalidParameterValueException` - if the driver is unknown.

```php
$networkConfig->setIpamDriver('default');
```

### addOption(string $key, string $value): self

Adds a driver option.

**Parameters:**
- `$key` - Option key
- `$value` - Option value

```php
$networkConfig->addOption('com.docker.network.bridge.name', 'my-bridge');
```

### addLabel(string $key, string $value): self

Adds a label to the network.

**Parameters:**
- `$key` - Label key
- `$value` - Label value

```php
$networkConfig->addLabel('com.example.description', 'Network for web applications');
```

### toArray(): array

Converts the configuration to an array for the Docker API.

**Exceptions:**
- `InvalidConfigurationException` - if the configuration is invalid.

**Returns:**
- `array<string, mixed>` - Configuration array for the Docker API.

```php
$configArray = $networkConfig->toArray();
```

## Usage Example

```php
use Sangezar\DockerClient\Config\NetworkConfig;

// Creating a network configuration
$networkConfig = NetworkConfig::create()
    ->setName('my-web-network')
    ->setDriver('bridge')
    ->setEnableIPv6(true)
    ->addSubnet('192.168.0.0/24', '192.168.0.1')
    ->addLabel('environment', 'production')
    ->addOption('com.docker.network.bridge.enable_icc', 'true');

// Converting to an array for the Docker API
$configArray = $networkConfig->toArray();

// Creating a network using the API client
$dockerClient->networks()->create($networkConfig);
``` 