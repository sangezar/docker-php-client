# Image Class Documentation

## Description
`Image` is a class for working with Docker images through the API. It provides methods for creating, downloading, getting information, tagging, and removing Docker images.

## Namespace
`Sangezar\DockerClient\Api`

## Inheritance
The `Image` class inherits from `AbstractApi` and implements the `ImageInterface` interface.

## Methods

### list
```php
public function list(array $parameters = []): array
```
Gets a list of images.

#### Parameters:
- `$parameters` - Array of parameters for filtering results:
  - `all` (bool) - Show all images (by default hides intermediate images)
  - `filters` (array|string) - Filters to apply in JSON format or array
  - `digests` (bool) - Show digest information

#### Returns:
- Array of images

#### Exceptions:
- `InvalidParameterValueException` - if invalid parameters are passed

### build
```php
public function build(array $parameters = [], array $config = []): array
```
Builds a new image.

#### Parameters:
- `$parameters` - Request parameters for building:
  - `t` (string) - Name and tag for the built image
  - `q` (bool) - Suppress detailed build output
  - `nocache` (bool) - Do not use cache when building the image
  - `pull` (bool|string) - Download the image before building
  - `rm` (bool) - Remove intermediate containers after successful build
  - `forcerm` (bool) - Always remove intermediate containers
  - other parameters
- `$config` - Configuration for the build context

#### Returns:
- Array with build result

#### Exceptions:
- `InvalidParameterValueException` - if invalid parameters are passed
- `OperationFailedException` - if the build fails

### buildWithOptions
```php
public function buildWithOptions(\Sangezar\DockerClient\Config\ImageBuildOptions $options): array
```
Builds an image using an ImageBuildOptions object.

#### Parameters:
- `$options` - Configuration options for building the image

#### Returns:
- Array with build result

#### Exceptions:
- `InvalidParameterValueException` - if invalid parameters are passed
- `OperationFailedException` - if the build fails

### create
```php
public function create(string $fromImage, ?string $tag = null): array
```
Creates an image by downloading it from the registry.

#### Parameters:
- `$fromImage` - Source image name
- `$tag` - Tag to download (default 'latest')

#### Returns:
- Array with creation result

#### Exceptions:
- `MissingRequiredParameterException` - if fromImage is empty
- `InvalidParameterValueException` - if the image name format or tag is invalid
- `OperationFailedException` - if the download fails

### inspect
```php
public function inspect(string $name): array
```
Gets detailed information about an image.

#### Parameters:
- `$name` - Image name or ID

#### Returns:
- Array with detailed information about the image

#### Exceptions:
- `MissingRequiredParameterException` - if the image name is empty
- `NotFoundException` - if the image is not found

### history
```php
public function history(string $name): array
```
Gets the image history.

#### Parameters:
- `$name` - Image name or ID

#### Returns:
- Array with image history

#### Exceptions:
- `MissingRequiredParameterException` - if the image name is empty
- `NotFoundException` - if the image is not found

### push
```php
public function push(string $name, array $parameters = []): array
```
Pushes an image to the registry.

#### Parameters:
- `$name` - Image name
- `$parameters` - Additional parameters:
  - `tag` (string) - Tag to push

#### Returns:
- Array with push result

#### Exceptions:
- `MissingRequiredParameterException` - if the image name is empty
- `OperationFailedException` - if the push fails

### tag
```php
public function tag(string $name, string $repo, ?string $tag = null): bool
```
Tags an image with a new name and tag.

#### Parameters:
- `$name` - Image name or ID
- `$repo` - New repository name for the image
- `$tag` - New tag (default 'latest')

#### Returns:
- `true` if the image was successfully tagged

#### Exceptions:
- `MissingRequiredParameterException` - if required parameters are empty
- `InvalidParameterValueException` - if the repository format or tag is invalid
- `OperationFailedException` - if tagging fails
- `NotFoundException` - if the image is not found

### exists
```php
public function exists(string $name): bool
```
Checks if an image exists.

#### Parameters:
- `$name` - Image name or ID

#### Returns:
- `true` if the image exists, `false` otherwise

#### Exceptions:
- `MissingRequiredParameterException` - if the image name is empty

### remove
```php
public function remove(string $name, bool $force = false, bool $noprune = false): bool
```
Removes an image.

#### Parameters:
- `$name` - Image name or ID
- `$force` - Force removal (default `false`)
- `$noprune` - Do not delete unused parent layers (default `false`)

#### Returns:
- `true` if the image was successfully removed

#### Exceptions:
- `MissingRequiredParameterException` - if the image name is empty
- `OperationFailedException` - if removal fails
- `NotFoundException` - if the image is not found

### search
```php
public function search(string $term): array
```
Searches for images in the Docker Hub registry.

#### Parameters:
- `$term` - Search query

#### Returns:
- Array with search results

#### Exceptions:
- `MissingRequiredParameterException` - if the search query is empty
- `OperationFailedException` - if the search fails

### prune
```php
public function prune(array $filters = []): array
```
Removes unused images.

#### Parameters:
- `$filters` - Filters for selecting images to clean up:
  - `dangling` (bool) - Remove only dangling images (images without tags)
  - `until` (string) - Remove images created before the specified time
  - `label` (string) - Remove images with specified labels

#### Returns:
- Array with cleanup result, including freed space

#### Exceptions:
- `InvalidParameterValueException` - if filters are invalid
- `OperationFailedException` - if cleanup fails

## Usage Examples

### Getting a List of Images
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createUnix();
$images = $client->image()->list(['all' => true]);

foreach ($images as $image) {
    $tags = $image['RepoTags'] ?? ['<none>:<none>'];
    echo "Image: " . implode(', ', $tags) . ", size: " . $image['Size'] . " bytes\n";
}
```

### Downloading an Image from Docker Hub
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createUnix();
$result = $client->image()->create('nginx', 'latest');
echo "Image nginx:latest successfully downloaded\n";
```

### Tagging and Removing an Image
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createUnix();
$imageName = 'nginx:latest';

if ($client->image()->exists($imageName)) {
    // Tag the image with a new name
    $client->image()->tag($imageName, 'my-nginx', 'v1');
    
    // Remove the original image
    $client->image()->remove($imageName);
    
    echo "Image {$imageName} renamed to my-nginx:v1 and original removed\n";
} 