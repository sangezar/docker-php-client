# Документація класу ContainerOperations

## Опис
`ContainerOperations` - це клас, що надає функціональність для виконання операцій з контейнерами Docker на всіх вузлах кластера одночасно. Цей клас розширює `AbstractOperations` і дозволяє отримувати список контейнерів, створювати, запускати, зупиняти, перезапускати та видаляти контейнери на всіх вузлах кластера.

## Простір імен
`Sangezar\DockerClient\Cluster\Operations`

## Успадкування
Клас розширює `AbstractOperations` і успадковує всі його методи та властивості.

## Методи

### list
```php
public function list(array $parameters = []): array
```
Отримує список контейнерів з усіх вузлів кластера.

#### Параметри:
- `$parameters` - Масив параметрів фільтрації:
  - `all` (bool): Показати всі контейнери (не тільки активні)
  - `size` (bool): Показувати розмір контейнерів
  - `limit` (int): Обмежити кількість результатів
  - `filters` (array): Фільтри для пошуку контейнерів

#### Повертає:
- Масив, де ключі - імена вузлів, а значення - результати операції на кожному вузлі

#### Винятки:
- `InvalidParameterValueException` - якщо надані неприпустимі параметри фільтрації

### create
```php
public function create(ContainerConfig $config): array
```
Створює контейнер на всіх вузлах кластера.

#### Параметри:
- `$config` - Конфігурація контейнера

#### Повертає:
- Масив, де ключі - імена вузлів, а значення - результати операції на кожному вузлі

#### Винятки:
- `InvalidConfigurationException` - якщо конфігурація недійсна
- `OperationFailedException` - якщо операція не вдалася

### inspect
```php
public function inspect(string $containerId): array
```
Отримує детальну інформацію про контейнер на всіх вузлах кластера.

#### Параметри:
- `$containerId` - ID або ім'я контейнера

#### Повертає:
- Масив, де ключі - імена вузлів, а значення - результати операції на кожному вузлі

#### Винятки:
- `MissingRequiredParameterException` - якщо ID контейнера порожній

### start
```php
public function start(string $containerId): array
```
Запускає контейнер на всіх вузлах кластера.

#### Параметри:
- `$containerId` - ID або ім'я контейнера

#### Повертає:
- Масив, де ключі - імена вузлів, а значення - результати операції на кожному вузлі

#### Винятки:
- `MissingRequiredParameterException` - якщо ID контейнера порожній

### stop
```php
public function stop(string $containerId, int $timeout = 10): array
```
Зупиняє контейнер на всіх вузлах кластера.

#### Параметри:
- `$containerId` - ID або ім'я контейнера
- `$timeout` - Тайм-аут зупинки в секундах (за замовчуванням 10)

#### Повертає:
- Масив, де ключі - імена вузлів, а значення - результати операції на кожному вузлі

#### Винятки:
- `MissingRequiredParameterException` - якщо ID контейнера порожній
- `InvalidParameterValueException` - якщо тайм-аут від'ємний

### restart
```php
public function restart(string $containerId, int $timeout = 10): array
```
Перезапускає контейнер на всіх вузлах кластера.

#### Параметри:
- `$containerId` - ID або ім'я контейнера
- `$timeout` - Тайм-аут зупинки в секундах (за замовчуванням 10)

#### Повертає:
- Масив, де ключі - імена вузлів, а значення - результати операції на кожному вузлі

#### Винятки:
- `MissingRequiredParameterException` - якщо ID контейнера порожній
- `InvalidParameterValueException` - якщо тайм-аут від'ємний

### remove
```php
public function remove(string $containerId, bool $force = false, bool $removeVolumes = false): array
```
Видаляє контейнер на всіх вузлах кластера.

#### Параметри:
- `$containerId` - ID або ім'я контейнера
- `$force` - Примусово видалити працюючий контейнер (за замовчуванням false)
- `$removeVolumes` - Видалити томи разом з контейнером (за замовчуванням false)

#### Повертає:
- Масив, де ключі - імена вузлів, а значення - результати операції на кожному вузлі

#### Винятки:
- `MissingRequiredParameterException` - якщо ID контейнера порожній

### logs
```php
public function logs(string $containerId, array $parameters = []): array
```
Отримує логи контейнера з усіх вузлів кластера.

#### Параметри:
- `$containerId` - ID або ім'я контейнера
- `$parameters` - Параметри запиту логів:
  - `stdout` (bool): Показувати стандартний вихід (stdout)
  - `stderr` (bool): Показувати стандартний потік помилок (stderr)
  - `since` (int): Показувати логи з вказаного часу (Unix timestamp)
  - `until` (int): Показувати логи до вказаного часу (Unix timestamp)
  - `timestamps` (bool): Додавати часові мітки до логів
  - `tail` (string/int): Кількість останніх рядків логів для повернення

