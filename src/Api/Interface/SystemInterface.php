<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Api\Interface;

use Sangezar\DockerClient\Exception\OperationFailedException;
use Sangezar\DockerClient\Exception\Validation\InvalidParameterValueException;

/**
 * Interface for working with Docker system APIs
 */
interface SystemInterface extends ApiInterface
{
    /** Constants for event types */
    public const EVENT_TYPE_CONTAINER = 'container';
    public const EVENT_TYPE_IMAGE = 'image';
    public const EVENT_TYPE_VOLUME = 'volume';
    public const EVENT_TYPE_NETWORK = 'network';
    public const EVENT_TYPE_DAEMON = 'daemon';
    public const EVENT_TYPE_PLUGIN = 'plugin';
    public const EVENT_TYPE_SERVICE = 'service';
    public const EVENT_TYPE_NODE = 'node';
    public const EVENT_TYPE_SECRET = 'secret';
    public const EVENT_TYPE_CONFIG = 'config';

    /** Constants for container events */
    public const CONTAINER_EVENT_ATTACH = 'attach';
    public const CONTAINER_EVENT_COMMIT = 'commit';
    public const CONTAINER_EVENT_COPY = 'copy';
    public const CONTAINER_EVENT_CREATE = 'create';
    public const CONTAINER_EVENT_DESTROY = 'destroy';
    public const CONTAINER_EVENT_DETACH = 'detach';
    public const CONTAINER_EVENT_DIE = 'die';
    public const CONTAINER_EVENT_EXEC_CREATE = 'exec_create';
    public const CONTAINER_EVENT_EXEC_DETACH = 'exec_detach';
    public const CONTAINER_EVENT_EXEC_START = 'exec_start';
    public const CONTAINER_EVENT_EXEC_DIE = 'exec_die';
    public const CONTAINER_EVENT_EXPORT = 'export';
    public const CONTAINER_EVENT_HEALTH_STATUS = 'health_status';
    public const CONTAINER_EVENT_KILL = 'kill';
    public const CONTAINER_EVENT_OOM = 'oom';
    public const CONTAINER_EVENT_PAUSE = 'pause';
    public const CONTAINER_EVENT_RENAME = 'rename';
    public const CONTAINER_EVENT_RESIZE = 'resize';
    public const CONTAINER_EVENT_RESTART = 'restart';
    public const CONTAINER_EVENT_START = 'start';
    public const CONTAINER_EVENT_STOP = 'stop';
    public const CONTAINER_EVENT_TOP = 'top';
    public const CONTAINER_EVENT_UNPAUSE = 'unpause';
    public const CONTAINER_EVENT_UPDATE = 'update';

    /** Constants for image events */
    public const IMAGE_EVENT_DELETE = 'delete';
    public const IMAGE_EVENT_IMPORT = 'import';
    public const IMAGE_EVENT_LOAD = 'load';
    public const IMAGE_EVENT_PULL = 'pull';
    public const IMAGE_EVENT_PUSH = 'push';
    public const IMAGE_EVENT_SAVE = 'save';
    public const IMAGE_EVENT_TAG = 'tag';
    public const IMAGE_EVENT_UNTAG = 'untag';

    /**
     * Gets information about Docker Engine
     *
     * @return array<string, mixed> Docker Engine information
     * @throws OperationFailedException if the operation fails
     */
    public function info(): array;

    /**
     * Gets Docker Engine version
     *
     * @return array<string, mixed> Docker Engine version information
     * @throws OperationFailedException if the operation fails
     */
    public function version(): array;

    /**
     * Checks connection to Docker Engine
     *
     * @return bool Check result
     */
    public function ping(): bool;

    /**
     * Gets Docker events
     *
     * @param array<string, mixed> $filters Event filters
     * @return array<int, array<string, mixed>> List of events
     * @throws InvalidParameterValueException if invalid parameters are provided
     * @throws OperationFailedException if the operation fails
     */
    public function events(array $filters = []): array;

    /**
     * Gets system resource usage (CPU, memory, network, etc.)
     *
     * @return array<string, mixed> Resource usage information
     * @throws OperationFailedException if the operation fails
     */
    public function usage(): array;

    /**
     * Cleans up unused Docker data
     *
     * @param array<string, mixed> $options Cleanup options
     * @return array<string, mixed> Operation result
     * @throws InvalidParameterValueException if invalid parameters are provided
     * @throws OperationFailedException if the operation fails
     */
    public function prune(array $options = []): array;
}
