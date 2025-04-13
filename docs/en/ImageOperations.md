# ImageOperations Class Documentation

## Description
`ImageOperations` is a class that provides operations with Docker images on all cluster nodes simultaneously. The class allows you to get a list of images, build, create (pull), inspect, push to registry, tag, and delete Docker images on all cluster nodes.

## Namespace
`Sangezar\DockerClient\Cluster\Operations`

## Inheritance
The class extends `AbstractOperations` and inherits all its methods and properties.

## Methods

### list
```php
public function list(array $parameters = []): array
```
Gets a list of images from all cluster nodes.

#### Parameters:
- `$parameters` - Array of filtering parameters:
  - `all` (bool): Show all images (default false)
  - `filters` (array): Filters for searching images

#### Returns:
- Array where keys are node names and values are operation results on each node

#### Exceptions:
- `InvalidParameterValueException` - if invalid filtering parameters are provided

### build
```php
public function build(array $parameters = [], array $config = []): array
```
Builds an image on all cluster nodes.

#### Parameters:
- `$parameters` - Build parameters:
  - `t` or `tag` (string): Image tag (required)
  - Other Docker API parameters for building
- `$config` - Additional configuration:
  - `context` (string): Path to the build context

#### Returns:
- Array where keys are node names and values are operation results on each node

#### Exceptions:
- `InvalidParameterValueException` - if invalid parameters are provided
- `InvalidConfigurationException` - if the configuration is invalid
- `MissingRequiredParameterException` - if the required tag parameter is missing

### buildWithOptions
```php
public function buildWithOptions(\Sangezar\DockerClient\Config\ImageBuildOptions $options): array
```
Builds an image on all cluster nodes using a build options object.

#### Parameters:
- `$options` - Image build options object

#### Returns:
- Array where keys are node names and values are operation results on each node

#### Exceptions:
- `InvalidParameterValueException` - if invalid parameters are provided
- `InvalidConfigurationException` - if the configuration is invalid

### create
```php
public function create(string $fromImage, ?string $tag = null): array
```
Creates an image by pulling it from the registry, on all cluster nodes.

#### Parameters:
- `$fromImage` - Name of the image to pull
- `$tag` - Image tag (default "latest")

#### Returns:
- Array where keys are node names and values are operation results on each node

#### Exceptions:
- `MissingRequiredParameterException` - if the image name is empty
- `InvalidParameterValueException` - if invalid parameters are provided

### inspect
```php
public function inspect(string $name): array
```
Gets detailed information about an image on all cluster nodes.

#### Parameters:
- `$name` - Image name or ID

#### Returns:
- Array where keys are node names and values are operation results on each node

#### Exceptions:
- `MissingRequiredParameterException` - if the image name is empty

### history
```php
public function history(string $name): array
```
Gets the image history on all cluster nodes.

#### Parameters:
- `$name` - Image name or ID

#### Returns:
- Array where keys are node names and values are operation results on each node

#### Exceptions:
- `MissingRequiredParameterException` - if the image name is empty

### push
```php
public function push(string $name, array $parameters = []): array
```
Pushes an image to the registry from all cluster nodes.

#### Parameters:
- `$name` - Name of the image to push
- `$parameters` - Additional parameters

#### Returns:
- Array where keys are node names and values are operation results on each node

#### Exceptions:
- `MissingRequiredParameterException` - if the image name is empty

### tag
```php
public function tag(string $name, string $repo, ?string $tag = null): array
```
Tags an image on all cluster nodes.

#### Parameters:
- `$name` - Name or ID of the source image
- `$repo` - Repository for the new tag
- `$tag` - New tag (if null, "latest" is used)

#### Returns:
- Array where keys are node names and values are operation results on each node

#### Exceptions:
- `MissingRequiredParameterException` - if the image name or repository is empty
- `InvalidParameterValueException` - if invalid parameters are provided

### remove
```php
public function remove(string $name, bool $force = false, bool $noprune = false): array
```
Removes an image on all cluster nodes.

#### Parameters:
- `$name` - Image name or ID
- `$force` - Force image removal (default false)
- `$noprune` - Do not delete intermediate images (default false)

#### Returns:
- Array where keys are node names and values are operation results on each node

#### Exceptions:
- `MissingRequiredParameterException` - if the image name is empty

### search
```php
public function search(string $term): array
```
Searches for images in Docker Hub from all cluster nodes.

#### Parameters:
- `$term` - Search term

#### Returns:
- Array where keys are node names and values are operation results on each node

#### Exceptions:
- `MissingRequiredParameterException` - if the search term is empty

### prune
```php
public function prune(array $parameters = []): array
```
Removes unused images on all cluster nodes.

