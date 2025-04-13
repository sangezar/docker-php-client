# DockerCluster Class Documentation

## Description
`DockerCluster` is a class that represents a cluster of Docker servers. It allows you to manage a set of Docker nodes, group them using tags, and perform operations on individual nodes as well as on groups of nodes.

## Namespace
`Sangezar\DockerClient\Cluster`

## Constants
```php
// Regular expression for node name validation
private const NODE_NAME_PATTERN = '/^[a-zA-Z0-9][a-zA-Z0-9_.-]*$/';

// Regular expression for tag name validation
private const TAG_NAME_PATTERN = '/^[a-zA-Z0-9][a-zA-Z0-9_.-]*$/';
```

## Methods

### addNode
```php
public function addNode(string $name, DockerClient $client, array $tags = []): self
```
Adds a new node to the cluster.

#### Parameters:
- `$name` - Unique node name
- `$client` - Docker API client
- `$tags` - Array of tags for categorizing the node (default empty array)

#### Returns:
- `DockerCluster` instance (for chained calls)

#### Exceptions:
- `ValidationException` - if the node name is invalid or already exists

### node
```php
public function node(string $name): DockerClient
```
Returns a node client by its name.

#### Parameters:
- `$name` - Node name

#### Returns:
- `DockerClient` instance for the specified node

#### Exceptions:
- `NodeNotFoundException` - if the node is not found
- `ValidationException` - if the node name is empty

### hasNode
```php
public function hasNode(string $name): bool
```
Checks if a node with the specified name exists.

#### Parameters:
- `$name` - Node name

#### Returns:
- `true` if the node exists, `false` otherwise

### removeNode
```php
public function removeNode(string $name): self
```
Removes a node from the cluster.

#### Parameters:
- `$name` - Node name

#### Returns:
- `DockerCluster` instance (for chained calls)

#### Exceptions:
- `ValidationException` - if the node name is empty

### getNodesByTag
```php
public function getNodesByTag(string $tag): array
```
Returns all nodes with the specified tag.

#### Parameters:
- `$tag` - Tag to filter by

#### Returns:
- Associative array of nodes that have the specified tag

#### Exceptions:
- `ValidationException` - if the tag is invalid

### addTagToNode
```php
public function addTagToNode(string $nodeName, string $tag): self
```
Adds a tag to an existing node.

#### Parameters:
- `$nodeName` - Node name
- `$tag` - Tag to add

#### Returns:
- `DockerCluster` instance (for chained calls)

#### Exceptions:
- `NodeNotFoundException` - if the node is not found
- `ValidationException` - if the tag is invalid or the node name is empty

### removeTagFromNode
```php
public function removeTagFromNode(string $nodeName, string $tag): self
```
Removes a tag from a node.

#### Parameters:
- `$nodeName` - Node name
- `$tag` - Tag to remove

#### Returns:
- `DockerCluster` instance (for chained calls)

#### Exceptions:
- `NodeNotFoundException` - if the node is not found
- `ValidationException` - if the node name is empty

### getNodesByAllTags
```php
public function getNodesByAllTags(array $tags): array
```
Returns all nodes that have all the specified tags (AND operation).

#### Parameters:
- `$tags` - Array of tags

#### Returns:
- Associative array of nodes that have all the specified tags

#### Exceptions:
- `ValidationException` - if any tag is invalid

### getNodesByAnyTag
```php
public function getNodesByAnyTag(array $tags): array
```
Returns all nodes that have at least one of the specified tags (OR operation).

#### Parameters:
- `$tags` - Array of tags

#### Returns:
- Associative array of nodes that have at least one of the specified tags

#### Exceptions:
- `ValidationException` - if any tag is invalid

### filter
```php
public function filter(callable $callback): NodeCollection
```
Returns a collection of nodes filtered using a callback function.

#### Parameters:
- `$callback` - Callback function for filtering that takes a node as an argument and returns a boolean value

#### Returns:
- `NodeCollection` instance with filtered nodes

### all
```php
public function all(): NodeCollection
```
Returns a collection of all cluster nodes.

#### Returns:
- `NodeCollection` instance with all nodes

### byTag
```php
public function byTag(string $tag): NodeCollection
```
Returns a collection of nodes with the specified tag.

#### Parameters:
- `$tag` - Tag to filter by

