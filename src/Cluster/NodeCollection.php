<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Cluster;

use Sangezar\DockerClient\Cluster\Operations\ContainerOperations;
use Sangezar\DockerClient\Cluster\Operations\ImageOperations;
use Sangezar\DockerClient\Cluster\Operations\NetworkOperations;
use Sangezar\DockerClient\Cluster\Operations\SystemOperations;
use Sangezar\DockerClient\Cluster\Operations\VolumeOperations;
use Sangezar\DockerClient\DockerClient;

/**
 * Collection of Docker nodes for cluster operations
 */
class NodeCollection
{
    /** @var array<string, DockerClient> */
    private array $nodes;

    /**
     * @param array<string, DockerClient> $nodes
     */
    public function __construct(array $nodes)
    {
        $this->nodes = $nodes;
    }

    /**
     * Get container operations for all nodes in the collection
     *
     * @return ContainerOperations
     */
    public function containers(): ContainerOperations
    {
        return new ContainerOperations($this->nodes);
    }

    /**
     * Get image operations for all nodes in the collection
     *
     * @return ImageOperations
     */
    public function images(): ImageOperations
    {
        return new ImageOperations($this->nodes);
    }

    /**
     * Get network operations for all nodes in the collection
     *
     * @return NetworkOperations
     */
    public function networks(): NetworkOperations
    {
        return new NetworkOperations($this->nodes);
    }

    /**
     * Get volume operations for all nodes in the collection
     *
     * @return VolumeOperations
     */
    public function volumes(): VolumeOperations
    {
        return new VolumeOperations($this->nodes);
    }

    /**
     * Get system operations for all nodes in the collection
     *
     * @return SystemOperations
     */
    public function system(): SystemOperations
    {
        return new SystemOperations($this->nodes);
    }

    /**
     * Filter nodes based on a callback function
     *
     * @param callable $callback Callback function for filtering
     * @return self New NodeCollection with filtered nodes
     */
    public function filter(callable $callback): self
    {
        return new self(array_filter($this->nodes, $callback, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * Get all nodes in the collection
     *
     * @return array<string, DockerClient>
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    /**
     * Count the number of nodes in the collection
     *
     * @return int Number of nodes
     */
    public function count(): int
    {
        return count($this->nodes);
    }

    /**
     * Check if the collection is empty
     *
     * @return bool True if the collection has no nodes, false otherwise
     */
    public function isEmpty(): bool
    {
        return empty($this->nodes);
    }
}
