<?php

declare(strict_types=1);

namespace Sangezar\DockerClient\Api;

use Sangezar\DockerClient\Api\Interface\ContainerInterface;
use Sangezar\DockerClient\Config\ContainerConfig;
use Sangezar\DockerClient\Exception\NotFoundException;
use Sangezar\DockerClient\Exception\OperationFailedException;
use Sangezar\DockerClient\Exception\Validation\InvalidConfigurationException;
use Sangezar\DockerClient\Exception\Validation\InvalidParameterValueException;
use Sangezar\DockerClient\Exception\Validation\MissingRequiredParameterException;

/**
 * Docker Container API client
 */
class Container extends AbstractApi implements ContainerInterface
{
    /**
     * Constants for signals that can be sent to a container
     */
    public const SIGNAL_HUP = 'SIGHUP';
    public const SIGNAL_INT = 'SIGINT';
    public const SIGNAL_QUIT = 'SIGQUIT';
    public const SIGNAL_ILL = 'SIGILL';
    public const SIGNAL_TRAP = 'SIGTRAP';
    public const SIGNAL_ABRT = 'SIGABRT';
    public const SIGNAL_BUS = 'SIGBUS';
    public const SIGNAL_FPE = 'SIGFPE';
    public const SIGNAL_KILL = 'SIGKILL';
    public const SIGNAL_USR1 = 'SIGUSR1';
    public const SIGNAL_SEGV = 'SIGSEGV';
    public const SIGNAL_USR2 = 'SIGUSR2';
    public const SIGNAL_PIPE = 'SIGPIPE';
    public const SIGNAL_ALRM = 'SIGALRM';
    public const SIGNAL_TERM = 'SIGTERM';
    public const SIGNAL_STKFLT = 'SIGSTKFLT';
    public const SIGNAL_CHLD = 'SIGCHLD';
    public const SIGNAL_CONT = 'SIGCONT';
    public const SIGNAL_STOP = 'SIGSTOP';
    public const SIGNAL_TSTP = 'SIGTSTP';
    public const SIGNAL_TTIN = 'SIGTTIN';
    public const SIGNAL_TTOU = 'SIGTTOU';
    public const SIGNAL_URG = 'SIGURG';
    public const SIGNAL_XCPU = 'SIGXCPU';
    public const SIGNAL_XFSZ = 'SIGXFSZ';
    public const SIGNAL_VTALRM = 'SIGVTALRM';
    public const SIGNAL_PROF = 'SIGPROF';
    public const SIGNAL_WINCH = 'SIGWINCH';
    public const SIGNAL_IO = 'SIGIO';
    public const SIGNAL_PWR = 'SIGPWR';
    public const SIGNAL_SYS = 'SIGSYS';
    public const SIGNAL_POLL = 'SIGPOLL';

