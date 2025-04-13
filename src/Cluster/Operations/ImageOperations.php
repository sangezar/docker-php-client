<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Cluster\Operations;

use Sangezar\DockerClient\Exception\NotFoundException;
use Sangezar\DockerClient\Exception\OperationFailedException;
use Sangezar\DockerClient\Exception\Validation\InvalidConfigurationException;
use Sangezar\DockerClient\Exception\Validation\InvalidParameterValueException;
use Sangezar\DockerClient\Exception\Validation\MissingRequiredParameterException;

/**
 * Docker image operations on all cluster nodes
 */
class ImageOperations extends AbstractOperations
{
    /**
     * Gets a list of images from all cluster nodes
     *
     * @param array<string, mixed> $parameters Filtering parameters
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws InvalidParameterValueException if invalid filtering parameters are provided
     */
    public function list(array $parameters = []): array
    {
        // Validate filtering parameters
        if (isset($parameters['all']) && ! is_bool($parameters['all'])) {
            throw new InvalidParameterValueException(
                'parameters.all',
                $parameters['all'],
                'boolean',
                'Parameter "all" must be a boolean value'
            );
        }

        if (isset($parameters['filters']) && ! is_array($parameters['filters'])) {
            throw new InvalidParameterValueException(
                'parameters.filters',
                $parameters['filters'],
                'array',
                'Parameter "filters" must be an array'
            );
        }

        return $this->executeOnAll(
            fn ($client) => $client->image()->list($parameters)
        );
    }

    /**
     * Builds an image on all cluster nodes
     *
     * @param array<string, mixed> $parameters Build parameters
     * @param array<string, mixed> $config Additional configuration
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws InvalidParameterValueException if invalid parameters are provided
     * @throws InvalidConfigurationException if configuration is invalid
     */
    public function build(array $parameters = [], array $config = []): array
    {
        // Check for required parameters
        if (! isset($parameters['t']) && ! isset($parameters['tag'])) {
            throw new MissingRequiredParameterException(
                'parameters.t or parameters.tag',
                'Tag parameter (t or tag) is required for building an image'
            );
        }

        // Validate configuration
        if (isset($config['context']) && ! is_string($config['context'])) {
            throw new InvalidParameterValueException(
                'config.context',
                $config['context'],
                'string',
                'Context must be a string containing path to build context'
            );
        }

        return $this->executeOnAll(
            function ($client) use ($parameters, $config) {
                try {
                    return $client->image()->build($parameters, $config);
                } catch (\Throwable $e) {
                    // Create structured error for return
                    $imageId = 'unknown';
                    if (isset($parameters['t']) && is_string($parameters['t'])) {
                        $imageId = $parameters['t'];
                    } elseif (isset($parameters['tag']) && is_string($parameters['tag'])) {
                        $imageId = $parameters['tag'];
                    }

                    throw new OperationFailedException(
                        'build',
                        'image',
                        $imageId,
                        'Failed to build image: ' . $e->getMessage(),
                        0,
                        $e
                    );
                }
            }
        );
    }

    /**
     * Builds an image on all cluster nodes using a build options object
     *
     * @param \Sangezar\DockerClient\Config\ImageBuildOptions $options Build options object
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws InvalidParameterValueException if invalid parameters are provided
     * @throws InvalidConfigurationException if configuration is invalid
     */
    public function buildWithOptions(\Sangezar\DockerClient\Config\ImageBuildOptions $options): array
    {
        $buildConfig = $options->toArrays();

        return $this->build($buildConfig['parameters'], $buildConfig['config']);
    }

