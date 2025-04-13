<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Tests\Api;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Sangezar\DockerClient\Api\Interface\VolumeInterface;
use Sangezar\DockerClient\Api\Volume;

class VolumeTest extends TestCase
{
    /**
     * Checks the correspondence of constants between interface and implementation
     */
    public function testConstantsMatchBetweenInterfaceAndImplementation(): void
    {
        $interfaceReflection = new ReflectionClass(VolumeInterface::class);
        $implementationReflection = new ReflectionClass(Volume::class);

        $interfaceConstants = $interfaceReflection->getConstants();
        $implementationConstants = $implementationReflection->getConstants();

        // Check that all interface constants are present in the implementation
        foreach ($interfaceConstants as $name => $value) {
            $this->assertArrayHasKey(
                $name,
                $implementationConstants,
                "Constant '{$name}' is missing in the Volume class"
            );

            $this->assertSame(
                $value,
                $implementationConstants[$name],
                "Value of constant '{$name}' differs between interface and implementation"
            );
        }
    }

    /**
     * Checks the presence of all driver constants
     */
    public function testDriverConstants(): void
    {
        $this->assertSame('local', Volume::DRIVER_LOCAL);
        $this->assertSame('nfs', Volume::DRIVER_NFS);
        $this->assertSame('tmpfs', Volume::DRIVER_TMPFS);
        $this->assertSame('cifs', Volume::DRIVER_CIFS);
        $this->assertSame('btrfs', Volume::DRIVER_BTRFS);
        $this->assertSame('vieux-bridge', Volume::DRIVER_VIEUX_BRIDGE);
        $this->assertSame('vfs', Volume::DRIVER_VFS);
    }

    /**
     * Checks the presence of all driver option constants
     */
    public function testDriverOptionConstants(): void
    {
        $this->assertSame('type', Volume::OPT_TYPE);
        $this->assertSame('device', Volume::OPT_DEVICE);
        $this->assertSame('o', Volume::OPT_O);
        $this->assertSame('size', Volume::OPT_SIZE);
    }
}
