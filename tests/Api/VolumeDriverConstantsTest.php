<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Tests\Api;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Sangezar\DockerClient\Api\Interface\VolumeInterface;
use Sangezar\DockerClient\Api\Volume;

/**
 * Detailed test for checking volume driver constants
 */
class VolumeDriverConstantsTest extends TestCase
{
    /**
     * Checks the presence of all constants with DRIVER_ prefix in VolumeInterface
     */
    public function testDriverConstantsInInterface(): void
    {
        $reflection = new ReflectionClass(VolumeInterface::class);
        $constants = $reflection->getConstants();

        $driverConstants = array_filter(
            $constants,
            fn ($key) => str_starts_with($key, 'DRIVER_'),
            ARRAY_FILTER_USE_KEY
        );

        $this->assertNotEmpty($driverConstants, 'VolumeInterface must have driver constants');

        // Check the presence of each expected constant individually
        $this->assertArrayHasKey('DRIVER_LOCAL', $driverConstants);
        $this->assertArrayHasKey('DRIVER_NFS', $driverConstants);
        $this->assertArrayHasKey('DRIVER_TMPFS', $driverConstants);
        $this->assertArrayHasKey('DRIVER_CIFS', $driverConstants);
        $this->assertArrayHasKey('DRIVER_BTRFS', $driverConstants);
        $this->assertArrayHasKey('DRIVER_VIEUX_BRIDGE', $driverConstants);
        $this->assertArrayHasKey('DRIVER_VFS', $driverConstants);

        // Check the value of each constant
        $this->assertEquals('local', $driverConstants['DRIVER_LOCAL']);
        $this->assertEquals('nfs', $driverConstants['DRIVER_NFS']);
        $this->assertEquals('tmpfs', $driverConstants['DRIVER_TMPFS']);
        $this->assertEquals('cifs', $driverConstants['DRIVER_CIFS']);
        $this->assertEquals('btrfs', $driverConstants['DRIVER_BTRFS']);
        $this->assertEquals('vieux-bridge', $driverConstants['DRIVER_VIEUX_BRIDGE']);
        $this->assertEquals('vfs', $driverConstants['DRIVER_VFS']);
    }

    /**
     * Checks the presence of all constants with DRIVER_ prefix in Volume class
     */
    public function testDriverConstantsInImplementation(): void
    {
        $reflection = new ReflectionClass(Volume::class);
        $constants = $reflection->getConstants();

        $driverConstants = array_filter(
            $constants,
            fn ($key) => str_starts_with($key, 'DRIVER_'),
            ARRAY_FILTER_USE_KEY
        );

        $this->assertNotEmpty($driverConstants, 'Volume class must have driver constants');

        // Check the presence of each expected constant individually
        $this->assertArrayHasKey('DRIVER_LOCAL', $driverConstants);
        $this->assertArrayHasKey('DRIVER_NFS', $driverConstants);
        $this->assertArrayHasKey('DRIVER_TMPFS', $driverConstants);
        $this->assertArrayHasKey('DRIVER_CIFS', $driverConstants);
        $this->assertArrayHasKey('DRIVER_BTRFS', $driverConstants);
        $this->assertArrayHasKey('DRIVER_VIEUX_BRIDGE', $driverConstants);
        $this->assertArrayHasKey('DRIVER_VFS', $driverConstants);

        // Check the value of each constant
        $this->assertEquals('local', $driverConstants['DRIVER_LOCAL']);
        $this->assertEquals('nfs', $driverConstants['DRIVER_NFS']);
        $this->assertEquals('tmpfs', $driverConstants['DRIVER_TMPFS']);
        $this->assertEquals('cifs', $driverConstants['DRIVER_CIFS']);
        $this->assertEquals('btrfs', $driverConstants['DRIVER_BTRFS']);
        $this->assertEquals('vieux-bridge', $driverConstants['DRIVER_VIEUX_BRIDGE']);
        $this->assertEquals('vfs', $driverConstants['DRIVER_VFS']);
    }

    /**
     * Checks the order of driver constants in interface and implementation
     */
    public function testDriverConstantsOrderBetweenInterfaceAndImplementation(): void
    {
        $interfaceReflection = new ReflectionClass(VolumeInterface::class);
        $implementationReflection = new ReflectionClass(Volume::class);

        $interfaceConstants = array_filter(
            $interfaceReflection->getConstants(),
            fn ($key) => str_starts_with($key, 'DRIVER_'),
            ARRAY_FILTER_USE_KEY
        );

        $implementationConstants = array_filter(
            $implementationReflection->getConstants(),
            fn ($key) => str_starts_with($key, 'DRIVER_'),
            ARRAY_FILTER_USE_KEY
        );

        // Compare the number of constants
        $this->assertEquals(
            count($interfaceConstants),
            count($implementationConstants),
            'The number of driver constants should be the same in interface and implementation'
        );

        // Check the presence of all interface constants in the implementation
        foreach ($interfaceConstants as $name => $value) {
            $this->assertArrayHasKey(
                $name,
                $implementationConstants,
                "Driver constant '{$name}' from interface is missing in implementation"
            );

            $this->assertEquals(
                $value,
                $implementationConstants[$name],
                "Value of driver constant '{$name}' differs in interface and implementation"
            );
        }
    }

    /**
     * Checks the presence of all driver option constants in interface and implementation
     */
    public function testDriverOptionConstants(): void
    {
        $interfaceReflection = new ReflectionClass(VolumeInterface::class);
        $implementationReflection = new ReflectionClass(Volume::class);

        $interfaceConstants = array_filter(
            $interfaceReflection->getConstants(),
            fn ($key) => str_starts_with($key, 'OPT_'),
            ARRAY_FILTER_USE_KEY
        );

        $implementationConstants = array_filter(
            $implementationReflection->getConstants(),
            fn ($key) => str_starts_with($key, 'OPT_'),
            ARRAY_FILTER_USE_KEY
        );

        // Check the number and presence of constants
        $this->assertEquals(
            count($interfaceConstants),
            count($implementationConstants),
            'The number of driver option constants should be the same in interface and implementation'
        );

        // Check the presence of each expected constant individually
        $this->assertArrayHasKey('OPT_TYPE', $interfaceConstants);
        $this->assertArrayHasKey('OPT_DEVICE', $interfaceConstants);
        $this->assertArrayHasKey('OPT_O', $interfaceConstants);
        $this->assertArrayHasKey('OPT_SIZE', $interfaceConstants);

        // Check the value of each constant
        $this->assertEquals('type', $interfaceConstants['OPT_TYPE']);
        $this->assertEquals('device', $interfaceConstants['OPT_DEVICE']);
        $this->assertEquals('o', $interfaceConstants['OPT_O']);
        $this->assertEquals('size', $interfaceConstants['OPT_SIZE']);

        // Check that all interface constants are in implementation with the same values
        foreach ($interfaceConstants as $name => $value) {
            $this->assertArrayHasKey(
                $name,
                $implementationConstants,
                "Option constant '{$name}' from interface is missing in implementation"
            );

            $this->assertEquals(
                $value,
                $implementationConstants[$name],
                "Value of option constant '{$name}' differs in interface and implementation"
            );
        }
    }
}
