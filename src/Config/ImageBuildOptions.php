<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Config;

use Sangezar\DockerClient\Exception\Validation\InvalidConfigurationException;
use Sangezar\DockerClient\Exception\Validation\InvalidParameterValueException;

/**
 * Docker image build options configuration class
 */
class ImageBuildOptions
{
    private ?string $tag = null;
    private ?string $context = null;
    private ?string $dockerfile = null;
    private ?string $dockerfileContent = null;
    private bool $noCache = false;
    private bool $quiet = false;
    private bool $rm = true;
    private bool $forceRm = false;
    /** @var array<string, string> */
    private array $buildArgs = [];
    /** @var array<string, string> */
    private array $labels = [];
    private ?string $target = null;
    private ?string $platform = null;
    /** @var array<string, string> */
    private array $extraHosts = [];
    private bool $squash = false;
    private ?string $network = null;
    /** @var array<string, string> */
    private array $secrets = [];
    /** @var array<string, string> */
    private array $sshSources = [];
    /** @var array<string, string> */
    private array $extraContexts = [];
    private ?string $pull = null;
    /** @var array<int, string> */
    private array $cacheFrom = [];
    private ?string $outputType = null;
    private ?string $securityOpt = null;

    /**
     * Constants for pull parameter
     */
    public const PULL_ALWAYS = 'always';
    public const PULL_MISSING = 'missing';
    public const PULL_NEVER = 'never';

    /**
     * Constants for output types
     */
    public const OUTPUT_TYPE_IMAGE = 'image';
    public const OUTPUT_TYPE_LOCAL = 'local';
    public const OUTPUT_TYPE_TAR = 'tar';

    /**
     * Creates a new image build options instance
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Sets the image name and tag
     *
     * @param string $tag Image name and tag (name:tag)
     * @return $this
     * @throws InvalidParameterValueException if tag has invalid format
     */
    public function setTag(string $tag): self
    {
        if (empty($tag)) {
            throw new InvalidParameterValueException(
                'tag',
                $tag,
                'non-empty string in name:tag format',
                'Tag cannot be empty'
            );
        }

        // Validate tag format (name:tag)
        if (! preg_match('/^[a-z0-9]+((\.|_|-)?[a-z0-9]+)*(\/[a-z0-9]+((\.|_|-)?[a-z0-9]+)*)*(\:[a-zA-Z0-9_.-]+)?$/', $tag)) {
            throw new InvalidParameterValueException(
                'tag',
                $tag,
                'valid Docker tag format: repository/name:tag',
                'Invalid image name/tag format'
            );
        }

        $this->tag = $tag;

        return $this;
    }

    /**
     * Sets the build context path
     *
     * @param string $contextPath Path to build context (directory or archive)
     * @return $this
     * @throws InvalidParameterValueException if path is invalid
     */
    public function setContext(string $contextPath): self
    {
        if (empty($contextPath)) {
            throw new InvalidParameterValueException(
                'contextPath',
                $contextPath,
                'non-empty path to directory or archive',
                'Build context path cannot be empty'
            );
        }

        // Check if context exists
        if (! file_exists($contextPath)) {
            throw new InvalidParameterValueException(
                'contextPath',
                $contextPath,
                'existing file or directory',
                'Build context does not exist'
            );
        }

        $this->context = $contextPath;

        return $this;
    }

    /**
     * Sets the path to Dockerfile in the build context
     *
     * @param string $dockerfilePath Path to Dockerfile relative to context
     * @return $this
     * @throws InvalidParameterValueException if path is empty
     */
    public function setDockerfilePath(string $dockerfilePath): self
    {
        if (empty($dockerfilePath)) {
            throw new InvalidParameterValueException(
                'dockerfilePath',
                $dockerfilePath,
                'non-empty path to Dockerfile',
                'Dockerfile path cannot be empty'
            );
        }

        // Cannot set both path and content simultaneously
        if ($this->dockerfileContent !== null) {
            throw new InvalidParameterValueException(
                'dockerfilePath',
                $dockerfilePath,
                'null if Dockerfile content is already set',
                'Cannot set both Dockerfile path and content simultaneously'
            );
        }

        $this->dockerfile = $dockerfilePath;

        return $this;
    }

