# DockerClient Class Documentation

## Description
`DockerClient` is the main class for interacting with the Docker API. It provides a convenient interface for working with various Docker components such as containers, images, networks, volumes, and system functions.

## Namespace
`Sangezar\DockerClient`

## Methods

### Constructor
```php
public function __construct(
    ?ClientConfig $config = null,
    ?ClientInterface $httpClient = null,
    string $apiVersion = 'v1.47'
)
```

#### Parameters:
- `$config` - (optional) `ClientConfig` client configuration object
- `$httpClient` - (optional) HTTP client implementing the `ClientInterface` interface
- `$apiVersion` - Docker API version (default `v1.47`)

### Static Creation Methods

#### create
```php
public static function create(?ClientConfig $config = null): self
```
Creates a new client instance.

##### Parameters:
- `$config` - (optional) `ClientConfig` client configuration object

##### Returns:
- New `DockerClient` instance

#### createTcp
```php
public static function createTcp(
    string $host,
    ?string $certPath = null,
    ?string $keyPath = null,
    ?string $caPath = null
): self
```
Creates a client for TCP connection to the Docker API.

##### Parameters:
- `$host` - Docker API host
- `$certPath` - (optional) Path to certificate
- `$keyPath` - (optional) Path to key
- `$caPath` - (optional) Path to CA certificate

##### Returns:
- New `DockerClient` instance configured for TCP connection

#### createUnix
```php
public static function createUnix(string $socketPath = '/var/run/docker.sock'): self
```
Creates a client for connection via Unix socket.

##### Parameters:
- `$socketPath` - Path to Unix socket (default `/var/run/docker.sock`)

##### Returns:
- New `DockerClient` instance configured for Unix socket connection

### API Access Methods

#### container
```php
public function container(): ContainerInterface
```
Returns API for working with containers.

##### Returns:
- Object implementing the `ContainerInterface` interface

#### image
```php
public function image(): ImageInterface
```
Returns API for working with images.

##### Returns:
- Object implementing the `ImageInterface` interface

#### system
```php
public function system(): SystemInterface
```
Returns API for working with Docker system functions.

##### Returns:
- Object implementing the `SystemInterface` interface

#### network
```php
public function network(): NetworkInterface
```
Returns API for working with Docker networks.

##### Returns:
- Object implementing the `NetworkInterface` interface

#### volume
```php
public function volume(): VolumeInterface
```
Returns API for working with Docker volumes.

##### Returns:
- Object implementing the `VolumeInterface` interface

## Usage Examples

### Connection via Unix Socket (Most Common Method)
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createUnix();
```

### Connection via TCP
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createTcp('tcp://docker-host:2375');
```

### Connection via TCP with TLS
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createTcp(
    'tcp://docker-host:2376',
    '/path/to/cert.pem',
    '/path/to/key.pem',
    '/path/to/ca.pem'
);
```

### Using Different Client APIs
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createUnix();

// Getting a list of containers
$containers = $client->container()->list();

// Getting a list of images
$images = $client->image()->list();

// Getting information about the Docker system
$info = $client->system()->info();

// Working with networks
$networks = $client->network()->list();

// Working with volumes
$volumes = $client->volume()->list();
``` 