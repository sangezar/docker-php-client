<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Api;

use Sangezar\DockerClient\Api\Interface\ImageInterface;
use Sangezar\DockerClient\Exception\NotFoundException;
use Sangezar\DockerClient\Exception\OperationFailedException;
use Sangezar\DockerClient\Exception\Validation\InvalidParameterValueException;
use Sangezar\DockerClient\Exception\Validation\MissingRequiredParameterException;

/**
 * Docker Image API client
 */
class Image extends AbstractApi implements ImageInterface
{
    /**
     * List images
     *
     * @param array<string, mixed> $parameters Parameters for filtering results
     *    - all: bool - Show all images (default hides intermediate images)
     *    - filters: array|string - Filters to apply as JSON string or array
     *    - digests: bool - Show digest information
     * @return array<int, array<string, mixed>> List of images
     * @throws InvalidParameterValueException if invalid parameters are provided
     */
    public function list(array $parameters = []): array
    {
        // Validate parameters
        $allowedParams = ['all', 'filters', 'digests'];
        foreach (array_keys($parameters) as $param) {
            if (! in_array($param, $allowedParams)) {
                throw new InvalidParameterValueException(
                    'parameters',
                    $parameters,
                    implode(', ', $allowedParams),
                    sprintf(
                        'Parameter "%s" is not supported for the list method. Allowed parameters: %s',
                        $param,
                        implode(', ', $allowedParams)
                    )
                );
            }
        }

        // Handle the 'filters' parameter, which should be a JSON-encoded string in Docker API
        if (isset($parameters['filters']) && is_array($parameters['filters'])) {
            $parameters['filters'] = json_encode($parameters['filters']);
        }

        $response = $this->get('/images/json', ['query' => $parameters]);

        // Ensure the response is an array with integer keys
        $result = [];
        foreach ($response as $item) {
            if (is_array($item)) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * Build an image
     *
     * @param array<string, mixed> $parameters Query parameters for building
     *    - t: string - Name and tag for the built image
     *    - q: bool - Suppress verbose build output
     *    - nocache: bool - Do not use cache when building the image
     *    - pull: bool|string - Pull image before building
     *    - rm: bool - Remove intermediate containers after successful build
     *    - forcerm: bool - Always remove intermediate containers
     * @param array<string, mixed> $config Configuration for the build context
     * @return array<string, mixed> Build result
     * @throws InvalidParameterValueException if invalid parameters are provided
     * @throws OperationFailedException if the build fails
     */
    public function build(array $parameters = [], array $config = []): array
    {
        // Validate parameters
        $allowedParams = ['t', 'q', 'nocache', 'pull', 'rm', 'forcerm', 'dockerfile', 'buildargs', 'labels', 'target', 'extrahosts', 'platform', 'squash', 'networkmode', 'cachefrom', 'outputtype', 'securityopt'];
        foreach (array_keys($parameters) as $param) {
            if (! in_array($param, $allowedParams)) {
                throw new InvalidParameterValueException(
                    'parameters',
                    $parameters,
                    implode(', ', $allowedParams),
                    sprintf(
                        'Parameter "%s" is not supported for the build method. Allowed parameters: %s',
                        $param,
                        implode(', ', $allowedParams)
                    )
                );
            }
        }

        // Validate boolean parameters
        foreach (['q', 'nocache', 'rm', 'forcerm', 'squash'] as $boolParam) {
            if (isset($parameters[$boolParam]) && ! is_bool($parameters[$boolParam])) {
                throw new InvalidParameterValueException(
                    $boolParam,
                    $parameters[$boolParam],
                    'true or false',
                    sprintf(
                        'The "%s" parameter value must be boolean, received: %s',
                        $boolParam,
                        var_export($parameters[$boolParam], true)
                    )
                );
            }
        }

        try {
            return $this->post('/build', [
                'query' => $parameters,
                'json' => $config,
            ]);
        } catch (\Exception $e) {
            throw new OperationFailedException(
                'build',
                'image',
                isset($parameters['t']) && is_string($parameters['t']) ? $parameters['t'] : 'unknown',
                'Failed to build image',
                0,
                $e
            );
        }
    }

    /**
     * Build an image using ImageBuildOptions
     *
     * @param \Sangezar\DockerClient\Config\ImageBuildOptions $options Configuration options for building
     * @return array<string, mixed> Build result
     * @throws InvalidParameterValueException if invalid parameters are provided
     * @throws OperationFailedException if the build fails
     */
    public function buildWithOptions(\Sangezar\DockerClient\Config\ImageBuildOptions $options): array
    {
        $buildConfig = $options->toArrays();

        return $this->build($buildConfig['parameters'], $buildConfig['config']);
    }

    /**
     * Create an image by pulling it from a registry
     *
     * @param string $fromImage Source image name
     * @param string|null $tag Tag to pull (default is 'latest')
     * @return array<string, mixed> Creation result
     * @throws MissingRequiredParameterException if fromImage is empty
     * @throws OperationFailedException if the pull fails
     */
    public function create(string $fromImage, ?string $tag = null): array
    {
        if (empty($fromImage)) {
            throw new MissingRequiredParameterException(
                'fromImage',
                'Source image name cannot be empty'
            );
        }

        // Validate image name format
        if (! preg_match('/^[a-z0-9]+((\.|_|-)?[a-z0-9]+)*(\/[a-z0-9]+((\.|_|-)?[a-z0-9]+)*)*$/', $fromImage)) {
            throw new InvalidParameterValueException(
                'fromImage',
                $fromImage,
                'Valid Docker image name',
                'Invalid Docker image name format'
            );
        }

        // Validate tag format if provided
        if ($tag !== null && ! empty($tag) && ! preg_match('/^[a-zA-Z0-9_.-]+$/', $tag)) {
            throw new InvalidParameterValueException(
                'tag',
                $tag,
                'Valid Docker tag (letters, digits, _, ., -)',
                'Invalid Docker tag format'
            );
        }

        $parameters = ['fromImage' => $fromImage];
        if ($tag !== null) {
            $parameters['tag'] = $tag;
        }

        try {
            return $this->post('/images/create', ['query' => $parameters]);
        } catch (\Exception $e) {
            throw new OperationFailedException(
                'create',
                'image',
                $fromImage . ($tag ? ':' . $tag : ''),
                'Failed to pull image',
                0,
                $e
            );
        }
    }

    /**
     * Inspect an image
     *
     * @param string $name Image name or ID
     * @return array<string, mixed> Image details
     * @throws MissingRequiredParameterException if name is empty
     * @throws NotFoundException if the image is not found
     */
    public function inspect(string $name): array
    {
        if (empty($name)) {
            throw new MissingRequiredParameterException(
                'name',
                'Image name cannot be empty'
            );
        }

        try {
            return $this->get("/images/{$name}/json");
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), '404') !== false) {
                throw new NotFoundException('image', $name);
            }

            throw $e;
        }
    }

    /**
     * Get the history of an image
     *
     * @param string $name Image name or ID
     * @return array<int, array<string, mixed>> Image history
     * @throws MissingRequiredParameterException if name is empty
     * @throws NotFoundException if the image is not found
     */
    public function history(string $name): array
    {
        if (empty($name)) {
            throw new MissingRequiredParameterException(
                'name',
                'Image name or ID cannot be empty'
            );
        }

        try {
            $response = $this->get("/images/{$name}/history");

            // Ensure the response is an array with integer keys
            $result = [];
            foreach ($response as $item) {
                if (is_array($item)) {
                    $result[] = $item;
                }
            }

            return $result;
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), '404') !== false) {
                throw new NotFoundException('image', $name);
            }

            throw $e;
        }
    }

    /**
     * Push an image to a registry
     *
     * @param string $name Image name
     * @param array<string, mixed> $parameters Parameters for the push
     *    - tag: string - Tag to push
     *    - X-Registry-Auth: string - Registry authentication details
     * @return array<string, mixed> Push result
     * @throws MissingRequiredParameterException if name is empty
     * @throws OperationFailedException if the push fails
     */
    public function push(string $name, array $parameters = []): array
    {
        if (empty($name)) {
            throw new MissingRequiredParameterException(
                'name',
                'Image name cannot be empty'
            );
        }

        // Validate tag format if provided
        if (isset($parameters['tag']) && is_string($parameters['tag'])) {
            // Параметр тепер завжди рядок, можемо безпечно перевіряти його
            if (! empty($parameters['tag']) && ! preg_match('/^[a-zA-Z0-9_.-]+$/', $parameters['tag'])) {
                throw new InvalidParameterValueException(
                    'tag',
                    $parameters['tag'],
                    'Valid Docker tag (letters, digits, _, ., -)',
                    'Invalid Docker tag format'
                );
            }
        }

        try {
            return $this->post("/images/{$name}/push", ['query' => $parameters]);
        } catch (\Exception $e) {
            throw new OperationFailedException(
                'push',
                'image',
                $name . (isset($parameters['tag']) ? ':' . $parameters['tag'] : ''),
                'Failed to push image',
                0,
                $e
            );
        }
    }

    /**
     * Tag an image
     *
     * @param string $name Source image name or ID
     * @param string $repo Repository to tag the image into
     * @param string|null $tag Tag to assign (default is 'latest')
     * @return bool Result of operation
     * @throws MissingRequiredParameterException if name or repo is empty
     * @throws InvalidParameterValueException if the tag format is invalid
     * @throws OperationFailedException if the tagging fails
     */
    public function tag(string $name, string $repo, ?string $tag = null): bool
    {
        if (empty($name)) {
            throw new MissingRequiredParameterException(
                'name',
                'Source image name cannot be empty'
            );
        }

        if (empty($repo)) {
            throw new MissingRequiredParameterException(
                'repo',
                'Repository name cannot be empty'
            );
        }

        // Validate repository format
        if (! preg_match('/^[a-z0-9]+((\.|_|-)?[a-z0-9]+)*(\/[a-z0-9]+((\.|_|-)?[a-z0-9]+)*)*$/', (string)$repo)) {
            throw new InvalidParameterValueException(
                'repo',
                $repo,
                'Valid Docker repository name',
                'Invalid Docker repository name format'
            );
        }

        // Validate tag format if provided
        if ($tag !== null && ! empty($tag)) {
            $tagStr = (string)$tag;
            if (! preg_match('/^[a-zA-Z0-9_.-]+$/', $tagStr)) {
                throw new InvalidParameterValueException(
                    'tag',
                    $tag,
                    'Valid Docker tag (letters, digits, _, ., -)',
                    'Invalid Docker tag format'
                );
            }
        }

        try {
            $parameters = ['repo' => $repo];
            if ($tag !== null) {
                $parameters['tag'] = $tag;
            }

            $this->post("/images/{$name}/tag", ['query' => $parameters]);

            return true;
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), '404') !== false) {
                throw new NotFoundException('image', $name);
            }

            throw new OperationFailedException(
                'tag',
                'image',
                $name,
                sprintf('Failed to tag image as %s%s', $repo, $tag ? ':' . $tag : ''),
                0,
                $e
            );
        }
    }

    /**
     * Check if an image exists
     *
     * @param string $name Image name or ID
     * @return bool Whether the image exists
     * @throws MissingRequiredParameterException if name is empty
     */
    public function exists(string $name): bool
    {
        if (empty($name)) {
            throw new MissingRequiredParameterException(
                'name',
                'Image name cannot be empty'
            );
        }

        try {
            $response = $this->inspect($name);

            return isset($response['Id']);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Remove an image
     *
     * @param string $name Image name or ID
     * @param bool $force Force removal of the image
     * @param bool $noprune Do not delete untagged parent images
     * @return bool Result of operation
     * @throws MissingRequiredParameterException if name is empty
     * @throws NotFoundException if the image is not found
     * @throws OperationFailedException if the removal fails
     */
    public function remove(string $name, bool $force = false, bool $noprune = false): bool
    {
        if (empty($name)) {
            throw new MissingRequiredParameterException(
                'name',
                'Image name cannot be empty'
            );
        }

        try {
            $this->delete("/images/{$name}", [
                'query' => [
                    'force' => $force,
                    'noprune' => $noprune,
                ],
            ]);

            return true;
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), '404') !== false) {
                throw new NotFoundException('image', $name);
            }

            if (strpos($e->getMessage(), '409') !== false) {
                throw new OperationFailedException(
                    'remove',
                    'image',
                    $name,
                    'Failed to remove image: image is in use by a container',
                    0,
                    $e
                );
            }

            throw new OperationFailedException(
                'remove',
                'image',
                $name,
                'Failed to remove image',
                0,
                $e
            );
        }
    }

    /**
     * Search for images on Docker Hub
     *
     * @param string $term Search term
     * @return array<int, array<string, mixed>> Search results
     * @throws MissingRequiredParameterException if term is empty
     * @throws OperationFailedException if the search fails
     */
    public function search(string $term): array
    {
        if (empty($term)) {
            throw new MissingRequiredParameterException(
                'term',
                'Search term cannot be empty'
            );
        }

        try {
            $response = $this->get('/images/search', ['query' => ['term' => $term]]);

            // Ensure the response is an array with integer keys
            $result = [];
            foreach ($response as $item) {
                if (is_array($item)) {
                    $result[] = $item;
                }
            }

            return $result;
        } catch (\Exception $e) {
            throw new OperationFailedException(
                'search',
                'images',
                $term,
                'Failed to search for images: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Prune unused images
     *
     * @param array<string, mixed> $filters Filters to apply
     *    - dangling: bool - Only remove dangling images (untagged)
     *    - until: string - Only remove images created before given timestamp
     *    - label: string|array - Only remove images with (or without) labels
     * @return array<string, mixed> Prune result with space reclaimed
     * @throws InvalidParameterValueException if invalid filters are provided
     * @throws OperationFailedException if the operation fails
     */
    public function prune(array $filters = []): array
    {
        // Validate filters
        $allowedFilters = ['dangling', 'until', 'label'];
        foreach (array_keys($filters) as $filter) {
            if (! in_array($filter, $allowedFilters)) {
                throw new InvalidParameterValueException(
                    'filters',
                    $filters,
                    implode(', ', $allowedFilters),
                    sprintf(
                        'Filter "%s" is not supported for the prune method. Allowed filters: %s',
                        $filter,
                        implode(', ', $allowedFilters)
                    )
                );
            }
        }

        // Validate dangling filter if present
        if (isset($filters['dangling']) && ! is_bool($filters['dangling'])) {
            throw new InvalidParameterValueException(
                'dangling',
                $filters['dangling'],
                'true or false',
                'The "dangling" filter value must be boolean'
            );
        }

        try {
            return $this->post('/images/prune', ['query' => ['filters' => json_encode($filters)]]);
        } catch (\Exception $e) {
            throw new OperationFailedException(
                'prune',
                'images',
                '',
                'Failed to prune unused images',
                0,
                $e
            );
        }
    }
}
