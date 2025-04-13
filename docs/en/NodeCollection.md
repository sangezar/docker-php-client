# NodeCollection Class Documentation

## Description
`NodeCollection` is a class that represents a collection of Docker nodes for performing operations in a cluster. It allows you to perform the same operations on multiple Docker nodes simultaneously.

## Namespace
`Sangezar\DockerClient\Cluster`

## Methods

### __construct
```php
public function __construct(array $nodes)
```
Creates a new node collection.

#### Parameters:
- `$nodes` - Associative array of nodes where keys are node names and values are `DockerClient` instances

### containers
```php
public function containers(): ContainerOperations
```
Gets an object for working with containers on all nodes in the collection.

#### Returns:
- An instance of the `ContainerOperations` class for working with containers

### images
```php
public function images(): ImageOperations
```
Gets an object for working with images on all nodes in the collection.

#### Returns:
- An instance of the `ImageOperations` class for working with images

### networks
```php
public function networks(): NetworkOperations
```
Gets an object for working with networks on all nodes in the collection.

#### Returns:
- An instance of the `NetworkOperations` class for working with networks

### volumes
```php
public function volumes(): VolumeOperations
```
Gets an object for working with volumes on all nodes in the collection.

#### Returns:
- An instance of the `VolumeOperations` class for working with volumes

### system
```php
public function system(): SystemOperations
```
Gets an object for working with system functions on all nodes in the collection.

#### Returns:
- An instance of the `SystemOperations` class for working with system functions

### filter
```php
public function filter(callable $callback): self
```
Filters nodes in the collection using a callback function.

#### Parameters:
- `$callback` - Callback function for filtering that takes a node as an argument and returns a boolean value

#### Returns:
- A new `NodeCollection` with filtered nodes

### getNodes
```php
public function getNodes(): array
```
Gets all nodes in the collection.

#### Returns:
- Associative array of nodes where keys are node names and values are `DockerClient` instances

### count
```php
public function count(): int
```
Counts the number of nodes in the collection.

#### Returns:
- Number of nodes in the collection

### isEmpty
```php
public function isEmpty(): bool
```
Checks if the collection is empty.

#### Returns:
- `true` if the collection contains no nodes, `false` otherwise

## Usage Examples

### Creating a Node Collection and Performing Operations
```php
use Sangezar\DockerClient\DockerClient;
use Sangezar\DockerClient\Cluster\NodeCollection;

// Creating Docker clients for different nodes
$node1 = DockerClient::createTcp('tcp://192.168.1.10:2375');
$node2 = DockerClient::createTcp('tcp://192.168.1.11:2375');

// Creating a node collection
$nodes = [
    'node1' => $node1,
    'node2' => $node2,
];
$collection = new NodeCollection($nodes);

// Getting a list of containers on all nodes
$containersMap = $collection->containers()->list(['all' => true]);

// Result - an array where keys are node names and values are arrays of containers
foreach ($containersMap as $nodeName => $containers) {
    echo "Node: $nodeName\n";
    foreach ($containers as $container) {
        echo "  Container: {$container['Names'][0]}\n";
    }
}
```

### Filtering a Node Collection
```php
use Sangezar\DockerClient\DockerClient;
use Sangezar\DockerClient\Cluster\NodeCollection;

// Creating a node collection
$nodes = [
    'node1' => DockerClient::createTcp('tcp://192.168.1.10:2375'),
    'node2' => DockerClient::createTcp('tcp://192.168.1.11:2375'),
    'node3' => DockerClient::createTcp('tcp://192.168.1.12:2375'),
];
$collection = new NodeCollection($nodes);

// Filtering nodes by name
$filteredCollection = $collection->filter(function ($client, $name) {
    return strpos($name, 'node1') === 0 || strpos($name, 'node2') === 0;
});

// Checking the number of nodes after filtering
echo "Number of nodes after filtering: " . $filteredCollection->count() . "\n";

// Performing operations only on filtered nodes
$imagesMap = $filteredCollection->images()->list();
``` 