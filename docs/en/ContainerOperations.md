# ContainerOperations Class Documentation

## Description
`ContainerOperations` is a class that provides functionality for performing operations with Docker containers on all cluster nodes simultaneously. This class extends `AbstractOperations` and allows you to get a list of containers, create, start, stop, restart, and delete containers on all cluster nodes.

## Namespace
`Sangezar\DockerClient\Cluster\Operations`

## Inheritance
The class extends `AbstractOperations` and inherits all its methods and properties.

## Methods

### list
```php
public function list(array $parameters = []): array
```
Gets a list of containers from all cluster nodes.

#### Parameters:
- `$parameters` - Array of filtering parameters:
  - `all` (bool): Show all containers (not just active ones)
  - `size` (bool): Show container sizes
  - `limit` (int): Limit the number of results
  - `filters` (array): Filters for searching containers

#### Returns:
- Array where keys are node names and values are operation results on each node

#### Exceptions:
- `InvalidParameterValueException` - if invalid filtering parameters are provided

### create
```php
public function create(ContainerConfig $config): array
```
Creates a container on all cluster nodes.

#### Parameters:
- `$config` - Container configuration

#### Returns:
- Array where keys are node names and values are operation results on each node

#### Exceptions:
- `InvalidConfigurationException` - if the configuration is invalid
- `OperationFailedException` - if the operation failed

### inspect
```php
public function inspect(string $containerId): array
```
Gets detailed information about a container on all cluster nodes.

#### Parameters:
- `$containerId` - Container ID or name

#### Returns:
- Array where keys are node names and values are operation results on each node

#### Exceptions:
- `MissingRequiredParameterException` - if the container ID is empty

### start
```php
public function start(string $containerId): array
```
Starts a container on all cluster nodes.

#### Parameters:
- `$containerId` - Container ID or name

#### Returns:
- Array where keys are node names and values are operation results on each node

#### Exceptions:
- `MissingRequiredParameterException` - if the container ID is empty

### stop
```php
public function stop(string $containerId, int $timeout = 10): array
```
Stops a container on all cluster nodes.

#### Parameters:
- `$containerId` - Container ID or name
- `$timeout` - Stop timeout in seconds (default 10)

#### Returns:
- Array where keys are node names and values are operation results on each node

#### Exceptions:
- `MissingRequiredParameterException` - if the container ID is empty
- `InvalidParameterValueException` - if the timeout is negative

### restart
```php
public function restart(string $containerId, int $timeout = 10): array
```
Restarts a container on all cluster nodes.

#### Parameters:
- `$containerId` - Container ID or name
- `$timeout` - Stop timeout in seconds (default 10)

#### Returns:
- Array where keys are node names and values are operation results on each node

#### Exceptions:
- `MissingRequiredParameterException` - if the container ID is empty
- `InvalidParameterValueException` - if the timeout is negative

### remove
```php
public function remove(string $containerId, bool $force = false, bool $removeVolumes = false): array
```
Removes a container on all cluster nodes.

#### Parameters:
- `$containerId` - Container ID or name
- `$force` - Force remove a running container (default false)
- `$removeVolumes` - Remove volumes along with the container (default false)

#### Returns:
- Array where keys are node names and values are operation results on each node

#### Exceptions:
- `MissingRequiredParameterException` - if the container ID is empty

### logs
```php
public function logs(string $containerId, array $parameters = []): array
```
Gets container logs from all cluster nodes.

#### Parameters:
- `$containerId` - Container ID or name
- `$parameters` - Log request parameters:
  - `stdout` (bool): Show standard output (stdout)
  - `stderr` (bool): Show standard error stream (stderr)
  - `since` (int): Show logs from the specified time (Unix timestamp)
  - `until` (int): Show logs until the specified time (Unix timestamp)
  - `timestamps` (bool): Add timestamps to logs
  - `tail` (string/int): Number of last log lines to return

