# 🐳 Docker PHP Client

[![Latest Version on Packagist](https://img.shields.io/packagist/v/sangezar/docker-php-client.svg?style=flat-square)](https://packagist.org/packages/sangezar/docker-php-client)
[![Total Downloads](https://img.shields.io/packagist/dt/sangezar/docker-php-client.svg?style=flat-square)](https://packagist.org/packages/sangezar/docker-php-client)
[![License](https://img.shields.io/packagist/l/sangezar/docker-php-client.svg?style=flat-square)](LICENSE)

A modern, powerful, and elegant Docker API client for PHP applications with cluster support.

## ✨ Features

- 🚀 Full Docker API support
- 🔄 Container management (create, inspect, start, stop, remove)
- 🖼️ Image operations (build, pull, push, tag)
- 🌐 Network configuration & management
- 💾 Volume creation & management
- 🔍 System information & events
- 🔐 TLS authentication support
- 🔌 Unix socket & TCP connection support
- 📦 PSR-18 compatible HTTP client
- 🧩 Fluent interfaces for all configurations
- 🔧 Comprehensive exception handling
- 🏘️ Docker Swarm & cluster operations
- ⚡ Asynchronous operations support

## 📋 Requirements

- PHP 8.2 or higher
- ext-json
- PSR-18 HTTP Client
- Docker Engine API v1.41+

## 🛠️ Installation

Install the package via Composer:

```bash
composer require sangezar/docker-php-client
```

## 🚀 Quick Start

### Connect to Docker via Unix Socket (Default)

```php
use Sangezar\DockerClient\DockerClient;

// Connect to local Docker daemon through unix socket
$client = DockerClient::createUnix();

// List all containers
$containers = $client->container()->list(['all' => true]);
```

### Connect to Docker via TCP

```php
use Sangezar\DockerClient\DockerClient;

// Connect to remote Docker daemon through TCP
$client = DockerClient::createTcp('tcp://docker-host:2375');

// Get system information
$info = $client->system()->info();
```

### Connect to Docker via TCP with TLS

```php
use Sangezar\DockerClient\DockerClient;

// Connect to remote Docker daemon with TLS
$client = DockerClient::createTcp(
    'tcp://docker-host:2376',
    '/path/to/cert.pem',
    '/path/to/key.pem',
    '/path/to/ca.pem'
);
```

## 📊 Working with Containers

### Create and Run a Container

```php
use Sangezar\DockerClient\Config\ContainerConfig;

// Create container configuration
$config = ContainerConfig::create()
    ->setImage('nginx:latest')
    ->setName('my-nginx')
    ->exposePorts(80, 443)
    ->addEnv('NGINX_HOST', 'example.com')
    ->addVolume('/var/www', '/usr/share/nginx/html')
    ->setRestartPolicy('always');

// Create container
$container = $client->container()->create($config);

// Start container
$client->container()->start($container['Id']);
```

### List Containers

```php
// List all running containers
$runningContainers = $client->container()->list();

// List all containers (including stopped ones)
$allContainers = $client->container()->list(['all' => true]);

// Filter containers
$filtered = $client->container()->list([
    'filters' => [
        'status' => ['running'],
        'label' => ['com.example.group=web']
    ]
]);
```

### Container Lifecycle Management

```php
$containerId = 'my-container';

// Inspect container
$info = $client->container()->inspect($containerId);

// Stop container
$client->container()->stop($containerId, 10); // 10 seconds timeout

// Restart container
$client->container()->restart($containerId);

// Remove container
$client->container()->remove($containerId, true, true); // force, remove volumes
```

## 🖼️ Working with Images

```php
// Pull an image
$client->image()->create('nginx', 'latest');

// List images
$images = $client->image()->list(['all' => true]);

// Build an image
$buildOptions = new ImageBuildOptions();
$buildOptions->setTag('my-app:latest')
    ->setContext('/path/to/context')
    ->setDockerfile('Dockerfile.prod');

$client->image()->buildWithOptions($buildOptions);

// Tag an image
$client->image()->tag('my-app:latest', 'registry.example.com/my-app', 'v1.0');

// Push an image
$client->image()->push('registry.example.com/my-app:v1.0');
```

## 🌐 Working with Networks

```php
use Sangezar\DockerClient\Config\NetworkConfig;

// Create a network
$networkConfig = NetworkConfig::create()
    ->setName('app-network')
    ->setDriver('bridge')
    ->addSubnet('172.28.0.0/16', '172.28.0.1')
    ->addLabel('environment', 'production');

$network = $client->network()->create($networkConfig);

// Connect a container to network
$client->network()->connect('app-network', 'my-container', [
    'Aliases' => ['web-server']
]);

// List networks
$networks = $client->network()->list();
```

## 💾 Working with Volumes

```php
use Sangezar\DockerClient\Config\VolumeConfig;

// Create a volume
$volumeConfig = VolumeConfig::create()
    ->setName('data-volume')
    ->setDriver('local')
    ->addLabel('backup', 'daily');

$volume = $client->volume()->create($volumeConfig);

// Inspect volume
$volumeInfo = $client->volume()->inspect('data-volume');

// List volumes
$volumes = $client->volume()->list();
```

## 🏘️ Working with Docker Clusters

```php
use Sangezar\DockerClient\Cluster\DockerCluster;

// Create a cluster
$cluster = new DockerCluster();

// Add nodes to cluster
$cluster->addNode('node1', DockerClient::createTcp('tcp://node1:2375'));
$cluster->addNode('node2', DockerClient::createTcp('tcp://node2:2375'));

// Get all containers across the cluster
$allContainers = $cluster->nodes()->containers()->list(['all' => true]);

// Filter nodes by name pattern
$webNodes = $cluster->getNodeCollection()->filter(function($client, $name) {
    return str_contains($name, 'web');
});

// Create containers on specific nodes
$webNodes->containers()->create($containerConfig);
```

## 🧩 Advanced Usage

### Custom HTTP Client

```php
use Sangezar\DockerClient\Config\ClientConfig;
use Sangezar\DockerClient\DockerClient;
use GuzzleHttp\Client;

// Create custom HTTP client
$httpClient = new Client([
    'timeout' => 30,
    'connect_timeout' => 5
]);

// Create client config
$config = new ClientConfig();
$config->setEndpoint('unix:///var/run/docker.sock');

// Create Docker client with custom HTTP client
$client = new DockerClient($config, $httpClient);
```

### Handle Events Stream

```php
// Get Docker events stream
$events = $client->system()->events([
    'filters' => [
        'type' => ['container'],
        'event' => ['start', 'stop', 'die']
    ]
]);

// Process events
foreach ($events as $event) {
    $type = $event['Type'];
    $action = $event['Action'];
    $id = $event['Actor']['ID'];
    
    echo "Event: {$type} {$action} on {$id}\n";
}
```

## 📚 Documentation

For full documentation, please visit [the documentation site](https://sangezar.github.io/docker-php-client/).

Documentation is available in:
- [English](https://sangezar.github.io/docker-php-client/en/)
- [Ukrainian](https://sangezar.github.io/docker-php-client/ua/)

## 🧪 Testing

```bash
composer test
```

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## ⭐ Star the Project

If you find this client useful, please consider giving it a star on GitHub! It helps to increase the visibility of the project and motivate further development.

## 🙏 Credits

- [Docker API Documentation](https://docs.docker.com/engine/api/)