    /**
     * Sets Dockerfile content directly
     *
     * @param string $content Dockerfile content
     * @return $this
     * @throws InvalidParameterValueException if content is empty or has errors
     */
    public function setDockerfileContent(string $content): self
    {
        if (empty($content)) {
            throw new InvalidParameterValueException(
                'content',
                $content,
                'non-empty Dockerfile content',
                'Dockerfile content cannot be empty'
            );
        }

        // Cannot set both path and content simultaneously
        if ($this->dockerfile !== null) {
            throw new InvalidParameterValueException(
                'content',
                $content,
                'null if Dockerfile path is already set',
                'Cannot set both Dockerfile path and content simultaneously'
            );
        }

        // Basic Dockerfile syntax validation
        if (! preg_match('/^(FROM|ARG\s+FROM)\s+.+/im', $content)) {
            throw new InvalidParameterValueException(
                'content',
                $content,
                'valid Dockerfile content with FROM instruction',
                'Dockerfile must start with FROM or ARG FROM instruction'
            );
        }

        $this->dockerfileContent = $content;

        return $this;
    }

    /**
     * Enables/disables no-cache mode
     *
     * @param bool $noCache Whether to disable layer caching during build
     * @return $this
     */
    public function setNoCache(bool $noCache = true): self
    {
        $this->noCache = $noCache;

        return $this;
    }

    /**
     * Enables/disables quiet build mode
     *
     * @param bool $quiet Whether to disable detailed output
     * @return $this
     */
    public function setQuiet(bool $quiet = true): self
    {
        $this->quiet = $quiet;

        return $this;
    }

    /**
     * Enables/disables intermediate container removal
     *
     * @param bool $remove Whether to remove intermediate containers
     * @return $this
     */
    public function setRemoveIntermediateContainers(bool $remove = true): self
    {
        $this->rm = $remove;

        return $this;
    }

    /**
     * Enables/disables forced removal of intermediate containers
     *
     * @param bool $forceRemove Whether to force remove intermediate containers
     * @return $this
     */
    public function setForceRemoveIntermediateContainers(bool $forceRemove = true): self
    {
        $this->forceRm = $forceRemove;

        return $this;
    }

    /**
     * Adds a build argument
     *
     * @param string $name Argument name
     * @param string $value Argument value
     * @return $this
     */
    public function addBuildArg(string $name, string $value): self
    {
        $this->buildArgs[$name] = $value;

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
     * Sets the target stage for a multi-stage Dockerfile
     *
     * @param string $target Target stage name
     * @return $this
     */
    public function setTarget(string $target): self
    {
        if (empty($target)) {
            throw new InvalidParameterValueException(
                'target',
                $target,
                'non-empty target stage name',
                'Target stage name cannot be empty'
            );
        }

        $this->target = $target;

        return $this;
    }

    /**
     * Sets the platform for build (e.g., linux/amd64)
     *
     * @param string $platform Platform in os/arch[/variant] format
     * @return $this
     * @throws InvalidParameterValueException if format is invalid
     */
    public function setPlatform(string $platform): self
    {
        if (empty($platform)) {
            throw new InvalidParameterValueException(
                'platform',
                $platform,
                'non-empty platform in os/arch format',
                'Platform cannot be empty'
            );
        }

        // Validate platform format
        if (! preg_match('/^[a-z0-9]+\/[a-z0-9]+(\/[a-z0-9]+)?$/', $platform)) {
            throw new InvalidParameterValueException(
                'platform',
                $platform,
                'platform in os/arch[/variant] format',
                'Invalid platform format'
            );
        }

        $this->platform = $platform;

        return $this;
    }

    /**
     * Adds an extra hosts record (like --add-host)
     *
     * @param string $hostname Hostname
     * @param string $ip IP address
     * @return $this
     * @throws InvalidParameterValueException if IP format is invalid
     */
    public function addExtraHost(string $hostname, string $ip): self
    {
        if (empty($hostname) || empty($ip)) {
            throw new InvalidParameterValueException(
                'hostname/ip',
                "$hostname:$ip",
                'non-empty values',
                'Hostname and IP address cannot be empty'
            );
        }

        // Validate IP address
        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new InvalidParameterValueException(
                'ip',
                $ip,
                'valid IP address',
                'Invalid IP format'
            );
        }

        $this->extraHosts[$hostname] = $ip;

        return $this;
    }

