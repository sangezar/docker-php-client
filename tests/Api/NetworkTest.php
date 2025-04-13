<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Tests\Api;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Sangezar\DockerClient\Api\Interface\NetworkInterface;
use Sangezar\DockerClient\Api\Network;
use Sangezar\DockerClient\Config\NetworkConfig;
use Sangezar\DockerClient\Exception\Validation\InvalidParameterValueException;
use Sangezar\DockerClient\Exception\Validation\MissingRequiredParameterException;

class NetworkTest extends TestCase
{
    /**
     * @var Network|MockObject
     */
    private $network;

    /**
     * Setup before tests
     */
    protected function setUp(): void
    {
        // Create a partial mock for the Network class
        $this->network = $this->getMockBuilder(Network::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get', 'post', 'delete'])
            ->getMock();
    }

    /**
     * Checks the correspondence of constants between interface and implementation
     */
    public function testConstantsMatchBetweenInterfaceAndImplementation(): void
    {
        $interfaceReflection = new ReflectionClass(NetworkInterface::class);
        $implementationReflection = new ReflectionClass(Network::class);

        $interfaceConstants = $interfaceReflection->getConstants();
        $implementationConstants = $implementationReflection->getConstants();

        // Check that all interface constants are present in the implementation
        foreach ($interfaceConstants as $name => $value) {
            $this->assertArrayHasKey(
                $name,
                $implementationConstants,
                "Constant '{$name}' is missing in the Network class"
            );

            $this->assertSame(
                $value,
                $implementationConstants[$name],
                "Value of constant '{$name}' differs between interface and implementation"
            );
        }
    }

    /**
     * Checks the presence of all constants for network driver types
     */
    public function testDriverConstants(): void
    {
        $this->assertSame('bridge', Network::DRIVER_BRIDGE);
        $this->assertSame('host', Network::DRIVER_HOST);
        $this->assertSame('overlay', Network::DRIVER_OVERLAY);
        $this->assertSame('macvlan', Network::DRIVER_MACVLAN);
        $this->assertSame('ipvlan', Network::DRIVER_IPVLAN);
        $this->assertSame('none', Network::DRIVER_NONE);
    }

    /**
     * Checks the presence of all constants for IPAM drivers
     */
    public function testIpamDriverConstants(): void
    {
        $this->assertSame('default', Network::IPAM_DRIVER_DEFAULT);
        $this->assertSame('null', Network::IPAM_DRIVER_NULL);
    }

    /**
     * Checks the presence of all constants for network scope
     */
    public function testScopeConstants(): void
    {
        $this->assertSame('local', Network::SCOPE_LOCAL);
        $this->assertSame('swarm', Network::SCOPE_SWARM);
        $this->assertSame('global', Network::SCOPE_GLOBAL);
    }

    /**
     * Checks that the list method correctly handles valid filters
     */
    public function testListWithValidFilters(): void
    {
        $filters = ['driver' => 'bridge', 'scope' => Network::SCOPE_LOCAL];
        $encodedFilters = json_encode($filters);

        $expectedResponse = [
            ['Id' => '123abc', 'Name' => 'test-network', 'Driver' => 'bridge'],
        ];

        $this->network->expects($this->once())
            ->method('get')
            ->with('/networks', ['query' => ['filters' => $encodedFilters]])
            ->willReturn($expectedResponse);

        $result = $this->network->list($filters);

        $this->assertEquals($expectedResponse, $result);
    }

    /**
     * Checks that the list method throws an exception with invalid filters
     */
    public function testListWithInvalidFilters(): void
    {
        $this->expectException(InvalidParameterValueException::class);

        $this->network->list(['invalid_filter' => 'value']);
    }

    /**
     * Checks that the inspect method throws an exception with an empty ID
     */
    public function testInspectWithEmptyId(): void
    {
        $this->expectException(MissingRequiredParameterException::class);

        $this->network->inspect('');
    }

    /**
     * Checks that the inspect method works correctly with a valid ID
     */
    public function testInspectWithValidId(): void
    {
        $networkId = 'test-network';
        $expectedResponse = ['Id' => '123abc', 'Name' => 'test-network', 'Driver' => 'bridge'];

        $this->network->expects($this->once())
            ->method('get')
            ->with("/networks/{$networkId}")
            ->willReturn($expectedResponse);

        $result = $this->network->inspect($networkId);

        $this->assertEquals($expectedResponse, $result);
    }

    /**
     * Checks that the connect method throws an exception with an empty network ID
     */
    public function testConnectWithEmptyNetworkId(): void
    {
        $this->expectException(MissingRequiredParameterException::class);

        $this->network->connect('', 'container-id');
    }

    /**
     * Checks that the connect method throws an exception with an empty container ID
     */
    public function testConnectWithEmptyContainerId(): void
    {
        $this->expectException(MissingRequiredParameterException::class);

        $this->network->connect('network-id', '');
    }

    /**
     * Checks that the connect method works correctly with valid parameters
     */
    public function testConnectWithValidParameters(): void
    {
        $networkId = 'test-network';
        $containerId = 'test-container';
        $config = ['Aliases' => ['web']];

        $this->network->expects($this->once())
            ->method('post')
            ->with(
                "/networks/{$networkId}/connect",
                ['json' => array_merge(['Container' => $containerId], $config)]
            );

        $result = $this->network->connect($networkId, $containerId, $config);

        $this->assertTrue($result);
    }

    /**
     * Checks implementation of the create method with valid configuration
     */
    public function testCreateWithValidConfig(): void
    {
        // Create a mock for NetworkConfig
        /** @var NetworkConfig&MockObject $config */
        $config = $this->getMockBuilder(NetworkConfig::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['toArray'])
            ->getMock();

        $configArray = [
            'Name' => 'test-network',
            'Driver' => Network::DRIVER_BRIDGE,
            'Options' => ['com.docker.network.bridge.name' => 'test-br'],
        ];

        $config->expects($this->once())
            ->method('toArray')
            ->willReturn($configArray);

        $expectedResponse = ['Id' => '123abc', 'Name' => 'test-network', 'Driver' => 'bridge'];

        $this->network->expects($this->once())
            ->method('post')
            ->with('/networks/create', ['json' => $configArray])
            ->willReturn($expectedResponse);

        $result = $this->network->create($config);

        $this->assertEquals($expectedResponse, $result);
    }

    /**
     * Checks that the remove method works correctly with a valid ID
     */
    public function testRemoveWithValidId(): void
    {
        $networkId = 'test-network';

        $this->network->expects($this->once())
            ->method('delete')
            ->with("/networks/{$networkId}")
            ->willReturn([]);

        $result = $this->network->remove($networkId);

        $this->assertTrue($result);
    }

    /**
     * Checks that the prune method works correctly with valid filters
     */
    public function testPruneWithValidFilters(): void
    {
        $filters = ['label' => 'test-label'];
        $encodedFilters = json_encode($filters);

        $expectedResponse = ['NetworksDeleted' => ['test-network'], 'SpaceReclaimed' => 1024];

        $this->network->expects($this->once())
            ->method('post')
            ->with('/networks/prune', ['query' => ['filters' => $encodedFilters]])
            ->willReturn($expectedResponse);

        $result = $this->network->prune($filters);

        $this->assertEquals($expectedResponse, $result);
    }

    /**
     * Checks that the exists method correctly determines the existence of the network
     */
    public function testExists(): void
    {
        $networkId = 'test-network';
        $expectedResponse = ['Id' => '123abc', 'Name' => 'test-network', 'Driver' => 'bridge'];

        $this->network->expects($this->once())
            ->method('get')
            ->with("/networks/{$networkId}")
            ->willReturn($expectedResponse);

        $result = $this->network->exists($networkId);

        $this->assertTrue($result);
    }

    /**
     * Checks that the exists method returns false when the network does not exist
     */
    public function testExistsReturnsFalseWhenNetworkNotFound(): void
    {
        $networkId = 'non-existent-network';

        $this->network->expects($this->once())
            ->method('get')
            ->with("/networks/{$networkId}")
            ->will($this->throwException(new \Exception('404 Network not found')));

        $result = $this->network->exists($networkId);

        $this->assertFalse($result);
    }
}
