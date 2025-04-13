<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Tests;

use PHPUnit\Framework\TestCase;
use Sangezar\DockerClient\Api\Container;
use Sangezar\DockerClient\Api\Image;
use Sangezar\DockerClient\Api\Network;
use Sangezar\DockerClient\Api\System;
use Sangezar\DockerClient\Api\Volume;
use Sangezar\DockerClient\DockerClient;

/**
 * Tests for the main DockerClient class
 *
 * This test suite verifies that the DockerClient class correctly initializes
 * and provides access to all the Docker API endpoints.
 */
class DockerClientTest extends TestCase
{
    /**
     * @var DockerClient Instance of the Docker client
     */
    private DockerClient $client;

    /**
     * Set up testing environment before each test
     *
     * Initializes a new DockerClient instance with default configuration
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->client = new DockerClient();
    }

    /**
     * Tests that the DockerClient can be instantiated correctly
     */
    public function testClientCanBeInstantiated(): void
    {
        $this->assertInstanceOf(DockerClient::class, $this->client);
    }

    /**
     * Tests that the client provides access to the System API
     */
    public function testClientHasSystemApi(): void
    {
        $this->assertInstanceOf(System::class, $this->client->system());
    }

    /**
     * Tests that the client provides access to the Container API
     */
    public function testClientHasContainerApi(): void
    {
        $this->assertInstanceOf(Container::class, $this->client->container());
    }

    /**
     * Tests that the client provides access to the Image API
     */
    public function testClientHasImageApi(): void
    {
        $this->assertInstanceOf(Image::class, $this->client->image());
    }

    /**
     * Tests that the client provides access to the Network API
     */
    public function testClientHasNetworkApi(): void
    {
        $this->assertInstanceOf(Network::class, $this->client->network());
    }

    /**
     * Tests that the client provides access to the Volume API
     */
    public function testClientHasVolumeApi(): void
    {
        $this->assertInstanceOf(Volume::class, $this->client->volume());
    }
}
