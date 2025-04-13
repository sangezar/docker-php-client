<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Exception;

/**
 * Exception thrown when a node is not found in the cluster
 */
class NodeNotFoundException extends \RuntimeException
{
    private string $nodeName;

    /**
     * @param string $nodeName Name of the node that was not found
     */
    public function __construct(string $nodeName)
    {
        parent::__construct(sprintf('Node "%s" not found', $nodeName));
        $this->nodeName = $nodeName;
    }

    /**
     * Get the name of the node that was not found
     *
     * @return string
     */
    public function getNodeName(): string
    {
        return $this->nodeName;
    }
}
