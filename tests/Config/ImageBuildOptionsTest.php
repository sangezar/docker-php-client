<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Tests\Config;

use PHPUnit\Framework\TestCase;
use Sangezar\DockerClient\Config\ImageBuildOptions;
use Sangezar\DockerClient\Exception\Validation\InvalidConfigurationException;
use Sangezar\DockerClient\Exception\Validation\InvalidParameterValueException;

class ImageBuildOptionsTest extends TestCase
{
    private $options;
    private $tempDir;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a temporary directory for the context
        $this->tempDir = sys_get_temp_dir() . '/docker-build-' . uniqid();
        mkdir($this->tempDir, 0777, true);

        // Create a Dockerfile in the temporary directory
        file_put_contents($this->tempDir . '/Dockerfile', 'FROM php:8.1-alpine');

        // Base object for tests
        $this->options = ImageBuildOptions::create();
    }

    protected function tearDown(): void
    {
        // Delete the temporary directory
        if (is_dir($this->tempDir)) {
            // First delete the files
            if (file_exists($this->tempDir . '/Dockerfile')) {
                unlink($this->tempDir . '/Dockerfile');
            }
            rmdir($this->tempDir);
        }

        parent::tearDown();
    }

    /**
     * Checks the basic functionality of creating an options object
     */
    public function testCreate(): void
    {
        $options = ImageBuildOptions::create();
        $this->assertInstanceOf(ImageBuildOptions::class, $options);
    }

    /**
     * Checks setting a tag
     */
    public function testSetTag(): void
    {
        $options = ImageBuildOptions::create()
            ->setTag('test-image:latest')
            ->setContext($this->tempDir)
            ->setDockerfilePath('Dockerfile');

        $config = $options->toArrays();
        $this->assertArrayHasKey('parameters', $config);
        $this->assertArrayHasKey('t', $config['parameters']);
        $this->assertEquals('test-image:latest', $config['parameters']['t']);
    }

    /**
     * Checks setting the build context
     */
    public function testSetContext(): void
    {
        $options = ImageBuildOptions::create()
            ->setTag('test:latest')
            ->setContext($this->tempDir)
            ->setDockerfilePath('Dockerfile');

        $config = $options->toArrays();
        $this->assertArrayHasKey('config', $config);
        $this->assertArrayHasKey('context', $config['config']);
        $this->assertEquals($this->tempDir, $config['config']['context']);
    }

    /**
     * Checks setting the Dockerfile path
     */
    public function testSetDockerfilePath(): void
    {
        $options = ImageBuildOptions::create()
            ->setTag('test:latest')
            ->setContext($this->tempDir)
            ->setDockerfilePath('Dockerfile');

        $config = $options->toArrays();
        $this->assertArrayHasKey('parameters', $config);
        $this->assertArrayHasKey('dockerfile', $config['parameters']);
        $this->assertEquals('Dockerfile', $config['parameters']['dockerfile']);
    }

    /**
     * Checks setting the Dockerfile content
     */
    public function testSetDockerfileContent(): void
    {
        $content = "FROM php:8.2-alpine\nRUN echo 'test'";

        $options = ImageBuildOptions::create()
            ->setTag('test:latest')
            ->setContext($this->tempDir)
            ->setDockerfileContent($content);

        $config = $options->toArrays();
        $this->assertArrayHasKey('config', $config);
        $this->assertArrayHasKey('dockerfileContent', $config['config']);
        $this->assertEquals($content, $config['config']['dockerfileContent']);
    }

    /**
     * Checks that both Dockerfile path and content cannot be set simultaneously
     */
    public function testCannotSetBothDockerfilePathAndContent(): void
    {
        $this->expectException(InvalidParameterValueException::class);

        $options = ImageBuildOptions::create()
            ->setTag('test:latest')
            ->setContext($this->tempDir)
            ->setDockerfilePath('Dockerfile')
            ->setDockerfileContent("FROM php:8.2-alpine");
    }

    /**
     * Checks setting the noCache option
     */
    public function testSetNoCache(): void
    {
        $options = ImageBuildOptions::create()
            ->setTag('test:latest')
            ->setContext($this->tempDir)
            ->setDockerfilePath('Dockerfile')
            ->setNoCache(true);

        $config = $options->toArrays();
        $this->assertArrayHasKey('parameters', $config);
        $this->assertArrayHasKey('nocache', $config['parameters']);
        $this->assertTrue($config['parameters']['nocache']);
    }

    /**
     * Checks setting the quiet option
     */
    public function testSetQuiet(): void
    {
        // Test for quiet=true
        $options = ImageBuildOptions::create()
            ->setTag('test:latest')
            ->setContext($this->tempDir)
            ->setDockerfilePath('Dockerfile')
            ->setQuiet(true);

        $config = $options->toArrays();
        $this->assertArrayHasKey('parameters', $config);
        $this->assertArrayHasKey('q', $config['parameters']);
        $this->assertTrue($config['parameters']['q']);

        // Check that by default the option is not added to parameters
        $defaultOptions = ImageBuildOptions::create()
            ->setTag('test:latest')
            ->setContext($this->tempDir)
            ->setDockerfilePath('Dockerfile');

        $defaultConfig = $defaultOptions->toArrays();
        $this->assertArrayHasKey('parameters', $defaultConfig);

        // By default the q option should not be set
        $this->assertArrayNotHasKey('q', $defaultConfig['parameters']);
    }

    /**
     * Checks setting the removeIntermediateContainers option
     */
    public function testSetRemoveIntermediateContainers(): void
    {
        $options = ImageBuildOptions::create()
            ->setTag('test:latest')
            ->setContext($this->tempDir)
            ->setDockerfilePath('Dockerfile')
            ->setRemoveIntermediateContainers(true);

        $config = $options->toArrays();
        $this->assertArrayHasKey('parameters', $config);
        $this->assertArrayHasKey('rm', $config['parameters']);
        $this->assertTrue($config['parameters']['rm']);

        // Check changing the value
        $options->setRemoveIntermediateContainers(false);
        $config = $options->toArrays();
        $this->assertFalse($config['parameters']['rm']);
    }

    /**
     * Checks setting the forceRemoveIntermediateContainers option
     */
    public function testSetForceRemoveIntermediateContainers(): void
    {
        $options = ImageBuildOptions::create()
            ->setTag('test:latest')
            ->setContext($this->tempDir)
            ->setDockerfilePath('Dockerfile')
            ->setForceRemoveIntermediateContainers(true);

        $config = $options->toArrays();
        $this->assertArrayHasKey('parameters', $config);
        $this->assertArrayHasKey('forcerm', $config['parameters']);
        $this->assertTrue($config['parameters']['forcerm']);

        // By default it should be false
        $options = ImageBuildOptions::create()
            ->setTag('test:latest')
            ->setContext($this->tempDir)
            ->setDockerfilePath('Dockerfile');

        $config = $options->toArrays();
        $this->assertArrayNotHasKey('forcerm', $config['parameters']);
    }

    /**
     * Checks adding build arguments
     */
    public function testAddBuildArg(): void
    {
        $options = ImageBuildOptions::create()
            ->setTag('test:latest')
            ->setContext($this->tempDir)
            ->setDockerfilePath('Dockerfile')
            ->addBuildArg('VERSION', '1.0.0')
            ->addBuildArg('ENV', 'production');

        $config = $options->toArrays();
        $this->assertArrayHasKey('parameters', $config);
        $this->assertArrayHasKey('buildargs', $config['parameters']);

        $expectedArgs = [
            'VERSION' => '1.0.0',
            'ENV' => 'production',
        ];

        $this->assertEquals(json_encode($expectedArgs), $config['parameters']['buildargs']);
    }

    /**
     * Checks adding labels
     */
    public function testAddLabel(): void
    {
        $options = ImageBuildOptions::create()
            ->setTag('test:latest')
            ->setContext($this->tempDir)
            ->setDockerfilePath('Dockerfile')
            ->addLabel('maintainer', 'test@example.com')
            ->addLabel('version', '1.0.0');

        $config = $options->toArrays();
        $this->assertArrayHasKey('parameters', $config);
        $this->assertArrayHasKey('labels', $config['parameters']);

        $expectedLabels = [
            'maintainer' => 'test@example.com',
            'version' => '1.0.0',
        ];

        $this->assertEquals(json_encode($expectedLabels), $config['parameters']['labels']);
    }

    /**
     * Checks setting a target stage
     */
    public function testSetTarget(): void
    {
        $options = ImageBuildOptions::create()
            ->setTag('test:latest')
            ->setContext($this->tempDir)
            ->setDockerfilePath('Dockerfile')
            ->setTarget('production');

        $config = $options->toArrays();
        $this->assertArrayHasKey('parameters', $config);
        $this->assertArrayHasKey('target', $config['parameters']);
        $this->assertEquals('production', $config['parameters']['target']);
    }

    /**
     * Checks error with empty target stage name
     */
    public function testInvalidTarget(): void
    {
        $this->expectException(InvalidParameterValueException::class);

        ImageBuildOptions::create()->setTarget('');
    }

    /**
     * Checks setting the platform
     */
    public function testSetPlatform(): void
    {
        $options = ImageBuildOptions::create()
            ->setTag('test:latest')
            ->setContext($this->tempDir)
            ->setDockerfilePath('Dockerfile')
            ->setPlatform('linux/amd64');

        $config = $options->toArrays();
        $this->assertArrayHasKey('parameters', $config);
        $this->assertArrayHasKey('platform', $config['parameters']);
        $this->assertEquals('linux/amd64', $config['parameters']['platform']);
    }

    /**
     * Checks error with invalid platform
     */
    public function testInvalidPlatform(): void
    {
        $this->expectException(InvalidParameterValueException::class);

        ImageBuildOptions::create()->setPlatform('invalid-platform');
    }

    /**
     * Checks adding extra hosts
     */
    public function testAddExtraHost(): void
    {
        $options = ImageBuildOptions::create()
            ->setTag('test:latest')
            ->setContext($this->tempDir)
            ->setDockerfilePath('Dockerfile')
            ->addExtraHost('db.local', '192.168.1.10')
            ->addExtraHost('redis.local', '192.168.1.11');

        $config = $options->toArrays();
        $this->assertArrayHasKey('parameters', $config);
        $this->assertArrayHasKey('extrahosts', $config['parameters']);
        $this->assertEquals('db.local:192.168.1.10,redis.local:192.168.1.11', $config['parameters']['extrahosts']);
    }

    /**
     * Checks error when adding extra host with invalid IP address
     */
    public function testInvalidExtraHostIp(): void
    {
        $this->expectException(InvalidParameterValueException::class);

        ImageBuildOptions::create()->addExtraHost('db.local', 'invalid-ip');
    }

    /**
     * Checks error with empty hostname for extra host
     */
    public function testInvalidExtraHostHostname(): void
    {
        $this->expectException(InvalidParameterValueException::class);

        // Create an object and add an empty hostname
        ImageBuildOptions::create()->addExtraHost('', '192.168.1.10');
    }

    /**
     * Checks validation of required parameters
     */
    public function testRequiredParametersValidation(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        // Create an object without required parameters and try to get parameters
        ImageBuildOptions::create()->toArrays();
    }
}
