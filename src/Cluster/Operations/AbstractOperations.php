<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Cluster\Operations;

use Sangezar\DockerClient\Config\ClusterConfig;
use Sangezar\DockerClient\DockerClient;
use Sangezar\DockerClient\Exception\Validation\InvalidParameterValueException;
use Sangezar\DockerClient\Exception\Validation\MissingRequiredParameterException;

/**
 * Base class for cluster operations
 */
abstract class AbstractOperations
{
    /**
     * Execution strategy types
     */
    public const EXECUTION_SEQUENTIAL = 'sequential';
    public const EXECUTION_PARALLEL = 'parallel';

    /**
     * Error detail level settings
     */
    public const ERROR_LEVEL_BASIC = 'basic';      // Message only
    public const ERROR_LEVEL_STANDARD = 'standard'; // Message + exception type + code
    public const ERROR_LEVEL_DETAILED = 'detailed'; // All details including stack trace

    /** @var array<string, DockerClient> */
    protected array $nodes;

    /** @var string Execution strategy */
    protected string $executionStrategy = self::EXECUTION_SEQUENTIAL;

    /** @var string Error detail level */
    protected string $errorDetailLevel = self::ERROR_LEVEL_STANDARD;

    /** @var bool Allow automatic retries on failure */
    protected bool $retryOnFailure = false;

    /** @var int Maximum number of retries */
    protected int $maxRetries = 3;

    /**
     * Constructor
     *
     * @param array<string, DockerClient> $nodes Array of Docker API clients with node names as keys
     * @param ClusterConfig|null $config Cluster configuration (optional)
     * @throws MissingRequiredParameterException If nodes array is empty
     * @throws InvalidParameterValueException If parameters are invalid
     */
    public function __construct(array $nodes, ?ClusterConfig $config = null)
    {
        if (empty($nodes)) {
            throw new MissingRequiredParameterException(
                'nodes',
                'Node collection cannot be empty'
            );
        }

        // Validate nodes
        foreach ($nodes as $name => $client) {
            if (empty($name)) {
                throw new InvalidParameterValueException(
                    'node name',
                    $name,
                    'non-empty string',
                    'Node name must be a non-empty string'
                );
            }

            if (! $client instanceof DockerClient) {
                throw new InvalidParameterValueException(
                    'node client',
                    $client,
                    DockerClient::class,
                    sprintf('Node client must be an instance of %s', DockerClient::class)
                );
            }
        }

        $this->nodes = $nodes;

        // Apply configuration if provided
        if ($config !== null) {
            $this->applyConfig($config);
        }
    }

    /**
     * Applies cluster configuration
     *
     * @param ClusterConfig $config Configuration to apply
     * @return $this
     */
    public function applyConfig(ClusterConfig $config): self
    {
        $this->executionStrategy = $config->getExecutionStrategy();
        $this->errorDetailLevel = $config->getErrorDetailLevel();
        $this->retryOnFailure = $config->isRetryOnFailure();
        $this->maxRetries = $config->getMaxRetries();

        return $this;
    }

    /**
     * Sets execution strategy
     *
     * @param string $strategy Execution strategy (EXECUTION_SEQUENTIAL or EXECUTION_PARALLEL)
     * @return $this
     * @throws InvalidParameterValueException If an unknown strategy is provided
     */
    public function setExecutionStrategy(string $strategy): self
    {
        $allowedStrategies = [self::EXECUTION_SEQUENTIAL, self::EXECUTION_PARALLEL];

        if (! in_array($strategy, $allowedStrategies)) {
            throw new InvalidParameterValueException(
                'strategy',
                $strategy,
                implode(' or ', $allowedStrategies),
                sprintf(
                    'Invalid execution strategy: %s. Allowed values: %s',
                    $strategy,
                    implode(', ', $allowedStrategies)
                )
            );
        }

        $this->executionStrategy = $strategy;

        return $this;
    }

    /**
     * Sets error detail level
     *
     * @param string $level Detail level (ERROR_LEVEL_BASIC, ERROR_LEVEL_STANDARD or ERROR_LEVEL_DETAILED)
     * @return $this
     * @throws InvalidParameterValueException If an unknown level is provided
     */
    public function setErrorDetailLevel(string $level): self
    {
        $allowedLevels = [self::ERROR_LEVEL_BASIC, self::ERROR_LEVEL_STANDARD, self::ERROR_LEVEL_DETAILED];

        if (! in_array($level, $allowedLevels)) {
            throw new InvalidParameterValueException(
                'level',
                $level,
                implode(' or ', $allowedLevels),
                sprintf(
                    'Invalid error detail level: %s. Allowed values: %s',
                    $level,
                    implode(', ', $allowedLevels)
                )
            );
        }

        $this->errorDetailLevel = $level;

        return $this;
    }

    /**
     * Sets retry on failure settings
     *
     * @param bool $enable Whether retries are allowed
     * @param int|null $maxRetries Maximum number of retries (default 3)
     * @return $this
     * @throws InvalidParameterValueException If retries count is less than 1
     */
    public function setRetryOnFailure(bool $enable, ?int $maxRetries = null): self
    {
        $this->retryOnFailure = $enable;

        if ($maxRetries !== null) {
            if ($maxRetries < 1) {
                throw new InvalidParameterValueException(
                    'maxRetries',
                    $maxRetries,
                    'positive integer',
                    'Maximum retries must be greater than zero'
                );
            }
            $this->maxRetries = $maxRetries;
        }

        return $this;
    }