    /**
     * List containers
     *
     * @param array<string, mixed> $parameters Parameters for filtering results
     *    - all: true|false - Show all containers (default shows just running)
     *    - limit: int - Limit the number of results
     *    - size: true|false - Show container sizes
     *    - filters: array|string - Filters in JSON format
     * @return array<int, array<string, mixed>> List of containers
     * @throws InvalidParameterValueException if invalid parameters are provided
     */
    public function list(array $parameters = []): array
    {
        // Validate parameters
        $allowedParams = ['all', 'limit', 'size', 'filters'];
        foreach (array_keys($parameters) as $param) {
            if (! in_array($param, $allowedParams)) {
                throw new InvalidParameterValueException(
                    'parameters',
                    $parameters,
                    implode(', ', $allowedParams),
                    sprintf(
                        'Parameter "%s" is not supported for the list method. Allowed parameters: %s',
                        $param,
                        implode(', ', $allowedParams)
                    )
                );
            }
        }

        // Validate limit
        if (isset($parameters['limit']) && (! is_int($parameters['limit']) || $parameters['limit'] < 1)) {
            throw new InvalidParameterValueException(
                'limit',
                $parameters['limit'],
                'integer greater than 0',
                sprintf(
                    'The "limit" parameter value must be an integer greater than 0, received: %s',
                    var_export($parameters['limit'], true)
                )
            );
        }

        // Validate all and size (must be boolean)
        foreach (['all', 'size'] as $boolParam) {
            if (isset($parameters[$boolParam]) && ! is_bool($parameters[$boolParam])) {
                throw new InvalidParameterValueException(
                    $boolParam,
                    $parameters[$boolParam],
                    'true or false',
                    sprintf(
                        'The "%s" parameter value must be boolean, received: %s',
                        $boolParam,
                        var_export($parameters[$boolParam], true)
                    )
                );
            }
        }

        // Handle the 'filters' parameter, which should be a JSON-encoded string in Docker API
        if (isset($parameters['filters']) && is_array($parameters['filters'])) {
            $parameters['filters'] = json_encode($parameters['filters']);
        }

        $response = $this->get('/containers/json', ['query' => $parameters]);

        // Ensure the response is an array with integer keys
        $result = [];
        foreach ($response as $item) {
            if (is_array($item)) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * Create a container
     *
     * @param ContainerConfig $config Container configuration
     * @return array<string, mixed> Container creation response
     * @throws MissingRequiredParameterException if required parameters are missing
     * @throws InvalidParameterValueException if invalid parameters are provided
     * @throws InvalidConfigurationException if configuration is invalid
     * @throws OperationFailedException if operation fails
     */
    public function create(ContainerConfig $config): array
    {
        // Extract parameters from the config object
        $configArray = $config->toArray();

        // Separate query parameters from container JSON data
        $queryParams = [];
        if (isset($configArray['name']) && is_string($configArray['name'])) {
            $queryParams['name'] = $configArray['name'];
            unset($configArray['name']);
        }

        try {
            return $this->post('/containers/create', [
                'query' => $queryParams,
                'json' => $configArray,
            ]);
        } catch (\Exception $e) {
            $name = $queryParams['name'] ?? null;

            throw new OperationFailedException(
                'create',
                'container',
                $name,
                'Failed to create container',
                0,
                $e
            );
        }
    }

    /**
     * Inspect a container
     *
     * @param string $containerId Container ID or name
     * @return array<string, mixed> Container info
     * @throws MissingRequiredParameterException if container ID is empty
     * @throws NotFoundException if the container is not found
     */
    public function inspect(string $containerId): array
    {
        // Validate container ID
        if (empty($containerId)) {
            throw new MissingRequiredParameterException(
                'containerId',
                'Container ID cannot be empty'
            );
        }

        try {
            return $this->get("/containers/{$containerId}/json");
        } catch (\Exception $e) {
            // Assume 404 means "not found"
            if (strpos($e->getMessage(), '404') !== false) {
                throw new NotFoundException('container', $containerId);
            }

            throw $e;
        }
    }

    /**
     * Start a container
     *
     * @param string $containerId Container ID or name
     * @return bool Result of operation
     * @throws MissingRequiredParameterException if container ID is empty
     * @throws OperationFailedException if operation fails
     */
    public function start(string $containerId): bool
    {
        // Validate container ID
        if (empty($containerId)) {
            throw new MissingRequiredParameterException(
                'containerId',
                'Container ID cannot be empty'
            );
        }

        try {
            $this->post("/containers/{$containerId}/start");

            return true;
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), '404') !== false) {
                throw new NotFoundException('container', $containerId);
            }

            if (strpos($e->getMessage(), '304') !== false) {
                // Container already started, not considered an error
                return true;
            }

            throw new OperationFailedException(
                'start',
                'container',
                $containerId,
                'Failed to start container',
                0,
                $e
            );
        }
    }

    /**
     * Stop a container
     *
     * @param string $containerId Container ID or name
     * @param int $timeout Timeout (in seconds) before killing the container
     * @return bool Result of operation
     * @throws MissingRequiredParameterException if container ID is empty
     * @throws InvalidParameterValueException if timeout is less than 0
     * @throws OperationFailedException if operation fails
     */
    public function stop(string $containerId, int $timeout = 10): bool
    {
        // Validate container ID
        if (empty($containerId)) {
            throw new MissingRequiredParameterException(
                'containerId',
                'Container ID cannot be empty'
            );
        }

        // Validate timeout
        if ($timeout < 0) {
            throw new InvalidParameterValueException(
                'timeout',
                $timeout,
                'integer >= 0',
                'Timeout value cannot be negative'
            );
        }

        try {
            $this->post("/containers/{$containerId}/stop", [
                'query' => ['t' => $timeout],
            ]);

            return true;
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), '404') !== false) {
                throw new NotFoundException('container', $containerId);
            }

            if (strpos($e->getMessage(), '304') !== false) {
                // Container already stopped, not considered an error
                return true;
            }

            throw new OperationFailedException(
                'stop',
                'container',
                $containerId,
                'Failed to stop container',
                0,
                $e
            );
        }
    }

    /**
     * Restart a container
     *
     * @param string $containerId Container ID or name
     * @param int $timeout Timeout (in seconds) before killing the container
     * @return bool Result of operation
     * @throws MissingRequiredParameterException if container ID is empty
     * @throws InvalidParameterValueException if timeout is less than 0
     * @throws OperationFailedException if operation fails
     */
    public function restart(string $containerId, int $timeout = 10): bool
    {
        // Validate container ID
        if (empty($containerId)) {
            throw new MissingRequiredParameterException(
                'containerId',
                'Container ID cannot be empty'
            );
        }

        // Validate timeout
        if ($timeout < 0) {
            throw new InvalidParameterValueException(
                'timeout',
                $timeout,
                'integer >= 0',
                'Timeout value cannot be negative'
            );
        }

        try {
            $this->post("/containers/{$containerId}/restart", [
                'query' => ['t' => $timeout],
            ]);

            return true;
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), '404') !== false) {
                throw new NotFoundException('container', $containerId);
            }

            throw new OperationFailedException(
                'restart',
                'container',
                $containerId,
                'Failed to restart container',
                0,
                $e
            );
        }
    }

    /**
     * Kill a container
     *
     * @param string $id Container ID or name
     * @param string $signal Signal to send (default: SIGKILL)
     * @return bool Success
     * @throws InvalidParameterValueException if the signal is invalid
     * @throws OperationFailedException if the operation fails
     *
     * @example
     * // Kill container with SIGTERM
     * $container->kill('my_container', Container::SIGNAL_TERM);
     *
     * // Kill container with SIGKILL (default)
     * $container->kill('my_container');
     *
     * // Send SIGHUP to container
     * $container->kill('my_container', Container::SIGNAL_HUP);
     */
    public function kill(string $id, string $signal = null): bool
    {
        $this->validateIdentifier($id, 'Container ID or name');

        $validSignals = [
            self::SIGNAL_HUP, self::SIGNAL_INT, self::SIGNAL_QUIT,
            self::SIGNAL_ILL, self::SIGNAL_TRAP, self::SIGNAL_ABRT,
            self::SIGNAL_BUS, self::SIGNAL_FPE, self::SIGNAL_KILL,
            self::SIGNAL_USR1, self::SIGNAL_SEGV, self::SIGNAL_USR2,
            self::SIGNAL_PIPE, self::SIGNAL_ALRM, self::SIGNAL_TERM,
            self::SIGNAL_STKFLT, self::SIGNAL_CHLD, self::SIGNAL_CONT,
            self::SIGNAL_STOP, self::SIGNAL_TSTP, self::SIGNAL_TTIN,
            self::SIGNAL_TTOU, self::SIGNAL_URG, self::SIGNAL_XCPU,
            self::SIGNAL_XFSZ, self::SIGNAL_VTALRM, self::SIGNAL_PROF,
            self::SIGNAL_WINCH, self::SIGNAL_POLL, self::SIGNAL_PWR,
            self::SIGNAL_SYS,
        ];

        $queryParams = [];

        if ($signal !== null) {
            if (! in_array($signal, $validSignals)) {
                throw new InvalidParameterValueException(
                    'signal',
                    $signal,
                    implode(', ', $validSignals),
                    'Invalid signal value'
                );
            }
            $queryParams['signal'] = $signal;
        } else {
            // Default to SIGKILL if not specified
            $queryParams['signal'] = self::SIGNAL_KILL;
        }

        try {
            $this->post("/containers/{$id}/kill", ['query' => $queryParams]);

            return true;
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), '404') !== false) {
                throw new NotFoundException('container', $id, $e->getMessage(), $e->getCode(), $e);
            }

            if (strpos($e->getMessage(), '409') !== false) {
                throw new OperationFailedException(
                    'kill',
                    'container',
                    $id,
                    'Container is not running',
                    0,
                    $e
                );
            }

            throw new OperationFailedException(
                'kill',
                'container',
                $id,
                'Failed to kill container: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Remove a container
     *
     * @param string $containerId Container ID or name
     * @param bool $force Force remove a running container
     * @param bool $removeVolumes Remove associated volumes
     * @return bool Result of operation
     * @throws MissingRequiredParameterException if container ID is empty
     * @throws OperationFailedException if operation fails
     */
    public function remove(string $containerId, bool $force = false, bool $removeVolumes = false): bool
    {
        // Validate container ID
        if (empty($containerId)) {
            throw new MissingRequiredParameterException(
                'containerId',
                'Container ID cannot be empty'
            );
        }

        try {
            $this->delete("/containers/{$containerId}", [
                'query' => [
                    'force' => $force,
                    'v' => $removeVolumes,
                ],
            ]);

            return true;
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), '404') !== false) {
                throw new NotFoundException('container', $containerId);
            }

            if (strpos($e->getMessage(), '409') !== false) {
                throw new OperationFailedException(
                    'remove',
                    'container',
                    $containerId,
                    'Failed to remove container: container may be running or has associated resources',
                    0,
                    $e
                );
            }

            throw new OperationFailedException(
                'remove',
                'container',
                $containerId,
                'Failed to remove container',
                0,
                $e
            );
        }
    }

    /**
     * Get container logs
     *
     * @param string $containerId Container ID or name
     * @param array<string, mixed> $parameters Parameters for filtering logs
     *    - follow: bool - Follow log output
     *    - stdout: bool - Return logs from stdout
     *    - stderr: bool - Return logs from stderr
     *    - since: int - Unix timestamp for logs since a specific time
     *    - until: int - Unix timestamp for logs until a specific time
     *    - timestamps: bool - Add timestamps to every log line
     *    - tail: string - Number of lines to show from the end of logs (e.g., "100" or "all")
     * @return array<string, mixed> Container logs
     * @throws MissingRequiredParameterException if container ID is empty
     * @throws InvalidParameterValueException if invalid parameters are provided
     * @throws OperationFailedException if operation fails
     */
    public function logs(string $containerId, array $parameters = []): array
    {
        // Validate container ID
        if (empty($containerId)) {
            throw new MissingRequiredParameterException(
                'containerId',
                'Container ID cannot be empty'
            );
        }

        // Validate parameters
        $allowedParams = ['follow', 'stdout', 'stderr', 'since', 'until', 'timestamps', 'tail'];
        foreach (array_keys($parameters) as $param) {
            if (! in_array($param, $allowedParams)) {
                throw new InvalidParameterValueException(
                    'parameters',
                    $parameters,
                    implode(', ', $allowedParams),
                    sprintf(
                        'Parameter "%s" is not supported for the logs method. Allowed parameters: %s',
                        $param,
                        implode(', ', $allowedParams)
                    )
                );
            }
        }

        // Validate boolean parameters
        foreach (['follow', 'stdout', 'stderr', 'timestamps'] as $boolParam) {
            if (isset($parameters[$boolParam]) && ! is_bool($parameters[$boolParam])) {
                throw new InvalidParameterValueException(
                    $boolParam,
                    $parameters[$boolParam],
                    'true or false',
                    sprintf(
                        'The "%s" parameter value must be boolean, received: %s',
                        $boolParam,
                        var_export($parameters[$boolParam], true)
                    )
                );
            }
        }

        // Validate tail parameter
        if (isset($parameters['tail']) && is_string($parameters['tail'])) {
            $tail = (string)$parameters['tail'];

            if ($tail !== 'all' && ! is_numeric($tail)) {
                throw new InvalidParameterValueException(
                    'tail',
                    $tail,
                    '"all" or a positive integer as string',
                    'The "tail" parameter value must be "all" or a positive integer'
                );
            }
        }

        try {
            return $this->get("/containers/{$containerId}/logs", ['query' => $parameters]);
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), '404') !== false) {
                throw new NotFoundException('container', $containerId);
            }

            throw $e;
        }
    }

    /**
     * Get container stats
     *
     * @param string $containerId Container ID or name
     * @param bool $stream Get a stream of stats instead of one result
     * @return array<string, mixed> Container stats
     * @throws MissingRequiredParameterException if container ID is empty
     * @throws NotFoundException if container is not found
     * @throws OperationFailedException if operation fails
     */
    public function stats(string $containerId, bool $stream = false): array
    {
        // Validate container ID
        if (empty($containerId)) {
            throw new MissingRequiredParameterException(
                'containerId',
                'Container ID cannot be empty'
            );
        }

        try {
            return $this->get("/containers/{$containerId}/stats", [
                'query' => ['stream' => $stream],
            ]);
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), '404') !== false) {
                throw new NotFoundException('container', $containerId);
            }

            throw $e;
        }
    }

    /**
     * Check if a container exists
     *
     * @param string $containerId Container ID or name
     * @return bool Whether the container exists
     * @throws MissingRequiredParameterException if container ID is empty
     */
    public function exists(string $containerId): bool
    {
        // Validate container ID
        if (empty($containerId)) {
            throw new MissingRequiredParameterException(
                'containerId',
                'Container ID cannot be empty'
            );
        }

        try {
            $response = $this->inspect($containerId);

            return isset($response['Id']);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Validate an identifier (container ID or name)
     *
     * @param string $id The identifier to validate
     * @param string $itemName The name of the item being identified
     * @throws MissingRequiredParameterException if the identifier is empty
     */
    protected function validateIdentifier(string $id, string $itemName): void
    {
        if (empty($id)) {
            throw new MissingRequiredParameterException(
                'id',
                "$itemName cannot be empty"
            );
        }
    }
}
