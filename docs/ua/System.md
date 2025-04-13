# Документація класу System

## Опис
`System` - це клас для роботи з системними функціями Docker через API. Він надає методи для отримання інформації про систему Docker, перевірки автентифікації, моніторингу подій та управління системними ресурсами.

## Простір імен
`Sangezar\DockerClient\Api`

## Успадкування
Клас `System` успадковується від `AbstractApi` та реалізує інтерфейс `SystemInterface`.

## Константи

### Типи подій
```php
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
```

### Типи подій для контейнерів
```php
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
```

### Типи подій для образів
```php
public const IMAGE_EVENT_DELETE = 'delete';
public const IMAGE_EVENT_IMPORT = 'import';
public const IMAGE_EVENT_LOAD = 'load';
public const IMAGE_EVENT_PULL = 'pull';
public const IMAGE_EVENT_PUSH = 'push';
public const IMAGE_EVENT_SAVE = 'save';
public const IMAGE_EVENT_TAG = 'tag';
public const IMAGE_EVENT_UNTAG = 'untag';
```

### Типи подій для томів
```php
public const VOLUME_EVENT_CREATE = 'create';
public const VOLUME_EVENT_DESTROY = 'destroy';
public const VOLUME_EVENT_MOUNT = 'mount';
public const VOLUME_EVENT_UNMOUNT = 'unmount';
```

### Типи подій для мереж
```php
public const NETWORK_EVENT_CREATE = 'create';
public const NETWORK_EVENT_CONNECT = 'connect';
public const NETWORK_EVENT_DESTROY = 'destroy';
public const NETWORK_EVENT_DISCONNECT = 'disconnect';
public const NETWORK_EVENT_REMOVE = 'remove';
```

## Методи

### version
```php
public function version(): array
```
Отримує інформацію про версію Docker.

#### Повертає:
- Масив з деталями про версію Docker, включаючи версію API, версію Git-коміту, OS/архітектуру тощо

#### Винятки:
- `OperationFailedException` - якщо запит не вдався

### info
```php
public function info(): array
```
Отримує загальносистемну інформацію.

#### Повертає:
- Масив з системною інформацією, включаючи кількість контейнерів, образів, версію сервера тощо

#### Винятки:
- `OperationFailedException` - якщо запит не вдався

### auth
```php
public function auth(array $authConfig): array
```
Перевіряє конфігурацію автентифікації.

#### Параметри:
- `$authConfig` - Конфігурація автентифікації:
  - `username` (string) - Ім'я користувача для автентифікації в реєстрі
  - `password` (string) - Пароль для автентифікації в реєстрі
  - `email` (string) - Email для автентифікації в реєстрі (необов'язковий)
  - `serveraddress` (string) - Адреса реєстру (наприклад, https://index.docker.io/v1/)
  - `identitytoken` (string) - Токен ідентифікації для реєстру (необов'язковий)
  - `registrytoken` (string) - Токен реєстру (необов'язковий)

#### Повертає:
- Масив з результатом автентифікації зі статусом та токеном, якщо успішно

#### Винятки:
- `MissingRequiredParameterException` - якщо відсутні обов'язкові параметри
- `OperationFailedException` - якщо автентифікація не вдалася

### ping
```php
public function ping(): bool
```
Перевіряє доступність сервера Docker.

#### Повертає:
- `true`, якщо сервер Docker доступний, `false` - якщо ні

### events
```php
public function events(array $filters = []): array
```
Отримує події від сервера в реальному часі.

#### Параметри:
- `$filters` - Фільтри для застосування до подій:
  - `config` - об'єкт з атрибутами (необов'язковий)
  - `type` - масив типів подій (необов'язковий, використовуйте константи EVENT_TYPE_*)
  - `until` - мітка часу (необов'язковий)
  - `since` - мітка часу (необов'язковий)

#### Повертає:
- Масив подій

#### Винятки:
- `InvalidParameterValueException` - якщо деякі значення фільтрів неприпустимі
- `OperationFailedException` - якщо запит не вдався

#### Приклад:
```php
// Отримати тільки події контейнерів
$events = $system->events(['type' => [System::EVENT_TYPE_CONTAINER]]);

// Отримати події для контейнерів і мереж з учорашнього дня
$events = $system->events([
    'type' => [System::EVENT_TYPE_CONTAINER, System::EVENT_TYPE_NETWORK],
    'since' => strtotime('-1 day')
]);
```

### dataUsage
```php
public function dataUsage(): array
```
Отримує інформацію про використання даних.

#### Повертає:
- Масив з інформацією про використання даних, включаючи образи, контейнери, томи тощо

#### Винятки:
- `OperationFailedException` - якщо запит не вдався

### usage
```php
public function usage(): array
```
Аналог методу dataUsage(), отримує інформацію про використання системи.

#### Повертає:
- Масив з інформацією про використання системи

#### Винятки:
- `OperationFailedException` - якщо запит не вдався

### prune
```php
public function prune(array $options = []): array
```
Видаляє невикористані дані (контейнери, мережі, образи, томи).

#### Параметри:
- `$options` - Опції очищення:
  - `volumes` (bool) - Видалити невикористані томи
  - `networks` (bool) - Видалити невикористані мережі
  - `containers` (bool) - Видалити зупинені контейнери
  - `images` (bool) - Видалити невикористані образи
  - `builder` (bool) - Очистити кеш збірки
  - `filters` (array) - Фільтри для застосування

#### Повертає:
- Масив з результатом очищення, включаючи звільнене місце

#### Винятки:
- `InvalidParameterValueException` - якщо опції неприпустимі
- `OperationFailedException` - якщо очищення не вдалося

## Приклади використання

### Отримання інформації про Docker
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createUnix();

// Отримати інформацію про версію Docker
$version = $client->system()->version();
echo "Docker Engine версія: {$version['Version']}\n";
echo "API версія: {$version['ApiVersion']}\n";

// Отримати системну інформацію
$info = $client->system()->info();
echo "Кількість контейнерів: {$info['Containers']}\n";
echo "Кількість образів: {$info['Images']}\n";
echo "ОС/Архітектура: {$info['OperatingSystem']}/{$info['Architecture']}\n";
```

### Перевірка автентифікації
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createUnix();

$auth = $client->system()->auth([
    'username' => 'myusername',
    'password' => 'mypassword',
    'serveraddress' => 'https://index.docker.io/v1/'
]);

if (isset($auth['Status']) && $auth['Status'] === 'Login Succeeded') {
    echo "Автентифікація успішна\n";
} else {
    echo "Помилка автентифікації\n";
}
```

### Моніторинг подій
```php
use Sangezar\DockerClient\DockerClient;
use Sangezar\DockerClient\Api\System;

$client = DockerClient::createUnix();

// Отримати події контейнерів за останню годину
$events = $client->system()->events([
    'type' => [System::EVENT_TYPE_CONTAINER],
    'since' => strtotime('-1 hour')
]);

foreach ($events as $event) {
    echo "Подія: {$event['Type']}/{$event['Action']} для {$event['Actor']['ID']}\n";
    echo "Час: " . date('Y-m-d H:i:s', $event['time']) . "\n";
}
```

### Очищення невикористаних ресурсів
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createUnix();

// Очистити всі невикористані ресурси (контейнери, мережі, образи, томи)
$pruneResult = $client->system()->prune([
    'volumes' => true,
    'images' => true
]);

echo "Звільнено місця: " . ($pruneResult['SpaceReclaimed'] ?? 0) . " байт\n";
``` 