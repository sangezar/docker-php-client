<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Tests\Config;

use PHPUnit\Framework\TestCase;
use Sangezar\DockerClient\Config\ClientConfig;

class ClientConfigTest extends TestCase
{
    private array $tempFiles = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Create temporary files for testing
        $this->tempFiles['cert'] = tempnam(sys_get_temp_dir(), 'docker_cert_');
        $this->tempFiles['key'] = tempnam(sys_get_temp_dir(), 'docker_key_');
        $this->tempFiles['ca'] = tempnam(sys_get_temp_dir(), 'docker_ca_');
    }

    protected function tearDown(): void
    {
        // Delete temporary files
        foreach ($this->tempFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }

        parent::tearDown();
    }

    public function testDefaultConfig(): void
    {
        $config = new ClientConfig();
        $array = $config->toArray();

        $this->assertArrayHasKey('timeout', $array);
        $this->assertArrayHasKey('headers', $array);
        $this->assertArrayHasKey('curl', $array);
        $this->assertArrayHasKey('base_uri', $array);
        $this->assertEquals('http://localhost', $array['base_uri']);
        $this->assertEquals('/var/run/docker.sock', $array['curl'][CURLOPT_UNIX_SOCKET_PATH]);
    }

    public function testHttpConfig(): void
    {
        $config = ClientConfig::create()
            ->setHost('http://localhost:2375');
        $array = $config->toArray();

        $this->assertArrayHasKey('base_uri', $array);
        $this->assertEquals('http://localhost:2375', $array['base_uri']);
        $this->assertArrayNotHasKey('curl', $array);
    }

    public function testHttpsConfig(): void
    {
        $config = ClientConfig::create()
            ->setHost('https://docker.example.com:2376')
            ->setCertPath($this->tempFiles['cert'])
            ->setKeyPath($this->tempFiles['key'])
            ->setCaPath($this->tempFiles['ca']);
        $array = $config->toArray();

        $this->assertArrayHasKey('base_uri', $array);
        $this->assertEquals('https://docker.example.com:2376', $array['base_uri']);
        $this->assertEquals($this->tempFiles['cert'], $array['cert']);
        $this->assertEquals($this->tempFiles['key'], $array['ssl_key']);
        $this->assertEquals($this->tempFiles['ca'], $array['verify']);
    }

    public function testCustomHeaders(): void
    {
        $config = ClientConfig::create()
            ->addHeader('X-Registry-Auth', 'test-auth');
        $array = $config->toArray();

        $this->assertArrayHasKey('headers', $array);
        $this->assertEquals('test-auth', $array['headers']['X-Registry-Auth']);
    }

    public function testTimeout(): void
    {
        $config = ClientConfig::create()
            ->setTimeout(60);
        $array = $config->toArray();

        $this->assertArrayHasKey('timeout', $array);
        $this->assertEquals(60, $array['timeout']);
    }
}
