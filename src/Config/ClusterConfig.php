<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Config;

use Sangezar\DockerClient\Cluster\Operations\AbstractOperations;
use Sangezar\DockerClient\Exception\Validation\InvalidParameterValueException;

/**
 * Docker cluster operations configuration class
 */
class ClusterConfig
{
    private string $executionStrategy = AbstractOperations::EXECUTION_SEQUENTIAL;
    private string $errorDetailLevel = AbstractOperations::ERROR_LEVEL_STANDARD;
    private bool $retryOnFailure = false;
    private int $maxRetries = 3;
    private int $retryDelay = 1000; // ms
    private bool $exponentialBackoff = true;
    private int $operationTimeout = 30; // seconds
    /** @var array<string, int> */
    private array $nodePriorities = [];
    private ?string $defaultNodeTag = null;
    /** @var array<int, string> */
    private array $failoverNodes = [];

    /**
     * Creates a new cluster configuration instance
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Sets the execution strategy for cluster node operations
     *
     * @param string $strategy Execution strategy (EXECUTION_SEQUENTIAL or EXECUTION_PARALLEL)
     * @return $this
     * @throws InvalidParameterValueException if an unknown strategy is specified
     */
    public function setExecutionStrategy(string $strategy): self
    {
        $allowedStrategies = [
            AbstractOperations::EXECUTION_SEQUENTIAL,
            AbstractOperations::EXECUTION_PARALLEL,
        ];

        if (! in_array($strategy, $allowedStrategies)) {
            throw new InvalidParameterValueException(
                'strategy',
                $strategy,
                implode(', ', $allowedStrategies),
                'Unknown execution strategy. Allowed strategies: ' . implode(', ', $allowedStrategies)
            );
        }

        $this->executionStrategy = $strategy;

        return $this;
    }

    /**
     * Sets the error detail level
     *
     * @param string $level Detail level (ERROR_LEVEL_BASIC, ERROR_LEVEL_STANDARD, ERROR_LEVEL_DETAILED)
     * @return $this
     * @throws InvalidParameterValueException if an unknown level is specified
     */
    public function setErrorDetailLevel(string $level): self
    {
        $allowedLevels = [
            AbstractOperations::ERROR_LEVEL_BASIC,
            AbstractOperations::ERROR_LEVEL_STANDARD,
            AbstractOperations::ERROR_LEVEL_DETAILED,
        ];

        if (! in_array($level, $allowedLevels)) {
            throw new InvalidParameterValueException(
                'level',
                $level,
                implode(', ', $allowedLevels),
                'Unknown error detail level. Allowed levels: ' . implode(', ', $allowedLevels)
            );
        }

        $this->errorDetailLevel = $level;

        return $this;
    }

    /**
     * Configures retry settings for error handling
     *
     * @param bool $enable Whether to enable retries on failure
     * @param int|null $maxRetries Maximum number of attempts (default 3)
     * @param int|null $retryDelay Initial delay between retries in ms (default 1000)
     * @param bool|null $exponentialBackoff Whether to use exponential backoff between retries
     * @return $this
     * @throws InvalidParameterValueException if parameters are invalid
     */
    public function setRetryConfig(bool $enable, ?int $maxRetries = null, ?int $retryDelay = null, ?bool $exponentialBackoff = null): self
    {
        $this->retryOnFailure = $enable;

        if ($maxRetries !== null) {
            if ($maxRetries < 1) {
                throw new InvalidParameterValueException(
                    'maxRetries',
                    $maxRetries,
                    'integer >= 1',
                    'Maximum number of retries must be at least 1'
                );
            }
            $this->maxRetries = $maxRetries;
        }

        if ($retryDelay !== null) {
            if ($retryDelay < 0) {
                throw new InvalidParameterValueException(
                    'retryDelay',
                    $retryDelay,
                    'integer >= 0',
                    'Retry delay must be at least 0 ms'
                );
            }
            $this->retryDelay = $retryDelay;
        }

        if ($exponentialBackoff !== null) {
            $this->exponentialBackoff = $exponentialBackoff;
        }

        return $this;
    }

    /**
     * Sets the timeout for cluster node operations
     *
     * @param int $seconds Timeout in seconds
     * @return $this
     * @throws InvalidParameterValueException if timeout value is invalid
     */
    public function setOperationTimeout(int $seconds): self
    {
        if ($seconds <= 0) {
            throw new InvalidParameterValueException(
                'seconds',
                $seconds,
                'integer > 0',
                'Operation timeout must be greater than 0 seconds'
            );
        }

        $this->operationTimeout = $seconds;

        return $this;
    }

