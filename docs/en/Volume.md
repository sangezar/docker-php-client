# Volume Class Documentation

## Description
`Volume` is a class for working with Docker volumes through API. It provides methods for creating, inspecting, deleting, and managing Docker volumes.

## Namespace
`Sangezar\DockerClient\Api`

## Inheritance
The `Volume` class inherits from `AbstractApi` and implements the `VolumeInterface` interface.

## Constants

### Volume Driver Types
```php
public const DRIVER_LOCAL = 'local';
public const DRIVER_NFS = 'nfs';
public const DRIVER_TMPFS = 'tmpfs';
public const DRIVER_BTRFS = 'btrfs';
public const DRIVER_VIEUX_BRIDGE = 'vieux-bridge';
public const DRIVER_VFS = 'vfs';
public const DRIVER_CIFS = 'cifs';
```

### Driver Option Constants
```php
public const OPT_TYPE = 'type';
public const OPT_DEVICE = 'device';
public const OPT_O = 'o';
public const OPT_SIZE = 'size';
```

## Methods

### list
```php
public function list(array $filters = []): array
```
Gets a list of volumes.

#### Parameters:
- `$filters` - Filters to apply in JSON format or array:
  - `driver` (string) - Filter by volume driver
  - `label` (string) - Filter by volume label
  - `name` (string) - Filter by volume name
  - `dangling` (bool) - Filter volumes not used by any container

#### Returns:
- Array with information about volumes

#### Exceptions:
- `InvalidParameterValueException` - if invalid filters are passed

### create
```php
public function create(VolumeConfig $config): array
```
Creates a new volume.

#### Parameters:
- `$config` - `VolumeConfig` object with volume configuration

#### Returns:
- Array with information about the created volume

#### Exceptions:
- `OperationFailedException` - if creation failed

### inspect
```php
public function inspect(string $name): array
```
Gets detailed information about a volume.

#### Parameters:
- `$name` - Volume name

#### Returns:
- Array with detailed information about the volume

#### Exceptions:
- `MissingRequiredParameterException` - if the volume name is empty
- `NotFoundException` - if the volume is not found

### remove
```php
public function remove(string $name, bool $force = false): bool
```
Removes a volume.

#### Parameters:
- `$name` - Volume name
- `$force` - Force remove the volume even if it's in use (default `false`)

#### Returns:
- `true` if the volume was successfully removed

#### Exceptions:
- `MissingRequiredParameterException` - if the volume name is empty
- `NotFoundException` - if the volume is not found
- `OperationFailedException` - if removal failed

### prune
```php
public function prune(array $filters = []): array
```
Removes unused volumes.

#### Parameters:
- `$filters` - Filters for selecting volumes to clean up:
  - `label` (string) - Remove only volumes with specified labels
  - `all` (bool) - Remove all unused volumes, not just anonymous ones

#### Returns:
- Array with information about removed volumes, including freed space

#### Exceptions:
- `InvalidParameterValueException` - if filters are invalid
- `OperationFailedException` - if cleanup failed

### exists
```php
public function exists(string $name): bool
```
Checks if a volume exists.

#### Parameters:
- `$name` - Volume name

#### Returns:
- `true` if the volume exists, `false` otherwise

#### Exceptions:
- `MissingRequiredParameterException` - if the volume name is empty

## Usage Examples

### Getting a List of Volumes
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createUnix();
$volumes = $client->volume()->list();

foreach ($volumes['Volumes'] as $volume) {
    echo "Volume: {$volume['Name']}, driver: {$volume['Driver']}\n";
}
```

### Creating a New Volume
```php
use Sangezar\DockerClient\DockerClient;
use Sangezar\DockerClient\Config\VolumeConfig;

$client = DockerClient::createUnix();

$config = new VolumeConfig();
$config->setName('my-volume')
       ->setDriver(Volume::DRIVER_LOCAL)
       ->setLabels([
           'environment' => 'development',
           'application' => 'my-app'
       ]);

$volume = $client->volume()->create($config);
echo "Volume created with name: {$volume['Name']}\n";
```

### Inspecting a Volume
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createUnix();
$volumeName = 'my-volume';

if ($client->volume()->exists($volumeName)) {
    $volumeInfo = $client->volume()->inspect($volumeName);
    echo "Volume: {$volumeInfo['Name']}\n";
    echo "Driver: {$volumeInfo['Driver']}\n";
    echo "Mount point: {$volumeInfo['Mountpoint']}\n";
    
    if (!empty($volumeInfo['Labels'])) {
        echo "Labels:\n";
        foreach ($volumeInfo['Labels'] as $key => $value) {
            echo "  - {$key}: {$value}\n";
        }
    }
}
```

### Removing a Volume
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createUnix();
$volumeName = 'my-volume';

if ($client->volume()->exists($volumeName)) {
    $client->volume()->remove($volumeName);
    echo "Volume {$volumeName} removed\n";
}
```

### Cleaning Up Unused Volumes
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createUnix();

$pruneResult = $client->volume()->prune();
echo "Volumes removed: " . count($pruneResult['VolumesDeleted'] ?? []) . "\n";
echo "Space freed: " . ($pruneResult['SpaceReclaimed'] ?? 0) . " bytes\n";
``` 