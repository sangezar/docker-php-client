# Документація класу DockerCluster

## Опис
`DockerCluster` - це клас, що представляє кластер серверів Docker. Він дозволяє керувати набором вузлів Docker, групувати їх за допомогою тегів та виконувати операції як на окремих вузлах, так і на групах вузлів.

## Простір імен
`Sangezar\DockerClient\Cluster`

## Константи
```php
// Регулярний вираз для валідації імен вузлів
private const NODE_NAME_PATTERN = '/^[a-zA-Z0-9][a-zA-Z0-9_.-]*$/';

// Регулярний вираз для валідації імен тегів
private const TAG_NAME_PATTERN = '/^[a-zA-Z0-9][a-zA-Z0-9_.-]*$/';
```

## Методи

### addNode
```php
public function addNode(string $name, DockerClient $client, array $tags = []): self
```
Додає новий вузол до кластера.

#### Параметри:
- `$name` - Унікальне ім'я вузла
- `$client` - Клієнт Docker API
- `$tags` - Масив тегів для категоризації вузла (за замовчуванням порожній масив)

#### Повертає:
- Екземпляр `DockerCluster` (для ланцюжкових викликів)

#### Винятки:
- `ValidationException` - якщо ім'я вузла неприпустиме або вже існує

### node
```php
public function node(string $name): DockerClient
```
Повертає клієнт вузла за його іменем.

#### Параметри:
- `$name` - Ім'я вузла

#### Повертає:
- Екземпляр `DockerClient` для вказаного вузла

#### Винятки:
- `NodeNotFoundException` - якщо вузол не знайдено
- `ValidationException` - якщо ім'я вузла порожнє

### hasNode
```php
public function hasNode(string $name): bool
```
Перевіряє, чи існує вузол з вказаним іменем.

#### Параметри:
- `$name` - Ім'я вузла

#### Повертає:
- `true`, якщо вузол існує, `false` - якщо ні

### removeNode
```php
public function removeNode(string $name): self
```
Видаляє вузол з кластера.

#### Параметри:
- `$name` - Ім'я вузла

#### Повертає:
- Екземпляр `DockerCluster` (для ланцюжкових викликів)

#### Винятки:
- `ValidationException` - якщо ім'я вузла порожнє

### getNodesByTag
```php
public function getNodesByTag(string $tag): array
```
Повертає всі вузли з вказаним тегом.

#### Параметри:
- `$tag` - Тег для фільтрації

#### Повертає:
- Асоціативний масив вузлів, які мають вказаний тег

#### Винятки:
- `ValidationException` - якщо тег неприпустимий

### addTagToNode
```php
public function addTagToNode(string $nodeName, string $tag): self
```
Додає тег до існуючого вузла.

#### Параметри:
- `$nodeName` - Ім'я вузла
- `$tag` - Тег для додавання

#### Повертає:
- Екземпляр `DockerCluster` (для ланцюжкових викликів)

#### Винятки:
- `NodeNotFoundException` - якщо вузол не знайдено
- `ValidationException` - якщо тег неприпустимий або ім'я вузла порожнє

### removeTagFromNode
```php
public function removeTagFromNode(string $nodeName, string $tag): self
```
Видаляє тег з вузла.

#### Параметри:
- `$nodeName` - Ім'я вузла
- `$tag` - Тег для видалення

#### Повертає:
- Екземпляр `DockerCluster` (для ланцюжкових викликів)

#### Винятки:
- `NodeNotFoundException` - якщо вузол не знайдено
- `ValidationException` - якщо ім'я вузла порожнє

### getNodesByAllTags
```php
public function getNodesByAllTags(array $tags): array
```
Повертає всі вузли, які мають всі вказані теги (операція І).

#### Параметри:
- `$tags` - Масив тегів

#### Повертає:
- Асоціативний масив вузлів, які мають всі вказані теги

#### Винятки:
- `ValidationException` - якщо будь-який тег неприпустимий

### getNodesByAnyTag
```php
public function getNodesByAnyTag(array $tags): array
```
Повертає всі вузли, які мають хоча б один з вказаних тегів (операція АБО).

#### Параметри:
- `$tags` - Масив тегів

#### Повертає:
- Асоціативний масив вузлів, які мають хоча б один з вказаних тегів

#### Винятки:
- `ValidationException` - якщо будь-який тег неприпустимий

### filter
```php
public function filter(callable $callback): NodeCollection
```
Повертає колекцію вузлів, відфільтровану за допомогою функції зворотного виклику.

#### Параметри:
- `$callback` - Функція-колбек для фільтрації, яка приймає вузол як аргумент і повертає булеве значення

#### Повертає:
- Екземпляр `NodeCollection` з відфільтрованими вузлами

### all
```php
public function all(): NodeCollection
```
Повертає колекцію всіх вузлів кластера.

#### Повертає:
- Екземпляр `NodeCollection` з усіма вузлами

