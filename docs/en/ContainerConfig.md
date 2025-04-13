# ContainerConfig Class Documentation

## Description
`ContainerConfig` is a class for configuring Docker containers. It allows you to set the image, name, environment variables, commands, volumes, ports, labels, and other container parameters.

## Namespace
`Sangezar\DockerClient\Config`

## Methods

### create
```php
public static function create(): self
```
Creates a new container configuration instance.

#### Returns:
- A new `ContainerConfig` instance

### setImage
```php
public function setImage(string $image): self
```
Sets the image for the container.

#### Parameters:
- `$image` - Image name (with tag)

#### Returns:
- Current instance for chained calls

#### Exceptions:
- `InvalidParameterValueException` - if the image name is empty

### setName
```php
public function setName(string $name): self
```
Sets the name for the container.

#### Parameters:
- `$name` - Container name

#### Returns:
- Current instance for chained calls

#### Exceptions:
- `InvalidParameterValueException` - if the name is invalid

### addEnv
```php
public function addEnv(string $name, string $value): self
```
Adds an environment variable.

#### Parameters:
- `$name` - Variable name
- `$value` - Variable value

#### Returns:
- Current instance for chained calls

### setCmd
```php
public function setCmd(array $cmd): self
```
Sets the command to run.

#### Parameters:
- `$cmd` - Command as an array of arguments

#### Returns:
- Current instance for chained calls

### addVolume
```php
public function addVolume(string $hostPath, string $containerPath, string $mode = 'rw'): self
```
Adds a volume.

#### Parameters:
- `$hostPath` - Path on the host
- `$containerPath` - Path in the container
- `$mode` - Access mode ('ro', 'rw')

#### Returns:
- Current instance for chained calls

### addPort
```php
public function addPort(int $hostPort, int $containerPort, string $protocol = 'tcp'): self
```
Adds a port mapping.

#### Parameters:
- `$hostPort` - Port on the host
- `$containerPort` - Port in the container
- `$protocol` - Protocol ('tcp', 'udp')

#### Returns:
- Current instance for chained calls

### addLabel
```php
public function addLabel(string $name, string $value): self
```
Adds a label.

#### Parameters:
- `$name` - Label name
- `$value` - Label value

#### Returns:
- Current instance for chained calls

### toArray
```php
public function toArray(): array
```
Converts the configuration to an array for the Docker API.

#### Returns:
- Array with configuration for the Docker API

#### Exceptions:
- `InvalidConfigurationException` - if the configuration is invalid

### setWorkingDir
```php
public function setWorkingDir(string $dir): self
```
Sets the working directory for the container.

#### Parameters:
- `$dir` - Path to the working directory

#### Returns:
- Current instance for chained calls

### setUser
```php
public function setUser(string $user): self
```
Sets the user for the container.

#### Parameters:
- `$user` - Username or UID

#### Returns:
- Current instance for chained calls

### setNetworkMode
```php
public function setNetworkMode(string $mode): self
```
Sets the network mode for the container.

#### Parameters:
- `$mode` - Network mode ('bridge', 'host', 'none', 'container:[name|id]')

#### Returns:
- Current instance for chained calls

### addNetworkConnection
```php
public function addNetworkConnection(string $networkName, array $config = []): self
```
Adds a network connection.

#### Parameters:
- `$networkName` - Network name
- `$config` - Connection configuration (aliases, IPAddress, etc.)

#### Returns:
- Current instance for chained calls

### setTty
```php
public function setTty(bool $enable = true): self
```
Sets the TTY option.

#### Parameters:
- `$enable` - Enable TTY (default true)

#### Returns:
- Current instance for chained calls

### setOpenStdin
```php
public function setOpenStdin(bool $enable = true): self
```
Sets the option to open stdin.

#### Parameters:
- `$enable` - Enable stdin opening (default true)

#### Returns:
- Current instance for chained calls

### setMemoryLimit
```php
public function setMemoryLimit(int $memoryBytes): self
```
Sets the memory limit for the container.

