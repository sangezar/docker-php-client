<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Cluster;

use Sangezar\DockerClient\DockerClient;
use Sangezar\DockerClient\Exception\NodeNotFoundException;
use Sangezar\DockerClient\Exception\ValidationException;

/**
 * Docker servers cluster
 */
class DockerCluster
{
    /** @var array<string, DockerClient> */
    private array $nodes = [];

    /** @var array<string, array<string>> */
    private array $tags = [];

    /** @var string Regular expression for node name validation */
    private const NODE_NAME_PATTERN = '/^[a-zA-Z0-9][a-zA-Z0-9_.-]*$/';

    /** @var string Regular expression for tag name validation */
    private const TAG_NAME_PATTERN = '/^[a-zA-Z0-9][a-zA-Z0-9_.-]*$/';

    /**
     * Adds a new node to the cluster
     *
     * @param string $name Unique node name
     * @param DockerClient $client Docker API client
     * @param array<string> $tags Array of tags for node categorization
     * @return self
     * @throws ValidationException if the node name is invalid or already exists
     */
    public function addNode(string $name, DockerClient $client, array $tags = []): self
    {
        // Node name validation
        if (empty($name)) {
            throw ValidationException::requiredParameter('name');
        }

        if (! preg_match(self::NODE_NAME_PATTERN, $name)) {
            throw ValidationException::invalidValue(
                'name',
                $name,
                'a valid node name (only letters, numbers, underscore, dot, and hyphen allowed)'
            );
        }

        // Check for duplicates
        if ($this->hasNode($name)) {
            throw ValidationException::invalidValue(
                'name',
                $name,
                'unique node name (a node with this name already exists)'
            );
        }

        // Tags validation
        foreach ($tags as $tag) {
            $this->validateTag($tag);
        }

        // Adding the node
        $this->nodes[$name] = $client;

        // Adding tags
        foreach ($tags as $tag) {
            if (! isset($this->tags[$tag])) {
                $this->tags[$tag] = [];
            }
            $this->tags[$tag][] = $name;
        }

        return $this;
    }

    /**
     * Returns a node client by its name
     *
     * @param string $name Node name
     * @return DockerClient
     * @throws NodeNotFoundException if the node is not found
     */
    public function node(string $name): DockerClient
    {
        if (empty($name)) {
            throw ValidationException::requiredParameter('name');
        }

        if (! isset($this->nodes[$name])) {
            throw new NodeNotFoundException($name);
        }

        return $this->nodes[$name];
    }

    /**
     * Checks if a node exists by name
     *
     * @param string $name Node name
     * @return bool true if the node exists
     */
    public function hasNode(string $name): bool
    {
        if (empty($name)) {
            return false;
        }

        return isset($this->nodes[$name]);
    }

    /**
     * Removes a node from the cluster
     *
     * @param string $name Node name
     * @return self
     */
    public function removeNode(string $name): self
    {
        if (empty($name)) {
            throw ValidationException::requiredParameter('name');
        }

        if ($this->hasNode($name)) {
            unset($this->nodes[$name]);

            foreach ($this->tags as $tag => $nodes) {
                $this->tags[$tag] = array_filter($nodes, fn ($node) => $node !== $name);
                if (empty($this->tags[$tag])) {
                    unset($this->tags[$tag]);
                }
            }
        }

        return $this;
    }

    /**
     * Returns all nodes with the specified tag
     *
     * @param string $tag Tag for filtering
     * @return array<string, DockerClient> Nodes that have this tag
     * @throws ValidationException if the tag is invalid
     */
    public function getNodesByTag(string $tag): array
    {
        $this->validateTag($tag);

        if (! isset($this->tags[$tag])) {
            return [];
        }

        return array_intersect_key(
            $this->nodes,
            array_flip($this->tags[$tag])
        );
    }

    /**
     * Adds a tag to an existing node
     *
     * @param string $nodeName Node name
     * @param string $tag Tag to add
     * @return self
     * @throws NodeNotFoundException if the node is not found
     * @throws ValidationException if the tag is invalid
     */
    public function addTagToNode(string $nodeName, string $tag): self
    {
        if (empty($nodeName)) {
            throw ValidationException::requiredParameter('nodeName');
        }

        if (! $this->hasNode($nodeName)) {
            throw new NodeNotFoundException($nodeName);
        }

        $this->validateTag($tag);

        if (! isset($this->tags[$tag])) {
            $this->tags[$tag] = [];
        }

        if (! in_array($nodeName, $this->tags[$tag])) {
            $this->tags[$tag][] = $nodeName;
        }

        return $this;
    }

    /**
     * Removes a tag from a node
     *
     * @param string $nodeName Node name
     * @param string $tag Tag to remove
     * @return self
     * @throws NodeNotFoundException if the node is not found
     */
    public function removeTagFromNode(string $nodeName, string $tag): self
    {
        if (empty($nodeName)) {
            throw ValidationException::requiredParameter('nodeName');
        }

        if (! $this->hasNode($nodeName)) {
            throw new NodeNotFoundException($nodeName);
        }

        if (isset($this->tags[$tag])) {
            $this->tags[$tag] = array_filter($this->tags[$tag], fn ($node) => $node !== $nodeName);

            if (empty($this->tags[$tag])) {
                unset($this->tags[$tag]);
            }
        }

        return $this;
    }

