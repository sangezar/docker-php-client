# System Class Documentation

## Description
`System` is a class for working with Docker system functions through the API. It provides methods for getting information about the Docker system, checking authentication, monitoring events, and managing system resources.

## Namespace
`Sangezar\DockerClient\Api`

## Inheritance
The `System` class inherits from `AbstractApi` and implements the `SystemInterface` interface.

## Constants

### Event Types
```php
public const EVENT_TYPE_CONTAINER = 'container';
public const EVENT_TYPE_IMAGE = 'image';
public const EVENT_TYPE_VOLUME = 'volume';
public const EVENT_TYPE_NETWORK = 'network';
public const EVENT_TYPE_DAEMON = 'daemon';
public const EVENT_TYPE_PLUGIN = 'plugin';
public const EVENT_TYPE_SERVICE = 'service';
public const EVENT_TYPE_NODE = 'node';
public const EVENT_TYPE_SECRET = 'secret';
public const EVENT_TYPE_CONFIG = 'config';
```

### Container Event Types
```php
public const CONTAINER_EVENT_ATTACH = 'attach';
public const CONTAINER_EVENT_COMMIT = 'commit';
public const CONTAINER_EVENT_COPY = 'copy';
public const CONTAINER_EVENT_CREATE = 'create';
public const CONTAINER_EVENT_DESTROY = 'destroy';
public const CONTAINER_EVENT_DETACH = 'detach';
public const CONTAINER_EVENT_DIE = 'die';
public const CONTAINER_EVENT_EXEC_CREATE = 'exec_create';
public const CONTAINER_EVENT_EXEC_DETACH = 'exec_detach';
public const CONTAINER_EVENT_EXEC_START = 'exec_start';
public const CONTAINER_EVENT_EXEC_DIE = 'exec_die';
public const CONTAINER_EVENT_EXPORT = 'export';
public const CONTAINER_EVENT_HEALTH_STATUS = 'health_status';
public const CONTAINER_EVENT_KILL = 'kill';
public const CONTAINER_EVENT_OOM = 'oom';
public const CONTAINER_EVENT_PAUSE = 'pause';
public const CONTAINER_EVENT_RENAME = 'rename';
public const CONTAINER_EVENT_RESIZE = 'resize';
public const CONTAINER_EVENT_RESTART = 'restart';
public const CONTAINER_EVENT_START = 'start';
public const CONTAINER_EVENT_STOP = 'stop';
public const CONTAINER_EVENT_TOP = 'top';
public const CONTAINER_EVENT_UNPAUSE = 'unpause';
public const CONTAINER_EVENT_UPDATE = 'update';
```

### Image Event Types
```php
public const IMAGE_EVENT_DELETE = 'delete';
public const IMAGE_EVENT_IMPORT = 'import';
public const IMAGE_EVENT_LOAD = 'load';
public const IMAGE_EVENT_PULL = 'pull';
public const IMAGE_EVENT_PUSH = 'push';
public const IMAGE_EVENT_SAVE = 'save';
public const IMAGE_EVENT_TAG = 'tag';
public const IMAGE_EVENT_UNTAG = 'untag';
```

### Volume Event Types
```php
public const VOLUME_EVENT_CREATE = 'create';
public const VOLUME_EVENT_DESTROY = 'destroy';
public const VOLUME_EVENT_MOUNT = 'mount';
public const VOLUME_EVENT_UNMOUNT = 'unmount';
```

### Network Event Types
```php
public const NETWORK_EVENT_CREATE = 'create';
public const NETWORK_EVENT_CONNECT = 'connect';
public const NETWORK_EVENT_DESTROY = 'destroy';
public const NETWORK_EVENT_DISCONNECT = 'disconnect';
public const NETWORK_EVENT_REMOVE = 'remove';
```

## Methods

### version
```php
public function version(): array
```
Gets information about the Docker version.

#### Returns:
- Array with details about the Docker version, including API version, Git commit version, OS/architecture, etc.

#### Exceptions:
- `OperationFailedException` - if the request fails

### info
```php
public function info(): array
```
Gets system-wide information.

#### Returns:
- Array with system information, including the number of containers, images, server version, etc.

#### Exceptions:
- `OperationFailedException` - if the request fails

### auth
```php
public function auth(array $authConfig): array
```
Checks the authentication configuration.

