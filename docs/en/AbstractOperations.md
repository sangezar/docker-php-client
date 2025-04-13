# AbstractOperations Class Documentation

## Description
`AbstractOperations` is an abstract base class for all Docker cluster operation classes. It provides core functionality for performing operations on multiple Docker nodes simultaneously, with support for sequential and parallel execution, retry on failure, and various levels of error detail.

## Namespace
`Sangezar\DockerClient\Cluster\Operations`

## Constants

### Execution Strategy Types
```php
public const EXECUTION_SEQUENTIAL = 'sequential';
public const EXECUTION_PARALLEL = 'parallel';
```

### Error Detail Levels
```php
public const ERROR_LEVEL_BASIC = 'basic';      // Message only
public const ERROR_LEVEL_STANDARD = 'standard'; // Message + exception type + code
public const ERROR_LEVEL_DETAILED = 'detailed'; // All details, including stack trace
```

## Properties
```php
/** @var array<string, DockerClient> */
protected array $nodes;

/** @var string Execution strategy */
protected string $executionStrategy = self::EXECUTION_SEQUENTIAL;

/** @var string Error detail level */
protected string $errorDetailLevel = self::ERROR_LEVEL_STANDARD;

/** @var bool Allow automatic retries on failure */
protected bool $retryOnFailure = false;

/** @var int Maximum number of retries */
protected int $maxRetries = 3;
```

## Methods

### __construct
```php
public function __construct(array $nodes, ?ClusterConfig $config = null)
```
Constructor.

#### Parameters:
- `$nodes` - Array of Docker API clients with node names as keys
- `$config` - Cluster configuration (optional)

#### Exceptions:
- `MissingRequiredParameterException` - if the nodes array is empty
- `InvalidParameterValueException` - if parameters are invalid

### applyConfig
```php
public function applyConfig(ClusterConfig $config): self
```
Applies cluster configuration.

#### Parameters:
- `$config` - Configuration to apply

#### Returns:
- Current object for chained calls

### setExecutionStrategy
```php
public function setExecutionStrategy(string $strategy): self
```
Sets the execution strategy.

#### Parameters:
- `$strategy` - Execution strategy (EXECUTION_SEQUENTIAL or EXECUTION_PARALLEL)

#### Returns:
- Current object for chained calls

#### Exceptions:
- `InvalidParameterValueException` - if an unknown strategy is specified

### setErrorDetailLevel
```php
public function setErrorDetailLevel(string $level): self
```
Sets the error detail level.

#### Parameters:
- `$level` - Detail level (ERROR_LEVEL_BASIC, ERROR_LEVEL_STANDARD or ERROR_LEVEL_DETAILED)

#### Returns:
- Current object for chained calls

#### Exceptions:
- `InvalidParameterValueException` - if an unknown level is specified

### setRetryOnFailure
```php
public function setRetryOnFailure(bool $enable, ?int $maxRetries = null): self
```
Sets retry on failure settings.

#### Parameters:
- `$enable` - Whether retries are allowed
- `$maxRetries` - Maximum number of retries (default 3)

#### Returns:
- Current object for chained calls

#### Exceptions:
- `InvalidParameterValueException` - if the number of retries is less than 1

### executeOnAll
```php
protected function executeOnAll(callable $operation): array
```
Executes an operation on all cluster nodes.

#### Parameters:
- `$operation` - Function to be executed on each node

#### Returns:
- Array of execution results for each node

#### Exceptions:
- `InvalidParameterValueException` - if the operation is not callable

### getNodes
```php
public function getNodes(): array
```
Gets all nodes.

#### Returns:
- Array of nodes where keys are node names and values are `DockerClient` instances

### isEmpty
```php
public function isEmpty(): bool
```
Checks if the node collection is empty.

#### Returns:
- `true` if the collection contains no nodes, `false` otherwise

### count
```php
public function count(): int
```
Counts the number of nodes.

#### Returns:
- Number of nodes

### addNode
```php
public function addNode(string $name, DockerClient $client): self
```
Adds a new node to the collection.

#### Parameters:
- `$name` - Node name
- `$client` - Docker API client

#### Returns:
- Current object for chained calls

#### Exceptions:
- `InvalidParameterValueException` - if the node name is empty or a node with this name already exists

### removeNode
```php
public function removeNode(string $name): self
```
Removes a node from the collection.

#### Parameters:
- `$name` - Node name

#### Returns:
- Current object for chained calls

### hasNode
```php
public function hasNode(string $name): bool
```
Checks if a node with the specified name exists.

#### Parameters:
- `$name` - Node name

#### Returns:
- `true` if the node exists, `false` otherwise

## Protected and Private Methods

### executeSequential
```php
private function executeSequential(callable $operation): array
```
Executes an operation sequentially on all cluster nodes.

#### Parameters:
- `$operation` - Function to be executed on each node

#### Returns:
- Array of execution results for each node

### executeParallel
```php
private function executeParallel(callable $operation): array
```
Executes an operation in parallel on all cluster nodes.

#### Parameters:
- `$operation` - Function to be executed on each node

#### Returns:
- Array of execution results for each node

### formatError
```php
private function formatError(\Throwable $e): array
```
Formats an error according to the set detail level.

#### Parameters:
- `$e` - Exception object

#### Returns:
- Formatted array with error information

## Usage Examples

### Basic Usage in Inherited Classes
```php
class MyOperations extends AbstractOperations
{
    public function perform(): array
    {
        return $this->executeOnAll(function (DockerClient $client) {
            // Perform operation with the client
            return $client->container()->list();
        });
    }
}

// Creating an instance
$nodes = [
    'node1' => DockerClient::createTcp('tcp://192.168.1.10:2375'),
    'node2' => DockerClient::createTcp('tcp://192.168.1.11:2375'),
];
$operations = new MyOperations($nodes);

// Performing operation on all nodes
$results = $operations->perform();
```

### Configuring Execution Strategy and Error Handling
```php
use Sangezar\DockerClient\Config\ClusterConfig;
use Sangezar\DockerClient\Cluster\Operations\AbstractOperations;

// Creating cluster configuration
$config = ClusterConfig::create()
    ->setExecutionStrategy(AbstractOperations::EXECUTION_PARALLEL)
    ->setErrorDetailLevel(AbstractOperations::ERROR_LEVEL_DETAILED)
    ->setRetryOnFailure(true, 5);

// Applying configuration to operations
$operations->applyConfig($config);

// Or individual configuration
$operations->setExecutionStrategy(AbstractOperations::EXECUTION_PARALLEL)
    ->setErrorDetailLevel(AbstractOperations::ERROR_LEVEL_DETAILED)
    ->setRetryOnFailure(true, 5);
```

### Managing Nodes During Execution
```php
// Adding a new node
$operations->addNode('node3', DockerClient::createTcp('tcp://192.168.1.12:2375'));

// Checking if a node exists
if ($operations->hasNode('node1')) {
    echo "Node 'node1' exists\n";
}

// Removing a node
$operations->removeNode('node2');

// Getting a list of all nodes
$allNodes = $operations->getNodes();

// Checking the number of nodes
echo "Number of nodes: " . $operations->count() . "\n";
``` 