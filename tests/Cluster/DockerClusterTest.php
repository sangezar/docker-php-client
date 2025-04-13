<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Tests\Cluster;

use PHPUnit\Framework\TestCase;
use Sangezar\DockerClient\Cluster\DockerCluster;
use Sangezar\DockerClient\DockerClient;
use Sangezar\DockerClient\Exception\NodeNotFoundException;

/**
 * Tests for Docker cluster functionality
 */
class DockerClusterTest extends TestCase
{
    private DockerCluster $cluster;

    /**
     * Setup before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->cluster = new DockerCluster();
    }

    /**
     * Tests adding a node to the cluster
     */
    public function testAddNode(): void
    {
        $client = DockerClient::createUnix();
        $this->cluster->addNode('test', $client, ['prod', 'web']);

        $this->assertTrue($this->cluster->hasNode('test'));
        $this->assertCount(1, $this->cluster->getNodes());
        $this->assertSame($client, $this->cluster->node('test'));
    }

    /**
     * Tests getting nodes by tag
     */
    public function testGetNodesByTag(): void
    {
        $client1 = DockerClient::createUnix();
        $client2 = DockerClient::createUnix();
        $client3 = DockerClient::createUnix();

        $this->cluster
            ->addNode('prod1', $client1, ['prod', 'web'])
            ->addNode('prod2', $client2, ['prod', 'db'])
            ->addNode('staging', $client3, ['staging']);

        $prodNodes = $this->cluster->getNodesByTag('prod');
        $this->assertCount(2, $prodNodes);
        $this->assertArrayHasKey('prod1', $prodNodes);
        $this->assertArrayHasKey('prod2', $prodNodes);

        $webNodes = $this->cluster->getNodesByTag('web');
        $this->assertCount(1, $webNodes);
        $this->assertArrayHasKey('prod1', $webNodes);

        $stagingNodes = $this->cluster->getNodesByTag('staging');
        $this->assertCount(1, $stagingNodes);
        $this->assertArrayHasKey('staging', $stagingNodes);
    }

    /**
     * Tests removing a node from the cluster
     */
    public function testRemoveNode(): void
    {
        $client = DockerClient::createUnix();
        $this->cluster->addNode('test', $client, ['prod']);

        $this->assertTrue($this->cluster->hasNode('test'));
        $this->assertCount(1, $this->cluster->getNodesByTag('prod'));

        $this->cluster->removeNode('test');

        $this->assertFalse($this->cluster->hasNode('test'));
        $this->assertCount(0, $this->cluster->getNodesByTag('prod'));
    }

    /**
     * Tests that an exception is thrown when a node is not found
     */
    public function testNodeNotFound(): void
    {
        $this->expectException(NodeNotFoundException::class);
        $this->expectExceptionMessage('Node "test" not found');
        $this->cluster->node('test');
    }

    /**
     * Tests filtering nodes in the cluster
     */
    public function testFilter(): void
    {
        $client1 = DockerClient::createUnix();
        $client2 = DockerClient::createUnix();

        $this->cluster
            ->addNode('prod1', $client1)
            ->addNode('staging', $client2);

        $filtered = $this->cluster->filter(
            fn ($client, $name) => str_starts_with($name, 'prod')
        );

        $this->assertCount(1, $filtered->getNodes());
        $this->assertArrayHasKey('prod1', $filtered->getNodes());
    }
}