    /**
     * Executes operation on all cluster nodes
     *
     * @param callable $operation Function to be executed on each node
     * @return array<string, array<string, mixed>|bool> Execution results for each node
     */
    protected function executeOnAll(callable $operation): array
    {
        if (! is_callable($operation)) {
            throw new InvalidParameterValueException(
                'operation',
                $operation,
                'callable',
                'Operation must be a callable'
            );
        }

        if ($this->executionStrategy === self::EXECUTION_PARALLEL) {
            return $this->executeParallel($operation);
        }

        return $this->executeSequential($operation);
    }

    /**
     * Executes operation sequentially on all cluster nodes
     *
     * @param callable $operation Function to be executed on each node
     * @return array<string, array{error: bool, message: string, exception?: class-string, code?: int, file?: string, line?: int, trace?: string}|bool|array<string, mixed>> Execution results for each node
     */
    private function executeSequential(callable $operation): array
    {
        $results = [];

        foreach ($this->nodes as $name => $client) {
            $retryCount = 0;
            $success = false;

            while (! $success && ($retryCount <= $this->maxRetries)) {
                try {
                    $results[$name] = $operation($client);
                    $success = true;
                } catch (\Throwable $e) {
                    $retryCount++;

                    if (! $this->retryOnFailure || $retryCount > $this->maxRetries) {
                        $results[$name] = $this->formatError($e);

                        break;
                    }

                    // Exponential backoff between retries
                    if ($retryCount <= $this->maxRetries) {
                        usleep(100000 * pow(2, $retryCount - 1)); // 100ms, 200ms, 400ms, ...
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Executes operation in parallel on all cluster nodes
     *
     * @param callable $operation Function to be executed on each node
     * @return array<string, array{error: bool, message: string, exception?: class-string, code?: int, file?: string, line?: int, trace?: string}|bool|array<string, mixed>> Execution results for each node
     */
    private function executeParallel(callable $operation): array
    {
        $results = [];
        $pendingNodes = [];

        // Initialize results for all nodes
        foreach ($this->nodes as $name => $client) {
            $pendingNodes[$name] = [
                'client' => $client,
                'retries' => 0,
                'completed' => false,
            ];
        }

        // Execute operations on all nodes until completion or retries exhausted
        while (! empty($pendingNodes)) {
            foreach ($pendingNodes as $name => &$nodeData) {
                try {
                    // Execute operation for current node
                    $results[$name] = $operation($nodeData['client']);
                    $nodeData['completed'] = true;
                    unset($pendingNodes[$name]);
                } catch (\Throwable $e) {
                    $nodeData['retries']++;

                    if (! $this->retryOnFailure || $nodeData['retries'] > $this->maxRetries) {
                        $results[$name] = $this->formatError($e);
                        $nodeData['completed'] = true;
                        unset($pendingNodes[$name]);
                    }
                }
            }

            // If there are pending operations, wait a small interval before retrying
            if (! empty($pendingNodes)) {
                usleep(50000); // 50ms between iterations to reduce CPU load
            }
        }

        return $results;
    }

    /**
     * Formats error according to configured detail level
     *
     * @param \Throwable $e Exception
     * @return array{error: bool, message: string, exception?: string, code?: int, file?: string, line?: int, trace?: string} Formatted error
     */
    private function formatError(\Throwable $e): array
    {
        $error = [
            'error' => true,
            'message' => $e->getMessage(),
        ];

        if ($this->errorDetailLevel !== self::ERROR_LEVEL_BASIC) {
            $error['exception'] = get_class($e);
            $error['code'] = $e->getCode();
        }

        if ($this->errorDetailLevel === self::ERROR_LEVEL_DETAILED) {
            $error['file'] = $e->getFile();
            $error['line'] = $e->getLine();
            $error['trace'] = $e->getTraceAsString();
        }

        return $error;
    }

    /**
     * Returns list of all nodes
     *
     * @return array<string, DockerClient>
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    /**
     * Checks if node collection is empty
     *
     * @return bool true if there are no nodes
     */
    public function isEmpty(): bool
    {
        return empty($this->nodes);
    }

    /**
     * Returns node count
     *
     * @return int Number of nodes
     */
    public function count(): int
    {
        return count($this->nodes);
    }

    /**
     * Adds a new node to the collection
     *
     * @param string $name Node name
     * @param DockerClient $client Docker API client
     * @return $this
     * @throws InvalidParameterValueException If node name is empty or node with this name already exists
     */
    public function addNode(string $name, DockerClient $client): self
    {
        if (empty($name)) {
            throw new InvalidParameterValueException(
                'name',
                $name,
                'non-empty string',
                'Node name cannot be empty'
            );
        }

        if (isset($this->nodes[$name])) {
            throw new InvalidParameterValueException(
                'name',
                $name,
                'unique node name',
                sprintf('Node with name "%s" already exists', $name)
            );
        }

        $this->nodes[$name] = $client;

        return $this;
    }

    /**
     * Removes a node from the collection
     *
     * @param string $name Node name
     * @return $this
     * @throws InvalidParameterValueException If node with this name does not exist
     */
    public function removeNode(string $name): self
    {
        if (! isset($this->nodes[$name])) {
            throw new InvalidParameterValueException(
                'name',
                $name,
                'existing node name',
                sprintf('Node with name "%s" not found', $name)
            );
        }

        unset($this->nodes[$name]);

        return $this;
    }

    /**
     * Checks if node with the specified name exists
     *
     * @param string $name Node name
     * @return bool true if the node exists
     */
    public function hasNode(string $name): bool
    {
        return isset($this->nodes[$name]);
    }
}
