# Network Class Documentation

## Description
`Network` is a class for working with Docker networks through the API. It provides methods for creating, inspecting, connecting containers, and managing Docker networks.

## Namespace
`Sangezar\DockerClient\Api`

## Inheritance
The `Network` class inherits from `AbstractApi` and implements the `NetworkInterface` interface.

## Constants

### Network Driver Types
```php
public const DRIVER_BRIDGE = 'bridge';
public const DRIVER_HOST = 'host';
public const DRIVER_OVERLAY = 'overlay';
public const DRIVER_MACVLAN = 'macvlan';
public const DRIVER_IPVLAN = 'ipvlan';
public const DRIVER_NONE = 'none';
```

### Constants for Network Creation Parameters
```php
public const SCOPE_LOCAL = 'local';
public const SCOPE_SWARM = 'swarm';
public const SCOPE_GLOBAL = 'global';

public const IPAM_DRIVER_DEFAULT = 'default';
public const IPAM_DRIVER_NULL = 'null';
```

## Methods

### list
```php
public function list(array $filters = []): array
```
Gets a list of networks.

#### Parameters:
- `$filters` - Filters to apply in JSON format or array:
  - `driver` (string) - Filter by network driver
  - `id` (string) - Network ID
  - `label` (string) - Filter by network labels
  - `name` (string) - Network name
  - `scope` (string) - Filter by network scope (swarm, global, or local)
  - `type` (string) - Filter by network type (custom or builtin)

#### Returns:
- Array of networks

#### Exceptions:
- `InvalidParameterValueException` - if invalid filters are passed

### inspect
```php
public function inspect(string $id): array
```
Gets detailed information about a network.

#### Parameters:
- `$id` - Network ID or name

#### Returns:
- Array with detailed information about the network

#### Exceptions:
- `MissingRequiredParameterException` - if the network ID is empty
- `NotFoundException` - if the network is not found

### create
```php
public function create(NetworkConfig $config): array
```
Creates a new network.

#### Parameters:
- `$config` - `NetworkConfig` object with network configuration

#### Returns:
- Array with information about the created network

#### Exceptions:
- `InvalidConfigurationException` - if the configuration is invalid
- `OperationFailedException` - if the operation fails

### connect
```php
public function connect(string $id, string $container, array $config = []): bool
```
Connects a container to a network.

#### Parameters:
- `$id` - Network ID or name
- `$container` - Container ID or name
- `$config` - Connection configuration:
  - `EndpointConfig` (array) - Endpoint configuration
  - `IPAddress` (string) - IPv4 address
  - `IPv6Address` (string) - IPv6 address
  - `Links` (array) - Links to other containers
  - `Aliases` (array) - Names to use in the network

#### Returns:
- `true` if the connection is successful

#### Exceptions:
- `MissingRequiredParameterException` - if required parameters are missing
- `NotFoundException` - if the network or container is not found
- `OperationFailedException` - if the connection fails

### disconnect
```php
public function disconnect(string $networkId, string $containerId, array $config = []): bool
```
Disconnects a container from a network.

#### Parameters:
- `$networkId` - Network ID or name
- `$containerId` - Container ID or name
- `$config` - Disconnection configuration:
  - `Force` (bool) - Force disconnection of the container even if it results in a communication error

#### Returns:
- `true` if the disconnection is successful

#### Exceptions:
- `MissingRequiredParameterException` - if required parameters are missing
- `NotFoundException` - if the network or container is not found
- `OperationFailedException` - if the disconnection fails

### remove
```php
public function remove(string $id): bool
```
Removes a network.

#### Parameters:
- `$id` - Network ID or name

#### Returns:
- `true` if the network was successfully removed

#### Exceptions:
- `MissingRequiredParameterException` - if the network ID is empty
- `NotFoundException` - if the network is not found
- `OperationFailedException` - if removal fails (e.g., network is in use)

### prune
```php
public function prune(array $filters = []): array
```
Removes all unused networks.

#### Parameters:
- `$filters` - Filters for determining networks to clean up:
  - `until` (string) - Remove networks created before the specified time
  - `label` (string) - Remove only networks with specified labels

#### Returns:
- Array with information about removed networks, including freed space

#### Exceptions:
- `InvalidParameterValueException` - if filters are invalid
- `OperationFailedException` - if cleanup fails

### exists
```php
public function exists(string $id): bool
```
Checks if a network exists.

#### Parameters:
- `$id` - Network ID or name

#### Returns:
- `true` if the network exists, `false` otherwise

#### Exceptions:
- `MissingRequiredParameterException` - if the network ID is empty

## Usage Examples

### Getting a List of Networks
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createUnix();
$networks = $client->network()->list();

foreach ($networks as $network) {
    echo "Network: {$network['Name']}, driver: {$network['Driver']}\n";
}
```

### Creating a New Network
```php
use Sangezar\DockerClient\DockerClient;
use Sangezar\DockerClient\Config\NetworkConfig;

$client = DockerClient::createUnix();

$config = new NetworkConfig();
$config->setName('my-network')
       ->setDriver(Network::DRIVER_BRIDGE)
       ->setOptions([
           'com.docker.network.bridge.name' => 'my-bridge'
       ]);

$network = $client->network()->create($config);
echo "Network created with ID: {$network['Id']}\n";
```

### Connecting a Container to a Network
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createUnix();
$networkId = 'my-network';
$containerId = 'my-container';

$client->network()->connect($networkId, $containerId, [
    'Aliases' => ['web-server'],
    'IPAddress' => '172.18.0.10'
]);

echo "Container {$containerId} connected to network {$networkId}\n";
```

### Removing a Network
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createUnix();
$networkId = 'my-network';

if ($client->network()->exists($networkId)) {
    $client->network()->remove($networkId);
    echo "Network {$networkId} removed\n";
}
``` 