#### Parameters:
- `$authConfig` - Authentication configuration:
  - `username` (string) - Username for authentication in the registry
  - `password` (string) - Password for authentication in the registry
  - `email` (string) - Email for authentication in the registry (optional)
  - `serveraddress` (string) - Registry address (e.g., https://index.docker.io/v1/)
  - `identitytoken` (string) - Identity token for the registry (optional)
  - `registrytoken` (string) - Registry token (optional)

#### Returns:
- Array with authentication result with status and token, if successful

#### Exceptions:
- `MissingRequiredParameterException` - if required parameters are missing
- `OperationFailedException` - if authentication fails

### ping
```php
public function ping(): bool
```
Checks the availability of the Docker server.

#### Returns:
- `true` if the Docker server is available, `false` if not

### events
```php
public function events(array $filters = []): array
```
Gets events from the server in real-time.

#### Parameters:
- `$filters` - Filters to apply to events:
  - `config` - object with attributes (optional)
  - `type` - array of event types (optional, use EVENT_TYPE_* constants)
  - `until` - timestamp (optional)
  - `since` - timestamp (optional)

#### Returns:
- Array of events

#### Exceptions:
- `InvalidParameterValueException` - if some filter values are invalid
- `OperationFailedException` - if the request fails

#### Example:
```php
// Get only container events
$events = $system->events(['type' => [System::EVENT_TYPE_CONTAINER]]);

// Get events for containers and networks from yesterday
$events = $system->events([
    'type' => [System::EVENT_TYPE_CONTAINER, System::EVENT_TYPE_NETWORK],
    'since' => strtotime('-1 day')
]);
```

### dataUsage
```php
public function dataUsage(): array
```
Gets information about data usage.

#### Returns:
- Array with information about data usage, including images, containers, volumes, etc.

#### Exceptions:
- `OperationFailedException` - if the request fails

### usage
```php
public function usage(): array
```
Equivalent to the dataUsage() method, gets information about system usage.

#### Returns:
- Array with information about system usage

#### Exceptions:
- `OperationFailedException` - if the request fails

### prune
```php
public function prune(array $options = []): array
```
Removes unused data (containers, networks, images, volumes).

#### Parameters:
- `$options` - Cleanup options:
  - `volumes` (bool) - Remove unused volumes
  - `networks` (bool) - Remove unused networks
  - `containers` (bool) - Remove stopped containers
  - `images` (bool) - Remove unused images
  - `builder` (bool) - Clean up build cache
  - `filters` (array) - Filters to apply

#### Returns:
- Array with cleanup result, including space freed

#### Exceptions:
- `InvalidParameterValueException` - if options are invalid
- `OperationFailedException` - if cleanup fails

## Usage Examples

### Getting Information About Docker
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createUnix();

// Get information about Docker version
$version = $client->system()->version();
echo "Docker Engine version: {$version['Version']}\n";
echo "API version: {$version['ApiVersion']}\n";

// Get system information
$info = $client->system()->info();
echo "Number of containers: {$info['Containers']}\n";
echo "Number of images: {$info['Images']}\n";
echo "OS/Architecture: {$info['OperatingSystem']}/{$info['Architecture']}\n";
```

### Checking Authentication
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createUnix();

$auth = $client->system()->auth([
    'username' => 'myusername',
    'password' => 'mypassword',
    'serveraddress' => 'https://index.docker.io/v1/'
]);

if (isset($auth['Status']) && $auth['Status'] === 'Login Succeeded') {
    echo "Authentication successful\n";
} else {
    echo "Authentication failed\n";
}
```

### Monitoring Events
```php
use Sangezar\DockerClient\DockerClient;
use Sangezar\DockerClient\Api\System;

$client = DockerClient::createUnix();

// Get container events from the last hour
$events = $client->system()->events([
    'type' => [System::EVENT_TYPE_CONTAINER],
    'since' => strtotime('-1 hour')
]);

foreach ($events as $event) {
    echo "Event: {$event['Type']}/{$event['Action']} for {$event['Actor']['ID']}\n";
    echo "Time: " . date('Y-m-d H:i:s', $event['time']) . "\n";
}
```

### Cleaning Up Unused Resources
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createUnix();

// Clean up all unused resources (containers, networks, images, volumes)
$pruneResult = $client->system()->prune([
    'volumes' => true,
    'images' => true
]);

echo "Space freed: " . ($pruneResult['SpaceReclaimed'] ?? 0) . " bytes\n";
``` 