### byTag
```php
public function byTag(string $tag): NodeCollection
```
Повертає колекцію вузлів з вказаним тегом.

#### Параметри:
- `$tag` - Тег для фільтрації

#### Повертає:
- Екземпляр `NodeCollection` з вузлами, які мають вказаний тег

### byAllTags
```php
public function byAllTags(array $tags): NodeCollection
```
Повертає колекцію вузлів, які мають всі вказані теги.

#### Параметри:
- `$tags` - Масив тегів

#### Повертає:
- Екземпляр `NodeCollection` з вузлами, які мають всі вказані теги

### byAnyTag
```php
public function byAnyTag(array $tags): NodeCollection
```
Повертає колекцію вузлів, які мають хоча б один з вказаних тегів.

#### Параметри:
- `$tags` - Масив тегів

#### Повертає:
- Екземпляр `NodeCollection` з вузлами, які мають хоча б один з вказаних тегів

### addNodes
```php
public function addNodes(array $nodes): self
```
Додає кілька вузлів до кластера.

#### Параметри:
- `$nodes` - Масив вузлів для додавання, де ключі:
  - `name` (string) - Ім'я вузла
  - `client` (DockerClient) - Клієнт Docker API
  - `tags` (array, необов'язковий) - Масив тегів

#### Повертає:
- Екземпляр `DockerCluster` (для ланцюжкових викликів)

#### Винятки:
- Ті самі, що і в методі `addNode`

### getNodes
```php
public function getNodes(): array
```
Повертає всі вузли кластера.

#### Повертає:
- Асоціативний масив вузлів, де ключі - імена вузлів, а значення - екземпляри `DockerClient`

### getTags
```php
public function getTags(): array
```
Повертає всі теги кластера.

#### Повертає:
- Асоціативний масив тегів, де ключі - імена тегів, а значення - масиви імен вузлів

### isEmpty
```php
public function isEmpty(): bool
```
Перевіряє, чи кластер порожній.

#### Повертає:
- `true`, якщо в кластері немає вузлів, `false` - якщо є

### count
```php
public function count(): int
```
Підраховує кількість вузлів у кластері.

#### Повертає:
- Кількість вузлів у кластері

## Приклади використання

### Створення кластера та додавання вузлів
```php
use Sangezar\DockerClient\DockerClient;
use Sangezar\DockerClient\Cluster\DockerCluster;

// Створення кластера
$cluster = new DockerCluster();

// Додавання вузлів з тегами
$cluster->addNode(
    'node1',
    DockerClient::createTcp('tcp://192.168.1.10:2375'),
    ['production', 'web']
);

$cluster->addNode(
    'node2',
    DockerClient::createTcp('tcp://192.168.1.11:2375'),
    ['production', 'database']
);

$cluster->addNode(
    'node3',
    DockerClient::createTcp('tcp://192.168.1.12:2375'),
    ['staging', 'web']
);

// Перевірка кількості вузлів
echo "Загальна кількість вузлів: " . $cluster->count() . "\n";
```

### Отримання вузлів за тегами
```php
// Отримання всіх вузлів з тегом 'production'
$productionNodes = $cluster->getNodesByTag('production');
echo "Вузли з тегом 'production': " . implode(', ', array_keys($productionNodes)) . "\n";

// Отримання вузлів, які мають обидва теги: 'production' та 'web'
$productionWebNodes = $cluster->getNodesByAllTags(['production', 'web']);
echo "Вузли з тегами 'production' та 'web': " . implode(', ', array_keys($productionWebNodes)) . "\n";

// Отримання вузлів, які мають тег 'production' або 'staging'
$allEnvNodes = $cluster->getNodesByAnyTag(['production', 'staging']);
echo "Вузли з тегами 'production' або 'staging': " . implode(', ', array_keys($allEnvNodes)) . "\n";
```

### Виконання операцій на групі вузлів
```php
// Отримання колекції вузлів за тегом і виконання операцій
$webNodesCollection = $cluster->byTag('web');

// Отримання списку всіх контейнерів на веб-вузлах
$containersMap = $webNodesCollection->containers()->list(['all' => true]);

foreach ($containersMap as $nodeName => $containers) {
    echo "Вузол: $nodeName\n";
    foreach ($containers as $container) {
        echo "  Контейнер: {$container['Names'][0]}\n";
    }
}
```

### Керування тегами
```php
// Додавання тегу до вузла
$cluster->addTagToNode('node3', 'monitoring');

// Видалення тегу з вузла
$cluster->removeTagFromNode('node2', 'production');

// Перевірка наявності вузла
if ($cluster->hasNode('node1')) {
    echo "Вузол 'node1' існує\n";
    
    // Виконання операцій на окремому вузлі
    $node1Client = $cluster->node('node1');
    $containers = $node1Client->container()->list(['all' => true]);
    echo "Кількість контейнерів на вузлі 'node1': " . count($containers) . "\n";
}
``` 