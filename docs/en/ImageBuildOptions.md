# ImageBuildOptions Class

`ImageBuildOptions` is a class for type-safe configuration of Docker image build parameters. This class allows you to create and validate configurations for the Docker image build API.

## Purpose

The `ImageBuildOptions` class addresses the following tasks:

- **Parameter Typing** - provides clearly defined types for all build parameters
- **Parameter Validation** - checks the correctness of values when they are set
- **Documentation** - provides a complete description of all possible options and their purpose
- **Flexibility** - supports all Docker API functions for building images
- **Security** - prevents errors when forming requests to the Docker API

## Main Features

- Support for all Docker API parameters for building images
- Ability to set both the path to Dockerfile and its content directly
- Complete validation of all parameters with detailed error messages
- Possibility to dynamically create Dockerfile based on templates or program logic

## Usage Examples

### Basic Example

```php
use Sangezar\DockerClient\Config\ImageBuildOptions;
use Sangezar\DockerClient\DockerClient;

$docker = DockerClient::createUnix();

// Basic settings for image building
$options = ImageBuildOptions::create()
    ->setTag('myapp:latest')
    ->setContext('./app')
    ->setDockerfilePath('Dockerfile')
    ->setNoCache(true);

// Building the image
$result = $docker->image()->buildWithOptions($options);
```

### Using Custom Dockerfile Content

```php
$dockerfileContent = <<<DOCKERFILE
FROM php:8.2-fpm-alpine
WORKDIR /var/www/html
COPY . /var/www/html/
RUN docker-php-ext-install pdo pdo_mysql
EXPOSE 9000
CMD ["php-fpm"]
DOCKERFILE;

$options = ImageBuildOptions::create()
    ->setTag('myphpapp:1.0')
    ->setContext('./app')
    ->setDockerfileContent($dockerfileContent)
    ->addBuildArg('PHP_VERSION', '8.2')
    ->addLabel('maintainer', 'devops@example.com');

$result = $docker->image()->buildWithOptions($options);
```

### Advanced Example with Many Parameters

```php
$options = ImageBuildOptions::create()
    ->setTag('myproject/webapp:2.0')
    ->setContext('./project')
    ->setDockerfilePath('docker/Dockerfile.production')
    ->setNoCache(true)
    ->setQuiet(false)
    ->setRemoveIntermediateContainers(true)
    ->setForceRemoveIntermediateContainers(false)
    ->setPlatform('linux/amd64')
    ->addBuildArg('NODE_ENV', 'production')
    ->addBuildArg('APP_VERSION', '2.0.0')
    ->addLabel('org.opencontainers.image.source', 'https://github.com/example/project')
    ->addLabel('org.opencontainers.image.created', date('c'))
    ->setTarget('production')
    ->setSquash(true)
    ->setPullPolicy(ImageBuildOptions::PULL_ALWAYS)
    ->setNetwork('host')
    ->addExtraHost('db.local', '172.17.0.2')
    ->addCacheFrom('myproject/webapp:1.0')
    ->addSecret('npmrc', '/path/to/.npmrc')
    ->addSshSource('default', '/path/to/ssh-agent.sock');

$result = $docker->image()->buildWithOptions($options);
```

### Usage in Docker Cluster

```php
use Sangezar\DockerClient\DockerClient;
use Sangezar\DockerClient\Cluster\DockerCluster;
use Sangezar\DockerClient\Cluster\Operations\ImageOperations;
use Sangezar\DockerClient\Config\ImageBuildOptions;

// Cluster initialization
$cluster = new DockerCluster();
$cluster->addNode('node1', DockerClient::createUnix());
$cluster->addNode('node2', new DockerClient($remoteConfig));

// Creating operations for images
$imageOps = new ImageOperations($cluster->getNodes());

// Build configuration
$options = ImageBuildOptions::create()
    ->setTag('myservice:1.0')
    ->setContext('./service')
    ->setDockerfilePath('Dockerfile')
    ->addBuildArg('VERSION', '1.0.0');

// Building image on all cluster nodes
$results = $imageOps->buildWithOptions($options);
```

## Available Methods

| Method | Description |
|-------|------|
| `setTag(string $tag)` | Sets the image name and tag |
| `setContext(string $contextPath)` | Sets the path to the build context |
| `setDockerfilePath(string $path)` | Sets the path to Dockerfile in the context |
| `setDockerfileContent(string $content)` | Sets the Dockerfile content directly |
| `setNoCache(bool $noCache)` | Disables cache usage during build |
| `setQuiet(bool $quiet)` | Disables detailed build information output |
| `setRemoveIntermediateContainers(bool $remove)` | Enables removal of intermediate containers |
| `setForceRemoveIntermediateContainers(bool $force)` | Enables forced removal of intermediate containers |
| `addBuildArg(string $name, string $value)` | Adds a build argument |
| `addLabel(string $name, string $value)` | Adds a label to the image |
| `setTarget(string $target)` | Sets the target stage of a multi-stage Dockerfile |
| `setPlatform(string $platform)` | Sets the platform for building (os/arch) |
| `addExtraHost(string $hostname, string $ip)` | Adds an entry to /etc/hosts in the container |
| `setSquash(bool $squash)` | Enables squashing of image layers |
| `setNetwork(string $network)` | Sets the network for build containers |
| `addSecret(string $id, string $source)` | Adds a secret for building |
| `addSshSource(string $id, string $source)` | Adds an SSH source for building |
| `addExtraContext(string $name, string $source)` | Adds an additional build context |
| `setPullPolicy(string $pull)` | Sets the strategy for pulling base images |
| `addCacheFrom(string $image)` | Adds an image to use as cache |
| `setOutputType(string $outputType)` | Sets the build output type |
| `toArrays()` | Converts settings to arrays for the Docker API |

## Error Handling

The class uses an exception system to report errors:

- `InvalidParameterValueException` - invalid parameter value
- `InvalidConfigurationException` - invalid configuration (for example, missing required parameters)

Example:

```php
try {
    $options = ImageBuildOptions::create()
        ->setTag('myapp:latest')
        ->setContext('./app')
        ->setPlatform('invalid-platform'); // Will throw an exception
} catch (InvalidParameterValueException $e) {
    echo "Parameter error: " . $e->getMessage();
}
```

## Integration with Docker API

The `ImageBuildOptions` class integrates with the Docker API through methods:

- `Image::buildWithOptions()` for a single Docker client
- `ImageOperations::buildWithOptions()` for operations in a cluster

These methods accept an `ImageBuildOptions` object and use it to form correct requests to the Docker API. 