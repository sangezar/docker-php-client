<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Tests\Api;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Sangezar\DockerClient\Api\Interface\SystemInterface;
use Sangezar\DockerClient\Api\System;
use Sangezar\DockerClient\Exception\Validation\MissingRequiredParameterException;

class SystemTest extends TestCase
{
    /**
     * @var System|MockObject
     */
    private $system;

    /**
     * Setup before tests
     */
    protected function setUp(): void
    {
        // Create a partial mock for the System class
        $this->system = $this->getMockBuilder(System::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get', 'post'])
            ->getMock();
    }

    /**
     * Checks the correspondence of constants between interface and implementation
     */
    public function testConstantsMatchBetweenInterfaceAndImplementation(): void
    {
        $interfaceReflection = new ReflectionClass(SystemInterface::class);
        $implementationReflection = new ReflectionClass(System::class);

        $interfaceConstants = $interfaceReflection->getConstants();
        $implementationConstants = $implementationReflection->getConstants();

        // Check that all interface constants are present in the implementation
        foreach ($interfaceConstants as $name => $value) {
            $this->assertArrayHasKey(
                $name,
                $implementationConstants,
                "Constant '{$name}' is missing in the System class"
            );

            $this->assertSame(
                $value,
                $implementationConstants[$name],
                "Value of constant '{$name}' differs between interface and implementation"
            );
        }
    }

    /**
     * Checks the presence of all constants for event types
     */
    public function testEventTypeConstants(): void
    {
        $this->assertSame('container', System::EVENT_TYPE_CONTAINER);
        $this->assertSame('image', System::EVENT_TYPE_IMAGE);
        $this->assertSame('volume', System::EVENT_TYPE_VOLUME);
        $this->assertSame('network', System::EVENT_TYPE_NETWORK);
        $this->assertSame('daemon', System::EVENT_TYPE_DAEMON);
        $this->assertSame('plugin', System::EVENT_TYPE_PLUGIN);
        $this->assertSame('service', System::EVENT_TYPE_SERVICE);
        $this->assertSame('node', System::EVENT_TYPE_NODE);
        $this->assertSame('secret', System::EVENT_TYPE_SECRET);
        $this->assertSame('config', System::EVENT_TYPE_CONFIG);
    }

    /**
     * Checks the presence of constants for container event types
     */
    public function testContainerEventConstants(): void
    {
        $this->assertSame('create', System::CONTAINER_EVENT_CREATE);
        $this->assertSame('start', System::CONTAINER_EVENT_START);
        $this->assertSame('stop', System::CONTAINER_EVENT_STOP);
        $this->assertSame('die', System::CONTAINER_EVENT_DIE);
        $this->assertSame('destroy', System::CONTAINER_EVENT_DESTROY);
        $this->assertSame('kill', System::CONTAINER_EVENT_KILL);
        // Let's check a few more constants as examples
        $this->assertSame('pause', System::CONTAINER_EVENT_PAUSE);
        $this->assertSame('unpause', System::CONTAINER_EVENT_UNPAUSE);
        $this->assertSame('restart', System::CONTAINER_EVENT_RESTART);
    }

    /**
     * Checks the presence of constants for image event types
     */
    public function testImageEventConstants(): void
    {
        $this->assertSame('delete', System::IMAGE_EVENT_DELETE);
        $this->assertSame('import', System::IMAGE_EVENT_IMPORT);
        $this->assertSame('load', System::IMAGE_EVENT_LOAD);
        $this->assertSame('pull', System::IMAGE_EVENT_PULL);
        $this->assertSame('push', System::IMAGE_EVENT_PUSH);
        $this->assertSame('tag', System::IMAGE_EVENT_TAG);
        $this->assertSame('untag', System::IMAGE_EVENT_UNTAG);
    }

    /**
     * Checks the version() method
     */
    public function testVersion(): void
    {
        $expectedResponse = [
            'Version' => '20.10.8',
            'ApiVersion' => '1.41',
            'MinAPIVersion' => '1.12',
            'GitCommit' => 'b39544b',
            'GoVersion' => 'go1.16.6',
            'Os' => 'linux',
            'Arch' => 'amd64',
            'KernelVersion' => '5.10.0',
            'BuildTime' => '2021-08-01T12:34:56.789012345+00:00',
        ];

        $this->system->expects($this->once())
            ->method('get')
            ->with('/version')
            ->willReturn($expectedResponse);

        $result = $this->system->version();

        $this->assertEquals($expectedResponse, $result);
    }

    /**
     * Checks the info() method
     */
    public function testInfo(): void
    {
        $expectedResponse = [
            'Containers' => 5,
            'ContainersRunning' => 2,
            'ContainersPaused' => 0,
            'ContainersStopped' => 3,
            'Images' => 45,
            'Driver' => 'overlay2',
            'DriverStatus' => [],
            'SystemStatus' => null,
            'Plugins' => [],
            'MemoryLimit' => true,
            'SwapLimit' => true,
            'KernelMemory' => true,
            'CpuCfsPeriod' => true,
            'CpuCfsQuota' => true,
            'CPUShares' => true,
            'CPUSet' => true,
            'PidsLimit' => true,
            'IPv4Forwarding' => true,
            'BridgeNfIptables' => true,
            'BridgeNfIp6tables' => true,
            'Debug' => false,
            'NFd' => 28,
            'OomKillDisable' => true,
            'NGoroutines' => 42,
            'SystemTime' => '2021-08-20T12:34:56.789012345Z',
            'LoggingDriver' => 'json-file',
            'CgroupDriver' => 'systemd',
            'CgroupVersion' => '2',
            'NEventsListener' => 0,
            'KernelVersion' => '5.10.0',
            'OperatingSystem' => 'Ubuntu 20.04.2 LTS',
            'OSVersion' => '20.04',
            'OSType' => 'linux',
            'Architecture' => 'x86_64',
            'IndexServerAddress' => 'https://index.docker.io/v1/',
            'RegistryConfig' => [],
            'NCPU' => 8,
            'MemTotal' => 16658644992,
            'DockerRootDir' => '/var/lib/docker',
            'HttpProxy' => '',
            'HttpsProxy' => '',
            'NoProxy' => '',
            'Name' => 'docker-host',
            'Labels' => [],
            'ExperimentalBuild' => false,
            'ServerVersion' => '20.10.8',
            'DefaultRuntime' => 'runc',
            'Swarm' => [],
            'LiveRestoreEnabled' => false,
            'Isolation' => '',
            'InitBinary' => 'docker-init',
            'ContainerdCommit' => [],
            'RuncCommit' => [],
            'InitCommit' => [],
            'SecurityOptions' => [],
        ];

        $this->system->expects($this->once())
            ->method('get')
            ->with('/info')
            ->willReturn($expectedResponse);

        $result = $this->system->info();

        $this->assertEquals($expectedResponse, $result);
    }

    /**
     * Checks the ping() method
     */
    public function testPingReturnsTrue(): void
    {
        $this->system->expects($this->once())
            ->method('get')
            ->with('/_ping')
            ->willReturn([]);

        $result = $this->system->ping();

        $this->assertTrue($result);
    }

    /**
     * Checks the ping() method when server is unavailable
     */
    public function testPingReturnsFalseWhenServerUnavailable(): void
    {
        $this->system->expects($this->once())
            ->method('get')
            ->with('/_ping')
            ->will($this->throwException(new \Exception('Connection refused')));

        $result = $this->system->ping();

        $this->assertFalse($result);
    }

    /**
     * Checks the auth() method with valid parameters
     */
    public function testAuthWithValidCredentials(): void
    {
        $authConfig = [
            'username' => 'testuser',
            'password' => 'testpassword',
            'serveraddress' => 'https://index.docker.io/v1/',
        ];

        $expectedResponse = [
            'Status' => 'Login Succeeded',
            'IdentityToken' => 'token123',
        ];

        $this->system->expects($this->once())
            ->method('post')
            ->with('/auth', ['json' => $authConfig])
            ->willReturn($expectedResponse);

        $result = $this->system->auth($authConfig);

        $this->assertEquals($expectedResponse, $result);
    }

    /**
     * Checks the auth() method with missing parameters
     */
    public function testAuthWithMissingCredentials(): void
    {
        $this->expectException(MissingRequiredParameterException::class);

        $authConfig = [
            'username' => 'testuser',
            // Missing password and serveraddress
        ];

        $this->system->auth($authConfig);
    }

    /**
     * Checks the events() method with valid filters
     */
    public function testEventsWithValidFilters(): void
    {
        $filters = [
            'type' => [System::EVENT_TYPE_CONTAINER],
            'since' => strtotime('-1 day'),
        ];

        $expectedResponse = [
            [
                'Type' => 'container',
                'Action' => 'start',
                'Actor' => [
                    'ID' => 'abc123',
                    'Attributes' => [
                        'name' => 'test-container',
                    ],
                ],
                'time' => time(),
                'timeNano' => time() * 1000000000,
            ],
        ];

        // Check that query parameters are correctly transformed
        $this->system->expects($this->once())
            ->method('get')
            ->with(
                $this->equalTo('/events'),
                $this->callback(function ($params) use ($filters) {
                    // Check that filters were correctly encoded in JSON
                    return isset($params['query']['filters']);
                })
            )
            ->willReturn($expectedResponse);

        $result = $this->system->events($filters);

        $this->assertEquals($expectedResponse, $result);
    }

    /**
     * Checks the prune() method for removing unused resources
     */
    public function testPrune(): void
    {
        $options = [
            'containers' => true,
            'images' => true,
            'volumes' => true,
            'networks' => true,
        ];

        $expectedResponse = [
            'ContainersDeleted' => ['abc123'],
            'SpaceReclaimed' => 123456789,
            'ImagesDeleted' => [
                ['Untagged' => 'image1:latest'],
                ['Deleted' => 'sha256:abc123def456'],
            ],
            'VolumesDeleted' => ['volume1', 'volume2'],
            'NetworksDeleted' => ['network1'],
        ];

        $this->system->expects($this->once())
            ->method('post')
            ->with('/system/prune', ['query' => ['all' => true]])
            ->willReturn($expectedResponse);

        $result = $this->system->prune(['all' => true]);

        $this->assertEquals($expectedResponse, $result);
    }

    /**
     * Checks the dataUsage() method for getting disk usage data
     */
    public function testDataUsage(): void
    {
        $expectedResponse = [
            'LayersSize' => 1234567890,
            'Images' => [
                [
                    'Id' => 'sha256:abc123',
                    'ParentId' => '',
                    'RepoTags' => ['image:latest'],
                    'RepoDigests' => [],
                    'Created' => 1623456789,
                    'Size' => 123456789,
                    'SharedSize' => 0,
                    'VirtualSize' => 123456789,
                    'Labels' => [],
                    'Containers' => 1,
                ],
            ],
            'Containers' => [
                [
                    'Id' => 'abc123',
                    'Names' => ['/test-container'],
                    'Image' => 'image:latest',
                    'ImageID' => 'sha256:abc123',
                    'Command' => '/bin/sh',
                    'Created' => 1623456789,
                    'Ports' => [],
                    'SizeRw' => 12345,
                    'SizeRootFs' => 123456789,
                    'Labels' => [],
                    'State' => 'running',
                    'Status' => 'Up 2 hours',
                    'HostConfig' => [],
                    'NetworkSettings' => [],
                    'Mounts' => [],
                ],
            ],
            'Volumes' => [
                [
                    'Name' => 'volume1',
                    'Driver' => 'local',
                    'Mountpoint' => '/var/lib/docker/volumes/volume1/_data',
                    'Labels' => [],
                    'Scope' => 'local',
                    'Options' => [],
                    'UsageData' => [
                        'Size' => 12345,
                        'RefCount' => 1,
                    ],
                ],
            ],
            'BuildCache' => [],
        ];

        $this->system->expects($this->once())
            ->method('get')
            ->with('/system/df')
            ->willReturn($expectedResponse);

        $result = $this->system->dataUsage();

        $this->assertEquals($expectedResponse, $result);
    }
}