    /**
     * Enables/disables squash (layer merging)
     *
     * @param bool $squash Whether to squash layers
     * @return $this
     */
    public function setSquash(bool $squash = true): self
    {
        $this->squash = $squash;

        return $this;
    }

    /**
     * Sets the network for build
     *
     * @param string $network Network name or 'none', 'host'
     * @return $this
     */
    public function setNetwork(string $network): self
    {
        if (empty($network)) {
            throw new InvalidParameterValueException(
                'network',
                $network,
                'non-empty network name or "none", "host"',
                'Network name cannot be empty'
            );
        }

        $this->network = $network;

        return $this;
    }

    /**
     * Adds a secret for build
     *
     * @param string $id Secret ID
     * @param string $source Path to secret file
     * @return $this
     */
    public function addSecret(string $id, string $source): self
    {
        if (empty($id) || empty($source)) {
            throw new InvalidParameterValueException(
                'id/source',
                "$id:$source",
                'non-empty values',
                'Secret ID and source path cannot be empty'
            );
        }

        $this->secrets[$id] = $source;

        return $this;
    }

    /**
     * Adds an SSH source for build
     *
     * @param string $id SSH source ID
     * @param string $source Path to SSH socket or key
     * @return $this
     */
    public function addSshSource(string $id, string $source): self
    {
        if (empty($id) || empty($source)) {
            throw new InvalidParameterValueException(
                'id/source',
                "$id:$source",
                'non-empty values',
                'SSH source ID and path cannot be empty'
            );
        }

        $this->sshSources[$id] = $source;

        return $this;
    }

    /**
     * Adds an extra context
     *
     * @param string $name Context name
     * @param string $source Path to context source
     * @return $this
     */
    public function addExtraContext(string $name, string $source): self
    {
        if (empty($name) || empty($source)) {
            throw new InvalidParameterValueException(
                'name/source',
                "$name:$source",
                'non-empty values',
                'Context name and source path cannot be empty'
            );
        }

        $this->extraContexts[$name] = $source;

        return $this;
    }

    /**
     * Sets the pull policy for base images
     *
     * @param string $pull Pull policy (always, missing, never)
     * @return $this
     * @throws InvalidParameterValueException if policy is invalid
     */
    public function setPullPolicy(string $pull): self
    {
        $allowedValues = [self::PULL_ALWAYS, self::PULL_MISSING, self::PULL_NEVER];

        if (! in_array($pull, $allowedValues)) {
            throw new InvalidParameterValueException(
                'pull',
                $pull,
                implode(', ', $allowedValues),
                'Invalid pull policy'
            );
        }

        $this->pull = $pull;

        return $this;
    }

    /**
     * Adds an image for caching
     *
     * @param string $image Image to use as cache
     * @return $this
     */
    public function addCacheFrom(string $image): self
    {
        if (empty($image)) {
            throw new InvalidParameterValueException(
                'image',
                $image,
                'non-empty image name',
                'Image name for caching cannot be empty'
            );
        }

        $this->cacheFrom[] = $image;

        return $this;
    }

    /**
     * Sets the output type
     *
     * @param string $outputType Output type (image, local, tar)
     * @return $this
     * @throws InvalidParameterValueException if type is invalid
     */
    public function setOutputType(string $outputType): self
    {
        $allowedValues = [self::OUTPUT_TYPE_IMAGE, self::OUTPUT_TYPE_LOCAL, self::OUTPUT_TYPE_TAR];

        if (! in_array($outputType, $allowedValues)) {
            throw new InvalidParameterValueException(
                'outputType',
                $outputType,
                implode(', ', $allowedValues),
                'Invalid output type'
            );
        }

        $this->outputType = $outputType;

        return $this;
    }

