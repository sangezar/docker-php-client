<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Tests\Cluster\Operations;

use PHPUnit\Framework\TestCase;
use Sangezar\DockerClient\Api\Interface\VolumeInterface;
use Sangezar\DockerClient\Cluster\Operations\VolumeOperations;
use Sangezar\DockerClient\Config\VolumeConfig;
use Sangezar\DockerClient\DockerClient;

class VolumeOperationsTest extends TestCase
{
    private VolumeOperations $operations;
    private array $nodes;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nodes = [
            'node1' => DockerClient::createUnix(),
            'node2' => DockerClient::createUnix('/var/run/docker2.sock'),
        ];

        $this->operations = new VolumeOperations($this->nodes);
    }

    public function testList(): void
    {
        $results = $this->operations->list();

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

    public function testListWithFilters(): void
    {
        $filters = [
            'driver' => [VolumeInterface::DRIVER_LOCAL],
            'dangling' => ['true'],
        ];

        $results = $this->operations->list($filters);

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

    public function testCreate(): void
    {
        $volumeName = 'test-volume-' . uniqid();

        $config = VolumeConfig::create()
            ->setName($volumeName)
            ->setDriver(VolumeInterface::DRIVER_LOCAL)
            ->addDriverOpt('type', 'tmpfs')
            ->addDriverOpt('device', 'tmpfs')
            ->addDriverOpt('o', 'size=100m')
            ->addLabel('test-label', 'test-value');

        $results = $this->operations->create($config);

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

    public function testInspect(): void
    {
        $volumeName = 'test-volume-' . uniqid();

        // First create a volume for testing
        $config = VolumeConfig::create()
            ->setName($volumeName)
            ->setDriver(VolumeInterface::DRIVER_LOCAL);

        $this->operations->create($config);

        // Test inspecting the volume
        $results = $this->operations->inspect($volumeName);

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
        $volumeName = 'test-volume-' . uniqid();

        // First create a volume for testing
        $config = VolumeConfig::create()
            ->setName($volumeName)
            ->setDriver(VolumeInterface::DRIVER_LOCAL);

        $this->operations->create($config);

        // Test removing the volume
        $results = $this->operations->remove($volumeName);

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertArrayHasKey('node1', $results);
        $this->assertArrayHasKey('node2', $results);

        // Results can be either a data array or an error array
        foreach ($results as $nodeResult) {
            $this->assertTrue(is_bool($nodeResult) || is_array($nodeResult));
            if (is_array($nodeResult) && isset($nodeResult['error'])) {
                $this->assertTrue($nodeResult['error']);
                $this->assertArrayHasKey('message', $nodeResult);
            }
        }
    }

    public function testExists(): void
    {
        $volumeName = 'test-volume-' . uniqid();

        // First create a volume for testing
        $config = VolumeConfig::create()
            ->setName($volumeName)
            ->setDriver(VolumeInterface::DRIVER_LOCAL);

        $this->operations->create($config);

        // Test checking if the volume exists
        $results = $this->operations->exists($volumeName);

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertArrayHasKey('node1', $results);
        $this->assertArrayHasKey('node2', $results);

        // Results can be either boolean values or an error array
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
        $volumeName = 'test-volume-' . uniqid();

        // First create a volume for testing
        $config = VolumeConfig::create()
            ->setName($volumeName)
            ->setDriver(VolumeInterface::DRIVER_LOCAL);

        $this->operations->create($config);

        // Test checking if the volume exists on all nodes
        $exists = $this->operations->existsOnAllNodes($volumeName);

        // Result should be a boolean value
        $this->assertTrue(is_bool($exists));
    }

    public function testGetNodesWithVolume(): void
    {
        $volumeName = 'test-volume-' . uniqid();

        // First create a volume for testing
        $config = VolumeConfig::create()
            ->setName($volumeName)
            ->setDriver(VolumeInterface::DRIVER_LOCAL);

        $this->operations->create($config);

        // Test getting nodes with the volume
        $nodes = $this->operations->getNodesWithVolume($volumeName);

        // Result should be an array of node names
        $this->assertIsArray($nodes);
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

    public function testPruneWithFilters(): void
    {
        $filters = [
            'label' => ['test=true'],
        ];

        $results = $this->operations->prune($filters);

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
}