#### Parameters:
- `$memoryBytes` - Number of memory bytes

#### Returns:
- Current instance for chained calls

### setCpuShares
```php
public function setCpuShares(int $cpuShares): self
```
Sets the CPU share for the container.

#### Parameters:
- `$cpuShares` - CPU share (relative weight)

#### Returns:
- Current instance for chained calls

### setRestartPolicy
```php
public function setRestartPolicy(string $policy, int $maxRetryCount = 0): self
```
Sets the container restart policy.

#### Parameters:
- `$policy` - Restart policy ('no', 'always', 'unless-stopped', 'on-failure')
- `$maxRetryCount` - Maximum retry count (for 'on-failure')

#### Returns:
- Current instance for chained calls

#### Exceptions:
- `InvalidParameterValueException` - if the policy is invalid

## Usage Examples

### Creating Basic Configuration
```php
use Sangezar\DockerClient\Config\ContainerConfig;

// Creating container configuration
$config = ContainerConfig::create()
    ->setImage('nginx:latest')
    ->setName('my-nginx')
    ->addEnv('NGINX_HOST', 'example.com')
    ->addEnv('NGINX_PORT', '80')
    ->addPort(8080, 80)
    ->addPort(8443, 443, 'tcp');
```

### Configuring Volumes and Labels
```php
use Sangezar\DockerClient\Config\ContainerConfig;

$config = ContainerConfig::create()
    ->setImage('php:8.2-fpm')
    ->setName('php-app');

// Adding volumes
$config->addVolume('/local/path/app', '/var/www/app', 'rw')
       ->addVolume('/local/path/config', '/etc/nginx/conf.d', 'ro');

// Adding labels
$config->addLabel('com.example.environment', 'production')
       ->addLabel('com.example.version', '1.0.0')
       ->addLabel('maintainer', 'team@example.com');
```

### Configuring Resources and Network
```php
use Sangezar\DockerClient\Config\ContainerConfig;

$config = ContainerConfig::create()
    ->setImage('mysql:8.0')
    ->setName('db-server');

// Configuring resources
$config->setMemoryLimit(512 * 1024 * 1024) // 512 MB
       ->setCpuShares(512)
       ->setRestartPolicy('always');

// Configuring network
$config->setNetworkMode('bridge')
       ->addNetworkConnection('app-network', [
           'Aliases' => ['database', 'mysql'],
           'IPAddress' => '172.18.0.10'
       ]);
```

### Configuring User and Working Directory
```php
use Sangezar\DockerClient\Config\ContainerConfig;

$config = ContainerConfig::create()
    ->setImage('node:18')
    ->setName('node-app')
    ->setUser('node')
    ->setWorkingDir('/app')
    ->setCmd(['npm', 'start'])
    ->setTty(true)
    ->setOpenStdin(true);

// Converting configuration to array for Docker API
$apiConfig = $config->toArray();
```

### Comprehensive Example
```php
use Sangezar\DockerClient\Config\ContainerConfig;
use Sangezar\DockerClient\DockerClient;

// Creating Docker client
$client = DockerClient::createUnix();

// Creating container configuration
$config = ContainerConfig::create()
    ->setImage('wordpress:latest')
    ->setName('my-wordpress')
    ->addEnv('WORDPRESS_DB_HOST', 'db-server')
    ->addEnv('WORDPRESS_DB_USER', 'wordpress')
    ->addEnv('WORDPRESS_DB_PASSWORD', 'secret')
    ->addPort(8000, 80)
    ->addVolume('/local/path/wordpress', '/var/www/html', 'rw')
    ->setRestartPolicy('unless-stopped')
    ->setNetworkMode('bridge')
    ->addNetworkConnection('wordpress-network');

// Creating container
$container = $client->container()->create($config);
echo "Container created with ID: " . $container['Id'] . "\n";

// Starting container
$client->container()->start($container['Id']);
echo "Container started\n";
``` 