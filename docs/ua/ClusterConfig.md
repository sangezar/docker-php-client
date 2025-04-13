# Документація класу ClusterConfig

## Опис
`ClusterConfig` - це клас для налаштування операцій Docker кластера. Він дозволяє встановлювати стратегію виконання, рівень деталізації помилок, налаштовувати повторні спроби, встановлювати пріоритети вузлів та інші параметри кластера.

## Простір імен
`Sangezar\DockerClient\Config`

## Методи

### create
```php
public static function create(): self
```
Створює новий екземпляр конфігурації кластера.

#### Повертає:
- Новий екземпляр `ClusterConfig`

### setExecutionStrategy
```php
public function setExecutionStrategy(string $strategy): self
```
Встановлює стратегію виконання операцій на вузлах кластера.

#### Параметри:
- `$strategy` - Стратегія виконання:
  - `AbstractOperations::EXECUTION_SEQUENTIAL` - послідовне виконання
  - `AbstractOperations::EXECUTION_PARALLEL` - паралельне виконання

#### Повертає:
- Поточний екземпляр для ланцюжкових викликів

#### Винятки:
- `InvalidParameterValueException` - якщо вказана невідома стратегія

### setErrorDetailLevel
```php
public function setErrorDetailLevel(string $level): self
```
Встановлює рівень деталізації помилок.

#### Параметри:
- `$level` - Рівень деталізації:
  - `AbstractOperations::ERROR_LEVEL_BASIC` - базовий рівень (тільки повідомлення)
  - `AbstractOperations::ERROR_LEVEL_STANDARD` - стандартний рівень (повідомлення, тип винятку, код)
  - `AbstractOperations::ERROR_LEVEL_DETAILED` - детальний рівень (всі деталі, включаючи стек викликів)

#### Повертає:
- Поточний екземпляр для ланцюжкових викликів

#### Винятки:
- `InvalidParameterValueException` - якщо вказаний невідомий рівень

### setRetryConfig
```php
public function setRetryConfig(bool $enable, ?int $maxRetries = null, ?int $retryDelay = null, ?bool $exponentialBackoff = null): self
```
Налаштовує параметри повторних спроб при помилках.

#### Параметри:
- `$enable` - Дозволити повторні спроби
- `$maxRetries` - Максимальна кількість спроб (за замовчуванням 3)
- `$retryDelay` - Початкова затримка між спробами в мілісекундах (за замовчуванням 1000)
- `$exponentialBackoff` - Чи використовувати експоненціальне зростання затримки між спробами

#### Повертає:
- Поточний екземпляр для ланцюжкових викликів

#### Винятки:
- `InvalidParameterValueException` - якщо параметри неприпустимі

### setOperationTimeout
```php
public function setOperationTimeout(int $seconds): self
```
Встановлює таймаут для операцій вузлів кластера.

#### Параметри:
- `$seconds` - Таймаут у секундах

#### Повертає:
- Поточний екземпляр для ланцюжкових викликів

#### Винятки:
- `InvalidParameterValueException` - якщо значення таймауту неприпустиме

### setNodePriority
```php
public function setNodePriority(string $nodeName, int $priority): self
```
Встановлює пріоритет для вузла.

#### Параметри:
- `$nodeName` - Ім'я вузла
- `$priority` - Пріоритет (1 - найвищий)

#### Повертає:
- Поточний екземпляр для ланцюжкових викликів

#### Винятки:
- `InvalidParameterValueException` - якщо значення пріоритету неприпустиме

### setDefaultNodeTag
```php
public function setDefaultNodeTag(?string $tag): self
```
Встановлює тег за замовчуванням для операцій.

#### Параметри:
- `$tag` - Тег для фільтрації вузлів

#### Повертає:
- Поточний екземпляр для ланцюжкових викликів

### addFailoverNode
```php
public function addFailoverNode(string $nodeName): self
```
Додає вузол до списку резервних вузлів.

#### Параметри:
- `$nodeName` - Ім'я вузла для використання у випадку збою основних вузлів

#### Повертає:
- Поточний екземпляр для ланцюжкових викликів

#### Винятки:
- `InvalidParameterValueException` - якщо ім'я вузла порожнє

