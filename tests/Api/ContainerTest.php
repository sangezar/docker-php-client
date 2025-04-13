<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Tests\Api;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Sangezar\DockerClient\Api\Container;
use Sangezar\DockerClient\Api\Interface\ContainerInterface;
use Sangezar\DockerClient\Config\ContainerConfig;
use Sangezar\DockerClient\Exception\NotFoundException;
use Sangezar\DockerClient\Exception\Validation\InvalidParameterValueException;
use Sangezar\DockerClient\Exception\Validation\MissingRequiredParameterException;

class ContainerTest extends TestCase
{
    /**
     * @var Container|MockObject
     */
    private $container;

    /**
     * Setup before tests
     */
    protected function setUp(): void
    {
        // Create a partial mock for the Container class
        $this->container = $this->getMockBuilder(Container::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get', 'post', 'delete'])
            ->getMock();
    }

    /**
     * Checks the correspondence of constants between interface and implementation
     */
    public function testConstantsMatchBetweenInterfaceAndImplementation(): void
    {
        $interfaceReflection = new ReflectionClass(ContainerInterface::class);
        $implementationReflection = new ReflectionClass(Container::class);

        $interfaceConstants = $interfaceReflection->getConstants();
        $implementationConstants = $implementationReflection->getConstants();

        // Check that all interface constants are present in the implementation
        foreach ($interfaceConstants as $name => $value) {
            $this->assertArrayHasKey(
                $name,
                $implementationConstants,
                "Constant '{$name}' is missing in the Container class"
            );

            $this->assertSame(
                $value,
                $implementationConstants[$name],
                "Value of constant '{$name}' differs between interface and implementation"
            );
        }

        // Check at least one specific signal constant
        $this->assertTrue(isset($implementationConstants['SIGNAL_KILL']), 'Constant SIGNAL_KILL is missing');
        $this->assertSame('SIGKILL', $implementationConstants['SIGNAL_KILL']);
    }

    /**
     * Checks the presence of constants for signals
     */
    public function testSignalConstants(): void
    {
        $this->assertSame('SIGKILL', Container::SIGNAL_KILL);
        $this->assertSame('SIGTERM', Container::SIGNAL_TERM);
        $this->assertSame('SIGINT', Container::SIGNAL_INT);
        $this->assertSame('SIGHUP', Container::SIGNAL_HUP);
        $this->assertSame('SIGSTOP', Container::SIGNAL_STOP);
        $this->assertSame('SIGCONT', Container::SIGNAL_CONT);
        $this->assertSame('SIGUSR1', Container::SIGNAL_USR1);
        $this->assertSame('SIGUSR2', Container::SIGNAL_USR2);
    }

    /**
     * Checks the list method with valid parameters
     */
    public function testListWithValidParameters(): void
    {
        $parameters = [
            'all' => true,
            'limit' => 10,
            'size' => true,
        ];

        $expectedResponse = [
            [
                'Id' => 'abc123',
                'Names' => ['/test-container'],
                'Image' => 'nginx',
                'ImageID' => 'sha256:def456',
                'Command' => 'nginx -g "daemon off;"',
                'Created' => 1627984123,
                'State' => 'running',
                'Status' => 'Up 2 hours',
                'Ports' => [
                    [
                        'IP' => '0.0.0.0',
                        'PrivatePort' => 80,
                        'PublicPort' => 8080,
                        'Type' => 'tcp',
                    ],
                ],
                'Labels' => [],
                'SizeRw' => 123456,
                'SizeRootFs' => 78901234,
                'NetworkSettings' => [
                    'Networks' => [
                        'bridge' => [
                            'IPAddress' => '172.17.0.2',
                        ],
                    ],
                ],
            ],
        ];

        $this->container->expects($this->once())
            ->method('get')
            ->with('/containers/json', ['query' => $parameters])
            ->willReturn($expectedResponse);

        $result = $this->container->list($parameters);

        $this->assertEquals($expectedResponse, $result);
    }

    /**
     * Checks the list method with invalid parameters
     */
    public function testListWithInvalidParameters(): void
    {
        $this->expectException(InvalidParameterValueException::class);

        $this->container->list(['invalid_param' => 'value']);
    }

    /**
     * Checks the list method with invalid limit value
     */
    public function testListWithInvalidLimitValue(): void
    {
        $this->expectException(InvalidParameterValueException::class);

        $this->container->list(['limit' => -1]);
    }

    /**
     * Checks the inspect method with valid ID
     */
    public function testInspectWithValidId(): void
    {
        $containerId = 'abc123';

        $expectedResponse = [
            'Id' => 'abc123',
            'Created' => '2021-08-03T12:34:56.789Z',
            'Path' => 'nginx',
            'Args' => ['-g', 'daemon off;'],
            'State' => [
                'Status' => 'running',
                'Running' => true,
                'Paused' => false,
                'Restarting' => false,
                'OOMKilled' => false,
                'Dead' => false,
                'Pid' => 1234,
                'ExitCode' => 0,
                'Error' => '',
                'StartedAt' => '2021-08-03T12:34:56.789Z',
                'FinishedAt' => '0001-01-01T00:00:00Z',
            ],
            'Image' => 'sha256:def456',
            'Name' => '/test-container',
            'RestartCount' => 0,
            'Driver' => 'overlay2',
            'Platform' => 'linux',
            'MountLabel' => '',
            'ProcessLabel' => '',
            'AppArmorProfile' => '',
            'ExecIDs' => null,
            'HostConfig' => [],
            'GraphDriver' => [],
            'Mounts' => [],
            'Config' => [],
            'NetworkSettings' => [],
        ];

        $this->container->expects($this->once())
            ->method('get')
            ->with("/containers/{$containerId}/json")
            ->willReturn($expectedResponse);

        $result = $this->container->inspect($containerId);

        $this->assertEquals($expectedResponse, $result);
    }

    /**
     * Checks the inspect method with empty ID
     */
    public function testInspectWithEmptyId(): void
    {
        $this->expectException(MissingRequiredParameterException::class);

        $this->container->inspect('');
    }

    /**
     * Checks the inspect method with non-existent container
     */
    public function testInspectWithNonExistentContainer(): void
    {
        $this->expectException(NotFoundException::class);

        $containerId = 'non-existent';

        $this->container->expects($this->once())
            ->method('get')
            ->with("/containers/{$containerId}/json")
            ->will($this->throwException(new \Exception('404 Container not found')));

        $this->container->inspect($containerId);
    }

    /**
     * Checks the create method with valid configuration
     */
    public function testCreateWithValidConfig(): void
    {
        /** @var ContainerConfig&MockObject $config */
        $config = $this->getMockBuilder(ContainerConfig::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['toArray'])
            ->getMock();

        $configArray = [
            'name' => 'test-container',
            'Image' => 'nginx',
            'Env' => ['NGINX_PORT=80'],
            'ExposedPorts' => [
                '80/tcp' => (object)[],
            ],
            'HostConfig' => [
                'PortBindings' => [
                    '80/tcp' => [
                        [
                            'HostIp' => '0.0.0.0',
                            'HostPort' => '8080',
                        ],
                    ],
                ],
            ],
        ];

        $config->expects($this->once())
            ->method('toArray')
            ->willReturn($configArray);

        $expectedResponse = [
            'Id' => 'abc123',
            'Warnings' => [],
        ];

        $this->container->expects($this->once())
            ->method('post')
            ->with(
                '/containers/create',
                [
                    'query' => ['name' => 'test-container'],
                    'json' => [
                        'Image' => 'nginx',
                        'Env' => ['NGINX_PORT=80'],
                        'ExposedPorts' => [
                            '80/tcp' => (object)[],
                        ],
                        'HostConfig' => [
                            'PortBindings' => [
                                '80/tcp' => [
                                    [
                                        'HostIp' => '0.0.0.0',
                                        'HostPort' => '8080',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            )
            ->willReturn($expectedResponse);

        $result = $this->container->create($config);

        $this->assertEquals($expectedResponse, $result);
    }

    /**
     * Checks the start method with valid ID
     */
    public function testStartWithValidId(): void
    {
        $containerId = 'abc123';

        $this->container->expects($this->once())
            ->method('post')
            ->with("/containers/{$containerId}/start")
            ->willReturn([]);

        $result = $this->container->start($containerId);

        $this->assertTrue($result);
    }

    /**
     * Checks the start method with empty ID
     */
    public function testStartWithEmptyId(): void
    {
        $this->expectException(MissingRequiredParameterException::class);

        $this->container->start('');
    }

    /**
     * Checks the stop method with valid ID
     */
    public function testStopWithValidId(): void
    {
        $containerId = 'abc123';
        $timeout = 20;

        $this->container->expects($this->once())
            ->method('post')
            ->with(
                "/containers/{$containerId}/stop",
                ['query' => ['t' => $timeout]]
            )
            ->willReturn([]);

        $result = $this->container->stop($containerId, $timeout);

        $this->assertTrue($result);
    }

    /**
     * Checks the restart method with valid ID
     */
    public function testRestartWithValidId(): void
    {
        $containerId = 'abc123';
        $timeout = 15;

        $this->container->expects($this->once())
            ->method('post')
            ->with(
                "/containers/{$containerId}/restart",
                ['query' => ['t' => $timeout]]
            )
            ->willReturn([]);

        $result = $this->container->restart($containerId, $timeout);

        $this->assertTrue($result);
    }

    /**
     * Checks the kill method with valid ID and signal
     */
    public function testKillWithValidIdAndSignal(): void
    {
        $containerId = 'abc123';
        $signal = Container::SIGNAL_TERM;

        $this->container->expects($this->once())
            ->method('post')
            ->with(
                "/containers/{$containerId}/kill",
                ['query' => ['signal' => $signal]]
            )
            ->willReturn([]);

        $result = $this->container->kill($containerId, $signal);

        $this->assertTrue($result);
    }

    /**
     * Checks the remove method with valid ID
     */
    public function testRemoveWithValidId(): void
    {
        $containerId = 'abc123';
        $force = true;
        $removeVolumes = true;

        $this->container->expects($this->once())
            ->method('delete')
            ->with(
                "/containers/{$containerId}",
                ['query' => ['force' => $force, 'v' => $removeVolumes]]
            )
            ->willReturn([]);

        $result = $this->container->remove($containerId, $force, $removeVolumes);

        $this->assertTrue($result);
    }

    /**
     * Checks the logs method with valid ID
     */
    public function testLogsWithValidId(): void
    {
        $containerId = 'abc123';
        $parameters = [
            'stdout' => true,
            'stderr' => true,
            'since' => 1627984123,
            'until' => 1627987723,
            'timestamps' => true,
            'tail' => 'all',
        ];

        $expectedResponse = [
            '2021-08-03T12:34:56.789Z stdout line 1',
            '2021-08-03T12:35:01.234Z stderr error message',
            '2021-08-03T12:35:10.456Z stdout line 2',
        ];

        $this->container->expects($this->once())
            ->method('get')
            ->with(
                "/containers/{$containerId}/logs",
                ['query' => $parameters]
            )
            ->willReturn($expectedResponse);

        $result = $this->container->logs($containerId, $parameters);

        $this->assertEquals($expectedResponse, $result);
    }

    /**
     * Checks the exists method with existing container
     */
    public function testExistsReturnsTrue(): void
    {
        $containerId = 'abc123';

        $this->container->expects($this->once())
            ->method('get')
            ->with("/containers/{$containerId}/json")
            ->willReturn(['Id' => 'abc123']);

        $result = $this->container->exists($containerId);

        $this->assertTrue($result);
    }

    /**
     * Checks the exists method with non-existent container
     */
    public function testExistsReturnsFalse(): void
    {
        $containerId = 'non-existent';

        $this->container->expects($this->once())
            ->method('get')
            ->with("/containers/{$containerId}/json")
            ->will($this->throwException(new \Exception('404 Container not found')));

        $result = $this->container->exists($containerId);

        $this->assertFalse($result);
    }

    /**
     * Checks the stats method with valid ID
     */
    public function testStatsWithValidId(): void
    {
        $containerId = 'abc123';

        $expectedResponse = [
            'id' => 'abc123',
            'name' => '/test-container',
            'cpu_stats' => [
                'cpu_usage' => [
                    'total_usage' => 123456789,
                    'usage_in_kernelmode' => 12345678,
                    'usage_in_usermode' => 123456789,
                    'percpu_usage' => [12345678, 23456789],
                ],
                'system_cpu_usage' => 9876543210,
                'online_cpus' => 8,
                'throttling_data' => [
                    'periods' => 0,
                    'throttled_periods' => 0,
                    'throttled_time' => 0,
                ],
            ],
            'memory_stats' => [
                'usage' => 12345678,
                'max_usage' => 23456789,
                'stats' => [],
                'limit' => 987654321,
            ],
            'blkio_stats' => [],
            'networks' => [
                'eth0' => [
                    'rx_bytes' => 1234,
                    'rx_packets' => 10,
                    'rx_errors' => 0,
                    'rx_dropped' => 0,
                    'tx_bytes' => 5678,
                    'tx_packets' => 5,
                    'tx_errors' => 0,
                    'tx_dropped' => 0,
                ],
            ],
        ];

        $this->container->expects($this->once())
            ->method('get')
            ->with(
                "/containers/{$containerId}/stats",
                ['query' => ['stream' => false]]
            )
            ->willReturn($expectedResponse);

        $result = $this->container->stats($containerId, false);

        $this->assertEquals($expectedResponse, $result);
    }
}
