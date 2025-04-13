# Документація класу Network

## Опис
`Network` - це клас для роботи з мережами Docker через API. Він надає методи для створення, інспектування, підключення контейнерів та керування мережами Docker.

## Простір імен
`Sangezar\DockerClient\Api`

## Успадкування
Клас `Network` успадковується від `AbstractApi` та реалізує інтерфейс `NetworkInterface`.

## Константи

### Типи драйверів мережі
```php
public const DRIVER_BRIDGE = 'bridge';
public const DRIVER_HOST = 'host';
public const DRIVER_OVERLAY = 'overlay';
public const DRIVER_MACVLAN = 'macvlan';
public const DRIVER_IPVLAN = 'ipvlan';
public const DRIVER_NONE = 'none';
```

### Константи для параметрів створення мережі
```php
public const SCOPE_LOCAL = 'local';
public const SCOPE_SWARM = 'swarm';
public const SCOPE_GLOBAL = 'global';

public const IPAM_DRIVER_DEFAULT = 'default';
public const IPAM_DRIVER_NULL = 'null';
```

## Методи

### list
```php
public function list(array $filters = []): array
```
Отримує список мереж.

#### Параметри:
- `$filters` - Фільтри для застосування у форматі JSON або масиву:
  - `driver` (string) - Фільтрація за драйвером мережі
  - `id` (string) - ID мережі
  - `label` (string) - Фільтрація за мітками мережі
  - `name` (string) - Ім'я мережі
  - `scope` (string) - Фільтрація за областю мережі (swarm, global або local)
  - `type` (string) - Фільтрація за типом мережі (custom або builtin)

#### Повертає:
- Масив мереж

#### Винятки:
- `InvalidParameterValueException` - якщо передані неприпустимі фільтри

### inspect
```php
public function inspect(string $id): array
```
Отримує детальну інформацію про мережу.

#### Параметри:
- `$id` - ID або ім'я мережі

#### Повертає:
- Масив з детальною інформацією про мережу

#### Винятки:
- `MissingRequiredParameterException` - якщо ID мережі порожній
- `NotFoundException` - якщо мережа не знайдена

### create
```php
public function create(NetworkConfig $config): array
```
Створює нову мережу.

#### Параметри:
- `$config` - Об'єкт `NetworkConfig` з конфігурацією мережі

#### Повертає:
- Масив з інформацією про створену мережу

#### Винятки:
- `InvalidConfigurationException` - якщо конфігурація неприпустима
- `OperationFailedException` - якщо операція не вдалася

### connect
```php
public function connect(string $id, string $container, array $config = []): bool
```
Підключає контейнер до мережі.

#### Параметри:
- `$id` - ID або ім'я мережі
- `$container` - ID або ім'я контейнера
- `$config` - Конфігурація підключення:
  - `EndpointConfig` (array) - Конфігурація кінцевої точки
  - `IPAddress` (string) - IPv4 адреса
  - `IPv6Address` (string) - IPv6 адреса
  - `Links` (array) - Посилання на інші контейнери
  - `Aliases` (array) - Імена для використання в мережі

#### Повертає:
- `true`, якщо підключення успішне

#### Винятки:
- `MissingRequiredParameterException` - якщо обов'язкові параметри відсутні
- `NotFoundException` - якщо мережа або контейнер не знайдені
- `OperationFailedException` - якщо підключення не вдалося

### disconnect
```php
public function disconnect(string $networkId, string $containerId, array $config = []): bool
```
Відключає контейнер від мережі.

#### Параметри:
- `$networkId` - ID або ім'я мережі
- `$containerId` - ID або ім'я контейнера
- `$config` - Конфігурація відключення:
  - `Force` (bool) - Примусово відключити контейнер навіть якщо це призведе до помилки зв'язку

#### Повертає:
- `true`, якщо відключення успішне

#### Винятки:
- `MissingRequiredParameterException` - якщо обов'язкові параметри відсутні
- `NotFoundException` - якщо мережа або контейнер не знайдені
- `OperationFailedException` - якщо відключення не вдалося

### remove
```php
public function remove(string $id): bool
```
Видаляє мережу.

#### Параметри:
- `$id` - ID або ім'я мережі

#### Повертає:
- `true`, якщо мережа успішно видалена

#### Винятки:
- `MissingRequiredParameterException` - якщо ID мережі порожній
- `NotFoundException` - якщо мережа не знайдена
- `OperationFailedException` - якщо видалення не вдалося (наприклад, мережа використовується)

### prune
```php
public function prune(array $filters = []): array
```
Видаляє всі невикористані мережі.

#### Параметри:
- `$filters` - Фільтри для визначення мереж для очищення:
  - `until` (string) - Видалити мережі, створені до вказаного часу
  - `label` (string) - Видалити тільки мережі з вказаними мітками

#### Повертає:
- Масив з інформацією про видалені мережі, включаючи звільнене місце

#### Винятки:
- `InvalidParameterValueException` - якщо фільтри неприпустимі
- `OperationFailedException` - якщо очищення не вдалося

### exists
```php
public function exists(string $id): bool
```
Перевіряє, чи існує мережа.

#### Параметри:
- `$id` - ID або ім'я мережі

#### Повертає:
- `true`, якщо мережа існує, `false` - якщо ні

#### Винятки:
- `MissingRequiredParameterException` - якщо ID мережі порожній

## Приклади використання

### Отримання списку мереж
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createUnix();
$networks = $client->network()->list();

foreach ($networks as $network) {
    echo "Мережа: {$network['Name']}, драйвер: {$network['Driver']}\n";
}
```

### Створення нової мережі
```php
use Sangezar\DockerClient\DockerClient;
use Sangezar\DockerClient\Config\NetworkConfig;

$client = DockerClient::createUnix();

$config = new NetworkConfig();
$config->setName('my-network')
       ->setDriver(Network::DRIVER_BRIDGE)
       ->setOptions([
           'com.docker.network.bridge.name' => 'my-bridge'
       ]);

$network = $client->network()->create($config);
echo "Мережу створено з ID: {$network['Id']}\n";
```

### Підключення контейнера до мережі
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createUnix();
$networkId = 'my-network';
$containerId = 'my-container';

$client->network()->connect($networkId, $containerId, [
    'Aliases' => ['web-server'],
    'IPAddress' => '172.18.0.10'
]);

echo "Контейнер {$containerId} підключено до мережі {$networkId}\n";
```

### Видалення мережі
```php
use Sangezar\DockerClient\DockerClient;

$client = DockerClient::createUnix();
$networkId = 'my-network';

if ($client->network()->exists($networkId)) {
    $client->network()->remove($networkId);
    echo "Мережу {$networkId} видалено\n";
}
``` 