    /**
     * Returns all nodes that have all the specified tags (AND operation)
     *
     * @param array<string> $tags Array of tags
     * @return array<string, DockerClient> Nodes that have all the specified tags
     */
    public function getNodesByAllTags(array $tags): array
    {
        if (empty($tags)) {
            return [];
        }

        // Tags validation
        foreach ($tags as $tag) {
            $this->validateTag($tag);
        }

        $nodeNames = null;

        foreach ($tags as $tag) {
            if (! isset($this->tags[$tag])) {
                return []; // If at least one tag doesn't exist, the result is empty
            }

            $tagNodes = $this->tags[$tag];

            if ($nodeNames === null) {
                $nodeNames = $tagNodes;
            } else {
                $nodeNames = array_intersect($nodeNames, $tagNodes);
            }

            if (empty($nodeNames)) {
                return []; // No nodes that have all tags
            }
        }

        return array_intersect_key($this->nodes, array_flip($nodeNames));
    }

    /**
     * Returns all nodes that have any of the specified tags (OR operation)
     *
     * @param array<string> $tags Array of tags
     * @return array<string, DockerClient> Nodes that have at least one of the specified tags
     */
    public function getNodesByAnyTag(array $tags): array
    {
        if (empty($tags)) {
            return [];
        }

        // Tags validation
        foreach ($tags as $tag) {
            $this->validateTag($tag);
        }

        $nodeNames = [];

        foreach ($tags as $tag) {
            if (isset($this->tags[$tag])) {
                $nodeNames = array_merge($nodeNames, $this->tags[$tag]);
            }
        }

        $nodeNames = array_unique($nodeNames);

        if (empty($nodeNames)) {
            return [];
        }

        return array_intersect_key($this->nodes, array_flip($nodeNames));
    }

    /**
     * Returns a collection of nodes filtered by a callback function
     *
     * @param callable $callback Callback function (parameters: DockerClient, node name)
     * @return NodeCollection Collection of filtered nodes
     */
    public function filter(callable $callback): NodeCollection
    {
        $filtered = array_filter($this->nodes, $callback, ARRAY_FILTER_USE_BOTH);

        return new NodeCollection($filtered);
    }

    /**
     * Returns a collection of all cluster nodes
     *
     * @return NodeCollection Collection of all nodes
     */
    public function all(): NodeCollection
    {
        return new NodeCollection($this->nodes);
    }

    /**
     * Returns a collection of nodes with a specific tag
     *
     * @param string $tag Tag for filtering
     * @return NodeCollection Collection of nodes with the specified tag
     * @throws ValidationException if the tag is invalid
     */
    public function byTag(string $tag): NodeCollection
    {
        return new NodeCollection($this->getNodesByTag($tag));
    }

    /**
     * Returns a collection of nodes that have all the specified tags
     *
     * @param array<string> $tags Array of tags
     * @return NodeCollection Collection of nodes
     */
    public function byAllTags(array $tags): NodeCollection
    {
        return new NodeCollection($this->getNodesByAllTags($tags));
    }

    /**
     * Returns a collection of nodes that have any of the specified tags
     *
     * @param array<string> $tags Array of tags
     * @return NodeCollection Collection of nodes
     */
    public function byAnyTag(array $tags): NodeCollection
    {
        return new NodeCollection($this->getNodesByAnyTag($tags));
    }

    /**
     * Adds an array of nodes to the cluster
     *
     * @param array<string, array{client: DockerClient, tags?: array<string>}> $nodes Array of nodes
     * @return self
     * @throws ValidationException if the node configuration is invalid
     */
    public function addNodes(array $nodes): self
    {
        foreach ($nodes as $name => $config) {
            if (! isset($config['client']) || ! ($config['client'] instanceof DockerClient)) {
                throw ValidationException::invalidValue(
                    "nodes[$name][client]",
                    $config['client'] ?? null,
                    'instance of DockerClient'
                );
            }

            $tags = $config['tags'] ?? [];

            if (! is_array($tags)) {
                throw ValidationException::invalidValue(
                    "nodes[$name][tags]",
                    $tags,
                    'array'
                );
            }

            $this->addNode($name, $config['client'], $tags);
        }

        return $this;
    }

    /**
     * Returns all cluster nodes
     *
     * @return array<string, DockerClient>
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    /**
     * Returns all cluster tags and nodes associated with them
     *
     * @return array<string, array<string>>
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * Checks if the cluster is empty
     *
     * @return bool true if the cluster contains no nodes
     */
    public function isEmpty(): bool
    {
        return empty($this->nodes);
    }

    /**
     * Returns the number of nodes in the cluster
     *
     * @return int Number of nodes
     */
    public function count(): int
    {
        return count($this->nodes);
    }

    /**
     * Validates a tag
     *
     * @param string $tag Tag to validate
     * @throws ValidationException if the tag is invalid
     */
    private function validateTag(string $tag): void
    {
        if (empty($tag)) {
            throw ValidationException::invalidValue('tag', $tag, 'non-empty string');
        }

        if (! preg_match(self::TAG_NAME_PATTERN, $tag)) {
            throw ValidationException::invalidValue(
                'tag',
                $tag,
                'a valid tag name (only letters, numbers, underscore, dot, and hyphen allowed)'
            );
        }
    }
}