    /**
     * Creates an image by pulling from registry on all cluster nodes
     *
     * @param string $fromImage Image name to pull
     * @param string|null $tag Image tag (default "latest")
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws MissingRequiredParameterException if image name is empty
     * @throws InvalidParameterValueException if invalid parameters are provided
     */
    public function create(string $fromImage, ?string $tag = null): array
    {
        if (empty($fromImage)) {
            throw new MissingRequiredParameterException(
                'fromImage',
                'Image name cannot be empty'
            );
        }

        // Validate image name format
        if (! preg_match('/^[a-z0-9]+((\.|_|-)?[a-z0-9]+)*(\/[a-z0-9]+((\.|_|-)?[a-z0-9]+)*)*$/', $fromImage)) {
            throw new InvalidParameterValueException(
                'fromImage',
                $fromImage,
                'valid Docker image name',
                'Invalid Docker image name format'
            );
        }

        // Validate tag if specified
        if ($tag !== null && ! empty($tag) && ! preg_match('/^[a-zA-Z0-9_.-]+$/', $tag)) {
            throw new InvalidParameterValueException(
                'tag',
                $tag,
                'valid Docker tag (letters, digits, _, ., -)',
                'Invalid Docker tag format'
            );
        }

        return $this->executeOnAll(
            function ($client) use ($fromImage, $tag) {
                try {
                    return $client->image()->create($fromImage, $tag);
                } catch (\Throwable $e) {
                    // Create structured error for return
                    throw new OperationFailedException(
                        'create',
                        'image',
                        $fromImage . ($tag ? ':' . $tag : ''),
                        'Failed to pull image: ' . $e->getMessage(),
                        0,
                        $e
                    );
                }
            }
        );
    }

    /**
     * Gets detailed information about an image on all cluster nodes
     *
     * @param string $name Image name
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws MissingRequiredParameterException if image name is empty
     */
    public function inspect(string $name): array
    {
        if (empty($name)) {
            throw new MissingRequiredParameterException(
                'name',
                'Image name or ID cannot be empty'
            );
        }

        return $this->executeOnAll(
            function ($client) use ($name) {
                try {
                    return $client->image()->inspect($name);
                } catch (NotFoundException $e) {
                    // Return structured result for "not found" error
                    return [
                        'error' => true,
                        'errorType' => 'not_found',
                        'message' => sprintf('Image "%s" not found on this node', $name),
                    ];
                } catch (\Throwable $e) {
                    throw $e; // Re-throw other errors for handling in executeOnAll
                }
            }
        );
    }

    /**
     * Gets image history on all cluster nodes
     *
     * @param string $name Image name
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws MissingRequiredParameterException if image name is empty
     */
    public function history(string $name): array
    {
        if (empty($name)) {
            throw new MissingRequiredParameterException(
                'name',
                'Image name or ID cannot be empty'
            );
        }

        return $this->executeOnAll(
            function ($client) use ($name) {
                try {
                    return $client->image()->history($name);
                } catch (NotFoundException $e) {
                    // Return structured result for "not found" error
                    return [
                        'error' => true,
                        'errorType' => 'not_found',
                        'message' => sprintf('Image "%s" not found on this node', $name),
                    ];
                } catch (\Throwable $e) {
                    throw $e; // Re-throw other errors for handling in executeOnAll
                }
            }
        );
    }

    /**
     * Pushes an image to registry from all cluster nodes
     *
     * @param string $name Image name to push
     * @param array<string, mixed> $parameters Additional parameters
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws MissingRequiredParameterException if image name is empty
     * @throws InvalidParameterValueException if invalid parameters are provided
     */
    public function push(string $name, array $parameters = []): array
    {
        if (empty($name)) {
            throw new MissingRequiredParameterException(
                'name',
                'Image name cannot be empty'
            );
        }

        return $this->executeOnAll(
            function ($client) use ($name, $parameters) {
                try {
                    return $client->image()->push($name, $parameters);
                } catch (NotFoundException $e) {
                    // Return structured result for "not found" error
                    return [
                        'error' => true,
                        'errorType' => 'not_found',
                        'message' => sprintf('Image "%s" not found on this node', $name),
                    ];
                } catch (\Throwable $e) {
                    // Create structured error for return
                    throw new OperationFailedException(
                        'push',
                        'image',
                        $name,
                        'Failed to push image: ' . $e->getMessage(),
                        0,
                        $e
                    );
                }
            }
        );
    }

