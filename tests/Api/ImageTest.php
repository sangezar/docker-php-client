<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Tests\Api;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sangezar\DockerClient\Api\Image;
use Sangezar\DockerClient\Api\Interface\ImageInterface;
use Sangezar\DockerClient\Config\ImageBuildOptions;

class ImageTest extends TestCase
{
    /**
     * @var Image|MockObject
     */
    private $image;

    /**
     * Setup before tests
     */
    protected function setUp(): void
    {
        // Create a partial mock for the Image class
        $this->image = $this->getMockBuilder(Image::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get', 'post', 'delete', 'build'])
            ->getMock();
    }

    /**
     * Checks that the Image class implements ImageInterface
     */
    public function testImplementsImageInterface(): void
    {
        $this->assertInstanceOf(ImageInterface::class, $this->image);
    }

    /**
     * Checks that the buildWithOptions method transforms the ImageBuildOptions object correctly
     */
    public function testBuildWithOptionsTransformsCorrectly(): void
    {
        // Create an Image class with a mocked build method
        $imageMock = $this->getMockBuilder(Image::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['build'])
            ->getMock();

        // Create a mock for ImageBuildOptions
        $options = $this->getMockBuilder(ImageBuildOptions::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['toArrays'])
            ->getMock();

        // Configure the expected result from toArrays
        $expectedConfig = [
            'parameters' => [
                't' => 'test-image:latest',
                'dockerfile' => 'Dockerfile.test',
                'nocache' => true,
            ],
            'config' => [
                'context' => './test',
            ],
        ];

        $options->expects($this->once())
            ->method('toArrays')
            ->willReturn($expectedConfig);

        // Configure expectations for the build method
        $imageMock->expects($this->once())
            ->method('build')
            ->with(
                $this->equalTo($expectedConfig['parameters']),
                $this->equalTo($expectedConfig['config'])
            )
            ->willReturn(['Id' => 'sha256:test123']);

        // Now use ReflectionMethod to call the private buildWithOptions method
        $buildWithOptionsMethod = new \ReflectionMethod(Image::class, 'buildWithOptions');
        $buildWithOptionsMethod->setAccessible(true);

        $result = $buildWithOptionsMethod->invokeArgs($imageMock, [$options]);

        // Check the result
        $this->assertArrayHasKey('Id', $result);
        $this->assertEquals('sha256:test123', $result['Id']);
    }

    /**
     * Checks the handling of valid parameters for the list method
     */
    public function testListWithValidParameters(): void
    {
        // Configure expectations for the get method
        $this->image->expects($this->once())
            ->method('get')
            ->with('/images/json', ['query' => ['all' => true]])
            ->willReturn([['Id' => 'sha256:test123']]);

        // Call the method with valid parameters
        $result = $this->image->list(['all' => true]);

        // Check the result
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('sha256:test123', $result[0]['Id']);
    }

    /**
     * Checks error handling with invalid parameters for the list method
     */
    public function testListWithInvalidParameters(): void
    {
        $this->expectException(\Sangezar\DockerClient\Exception\Validation\InvalidParameterValueException::class);

        // Call the method with invalid parameters
        $this->image->list(['invalid_param' => true]);
    }
}
