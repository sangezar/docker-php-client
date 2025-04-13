<?php

declare(strict_types=1);

namespace Sangezar\DockerClient;

use GuzzleHttp\Client as HttpClient;
use Psr\Http\Client\ClientInterface;
use Sangezar\DockerClient\Api\Container;
use Sangezar\DockerClient\Api\Image;
use Sangezar\DockerClient\Api\Interface\ContainerInterface;
use Sangezar\DockerClient\Api\Interface\ImageInterface;
use Sangezar\DockerClient\Api\Interface\NetworkInterface;
use Sangezar\DockerClient\Api\Interface\SystemInterface;
use Sangezar\DockerClient\Api\Interface\VolumeInterface;
use Sangezar\DockerClient\Api\Network;
use Sangezar\DockerClient\Api\System;
use Sangezar\DockerClient\Api\Volume;
use Sangezar\DockerClient\Config\ClientConfig;

/**
 * Main class for interaction with Docker API
 */
class DockerClient
{
    private ClientInterface $httpClient;
    private string $apiVersion;
    /** @var array<string, mixed> */
    private array $options;

    /**
     * Constructor
     *
     * @param ClientConfig|null $config Client configuration
     * @param ClientInterface|null $httpClient HTTP client
     * @param string $apiVersion Docker API version
     */
    public function __construct(
        ?ClientConfig $config = null,
        ?ClientInterface $httpClient = null,
        string $apiVersion = 'v1.47'
    ) {
        $this->apiVersion = $apiVersion;
        $config = $config ?? new ClientConfig();

        $options = $config->toArray();
        $baseUri = $options['base_uri'] ?? '';
        if (is_string($baseUri)) {
            $options['base_uri'] = rtrim($baseUri, '/') . '/' . $this->apiVersion;
        } else {
            $options['base_uri'] = $this->apiVersion;
        }

        $this->httpClient = $httpClient ?? new HttpClient($options);
        $this->options = $options;
    }

    /**
     * Creates a new client instance
     *
     * @param ClientConfig|null $config Client configuration
     * @return self
     */
    public static function create(?ClientConfig $config = null): self
    {
        return new self($config);
    }

    /**
     * Creates a client for TCP connection
     *
     * @param string $host Docker API host
     * @param string|null $certPath Path to certificate
     * @param string|null $keyPath Path to key
     * @param string|null $caPath Path to CA certificate
     * @return self
     */
    public static function createTcp(
        string $host,
        ?string $certPath = null,
        ?string $keyPath = null,
        ?string $caPath = null
    ): self {
        $config = ClientConfig::create()
            ->setHost($host);

        if ($certPath !== null && $keyPath !== null) {
            $config
                ->setCertPath($certPath)
                ->setKeyPath($keyPath);

            if ($caPath !== null) {
                $config->setCaPath($caPath);
            }
        }

        return new self($config);
    }

    /**
     * Creates a client for Unix socket connection
     *
     * @param string $socketPath Path to Unix socket
     * @return self
     */
    public static function createUnix(string $socketPath = '/var/run/docker.sock'): self
    {
        return new self(
            ClientConfig::create()->setHost('unix://' . $socketPath)
        );
    }

    /**
     * Returns API for working with containers
     *
     * @return ContainerInterface
     */
    public function container(): ContainerInterface
    {
        return new Container($this->httpClient, $this->options);
    }

    /**
     * Returns API for working with images
     *
     * @return ImageInterface
     */
    public function image(): ImageInterface
    {
        return new Image($this->httpClient, $this->options);
    }

    /**
     * Returns API for working with system functions
     *
     * @return SystemInterface
     */
    public function system(): SystemInterface
    {
        return new System($this->httpClient, $this->options);
    }

    /**
     * Returns API for working with networks
     *
     * @return NetworkInterface
     */
    public function network(): NetworkInterface
    {
        return new Network($this->httpClient, $this->options);
    }

    /**
     * Returns API for working with volumes
     *
     * @return VolumeInterface
     */
    public function volume(): VolumeInterface
    {
        return new Volume($this->httpClient, $this->options);
    }
}