    /**
     * Creates a tag for an image on all cluster nodes
     *
     * @param string $name Image name or ID
     * @param string $repo New repository name
     * @param string|null $tag New tag (default "latest")
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws MissingRequiredParameterException if required parameters are missing
     * @throws InvalidParameterValueException if invalid parameters are provided
     */
    public function tag(string $name, string $repo, ?string $tag = null): array
    {
        if (empty($name)) {
            throw new MissingRequiredParameterException(
                'name',
                'Image name or ID cannot be empty'
            );
        }

        if (empty($repo)) {
            throw new MissingRequiredParameterException(
                'repo',
                'Repository name cannot be empty'
            );
        }

        // Validate tag if specified
        if ($tag !== null && ! empty($tag) && ! preg_match('/^[a-zA-Z0-9_.-]+$/', $tag)) {
            throw new InvalidParameterValueException(
                'tag',
                $tag,
                'valid Docker tag (letters, digits, _, ., -)',
                'Invalid Docker tag format'
            );
        }

        return $this->executeOnAll(
            function ($client) use ($name, $repo, $tag) {
                try {
                    return $client->image()->tag($name, $repo, $tag);
                } catch (NotFoundException $e) {
                    // Return structured result for "not found" error
                    return false;
                } catch (\Throwable $e) {
                    throw $e; // Re-throw other errors for handling in executeOnAll
                }
            }
        );
    }

    /**
     * Removes an image on all cluster nodes
     *
     * @param string $name Image name
     * @param bool $force Force removal
     * @param bool $noprune Don't remove intermediate images
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws MissingRequiredParameterException if image name is empty
     */
    public function remove(string $name, bool $force = false, bool $noprune = false): array
    {
        if (empty($name)) {
            throw new MissingRequiredParameterException(
                'name',
                'Image name or ID cannot be empty'
            );
        }

        return $this->executeOnAll(
            function ($client) use ($name, $force, $noprune) {
                try {
                    return $client->image()->remove($name, $force, $noprune);
                } catch (NotFoundException $e) {
                    // Return structured result for "not found" error
                    return false;
                } catch (\Throwable $e) {
                    throw $e; // Re-throw other errors for handling in executeOnAll
                }
            }
        );
    }

    /**
     * Searches for images in Docker Hub from all cluster nodes
     *
     * @param string $term Search query
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws MissingRequiredParameterException if search query is empty
     */
    public function search(string $term): array
    {
        if (empty($term)) {
            throw new MissingRequiredParameterException(
                'term',
                'Search term cannot be empty'
            );
        }

        return $this->executeOnAll(
            fn ($client) => $client->image()->search($term)
        );
    }

    /**
     * Removes unused images on all cluster nodes
     *
     * @param array<string, mixed> $parameters Filtering parameters
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws InvalidParameterValueException if invalid filtering parameters are provided
     */
    public function prune(array $parameters = []): array
    {
        // Validate filters
        if (! empty($parameters) && ! is_array($parameters)) {
            throw new InvalidParameterValueException(
                'parameters',
                $parameters,
                'array',
                'Parameters must be an array'
            );
        }

        return $this->executeOnAll(
            fn ($client) => $client->image()->prune($parameters)
        );
    }

    /**
     * Checks if an image exists on all cluster nodes
     *
     * @param string $name Image name
     * @return array<string, array<string, mixed>|bool> Whether the image exists on each node
     * @throws MissingRequiredParameterException if image name is empty
     */
    public function exists(string $name): array
    {
        if (empty($name)) {
            throw new MissingRequiredParameterException(
                'name',
                'Image name or ID cannot be empty'
            );
        }

        return $this->executeOnAll(
            fn ($client) => $client->image()->exists($name)
        );
    }

    /**
     * Checks if an image with the specified name exists on all cluster nodes
     *
     * @param string $name Image name or ID
     * @return bool True if the image exists on all nodes, false if it's missing on at least one
     * @throws MissingRequiredParameterException if image name is empty
     */
    public function existsOnAllNodes(string $name): bool
    {
        if (empty($name)) {
            throw new MissingRequiredParameterException(
                'name',
                'Image name or ID cannot be empty'
            );
        }

        $results = $this->exists($name);

        // Check if all results = true
        foreach ($results as $nodeResult) {
            if ($nodeResult !== true) {
                return false;
            }
        }

        return true;
    }

