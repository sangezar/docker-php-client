# Container Class Documentation

## Description
`Container` is a class for working with Docker containers through the API. It provides methods for creating, starting, stopping, restarting, removing, and managing Docker containers.

## Namespace
`Sangezar\DockerClient\Api`

## Inheritance
The `Container` class inherits from `AbstractApi` and implements the `ContainerInterface` interface.

## Constants

### Container Signals
```php
public const SIGNAL_HUP = 'SIGHUP';
public const SIGNAL_INT = 'SIGINT';
public const SIGNAL_QUIT = 'SIGQUIT';
public const SIGNAL_ILL = 'SIGILL';
public const SIGNAL_TRAP = 'SIGTRAP';
public const SIGNAL_ABRT = 'SIGABRT';
public const SIGNAL_BUS = 'SIGBUS';
public const SIGNAL_FPE = 'SIGFPE';
public const SIGNAL_KILL = 'SIGKILL';
public const SIGNAL_USR1 = 'SIGUSR1';
public const SIGNAL_SEGV = 'SIGSEGV';
public const SIGNAL_USR2 = 'SIGUSR2';
public const SIGNAL_PIPE = 'SIGPIPE';
public const SIGNAL_ALRM = 'SIGALRM';
public const SIGNAL_TERM = 'SIGTERM';
public const SIGNAL_STKFLT = 'SIGSTKFLT';
public const SIGNAL_CHLD = 'SIGCHLD';
public const SIGNAL_CONT = 'SIGCONT';
public const SIGNAL_STOP = 'SIGSTOP';
public const SIGNAL_TSTP = 'SIGTSTP';
public const SIGNAL_TTIN = 'SIGTTIN';
public const SIGNAL_TTOU = 'SIGTTOU';
public const SIGNAL_URG = 'SIGURG';
public const SIGNAL_XCPU = 'SIGXCPU';
public const SIGNAL_XFSZ = 'SIGXFSZ';
public const SIGNAL_VTALRM = 'SIGVTALRM';
public const SIGNAL_PROF = 'SIGPROF';
public const SIGNAL_WINCH = 'SIGWINCH';
public const SIGNAL_IO = 'SIGIO';
public const SIGNAL_PWR = 'SIGPWR';
public const SIGNAL_SYS = 'SIGSYS';
public const SIGNAL_POLL = 'SIGPOLL';
```

## Methods

### list
```php
public function list(array $parameters = []): array
```
Gets a list of containers.

#### Parameters:
- `$parameters` - Array of parameters for filtering results:
  - `all` (bool) - Show all containers (by default only running containers are shown)
  - `limit` (int) - Limit the number of results
  - `size` (bool) - Show container sizes
  - `filters` (array|string) - Filters in JSON format

#### Returns:
- Array of containers

#### Exceptions:
- `InvalidParameterValueException` - if invalid parameters are passed

### create
```php
public function create(ContainerConfig $config): array
```
Creates a new container.

#### Parameters:
- `$config` - `ContainerConfig` object with container configuration

#### Returns:
- Array with information about the created container

#### Exceptions:
- `MissingRequiredParameterException` - if required parameters are missing
- `InvalidParameterValueException` - if invalid parameters are passed
- `InvalidConfigurationException` - if the configuration is invalid
- `OperationFailedException` - if the operation fails

### inspect
```php
public function inspect(string $containerId): array
```
Gets detailed information about a container.

#### Parameters:
- `$containerId` - Container ID or name

#### Returns:
- Array with detailed information about the container

#### Exceptions:
- `MissingRequiredParameterException` - if the container ID is empty
- `NotFoundException` - if the container is not found

### start
```php
public function start(string $containerId): bool
```
Starts a container.

#### Parameters:
- `$containerId` - Container ID or name

#### Returns:
- `true` if the container was successfully started

#### Exceptions:
- `MissingRequiredParameterException` - if the container ID is empty
- `OperationFailedException` - if the operation fails
- `NotFoundException` - if the container is not found

### stop
```php
public function stop(string $containerId, int $timeout = 10): bool
```
Stops a container.

#### Parameters:
- `$containerId` - Container ID or name
- `$timeout` - Timeout in seconds before forced stop (default 10 seconds)

#### Returns:
- `true` if the container was successfully stopped

