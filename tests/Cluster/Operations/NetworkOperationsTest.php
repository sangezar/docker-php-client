<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Tests\Cluster\Operations;

use PHPUnit\Framework\TestCase;
use Sangezar\DockerClient\Cluster\Operations\NetworkOperations;
use Sangezar\DockerClient\Config\NetworkConfig;
use Sangezar\DockerClient\DockerClient;

class NetworkOperationsTest extends TestCase
{
    private NetworkOperations $operations;
    private array $nodes;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nodes = [
            'node1' => DockerClient::createUnix(),
            'node2' => DockerClient::createUnix('/var/run/docker2.sock'),
        ];

        $this->operations = new NetworkOperations($this->nodes);
    }

    public function testList(): void
    {
        $results = $this->operations->list();

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertArrayHasKey('node1', $results);
        $this->assertArrayHasKey('node2', $results);
    }

    public function testCreate(): void
    {
        $config = NetworkConfig::create()
            ->setName('test-network')
            ->setDriver('bridge')
            ->setAttachable(true);

        $results = $this->operations->create($config);

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertArrayHasKey('node1', $results);
        $this->assertArrayHasKey('node2', $results);
    }

    public function testInspect(): void
    {
        $results = $this->operations->inspect('test-network');

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertArrayHasKey('node1', $results);
        $this->assertArrayHasKey('node2', $results);

        // Results can be either a data array or an error array
        foreach ($results as $nodeResult) {
            $this->assertTrue(is_array($nodeResult));
            if (isset($nodeResult['error'])) {
                $this->assertTrue($nodeResult['error']);
                $this->assertArrayHasKey('message', $nodeResult);
            }
        }
    }

    public function testRemove(): void
    {
        $results = $this->operations->remove('test-network');

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertArrayHasKey('node1', $results);
        $this->assertArrayHasKey('node2', $results);

        // Results can be either a boolean value or an error array
        foreach ($results as $nodeResult) {
            $this->assertTrue(is_bool($nodeResult) || is_array($nodeResult));
            if (is_array($nodeResult) && isset($nodeResult['error'])) {
                $this->assertTrue($nodeResult['error']);
                $this->assertArrayHasKey('message', $nodeResult);
            }
        }
    }

    public function testConnect(): void
    {
        $results = $this->operations->connect('test-network', 'test-container');

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertArrayHasKey('node1', $results);
        $this->assertArrayHasKey('node2', $results);

        // Results can be either a boolean value or an error array
        foreach ($results as $nodeResult) {
            $this->assertTrue(is_bool($nodeResult) || is_array($nodeResult));
            if (is_array($nodeResult) && isset($nodeResult['error'])) {
                $this->assertTrue($nodeResult['error']);
                $this->assertArrayHasKey('message', $nodeResult);
            }
        }
    }

    public function testDisconnect(): void
    {
        $results = $this->operations->disconnect('test-network', 'test-container');

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertArrayHasKey('node1', $results);
        $this->assertArrayHasKey('node2', $results);

        // Results can be either a boolean value or an error array
        foreach ($results as $nodeResult) {
            $this->assertTrue(is_bool($nodeResult) || is_array($nodeResult));
            if (is_array($nodeResult) && isset($nodeResult['error'])) {
                $this->assertTrue($nodeResult['error']);
                $this->assertArrayHasKey('message', $nodeResult);
            }
        }
    }

    public function testPrune(): void
    {
        $results = $this->operations->prune();

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertArrayHasKey('node1', $results);
        $this->assertArrayHasKey('node2', $results);

        // Results can be either a data array or an error array
        foreach ($results as $nodeResult) {
            $this->assertTrue(is_array($nodeResult));
            if (isset($nodeResult['error'])) {
                $this->assertTrue($nodeResult['error']);
                $this->assertArrayHasKey('message', $nodeResult);
            }
        }
    }

    public function testExists(): void
    {
        $results = $this->operations->exists('test-network');

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertArrayHasKey('node1', $results);
        $this->assertArrayHasKey('node2', $results);

        // Results should be boolean values
        foreach ($results as $nodeResult) {
            $this->assertTrue(is_bool($nodeResult) || is_array($nodeResult));
            if (is_array($nodeResult) && isset($nodeResult['error'])) {
                $this->assertTrue($nodeResult['error']);
                $this->assertArrayHasKey('message', $nodeResult);
            }
        }
    }

    public function testExistsOnAllNodes(): void
    {
        $result = $this->operations->existsOnAllNodes('test-network');

        // Result should be a boolean value
        $this->assertIsBool($result);
    }

    public function testGetNodesWithNetwork(): void
    {
        $results = $this->operations->getNodesWithNetwork('test-network');

        // Result should be an array of node names
        $this->assertIsArray($results);
        foreach ($results as $nodeName) {
            $this->assertIsString($nodeName);
        }
    }
}
