<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Api;

use Sangezar\DockerClient\Api\Interface\SystemInterface;
use Sangezar\DockerClient\Exception\OperationFailedException;
use Sangezar\DockerClient\Exception\Validation\InvalidParameterValueException;
use Sangezar\DockerClient\Exception\Validation\MissingRequiredParameterException;

/**
 * Docker System API client
 */
class System extends AbstractApi implements SystemInterface
{
    /**
     * Constants for event types
     */
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

    /**
     * Constants for container event types
     */
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

    /**
     * Constants for image event types
     */
    public const IMAGE_EVENT_DELETE = 'delete';
    public const IMAGE_EVENT_IMPORT = 'import';
    public const IMAGE_EVENT_LOAD = 'load';
    public const IMAGE_EVENT_PULL = 'pull';
    public const IMAGE_EVENT_PUSH = 'push';
    public const IMAGE_EVENT_SAVE = 'save';
    public const IMAGE_EVENT_TAG = 'tag';
    public const IMAGE_EVENT_UNTAG = 'untag';

    /**
     * Constants for volume event types
     */
    public const VOLUME_EVENT_CREATE = 'create';
    public const VOLUME_EVENT_DESTROY = 'destroy';
    public const VOLUME_EVENT_MOUNT = 'mount';
    public const VOLUME_EVENT_UNMOUNT = 'unmount';

    /**
     * Constants for network event types
     */
    public const NETWORK_EVENT_CREATE = 'create';
    public const NETWORK_EVENT_CONNECT = 'connect';
    public const NETWORK_EVENT_DESTROY = 'destroy';
    public const NETWORK_EVENT_DISCONNECT = 'disconnect';
    public const NETWORK_EVENT_REMOVE = 'remove';

    /**
     * Get Docker version information
     *
     * @return array<string, mixed> Docker version details including version, API version, Git commit, OS/architecture, etc.
     * @throws OperationFailedException if the request fails
     */
    public function version(): array
    {
        try {
            return $this->get('/version');
        } catch (\Exception $e) {
            throw new OperationFailedException(
                'version',
                'system',
                '',
                'Failed to get Docker version information',
                0,
                $e
            );
        }
    }

    /**
     * Get system-wide information
     *
     * @return array<string, mixed> System information including containers count, images count, server version, etc.
     * @throws OperationFailedException if the request fails
     */
    public function info(): array
    {
        try {
            return $this->get('/info');
        } catch (\Exception $e) {
            throw new OperationFailedException(
                'info',
                'system',
                '',
                'Failed to get system information',
                0,
                $e
            );
        }
    }

    /**
     * Check auth configuration
     *
     * @param array<string, mixed> $authConfig Authentication configuration
     *    - username: string - Username for registry authentication
     *    - password: string - Password for registry authentication
     *    - email: string - Email for registry authentication (optional)
     *    - serveraddress: string - Address of the registry (e.g., https://index.docker.io/v1/)
     *    - identitytoken: string - Identity token for registry (optional)
     *    - registrytoken: string - Registry token (optional)
     * @return array<string, mixed> Authentication result with status and token if successful
     * @throws MissingRequiredParameterException if required parameters are missing
     * @throws OperationFailedException if the authentication fails
     */
    public function auth(array $authConfig): array
    {
        // Check for required fields
        if (
            // Standard authentication requires username, password and serveraddress
            (! isset($authConfig['username']) || empty($authConfig['username']) ||
             ! isset($authConfig['password']) || empty($authConfig['password']) ||
             ! isset($authConfig['serveraddress']) || empty($authConfig['serveraddress']))
            &&
            // Token authentication requires identitytoken
            (! isset($authConfig['identitytoken']) || empty($authConfig['identitytoken']))
        ) {
            throw new MissingRequiredParameterException(
                'authConfig',
                'Authentication configuration must include either username/password/serveraddress or identitytoken'
            );
        }

        try {
            return $this->post('/auth', ['json' => $authConfig]);
        } catch (\Exception $e) {
            $serverAddress = isset($authConfig['serveraddress']) && is_string($authConfig['serveraddress']) ? $authConfig['serveraddress'] : '';
            $username = isset($authConfig['username']) && is_string($authConfig['username']) ? $authConfig['username'] : '';
            $resourceId = ! empty($serverAddress) ? $serverAddress : (! empty($username) ? $username : 'unknown');

            throw new OperationFailedException(
                'auth',
                'system',
                $resourceId,
                'Authentication failed with Docker registry',
                0,
                $e
            );
        }
    }