    /**
     * Gets a list of nodes where an image with the specified name exists
     *
     * @param string $name Image name or ID
     * @return array<string> Array of node names where the image is found
     * @throws MissingRequiredParameterException if image name is empty
     */
    public function getNodesWithImage(string $name): array
    {
        if (empty($name)) {
            throw new MissingRequiredParameterException(
                'name',
                'Image name or ID cannot be empty'
            );
        }

        $results = $this->exists($name);
        $nodesWithImage = [];

        foreach ($results as $nodeName => $exists) {
            if ($exists === true) {
                $nodesWithImage[] = $nodeName;
            }
        }

        return $nodesWithImage;
    }

    /**
     * Pulls an image on all cluster nodes
     *
     * @param string $name Image name
     * @param array<string, mixed> $parameters Additional parameters
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws MissingRequiredParameterException if image name is empty
     * @throws InvalidParameterValueException if additional parameters are invalid
     */
    public function pull(string $name, array $parameters = []): array
    {
        if (empty($name)) {
            throw new MissingRequiredParameterException(
                'name',
                'Image name cannot be empty'
            );
        }

        return $this->executeOnAll(
            function ($client) use ($name, $parameters) {
                try {
                    return $client->image()->pull($name, $parameters);
                } catch (NotFoundException $e) {
                    // Return structured result for "not found" error
                    return [
                        'error' => true,
                        'errorType' => 'not_found',
                        'message' => sprintf('Image "%s" not found on this node', $name),
                    ];
                } catch (\Throwable $e) {
                    // Create structured error for return
                    throw new OperationFailedException(
                        'pull',
                        'image',
                        $name,
                        'Failed to pull image: ' . $e->getMessage(),
                        0,
                        $e
                    );
                }
            }
        );
    }

    /**
     * Loads an image on all cluster nodes
     *
     * @param string $imageArchive Path to image archive
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws \Sangezar\DockerClient\Exception\Validation\MissingRequiredParameterException if archive path is empty
     * @throws \Sangezar\DockerClient\Exception\OperationFailedException for other errors
     */
    public function load(string $imageArchive): array
    {
        if (empty($imageArchive)) {
            throw new MissingRequiredParameterException(
                'imageArchive',
                'Image archive path cannot be empty'
            );
        }

        return $this->executeOnAll(
            function ($client) use ($imageArchive) {
                try {
                    return $client->image()->load($imageArchive);
                } catch (\Throwable $e) {
                    // Create structured error for return
                    throw new OperationFailedException(
                        'load',
                        'image',
                        $imageArchive,
                        'Failed to load image: ' . $e->getMessage(),
                        0,
                        $e
                    );
                }
            }
        );
    }

    /**
     * Saves an image to a file on all cluster nodes
     *
     * @param string|array<string> $names Image name or array of names
     * @param string $outputFile Path to save the file
     * @return array<string, array<string, mixed>|bool> Results for each node
     * @throws MissingRequiredParameterException if image name or output path is empty
     */
    public function save($names, string $outputFile): array
    {
        if (empty($names)) {
            throw new MissingRequiredParameterException(
                'names',
                'Image name or ID cannot be empty'
            );
        }

        if (empty($outputFile)) {
            throw new MissingRequiredParameterException(
                'outputFile',
                'Output file path cannot be empty'
            );
        }

        return $this->executeOnAll(
            function ($client) use ($names, $outputFile) {
                try {
                    return $client->image()->save($names, $outputFile);
                } catch (\Throwable $e) {
                    // Create structured error for return
                    throw new OperationFailedException(
                        'save',
                        'image',
                        is_array($names) ? implode(',', $names) : $names,
                        'Failed to save image: ' . $e->getMessage(),
                        0,
                        $e
                    );
                }
            }
        );
    }
}