    /**
     * Sets security options
     *
     * @param string $securityOpt Security options
     * @return $this
     */
    public function setSecurityOpt(string $securityOpt): self
    {
        if (empty($securityOpt)) {
            throw new InvalidParameterValueException(
                'securityOpt',
                $securityOpt,
                'non-empty string',
                'Security options cannot be empty'
            );
        }

        $this->securityOpt = $securityOpt;

        return $this;
    }

    /**
     * Converts build options to arrays for Docker API
     *
     * @return array{parameters: array<string, mixed>, config: array<string, mixed>} Arrays of parameters and configuration
     * @throws InvalidConfigurationException if configuration is invalid
     */
    public function toArrays(): array
    {
        // Validate required parameters
        $validationErrors = [];

        if ($this->tag === null) {
            $validationErrors[] = 'Image tag (setTag) is required';
        }

        if ($this->context === null) {
            $validationErrors[] = 'Build context (setContext) is required';
        }

        if ($this->dockerfile === null && $this->dockerfileContent === null) {
            $validationErrors[] = 'Either Dockerfile path or content must be set';
        }

        if (! empty($validationErrors)) {
            throw new InvalidConfigurationException(
                'ImageBuildOptions',
                $validationErrors
            );
        }

        // Prepare query parameters
        $parameters = [
            't' => $this->tag,
        ];

        if ($this->dockerfile !== null) {
            $parameters['dockerfile'] = $this->dockerfile;
        }

        if ($this->noCache) {
            $parameters['nocache'] = true;
        }

        if ($this->quiet) {
            $parameters['q'] = true;
        }

        if ($this->rm !== null) {
            $parameters['rm'] = $this->rm;
        }

        if ($this->forceRm) {
            $parameters['forcerm'] = true;
        }

        if (! empty($this->buildArgs)) {
            $parameters['buildargs'] = json_encode($this->buildArgs);
        }

        if (! empty($this->labels)) {
            $parameters['labels'] = json_encode($this->labels);
        }

        if ($this->target !== null) {
            $parameters['target'] = $this->target;
        }

        if ($this->platform !== null) {
            $parameters['platform'] = $this->platform;
        }

        if (! empty($this->extraHosts)) {
            $extraHostsList = [];
            foreach ($this->extraHosts as $hostname => $ip) {
                $extraHostsList[] = "$hostname:$ip";
            }
            $parameters['extrahosts'] = implode(',', $extraHostsList);
        }

        if ($this->squash) {
            $parameters['squash'] = true;
        }

        if ($this->network !== null) {
            $parameters['networkmode'] = $this->network;
        }

        if ($this->pull !== null) {
            $parameters['pull'] = $this->pull;
        }

        if (! empty($this->cacheFrom)) {
            $parameters['cachefrom'] = json_encode($this->cacheFrom);
        }

        if ($this->outputType !== null) {
            $parameters['outputtype'] = $this->outputType;
        }

        if ($this->securityOpt !== null) {
            $parameters['securityopt'] = $this->securityOpt;
        }

        // Prepare configuration
        $config = [
            'context' => $this->context,
        ];

        if ($this->dockerfileContent !== null) {
            $config['dockerfileContent'] = $this->dockerfileContent;
        }

        if (! empty($this->secrets)) {
            $secretsList = [];
            foreach ($this->secrets as $id => $source) {
                $secretsList[] = [
                    'id' => $id,
                    'source' => $source,
                ];
            }
            $config['secrets'] = $secretsList;
        }

        if (! empty($this->sshSources)) {
            $sshList = [];
            foreach ($this->sshSources as $id => $source) {
                $sshList[] = [
                    'id' => $id,
                    'source' => $source,
                ];
            }
            $config['ssh'] = $sshList;
        }

        if (! empty($this->extraContexts)) {
            $contextsList = [];
            foreach ($this->extraContexts as $name => $source) {
                $contextsList[] = [
                    'name' => $name,
                    'source' => $source,
                ];
            }
            $config['extraContexts'] = $contextsList;
        }

        return [
            'parameters' => $parameters,
            'config' => $config,
        ];
    }
}
