<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Config;

use Sangezar\DockerClient\Exception\Validation\InvalidConfigurationException;
use Sangezar\DockerClient\Exception\Validation\InvalidParameterValueException;

/**
 * Docker container configuration class
 */
class ContainerConfig
{
    private ?string $image = null;
    private ?string $name = null;
    /** @var array<int, string> */
    private array $env = [];
    /** @var array<int, string> */
    private array $cmd = [];
    /** @var array<int, array{host: string, container: string, mode: string}> */
    private array $volumes = [];
    /** @var array<int, array{host: int, container: int, protocol: string}> */
    private array $ports = [];
    /** @var array<string, string> */
    private array $labels = [];
    private ?string $workingDir = null;
    private ?string $user = null;
    private bool $tty = false;
    private bool $openStdin = false;
    /** @var array<string, mixed> */
    private array $hostConfig = [];
    private bool $attachStdin = false;
    private bool $attachStdout = true;
    private bool $attachStderr = true;
    private ?string $networkMode = null;
    /** @var array<string, array{Config: array<string, mixed>}> */
    private array $networks = [];

    /**
     * Creates a new container configuration instance
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Sets the image for the container
     *
     * @param string $image Image name (with tag)
     * @return $this
     * @throws InvalidParameterValueException if image name is empty
     */
    public function setImage(string $image): self
    {
        if (empty($image)) {
            throw new InvalidParameterValueException(
                'image',
                $image,
                'non-empty string',
                'Image name cannot be empty'
            );
        }

        $this->image = $image;

        return $this;
    }

    /**
     * Sets the name for the container
     *
     * @param string $name Container name
     * @return $this
     * @throws InvalidParameterValueException if name is invalid
     */
    public function setName(string $name): self
    {
        if (! preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_.-]+$/', $name)) {
            throw new InvalidParameterValueException(
                'name',
                $name,
                'letters, numbers, ._- (first character must be a letter or number)',
                'Container name contains invalid characters or has incorrect format'
            );
        }

        $this->name = $name;

