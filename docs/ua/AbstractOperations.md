# Документація класу AbstractOperations

## Опис
`AbstractOperations` - це абстрактний базовий клас для всіх класів операцій кластера Docker. Він забезпечує основну функціональність для виконання операцій на декількох вузлах Docker одночасно, з підтримкою послідовного та паралельного виконання, повторних спроб при невдачі та різних рівнів деталізації помилок.

## Простір імен
`Sangezar\DockerClient\Cluster\Operations`

## Константи

### Типи стратегій виконання
```php
public const EXECUTION_SEQUENTIAL = 'sequential';
public const EXECUTION_PARALLEL = 'parallel';
```

### Рівні деталізації помилок
```php
public const ERROR_LEVEL_BASIC = 'basic';      // Тільки повідомлення
public const ERROR_LEVEL_STANDARD = 'standard'; // Повідомлення + тип винятку + код
public const ERROR_LEVEL_DETAILED = 'detailed'; // Всі деталі, включаючи стек викликів
```

## Властивості
```php
/** @var array<string, DockerClient> */
protected array $nodes;

/** @var string Стратегія виконання */
protected string $executionStrategy = self::EXECUTION_SEQUENTIAL;

/** @var string Рівень деталізації помилок */
protected string $errorDetailLevel = self::ERROR_LEVEL_STANDARD;

/** @var bool Дозволити автоматичні повторні спроби при невдачі */
protected bool $retryOnFailure = false;

/** @var int Максимальна кількість повторних спроб */
protected int $maxRetries = 3;
```

## Методи

### __construct
```php
public function __construct(array $nodes, ?ClusterConfig $config = null)
```
Конструктор.

