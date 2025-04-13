<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Config;

use Sangezar\DockerClient\Exception\Validation\InvalidParameterValueException;
use Sangezar\DockerClient\Exception\Validation\MissingRequiredParameterException;

/**
 * Docker API client configuration
 */
class ClientConfig
{
    /**
     * Regular expression for validating HTTP URL
     */
    private const HTTP_URL_PATTERN = '/^https?:\/\/[a-zA-Z0-9][-a-zA-Z0-9.]*(?::[0-9]+)?(?:\/[-a-zA-Z0-9%_.~#+]*)*$/';

    /**
     * Regular expression for validating Unix socket path
     */
    private const UNIX_SOCKET_PATTERN = '/^unix:\/\/\/.+$/';

    /**
     * Regular expression for validating TCP URL
     */
    private const TCP_URL_PATTERN = '/^tcp:\/\/[a-zA-Z0-9][-a-zA-Z0-9.]*(?::[0-9]+)?$/';

    /**
     * @var string Docker API host
     */
    private string $host;

    /**
     * @var string|null Path to client certificate
     */
    private ?string $certPath = null;

    /**
     * @var string|null Path to client key
     */
    private ?string $keyPath = null;

    /**
     * @var string|null Path to CA certificate
     */
    private ?string $caPath = null;

    /**
     * @var bool Whether to verify server certificate
     */
    private bool $verifyPeer = true;

    /**
     * @var int Request timeout (in seconds)
     */
    private int $timeout = 30;

    /**
     * @var array<string, string> Additional HTTP headers
     */
    private array $headers = [];

    /**
     * Constructor
     *
     * @param string $host Docker API host
     * @throws InvalidParameterValueException if host has invalid format
     */
    public function __construct(string $host = 'unix:///var/run/docker.sock')
    {
        $this->setHost($host);
    }

    /**
     * Factory method
     *
     * @return self
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Creates configuration for local Docker daemon via Unix socket
     *
     * @param string $socketPath Path to Unix socket
     * @return self
     */
    public static function forUnixSocket(string $socketPath = '/var/run/docker.sock'): self
    {
        if (! str_starts_with($socketPath, '/')) {
            $socketPath = '/' . $socketPath;
        }

        return new self('unix://' . $socketPath);
    }

    /**
     * Creates configuration for Docker API via HTTP
     *
     * @param string $host Host (e.g., 'http://localhost:2375')
     * @return self
     */
    public static function forHttp(string $host): self
    {
        if (! preg_match(self::HTTP_URL_PATTERN, $host)) {
            throw new InvalidParameterValueException(
                'host',
                $host,
                'valid HTTP URL (e.g., http://localhost:2375)',
                'Invalid HTTP URL format'
            );
        }

        return new self($host);
    }

    /**
     * Creates configuration for Docker API via HTTPS with TLS
     *
     * @param string $host Host (e.g., 'https://docker-host:2376')
     * @param string $certPath Path to client certificate
     * @param string $keyPath Path to client key
     * @param string|null $caPath Path to CA certificate (optional)
     * @return self
     */
    public static function forTls(
        string $host,
        string $certPath,
        string $keyPath,
        ?string $caPath = null
    ): self {
        if (! str_starts_with($host, 'https://')) {
            $host = 'https://' . $host;
        }

        $config = new self($host);
        $config->setCertPath($certPath)
               ->setKeyPath($keyPath);

        if ($caPath !== null) {
            $config->setCaPath($caPath);
        }

        return $config;
    }

    /**
     * Get host
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Set host
     *
     * @param string $host Docker API host
     * @return self
     * @throws InvalidParameterValueException if host has invalid format
     */
    public function setHost(string $host): self
    {
        if (empty($host)) {
            throw new MissingRequiredParameterException('host');
        }

        // Check host format
        if (! $this->isValidHost($host)) {
            throw new InvalidParameterValueException(
                'host',
                $host,
                'valid Docker host (unix://socket/path, http://host:port, https://host:port, tcp://host:port)',
                'Invalid host format'
            );
        }

        $this->host = $host;

        return $this;
    }

    /**
     * Checks if host has valid format
     *
     * @param string $host Host to validate
     * @return bool
     */
    private function isValidHost(string $host): bool
    {
        return preg_match(self::UNIX_SOCKET_PATTERN, $host) ||
               preg_match(self::HTTP_URL_PATTERN, $host) ||
               preg_match(self::TCP_URL_PATTERN, $host);
    }

    /**
     * Get path to client certificate
     *
     * @return string|null
     */
    public function getCertPath(): ?string
    {
        return $this->certPath;
    }

    /**
     * Set path to client certificate
     *
     * @param string|null $certPath Path to certificate
     * @return self
     * @throws InvalidParameterValueException if file doesn't exist
     */
    public function setCertPath(?string $certPath): self
    {
        if ($certPath !== null && ! file_exists($certPath)) {
            throw new InvalidParameterValueException(
                'certPath',
                $certPath,
                'existing file path',
                'Certificate file does not exist'
            );
        }

        $this->certPath = $certPath;

        return $this;
    }

    /**
     * Get path to client key
     *
     * @return string|null
     */
    public function getKeyPath(): ?string
    {
        return $this->keyPath;
    }

    /**
     * Set path to client key
     *
     * @param string|null $keyPath Path to key
     * @return self
     * @throws InvalidParameterValueException if file doesn't exist
     */
    public function setKeyPath(?string $keyPath): self
    {
        if ($keyPath !== null && ! file_exists($keyPath)) {
            throw new InvalidParameterValueException(
                'keyPath',
                $keyPath,
                'existing file path',
                'Key file does not exist'
            );
        }

        $this->keyPath = $keyPath;

        return $this;
    }

