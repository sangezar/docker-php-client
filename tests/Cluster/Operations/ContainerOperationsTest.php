<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Tests\Cluster\Operations;

use PHPUnit\Framework\TestCase;
use Sangezar\DockerClient\Cluster\Operations\ContainerOperations;
use Sangezar\DockerClient\Config\ContainerConfig;
use Sangezar\DockerClient\DockerClient;

/**
 * Tests for ContainerOperations class
 */
class ContainerOperationsTest extends TestCase
{
    private ContainerOperations $operations;
    private array $nodes;

    /**
     * Setup before each test
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->nodes = [
            'node1' => DockerClient::createUnix(),
            'node2' => DockerClient::createUnix('/var/run/docker2.sock'),
        ];

        $this->operations = new ContainerOperations($this->nodes);
    }

    /**
     * Tests listing containers across nodes
     */
    public function testList(): void
    {
        $results = $this->operations->list();

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertArrayHasKey('node1', $results);
        $this->assertArrayHasKey('node2', $results);
    }

    /**
     * Tests creating a container across nodes
     */
    public function testCreate(): void
    {
        $config = ContainerConfig::create()
            ->setImage('nginx')
            ->setName('test-container')
            ->addPort(80, 80, 'tcp');

        $results = $this->operations->create($config);

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertArrayHasKey('node1', $results);
        $this->assertArrayHasKey('node2', $results);
    }

    /**
     * Tests starting a container across nodes
     */
    public function testStart(): void
    {
        $results = $this->operations->start('test-container');

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertArrayHasKey('node1', $results);
        $this->assertArrayHasKey('node2', $results);

        // Results can be either boolean values or arrays with errors
        foreach ($results as $nodeResult) {
            $this->assertTrue(is_bool($nodeResult) || is_array($nodeResult));
            if (is_array($nodeResult) && isset($nodeResult['error'])) {
                $this->assertTrue($nodeResult['error']);
                $this->assertArrayHasKey('message', $nodeResult);
            }
        }
    }

    /**
     * Tests stopping a container across nodes
     */
    public function testStop(): void
    {
        $results = $this->operations->stop('test-container');

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertArrayHasKey('node1', $results);
        $this->assertArrayHasKey('node2', $results);

        // Results can be either boolean values or arrays with errors
        foreach ($results as $nodeResult) {
            $this->assertTrue(is_bool($nodeResult) || is_array($nodeResult));
            if (is_array($nodeResult) && isset($nodeResult['error'])) {
                $this->assertTrue($nodeResult['error']);
                $this->assertArrayHasKey('message', $nodeResult);
            }
        }
    }

    /**
     * Tests removing a container across nodes
     */
    public function testRemove(): void
    {
        $results = $this->operations->remove('test-container');

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertArrayHasKey('node1', $results);
        $this->assertArrayHasKey('node2', $results);

        // Results can be either boolean values or arrays with errors
        foreach ($results as $nodeResult) {
            $this->assertTrue(is_bool($nodeResult) || is_array($nodeResult));
            if (is_array($nodeResult) && isset($nodeResult['error'])) {
                $this->assertTrue($nodeResult['error']);
                $this->assertArrayHasKey('message', $nodeResult);
            }
        }
    }
}