#### Параметри:
- `$nodes` - Масив клієнтів Docker API з іменами вузлів як ключами
- `$config` - Конфігурація кластера (необов'язково)

#### Винятки:
- `MissingRequiredParameterException` - якщо масив вузлів порожній
- `InvalidParameterValueException` - якщо параметри неприпустимі

### applyConfig
```php
public function applyConfig(ClusterConfig $config): self
```
Застосовує конфігурацію кластера.

#### Параметри:
- `$config` - Конфігурація для застосування

#### Повертає:
- Поточний об'єкт для ланцюжкових викликів

### setExecutionStrategy
```php
public function setExecutionStrategy(string $strategy): self
```
Встановлює стратегію виконання.

#### Параметри:
- `$strategy` - Стратегія виконання (EXECUTION_SEQUENTIAL або EXECUTION_PARALLEL)

#### Повертає:
- Поточний об'єкт для ланцюжкових викликів

#### Винятки:
- `InvalidParameterValueException` - якщо вказана невідома стратегія

### setErrorDetailLevel
```php
public function setErrorDetailLevel(string $level): self
```
Встановлює рівень деталізації помилок.

#### Параметри:
- `$level` - Рівень деталізації (ERROR_LEVEL_BASIC, ERROR_LEVEL_STANDARD або ERROR_LEVEL_DETAILED)

#### Повертає:
- Поточний об'єкт для ланцюжкових викликів

#### Винятки:
- `InvalidParameterValueException` - якщо вказаний невідомий рівень

### setRetryOnFailure
```php
public function setRetryOnFailure(bool $enable, ?int $maxRetries = null): self
```
Встановлює налаштування повторних спроб при невдачі.

#### Параметри:
- `$enable` - Чи дозволені повторні спроби
- `$maxRetries` - Максимальна кількість повторних спроб (за замовчуванням 3)

#### Повертає:
- Поточний об'єкт для ланцюжкових викликів

#### Винятки:
- `InvalidParameterValueException` - якщо кількість повторних спроб менше 1

### executeOnAll
```php
protected function executeOnAll(callable $operation): array
```
Виконує операцію на всіх вузлах кластера.

#### Параметри:
- `$operation` - Функція, яка буде виконана на кожному вузлі

#### Повертає:
- Масив результатів виконання для кожного вузла

#### Винятки:
- `InvalidParameterValueException` - якщо операція не є викликаємою

### getNodes
```php
public function getNodes(): array
```
Отримує всі вузли.

#### Повертає:
- Масив вузлів, де ключі - імена вузлів, а значення - екземпляри `DockerClient`

### isEmpty
```php
public function isEmpty(): bool
```
Перевіряє, чи колекція вузлів порожня.

#### Повертає:
- `true`, якщо колекція не містить вузлів, `false` - якщо містить

### count
```php
public function count(): int
```
Підраховує кількість вузлів.

#### Повертає:
- Кількість вузлів

### addNode
```php
public function addNode(string $name, DockerClient $client): self
```
Додає новий вузол до колекції.

#### Параметри:
- `$name` - Ім'я вузла
- `$client` - Клієнт Docker API

#### Повертає:
- Поточний об'єкт для ланцюжкових викликів

#### Винятки:
- `InvalidParameterValueException` - якщо ім'я вузла порожнє або вузол з таким іменем вже існує

### removeNode
```php
public function removeNode(string $name): self
```
Видаляє вузол з колекції.

#### Параметри:
- `$name` - Ім'я вузла

#### Повертає:
- Поточний об'єкт для ланцюжкових викликів

### hasNode
```php
public function hasNode(string $name): bool
```
Перевіряє, чи існує вузол з вказаним іменем.

#### Параметри:
- `$name` - Ім'я вузла

#### Повертає:
- `true`, якщо вузол існує, `false` - якщо ні

## Захищені та приватні методи

### executeSequential
```php
private function executeSequential(callable $operation): array
```
Виконує операцію послідовно на всіх вузлах кластера.

#### Параметри:
- `$operation` - Функція, яка буде виконана на кожному вузлі

#### Повертає:
- Масив результатів виконання для кожного вузла

### executeParallel
```php
private function executeParallel(callable $operation): array
```
Виконує операцію паралельно на всіх вузлах кластера.

#### Параметри:
- `$operation` - Функція, яка буде виконана на кожному вузлі

#### Повертає:
- Масив результатів виконання для кожного вузла

### formatError
```php
private function formatError(\Throwable $e): array
```
Форматує помилку відповідно до встановленого рівня деталізації.

#### Параметри:
- `$e` - Об'єкт винятку

#### Повертає:
- Форматований масив з інформацією про помилку

## Приклади використання

### Базове використання в успадкованих класах
```php
class MyOperations extends AbstractOperations
{
    public function perform(): array
    {
        return $this->executeOnAll(function (DockerClient $client) {
            // Виконати операцію з клієнтом
            return $client->container()->list();
        });
    }
}

// Створення екземпляру
$nodes = [
    'node1' => DockerClient::createTcp('tcp://192.168.1.10:2375'),
    'node2' => DockerClient::createTcp('tcp://192.168.1.11:2375'),
];
$operations = new MyOperations($nodes);

// Виконання операції на всіх вузлах
$results = $operations->perform();
```

### Налаштування стратегії виконання та обробки помилок
```php
use Sangezar\DockerClient\Config\ClusterConfig;
use Sangezar\DockerClient\Cluster\Operations\AbstractOperations;

// Створення конфігурації кластера
$config = ClusterConfig::create()
    ->setExecutionStrategy(AbstractOperations::EXECUTION_PARALLEL)
    ->setErrorDetailLevel(AbstractOperations::ERROR_LEVEL_DETAILED)
    ->setRetryOnFailure(true, 5);

// Застосування конфігурації до операцій
$operations->applyConfig($config);

// Або індивідуальне налаштування
$operations->setExecutionStrategy(AbstractOperations::EXECUTION_PARALLEL)
    ->setErrorDetailLevel(AbstractOperations::ERROR_LEVEL_DETAILED)
    ->setRetryOnFailure(true, 5);
```

### Керування вузлами під час виконання
```php
// Додавання нового вузла
$operations->addNode('node3', DockerClient::createTcp('tcp://192.168.1.12:2375'));

// Перевірка наявності вузла
if ($operations->hasNode('node1')) {
    echo "Вузол 'node1' існує\n";
}

// Видалення вузла
$operations->removeNode('node2');

// Отримання списку всіх вузлів
$allNodes = $operations->getNodes();

// Перевірка кількості вузлів
echo "Кількість вузлів: " . $operations->count() . "\n";
``` 