#### Parameters:
- `$parameters` - Parameters for removal:
  - `filters` (array): Filters for selecting images to remove

#### Returns:
- Array where keys are node names and values are operation results on each node

### exists
```php
public function exists(string $name): array
```
Checks if an image exists on all cluster nodes.

#### Parameters:
- `$name` - Image name or ID

#### Returns:
- Array where keys are node names and values are boolean results (true if the image exists, false if not)

#### Exceptions:
- `MissingRequiredParameterException` - if the image name is empty

### existsOnAllNodes
```php
public function existsOnAllNodes(string $name): bool
```
Checks if an image exists on all cluster nodes.

#### Parameters:
- `$name` - Image name or ID

#### Returns:
- `true` if the image exists on all nodes, `false` if it's missing on at least one node

#### Exceptions:
- `MissingRequiredParameterException` - if the image name is empty

### getNodesWithImage
```php
public function getNodesWithImage(string $name): array
```
Gets a list of nodes where the image exists.

#### Parameters:
- `$name` - Image name or ID

#### Returns:
- Array with names of nodes where the image exists

#### Exceptions:
- `MissingRequiredParameterException` - if the image name is empty

### pull
```php
public function pull(string $name, array $parameters = []): array
```
Pulls an image from the registry to all cluster nodes.

#### Parameters:
- `$name` - Name of the image to pull
- `$parameters` - Additional parameters for pulling

#### Returns:
- Array where keys are node names and values are operation results on each node

#### Exceptions:
- `MissingRequiredParameterException` - if the image name is empty
- `InvalidParameterValueException` - if invalid parameters are provided

### load
```php
public function load(string $imageArchive): array
```
Loads an image from an archive to all cluster nodes.

#### Parameters:
- `$imageArchive` - Path to the image archive

#### Returns:
- Array where keys are node names and values are operation results on each node

#### Exceptions:
- `MissingRequiredParameterException` - if the archive path is empty
- `FileNotFoundException` - if the archive file is not found

### save
```php
public function save($names, string $outputFile): array
```
Saves images to an archive from all cluster nodes.

#### Parameters:
- `$names` - Name or array of image names to save
- `$outputFile` - Path to the output file

#### Returns:
- Array where keys are node names and values are operation results on each node

#### Exceptions:
- `MissingRequiredParameterException` - if the image name or output path is empty
- `InvalidParameterValueException` - if invalid parameters are provided

## Usage Examples

### Getting a List of Images from All Nodes
```php
use Sangezar\DockerClient\Cluster\DockerCluster;
use Sangezar\DockerClient\DockerClient;

// Creating a cluster
$cluster = new DockerCluster();
$cluster->addNode('node1', DockerClient::createTcp('tcp://192.168.1.10:2375'));
$cluster->addNode('node2', DockerClient::createTcp('tcp://192.168.1.11:2375'));

// Getting a list of all images
$images = $cluster->images()->list(['all' => true]);

// Checking results
foreach ($images as $nodeName => $result) {
    echo "Images on node $nodeName:\n";
    foreach ($result as $image) {
        echo "  - {$image['RepoTags'][0]} (ID: {$image['Id']})\n";
    }
}
```

### Pulling an Image to All Cluster Nodes
```php
use Sangezar\DockerClient\Cluster\NodeCollection;
use Sangezar\DockerClient\DockerClient;

// Creating a node collection
$nodes = [
    'node1' => DockerClient::createTcp('tcp://192.168.1.10:2375'),
    'node2' => DockerClient::createTcp('tcp://192.168.1.11:2375'),
];
$collection = new NodeCollection($nodes);

// Pulling an image to all nodes
$results = $collection->images()->pull('nginx:latest');

// Checking if the image exists on all nodes
$exists = $collection->images()->existsOnAllNodes('nginx:latest');
if ($exists) {
    echo "Image 'nginx:latest' exists on all nodes\n";
} else {
    // Getting a list of nodes where the image exists
    $nodesWithImage = $collection->images()->getNodesWithImage('nginx:latest');
    echo "Image 'nginx:latest' exists only on nodes: " . implode(', ', $nodesWithImage) . "\n";
}
```

### Building and Pushing an Image
```php
// Building an image on all nodes
$buildResults = $collection->images()->build([
    't' => 'myapp:latest',
    'dockerfile' => 'Dockerfile',
], [
    'context' => '/path/to/build/context',
]);

// Tagging the image for pushing
$collection->images()->tag('myapp:latest', 'registry.example.com/myapp', 'latest');

// Pushing the image to the registry
$pushResults = $collection->images()->push('registry.example.com/myapp:latest');

// Removing the local image
$collection->images()->remove('myapp:latest', true);
``` 