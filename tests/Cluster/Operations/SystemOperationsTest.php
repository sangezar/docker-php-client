<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Tests\Cluster\Operations;

use PHPUnit\Framework\TestCase;
use Sangezar\DockerClient\Cluster\Operations\SystemOperations;
use Sangezar\DockerClient\DockerClient;

/**
 * Tests for SystemOperations class
 */
class SystemOperationsTest extends TestCase
{
    private SystemOperations $operations;
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

        $this->operations = new SystemOperations($this->nodes);
    }

    /**
     * Tests the ping operation across nodes
     */
    public function testPing(): void
    {
        $results = $this->operations->ping();

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertArrayHasKey('node1', $results);
        $this->assertArrayHasKey('node2', $results);

        // Results should be boolean values or arrays with errors
        foreach ($results as $nodeResult) {
            $this->assertTrue(is_bool($nodeResult) || is_array($nodeResult));
            if (is_array($nodeResult) && isset($nodeResult['error'])) {
                $this->assertTrue($nodeResult['error']);
                $this->assertArrayHasKey('message', $nodeResult);
            }
        }
    }

    /**
     * Tests the info operation across nodes
     */
    public function testInfo(): void
    {
        $results = $this->operations->info();

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertArrayHasKey('node1', $results);
        $this->assertArrayHasKey('node2', $results);

        // Results can be either data arrays or arrays with errors
        foreach ($results as $nodeResult) {
            $this->assertTrue(is_array($nodeResult));
            if (isset($nodeResult['error'])) {
                $this->assertTrue($nodeResult['error']);
                $this->assertArrayHasKey('message', $nodeResult);
            }
        }
    }

    /**
     * Tests the version operation across nodes
     */
    public function testVersion(): void
    {
        $results = $this->operations->version();

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertArrayHasKey('node1', $results);
        $this->assertArrayHasKey('node2', $results);

        // Results can be either data arrays or arrays with errors
        foreach ($results as $nodeResult) {
            $this->assertTrue(is_array($nodeResult));
            if (isset($nodeResult['error'])) {
                $this->assertTrue($nodeResult['error']);
                $this->assertArrayHasKey('message', $nodeResult);
            }
        }
    }

    /**
     * Tests the events operation across nodes
     */
    public function testEvents(): void
    {
        $parameters = [
            'since' => date('c', time() - 3600),
            'until' => date('c', time()),
        ];

        $results = $this->operations->events($parameters);

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertArrayHasKey('node1', $results);
        $this->assertArrayHasKey('node2', $results);
    }

    /**
     * Tests the disk free operation across nodes
     */
    public function testDf(): void
    {
        $results = $this->operations->df();

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertArrayHasKey('node1', $results);
        $this->assertArrayHasKey('node2', $results);

        // Results can be either data arrays or arrays with errors
        foreach ($results as $nodeResult) {
            $this->assertTrue(is_array($nodeResult));
            if (isset($nodeResult['error'])) {
                $this->assertTrue($nodeResult['error']);
                $this->assertArrayHasKey('message', $nodeResult);
            }
        }
    }

    /**
     * Tests the prune containers operation across nodes
     */
    public function testPruneContainers(): void
    {
        $results = $this->operations->pruneContainers();

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertArrayHasKey('node1', $results);
        $this->assertArrayHasKey('node2', $results);

        // Results can be either data arrays or arrays with errors
        foreach ($results as $nodeResult) {
            $this->assertTrue(is_array($nodeResult));
            if (isset($nodeResult['error'])) {
                $this->assertTrue($nodeResult['error']);
                $this->assertArrayHasKey('message', $nodeResult);
            }
        }
    }

    /**
     * Tests the prune images operation across nodes
     */
    public function testPruneImages(): void
    {
        $results = $this->operations->pruneImages();

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertArrayHasKey('node1', $results);
        $this->assertArrayHasKey('node2', $results);

        // Results can be either data arrays or arrays with errors
        foreach ($results as $nodeResult) {
            $this->assertTrue(is_array($nodeResult));
            if (isset($nodeResult['error'])) {
                $this->assertTrue($nodeResult['error']);
                $this->assertArrayHasKey('message', $nodeResult);
            }
        }
    }

    /**
     * Tests the prune networks operation across nodes
     */
    public function testPruneNetworks(): void
    {
        $results = $this->operations->pruneNetworks();

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertArrayHasKey('node1', $results);
        $this->assertArrayHasKey('node2', $results);

        // Results can be either data arrays or arrays with errors
        foreach ($results as $nodeResult) {
            $this->assertTrue(is_array($nodeResult));
            if (isset($nodeResult['error'])) {
                $this->assertTrue($nodeResult['error']);
                $this->assertArrayHasKey('message', $nodeResult);
            }
        }
    }

    /**
     * Tests the prune volumes operation across nodes
     */
    public function testPruneVolumes(): void
    {
        $results = $this->operations->pruneVolumes();

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertArrayHasKey('node1', $results);
        $this->assertArrayHasKey('node2', $results);

        // Results can be either data arrays or arrays with errors
        foreach ($results as $nodeResult) {
            $this->assertTrue(is_array($nodeResult));
            if (isset($nodeResult['error'])) {
                $this->assertTrue($nodeResult['error']);
                $this->assertArrayHasKey('message', $nodeResult);
            }
        }
    }

    /**
     * Tests the prune system operation across nodes
     */
    public function testPruneSystem(): void
    {
        $results = $this->operations->prune();

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertArrayHasKey('node1', $results);
        $this->assertArrayHasKey('node2', $results);

        // Results can be either data arrays or arrays with errors
        foreach ($results as $nodeResult) {
            $this->assertTrue(is_array($nodeResult));
            if (isset($nodeResult['error'])) {
                $this->assertTrue($nodeResult['error']);
                $this->assertArrayHasKey('message', $nodeResult);
            }
        }
    }
}