#### Exceptions:
- `MissingRequiredParameterException` - if the container ID is empty
- `OperationFailedException` - if the operation fails
- `NotFoundException` - if the container is not found

### restart
```php
public function restart(string $containerId, int $timeout = 10): bool
```
Restarts a container.

#### Parameters:
- `$containerId` - Container ID or name
- `$timeout` - Timeout in seconds before forced restart (default 10 seconds)

#### Returns:
- `true` if the container was successfully restarted

#### Exceptions:
- `MissingRequiredParameterException` - if the container ID is empty
- `OperationFailedException` - if the operation fails
- `NotFoundException` - if the container is not found

### kill
```php
public function kill(string $id, string $signal = null): bool
```
Kills a container by sending a signal.

#### Parameters:
- `$id` - Container ID or name
- `$signal` - Signal to send (default SIGKILL)

#### Returns:
- `true` if the container was successfully killed

#### Exceptions:
- `MissingRequiredParameterException` - if the container ID is empty
- `InvalidParameterValueException` - if the signal is invalid
- `OperationFailedException` - if the operation fails
- `NotFoundException` - if the container is not found

### remove
```php
public function remove(string $containerId, bool $force = false, bool $removeVolumes = false): bool
```
Removes a container.

#### Parameters:
- `$containerId` - Container ID or name
- `$force` - Force removal, even if the container is running (default `false`)
- `$removeVolumes` - Remove associated volumes (default `false`)

#### Returns:
- `true` if the container was successfully removed

#### Exceptions:
- `MissingRequiredParameterException` - if the container ID is empty
- `OperationFailedException` - if the operation fails
- `NotFoundException` - if the container is not found

### logs
```php
public function logs(string $containerId, array $parameters = []): array
```
Gets container logs.

#### Parameters:
- `$containerId` - Container ID or name
- `$parameters` - Array of parameters for filtering logs:
  - `follow` (bool) - Follow log output
  - `stdout` (bool) - Include stdout (default `true`)
  - `stderr` (bool) - Include stderr (default `true`)
  - `since` (int) - Timestamp for log start
  - `until` (int) - Timestamp for log end
  - `timestamps` (bool) - Include timestamps
  - `tail` (string|int) - Number of lines to show from the end of logs

#### Returns:
- Array with container logs

#### Exceptions:
- `MissingRequiredParameterException` - if the container ID is empty
- `InvalidParameterValueException` - if invalid parameters are passed
- `NotFoundException` - if the container is not found

### stats
```php
public function stats(string $containerId, bool $stream = false): array
```
Gets container resource usage statistics.

#### Parameters:
- `$containerId` - Container ID or name
- `$stream` - Get statistics in real-time (default `false`)

#### Returns:
- Array with resource usage statistics

#### Exceptions:
- `MissingRequiredParameterException` - if the container ID is empty
- `NotFoundException` - if the container is not found

### exists
```php
public function exists(string $containerId): bool
```
Checks if a container exists.

#### Parameters:
- `$containerId` - Container ID or name

#### Returns:
- `true` if the container exists, `false` otherwise

#### Exceptions:
- `MissingRequiredParameterException` - if the container ID is empty

## Usage Examples

### Getting a List of Containers
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createUnix();
$containers = $client->container()->list(['all' => true]);

foreach ($containers as $container) {
    echo "Container: {$container['Names'][0]}, status: {$container['Status']}\n";
}
```

### Creating and Starting a Container
```php
use Sangezar\DockerClient\DockerClient;
use Sangezar\DockerClient\Config\ContainerConfig;

$client = DockerClient::createUnix();

$config = new ContainerConfig();
$config->setName('my-container')
       ->setImage('nginx:latest')
       ->setExposedPorts(['80/tcp' => []])
       ->setHostConfig([
           'PortBindings' => [
               '80/tcp' => [['HostPort' => '8080']]
           ]
       ]);

$container = $client->container()->create($config);
$client->container()->start($container['Id']);

echo "Container created with ID: {$container['Id']}\n";
```

### Stopping and Removing a Container
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createUnix();
$containerId = 'my-container';

if ($client->container()->exists($containerId)) {
    $client->container()->stop($containerId);
    $client->container()->remove($containerId);
    echo "Container {$containerId} stopped and removed\n";
} 