#### Returns:
- `NodeCollection` instance with nodes that have the specified tag

### byAllTags
```php
public function byAllTags(array $tags): NodeCollection
```
Returns a collection of nodes that have all the specified tags.

#### Parameters:
- `$tags` - Array of tags

#### Returns:
- `NodeCollection` instance with nodes that have all the specified tags

### byAnyTag
```php
public function byAnyTag(array $tags): NodeCollection
```
Returns a collection of nodes that have at least one of the specified tags.

#### Parameters:
- `$tags` - Array of tags

#### Returns:
- `NodeCollection` instance with nodes that have at least one of the specified tags

### addNodes
```php
public function addNodes(array $nodes): self
```
Adds multiple nodes to the cluster.

#### Parameters:
- `$nodes` - Array of nodes to add, where keys:
  - `name` (string) - Node name
  - `client` (DockerClient) - Docker API client
  - `tags` (array, optional) - Array of tags

#### Returns:
- `DockerCluster` instance (for chained calls)

#### Exceptions:
- Same as in the `addNode` method

### getNodes
```php
public function getNodes(): array
```
Returns all cluster nodes.

#### Returns:
- Associative array of nodes where keys are node names and values are `DockerClient` instances

### getTags
```php
public function getTags(): array
```
Returns all cluster tags.

#### Returns:
- Associative array of tags where keys are tag names and values are arrays of node names

### isEmpty
```php
public function isEmpty(): bool
```
Checks if the cluster is empty.

#### Returns:
- `true` if the cluster has no nodes, `false` otherwise

### count
```php
public function count(): int
```
Counts the number of nodes in the cluster.

#### Returns:
- Number of nodes in the cluster

## Usage Examples

### Creating a Cluster and Adding Nodes
```php
use Sangezar\DockerClient\DockerClient;
use Sangezar\DockerClient\Cluster\DockerCluster;

// Creating a cluster
$cluster = new DockerCluster();

// Adding nodes with tags
$cluster->addNode(
    'node1',
    DockerClient::createTcp('tcp://192.168.1.10:2375'),
    ['production', 'web']
);

$cluster->addNode(
    'node2',
    DockerClient::createTcp('tcp://192.168.1.11:2375'),
    ['production', 'database']
);

$cluster->addNode(
    'node3',
    DockerClient::createTcp('tcp://192.168.1.12:2375'),
    ['staging', 'web']
);

// Checking the number of nodes
echo "Total number of nodes: " . $cluster->count() . "\n";
```

### Getting Nodes by Tags
```php
// Getting all nodes with the 'production' tag
$productionNodes = $cluster->getNodesByTag('production');
echo "Nodes with the 'production' tag: " . implode(', ', array_keys($productionNodes)) . "\n";

// Getting nodes that have both 'production' and 'web' tags
$productionWebNodes = $cluster->getNodesByAllTags(['production', 'web']);
echo "Nodes with 'production' and 'web' tags: " . implode(', ', array_keys($productionWebNodes)) . "\n";

// Getting nodes that have either 'production' or 'staging' tag
$allEnvNodes = $cluster->getNodesByAnyTag(['production', 'staging']);
echo "Nodes with 'production' or 'staging' tags: " . implode(', ', array_keys($allEnvNodes)) . "\n";
```

### Performing Operations on a Group of Nodes
```php
// Getting a collection of nodes by tag and performing operations
$webNodesCollection = $cluster->byTag('web');

// Getting a list of all containers on web nodes
$containersMap = $webNodesCollection->containers()->list(['all' => true]);

foreach ($containersMap as $nodeName => $containers) {
    echo "Node: $nodeName\n";
    foreach ($containers as $container) {
        echo "  Container: {$container['Names'][0]}\n";
    }
}
```

### Managing Tags
```php
// Adding a tag to a node
$cluster->addTagToNode('node3', 'monitoring');

// Removing a tag from a node
$cluster->removeTagFromNode('node2', 'production');

// Checking if a node exists
if ($cluster->hasNode('node1')) {
    echo "Node 'node1' exists\n";
    
    // Performing operations on an individual node
    $node1Client = $cluster->node('node1');
    $containers = $node1Client->container()->list(['all' => true]);
    echo "Number of containers on node 'node1': " . count($containers) . "\n";
}
``` 