    /**
     * Ping the docker server
     *
     * @return bool True if Docker daemon is responsive, false otherwise
     */
    public function ping(): bool
    {
        try {
            $response = $this->get('/_ping');

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get real-time events from the server
     *
     * @param array<string, mixed> $filters Filters to apply on events:
     *   - config: object with attributes (optional)
     *   - type: array of event types (optional, use the EVENT_TYPE_* constants)
     *   - until: timestamp (optional)
     *   - since: timestamp (optional)
     * @return array<int, array<string, mixed>> List of events
     * @throws InvalidParameterValueException if some filter values are invalid
     * @throws OperationFailedException if the request fails
     *
     * @example
     * // Get only container events
     * $events = $system->events(['type' => [System::EVENT_TYPE_CONTAINER]]);
     *
     * // Get events for both containers and networks since yesterday
     * $events = $system->events([
     *     'type' => [System::EVENT_TYPE_CONTAINER, System::EVENT_TYPE_NETWORK],
     *     'since' => strtotime('-1 day')
     * ]);
     */
    public function events(array $filters = []): array
    {
        // Validate event types if provided
        if (isset($filters['type']) && ! empty($filters['type'])) {
            // Ensure types is an array
            if (! is_array($filters['type'])) {
                $filters['type'] = [$filters['type']];
            }

            $validTypes = [
                self::EVENT_TYPE_CONTAINER, self::EVENT_TYPE_DAEMON,
                self::EVENT_TYPE_IMAGE, self::EVENT_TYPE_NETWORK,
                self::EVENT_TYPE_PLUGIN, self::EVENT_TYPE_VOLUME,
            ];

            foreach ($filters['type'] as $type) {
                if (! in_array($type, $validTypes)) {
                    throw new InvalidParameterValueException(
                        'type',
                        $type,
                        implode(', ', $validTypes),
                        'Invalid event type'
                    );
                }
            }
        }

        // Validate timestamps
        foreach (['until', 'since'] as $timeField) {
            if (isset($filters[$timeField])) {
                // Check if it's a numeric UNIX timestamp or a date string
                if (is_string($filters[$timeField]) && ! is_numeric($filters[$timeField])) {
                    // Check if the string can be converted to a timestamp
                    $timestamp = strtotime($filters[$timeField]);
                    if ($timestamp === false) {
                        throw new InvalidParameterValueException(
                            $timeField,
                            $filters[$timeField],
                            'UNIX timestamp or a valid date string',
                            "Invalid $timeField value"
                        );
                    }
                    // Convert date to timestamp
                    $filters[$timeField] = $timestamp;
                } elseif (! is_numeric($filters[$timeField])) {
                    throw new InvalidParameterValueException(
                        $timeField,
                        $filters[$timeField],
                        'UNIX timestamp or a valid date string',
                        "Invalid $timeField value"
                    );
                }
            }
        }

        // Check that 'since' is less than 'until' if both are provided
        if (isset($filters['since'], $filters['until'])) {
            $since = is_numeric($filters['since']) ? (int)$filters['since'] : 0;
            $until = is_numeric($filters['until']) ? (int)$filters['until'] : 0;

            if ($since >= $until) {
                throw new InvalidParameterValueException(
                    'since/until',
                    "since: $since, until: $until",
                    "'since' must be less than 'until'",
                    "Invalid time range"
                );
            }
        }

        try {
            $queryParams = [];
            if (! empty($filters)) {
                $queryParams['filters'] = json_encode($filters);
            }

            $response = $this->get('/events', ['query' => $queryParams]);

            // Ensure the response is an array with integer keys
            $result = [];
            foreach ($response as $item) {
                if (is_array($item)) {
                    $result[] = $item;
                }
            }

            return $result;
        } catch (\Exception $e) {
            throw new OperationFailedException(
                'events',
                'system',
                '',
                'Failed to get system events: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Get data usage information
     *
     * @return array<string, mixed> Data usage information
     * @throws OperationFailedException if the request fails
     */
    public function dataUsage(): array
    {
        try {
            return $this->get('/system/df');
        } catch (\Exception $e) {
            throw new OperationFailedException(
                'dataUsage',
                'system',
                '',
                'Failed to get data usage information',
                0,
                $e
            );
        }
    }

    /**
     * Get system usage data (alias to dataUsage)
     *
     * @return array<string, mixed> Docker data usage information
     * @throws OperationFailedException if the request fails
     */
    public function usage(): array
    {
        try {
            return $this->get('/system/df');
        } catch (\Exception $e) {
            throw new OperationFailedException(
                'get_usage',
                'system',
                '',
                'Failed to retrieve system resource usage: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Prune unused data
     *
     * @param array<string, mixed> $options Options for pruning
     *    - containers: bool - Prune containers
     *    - images: bool - Prune images
     *    - networks: bool - Prune networks
     *    - volumes: bool - Prune volumes
     *    - filters: array - Filters for pruning
     * @return array<string, mixed> Pruning results
     * @throws InvalidParameterValueException if invalid options are provided
     * @throws OperationFailedException if the operation fails
     */
    public function prune(array $options = []): array
    {
        // Validate options
        $allowedOptions = ['containers', 'images', 'networks', 'volumes', 'all', 'filters'];
        foreach (array_keys($options) as $option) {
            if (! in_array($option, $allowedOptions)) {
                throw new InvalidParameterValueException(
                    'options',
                    $options,
                    implode(', ', $allowedOptions),
                    sprintf(
                        'Option "%s" is not supported for the prune method. Allowed options: %s',
                        $option,
                        implode(', ', $allowedOptions)
                    )
                );
            }
        }

        try {
            // By default, clean everything
            $all = $options['all'] ?? true;
            $endpoint = '/system/prune';
            $params = ['query' => ['all' => $all]];

            // Add filters if they exist
            if (isset($options['filters'])) {
                if (is_array($options['filters'])) {
                    $params['query']['filters'] = json_encode($options['filters']);
                } else {
                    throw new InvalidParameterValueException(
                        'filters',
                        $options['filters'],
                        'array',
                        'The filters option must be an array'
                    );
                }
            }

            return $this->post($endpoint, $params);
        } catch (InvalidParameterValueException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new OperationFailedException(
                'prune',
                'system',
                '',
                'Failed to prune unused Docker data: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }
}
