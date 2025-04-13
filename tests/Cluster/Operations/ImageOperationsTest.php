<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Tests\Cluster\Operations;

use PHPUnit\Framework\TestCase;
use Sangezar\DockerClient\Cluster\Operations\ImageOperations;
use Sangezar\DockerClient\Config\ImageBuildOptions;
use Sangezar\DockerClient\DockerClient;

/**
 * Tests for ImageOperations class
 */
class ImageOperationsTest extends TestCase
{
    private ImageOperations $operations;
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

        $this->operations = new ImageOperations($this->nodes);
    }

    /**
     * Tests listing images across nodes
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
     * Tests listing images with filters across nodes
     */
    public function testListWithFilters(): void
    {
        $parameters = [
            'all' => true,
            'filters' => [
                'reference' => ['nginx'],
            ],
        ];

        $results = $this->operations->list($parameters);

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertArrayHasKey('node1', $results);
        $this->assertArrayHasKey('node2', $results);
    }

    /**
     * Tests building an image across nodes
     */
    public function testBuild(): void
    {
        // Create a temporary directory for the context
        $tempDir = sys_get_temp_dir() . '/docker-build-' . uniqid();
        mkdir($tempDir, 0777, true);

        // Create a Dockerfile
        $dockerfile = $tempDir . '/Dockerfile';
        file_put_contents($dockerfile, "FROM alpine:latest\nCMD [\"echo\", \"Hello World\"]");

        $parameters = [
            't' => 'test-image:latest',
            'dockerfile' => 'Dockerfile',
        ];

        $config = [
            'context' => $tempDir,
        ];

        $results = $this->operations->build($parameters, $config);

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertArrayHasKey('node1', $results);
        $this->assertArrayHasKey('node2', $results);

        // Remove the temporary directory
        unlink($dockerfile);
        rmdir($tempDir);
    }

    /**
     * Tests building an image with options across nodes
     */
    public function testBuildWithOptions(): void
    {
        // Create a temporary directory for the context
        $tempDir = sys_get_temp_dir() . '/docker-build-options-' . uniqid();
        mkdir($tempDir, 0777, true);

        // Create a Dockerfile
        $dockerfile = $tempDir . '/Dockerfile';
        file_put_contents($dockerfile, "FROM alpine:latest\nCMD [\"echo\", \"Hello World\"]");

        $options = ImageBuildOptions::create()
            ->setTag('test-image:latest')
            ->setContext($tempDir)
            ->setDockerfilePath('Dockerfile')
            ->setNoCache(true)
            ->addLabel('maintainer', 'test');

        $results = $this->operations->buildWithOptions($options);

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertArrayHasKey('node1', $results);
        $this->assertArrayHasKey('node2', $results);

        // Remove the temporary directory
        unlink($dockerfile);
        rmdir($tempDir);
    }

    /**
     * Tests creating an image across nodes
     */
    public function testCreate(): void
    {
        $results = $this->operations->create('alpine', 'latest');

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertArrayHasKey('node1', $results);
        $this->assertArrayHasKey('node2', $results);
    }

    /**
     * Tests inspecting an image across nodes
     */
    public function testInspect(): void
    {
        $results = $this->operations->inspect('alpine:latest');

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
     * Tests getting image history across nodes
     */
    public function testHistory(): void
    {
        $results = $this->operations->history('alpine:latest');

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
     * Tests removing an image across nodes
     */
    public function testRemove(): void
    {
        $results = $this->operations->remove('test-image:latest');

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
     * Tests pruning images across nodes
     */
    public function testPrune(): void
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

    /**
     * Tests checking if an image exists across nodes
     */
    public function testExists(): void
    {
        $results = $this->operations->exists('alpine:latest');

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

    /**
     * Tests checking if an image exists on all nodes
     */
    public function testExistsOnAllNodes(): void
    {
        $result = $this->operations->existsOnAllNodes('alpine:latest');

        // Result should be a boolean value
        $this->assertIsBool($result);
    }

    /**
     * Tests getting nodes that have a specific image
     */
    public function testGetNodesWithImage(): void
    {
        $results = $this->operations->getNodesWithImage('alpine:latest');

        // Result should be an array of node names
        $this->assertIsArray($results);
        foreach ($results as $nodeName) {
            $this->assertIsString($nodeName);
        }
    }

    /**
     * Tests tagging an image across nodes
     */
    public function testTag(): void
    {
        $results = $this->operations->tag('alpine:latest', 'myalpine', 'test');

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
     * Tests searching for images across nodes
     */
    public function testSearch(): void
    {
        $term = 'alpine';
        $parameters = [
            'limit' => 5,
        ];

        $results = $this->operations->search($term, $parameters);

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