        return $this;
    }

    /**
     * Adds an environment variable
     *
     * @param string $name Variable name
     * @param string $value Variable value
     * @return $this
     */
    public function addEnv(string $name, string $value): self
    {
        $this->env[] = "{$name}={$value}";

        return $this;
    }

    /**
     * Sets the command to run
     *
     * @param array<int, string> $cmd Command as array of arguments
     * @return $this
     */
    public function setCmd(array $cmd): self
    {
        $this->cmd = $cmd;

        return $this;
    }

    /**
     * Adds a volume
     *
     * @param string $hostPath Path on the host
     * @param string $containerPath Path in the container
     * @param string $mode Access mode (ro, rw)
     * @return $this
     */
    public function addVolume(string $hostPath, string $containerPath, string $mode = 'rw'): self
    {
        $this->volumes[] = [
            'host' => $hostPath,
            'container' => $containerPath,
            'mode' => $mode,
        ];

        return $this;
    }

    /**
     * Adds a port mapping
     *
     * @param int $hostPort Port on the host
     * @param int $containerPort Port in the container
     * @param string $protocol Protocol (tcp, udp)
     * @return $this
     */
    public function addPort(int $hostPort, int $containerPort, string $protocol = 'tcp'): self
    {
        $this->ports[] = [
            'host' => $hostPort,
            'container' => $containerPort,
            'protocol' => $protocol,
        ];

        return $this;
    }

    /**
     * Adds a label
     *
     * @param string $name Label name
     * @param string $value Label value
     * @return $this
     */
    public function addLabel(string $name, string $value): self
    {
        $this->labels[$name] = $value;

        return $this;
    }

    /**
     * Converts configuration to array for Docker API
     *
     * @return array<string, mixed>
     * @throws InvalidConfigurationException if configuration is invalid
     */
    public function toArray(): array
    {
        if (empty($this->image)) {
            throw new InvalidConfigurationException(
                'ContainerConfig',
                ['Image is required for container creation']
            );
        }

        $config = [
            'Image' => $this->image,
        ];

        if ($this->name !== null) {
            $config['name'] = $this->name;
        }

        if (! empty($this->cmd)) {
            $config['Cmd'] = $this->cmd;
        }

        if (! empty($this->env)) {
            $config['Env'] = $this->env;
        }

        if (! empty($this->labels)) {
            $config['Labels'] = $this->labels;
        }

        if ($this->workingDir !== null) {
            $config['WorkingDir'] = $this->workingDir;
        }

        if ($this->user !== null) {
            $config['User'] = $this->user;
        }

        $config['Tty'] = $this->tty;
        $config['OpenStdin'] = $this->openStdin;
        $config['AttachStdin'] = $this->attachStdin;
        $config['AttachStdout'] = $this->attachStdout;
        $config['AttachStderr'] = $this->attachStderr;

        // Prepare host settings
        $hostConfig = [];

        // Port settings
        if (! empty($this->ports)) {
            $portBindings = [];
            $exposedPorts = [];

            foreach ($this->ports as $port) {
                $containerPort = $port['container'] . '/' . $port['protocol'];
                $exposedPorts[$containerPort] = new \stdClass();

                $portBindings[$containerPort][] = [
                    'HostPort' => (string) $port['host'],
                ];
            }

            $config['ExposedPorts'] = $exposedPorts;
            $hostConfig['PortBindings'] = $portBindings;
        }

        // Volume settings
        if (! empty($this->volumes)) {
            $binds = [];
            $volumes = [];

            foreach ($this->volumes as $volume) {
                $containerPath = $volume['container'];
                $binds[] = $volume['host'] . ':' . $containerPath . ':' . $volume['mode'];
                $volumes[$containerPath] = new \stdClass();
            }

            $config['Volumes'] = $volumes;
            $hostConfig['Binds'] = $binds;
        }

        // Add other host settings
        $hostConfig = array_merge($hostConfig, $this->hostConfig);

        if ($this->networkMode !== null) {
            $hostConfig['NetworkMode'] = $this->networkMode;
        }

        $config['HostConfig'] = $hostConfig;

        // Networks
        if (! empty($this->networks)) {
            $config['NetworkingConfig'] = [
                'EndpointsConfig' => $this->networks,
            ];
        }

        return $config;
    }

    /**
     * Sets the working directory for the container
     *
     * @param string $dir Path to the working directory
     * @return $this
     */
    public function setWorkingDir(string $dir): self
    {
        $this->workingDir = $dir;

        return $this;
    }

    /**
     * Sets the user for running processes in the container
     *
     * @param string $user Username or UID
     * @return $this
     */
    public function setUser(string $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Sets the network mode
     *
     * @param string $mode Network mode (bridge, host, none, container:name|id)
     * @return $this
     */
    public function setNetworkMode(string $mode): self
    {
        $this->networkMode = $mode;

        return $this;
    }

    /**
     * Adds a network connection
     *
     * @param string $networkName Network name
     * @param array<string, mixed> $config Network configuration
     * @return $this
     */
    public function addNetworkConnection(string $networkName, array $config = []): self
    {
        $this->networks[$networkName] = [
            'Config' => $config,
        ];

        return $this;
    }

    /**
     * Enables/disables TTY
     *
     * @param bool $enable
     * @return $this
     */
    public function setTty(bool $enable = true): self
    {
        $this->tty = $enable;

        return $this;
    }

    /**
     * Enables/disables open STDIN
     *
     * @param bool $enable
     * @return $this
     */
    public function setOpenStdin(bool $enable = true): self
    {
        $this->openStdin = $enable;

        return $this;
    }

    /**
     * Sets the memory limit for the container
     *
     * @param int $memoryBytes Memory limit in bytes
     * @return $this
     */
    public function setMemoryLimit(int $memoryBytes): self
    {
        $this->hostConfig['Memory'] = $memoryBytes;

        return $this;
    }

    /**
     * Sets the CPU shares for the container
     *
     * @param int $cpuShares Relative CPU priority (default 1024)
     * @return $this
     */
    public function setCpuShares(int $cpuShares): self
    {
        $this->hostConfig['CpuShares'] = $cpuShares;

        return $this;
    }

    /**
     * Sets the restart policy for the container
     *
     * @param string $policy Restart policy (no, always, on-failure, unless-stopped)
     * @param int $maxRetryCount Maximum number of restart attempts (for on-failure)
     * @return $this
     */
    public function setRestartPolicy(string $policy, int $maxRetryCount = 0): self
    {
        $this->hostConfig['RestartPolicy'] = [
            'Name' => $policy,
        ];

        if ($policy === 'on-failure') {
            $this->hostConfig['RestartPolicy']['MaximumRetryCount'] = $maxRetryCount;
        }

        return $this;
    }
}
