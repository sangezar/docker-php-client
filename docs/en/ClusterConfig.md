# ClusterConfig Class Documentation

## Description
`ClusterConfig` is a class for configuring Docker cluster operations. It allows you to set execution strategy, error detail level, configure retries, set node priorities, and other cluster parameters.

## Namespace
`Sangezar\DockerClient\Config`

## Methods

### create
```php
public static function create(): self
```
Creates a new cluster configuration instance.

#### Returns:
- A new instance of `ClusterConfig`

### setExecutionStrategy
```php
public function setExecutionStrategy(string $strategy): self
```
Sets the execution strategy for operations on cluster nodes.

#### Parameters:
- `$strategy` - Execution strategy:
  - `AbstractOperations::EXECUTION_SEQUENTIAL` - sequential execution
  - `AbstractOperations::EXECUTION_PARALLEL` - parallel execution

#### Returns:
- Current instance for chained calls

#### Exceptions:
- `InvalidParameterValueException` - if an unknown strategy is specified

### setErrorDetailLevel
```php
public function setErrorDetailLevel(string $level): self
```
Sets the error detail level.

#### Parameters:
- `$level` - Detail level:
  - `AbstractOperations::ERROR_LEVEL_BASIC` - basic level (only messages)
  - `AbstractOperations::ERROR_LEVEL_STANDARD` - standard level (messages, exception type, code)
  - `AbstractOperations::ERROR_LEVEL_DETAILED` - detailed level (all details, including stack trace)

#### Returns:
- Current instance for chained calls

#### Exceptions:
- `InvalidParameterValueException` - if an unknown level is specified

### setRetryConfig
```php
public function setRetryConfig(bool $enable, ?int $maxRetries = null, ?int $retryDelay = null, ?bool $exponentialBackoff = null): self
```
Configures retry parameters for error handling.

#### Parameters:
- `$enable` - Enable retries
- `$maxRetries` - Maximum number of retries (default 3)
- `$retryDelay` - Initial delay between retries in milliseconds (default 1000)
- `$exponentialBackoff` - Whether to use exponential delay increase between retries

#### Returns:
- Current instance for chained calls

#### Exceptions:
- `InvalidParameterValueException` - if parameters are invalid

### setOperationTimeout
```php
public function setOperationTimeout(int $seconds): self
```
Sets the timeout for cluster node operations.

#### Parameters:
- `$seconds` - Timeout in seconds

#### Returns:
- Current instance for chained calls

#### Exceptions:
- `InvalidParameterValueException` - if the timeout value is invalid

### setNodePriority
```php
public function setNodePriority(string $nodeName, int $priority): self
```
Sets the priority for a node.

#### Parameters:
- `$nodeName` - Node name
- `$priority` - Priority (1 is highest)

#### Returns:
- Current instance for chained calls

#### Exceptions:
- `InvalidParameterValueException` - if the priority value is invalid

### setDefaultNodeTag
```php
public function setDefaultNodeTag(?string $tag): self
```
Sets the default tag for operations.

#### Parameters:
- `$tag` - Tag for filtering nodes

#### Returns:
- Current instance for chained calls

### addFailoverNode
```php
public function addFailoverNode(string $nodeName): self
```
Adds a node to the list of failover nodes.

#### Parameters:
- `$nodeName` - Node name to use in case of main nodes failure

#### Returns:
- Current instance for chained calls

#### Exceptions:
- `InvalidParameterValueException` - if the node name is empty

### getExecutionStrategy
```php
public function getExecutionStrategy(): string
```
Gets the current execution strategy.

#### Returns:
- Current execution strategy

### getErrorDetailLevel
```php
public function getErrorDetailLevel(): string
```
Gets the current error detail level.

#### Returns:
- Current error detail level

### isRetryOnFailure
```php
public function isRetryOnFailure(): bool
```
Checks if retries are allowed.

#### Returns:
- `true` if retries are allowed, `false` otherwise

### getMaxRetries
```php
public function getMaxRetries(): int
```
Gets the maximum number of retries.

#### Returns:
- Maximum number of retries

### getRetryDelay
```php
public function getRetryDelay(): int
```
Gets the delay between retries.

#### Returns:
- Delay in milliseconds

### isExponentialBackoff
```php
public function isExponentialBackoff(): bool
```
Checks if exponential delay increase is used.

#### Returns:
- `true` if exponential increase is used, `false` otherwise

### getOperationTimeout
```php
public function getOperationTimeout(): int
```
Gets the operation timeout.

#### Returns:
- Timeout in seconds

### getNodePriorities
```php
public function getNodePriorities(): array
```
Gets node priorities.

#### Returns:
- Array of priorities where keys are node names and values are priorities

### getDefaultNodeTag
```php
public function getDefaultNodeTag(): ?string
```
Gets the default tag.

#### Returns:
- Default tag or `null` if not set

### getFailoverNodes
```php
public function getFailoverNodes(): array
```
Gets the list of failover nodes.

#### Returns:
- Array of failover node names

### toArray
```php
public function toArray(): array
```
Converts the configuration to an array.

#### Returns:
- Array with all configuration settings

## Usage Examples

### Creating Basic Configuration
```php
use Sangezar\DockerClient\Config\ClusterConfig;
use Sangezar\DockerClient\Cluster\Operations\AbstractOperations;

// Creating configuration with default parameters
$config = ClusterConfig::create();

// Setting execution strategy and error level
$config->setExecutionStrategy(AbstractOperations::EXECUTION_PARALLEL)
       ->setErrorDetailLevel(AbstractOperations::ERROR_LEVEL_DETAILED);
```

### Configuring Retries
```php
use Sangezar\DockerClient\Config\ClusterConfig;

$config = ClusterConfig::create();

// Allow retries with maximum 5 attempts
// and 500ms delay between attempts
$config->setRetryConfig(true, 5, 500, true);

// Set operation timeout to 60 seconds
$config->setOperationTimeout(60);
```

### Configuring Node Priorities
```php
use Sangezar\DockerClient\Config\ClusterConfig;

$config = ClusterConfig::create();

// Setting priorities for nodes
$config->setNodePriority('node1', 1) // highest priority
       ->setNodePriority('node2', 2)
       ->setNodePriority('node3', 3);

// Adding failover nodes
$config->addFailoverNode('backup-node1')
       ->addFailoverNode('backup-node2');

// Setting default tag
$config->setDefaultNodeTag('production');
```

### Getting Configuration Settings
```php
use Sangezar\DockerClient\Config\ClusterConfig;

$config = ClusterConfig::create()
    ->setExecutionStrategy(AbstractOperations::EXECUTION_PARALLEL)
    ->setRetryConfig(true, 3, 1000, true);

// Checking if retries are allowed
if ($config->isRetryOnFailure()) {
    echo "Retries are allowed\n";
    echo "Maximum number of attempts: " . $config->getMaxRetries() . "\n";
    echo "Delay: " . $config->getRetryDelay() . " ms\n";
}

// Getting all settings as an array
$allSettings = $config->toArray();
``` 