#### Повертає:
- Масив, де ключі - імена вузлів, а значення - результати операції на кожному вузлі

#### Винятки:
- `MissingRequiredParameterException` - якщо ID контейнера порожній
- `InvalidParameterValueException` - якщо надані неприпустимі параметри

### stats
```php
public function stats(string $containerId, bool $stream = false): array
```
Отримує статистику використання ресурсів контейнером на всіх вузлах кластера.

#### Параметри:
- `$containerId` - ID або ім'я контейнера
- `$stream` - Чи слід потокову передачу статистики (за замовчуванням false)

#### Повертає:
- Масив, де ключі - імена вузлів, а значення - результати операції на кожному вузлі

#### Винятки:
- `MissingRequiredParameterException` - якщо ID контейнера порожній

### exists
```php
public function exists(string $containerId): array
```
Перевіряє, чи існує контейнер на всіх вузлах кластера.

#### Параметри:
- `$containerId` - ID або ім'я контейнера

#### Повертає:
- Масив, де ключі - імена вузлів, а значення - булеві результати (true, якщо контейнер існує, false - якщо ні)

#### Винятки:
- `MissingRequiredParameterException` - якщо ID контейнера порожній

### existsOnAllNodes
```php
public function existsOnAllNodes(string $containerId): bool
```
Перевіряє, чи існує контейнер на всіх вузлах кластера.

#### Параметри:
- `$containerId` - ID або ім'я контейнера

#### Повертає:
- `true`, якщо контейнер існує на всіх вузлах, `false` - якщо хоча б на одному вузлі контейнер відсутній

#### Винятки:
- `MissingRequiredParameterException` - якщо ID контейнера порожній

### getNodesWithContainer
```php
public function getNodesWithContainer(string $containerId): array
```
Отримує список вузлів, на яких існує контейнер.

#### Параметри:
- `$containerId` - ID або ім'я контейнера

#### Повертає:
- Масив з іменами вузлів, на яких існує контейнер

#### Винятки:
- `MissingRequiredParameterException` - якщо ID контейнера порожній

## Приклади використання

### Отримання списку контейнерів з усіх вузлів
```php
use Sangezar\DockerClient\Cluster\DockerCluster;
use Sangezar\DockerClient\DockerClient;

// Створення кластера
$cluster = new DockerCluster();
$cluster->addNode('node1', DockerClient::createTcp('tcp://192.168.1.10:2375'));
$cluster->addNode('node2', DockerClient::createTcp('tcp://192.168.1.11:2375'));

// Отримання списку всіх контейнерів
$containers = $cluster->node('node1')->containers()->list(['all' => true]);

// Перевірка результатів
foreach ($containers as $nodeName => $result) {
    echo "Контейнери на вузлі $nodeName:\n";
    foreach ($result as $container) {
        echo "  - {$container['Names'][0]} (ID: {$container['Id']})\n";
    }
}
```

### Створення та запуск контейнера на всіх вузлах
```php
use Sangezar\DockerClient\Config\ContainerConfig;
use Sangezar\DockerClient\Cluster\NodeCollection;
use Sangezar\DockerClient\DockerClient;

// Створення колекції вузлів
$nodes = [
    'node1' => DockerClient::createTcp('tcp://192.168.1.10:2375'),
    'node2' => DockerClient::createTcp('tcp://192.168.1.11:2375'),
];
$collection = new NodeCollection($nodes);

// Створення конфігурації контейнера
$config = ContainerConfig::create()
    ->setImage('nginx:latest')
    ->setName('test-nginx')
    ->exposePorts(80, 443);

// Створення контейнера на всіх вузлах
$results = $collection->containers()->create($config);

// Запуск контейнера на всіх вузлах
$startResults = $collection->containers()->start('test-nginx');
```

### Перевірка наявності контейнера на вузлах
```php
// Перевірка, чи існує контейнер на всіх вузлах
$exists = $collection->containers()->existsOnAllNodes('test-nginx');
if ($exists) {
    echo "Контейнер 'test-nginx' існує на всіх вузлах\n";
} else {
    // Отримання списку вузлів, де контейнер існує
    $nodesWithContainer = $collection->containers()->getNodesWithContainer('test-nginx');
    echo "Контейнер 'test-nginx' існує тільки на вузлах: " . implode(', ', $nodesWithContainer) . "\n";
}

// Зупинка та видалення контейнера
$collection->containers()->stop('test-nginx');
$collection->containers()->remove('test-nginx', true);
``` 