    /**
     * Get path to CA certificate
     *
     * @return string|null
     */
    public function getCaPath(): ?string
    {
        return $this->caPath;
    }

    /**
     * Set path to CA certificate
     *
     * @param string|null $caPath Path to CA certificate
     * @return self
     * @throws InvalidParameterValueException if file doesn't exist
     */
    public function setCaPath(?string $caPath): self
    {
        if ($caPath !== null && ! file_exists($caPath)) {
            throw new InvalidParameterValueException(
                'caPath',
                $caPath,
                'existing file path',
                'CA certificate file does not exist'
            );
        }

        $this->caPath = $caPath;

        return $this;
    }

    /**
     * Whether to verify server certificate
     *
     * @return bool
     */
    public function isVerifyPeer(): bool
    {
        return $this->verifyPeer;
    }

    /**
     * Set whether to verify server certificate
     *
     * @param bool $verifyPeer Value
     * @return self
     */
    public function setVerifyPeer(bool $verifyPeer): self
    {
        $this->verifyPeer = $verifyPeer;

        return $this;
    }

    /**
     * Get request timeout
     *
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * Set request timeout
     *
     * @param int $timeout Timeout in seconds
     * @return self
     * @throws InvalidParameterValueException if timeout is less than or equal to zero
     */
    public function setTimeout(int $timeout): self
    {
        if ($timeout <= 0) {
            throw new InvalidParameterValueException(
                'timeout',
                $timeout,
                'positive integer',
                'Timeout must be greater than zero'
            );
        }

        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Get HTTP headers
     *
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Set HTTP headers
     *
     * @param array<string, string> $headers Headers
     * @return self
     * @throws InvalidParameterValueException if headers have invalid format
     */
    public function setHeaders(array $headers): self
    {
        foreach ($headers as $name => $value) {
            if (! is_string($name) || ! is_string($value)) {
                throw new InvalidParameterValueException(
                    'headers',
                    $headers,
                    'associative array of string key-value pairs',
                    'Headers must be an associative array with string keys and values'
                );
            }

            // Check for invalid characters
            if (preg_match('/[\r\n]/', $name . $value)) {
                throw new InvalidParameterValueException(
                    'headers',
                    $headers,
                    'strings without CR or LF characters',
                    'Header name or value contains invalid characters (CR or LF)'
                );
            }
        }

        $this->headers = $headers;

        return $this;
    }

    /**
     * Add HTTP header
     *
     * @param string $name Header name
     * @param string $value Header value
     * @return self
     * @throws InvalidParameterValueException if header has invalid format
     */
    public function addHeader(string $name, string $value): self
    {
        if (empty($name)) {
            throw new MissingRequiredParameterException(
                'name',
                'Header name cannot be empty'
            );
        }

        // Check for invalid characters
        if (preg_match('/[\r\n]/', $name . $value)) {
            throw new InvalidParameterValueException(
                'header',
                $name . ': ' . $value,
                'string without CR or LF characters',
                'Header name or value contains invalid characters (CR or LF)'
            );
        }

        $this->headers[$name] = $value;

        return $this;
    }

    /**
     * Convert configuration to array for GuzzleHttp
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $config = [
            'timeout' => $this->timeout,
            'headers' => $this->headers,
        ];

        if (str_starts_with($this->host, 'unix://')) {
            $config['curl'] = [
                CURLOPT_UNIX_SOCKET_PATH => str_replace('unix://', '', $this->host),
            ];
            $config['base_uri'] = 'http://localhost';
        } elseif (str_starts_with($this->host, 'tcp://')) {
            // Convert tcp:// to http://
            $config['base_uri'] = str_replace('tcp://', 'http://', $this->host);
        } else {
            $config['base_uri'] = rtrim($this->host, '/');

            // Add TLS settings if certificate paths are specified
            if ($this->certPath !== null && $this->keyPath !== null) {
                $config['cert'] = $this->certPath;
                $config['ssl_key'] = $this->keyPath;

                if ($this->caPath !== null) {
                    $config['verify'] = $this->caPath;
                } else {
                    $config['verify'] = $this->verifyPeer;
                }
            }
        }

        return $config;
    }

    /**
     * Create configuration from array
     *
     * @param array<string, mixed> $config Configuration as array
     * @return self
     */
    public static function fromArray(array $config): self
    {
        $instance = new self();

        if (isset($config['host']) && is_string($config['host'])) {
            $instance->setHost($config['host']);
        }

        if (isset($config['cert_path'])) {
            if (is_string($config['cert_path'])) {
                $instance->setCertPath($config['cert_path']);
            } elseif (is_null($config['cert_path'])) {
                $instance->setCertPath(null);
            }
            // Ignore other types
        }

        if (isset($config['key_path'])) {
            if (is_string($config['key_path'])) {
                $instance->setKeyPath($config['key_path']);
            } elseif (is_null($config['key_path'])) {
                $instance->setKeyPath(null);
            }
            // Ignore other types
        }

        if (isset($config['ca_path'])) {
            if (is_string($config['ca_path'])) {
                $instance->setCaPath($config['ca_path']);
            } elseif (is_null($config['ca_path'])) {
                $instance->setCaPath(null);
            }
            // Ignore other types
        }

        if (isset($config['verify_peer'])) {
            $instance->setVerifyPeer((bool) $config['verify_peer']);
        }

        if (isset($config['timeout']) && (is_int($config['timeout']) || is_string($config['timeout']))) {
            $instance->setTimeout((int) $config['timeout']);
        }

        if (isset($config['headers']) && is_array($config['headers'])) {
            $instance->setHeaders($config['headers']);
        }

        return $instance;
    }
}
