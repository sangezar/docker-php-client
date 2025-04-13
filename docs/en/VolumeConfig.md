# VolumeConfig

The `VolumeConfig` class is part of the `Sangezar\DockerClient\Config` namespace and provides a convenient interface for configuring and creating Docker volumes.

## Namespace

```php
namespace Sangezar\DockerClient\Config;
```

## Description

The `VolumeConfig` class allows you to create and configure Docker volume configurations that can be used by the API client to create new volumes.

## Creating an Instance

```php
$volumeConfig = VolumeConfig::create();
```

## Methods

### create(): self

Static method for creating a new volume configuration instance.

```php
$volumeConfig = VolumeConfig::create();
```

### setName(string $name): self

Sets the volume name.

**Parameters:**
- `$name` - Volume name

**Exceptions:**
- `InvalidParameterValueException` - if the volume name is empty or invalid.

```php
$volumeConfig->setName('my-volume');
```

### setDriver(string $driver): self

Sets the volume driver.

**Parameters:**
- `$driver` - Volume driver (e.g., 'local', 'nfs', 'cifs', etc.)

**Exceptions:**
- `InvalidParameterValueException` - if the driver parameter is empty.

```php
$volumeConfig->setDriver('local');
```

### addDriverOpt(string $key, string $value): self

Adds a driver option.

**Parameters:**
- `$key` - Option key
- `$value` - Option value

```php
$volumeConfig->addDriverOpt('type', 'nfs');
$volumeConfig->addDriverOpt('device', ':/path/to/dir');
$volumeConfig->addDriverOpt('o', 'addr=192.168.1.1,rw');
```

### setupNfs(string $serverAddress, string $serverPath, string $options = 'addr={server},rw'): self

Configures an NFS volume. Automatically sets the driver to 'local' and adds the appropriate options.

**Parameters:**
- `$serverAddress` - IP address or hostname of the NFS server
- `$serverPath` - Path on the NFS server to mount
- `$options` - Mounting options that will be added to the parameters string

**Exceptions:**
- `InvalidParameterValueException` - if the server address or path is empty.

```php
$volumeConfig->setupNfs('192.168.1.100', '/exports/data');
```

### addLabel(string $key, string $value): self

Adds a label to the volume.

**Parameters:**
- `$key` - Label key
- `$value` - Label value

```php
$volumeConfig->addLabel('environment', 'production');
$volumeConfig->addLabel('backup', 'weekly');
```

### toArray(): array

Converts the configuration to an array for the Docker API.

**Exceptions:**
- `InvalidConfigurationException` - if the configuration is invalid.

**Returns:**
- `array<string, mixed>` - Configuration array for the Docker API.

```php
$configArray = $volumeConfig->toArray();
```

## Usage Examples

### Basic Local Volume

```php
use Sangezar\DockerClient\Config\VolumeConfig;

// Creating a volume configuration
$volumeConfig = VolumeConfig::create()
    ->setName('app-data')
    ->setDriver('local')
    ->addLabel('application', 'my-app')
    ->addLabel('environment', 'development');

// Creating a volume using the API client
$dockerClient->volumes()->create($volumeConfig);
```

### Configuring an NFS Volume

```php
use Sangezar\DockerClient\Config\VolumeConfig;

// Creating an NFS volume
$volumeConfig = VolumeConfig::create()
    ->setName('shared-data')
    ->setupNfs('192.168.1.100', '/exports/shared', 'addr={server},rw,nolock,soft')
    ->addLabel('type', 'shared-storage');

// Creating a volume using the API client
$dockerClient->volumes()->create($volumeConfig);
```

### Advanced Options

```php
use Sangezar\DockerClient\Config\VolumeConfig;

// Creating a volume with advanced options
$volumeConfig = VolumeConfig::create()
    ->setName('database-data')
    ->setDriver('local')
    ->addDriverOpt('type', 'tmpfs')
    ->addDriverOpt('device', 'tmpfs')
    ->addDriverOpt('o', 'size=100m,uid=1000')
    ->addLabel('service', 'database')
    ->addLabel('backup', 'hourly');

// Converting to an array for the Docker API
$configArray = $volumeConfig->toArray();

// Creating a volume using the API client
$dockerClient->volumes()->create($volumeConfig);
``` 