### getExecutionStrategy
```php
public function getExecutionStrategy(): string
```
Отримує поточну стратегію виконання.

#### Повертає:
- Поточна стратегія виконання

### getErrorDetailLevel
```php
public function getErrorDetailLevel(): string
```
Отримує поточний рівень деталізації помилок.

#### Повертає:
- Поточний рівень деталізації помилок

### isRetryOnFailure
```php
public function isRetryOnFailure(): bool
```
Перевіряє, чи дозволені повторні спроби.

#### Повертає:
- `true`, якщо повторні спроби дозволені, `false` - якщо ні

### getMaxRetries
```php
public function getMaxRetries(): int
```
Отримує максимальну кількість повторних спроб.

#### Повертає:
- Максимальна кількість повторних спроб

### getRetryDelay
```php
public function getRetryDelay(): int
```
Отримує затримку між повторними спробами.

#### Повертає:
- Затримка в мілісекундах

### isExponentialBackoff
```php
public function isExponentialBackoff(): bool
```
Перевіряє, чи використовується експоненціальне зростання затримки.

#### Повертає:
- `true`, якщо використовується експоненціальне зростання, `false` - якщо ні

### getOperationTimeout
```php
public function getOperationTimeout(): int
```
Отримує таймаут операцій.

#### Повертає:
- Таймаут у секундах

### getNodePriorities
```php
public function getNodePriorities(): array
```
Отримує пріоритети вузлів.

#### Повертає:
- Масив пріоритетів, де ключі - імена вузлів, а значення - пріоритети

### getDefaultNodeTag
```php
public function getDefaultNodeTag(): ?string
```
Отримує тег за замовчуванням.

#### Повертає:
- Тег за замовчуванням або `null`, якщо не встановлено

### getFailoverNodes
```php
public function getFailoverNodes(): array
```
Отримує список резервних вузлів.

#### Повертає:
- Масив імен резервних вузлів

### toArray
```php
public function toArray(): array
```
Перетворює конфігурацію в масив.

#### Повертає:
- Масив з усіма налаштуваннями конфігурації

## Приклади використання

### Створення базової конфігурації
```php
use Sangezar\DockerClient\Config\ClusterConfig;
use Sangezar\DockerClient\Cluster\Operations\AbstractOperations;

// Створення конфігурації з параметрами за замовчуванням
$config = ClusterConfig::create();

// Налаштування стратегії виконання та рівня помилок
$config->setExecutionStrategy(AbstractOperations::EXECUTION_PARALLEL)
       ->setErrorDetailLevel(AbstractOperations::ERROR_LEVEL_DETAILED);
```

### Налаштування повторних спроб
```php
use Sangezar\DockerClient\Config\ClusterConfig;

$config = ClusterConfig::create();

// Дозволити повторні спроби з максимум 5 спроб
// та затримкою 500 мс між спробами
$config->setRetryConfig(true, 5, 500, true);

// Встановити таймаут операції в 60 секунд
$config->setOperationTimeout(60);
```

### Налаштування пріоритетів вузлів
```php
use Sangezar\DockerClient\Config\ClusterConfig;

$config = ClusterConfig::create();

// Встановлення пріоритетів для вузлів
$config->setNodePriority('node1', 1) // найвищий пріоритет
       ->setNodePriority('node2', 2)
       ->setNodePriority('node3', 3);

// Додавання резервних вузлів
$config->addFailoverNode('backup-node1')
       ->addFailoverNode('backup-node2');

// Встановлення тегу за замовчуванням
$config->setDefaultNodeTag('production');
```

### Отримання налаштувань конфігурації
```php
use Sangezar\DockerClient\Config\ClusterConfig;

$config = ClusterConfig::create()
    ->setExecutionStrategy(AbstractOperations::EXECUTION_PARALLEL)
    ->setRetryConfig(true, 3, 1000, true);

// Перевірка, чи дозволені повторні спроби
if ($config->isRetryOnFailure()) {
    echo "Повторні спроби дозволені\n";
    echo "Максимальна кількість спроб: " . $config->getMaxRetries() . "\n";
    echo "Затримка: " . $config->getRetryDelay() . " мс\n";
}

// Отримання всіх налаштувань як масив
$allSettings = $config->toArray();
``` 