#### Returns:
- Array where keys are node names and values are operation results on each node

#### Exceptions:
- `MissingRequiredParameterException` - if the container ID is empty
- `InvalidParameterValueException` - if invalid parameters are provided

### stats
```php
public function stats(string $containerId, bool $stream = false): array
```
Gets container resource usage statistics on all cluster nodes.

#### Parameters:
- `$containerId` - Container ID or name
- `$stream` - Whether to stream statistics (default false)

#### Returns:
- Array where keys are node names and values are operation results on each node

#### Exceptions:
- `MissingRequiredParameterException` - if the container ID is empty

### exists
```php
public function exists(string $containerId): array
```
Checks if a container exists on all cluster nodes.

#### Parameters:
- `$containerId` - Container ID or name

#### Returns:
- Array where keys are node names and values are boolean results (true if the container exists, false if not)

#### Exceptions:
- `MissingRequiredParameterException` - if the container ID is empty

### existsOnAllNodes
```php
public function existsOnAllNodes(string $containerId): bool
```
Checks if a container exists on all cluster nodes.

#### Parameters:
- `$containerId` - Container ID or name

#### Returns:
- `true` if the container exists on all nodes, `false` if it's missing on at least one node

#### Exceptions:
- `MissingRequiredParameterException` - if the container ID is empty

### getNodesWithContainer
```php
public function getNodesWithContainer(string $containerId): array
```
Gets a list of nodes where the container exists.

#### Parameters:
- `$containerId` - Container ID or name

#### Returns:
- Array with names of nodes where the container exists

#### Exceptions:
- `MissingRequiredParameterException` - if the container ID is empty

## Usage Examples

### Getting a List of Containers from All Nodes
```php
use Sangezar\DockerClient\Cluster\DockerCluster;
use Sangezar\DockerClient\DockerClient;

// Creating a cluster
$cluster = new DockerCluster();
$cluster->addNode('node1', DockerClient::createTcp('tcp://192.168.1.10:2375'));
$cluster->addNode('node2', DockerClient::createTcp('tcp://192.168.1.11:2375'));

// Getting a list of all containers
$containers = $cluster->node('node1')->containers()->list(['all' => true]);

// Checking results
foreach ($containers as $nodeName => $result) {
    echo "Containers on node $nodeName:\n";
    foreach ($result as $container) {
        echo "  - {$container['Names'][0]} (ID: {$container['Id']})\n";
    }
}
```

### Creating and Starting a Container on All Nodes
```php
use Sangezar\DockerClient\Config\ContainerConfig;
use Sangezar\DockerClient\Cluster\NodeCollection;
use Sangezar\DockerClient\DockerClient;

// Creating a node collection
$nodes = [
    'node1' => DockerClient::createTcp('tcp://192.168.1.10:2375'),
    'node2' => DockerClient::createTcp('tcp://192.168.1.11:2375'),
];
$collection = new NodeCollection($nodes);

// Creating container configuration
$config = ContainerConfig::create()
    ->setImage('nginx:latest')
    ->setName('test-nginx')
    ->exposePorts(80, 443);

// Creating a container on all nodes
$results = $collection->containers()->create($config);

// Starting a container on all nodes
$startResults = $collection->containers()->start('test-nginx');
```

### Checking Container Existence on Nodes
```php
// Checking if the container exists on all nodes
$exists = $collection->containers()->existsOnAllNodes('test-nginx');
if ($exists) {
    echo "Container 'test-nginx' exists on all nodes\n";
} else {
    // Getting a list of nodes where the container exists
    $nodesWithContainer = $collection->containers()->getNodesWithContainer('test-nginx');
    echo "Container 'test-nginx' exists only on nodes: " . implode(', ', $nodesWithContainer) . "\n";
}

// Stopping and removing the container
$collection->containers()->stop('test-nginx');
$collection->containers()->remove('test-nginx', true);
``` 