    /**
     * Sets the priority for a node
     *
     * @param string $nodeName Node name
     * @param int $priority Priority (1 - highest)
     * @return $this
     * @throws InvalidParameterValueException if priority value is invalid
     */
    public function setNodePriority(string $nodeName, int $priority): self
    {
        if (empty($nodeName)) {
            throw new InvalidParameterValueException(
                'nodeName',
                $nodeName,
                'non-empty string',
                'Node name cannot be empty'
            );
        }

        if ($priority <= 0) {
            throw new InvalidParameterValueException(
                'priority',
                $priority,
                'integer > 0',
                'Node priority must be greater than 0'
            );
        }

        $this->nodePriorities[$nodeName] = $priority;

        return $this;
    }

    /**
     * Sets the default tag for operations
     *
     * @param string|null $tag Tag to use when filtering nodes
     * @return $this
     */
    public function setDefaultNodeTag(?string $tag): self
    {
        $this->defaultNodeTag = $tag;

        return $this;
    }

    /**
     * Adds a node to the failover nodes list
     *
     * @param string $nodeName Node name to use when primary nodes fail
     * @return $this
     * @throws InvalidParameterValueException if node name is empty
     */
    public function addFailoverNode(string $nodeName): self
    {
        if (empty($nodeName)) {
            throw new InvalidParameterValueException(
                'nodeName',
                $nodeName,
                'non-empty string',
                'Node name cannot be empty'
            );
        }

        if (! in_array($nodeName, $this->failoverNodes)) {
            $this->failoverNodes[] = $nodeName;
        }

        return $this;
    }

    /**
     * Gets the execution strategy
     *
     * @return string Execution strategy
     */
    public function getExecutionStrategy(): string
    {
        return $this->executionStrategy;
    }

    /**
     * Gets the error detail level
     *
     * @return string Detail level
     */
    public function getErrorDetailLevel(): string
    {
        return $this->errorDetailLevel;
    }

    /**
     * Перевіряє, чи дозволені повторні спроби при помилках
     *
     * @return bool true якщо дозволені, false якщо ні
     */
    public function isRetryOnFailure(): bool
    {
        return $this->retryOnFailure;
    }

    /**
     * Отримує максимальну кількість повторних спроб
     *
     * @return int Кількість спроб
     */
    public function getMaxRetries(): int
    {
        return $this->maxRetries;
    }

    /**
     * Отримує затримку між повторними спробами
     *
     * @return int Затримка в мс
     */
    public function getRetryDelay(): int
    {
        return $this->retryDelay;
    }

    /**
     * Перевіряє, чи використовується експоненційна затримка між спробами
     *
     * @return bool true якщо використовується, false якщо ні
     */
    public function isExponentialBackoff(): bool
    {
        return $this->exponentialBackoff;
    }

    /**
     * Отримує таймаут операцій
     *
     * @return int Таймаут в секундах
     */
    public function getOperationTimeout(): int
    {
        return $this->operationTimeout;
    }

    /**
     * Отримує пріоритети вузлів
     *
     * @return array<string, int> Асоціативний масив пріоритетів (ім'я_вузла => пріоритет)
     */
    public function getNodePriorities(): array
    {
        return $this->nodePriorities;
    }

    /**
     * Отримує тег за замовчуванням
     *
     * @return string|null Тег або null якщо не встановлено
     */
    public function getDefaultNodeTag(): ?string
    {
        return $this->defaultNodeTag;
    }

    /**
     * Отримує список failover вузлів
     *
     * @return array<int, string> Масив імен вузлів
     */
    public function getFailoverNodes(): array
    {
        return $this->failoverNodes;
    }

    /**
     * Конвертує конфігурацію в масив для зручного використання
     *
     * @return array<string, mixed> Конфігурація у вигляді масиву
     */
    public function toArray(): array
    {
        return [
            'executionStrategy' => $this->executionStrategy,
            'errorDetailLevel' => $this->errorDetailLevel,
            'retryOnFailure' => $this->retryOnFailure,
            'maxRetries' => $this->maxRetries,
            'retryDelay' => $this->retryDelay,
            'exponentialBackoff' => $this->exponentialBackoff,
            'operationTimeout' => $this->operationTimeout,
            'nodePriorities' => $this->nodePriorities,
            'defaultNodeTag' => $this->defaultNodeTag,
            'failoverNodes' => $this->failoverNodes,
        ];
    }
}
